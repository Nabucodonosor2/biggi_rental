<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wi_factura_arriendo_aux extends wi_factura_arriendo  {
	var $cod_factura;
	
	function wi_factura_arriendo_aux() {
		parent::wi_factura_arriendo('2035');
	}
	function get_key() {
		return $this->cod_factura;
	}
	function _load_record() {
		return;
	}
}

class wo_factura_arriendo extends w_output_biggi {
	const K_ESTADO_SII_EMITIDA	= 1;
	const K_ESTADO_CONFIRMADA	= 4;
	const K_ESTADO_CERRADA = 2;
	const K_PARAM_MAX_IT_FA = 29;
	//const K_AUTORIZA_EXPORTAR = '992010';
	const K_AUTORIZA_SOLO_BITACORA = '992025';
	const K_TIPO_ARRIENDO = 2;
	const K_AUTORIZA_SUMAR = '992060';
	var $checkbox_sumar;

	function wo_factura_arriendo() {
		$this->checkbox_sumar = false;
		
		$sql = "select F.COD_FACTURA
						,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
						,F.FECHA_FACTURA DATE_FACTURA
						,F.NRO_FACTURA
						,F.RUT
						,F.DIG_VERIF
						,F.NOM_EMPRESA
						,F.COD_DOC
						,F.COD_USUARIO_VENDEDOR1
						,EDS.COD_ESTADO_DOC_SII
						,EDS.NOM_ESTADO_DOC_SII
						,F.TOTAL_CON_IVA
						,U.INI_USUARIO
						,dbo.f_fa_tipo_doc(F.COD_FACTURA) TIPO_FA
				 from	FACTURA F, ESTADO_DOC_SII EDS, USUARIO U
				where	F.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII AND
						F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO AND
						F.COD_TIPO_FACTURA = ".self::K_TIPO_ARRIENDO."
				order by	isnull(NRO_FACTURA, 9999999999) desc, COD_FACTURA desc";
				
	     parent::w_output_biggi('factura_arriendo', $sql, $_REQUEST['cod_item_menu']);
			
		$this->dw->add_control(new edit_nro_doc('COD_FACTURA','FACTURA'));
		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new edit_precio('TOTAL_CON_IVA'));
		/*
	   	$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_EXPORTAR, $this->cod_usuario);
		if ($priv=='E') {
			$this->b_export_visible = true;
      	}
      	else {
			$this->b_export_visible = false;
      	}*/
	  /* 	$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SOLO_BITACORA, $this->cod_usuario);	// acceso bitacora
		if ($priv=='E') {
			$this->b_add_visible = false;
      	}
      	else {
			$this->b_add_visible = true;
      	}*/
	   	
		// headers
		$this->add_header($control = new header_date('FECHA_FACTURA', 'FECHA_FACTURA', 'Fecha'));
		$control->field_bd_order = 'DATE_FACTURA';
		$this->add_header(new header_num('NRO_FACTURA', 'NRO_FACTURA', 'Nº FA'));
		$this->add_header(new header_rut('RUT', 'F', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
		$sql_estado_doc_sii = "select COD_ESTADO_DOC_SII ,NOM_ESTADO_DOC_SII from ESTADO_DOC_SII order by	COD_ESTADO_DOC_SII";
		$this->add_header(new header_drop_down('NOM_ESTADO_DOC_SII', 'EDS.COD_ESTADO_DOC_SII', 'Estado', $sql_estado_doc_sii));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA'));
		$this->add_header(new header_text('COD_DOC', 'COD_DOC', 'N° NV'));
		$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from FACTURA F, USUARIO U where F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_drop_down('INI_USUARIO', 'F.COD_USUARIO_VENDEDOR1', 'V1', $sql));
		
		$sql = "SELECT 'NORMAL' ES_TIPO, 'NORMAL' TIPO_FA UNION SELECT 'EXENTA' ES_TIPO , 'EXENTA' TIPO_FA";
		$this->add_header($control = new header_drop_down_string('TIPO_FA', '(dbo.f_fa_tipo_doc(COD_FACTURA))', 'Tipo FA', $sql)); 
  		$control->field_bd_order = 'TIPO_FA';
  		
  		// dw checkbox
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SUMAR, $this->cod_usuario);
		if ($priv=='E')
			$DISPLAY_SUMAR = '';
      	else
			$DISPLAY_SUMAR = 'none';
			
		$sql = "select '$DISPLAY_SUMAR' DISPLAY_SUMAR
						,'N' CHECK_SUMAR
					   ,'N' HIZO_CLICK";
		$this->dw_check_box = new datawindow($sql);
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_SUMAR','S','N'));
		$control->set_onClick("sumar(); document.getElementById('loader').style.display='';");
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();
	}
	function redraw(&$temp) {
  		if ($this->b_add_visible)
			$this->habilita_boton($temp, 'create', $this->get_privilegio_opcion_usuario($this->cod_item_menu, $this->cod_usuario)=='E');
		
		$this->habilita_boton($temp, 'print_dte', $this->get_privilegio_opcion_usuario($this->cod_item_menu, $this->cod_usuario)=='E');
		$this->dw_check_box->habilitar($temp, true);
	}	
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='create') {
			if ($habilita)
				$temp->setVar("WO_CREATE", '<input name="b_create" id="b_create" src="../../../../commonlib/trunk/images/b_create.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_over.jpg\',1)" '.
											'onClick="return selecciona_contrato();"'.
											'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../../../commonlib/trunk/images/b_create_d.jpg"/>');
				
		}else if ($boton=='print_dte') {
			if ($habilita)
				$temp->setVar("WO_PRINT_DTE", '<input name="b_print_dte" id="b_print_dte" src="../../images_appl/b_dte.jpg" type="image" '. 
												'onMouseDown="MM_swapImage(\'b_print_dte\',\'\',\'../../images_appl/b_dte_click.jpg\',1)" '.
												'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
												'onMouseOver="MM_swapImage(\'b_print_dte\',\'\',\'../../images_appl/b_dte_over.jpg\',1)" '.
												'style="display:{VISIBLE_DTE}"'.
												'onclick=" facturas_dte(); if (TabbedPanels1) this.value =TabbedPanels1.getCurrentTabIndex();
															select_printer_dte();												
															if (validate_save()) {
																document.getElementById(\'wi_hidden\').value = \'save_desde_dte\';
																document.getElementById(\'b_save\').click();
																return true;
															}else
												return false;" '.
												'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../images_appl/b_print_dte_d.jpg"/>');
				
		}
		else
			parent::habilita_boton($temp, $boton, $habilita);
	}	
  	function crear_fa_from_arriendo($lista_contratos) {
		$a_lista_contratos = explode("|",$lista_contratos);
		if($a_lista_contratos[0] == "1fac_1cont"){
			for ($i = 1 ; $i < count($a_lista_contratos) ; $i++){
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$db->BEGIN_TRANSACTION();
				$cod_usuario = $this->cod_usuario;	
				$sp = 'sp_fa_arriendo';
				$param = "'$a_lista_contratos[$i]'
							,'S'
							,$cod_usuario";
				if ($db->EXECUTE_SP($sp, $param)) { 
					$db->COMMIT_TRANSACTION();
				}
				else{ 
					$db->ROLLBACK_TRANSACTION();
					$this->_redraw();
					$this->alert("No se pudo crear la factura. Error en 'sp_fa_arriendo', favor contacte a IntegraSystem.");
				}
			}
			$this->retrieve();
		}else{
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$db->BEGIN_TRANSACTION();
			$cod_usuario = $this->cod_usuario;	
			$sp = 'sp_fa_arriendo';
			$param = "'$lista_contratos'
						,'S'
						,$cod_usuario";
			if ($db->EXECUTE_SP($sp, $param)) { 
				$db->COMMIT_TRANSACTION();
				$this->retrieve();
			}
			else { 
				$db->ROLLBACK_TRANSACTION();
				$this->_redraw();
				$this->alert("No se pudo crear la factura. Error en 'sp_fa_arriendo', favor contacte a IntegraSystem.");
			}	
		}	
	}
	function masivo_dte($lista_empresa) {
		$lista_empresa = substr($lista_empresa, 0, strlen($lista_empresa) - 1);	// borra ultimo caracter
		$lista_empresa = explode('|', $lista_empresa);
		$w = new wi_factura_arriendo_aux();
		$count_cant = 0;
		for ($i=0; $i < count($lista_empresa); $i++) {
			$cod_empresa = $lista_empresa[$i];
			
			if($cod_empresa <> ''){
				$sql = "select COD_FACTURA 
							,(SELECT COUNT(COD_EMPRESA) 
								FROM FACTURA
								WHERE NRO_FACTURA is null
								and COD_EMPRESA = $cod_empresa ) CANT_FA
						from FACTURA
						where NRO_FACTURA is null
						  AND COD_EMPRESA = $cod_empresa";	  
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$result = $db->build_results($sql);
				$count_cant = $count_cant + $result[0]['CANT_FA'];
				
				for ($j=0; $j < count($result); $j++) {
					$cod_factura = $result[$j]['COD_FACTURA'];
					$w->cod_factura = $cod_factura;
					session::set('FA_ARRIENDO', '');
					$w->envia_FA_electronica(false);
				}
			}else{
				$count = 1;
			}	
		}
		
		unset($w);
		$this->retrieve();
		if($count <> 1)
			$this->alert("Se han generado:".$count_cant. " facturas electronicas");			
		
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_fa_from_arriendo($_POST['wo_hidden']);
		else if(isset($_POST['b_print_dte_x']))
			$this->masivo_dte($_POST['wo_hidden']);
		else if($_POST['HIZO_CLICK_0'] == 'S'){
			$this->checkbox_sumar = isset($_POST['CHECK_SUMAR_0']);
			
			// obtiene los datos del filtro aplicado
			$valor_filtro = $this->headers['TOTAL_CON_IVA']->valor_filtro;
			$valor_filtro2 = $this->headers['TOTAL_CON_IVA']->valor_filtro2;
			
			if($this->checkbox_sumar){
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'S');
				$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA', 0, true, 'SUM'));
			}
			else{
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'N');
				$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA'));  
			}

			// vuelve a setear el filtro aplicado
			$this->headers['TOTAL_CON_IVA']->valor_filtro = $valor_filtro;
			$this->headers['TOTAL_CON_IVA']->valor_filtro2 = $valor_filtro2;
			
			$this->save_SESSION();	
			$this->make_filtros();
			$this->retrieve();
		}else{
			$this->checkbox_sumar = 0;
			parent::procesa_event();
		}	
	}
}
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_arriendo extends w_output_biggi {
   function wo_arriendo() {
   		$sql = "select	COD_ARRIENDO
						,convert(varchar(20), FECHA_ARRIENDO, 103) FECHA_ARRIENDO
						,FECHA_ARRIENDO DATE_ARRIENDO
						,E.RUT
						,E.DIG_VERIF
						,NOM_EMPRESA
						,REFERENCIA
						--,EA.COD_ESTADO_ARRIENDO
						,NOM_ESTADO_ARRIENDO
						,dbo.f_arr_total_actual(A.COD_ARRIENDO,getdate()) TOTAL_VIGENTE
						,dbo.f_arr_vigente(A.COD_ARRIENDO) ES_VIGENTE
						,A.NRO_ORDEN_COMPRA
						,A.CENTRO_COSTO_CLIENTE	
			from 		ARRIENDO A,
						EMPRESA E,
						USUARIO U,
						ESTADO_ARRIENDO EA
			where		A.COD_EMPRESA = E.COD_EMPRESA and 
						A.COD_USUARIO = U.COD_USUARIO and 
						A.COD_ESTADO_ARRIENDO = EA.COD_ESTADO_ARRIENDO
			order by	COD_ARRIENDO desc";

   		parent::w_output_biggi('arriendo', $sql, $_REQUEST['cod_item_menu']);
				
		$this->dw->add_control(new edit_nro_doc('COD_ARRIENDO','ARRIENDO'));
		$this->dw->add_control(new static_num('TOTAL_VIGENTE'));
      	$this->dw->add_control(new static_num('RUT'));
			
	    // headers
      	$this->add_header($control = new header_date('FECHA_ARRIENDO', 'FECHA_ARRIENDO', 'Fecha'));
      	$control->field_bd_order = 'DATE_ARRIENDO';
	    $this->add_header(new header_num('COD_ARRIENDO', 'COD_ARRIENDO', 'Cod.'));
	    $this->add_header(new header_rut('RUT', 'E', 'Rut'));
	    $sql = "SELECT 'ES VIGENTE' ES_VIG, 
				'ES VIGENTE' ES_VIGENTE
				UNION
				SELECT 'NO VIGENTE' ES_VIGENTE, 
				'NO VIGENTE' ES_VIGENTE";
	    $this->add_header(new header_drop_down_string('ES_VIGENTE', '(select dbo.f_arr_vigente(A.COD_ARRIENDO))', 'Estado Arriendo', $sql));
		
	    $this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
	    $this->add_header(new header_text('NRO_ORDEN_COMPRA', 'A.NRO_ORDEN_COMPRA', 'N� OC'));
	    $this->add_header(new header_text('CENTRO_COSTO_CLIENTE', 'A.CENTRO_COSTO_CLIENTE', 'Centro Costo'));
		$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from ARRIENDO A, USUARIO U where A.COD_USUARIO = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_drop_down('INI_USUARIO', 'U.COD_USUARIO', 'Vend', $sql));
		
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Raz�n Social'));
		/*
		$sql_estado_arr = "select COD_ESTADO_ARRIENDO ,NOM_ESTADO_ARRIENDO from ESTADO_ARRIENDO order by COD_ESTADO_ARRIENDO";
	    $this->add_header(new header_drop_down('NOM_ESTADO_ARRIENDO', 'EA.COD_ESTADO_ARRIENDO', 'Estado', $sql_estado_arr));
	    */
		$this->add_header($control = new header_num('TOTAL_VIGENTE', 'dbo.f_arr_total_actual(A.COD_ARRIENDO)', 'Total'));
		$control->field_bd_order = 'TOTAL_VIGENTE';
  	}
  	
  	function crear_arr_from_cot_arr($cod_cot_arriendo){
		
  		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_COT_ARRIENDO
				FROM COT_ARRIENDO
				WHERE COD_COT_ARRIENDO = $cod_cot_arriendo";
		$result = $db->build_results($sql);
		
		if(count($result) == 0){
			$this->_redraw();
			$this->alert('La Cotizaci�n Arriendo N� '.$cod_cot_arriendo.' no existe.');
		}else{
			session::set('CREADA_DESDE_COT_ARR', $cod_cot_arriendo);
			$this->add();
		}
  		
  	}
  	
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='create'){
			if ($habilita)
				$temp->setVar("WO_CREATE", '<input name="b_create" id="b_create" src="../../../../commonlib/trunk/images/b_create.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_over.jpg\',1)" '.
											'onClick="return dlg_display_cot_arriendo();"'.
											'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../../../commonlib/trunk/images/b_create_d.jpg"/>');
		}
		else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	function redraw(&$temp) {
  		if ($this->b_add_visible)
			$this->habilita_boton($temp, 'create', $this->get_privilegio_opcion_usuario($this->cod_item_menu, $this->cod_usuario)=='E');
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x'])) {
			$this->crear_arr_from_cot_arr($_POST['wo_hidden']);
		}
		else
			parent::procesa_event();
	}	
}
?>
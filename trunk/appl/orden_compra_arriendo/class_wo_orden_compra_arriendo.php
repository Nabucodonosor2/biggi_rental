<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_orden_compra_arriendo extends w_output_biggi {
	const K_ARRIENDO_APROBADO = 2;
	const K_AUTORIZA_SUMAR = '991535';
	var $checkbox_sumar;
	
	function wo_orden_compra_arriendo() {
		$this->checkbox_sumar = false;
		$sql = "select		O.COD_ORDEN_COMPRA                
							,convert(varchar(20), O.FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA              							                      
							,O.FECHA_ORDEN_COMPRA DATE_FECHA_ORDEN_COMPRA
							,E.NOM_EMPRESA              
							,REFERENCIA
							,U.COD_USUARIO       
							,U.NOM_USUARIO
							,EOC.COD_ESTADO_ORDEN_COMPRA	
							,EOC.NOM_ESTADO_ORDEN_COMPRA			
							,TOTAL_NETO                   
				from 		ORDEN_COMPRA O
							,EMPRESA E
							,USUARIO U
							,ESTADO_ORDEN_COMPRA EOC
				where		O.COD_EMPRESA = E.COD_EMPRESA and 
							O.COD_USUARIO = U.COD_USUARIO and
							O.COD_ESTADO_ORDEN_COMPRA = EOC.COD_ESTADO_ORDEN_COMPRA and
							TIPO_ORDEN_COMPRA = 'ARRIENDO' and
							COD_DOC IS NOT NULL	and
							COD_NOTA_VENTA IS NULL
				order by	COD_ORDEN_COMPRA desc";		
			
   		parent::w_output_biggi('orden_compra_arriendo', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_nro_doc('COD_ORDEN_COMPRA','ORDEN_COMPRA'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
		
		// headers
		$this->add_header(new header_num('COD_ORDEN_COMPRA', 'COD_ORDEN_COMPRA', 'Nº OC'));
		$this->add_header($control = new header_date('FECHA_ORDEN_COMPRA', 'FECHA_ORDEN_COMPRA', 'Fecha'));
		$control->field_bd_order = 'DATE_FECHA_ORDEN_COMPRA';
		
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Proveedor'));
		$this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
		$sql_solicitante = "select COD_USUARIO, NOM_USUARIO from USUARIO order by COD_USUARIO";
		$this->add_header(new header_drop_down('NOM_USUARIO', 'U.COD_USUARIO', 'Solicitante', $sql_solicitante));
		$sql_estado_oc = "select COD_ESTADO_ORDEN_COMPRA, NOM_ESTADO_ORDEN_COMPRA from ESTADO_ORDEN_COMPRA order by COD_ESTADO_ORDEN_COMPRA";
		$this->add_header(new header_drop_down('NOM_ESTADO_ORDEN_COMPRA', 'EOC.COD_ESTADO_ORDEN_COMPRA', 'Estado', $sql_estado_oc));
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));

		// dw checkbox
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SUMAR, $this->cod_usuario);
		if ($priv=='E') {
			$DISPLAY_SUMAR = '';
      	}
      	else {
			$DISPLAY_SUMAR = 'none';
      	}
		
		$sql = "select '$DISPLAY_SUMAR' DISPLAY_SUMAR
						,'N' CHECK_SUMAR
					   ,'N' HIZO_CLICK";
		$this->dw_check_box = new datawindow($sql);
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_SUMAR','S','N'));
		$control->set_onClick("sumar(); document.getElementById('loader').style.display='';");
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();
	}
	function redraw(&$temp){
		parent::redraw(&$temp);
		$this->dw_check_box->habilitar($temp, true);
	}
	function crear_desde_arriendo($cod_arriendo) {
		// Cuando se compra para la bodega de RENTAL $cod_arriendo == 0
		if ($cod_arriendo!=0) {
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "select COD_ESTADO_ARRIENDO
					from ARRIENDO
					where COD_ARRIENDO = $cod_arriendo";
			$result = $db->build_results($sql);
			if (count($result)==0) {
				$this->_redraw();
				$this->alert("Contrato de arriendo no existe, contrato Nº: $cod_arriendo");
				return;
			}
			$cod_estado_arriendo = $result[0]['COD_ESTADO_ARRIENDO'];
			if ($cod_estado_arriendo !=self:: K_ARRIENDO_APROBADO) {
				$this->_redraw();
	 			$this->alert("El arriendo $cod_arriendo, no esta aprobado.");
				return;
			}
		}
	    session::set('ORDEN_COMPRA.CREAR_DESDE_ARRIENDO', $cod_arriendo);
		$this->add();
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_desde_arriendo($_POST['wo_hidden']);
		else if ($_POST['HIZO_CLICK_0'] == 'S'){
			$this->checkbox_sumar = isset($_POST['CHECK_SUMAR_0']);
			
			// obtiene los datos del filtro aplicado
			$valor_filtro = $this->headers['TOTAL_NETO']->valor_filtro;
			$valor_filtro2 = $this->headers['TOTAL_NETO']->valor_filtro2;
			
			if ($this->checkbox_sumar) {
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'S');
				$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto', 0, true, 'SUM'));
			}
			else{
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'N');
				$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));  
			}

			// vuelve a setear el friltro aplicado
			$this->headers['TOTAL_NETO']->valor_filtro = $valor_filtro;
			$this->headers['TOTAL_NETO']->valor_filtro2 = $valor_filtro2;
			
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
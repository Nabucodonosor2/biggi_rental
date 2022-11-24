<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_guia_recepcion_arriendo extends w_output_biggi {
	const K_ARRIENDO_APROBADO	= 2;
	
	function wo_guia_recepcion_arriendo() {
		$sql = "SELECT	GR.COD_GUIA_RECEPCION
						,convert(varchar(20),GR.FECHA_GUIA_RECEPCION, 103)FECHA_GUIA_RECEPCION
						,GR.FECHA_GUIA_RECEPCION DATE_GUIA_RECEPCION
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,EGR.COD_ESTADO_GUIA_RECEPCION
						,EGR.NOM_ESTADO_GUIA_RECEPCION
						,TGR.COD_TIPO_GUIA_RECEPCION
						,TGR.NOM_TIPO_GUIA_RECEPCION
						,(select COD_ARRIENDO from MOD_ARRIENDO where COD_MOD_ARRIENDO = GR.COD_DOC) COD_ARRIENDO
				FROM	GUIA_RECEPCION GR, EMPRESA E, ESTADO_GUIA_RECEPCION EGR, TIPO_GUIA_RECEPCION TGR
				WHERE	GR.COD_EMPRESA = E.COD_EMPRESA AND
						isnull(GR.TIPO_DOC,'') = 'MOD_ARRIENDO' AND
						EGR.COD_ESTADO_GUIA_RECEPCION = GR.COD_ESTADO_GUIA_RECEPCION AND
						TGR.COD_TIPO_GUIA_RECEPCION = GR.COD_TIPO_GUIA_RECEPCION
						order by COD_GUIA_RECEPCION desc";		
	
   		parent::w_output_biggi('guia_recepcion_arriendo', $sql, $_REQUEST['cod_item_menu']);

		$this->dw->add_control(new edit_precio('MONTO_DOCUMENTO'));
		$this->dw->add_control(new static_num('RUT'));
		
		// headers
		$this->add_header(new header_num('COD_GUIA_RECEPCION', 'COD_GUIA_RECEPCION', 'Código'));
		$this->add_header($control = new header_date('FECHA_GUIA_RECEPCION', 'convert(varchar(20), FECHA_GUIA_RECEPCION, 103)', 'Fecha'));
		$control->field_bd_order = 'DATE_GUIA_RECEPCION';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Proveedor'));
		$sql_estado_gr = "select COD_ESTADO_GUIA_RECEPCION, NOM_ESTADO_GUIA_RECEPCION from ESTADO_GUIA_RECEPCION order by COD_ESTADO_GUIA_RECEPCION";
		$this->add_header(new header_drop_down('NOM_ESTADO_GUIA_RECEPCION', 'EGR.COD_ESTADO_GUIA_RECEPCION', 'Estado', $sql_estado_gr));
		$sql_tipo_gr = "select COD_TIPO_GUIA_RECEPCION, NOM_TIPO_GUIA_RECEPCION from TIPO_GUIA_RECEPCION order by COD_TIPO_GUIA_RECEPCION";
		$this->add_header(new header_drop_down('NOM_TIPO_GUIA_RECEPCION', 'TGR.COD_TIPO_GUIA_RECEPCION', 'Tipo Doc.', $sql_tipo_gr));
		$this->add_header(new header_num('COD_ARRIENDO', '(select COD_ARRIENDO from MOD_ARRIENDO where COD_MOD_ARRIENDO = GR.COD_DOC)', 'Nº Contrato'));
	}
	
	function crear_gr_from_arriendo($cod_mod_arriendo) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		if($cod_mod_arriendo == '' || $cod_mod_arriendo== 'null'){
			$this->_redraw();
			return false;
		}
		
		$db->BEGIN_TRANSACTION();
		$cod_usuario = $this->cod_usuario;			
		$sp = 'sp_gr_crear_desde_arriendo';
		$param = "$cod_mod_arriendo, $cod_usuario";
		
		if ($db->EXECUTE_SP($sp, $param)){ 
			$db->COMMIT_TRANSACTION();
			$this->detalle_record_desde(true);
		}
		else{ 
			$db->ROLLBACK_TRANSACTION();	
			$this->_redraw();
			$this->alert("No se pudo crear la guía de recepción. Error en 'sp_gr_crear_desde_arriendo', favor contacte a IntegraSystem.");
		}			
	}
	function habilita_boton(&$temp, $boton, $habilita) {
		parent::habilita_boton($temp, $boton, $habilita);
		if ($boton=='create') {
			if ($habilita){
				$temp->setVar("WO_CREATE", '<input name="b_create" id="b_create" src="../../../../commonlib/trunk/images/b_create.jpg" type="image" '.
														'onMouseDown="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_click.jpg\',1)" '.
														'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
														'onMouseOver="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_over.jpg\',1)" '.
														'onClick="request_arriendo(); return true;" '.
														'/>');
			}else{
				$temp->setVar("WO_CREATE", '<img src="../../../../commonlib/trunk/images/b_create_d.jpg"/>');
			}
		}
	}
	function redraw(&$temp) {
		$this->habilita_boton($temp, 'create', $this->get_privilegio_opcion_usuario($this->cod_item_menu, $this->cod_usuario)=='E');
	}
	function procesa_event() {
	
		if(isset($_POST['b_create_x']))
			$this->crear_gr_from_arriendo($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>
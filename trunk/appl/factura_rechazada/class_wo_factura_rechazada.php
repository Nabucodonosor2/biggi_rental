<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");

class wi_factura_aux extends wi_factura  {
	var $cod_factura;
	
	function wi_factura_aux() {
		parent::wi_factura('1535');
	}
	function get_key() {
		return $this->cod_factura;
	}
	function _load_record() {
		return;
	}
}

class wo_factura_rechazada extends w_output_biggi{
	var $autoriza_print;
   	function wo_factura_rechazada(){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->query("exec spx_resuelve_fa_rechazadas");

		$sql = "SELECT COD_FACTURA_RECHAZADA
					  ,F.COD_FACTURA
					  ,NRO_FACTURA
					  ,CONVERT(VARCHAR, FECHA_RECHAZO, 103) FECHA_RECHAZO
					  ,FECHA_RECHAZO DATE_FECHA_RECHAZO
					  ,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
					  ,F.FECHA_FACTURA DATE_FACTURA
					  ,RESUELTA
					  ,COD_USUARIO_RESUELTA
					  ,F.RUT
					  ,F.DIG_VERIF
					  ,F.NOM_EMPRESA
					  ,UV1.INI_USUARIO
					  ,F.COD_USUARIO_VENDEDOR1
					  ,F.TOTAL_CON_IVA
					  ,dbo.f_origen_arriendo1(F.COD_FACTURA,'ARRIENDOS_X_FACTURA2') COD_ARRIENDO
					  ,dbo.f_get_nc_from_fa(F.COD_FACTURA) NRO_NOTA_CREDITO
					  ,COD_ESTADO_DOC_SII
					  ,NRO_RE_FACTURA
				FROM FACTURA_RECHAZADA FR LEFT OUTER JOIN USUARIO U ON U.COD_USUARIO = FR.COD_USUARIO_RESUELTA
					,FACTURA F
					,USUARIO UV1
				WHERE FR.COD_FACTURA = F.COD_FACTURA
				AND UV1.COD_USUARIO = F.COD_USUARIO_VENDEDOR1
				ORDER BY COD_FACTURA_RECHAZADA DESC";		
	
   		parent::w_output_biggi('factura_rechazada', $sql, $_REQUEST['cod_item_menu']);
		
   		$this->add_header(new header_num('COD_FACTURA_RECHAZADA', 'COD_FACTURA_RECHAZADA', 'Código'));
   		$this->add_header(new header_num('NRO_FACTURA', 'NRO_FACTURA', 'N° Factura'));
   		$this->add_header($control = new header_date('FECHA_RECHAZO', 'FECHA_RECHAZO', 'Fecha Rechazo'));
		$control->field_bd_order = 'DATE_FECHA_RECHAZO';
		$this->add_header($control = new header_date('FECHA_FACTURA', 'FECHA_FACTURA', 'Fecha Factura'));
		$control->field_bd_order = 'DATE_FACTURA';
		$this->add_header(new header_rut('RUT', 'F', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
		$this->add_header(new header_vendedor('INI_USUARIO', 'F.COD_USUARIO_VENDEDOR1', 'V1'));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total con IVA'));
		$this->add_header($control = new header_text('COD_ARRIENDO', "dbo.f_origen_arriendo1(F.COD_FACTURA,'ARRIENDOS_X_FACTURA2')", 'N° Arr'));
		$control->field_bd_order = 'COD_ARRIENDO';
		$this->add_header($control = new header_num('NRO_NOTA_CREDITO', 'dbo.f_get_nc_from_fa(F.COD_FACTURA)', 'NC'));
		$control->field_bd_order = 'NRO_NOTA_CREDITO';
		$this->add_header(new header_num('NRO_RE_FACTURA', "NRO_RE_FACTURA", 'FA'));

		$sql_s_n = "select 'S' RESUELTA,
							'Si' NOM_RESUELTA
					UNION 
					select 'N' RESUELTA,
						   'No' NOM_RESUELTA";
		$this->add_header($header = new header_drop_down_string('RESUELTA', 'RESUELTA', 'Resuelta',$sql_s_n));
   		$sql = "SELECT COD_USUARIO COD_USUARIO_RESUELTA
   					  ,NOM_USUARIO
   				FROM USUARIO";
		$this->add_header(new header_drop_down('NOM_USUARIO', 'COD_USUARIO_RESUELTA', 'Responsable', $sql));

		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new static_num('TOTAL_CON_IVA'));

		$priv = $this->get_privilegio_opcion_usuario('992075', $this->cod_usuario); //print
		if($priv=='E')
			$this->autoriza_print = true;
      	else
			$this->autoriza_print = false;

		$header->valor_filtro = 'N';
		$this->make_filtros();
	}

	function make_menu(&$temp){
		$menu = session::get('menu_appl');
		$menu->ancho_completa_menu = 367;
		$menu->draw($temp);
		$menu->ancho_completa_menu = 217;
	}

	function redraw_item(&$temp, $ind, $record){
		parent::redraw_item($temp, $ind, $record);
		$COD_FACTURA		= $this->dw->get_item($record, 'COD_FACTURA');
		$COD_ESTADO_DOC_SII = $this->dw->get_item($record, 'COD_ESTADO_DOC_SII');

		if($COD_ESTADO_DOC_SII == 2 && $this->autoriza_print == true){
			$control =  '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" type="button" title="Imprimir"'.
			   			'style="cursor:pointer;height:26px;width:17px;border: 0;background-image:url(../../images_appl/b_dte_print.png);background-repeat:no-repeat;background-position:center;"'.
			   			'onClick="return dlg_print_dte_wo(this, '.$COD_FACTURA.');"/>';

		}else if($COD_ESTADO_DOC_SII == 3 && $this->autoriza_print == true){
			$control =  '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" type="button" title="Imprimir"'.
			   			'style="cursor:pointer;height:26px;width:17px;border: 0;background-image:url(../../images_appl/b_dte_print.png);background-repeat:no-repeat;background-position:center;"'.
			   			'onClick="return dlg_print_dte_wo(this, '.$COD_FACTURA.');"/>';
		
		}else
			$control = '<img src="../../images_appl/b_dte_print_d.png">';

		$temp->setVar("wo_registro.WO_PRINT_DTE", $control);
	}

	function procesa_event(){
		if($this->clicked_boton('b_printDTE', $value_boton))
			$this->printdte();
		else 
			parent::procesa_event();
	}

	function printdte(){
		$COD_FACTURA = $_POST['wo_hidden'];
		$es_cedible = $_POST['wo_hidden2'];
		$w = new wi_factura_aux();
		$w->cod_factura = $COD_FACTURA;
		$w->imprimir_dte($es_cedible);
		$this->goto_page($this->current_page);
  	}
}
?>
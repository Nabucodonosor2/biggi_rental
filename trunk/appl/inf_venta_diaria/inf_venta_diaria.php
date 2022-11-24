<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (isset($_POST['b_ok'])) {
	$fecha = $_POST['FECHA_0'];
	$no_incluye_relacionado = $_POST['NO_INCLUYE_RELACIONADO_0'];
	if ($no_incluye_relacionado=='')
		$no_incluye_relacionado = 'N';
	
	session::set("inf_venta_diaria.FECHA", $fecha);
	session::set("inf_venta_diaria.NO_INCLUYE_RELACIONADO", $no_incluye_relacionado);
	
	$url = "../../../../commonlib/trunk/php/mantenedor.php?modulo=inf_venta_diaria&cod_item_menu=4094";
	header ('Location:'.$url);
}
else if (isset($_POST['b_cancel'])) {
	base::presentacion();
}
else {
	$temp = new Template_appl('inf_venta_diaria.htm');	
	
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$sql = "select convert(varchar, getdate(), 103) FECHA
				, 'S' NO_INCLUYE_RELACIONADO";
	$dw_param = new datawindow($sql);
	$dw_param->add_control(new edit_date('FECHA'));
	$dw_param->add_control(new edit_check_box('NO_INCLUYE_RELACIONADO', 'S', 'N'));
	
	// draw
	$dw_param->retrieve();
	$dw_param->habilitar($temp, true);
	
	print $temp->toString();
}
?>
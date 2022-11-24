<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (isset($_POST['b_ok'])) {
	$ano1 = $_POST['ANO1_0'];
	$ano2 = $_POST['ANO2_0'];
	$mes_desde = $_POST['MES_DESDE_0'];
	$mes_hasta = $_POST['MES_HASTA_0'];
	
	session::set("inf_resumen_venta.ANO1", $ano1);
	session::set("inf_resumen_venta.ANO2", $ano2);
	session::set("inf_resumen_venta.MES_DESDE", $mes_desde);
	session::set("inf_resumen_venta.MES_HASTA", $mes_hasta);
	
	$url = "../../../../commonlib/trunk/php/mantenedor.php?modulo=inf_resumen_venta&cod_item_menu=4092";
	header ('Location:'.$url);
}
else if (isset($_POST['b_cancel'])) {
	base::presentacion();
}
else {
	$temp = new Template_appl('inf_resumen_venta.htm');	
	
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$sql = "select year(getdate())-1 ANO1
				, year(getdate()) ANO2
				, 1 MES_DESDE
				, month(getdate()) MES_HASTA";
	$dw_param = new datawindow($sql);
	$dw_param->add_control(new edit_ano('ANO1'));
	$dw_param->add_control(new edit_ano('ANO2'));
	$dw_param->add_control(new edit_mes('MES_DESDE'));
	$dw_param->add_control(new edit_mes('MES_HASTA'));
	
	// draw
	$dw_param->retrieve();
	$dw_param->habilitar($temp, true);
	
	print $temp->toString();
}
?>
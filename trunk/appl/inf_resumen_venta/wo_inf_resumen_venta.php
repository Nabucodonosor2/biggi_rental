<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('inf_resumen_venta')) {
	$wo = new wo_inf_resumen_venta();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_inf_resumen_venta');
	$wo->procesa_event();
}
?>
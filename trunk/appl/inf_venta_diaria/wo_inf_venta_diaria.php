<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('inf_venta_diaria')) {
	$wo = new wo_inf_venta_diaria();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_inf_venta_diaria');
	$wo->procesa_event();
}
?>
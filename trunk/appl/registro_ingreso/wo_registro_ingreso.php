<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_output::f_viene_del_menu('registro_ingreso')) {
	$wo_registro_ingreso = new wo_registro_ingreso();
	$wo_registro_ingreso->retrieve();
}
else {
	$wo = session::get('wo_registro_ingreso');
	$wo->procesa_event();
	
}
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('proveedor_ext')) {
	$wo_proveedor_ext = new wo_proveedor_ext();
	$wo_proveedor_ext->retrieve();
}
else {
	$wo = session::get('wo_proveedor_ext');
	$wo->procesa_event();
	
}
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('dolar')) {
	$wo_dolar = new wo_dolar();
	$wo_dolar->retrieve();
}
else {
	$wo = session::get('wo_dolar');
	$wo->procesa_event();	
}
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if(w_output::f_viene_del_menu('orden_compra_interna')){
	$wo = new wo_orden_compra_interna();
  	$wo->retrieve();
}else{
	$wo = session::get('wo_orden_compra_interna');
	$wo->procesa_event();
}
?>
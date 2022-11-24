<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if(w_output::f_viene_del_menu('cheque')){
	$wo_cheque = new wo_cheque();
	$wo_cheque->retrieve();
}else{	
	$wo = session::get('wo_cheque');
	$wo->procesa_event();
}
?>
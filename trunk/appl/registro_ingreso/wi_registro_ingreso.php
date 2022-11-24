<?php
//cambio
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_input::f_viene_del_output('registro_ingreso')) {
	$wi = new wi_registro_ingreso($_REQUEST['cod_item_menu']);
	$rec_no = $_REQUEST['rec_no'];
	$wi->goto_record($rec_no);
}
else {
	$wi = session::get('wi_registro_ingreso');
	$wi->procesa_event();
}
?>
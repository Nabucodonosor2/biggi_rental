<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if(w_input::f_viene_del_output('orden_compra_interna')) {
	$wi = new wi_orden_compra_interna($_REQUEST['cod_item_menu']);
	$rec_no = $_REQUEST['rec_no'];
	$wi->goto_record($rec_no);
}else{
	$wi = session::get('wi_orden_compra_interna');
	$wi->procesa_event();
}
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (!session::is_set('wi_inf_tdnx_inventario_valorizado')) {
	$wi = new wi_inf_tdnx_inventario_valorizado($_REQUEST['cod_item_menu']);
	$wi->_load_record();
}
else {
	$wi = session::get('wi_inf_tdnx_inventario_valorizado');
	$wi->procesa_event();
}
?>
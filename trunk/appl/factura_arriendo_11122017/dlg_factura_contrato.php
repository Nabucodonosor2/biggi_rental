<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('dlg_factura_contrato.htm');	

$sql = "SELECT NULL COD_ARRIENDO";

$dw = new datawindow($sql);
$dw->add_control($control = new edit_num('COD_ARRIENDO'));
$dw->retrieve();
$dw->habilitar($temp, true);
print $temp->toString();
?>
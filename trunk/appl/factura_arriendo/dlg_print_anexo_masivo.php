<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('dlg_print_anexo_masivo.htm');	

$sql = "SELECT CONVERT(VARCHAR, GETDATE(), 103) FECHA_DESDE
			  ,CONVERT(VARCHAR, GETDATE(), 103) FECHA_HASTA";

$dw = new datawindow($sql);
$dw->add_control(new edit_date('FECHA_DESDE'));
$dw->add_control(new edit_date('FECHA_HASTA'));
$dw->retrieve();
$dw->habilitar($temp, true);
print $temp->toString();
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('dlg_print_anexo.htm');	

$sql = "select 'S' PDF
				,'N' XLS";
$dw = new datawindow($sql);
$dw->add_control($control = new edit_radio_button('PDF', 'S', 'N', 'Pdf', 'RADIO'));
$dw->add_control($control = new edit_radio_button('XLS', 'S', 'N', 'Excel', 'RADIO'));
$dw->retrieve();
$dw->habilitar($temp, true);

print $temp->toString();
?>
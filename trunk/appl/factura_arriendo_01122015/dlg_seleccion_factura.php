<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('dlg_seleccion_factura.htm');	

$sql = "SELECT 'S' SODEXO_CHILE
			  ,'N' SODEXO_SERV";
$dw = new datawindow($sql);
$dw->add_control(new edit_radio_button('SODEXO_CHILE', 'S', 'N', 'SODEXO CHILE S.A.', 'RADIO'));
$dw->add_control(new edit_radio_button('SODEXO_SERV', 'S', 'N', 'SODEXO SERVICIOS S.A', 'RADIO'));
$dw->retrieve();
$dw->habilitar($temp, true);

print $temp->toString();
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('dlg_print_dte_masivo.htm');	
$ve_cant_fact_tot = $_REQUEST['ve_cant_fact_tot'];

$sql = "SELECT 1 CANT_COPIAS
			 ,$ve_cant_fact_tot CANT_TOT_FAC";
			 
$dw = new datawindow($sql);
$dw->add_control(new edit_num('CANT_COPIAS', 10, 10));
$dw->add_control(new static_num('CANT_TOT_FAC'));
$dw->retrieve();
$dw->habilitar($temp, true);

print $temp->toString();
?>
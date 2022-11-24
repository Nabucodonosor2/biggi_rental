<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../appl.ini");

$temp = new Template_appl('dlg_display_cot_arr.htm');
$sql ="SELECT NULL COD_COT_ARRIENDO";
	  
$dw = new datawindow($sql);
$dw->add_control(new edit_num('COD_COT_ARRIENDO',15,8));
$dw->retrieve();
	
$dw->habilitar($temp, true);
print $temp->toString();
?>
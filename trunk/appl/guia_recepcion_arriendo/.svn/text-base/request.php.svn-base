<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");
$prompt = $_REQUEST['prompt'];
$valor =  $_REQUEST['valor'];
$temp = new Template_appl('request.htm');	
$temp->setVar("PROMPT", 'Ingrese Nº de Contrato arriendo');

$edit_num = new edit_num('VALOR', 6, 6);
//$edit_num->set_onChange('gd_pendiente();');
$temp->setVar("VALOR", $edit_num->draw_entrable($valor, 0));

// datawindow mod arriendo 
$sql ="SELECT NULL SELECCION ,
			  NULL COD_MOD_ARRIENDO,
			  NULL REFERENCIA";
$dw = new datawindow($sql,'MOD_ARRIENDO');
$dw->add_control(new edit_radio_button('SELECCION', 'S', 'N','',''));
$dw->add_control(new static_text('COD_MOD_ARRIENDO'));
$dw->add_control(new static_text('REFERENCIA'));

$dw->retrieve();
$dw->habilitar($temp, true);
print $temp->toString();
?>
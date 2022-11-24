<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../appl.ini");

	$temp = new Template_appl('dlg_input_contrato.htm');
			$sql ="SELECT  NULL CONTRATO
				   		  ,'S' AGREGA
				   		  ,NULL ELIMINA";
	  
		$dw = new datawindow($sql);
		
		$dw->add_control(new edit_num('CONTRATO',15,8));
		$dw->add_control(new edit_radio_button('AGREGA', 'S', 'N','','AGREGAR_ELIMINA'));
		$dw->add_control(new edit_radio_button('ELIMINA', 'S', 'N','','AGREGAR_ELIMINA'));
		$dw->retrieve();
	
	$dw->habilitar($temp, true);
	print $temp->toString();
?>
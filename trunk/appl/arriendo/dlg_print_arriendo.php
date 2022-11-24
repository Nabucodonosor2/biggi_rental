<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_arriendo = $_REQUEST['cod_arriendo'];
$temp = new Template_appl('dlg_print_arriendo.htm');	
$temp->setVar("COD_ARRIENDO", $cod_arriendo);

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT COD_MOD_ARRIENDO
			  ,FECHA_MOD_ARRIENDO
	  		  ,REFERENCIA 
		FROM MOD_ARRIENDO
		WHERE COD_ARRIENDO = $cod_arriendo
		AND TIPO_MOD_ARRIENDO = 'AGREGAR'
		ORDER BY COD_MOD_ARRIENDO DESC";
		
$result = $db->build_results($sql);
for ($j=0; $j < count($result); $j++) {
	$temp->gotoNext("MOD_ARRIENDO");
	if ($j%2==0)
		$temp->setVar("MOD_ARRIENDO.DW_TR_CSS", datawindow::css_claro);
	else
		$temp->setVar("MOD_ARRIENDO.DW_TR_CSS", datawindow::css_oscuro);

	$temp->setVar("MOD_ARRIENDO.DW_TR_ID", 'MOD_ARRIENDO_'.$j);
	$temp->setVar("MOD_ARRIENDO.R_MOD_ARRIENDO", 'R_MOD_ARRIENDO_'.$j);
	$temp->setVar("MOD_ARRIENDO.ID_MOD_ARRIENDO", 'ID_MOD_ARRIENDO_'.$j);			
	$temp->setVar("MOD_ARRIENDO.COD_MOD_ARRIENDO", $result[$j]['COD_MOD_ARRIENDO']);
	$temp->setVar("MOD_ARRIENDO.FECHA_MOD_ARRIENDO", $result[$j]['FECHA_MOD_ARRIENDO']);
	$temp->setVar("MOD_ARRIENDO.REFERENCIA", $result[$j]['REFERENCIA']);	
}
if(count($result) == 0){
	$temp->setVar("DISPLAY_LABEL", 'none');
}else{
	$temp->setVar("DISPLAY_LABEL", '');
}

$sql = "exec spi_anexo_arriendo $cod_arriendo";
$result = $db->build_results($sql);
if(count($result) == 0){
	$temp->setVar("DISPLAY_ANEXO_DOS", 'none');
	$temp->setVar("DISPLAY_ANEXO", 'none');
}else{
	$temp->setVar("DISPLAY_ANEXO_DOS", '');
	$temp->setVar("DISPLAY_ANEXO", '');
}

print $temp->toString();
?>
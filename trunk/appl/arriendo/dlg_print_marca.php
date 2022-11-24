<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_arriendo = $_REQUEST['cod_arriendo'];
$cod_mod_arriendo = $_REQUEST['cod_mod_arriendo'];
$temp = new Template_appl('dlg_print_marca.htm');	
$temp->setVar("COD_ARRIENDO", $cod_arriendo);
$temp->setVar("COD_MOD_ARRIENDO", $cod_mod_arriendo);
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql="SELECT COD_ITEM_MOD_ARRIENDO
			,COD_MOD_ARRIENDO
		    ,ITEM
		    ,COD_PRODUCTO
		    ,NOM_PRODUCTO
		    ,CANTIDAD
		    ,0 CANTIDAD_MARCA
	  FROM ITEM_MOD_ARRIENDO
	  WHERE COD_MOD_ARRIENDO =".$cod_mod_arriendo;
$result = $db->build_results($sql);

for($j=0; $j < count($result); $j++){
	$temp->gotoNext("ITEM_MOD_ARRIENDO");
	if ($j%2==0)
		$temp->setVar("ITEM_MOD_ARRIENDO.DW_TR_CSS", datawindow::css_claro);
	else
		$temp->setVar("ITEM_MOD_ARRIENDO.DW_TR_CSS", datawindow::css_oscuro);

	$temp->setVar("ITEM_MOD_ARRIENDO.DW_TR_ID", 'ITEM_MOD_ARRIENDO_'.$j);	
	$temp->setVar("ITEM_MOD_ARRIENDO.COD_ITEM_MOD_ARRIENDO", $result[$j]['COD_ITEM_MOD_ARRIENDO']);
	$temp->setVar("ITEM_MOD_ARRIENDO.COD_MOD_ARRIENDO", $result[$j]['COD_MOD_ARRIENDO']);
	$temp->setVar("ITEM_MOD_ARRIENDO.ITEM", $result[$j]['ITEM']);
	$temp->setVar("ITEM_MOD_ARRIENDO.COD_PRODUCTO", $result[$j]['COD_PRODUCTO']);
	$temp->setVar("ITEM_MOD_ARRIENDO.NOM_PRODUCTO", $result[$j]['NOM_PRODUCTO']);
	$temp->setVar("ITEM_MOD_ARRIENDO.CANTIDAD", $result[$j]['CANTIDAD']);	
	$temp->setVar("ITEM_MOD_ARRIENDO.CANTIDAD_ARRIENDO",'CANTIDAD_ARRIENDO_'.$j);
	$temp->setVar("ITEM_MOD_ARRIENDO.CANTIDAD_MARCA",'CANTIDAD_MARCA_'.$j);
	$temp->setVar("ITEM_MOD_ARRIENDO.ITEM_ARRIENDO",'ITEM_ARRIENDO_'.$j);	
	$temp->setVar("ITEM_MOD_ARRIENDO.NRO_INICIO",$j);
	
}		

print $temp->toString();
?>
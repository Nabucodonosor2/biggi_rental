<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$fecha		= $_REQUEST['fecha'];

if($fecha == '')
	$fecha = 'getdate()';
else
	$fecha = "dbo.to_date('$fecha')";
	
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select DATEDIFF(dd,$fecha,getdate()) RESPUESTA";

$res_fecha = $db->build_results($sql);
$dias = $res_fecha[0]['RESPUESTA'];

if($dias>0)
	$result = 'menor';	
else 
	$result = 'good';	

print urlencode(json_encode($result));
?>
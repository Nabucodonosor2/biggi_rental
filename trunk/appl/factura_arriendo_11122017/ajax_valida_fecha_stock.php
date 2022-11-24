<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

function str2date($fecha_str, $hora_str='00:00:00') {
	if ($fecha_str=='')
		return 'null';
	// Entra la fecha en formato dd/mm/yyyy		
	$res = explode('/', $fecha_str);
	if (strlen($res[2])==2)
		$res[2] = '20'.$res[2];
	return sprintf("{ts '$res[2]-$res[1]-$res[0] $hora_str.000'}");
}

$fecha = $_REQUEST['fecha'];
$fecha = str2date($fecha);

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql_fecha = "select ISDATE($fecha) ES_FECHA";	
$result_fecha = $db->build_results($sql_fecha);

if($result_fecha[0]['ES_FECHA'] == 0){
	print urlencode('NO_FECHA');

}else{
	$sql = "SELECT CONVERT(VARCHAR, GETDATE(), 103) FECHA_ACTUAL
				  ,CASE
				  	WHEN $fecha > dbo.f_makedate(day(getdate()), month(getdate()), year(getdate())) 
				  		THEN 'MAYOR'
				  	ELSE 'MENOR'
				  END VALIDACION";			
	$result = $db->build_results($sql);
	
	print urlencode($result[0]['VALIDACION']);
}
?>
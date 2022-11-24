<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
	$fecha = $_REQUEST['fecha'];

	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	$sql = "SELECT CASE
					WHEN '$fecha' =  CONVERT(VARCHAR, GETDATE(), 103)
						THEN 'ES_IGUAL'
					ELSE 'NO_ES_IGUAL'
					END VALIDACION";
	$result = $db->build_results($sql);

	print urlencode($result[0]['VALIDACION']);
?>
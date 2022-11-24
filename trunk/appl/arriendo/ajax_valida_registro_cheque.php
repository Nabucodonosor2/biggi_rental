<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_arriendo = $_REQUEST['cod_arriendo'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT COUNT(*) TOTAL 
		FROM ARRIENDO A
			,INGRESO_ARRIENDO IA
			,INGRESO_CHEQUE IC
			,CHEQUE CH
		WHERE A.COD_ARRIENDO = $cod_arriendo
		AND IC.COD_ESTADO_INGRESO_CHEQUE = 2
		AND A.COD_ARRIENDO = IA.COD_ARRIENDO
		AND IA.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE
		AND IC.COD_INGRESO_CHEQUE = CH.COD_INGRESO_CHEQUE";

$result = $db->build_results($sql);
$total = $result[0]['TOTAL'];

print urlencode($total);
?>
<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_cheque = $_REQUEST['cod_cheque'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT COD_CHEQUE
			  ,COD_TIPO_DOC_PAGO
			  ,CONVERT(VARCHAR, FECHA_DOC, 103) FECHA_DOC
			  ,NRO_DOC
			  ,COD_BANCO
			  ,dbo.f_ch_saldo(COD_CHEQUE) MONTO_DOC
		FROM CHEQUE
		WHERE COD_CHEQUE in ($cod_cheque)
		ORDER BY COD_CHEQUE"; 

$result = $db->build_results($sql);
print urlencode(json_encode($result));
?>
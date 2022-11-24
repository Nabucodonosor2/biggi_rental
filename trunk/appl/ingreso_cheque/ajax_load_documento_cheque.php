<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$fecha_1er_cheque	= $_REQUEST['fecha_1er_cheque'];
$meses				= $_REQUEST['meses'];

if($fecha_1er_cheque == '')
	$fecha_1er_cheque = 'getdate()';
else
	$fecha_1er_cheque = "dbo.to_date('$fecha_1er_cheque')";
	
$result = array();
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
for ($i=0; $i < $meses; $i++) {
	$sql = "select convert(varchar(20), dateadd(month, $i, $fecha_1er_cheque), 103) FECHA_DOCUMENTO_PAGO";
	$res_fecha = $db->build_results($sql);
	$result[$i]['FECHA_DOCUMENTO_PAGO'] = $res_fecha[0]['FECHA_DOCUMENTO_PAGO'];
}

print urlencode(json_encode($result));
?>
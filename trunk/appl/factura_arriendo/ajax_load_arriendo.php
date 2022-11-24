<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

function str2date($fecha_str, $hora_str='00:00:00') {
	if ($fecha_str=='')
		return 'null';
		// Entra la fecha en formato dd/mm/yyyy
	$res = explode('/', $fecha_str);
	if (strlen($res[2])==2)
		$res[2] = '20'.$res[2];
	return sprintf("{ts '$res[2]-$res[1]-$res[0] $hora_str.997'}");
}

$cod_empresa = $_REQUEST['cod_empresa'];
$fecha_stock = $_REQUEST['fecha_stock'];
$cathering = $_REQUEST['cathering'];

$fecha_stock = str2date($fecha_stock, '23:59:59');

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
if($cathering == 'S'){
    $sql = "select 'N' SELECCCION
				,A.COD_ARRIENDO
				,A.NOM_ARRIENDO
				,A.REFERENCIA
				,dbo.f_arr_total_actual(a.COD_ARRIENDO,$fecha_stock) TOTAL
				,A.EXIGE_CHEQUE
				,CONVERT(VARCHAR, CH.FECHA_LIBERADO, 103) FECHA_LIBERADO
				,ISNULL(dbo.f_ch_saldo_por_usar(CH.COD_CHEQUE), 0) MONTO_POR_USAR
		FROM ARRIENDO A LEFT OUTER JOIN CHEQUE CH ON CH.COD_CHEQUE = ISNULL(dbo.f_arr_1er_cheque(A.COD_ARRIENDO), 0)
		WHERE A.COD_EMPRESA = $cod_empresa
		AND dbo.f_arr_total_actual(A.COD_ARRIENDO,$fecha_stock) > 0
		AND dbo.f_arr_esta_facturado(A.COD_ARRIENDO, GETDATE()) = 0
		AND A.COD_ARRIENDO  <> dbo.f_arriendo_no_vigentes( A.COD_ARRIENDO)
        AND ORIGEN_ARRIENDO = 'C'";
}else{
    $sql = "select 'N' SELECCCION
				,A.COD_ARRIENDO
				,A.NOM_ARRIENDO
				,A.REFERENCIA
				,dbo.f_arr_total_actual(a.COD_ARRIENDO,$fecha_stock) TOTAL
				,A.EXIGE_CHEQUE
				,CONVERT(VARCHAR, CH.FECHA_LIBERADO, 103) FECHA_LIBERADO
				,ISNULL(dbo.f_ch_saldo_por_usar(CH.COD_CHEQUE), 0) MONTO_POR_USAR
		FROM ARRIENDO A LEFT OUTER JOIN CHEQUE CH ON CH.COD_CHEQUE = ISNULL(dbo.f_arr_1er_cheque(A.COD_ARRIENDO), 0)
		WHERE A.COD_EMPRESA = $cod_empresa
		AND dbo.f_arr_total_actual(A.COD_ARRIENDO,$fecha_stock) > 0
		AND dbo.f_arr_esta_facturado(A.COD_ARRIENDO, GETDATE()) = 0
		AND A.COD_ARRIENDO  <> dbo.f_arriendo_no_vigentes( A.COD_ARRIENDO)
        AND ORIGEN_ARRIENDO is null";
}

$result = $db->build_results($sql);
for($i=0; $i<count($result); $i++) {
	$result[$i]['NOM_ARRIENDO'] = urlencode($result[$i]['NOM_ARRIENDO']);
	$result[$i]['REFERENCIA'] = urlencode($result[$i]['REFERENCIA']);
}
print urlencode(json_encode($result));
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$cod_mes = URLDecode($_REQUEST['cod_mes']);
$ano = $_REQUEST['ano'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql_ano="select COD_ANO
		from ANO
		where ANO = $ano";
			
$result_ano = $db->build_results($sql_ano);
$cod_ano = $result_ano[0]['COD_ANO'];


$sql="select d.DOLAR_ADUANERO
		from DOLAR_TODOINOX d, MES m
		where d.COD_ANO = $cod_ano
		and d.COD_MES = m.COD_MES
		and d.COD_MES = $cod_mes";	
	
$result = $db->build_results($sql);
$dolar = $result[0]['DOLAR_ADUANERO'];
	
print $dolar; 	 
?>
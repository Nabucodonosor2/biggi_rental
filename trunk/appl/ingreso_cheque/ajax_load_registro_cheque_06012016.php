<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$rut = $_REQUEST['ve_rut'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select	A.COD_ARRIENDO
						,A.COD_ARRIENDO COD_ARRIENDO_H
						,NOM_ARRIENDO
						,convert(varchar(20), FECHA_ARRIENDO, 103) FECHA_ARRIENDO
						,REFERENCIA
						,A.NRO_ORDEN_COMPRA
						,A.CENTRO_COSTO_CLIENTE	
						,A.NRO_MESES
						,A.NRO_MESES NRO_MESES_H
						,A.TOTAL_CON_IVA
						,'N' CHECK_ARRIENDO
			from 		ARRIENDO A,
						EMPRESA E,
						USUARIO U,
						ESTADO_ARRIENDO EA
			where		A.COD_EMPRESA = E.COD_EMPRESA  
			and			A.COD_USUARIO = U.COD_USUARIO  
			and			A.COD_ESTADO_ARRIENDO = EA.COD_ESTADO_ARRIENDO
			and			A.COD_ESTADO_ARRIENDO NOT IN (3) --EMITIDA		
			and			E.RUT = $rut --89257000
			--and			A.EXIGE_CHEQUE = 'S'
			and			A.COD_ARRIENDO  not in(select COD_ARRIENDO from INGRESO_ARRIENDO IA, INGRESO_CHEQUE IC
												where IA.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE
												and COD_ESTADO_INGRESO_CHEQUE <> 3)
			order by A.COD_ARRIENDO desc";
$result = $db->build_results($sql);

$result_final = array();
for($i=0; $i<count($result); $i++) {
	$result_final[$i] = $result[$i]; 
}

print urlencode(json_encode($result_final));
?>
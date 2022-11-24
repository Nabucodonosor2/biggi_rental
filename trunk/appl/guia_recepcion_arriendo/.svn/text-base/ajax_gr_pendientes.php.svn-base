<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_arriendo = $_REQUEST['cod_arriendo'];
$K_ESTADO_EMITIDA	= 1;
$K_ESTADO_IMPRESA	= 2;

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	$sql = "SELECT DISTINCT(M.COD_MOD_ARRIENDO)
				 , M.REFERENCIA
			  FROM MOD_ARRIENDO M, ITEM_MOD_ARRIENDO I
			 WHERE M.COD_ARRIENDO = $cod_arriendo
			   AND M.COD_ESTADO_MOD_ARRIENDO = 2    --CONFIRMADO 
			   AND M.TIPO_MOD_ARRIENDO = 'ELIMINAR'
			   AND I.COD_MOD_ARRIENDO = M.COD_MOD_ARRIENDO
			   AND M.COD_MOD_ARRIENDO not in (select cod_doc from GUIA_RECEPCION where COD_ESTADO_GUIA_RECEPCION in($K_ESTADO_EMITIDA))
			   AND DBO.F_ARR_CANT_POR_RECEPCIONAR(I.COD_ITEM_MOD_ARRIENDO, NULL) > 0";
			 $result = $db->build_results($sql);  
			
				for ($i = 0; $i < count($result); $i++){
				 	$result[$i]['COD_MOD_ARRIENDO'] = urlencode($result[$i]['COD_MOD_ARRIENDO']);	
					$result[$i]['REFERENCIA'] = urlencode($result[$i]['REFERENCIA']);
				}
print json_encode($result);
?>
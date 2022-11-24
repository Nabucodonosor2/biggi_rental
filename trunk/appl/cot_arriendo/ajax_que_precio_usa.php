<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$cod_cotizacion = $_REQUEST['regreso'];
$num_dif = 0;
$respuesta = "";

	
	$sql="SELECT		COD_PRODUCTO,
						PRECIO
				FROM		ITEM_COT_ARRIENDO
				WHERE		COD_COT_ARRIENDO = $cod_cotizacion
				ORDER BY	ORDEN";
		 
    $result_i = $db->build_results($sql);
    for ($i=0; $i<count($result_i); $i++) {
    	$cod_producto = $result_i[$i]['COD_PRODUCTO'];
		$precio = $result_i[$i]['PRECIO'];
		$result	= $db->build_results("select PRECIO_VENTA_PUBLICO, PRECIO_LIBRE from PRODUCTO where COD_PRODUCTO = '$cod_producto'");
        $precio_bd	= $result[$i]['PRECIO_VENTA_PUBLICO'];
        if ($result[0]['PRECIO_LIBRE']=='S') 
				continue;
		if($precio_bd != $precio)
			$num_dif++;
	}
	if($num_dif > 0)
		$respuesta = "$cod_cotizacion|SI";
	else											  
		$respuesta = "$cod_cotizacion|NO";


print $respuesta;	

?>
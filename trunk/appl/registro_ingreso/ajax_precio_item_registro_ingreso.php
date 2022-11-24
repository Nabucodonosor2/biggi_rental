<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$cod_producto = URLDecode($_REQUEST['cod_producto']);
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql="SELECT PRECIO_VENTA_PUBLICO 0 
				,(SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO = 5) DOLAR
			FROM PRODUCTO
		  WHERE COD_PRODUCTO = '$cod_producto'";	
	
	$result = $db->build_results($sql);
	$precio = $result[0]['PRECIO_VENTA_PUBLICO'];
	$dolar = $result[0]['DOLAR'];
	
	$precio_us = $dolar * $precio; 
	
print $precio_us; 	 
?>
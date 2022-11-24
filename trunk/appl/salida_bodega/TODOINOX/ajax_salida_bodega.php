<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = $_REQUEST['cod_producto'];
$cod_usuario = session::get("COD_USUARIO");
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT MANEJA_INVENTARIO,dbo.f_bodega_stock('$cod_producto',1,GETDATE())STOCK
		FROM PRODUCTO 
		WHERE COD_PRODUCTO ='$cod_producto'";
		
		/*SELECT MANEJA_INVENTARIO,dbo.f_bodega_stock('$cod_producto',1,GETDATE())STOCK
		FROM PRODUCTO 
		WHERE COD_PRODUCTO ='$cod_producto'*/
		
		/*"SELECT MANEJA_INVENTARIO,COD_PRODUCTO 
		FROM PRODUCTO 
		WHERE COD_PRODUCTO ='$cod_producto'"*/
		
		  
$result = $db->build_results($sql);
$maneja_inventario = $result[0]['MANEJA_INVENTARIO'];
$stock  = $result[0]['STOCK'];

print $maneja_inventario.'|'.$stock;

?>
<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST['cod_empresa'];
$cod_usuario = session::get("COD_USUARIO");

  $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
  
  $sql="select DSCTO_PERMITIDO 
  		  from EMPRESA 
  		 where COD_EMPRESA = $cod_empresa";
 	
  	$result = $db->build_results($sql);
	$dscto_permitido = $result[0]['DSCTO_PERMITIDO'];

	if($dscto_permitido != 0.00){
		$empresa = 'la empresa';
		 $respuesta = $dscto_permitido.'|'.$empresa;
		print $respuesta;
	}else{
		$sql="select PORC_DESCUENTO_PERMITIDO 
			  from USUARIO
			  where COD_USUARIO = $cod_usuario";

		$result = $db->build_results($sql);
		$porc_descuento_permitido = $result[0]['PORC_DESCUENTO_PERMITIDO'];
		$usuario = 'el usuario';
		 $respuesta = $porc_descuento_permitido.'|'.$usuario;	
		print $respuesta;  
	}
?>
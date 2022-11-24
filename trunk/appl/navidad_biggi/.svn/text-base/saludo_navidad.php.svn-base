<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../appl.ini");

session::set('K_ROOT_URL', K_ROOT_URL);
session::set('K_ROOT_DIR', K_ROOT_DIR);
session::set('K_CLIENTE', K_CLIENTE);
session::set('K_APPL', K_APPL);

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT EMAIL 
		FROM AUX_EMAIL";
$result	= $db->build_results($sql);
for ($i = 0; $i < count($result); $i++){
	$email = $result[$i]['EMAIL'];
	
	$para = $email;
	//echo "<IMG SRC='imagen/feliz_navidad.jpg'>";
	//Envio de mail
	$asunto = 'Saludo Navideño ';
	$contenido .= "<IMG SRC='feliz_navidad.jpg'>";
	$cabeceras  = 'MIME-Version: 1.0' . "\n";
	$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
	$cabeceras .= 'From: jmino@integrasystem.cl'."\n";
	mail($para ,$asunto,$contenido,$cabeceras);
}
?>
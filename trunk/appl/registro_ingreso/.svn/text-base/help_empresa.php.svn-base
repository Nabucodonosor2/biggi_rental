<?php
///////////////////////
// php llamado con ajax en el javascript function help_empresa()
////////////////////////
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("class_help_empresa.php");

$cod_proveedor_ext_4d = $_REQUEST['cod_proveedor_ext_4d'];
$alias_proveedor_ext  = $_REQUEST['alias_proveedor_ext'];
$nom_proveedor_ext    = $_REQUEST['nom_proveedor_ext'];
help_empresa::find_empresa($cod_proveedor_ext_4d,$alias_proveedor_ext, $nom_proveedor_ext);
?>
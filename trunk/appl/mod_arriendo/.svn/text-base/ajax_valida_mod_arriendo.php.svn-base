<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_arriendo = $_REQUEST['cod_arriendo'];
   
      $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
      ///valida que exista
      $sql = "SELECT COD_MOD_ARRIENDO FROM MOD_ARRIENDO
			  WHERE COD_ARRIENDO = $cod_arriendo
			  AND COD_ESTADO_MOD_ARRIENDO = 1";
                  
      $result = $db->build_results($sql);
      $cod_estado = $result[0]['COD_MOD_ARRIENDO'];
      
print $cod_estado;
?>

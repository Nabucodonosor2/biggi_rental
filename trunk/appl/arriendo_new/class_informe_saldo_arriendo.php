<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
class informe_saldo_arriendo extends reporte {	
	function informe_saldo_arriendo($tipo_mod_arriendo, $cod_arriendo, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		$sql = "exec spi_informe_saldo_arriendo $cod_arriendo, '$tipo_mod_arriendo'";
		  
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
}
?>
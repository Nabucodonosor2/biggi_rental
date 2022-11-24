<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
//ini_set("display_errors", "on");
class print_anexo_arriendo_d extends reporte {	
	var $cod_arriendo;
	function print_anexo_arriendo_d($cod_arriendo, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		$this->cod_arriendo = $cod_arriendo;
		
		$sql = "exec spi_anexo_arriendo_d $cod_arriendo";

		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
}
?>
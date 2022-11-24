<?php
require_once(dirname(__FILE__)."/../../common_appl/class_reporte_biggi.php");

class dw_item_entrada_bodega extends dw_item_entrada_bodega_base {
	function dw_item_entrada_bodega() {
		parent::dw_item_entrada_bodega_base();	
	}
}

class wi_entrada_bodega extends wi_entrada_bodega_base{
	function wi_entrada_bodega($cod_item_menu) {
		parent::wi_entrada_bodega_base($cod_item_menu);
		
		$sql = "SELECT COD_BODEGA
						,NOM_BODEGA
				FROM BODEGA";
		$this->dws['dw_entrada_bodega']->add_control(new drop_down_dw('COD_BODEGA', $sql,120));
	}
	
	function load_record() {
		parent::load_record();
		$this->b_print_visible	 = true;
	}
	
	function print_record(){
		$cod_entrada = $this->get_key();
		$sql = "exec spi_entrada_bodega $cod_entrada";
		
		// reporte
		$labels = array();
		$labels['strCOD_ENTRADA'] = $cod_entrada;							
		$file_name = $this->find_file('entrada_bodega/RENTAL', 'entrada_bodega.xml');	
						
		$rpt = new print_entrada_bodega($sql, $file_name, $labels, "Entrada Bodega ".$cod_entrada, 1);
		$this->_load_record();
		return true;
	}
}

class print_entrada_bodega extends reporte_biggi {	
	function print_entrada_bodega($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
}
?>
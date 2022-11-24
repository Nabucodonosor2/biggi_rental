<?php
require_once(dirname(__FILE__) . "/../../../../../commonlib/trunk/php/auto_load.php");

class dw_item_salida_bodega extends dw_item_salida_bodega_base {
	function dw_item_salida_bodega(){
		parent::dw_item_salida_bodega_base();
	}
}
class wi_salida_bodega extends wi_salida_bodega_base{
	function wi_salida_bodega($cod_item_menu){
		parent::wi_salida_bodega_base($cod_item_menu);
	}
	
	function load_record(){
		parent::load_record();
		$this->b_delete_visible  = false;
		$this->b_save_visible 	 = false;
		$this->b_modify_visible	 = false;
		$this->b_print_visible	 = true;
	}
	function print_record() {
		$cod_salida_bodega = $this->get_key();
		$sql= "exec spi_salida_bodega $cod_salida_bodega";

		// reporte'
		$labels = array();
		$labels['strCOD_ITEM_SALIDA_BODEGA'] = $cod_salida_bodega;				
		$file_name = $this->find_file('salida_bodega', 'RENTAL/salida_bodega.xml');				

		$rpt = new print_salida_bodega($sql, $file_name, $labels, "Salida Bodega ".$cod_salida_bodega,1);
		$this->_load_record();
		return true;
	}
}
?>
<?php
require_once(dirname(__FILE__)."/../../common_appl/class_reporte_biggi.php");

class dw_item_entrada_bodega extends dw_item_entrada_bodega_base {
	function dw_item_entrada_bodega() {
		parent::dw_item_entrada_bodega_base();
		$this->controls['NOM_PRODUCTO']->size = 82;
		$this->controls['COD_PRODUCTO']->size = 15;	
	}
	
	function update($db, $cod_entrada_bodega)	{
		$sp = 'spu_item_entrada_bodega';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_item_entrada_bodega= $this->get_item($i, 'COD_ITEM_ENTRADA_BODEGA');
			$orden 					= $this->get_item($i, 'ORDEN');
			$item 					= $this->get_item($i, 'ITEM');
			$cod_producto 			= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 			= $this->get_item($i, 'NOM_PRODUCTO');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');
			//$precio 				= $this->get_item($i, 'PRECIO');

			$cod_item_entrada_bodega = ($cod_item_entrada_bodega=='') ? "null" : $cod_item_entrada_bodega;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion'
						,$cod_item_entrada_bodega
						,$cod_entrada_bodega
						,$orden
						,'$item'
						,'$cod_producto'
						,'$nom_producto'
						,$cantidad
						,0";
						
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$cod_item_entrada_bodega = $this->get_item($i, 'COD_ITEM_ENTRADA_BODEGA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_item_entrada_bodega")){
				return false;
			}
		}
		//Ordernar
		if ($this->row_count() > 0) {
			$parametros_sp = "'ITEM_ENTRADA_BODEGA','ENTRADA_BODEGA', $cod_entrada_bodega";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
		}
		return true;
	}
}

class wi_entrada_bodega extends wi_entrada_bodega_base{
	function wi_entrada_bodega($cod_item_menu) {
		parent::wi_entrada_bodega_base($cod_item_menu);
		
		$sql = "SELECT COD_BODEGA
					  ,NOM_BODEGA
				FROM BODEGA
				WHERE COD_TIPO_BODEGA = 1";
		$this->dws['dw_entrada_bodega']->add_control(new drop_down_dw('COD_BODEGA', $sql,120));
		$this->dws['dw_entrada_bodega']->add_control(new edit_text_multiline('OBS',100, 3));
		
		$sql = "select 1 COD_TIPO_DOC
						,'FACTURA' NOM_TIPO_DOC
				union
				select 2 COD_TIPO_DOC
						,'OCI' NOM_TIPO_DOC";
		
		$this->dws['dw_entrada_bodega']->add_control(new drop_down_dw('COD_TIPO_DOC', $sql, 120));
		$this->dws['dw_entrada_bodega']->add_control(new edit_text('COD_DOC', 15,10));
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
	
function save_record($db) {
		$cod_entrada_bodega = $this->get_key();
		$cod_bodega = $this->dws['dw_entrada_bodega']->get_item(0, 'COD_BODEGA');
		$tipo_doc = $this->dws['dw_entrada_bodega']->get_item(0, 'COD_TIPO_DOC');
		if ($tipo_doc == 1)
			$tipo_doc = 'FACTURA';
		else
			$tipo_doc = 'OCI'; 	
		$cod_doc= $this->dws['dw_entrada_bodega']->get_item(0, 'COD_DOC');
		$referencia = $this->dws['dw_entrada_bodega']->get_item(0, 'REFERENCIA');
		$obs = $this->dws['dw_entrada_bodega']->get_item(0, 'OBS');
		$nro_fa_proveedor = $this->dws['dw_entrada_bodega']->get_item(0, 'NRO_FA_PROVEEDOR');
		$fecha_fa_proveedor = $this->dws['dw_entrada_bodega']->get_item(0, 'FECHA_FA_PROVEEDOR');
		$tipo_fa_proveedor = 'NULL';
		
		$cod_entrada_bodega = ($cod_entrada_bodega=='') ? 'NULL' : $cod_entrada_bodega;
		
		$sp = 'spu_entrada_bodega';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion'
	    			,$cod_entrada_bodega
	    			,$this->cod_usuario
	    			,$cod_bodega
	    			,$tipo_doc
	    			,$cod_doc
	    			,'$referencia'
	    			,'$obs'";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_entrada_bodega = $db->GET_IDENTITY();
				$this->dws['dw_entrada_bodega']->set_item(0, 'COD_ENTRADA_BODEGA', $cod_entrada_bodega);
			}
			
			if (!$this->dws['dw_item_entrada_bodega']->update($db, $cod_entrada_bodega))
				return false;
			
			return true;
		}
		return false;		
				
	}
}

class print_entrada_bodega extends reporte_biggi {	
	function print_entrada_bodega($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
}
?>
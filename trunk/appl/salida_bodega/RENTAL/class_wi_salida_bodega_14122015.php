<?php
require_once(dirname(__FILE__) . "/../../../../../commonlib/trunk/php/auto_load.php");

class dw_item_salida_bodega extends dw_item_salida_bodega_base {
	function dw_item_salida_bodega(){
		parent::dw_item_salida_bodega_base();
		$this->add_controls_producto_help();	
		$this->set_first_focus('COD_PRODUCTO');
		$this->controls['NOM_PRODUCTO']->size = 115;
		$this->controls['COD_PRODUCTO']->size = 25;
		$this->add_control(new edit_text('CANTIDAD',25,25));
		$this->set_mandatory('COD_PRODUCTO', 'C�digo del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
	}
	
	function update($db)	{
		$sp = 'spu_item_salida_bodega';
			
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_item_salida_bodega	= $this->get_item($i, 'COD_ITEM_SALIDA_BODEGA');
			$cod_salida_bodega		= $this->get_item($i, 'COD_SALIDA_BODEGA');
			$cod_producto 			= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 			= $this->get_item($i, 'NOM_PRODUCTO');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');

			$item = $i + 1 ;
			$cod_item_salida_bodega = ($cod_item_salida_bodega=='') ? "null" : $cod_item_salida_bodega;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion'
						,$cod_item_salida_bodega
						,$cod_salida_bodega
						,0
						,'$item'
						,'$cod_producto'
						,'$nom_producto'
						,$cantidad
						,null";
			
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
				
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$cod_item_salida_bodega = $this->get_item($i, 'COD_ITEM_SALIDA_BODEGA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_item_salida_bodega")){
				return false;
			}
		}
		//Ordernar
		if ($this->row_count() > 0) {
			$parametros_sp = "'ITEM_SALIDA_BODEGA','SALIDA_BODEGA', $cod_salida_bodega";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
		}
		return true;
	}
}
class wi_salida_bodega extends wi_salida_bodega_base{
	function wi_salida_bodega($cod_item_menu){
		parent::wi_salida_bodega_base($cod_item_menu);
		$this->dws['dw_salida_bodega']->add_control(new edit_text_multiline('OBS',100, 3));
		
		$sql = "SELECT COD_BODEGA
					  ,NOM_BODEGA
				FROM BODEGA
				WHERE COD_TIPO_BODEGA = 1";
		$this->dws['dw_salida_bodega']->add_control(new drop_down_dw('COD_BODEGA', $sql));
		
		$sql = "select COD_TIPO_DOC_SII TIPO_DOC
						,NOM_TIPO_DOC_SII 
				from TIPO_DOC_SII
				ORDER BY ORDEN";	// sala venta
		$this->dws['dw_salida_bodega']->add_control(new drop_down_dw('TIPO_DOC', $sql));
		
		$this->dws['dw_salida_bodega']->add_control(new edit_text('COD_DOC', 15,10));
	}
	function new_record() {
		$this->dws['dw_salida_bodega']->insert_row();
		$this->dws['dw_salida_bodega']->set_item(0, 'FECHA_SALIDA_BODEGA', $this->current_date());
		$this->dws['dw_salida_bodega']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_salida_bodega']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->b_print_visible	 = false;
		
	}
	function load_record(){
		parent::load_record();
		$this->b_delete_visible  = false;
		$this->b_save_visible 	 = false;
		$this->b_modify_visible	 = false;
		$this->b_print_visible	 = true;
		
		$sql = "SELECT 'FACTURA' TIPO_DOC
					  ,'FACTURA' NOM_TIPO_DOC
				UNION
				SELECT 'GU�A DESPACHO' TIPO_DOC
					  ,'GU�A DESPACHO' NOM_TIPO_DOC
				UNION	  
				SELECT 'NOTA CR�DITO' TIPO_DOC
					  ,'NOTA CR�DITO' NOM_TIPO_DOC
				UNION	  
				SELECT 'NOTA D�BITO' TIPO_DOC
					  ,'NOTA D�BITO' NOM_TIPO_DOC
				UNION	  
				SELECT 'FACTURA EXENTA' TIPO_DOC
					  ,'FACTURA EXENTA' NOM_TIPO_DOC";
		$this->dws['dw_salida_bodega']->add_control(new drop_down_dw('TIPO_DOC', $sql));
	}
	
	function save_record($db) {
		$cod_salida_bodega	= $this->get_key();
		$cod_bodega			= $this->dws['dw_salida_bodega']->get_item(0, 'COD_BODEGA');
		$referencia			= $this->dws['dw_salida_bodega']->get_item(0, 'REFERENCIA');
		$tipo_doc			= $this->dws['dw_salida_bodega']->get_item(0, 'TIPO_DOC');
		$cod_doc 			= $this->dws['dw_salida_bodega']->get_item(0, 'COD_DOC');
		$obs	 			= $this->dws['dw_salida_bodega']->get_item(0, 'OBS');
		$cod_usuario 		= $this->dws['dw_salida_bodega']->get_item(0, 'COD_USUARIO');
		
		if($tipo_doc == 1)
			$tipo_doc = "FACTURA";
		if($tipo_doc == 2)
			$tipo_doc = "GU�A DESPACHO";
		if($tipo_doc == 3)
			$tipo_doc = "NOTA CR�DITO";
		if($tipo_doc == 4)
			$tipo_doc = "NOTA D�BITO";
		if($tipo_doc == 5)
			$tipo_doc = "FACTURA EXENTA";				
			
		$cod_salida_bodega 	= ($cod_salida_bodega	=='') ? 'NULL' : $cod_salida_bodega;
		$tipo_doc 			= ($tipo_doc			=='') ? "NULL" : "$tipo_doc";
		$cod_doc 			= ($cod_doc				=='') ? "NULL" : "$cod_doc";
		$obs 				= ($obs					=='') ? "NULL" : "'$obs'";
		
		$sp = 'spu_salida_bodega';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion'
	    			,$cod_salida_bodega
	    			,$cod_usuario
	    			,$cod_bodega
	    			,'$tipo_doc'
	    			,$cod_doc
	    			,'$referencia'
	    			,$obs";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_salida_bodega = $db->GET_IDENTITY();
				$this->dws['dw_salida_bodega']->set_item(0,'COD_SALIDA_BODEGA', $cod_salida_bodega);
			}
			
			for ($i=0; $i<$this->dws['dw_item_salida_bodega']->row_count(); $i++)
				$this->dws['dw_item_salida_bodega']->set_item($i, 'COD_SALIDA_BODEGA', $cod_salida_bodega);
			
			if (!$this->dws['dw_item_salida_bodega']->update($db))
				return false;
			
			return true;
		}
		return false;		
				
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
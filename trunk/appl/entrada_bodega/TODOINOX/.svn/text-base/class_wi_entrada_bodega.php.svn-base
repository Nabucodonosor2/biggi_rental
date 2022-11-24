<?php
require_once(dirname(__FILE__) . "/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../common_appl/class_reporte_biggi.php");

	class dw_item_entrada_bodega extends dw_item_entrada_bodega_base {
		function dw_item_entrada_bodega() {
			//parent::dw_item_entrada_bodega_base();
				$sql = "SELECT	IEB.COD_ITEM_ENTRADA_BODEGA,
							COD_ITEM_ENTRADA_BODEGA COD_ITEM_ENTRADA_BODEGA_H,
						IEB.COD_ENTRADA_BODEGA,
						IEB.ORDEN,
						IEB.ITEM,
						IEB.COD_PRODUCTO,
						IEB.NOM_PRODUCTO,
						IEB.CANTIDAD,
						IEB.PRECIO
				FROM	ITEM_ENTRADA_BODEGA IEB
				WHERE 	IEB.COD_ENTRADA_BODEGA =  {KEY1}";

		parent::datawindow($sql, 'ITEM_ENTRADA_BODEGA', true, true);	
		
		$this->add_control(new edit_text('COD_ITEM_ENTRADA_BODEGA_H',10, 10, 'hidden'));
		
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text('ITEM',4 , 5));
		$this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		$control->set_onChange("valida_stock(this);");

//		$this->add_control(new computed('PRECIO', 0));
		$this->add_control(new edit_num('PRECIO',10, 10));
		
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL');
		$this->add_controls_producto_help();	// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		$this->set_first_focus('COD_PRODUCTO');

		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
		 
		}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		$this->set_item($row, 'ITEM', $this->row_count());
		return $row;
	}
	function update($db, $cod_entrada_bodega)	{
		$sp = 'spu_item_entrada_bodega';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			
			$cod_item_entrada_bodega= $this->get_item($i, 'COD_ITEM_ENTRADA_BODEGA_H');
			
			$orden 					= $this->get_item($i, 'ORDEN');
			$item 					= $this->get_item($i, 'ITEM');
			$cod_producto 			= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 			= $this->get_item($i, 'NOM_PRODUCTO');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');
			$precio 				= $this->get_item($i, 'PRECIO');
			
			

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
						,$precio";
					
				
			if (!$db->EXECUTE_SP($sp,$param))
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
	const K_HABILITA_CREAR_DESDE = '993015';
	function wi_entrada_bodega($cod_item_menu) {
		parent::wi_entrada_bodega_base($cod_item_menu);
		
		$sql = "select COD_BODEGA
						,NOM_BODEGA
				from BODEGA
				where COD_BODEGA = 1";	// sala venta
		$this->dws['dw_entrada_bodega']->controls['COD_BODEGA']->set_sql($sql);
		$this->dws['dw_entrada_bodega']->controls['COD_BODEGA']->retrieve();
	}
	function new_record() {
		parent::new_record();
		$this->dws['dw_entrada_bodega']->set_item(0, 'COD_BODEGA', 1);	// BODEGA TODOINOX*/
		if (session::is_set('IB_CREAR_DESDE_RI')) {
				$nro_registro_ingreso = session::get('IB_CREAR_DESDE_RI');
			
				$this->creada_desde_ri($nro_registro_ingreso);
				session::un_set('IB_CREAR_DESDE_RI');
				return;
		}else if(session::is_set('IB_CREAR_DESDE_OC')){
				$cod_oc = session::get('IB_CREAR_DESDE_OC');
			
				$this->creada_desde_oc($cod_oc);
				session::un_set('IB_CREAR_DESDE_OC');
				return;
		}
	}

	function load_record() {
		parent::load_record();
		$COD_ENTRADA_BODEGA = $this->get_item_wo($this->current_record, 'COD_ENTRADA_BODEGA');
		$this->dws['dw_entrada_bodega']->retrieve($COD_ENTRADA_BODEGA);	
		$this->dws['dw_item_entrada_bodega']->retrieve($COD_ENTRADA_BODEGA);
		
		$priv = $this->get_privilegio_opcion_usuario(self::K_HABILITA_CREAR_DESDE, $this->cod_usuario);
		if ($priv=='E'){
			$this->b_create_visible = true;
		}else{
			$this->b_create_visible = false;
		}
		
		$this->b_delete_visible  = false;
		$this->b_save_visible 	 = false;
		//$this->b_no_save_visible = false;
		$this->b_modify_visible	 = false;
		$this->b_print_visible	 = true;
		
	}
	function save_record($db) {
		$cod_entrada_bodega = $this->get_key();
		$cod_bodega = $this->dws['dw_entrada_bodega']->get_item(0, 'COD_BODEGA');
		$tipo_doc = $this->dws['dw_entrada_bodega']->get_item(0, 'TIPO_DOC');
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
	    			,'AJUSTE'
	    			,null
	    			,'$referencia'
	    			,'$obs'";
		//echo $sp.' '.$param;
		if ($db->EXECUTE_SP($sp,$param)){
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
	
	function creada_desde_ri($nro_registro_ingreso) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql_desde_item_ri="SELECT		NUMERO_REGISTRO_INGRESO,
										ITEM,
										1 ORDEN,
										null COD_ITEM_ENTRADA_BODEGA_H,
										MODELO COD_PRODUCTO,
										NOM_PRODUCTO,
										CANTIDAD,
										PRECIO,
										TOTAL,
										null COD_ITEM_ENTRADA_BODEGA
							FROM		ITEM_REGISTRO_4D IR4 , PRODUCTO P
							WHERE		P.COD_PRODUCTO = IR4.MODELO
							AND NUMERO_REGISTRO_INGRESO = $nro_registro_ingreso";
		
		$sql = $this->dws['dw_item_entrada_bodega']->get_sql();
		$this->dws['dw_item_entrada_bodega']->set_sql($sql_desde_item_ri);
		$this->dws['dw_item_entrada_bodega']->retrieve($nro_registro_ingreso);
		$this->dws['dw_item_entrada_bodega']->set_sql($sql);
		
		for ($i=0; $i<$this->dws['dw_item_entrada_bodega']->row_count(); $i++)
			$this->dws['dw_item_entrada_bodega']->set_status_row($i, K_ROW_NEW_MODIFIED);
	}
	
	function creada_desde_oc($cod_oc) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
	  $sql_desde_item_oc="SELECT IOC.ITEM
						  ,IOC.ORDEN
						  ,IOC.COD_PRODUCTO
						  ,IOC.NOM_PRODUCTO
						  ,IOC.CANTIDAD
						  ,IOC.PRECIO
						  ,null COD_ITEM_ENTRADA_BODEGA_H
					FROM ITEM_ORDEN_COMPRA IOC , PRODUCTO P 
					WHERE P.COD_PRODUCTO = IOC.COD_PRODUCTO
					  AND IOC.COD_ORDEN_COMPRA = $cod_oc";
					  
		 
		$sql = $this->dws['dw_item_entrada_bodega']->get_sql();
		$this->dws['dw_item_entrada_bodega']->set_sql($sql_desde_item_oc);
		$this->dws['dw_item_entrada_bodega']->retrieve($cod_oc);
		$this->dws['dw_item_entrada_bodega']->set_sql($sql);
		
		for ($i=0; $i<$this->dws['dw_item_entrada_bodega']->row_count(); $i++)
			$this->dws['dw_item_entrada_bodega']->set_status_row($i, K_ROW_NEW_MODIFIED);
	}
	
	function print_record(){
		$cod_entrada = $this->get_key();
		$sql = "exec spi_entrada_bodega $cod_entrada";
		
		// reporte
		$labels = array();
		$labels['strCOD_ENTRADA'] = $cod_entrada;							
		$file_name = $this->find_file('entrada_bodega/TODOINOX', 'entrada_bodega.xml');	
						
		$rpt = new print_entrada_bodega($sql, $file_name, $labels, "Entrada Bodega".$cod_entrada, 1);
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
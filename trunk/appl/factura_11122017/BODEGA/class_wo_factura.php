<?php
////////////////////////////////////////
/////////// BODEGA_BIGGI ///////////////
////////////////////////////////////////
class wo_factura extends wo_factura_base {
	const K_EMPRESA_BODEGA_BIGGI = 1138;
	
	function wo_factura() {
		parent::wo_factura_base();
		// se elimina F.COD_TIPO_FACTURA = ".self::K_TIPO_VENTA."
		// parab que traiga todas las FA
		$sql = "select F.COD_FACTURA
						,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
						,F.NRO_FACTURA
						,F.RUT
						,F.DIG_VERIF
						,F.NOM_EMPRESA
						,dbo.f_fa_NV_COMERCIAL(F.COD_FACTURA) COD_DOC
						,EDS.NOM_ESTADO_DOC_SII
						,F.TOTAL_CON_IVA
						,U.INI_USUARIO
						,dbo.f_fa_tipo_doc(F.COD_FACTURA) TIPO_FA
						,F.NRO_ORDEN_COMPRA
				 from	FACTURA F, ESTADO_DOC_SII EDS, USUARIO U
				where	F.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII AND
						F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO
				order by	isnull(NRO_FACTURA, 9999999999) desc, COD_FACTURA desc";
		$this->dw->set_sql($sql);		
		$this->sql_original = $sql;
		//$this->add_header(new header_text('COD_DOC', 'dbo.f_fa_NV_COMERCIAL(F.COD_FACTURA)', 'N° NV'));
		$this->add_header(new header_text('NRO_ORDEN_COMPRA', 'F.NRO_ORDEN_COMPRA', 'N° OC Comercial'));
	}
	function detalle_record_desde($modificar, $cant_fa_a_hacer) 
	{
		
		// No se llama al ancestro porque se reimplementa toda la rutina
		session::set("cant_fa_a_hacer", $cant_fa_a_hacer);
		
		// retrieve
		$this->set_count_output();
		$this->last_page = Ceil($this->row_count_output / $this->row_per_page);
		$this->set_current_page(0);
		$this->save_SESSION();

		$pag_a_mostrar=$cant_fa_a_hacer -1;

		$this->detalle_record($pag_a_mostrar);	// Se va al primer registro
		echo $pag_a_mostrar;
	}
  	function crear_fa_from_oc_comercial($cod_orden_compra_comercial) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_ORDEN_COMPRA FROM BIGGI.dbo.ORDEN_COMPRA WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no existe en Comercial.');								
			return;
		}else{
			//si existe la Oc determina el tipo
			$sql = "SELECT COD_ORDEN_COMPRA
							,COD_EMPRESA 
					FROM BIGGI.dbo.ORDEN_COMPRA 
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial
						and TIPO_ORDEN_COMPRA IN ('NOTA_VENTA', 'BACKCHARGE')";

			$result = $db->build_results($sql);
			if (count($result) == 0){
				$this->_redraw();
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no es de tipo VENTA ó BACKCHARGE');								
				return;
			}
			else if ($result[0]['COD_EMPRESA'] != self::K_EMPRESA_BODEGA_BIGGI){
				$this->_redraw();
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no es para Bodega Biggi');								
				return;
			}

			//Verica si tiene items pendientes de facturar
			$sql = "SELECT isnull(sum(dbo.f_fa_OC_Comercial_por_facturar(COD_ITEM_ORDEN_COMPRA)), 0) CANT 
					FROM BIGGI.dbo.ITEM_ORDEN_COMPRA 
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
			$result = $db->build_results($sql);
			if ($result[0]['CANT']==0) {
				$this->_redraw();
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' ya esta facturada');								
				return;
			}
			else {			
				
				$cantidad_max = $this->get_parametro(self::K_PARAM_MAX_IT_FA);
				$sql_cuenta="select count(*) cantidad
							from BIGGI.dbo.ITEM_ORDEN_COMPRA IOC, biggi.dbo.ORDEN_COMPRA OC
							where OC.COD_ORDEN_COMPRA = $cod_orden_compra_comercial  
							AND OC.COD_ORDEN_COMPRA = IOC.COD_ORDEN_COMPRA";
					$result_cuenta = $db->build_results($sql_cuenta);
					$cantidad = $result_cuenta[0]['cantidad'];
					$cant_fa_a_hacer=ceil($cantidad/$cantidad_max);
				
				$cod_usuario = $this->cod_usuario;	
								
				$sp = 'sp_fa_crear_desde_oc';
				$param = "$cod_orden_compra_comercial, $cod_usuario";
				
			$db->BEGIN_TRANSACTION();
				if ($db->EXECUTE_SP($sp, $param)) { 
					$db->COMMIT_TRANSACTION();
					
					$this->detalle_record_desde(true,$cant_fa_a_hacer);
				}
				else { 
					$db->ROLLBACK_TRANSACTION();
					$this->_redraw();
					$this->alert("No se pudo crear la factura. Error en 'sp_fa_crear_desde_oc', favor contacte a IntegraSystem.");
				}
				
				
				session::set('FACTURA_DESDE_OC_COMERCIAL', $cod_orden_compra_comercial);
				$this->add();
	   		}				
		}
  		
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x'])) {
			$this->crear_fa_from_oc_comercial($_POST['wo_hidden']);
		}
		else
			parent::procesa_event();
	}
}
?>
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
						,F.FECHA_FACTURA DATE_FACTURA
						,F.NRO_FACTURA
						,F.RUT
						,F.DIG_VERIF
						,F.NOM_EMPRESA
						,F.COD_USUARIO_VENDEDOR1
						,F.COD_ESTADO_DOC_SII
						,dbo.f_fa_NV_COMERCIAL(F.COD_FACTURA) COD_DOC
						,EDS.NOM_ESTADO_DOC_SII
						,F.TOTAL_CON_IVA
						,U.INI_USUARIO
						,dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII) TIPO_FA
						,F.NRO_ORDEN_COMPRA
				 from	FACTURA F, ESTADO_DOC_SII EDS, USUARIO U
				where	F.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII AND
						F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO
						and F.COD_TIPO_FACTURA <> 2 --arriendo
				order by	isnull(NRO_FACTURA, 9999999999) desc, COD_FACTURA desc";
		$this->dw->set_sql($sql);		
		$this->sql_original = $sql;
		//$this->add_header(new header_text('COD_DOC', 'dbo.f_fa_NV_COMERCIAL(F.COD_FACTURA)', 'N� NV'));
		$this->add_header(new header_text('NRO_ORDEN_COMPRA', 'F.NRO_ORDEN_COMPRA', 'N� OC'));
	}
  	function crear_fa_from_oc_comercial($cod_orden_compra_comercial) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_ORDEN_COMPRA FROM BIGGI_dbo_ORDEN_COMPRA WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La OC N�'.$cod_orden_compra_comercial.' no existe en Comercial.');								
			return;
		}else{
			//si existe la Oc determina el tipo
			$sql = "SELECT COD_ORDEN_COMPRA
							,COD_EMPRESA 
					FROM BIGGI_dbo_ORDEN_COMPRA 
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial
						and TIPO_ORDEN_COMPRA IN ('NOTA_VENTA', 'BACKCHARGE')";

			$result = $db->build_results($sql);
			if (count($result) == 0){
				$this->_redraw();
				$this->alert('La OC N�'.$cod_orden_compra_comercial.' no es de tipo VENTA � BACKCHARGE');								
				return;
			}
			else if ($result[0]['COD_EMPRESA'] != self::K_EMPRESA_BODEGA_BIGGI){
				$this->_redraw();
				$this->alert('La OC N�'.$cod_orden_compra_comercial.' no es para Bodega Biggi');								
				return;
			}

			//Verica si tiene items pendientes de facturar
			$sql = "SELECT isnull(sum(dbo.f_fa_OC_Comercial_por_facturar(COD_ITEM_ORDEN_COMPRA)), 0) CANT 
					FROM BIGGI_dbo_ITEM_ORDEN_COMPRA 
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
			$result = $db->build_results($sql);
			if ($result[0]['CANT']==0) {
				$this->_redraw();
				$this->alert('La OC N�'.$cod_orden_compra_comercial.' ya esta facturada');								
				return;
			}
			else {			
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
<?php
////////////////////////////////////////
/////////// BODEGA_BIGGI ///////////////
////////////////////////////////////////
class wo_factura extends wo_factura_base {
	const K_EMPRESA_BODEGA_BIGGI = 1138;
	var $autoriza_print;
	var $autoriza_xml;
	
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
		//$this->add_header(new header_text('COD_DOC', 'dbo.f_fa_NV_COMERCIAL(F.COD_FACTURA)', 'N° NV'));
		$this->add_header(new header_text('NRO_ORDEN_COMPRA', 'F.NRO_ORDEN_COMPRA', 'N° OC'));
		
		$priv = $this->get_privilegio_opcion_usuario('992075', $this->cod_usuario); //print
		if($priv=='E')
			$this->autoriza_print = true;
      	else
			$this->autoriza_print = false;
			
		$priv = $this->get_privilegio_opcion_usuario('992085', $this->cod_usuario); //xml
		if($priv=='E')
			$this->autoriza_xml = true;
      	else
			$this->autoriza_xml = false;
	}
	
	function redraw_item(&$temp, $ind, $record){
		$COD_FACTURA		= $this->dw->get_item($record, 'COD_FACTURA');
		$COD_ESTADO_DOC_SII = $this->dw->get_item($record, 'COD_ESTADO_DOC_SII');
	
		$temp->gotoNext("wo_registro");
		if ($ind % 2 == 0) {
			$temp->setVar("wo_registro.WO_TR_CSS", $this->css_claro);
			$temp->setVar("wo_registro.WO_DETALLE", '<input name="b_detalle_'.$ind.'" id="b_detalle_'.$ind.'" value="'.$ind.'" src="../../../../com	monlib/trunk/images/lupa1.jpg" type="image">');
		}
		else {
			$temp->setVar("wo_registro.WO_TR_CSS", $this->css_oscuro);
			$temp->setVar("wo_registro.WO_DETALLE", '<input name="b_detalle_'.$ind.'" id="b_detalle_'.$ind.'" value="'.$ind.'" src="../../../../commonlib/trunk/images/lupa2.jpg" type="image">');
		}
		
		if($COD_ESTADO_DOC_SII == 2 && $this->autoriza_print == true){
			$control =  '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" type="button" title="Imprimir"'.
			   			'style="cursor:pointer;height:26px;width:17px;border: 0;background-image:url(../../images_appl/b_dte_print.png);background-repeat:no-repeat;background-position:center;"'.
			   			'onClick="return dlg_print_dte_wo(this, '.$COD_FACTURA.');"/>';
			
			$temp->setVar("wo_registro.WO_PRINT_DTE", $control);
		}else if($COD_ESTADO_DOC_SII == 3 && $this->autoriza_print == true){
			$control =  '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" type="button" title="Imprimir"'.
			   			'style="cursor:pointer;height:26px;width:17px;border: 0;background-image:url(../../images_appl/b_dte_print.png);background-repeat:no-repeat;background-position:center;"'.
			   			'onClick="return dlg_print_dte_wo(this, '.$COD_FACTURA.');"/>';
			
			$temp->setVar("wo_registro.WO_PRINT_DTE", $control);
		}else
			$temp->setVar("wo_registro.WO_PRINT_DTE", '<img src="../../images_appl/b_dte_print_d.png">');
		
		
		if($COD_ESTADO_DOC_SII == 3 && $this->autoriza_xml == true)
			$temp->setVar("wo_registro.WO_XML_DTE", '<input name="b_xmlDTE_'.$ind.'" id="b_xmlDTE_'.$ind.'" value="'.$ind.'" title="Descargar XML" src="../../images_appl/b_dte_xml.png" type="image">');
		else
			$temp->setVar("wo_registro.WO_XML_DTE", '<img src="../../images_appl/b_dte_xml_d.png">');
		
		
		$this->dw->fill_record($temp, $record);
		
		//////////////////
		// llama al js para grabar scrol
		$temp->setVar("wo_registro.WO_DETALLE", '<input name="b_detalle_'.$ind.'" id="b_detalle_'.$ind.'" value="'.$ind.'" src="../../../../commonlib/trunk/images/lupa2.jpg" type="image" onClick="graba_scroll(\''.$this->nom_tabla.'\');">');
		
		if (session::is_set('W_OUTPUT_RECNO_'.$this->nom_tabla)) {	
			$rec_no = session::get('W_OUTPUT_RECNO_'.$this->nom_tabla);	
			if ($rec_no==$ind) {
				session::un_set('W_OUTPUT_RECNO_'.$this->nom_tabla);	
				$temp->setVar("wo_registro.WO_TR_CSS", 'linea_selected');
			}
		}
		//////////////////
	}
	
  	function crear_fa_from_oc_comercial($cod_orden_compra_comercial) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_ORDEN_COMPRA FROM BIGGI_dbo_ORDEN_COMPRA WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no existe en Comercial.');								
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
					FROM BIGGI_dbo_ITEM_ORDEN_COMPRA 
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
			$result = $db->build_results($sql);
			if ($result[0]['CANT']==0) {
				$this->_redraw();
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' ya esta facturada');								
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
		}else if ($this->clicked_boton('b_printDTE', $value_boton))
			$this->printdte($value_boton);
		else if ($this->clicked_boton('b_xmlDTE', $value_boton))
			$this->xmldte($value_boton);
		else
			parent::procesa_event();
	}
	
	function printdte($rec_no){
		$es_cedible = $_POST['wo_hidden2'];
  		$wi = new wi_factura('cod_item_menu');
		$wi->current_record = $rec_no;
		$wi->load_record();
		$wi->imprimir_dte($es_cedible, true);
		$this->goto_page($this->current_page);
  	}
  	
	function xmldte($rec_no){
  		$wi = new wi_factura('cod_item_menu');
		$wi->current_record = $rec_no;
		$wi->load_record();
		$wi->xml_dte();
  	}
}
?>
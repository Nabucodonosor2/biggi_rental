<?php


////////////////////////////////////////
/////////// BODEGA_BIGGI ///////////////
////////////////////////////////////////
class wo_factura extends wo_factura_base {
	const K_EMPRESA_BODEGA_BIGGI = 9;
	const K_EMPRESA_COMERCIAL_BIGGI = 37;
	
	
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
		$this->add_header(new header_text('NRO_ORDEN_COMPRA', 'F.NRO_ORDEN_COMPRA', 'N° OC'));
	}

	function crear_fa_from_cot($cod_cotizacion) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'COTIZACION',C.COD_USUARIO_VENDEDOR1, C.COD_USUARIO_VENDEDOR2) TIENE_ACCESO
				FROM COTIZACION C 
				WHERE C.COD_COTIZACION = $cod_cotizacion";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La Cotización Nº '.$cod_cotizacion.' no existe.');								
			return;
		}
		else if ($result[0]['TIENE_ACCESO']==0){
			$this->_redraw();
			$this->alert('Ud. no tiene acceso a a Cotización Nº '.$cod_cotizacion);								
			return;
		}

		session::set('FA_CREADA_DESDE', $cod_cotizacion);
		$this->add();
	}
	function redraw(&$temp) {
  		if ($this->b_add_visible)
			$this->habilita_boton($temp, 'create', $this->get_privilegio_opcion_usuario($this->cod_item_menu, $this->cod_usuario)=='E');
	}
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='create') {
			if ($habilita)
				$temp->setVar("WO_CREATE", '<input name="b_create" id="b_create" src="../../../../commonlib/trunk/images/b_create.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_over.jpg\',1)" '.
											'onClick="return request_factura(\'Ingrese Nº de la Nota de Venta\',\'\');"'.
											'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../../../commonlib/trunk/images/b_create_d.jpg"/>');
		}
		else
			parent::habilita_boton($temp, $boton, $habilita);
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
	}
  	function crear_fa_from($valor_devuelto){
  		
  		$pos = strpos($valor_devuelto, '-');
	  	if ($pos!==false)
	  	{
	  		list($cod_nota_venta,$codigosGD )=split('[-]', $valor_devuelto);
			$opcion=substr($codigosGD, 0,9);
	  	}
		else
	  		list($opcion, $cod_nota_venta)=split('[|]', $valor_devuelto);

	  	$cantidad_max = $this->get_parametro(self::K_PARAM_MAX_IT_FA);
		if ($opcion=='desde_nv') {
				//crear la FA para todos los itemsNV que tengan pendiente por facturar
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				///valida que la NV exista
				$sql = "select * from NOTA_VENTA where COD_NOTA_VENTA = $cod_nota_venta";
				$result = $db->build_results($sql);
				if (count($result) == 0) {
					$this->_redraw();
					$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' no existe.');								
					return;
				}

				//valida que la NV este confirmada
				$sql = "select * from NOTA_VENTA 
						where COD_NOTA_VENTA = ".$cod_nota_venta." 
						and	COD_ESTADO_NOTA_VENTA IN (".self::K_ESTADO_CONFIRMADA.", ".self::K_ESTADO_CERRADA.")";
				$result = $db->build_results($sql);
				if (count($result) == 0) {
					$this->_redraw();
					$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' no esta confirmada.');								
					return;
				}

				/* valida que la NV no tenga FAs anteriores en estado = emitida
				ya que es suceptible a errores tener varias GD en estado emitida, ya que la cantidad por despachar 
				siempre será la misma cantidad de la NV.
				*/
				$sql = "select * from FACTURA
							where COD_DOC = $cod_nota_venta and
							COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA;
				$result = $db->build_results($sql);
				if (count($result) != 0) {
					$this->_redraw();
					$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' tiene Factura(s) pendientes(s) en estado emitido. Para poder generar más Facturas deberá imprimir los documentos emitidos.');						
					return;
				}

				//****************
				// valida que este pendiente de facturar
				$sql = "select dbo.f_nv_porc_facturado($cod_nota_venta ) PORC_FACTURA";
				$result = $db->build_results($sql);
				$porc_factura = $result[0]['PORC_FACTURA'];
				if ($porc_factura >= 100) { 
					$this->_redraw();
					$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' está totalmente Facturada.');								
					return;
				}
					
				if ($opcion=='desde_nv') {
					//cuenta cuantos items hay
					$sql_cuenta="select count(*) cantidad
									from ITEM_NOTA_VENTA IT, NOTA_VENTA NV
									where NV.COD_NOTA_VENTA = $cod_nota_venta and 
									NV.COD_NOTA_VENTA = IT.COD_NOTA_VENTA";
					$result_cuenta = $db->build_results($sql_cuenta);
					$cantidad = $result_cuenta[0]['cantidad'];
					$cant_fa_a_hacer=ceil($cantidad/$cantidad_max);
						
					$cod_usuario = $this->cod_usuario;	
								
					$sp = 'sp_fa_crear_desde_nv';
					$param = "$cod_nota_venta, $cod_usuario";
				}
				else if ($opcion=='desde_nv_anticipo') {
					$cant_fa_a_hacer = 1;
					$cod_usuario = $this->cod_usuario;	
								
					$sp = 'sp_fa_crear_desde_nv_anticipo';
					$param = "$cod_nota_venta, $cod_usuario";
				}
					
				
					
				$db->BEGIN_TRANSACTION();
				if ($db->EXECUTE_SP($sp, $param)) { 
					$db->COMMIT_TRANSACTION();
					$this->detalle_record_desde(true,$cant_fa_a_hacer);
				}
				else { 
					$db->ROLLBACK_TRANSACTION();
					$this->_redraw();
					$this->alert("No se pudo crear la factura. Error en 'sp_fa_crear_desde_nv', favor contacte a IntegraSystem.");
				}	
		}
		elseif($opcion=='desde_cot')  {	
			
				$cod_cotizacion = $cod_nota_venta;
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$sql = "SELECT dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'COTIZACION',C.COD_USUARIO_VENDEDOR1, C.COD_USUARIO_VENDEDOR2) TIENE_ACCESO
						FROM COTIZACION C 
						WHERE C.COD_COTIZACION = $cod_cotizacion";
				$result = $db->build_results($sql);
				if (count($result) == 0){
					$this->_redraw();
					$this->alert('La Cotización Nº '.$cod_cotizacion.' no existe.');								
					return;
				}
				else if ($result[0]['TIENE_ACCESO']==0){
					$this->_redraw();
					$this->alert('Ud. no tiene acceso a a Cotización Nº '.$cod_cotizacion);								
					return;
				}
		
				session::set('FA_CREADA_DESDE', $cod_cotizacion);
				$this->add();
		}elseif($opcion == 'desde_gd'){
			
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$codigos=substr($codigosGD, 10); //solo los codigos, sin valor de la opcion (venía primero en la variable)
			$codigos_gd=str_replace('|', ' ', $codigos);
			$codigos_gd2=str_replace('|', ',', $codigos);
			
			$largo=strlen($codigos_gd);
			
			$codigos_gd=substr($codigos_gd, 0,$largo -1);
			$codigos_gd2=substr($codigos_gd2, 0,$largo -1);
			
			// valida que hayan item pendiente por facturar.
				$sql_por_facturar = "select sum(dbo.f_nv_cant_por_facturar(IT.COD_ITEM_DOC, 'TODO_ESTADO')) POR_FACTURAR
				from ITEM_GUIA_DESPACHO IT, GUIA_DESPACHO GD
				where GD.COD_GUIA_DESPACHO = IT.COD_GUIA_DESPACHO
						AND GD.COD_DOC = $cod_nota_venta 
						AND GD.COD_GUIA_DESPACHO in ($codigos_gd2)
						AND IT.CANTIDAD > 0 
						AND IT.PRECIO > 0";
						
				$result = $db->build_results($sql_por_facturar);
				$por_facturarGd = $result[0]['POR_FACTURAR'];
				if ($por_facturarGd <= 0)
				{
						$this->_redraw();
						$this->alert('La Guía de despacho está totalmente Facturada.');								
						return;
				}
	
			//ver cuantos items tendré en todos esos GD
			$sql_items="select count(distinct(cod_item_doc)) cantidad from item_guia_despacho where COD_GUIA_DESPACHO in (".$codigos_gd2.")";
			$result_items = $db->build_results($sql_items);
			$cantidad= $result_items[0]['cantidad'];

			$cant_fa_a_hacer=ceil($cantidad/$cantidad_max);
			$db->BEGIN_TRANSACTION();
			$cod_usuario = $this->cod_usuario;	
			$codigos_gd="'".$codigos_gd."'";		
			$sp = 'sp_fa_crear_desde_gds';
			$param = "$cod_nota_venta, $codigos_gd, $cod_usuario";
			 	
			if ($db->EXECUTE_SP($sp, $param)) { 
				$db->COMMIT_TRANSACTION();
				$this->detalle_record_desde(true,$cant_fa_a_hacer);
			}
			else { 
				$db->ROLLBACK_TRANSACTION();
				$this->_redraw();
				$this->alert("No se pudo crear la factura. Error en 'sp_fa_crear_desde_gds', favor contacte a IntegraSystem.");
			}
						
		}
  	elseif($opcion == 'desde_comercial'){
		 $cod_orden_compra_comercial =$cod_nota_venta;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_ORDEN_COMPRA FROM OC_COMERCIAL WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no existe en Comercial.');								
			return;
		}else{
			//si existe la Oc determina el tipo
			$sql = "SELECT COD_ORDEN_COMPRA
							,COD_EMPRESA 
					FROM OC_COMERCIAL 
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial
						and TIPO_ORDEN_COMPRA IN ('NOTA_VENTA', 'BACKCHARGE')";

			$result = $db->build_results($sql);
			if (count($result) == 0){
				$this->_redraw();
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no es de tipo VENTA ó BACKCHARGE');								
				return;
			}
			else if ($result[0]['COD_EMPRESA'] != self::K_EMPRESA_COMERCIAL_BIGGI){
				$this->_redraw();
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no es para Comercial Biggi');								
				return;
			}

			//Verica si tiene items pendientes de facturar
			$sql = "SELECT isnull(sum(dbo.f_fa_OC_Comercial_por_facturar(COD_ITEM_ORDEN_COMPRA)), 0) CANT 
					FROM ITEM_OC_COMERCIAL
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
  	elseif($opcion == 'desde_bodega'){
		 $cod_orden_compra_comercial =$cod_nota_venta;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_ORDEN_COMPRA FROM OC_BODEGA WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no existe en Comercial.');								
			return;
		}else{
			//si existe la Oc determina el tipo
			$sql = "SELECT COD_ORDEN_COMPRA
							,COD_EMPRESA 
					FROM OC_BODEGA 
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
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no es para bodega Biggi');								
				return;
			}

			//Verica si tiene items pendientes de facturar
			$sql = "SELECT isnull(sum(dbo.f_fa_OC_Comercial_por_facturar(COD_ITEM_ORDEN_COMPRA)), 0) CANT 
					FROM ITEM_OC_BODEGA
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
			$result = $db->build_results($sql);
			if ($result[0]['CANT']==0) {
				$this->_redraw();
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' ya esta facturada');								
				return;
			}
			else {			
				session::set('FACTURA_DESDE_OC_BODEGA', $cod_orden_compra_comercial);
				$this->add();
	   		}
  		
		}
  	
  	 }
  	elseif($opcion == 'desde_servindus'){
		 $cod_orden_compra_comercial =$cod_nota_venta;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_ORDEN_COMPRA FROM OC_BODEGA WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no existe en Comercial.');								
			return;
		}else{
			//si existe la Oc determina el tipo
			$sql = "SELECT COD_ORDEN_COMPRA
							,COD_EMPRESA 
					FROM OC_SERVINDUS 
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
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no es para bodega Biggi');								
				return;
			}

			//Verica si tiene items pendientes de facturar
			$sql = "SELECT isnull(sum(dbo.f_fa_OC_Comercial_por_facturar(COD_ITEM_ORDEN_COMPRA)), 0) CANT 
					FROM ITEM_OC_SERVINDUS
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
			$result = $db->build_results($sql);
			if ($result[0]['CANT']==0) {
				$this->_redraw();
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' ya esta facturada');								
				return;
			}
			else {			
				session::set('FACTURA_DESDE_OC_BODEGA', $cod_orden_compra_comercial);
				$this->add();
	   		}
  		
		}
  	
  	 }
  	}	
	function procesa_event() {		
		if(isset($_POST['b_create_x'])) {
			$this->crear_fa_from($_POST['wo_hidden']);
		}else{
			parent::procesa_event();
		}
	}
 }
?>
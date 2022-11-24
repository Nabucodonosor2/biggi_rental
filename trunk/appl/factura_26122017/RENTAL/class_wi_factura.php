<?php
////////////////////////////////////////
/////////// RENTAL  ///////////////
////////////////////////////////////////
class dw_referencias extends datawindow {
	function dw_referencias() {
		
		$sql = "SELECT COD_REFERENCIA
				      ,CONVERT(VARCHAR, FECHA_REFERENCIA, 103) FECHA_REFERENCIA
				      ,DOC_REFERENCIA
				      ,COD_TIPO_REFERENCIA
				      ,COD_FACTURA
				FROM REFERENCIA
				WHERE COD_FACTURA = {KEY1}";
		
		parent::datawindow($sql, 'REFERENCIAS', true, true);
		
		// controls
		$this->add_control(new edit_date('FECHA_REFERENCIA'));
		$this->add_control($control = new edit_text('DOC_REFERENCIA', 20, 100));
		$control->set_onChange("valida_referencias(this);");
		
		$sql = "select COD_TIPO_REFERENCIA
						,NOM_TIPO_REFERENCIA
				from TIPO_REFERENCIA
				order by NOM_TIPO_REFERENCIA";
		$this->add_control($control = new drop_down_dw('COD_TIPO_REFERENCIA', $sql, 103));
		$control->set_onChange("valida_referencias(this);");

		// mandatory
		$this->set_mandatory('DOC_REFERENCIA', 'Doc. Referencia');
		$this->set_mandatory('COD_TIPO_REFERENCIA', 'Tipo Referencia');
	}
	function fill_template(&$temp) {
		parent::fill_template($temp);
		
		if($this->b_add_line_visible){
			if ($this->entrable){
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_ref(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
			}else 
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line_d.jpg">';
				
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);	
		}
	}
	
	
	function update($db){
		$sp = 'spu_referencia';
		
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
	
			$COD_REFERENCIA			= $this->get_item($i, 'COD_REFERENCIA');
			$FECHA_REFERENCIA		= $this->str2date($this->get_item($i, 'FECHA_REFERENCIA'));
			$DOC_REFERENCIA			= $this->get_item($i, 'DOC_REFERENCIA');
			$COD_TIPO_REFERENCIA	= $this->get_item($i, 'COD_TIPO_REFERENCIA');
			$COD_FACTURA			= $this->get_item($i, 'COD_FACTURA');
			
			$COD_REFERENCIA			= ($COD_REFERENCIA =='') ? "null" : $COD_REFERENCIA;							
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			else if ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';		
						
			$param = "'$operacion'
					,$COD_REFERENCIA
					,$FECHA_REFERENCIA
					,'$DOC_REFERENCIA'
					,$COD_TIPO_REFERENCIA
					,$COD_FACTURA";
			
			if(!$db->EXECUTE_SP($sp, $param))
				return false;
			else{
				if($statuts == K_ROW_NEW_MODIFIED) {
					$COD_REFERENCIA = $db->GET_IDENTITY();
					$this->set_item($i, 'COD_REFERENCIA', $COD_REFERENCIA);		
				}
			}
		}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_REFERENCIA = $this->get_item($i, 'COD_REFERENCIA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_REFERENCIA"))
				return false;
		}	
		return true;
	}
}

class wi_factura extends wi_factura_base {
	const K_BODEGA_TODOINOX = 1;
	const K_TIPO_FA_OC_COMERCIAL = 3;
	
	function wi_factura($cod_item_menu) {
		parent::wi_factura_base($cod_item_menu);
		
		$this->dws['dw_referencias'] = new dw_referencias();
	}
	
	function new_record() {
		parent::new_record();
		$this->dws['dw_factura']->set_item(0, 'GENERA_SALIDA', 'N');
		$this->dws['dw_factura']->set_entrable('GENERA_SALIDA', false);
        if (session::is_set("FACTURA_DESDE_OC_COMERCIAL")) {
            $cod_orden_compra_comercial = session::get("FACTURA_DESDE_OC_COMERCIAL");
            session::un_set("FACTURA_DESDE_OC_COMERCIAL");
            $this->crear_desde_oc_comercial($cod_orden_compra_comercial);
        }
	}
	function crear_desde_oc_comercial($cod_orden_compra_comercial) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select O.REFERENCIA
    					,convert(varchar, O.FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA           
        				,O.COD_NOTA_VENTA
				from BIGGI_dbo_ORDEN_COMPRA O
				where O.COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
        $result_oc = $db->build_results($sql);
        $cod_nota_venta_comercial = $result_oc[0]['COD_NOTA_VENTA']; 
        
        $sql = "select N.REFERENCIA
        				,U.INI_USUARIO
						,CC.NOM_CENTRO_COSTO 
				from BIGGI_dbo_NOTA_VENTA N,  BIGGI_dbo_USUARIO U, BIGGI_dbo_CENTRO_COSTO CC
				where N.COD_NOTA_VENTA = $cod_nota_venta_comercial
				  and U.COD_USUARIO = N.COD_USUARIO_VENDEDOR1
				  and CC.COD_CENTRO_COSTO = dbo.BIGGI_dbo_f_emp_get_cc(N.COD_EMPRESA)";
        $result_nv = $db->build_results($sql);
		$referencia_nv = $result_nv[0]['REFERENCIA'];
		$usuario_nv = $result_nv[0]['INI_USUARIO'];
		$nom_centro_costo_nv = $result_nv[0]['NOM_CENTRO_COSTO'];
		
		$sql = "select E.COD_EMPRESA
    					,E.ALIAS
    					,E.RUT
    					,E.DIG_VERIF
    					,E.NOM_EMPRESA
    					,E.GIRO
    					,S.COD_SUCURSAL
    					,dbo.f_get_direccion('SUCURSAL', S.COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA
    					,P.COD_PERSONA
		    			,dbo.f_emp_get_mail_cargo_persona(P.COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA
				from EMPRESA E, SUCURSAL S, PERSONA P
				where E.COD_EMPRESA = 1	-- COMERCIAL BIGGI
				  and S.COD_EMPRESA = E.COD_EMPRESA
				  and P.COD_PERSONA = 1";	//JJ
        $result_emp = $db->build_results($sql);

		$this->dws['dw_factura']->set_item(0, 'COD_EMPRESA', $result_emp[0]['COD_EMPRESA']);	
		$this->dws['dw_factura']->set_item(0, 'ALIAS', $result_emp[0]['ALIAS']);	
		$this->dws['dw_factura']->set_item(0, 'RUT', $result_emp[0]['RUT']);	
		$this->dws['dw_factura']->set_item(0, 'DIG_VERIF', $result_emp[0]['DIG_VERIF']);	
		$this->dws['dw_factura']->set_item(0, 'NOM_EMPRESA', $result_emp[0]['NOM_EMPRESA']);	
		$this->dws['dw_factura']->set_item(0, 'GIRO', $result_emp[0]['GIRO']);	

		$this->dws['dw_factura']->set_item(0, 'COD_SUCURSAL_FACTURA', $result_emp[0]['COD_SUCURSAL']);	
		$this->dws['dw_factura']->set_item(0, 'DIRECCION_FACTURA', $result_emp[0]['DIRECCION_FACTURA']);	
		$this->dws['dw_factura']->set_item(0, 'COD_PERSONA', $result_emp[0]['COD_PERSONA']);			
		$this->dws['dw_factura']->set_item(0, 'MAIL_CARGO_PERSONA', $result_emp[0]['MAIL_CARGO_PERSONA']);
		$referencia = "N/V $cod_nota_venta_comercial; $referencia_nv; $usuario_nv; CC: $nom_centro_costo_nv";	
		$this->dws['dw_factura']->set_item(0, 'REFERENCIA', $referencia);
		
		$this->dws['dw_factura']->set_item(0, 'NRO_ORDEN_COMPRA', $cod_orden_compra_comercial);
		$this->dws['dw_factura']->set_item(0, 'COD_DOC', $cod_orden_compra_comercial);
		$this->dws['dw_factura']->set_entrable('NRO_ORDEN_COMPRA', false);
		$this->dws['dw_factura']->set_item(0, 'FECHA_ORDEN_COMPRA_CLIENTE', $result_oc[0]['FECHA_ORDEN_COMPRA']);	
		$this->dws['dw_factura']->set_entrable('FECHA_ORDEN_COMPRA_CLIENTE', false);
		$this->dws['dw_factura']->set_item(0, 'COD_TIPO_FACTURA', self::K_TIPO_FA_OC_COMERCIAL);
		$this->dws['dw_factura']->set_item(0, 'TD_DISPLAY_CANT_POR_FACT', '');
		$this->dws['dw_factura']->set_item(0, 'COD_USUARIO_VENDEDOR1', 6);	//MMinguez
		$this->dws['dw_factura']->set_item(0, 'PORC_VENDEDOR1', 0);	
		
		$this->dws['dw_factura']->controls['COD_SUCURSAL_FACTURA']->retrieve($result_emp[0]['COD_EMPRESA']);
		$this->dws['dw_factura']->controls['COD_PERSONA']->retrieve($result_emp[0]['COD_EMPRESA']);


		////////////////////
		// items		
		$sql = "select I.ORDEN
    					,I.ITEM
		    			,I.COD_PRODUCTO
    					,I.NOM_PRODUCTO           
					    ,dbo.f_fa_OC_Comercial_por_facturar(I.COD_ITEM_ORDEN_COMPRA) CANTIDAD               
					    ,dbo.f_bodega_stock_cero(I.COD_PRODUCTO, ".self::K_BODEGA_TERMINADO.", getdate()) CANTIDAD_STOCK
					    ,P.PRECIO_VENTA_INTERNO PRECIO                 
    					,I.COD_ITEM_ORDEN_COMPRA
				from BIGGI_dbo_ITEM_ORDEN_COMPRA I, PRODUCTO P
				where I.COD_ORDEN_COMPRA = $cod_orden_compra_comercial
    			  and P.COD_PRODUCTO = I.COD_PRODUCTO
    			  and dbo.f_fa_OC_Comercial_por_facturar(I.COD_ITEM_ORDEN_COMPRA) > 0
				order by ORDEN";
        $result = $db->build_results($sql);
        $sum_total = 0;
        for ($i=0; $i<count($result); $i++) {
			$this->dws['dw_item_factura']->insert_row();
			$this->dws['dw_item_factura']->set_item($i, 'ORDEN', $result[$i]['ORDEN']);
        	
			$this->dws['dw_item_factura']->set_item($i, 'ITEM', $result[$i]['ITEM']);
			$this->dws['dw_item_factura']->set_item($i, 'COD_PRODUCTO', $result[$i]['COD_PRODUCTO']);
			$this->dws['dw_item_factura']->set_item($i, 'COD_PRODUCTO_OLD', $result[$i]['COD_PRODUCTO']);
			$this->dws['dw_item_factura']->set_item($i, 'NOM_PRODUCTO', $result[$i]['NOM_PRODUCTO']);
			if ($result[$i]['CANTIDAD'] > $result[$i]['CANTIDAD_STOCK'])
				$this->dws['dw_item_factura']->set_item($i, 'CANTIDAD', $result[$i]['CANTIDAD_STOCK']);
			else
				$this->dws['dw_item_factura']->set_item($i, 'CANTIDAD', $result[$i]['CANTIDAD']);
			$this->dws['dw_item_factura']->set_item($i, 'CANTIDAD_POR_FACTURAR', $result[$i]['CANTIDAD']);
			$this->dws['dw_item_factura']->set_item($i, 'PRECIO', $result[$i]['PRECIO']);
			$this->dws['dw_item_factura']->set_item($i, 'COD_ITEM_DOC', $result[$i]['COD_ITEM_ORDEN_COMPRA']);
			$this->dws['dw_item_factura']->set_item($i, 'TIPO_DOC', 'ITEM_ORDEN_COMPRA_COMERCIAL');
			$this->dws['dw_item_factura']->set_item($i, 'TD_DISPLAY_CANT_POR_FACT', '');
			$total = $result[$i]['CANTIDAD'] * $result[$i]['PRECIO'];
			$this->dws['dw_item_factura']->set_item($i, 'TOTAL', $total);
			$sum_total += $total;
        }
		$this->dws['dw_item_factura']->controls['ORDEN']->size = 3;
		$this->dws['dw_item_factura']->controls['ITEM']->size = 3;
		$this->dws['dw_item_factura']->controls['COD_PRODUCTO']->size = 20;
		$this->dws['dw_item_factura']->controls['NOM_PRODUCTO']->size = 45;
		
		$this->dws['dw_item_factura']->calc_computed();
		
		$this->dws['dw_factura']->set_item(0, 'SUM_TOTAL', $sum_total);
		$this->dws['dw_factura']->set_item(0, 'PORC_IVA', $this->get_parametro(1));	// IVA
		
		$this->dws['dw_factura']->calc_computed();
	}
	function load_record() {
		parent::load_record();
		$this->dws['dw_factura']->set_entrable('GENERA_SALIDA', false);
		
		// cambia el COD_NOTA_VENTA por el nro de NV
		$this->dws['dw_factura']->add_field('COD_NOTA_VENTA');
		$cod_factura = $this->dws['dw_factura']->get_item(0, 'COD_FACTURA');
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select dbo.f_fa_NV_COMERCIAL(COD_FACTURA) COD_NOTA_VENTA
				from FACTURA
				where COD_FACTURA = $cod_factura";
		$result = $db->build_results($sql);
		$this->dws['dw_factura']->set_item(0, 'COD_NOTA_VENTA', $result[0]['COD_NOTA_VENTA']);
		
		$this->dws['dw_referencias']->retrieve($cod_factura);
	}
	function envia_FA_electronica($con_alerta=true){
			if (!$this->lock_record())
				return false;
			if(!session::is_set("FA_ARRIENDO")){
				$COD_ESTADO_DOC_SII = $this->dws['dw_factura']->get_item(0, 'COD_ESTADO_DOC_SII');
				session::un_set("FA_ARRIENDO");
				if($COD_ESTADO_DOC_SII == 1){//Emitida
					/////////// reclacula la FA porsiaca
					$parametros_sp = "'RECALCULA',$cod_factura";   
					$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
					$db->EXECUTE_SP('spu_factura', $parametros_sp);
		            /////////
				}	
			}	
			$this->sepa_decimales	= ',';	//Usar , como separador de decimales
			$this->vacio 			= ' ';	//Usar rellenos de blanco, CAMPO ALFANUMERICO
			$this->llena_cero		= 0;	//Usar rellenos con '0', CAMPO NUMERICO
			$this->separador		= ';';	//Usar ; como separador de campos
			$cod_factura = $this->get_key();
			$cod_usuario_impresion = $this->cod_usuario;
			$CMR = 9;
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$cod_impresora_dte = $_POST['wi_impresora_dte'];
			if($cod_impresora_dte == 100){
				$emisor_factura = 'SALA VENTA';
			}else{
				
			if ($cod_impresora_dte == '')
				$sql = "SELECT U.NOM_USUARIO EMISOR_FACTURA
						FROM USUARIO U, FACTURA F
						WHERE F.COD_FACTURA = $cod_factura
						  and U.COD_USUARIO = $cod_usuario_impresion";
			else
				$sql = "SELECT NOM_REGLA EMISOR_FACTURA
						FROM IMPRESORA_DTE
						WHERE COD_IMPRESORA_DTE = $cod_impresora_dte";
						
			$result = $db->build_results($sql);
			$emisor_factura = $result[0]['EMISOR_FACTURA'] ;
			}
			
			$db->BEGIN_TRANSACTION();
			$sp = 'spu_factura';
			$param = "'ENVIA_DTE', $cod_factura, $cod_usuario_impresion";

			if ($db->EXECUTE_SP($sp, $param)) {
				//$db->COMMIT_TRANSACTION();
				
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				//declrar constante para que el monto con iva del reporte lo transpforme a palabras
				$sql_total = "select TOTAL_CON_IVA from FACTURA where COD_FACTURA = $cod_factura";
				$resul_total = $db->build_results($sql_total);
				$total_con_iva = $resul_total[0]['TOTAL_CON_IVA'] ;
				$total_en_palabras =  Numbers_Words::toWords($total_con_iva,"es"); 
				$total_en_palabras = strtr($total_en_palabras, "�����", "aeiou");
				$total_en_palabras = strtoupper($total_en_palabras);
				
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$sql_dte = "SELECT	F.COD_FACTURA,
									F.NRO_FACTURA,
									F.TIPO_DOC,
									dbo.f_format_date(FECHA_FACTURA,1)FECHA_FACTURA,
									F.COD_USUARIO_IMPRESION,
									'$emisor_factura' EMISOR_FACTURA,
									F.NRO_ORDEN_COMPRA,
									dbo.f_fa_nros_guia_despacho(".$cod_factura.") NRO_GUIAS_DESPACHO,	
									F.REFERENCIA,
									F.NOM_EMPRESA,
									F.GIRO,
									F.RUT,
									F.DIG_VERIF,
									F.DIRECCION,
									dbo.f_emp_get_mail_cargo_persona(F.COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA,
									F.TELEFONO,
									F.FAX,
									F.COD_DOC,
									F.SUBTOTAL,
									F.PORC_DSCTO1,
									F.MONTO_DSCTO1,
									F.PORC_DSCTO2,
									F.MONTO_DSCTO2,
									F.MONTO_DSCTO1 + F.MONTO_DSCTO2 TOTAL_DSCTO,
									F.TOTAL_NETO,
									F.PORC_IVA,
									F.MONTO_IVA,
									F.TOTAL_CON_IVA,
									F.RETIRADO_POR,
									F.RUT_RETIRADO_POR,
									F.DIG_VERIF_RETIRADO_POR,
									COM.NOM_COMUNA,
									CIU.NOM_CIUDAD,
									FP.NOM_FORMA_PAGO,
									FP.COD_PAGO_DTE,
									F.NOM_FORMA_PAGO_OTRO,
									ITF.COD_ITEM_FACTURA,
									ITF.ORDEN,								
									ITF.ITEM,
									ITF.CANTIDAD,
									ITF.COD_PRODUCTO,
									ITF.NOM_PRODUCTO,
									ITF.PRECIO,
									ITF.PRECIO * ITF.CANTIDAD  TOTAL_FA,
									'".$total_en_palabras."' TOTAL_EN_PALABRAS,
									convert(varchar(5), GETDATE(), 8) HORA,
									F.GENERA_SALIDA,
									F.OBS,
									F.CANCELADA,
									F.COD_TIPO_FACTURA
							FROM 	FACTURA F left outer join COMUNA COM on F.COD_COMUNA = COM.COD_COMUNA,
									ITEM_FACTURA ITF, CIUDAD CIU, FORMA_PAGO FP 
							WHERE 	F.COD_FACTURA = ".$cod_factura." 
							AND	ITF.COD_FACTURA = F.COD_FACTURA
							AND	CIU.COD_CIUDAD = F.COD_CIUDAD
							AND	FP.COD_FORMA_PAGO = F.COD_FORMA_PAGO";
				$result_dte = $db->build_results($sql_dte);
				//CANTIDAD DE ITEM_FACTURA 
				$count = count($result_dte);
				
				// datos de factura
				$NRO_FACTURA		= $result_dte[0]['NRO_FACTURA'] ;		// 1 Numero Factura
				$FECHA_FACTURA		= $result_dte[0]['FECHA_FACTURA'] ;		// 2 Fecha Factura
				//Email - VE: =>En el caso de las Factura y otros documentos, no aplica por lo que se dejan 0;0 
				$TD					= $this->llena_cero;					// 3 Tipo Despacho
				$TT					= $this->llena_cero;					// 4 Tipo Traslado
				//Email - VE: => 
				$PAGO_DTE			= $result_dte[0]['COD_PAGO_DTE'];		// 5 Forma de Pago
				$FV					= $this->vacio;							// 6 Fecha Vencimiento
				$RUT				= $result_dte[0]['RUT'];				
				$DIG_VERIF			= $result_dte[0]['DIG_VERIF'];
				$RUT_EMPRESA		= $RUT.'-'.$DIG_VERIF;					// 7 Rut Empresa
				$NOM_EMPRESA		= $result_dte[0]['NOM_EMPRESA'] ;		// 8 Razol Social_Nombre Empresa
				$GIRO				= $result_dte[0]['GIRO'];				// 9 Giro Empresa
				$DIRECCION			= $result_dte[0]['DIRECCION'];			//10 Direccion empresa
				$MAIL_CARGO_PERSONA	= $result_dte[0]['MAIL_CARGO_PERSONA'];	//11 E-Mail Contacto
				$TELEFONO			= $result_dte[0]['TELEFONO'];			//12 Telefono Empresa
				$REFERENCIA			= $result_dte[0]['REFERENCIA'];			//12 Referencia de la Factura  //datos olvidado por VE.
				$NRO_GUIA_DESPACHO	= $result_dte[0]['NRO_GUIAS_DESPACHO'];	//Solicitado a VE por SP
				$GENERA_SALIDA		= $result_dte[0]['GENERA_SALIDA'];		//Solicitado a VE por SP "DESPACHADO"
				if ($GENERA_SALIDA == 'S'){
					$GENERA_SALIDA = 'DESPACHADO';
				}else{
					$GENERA_SALIDA = '';
				}
				$CANCELADA			= $result_dte[0]['CANCELADA'];			//Solicitado a VE por SP "CANCELADO"
				if ($CANCELADA == 'S'){
					$CANCELADA = 'CANCELADA';
				}else{
					$CANCELADA = '';
				}
				$SUBTOTAL			= number_format($result_dte[0]['SUBTOTAL'], 1, ',', '');	//Solicitado a VE por SP "SUBTOTAL"
				$PORC_DSCTO1		= number_format($result_dte[0]['PORC_DSCTO1'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO1"
				$PORC_DSCTO2		= number_format($result_dte[0]['PORC_DSCTO2'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO2"
				$EMISOR_FACTURA		= $result_dte[0]['EMISOR_FACTURA'];		//Solicitado a VE por SP "EMISOR_FACTURA"
				$NOM_COMUNA			= $result_dte[0]['NOM_COMUNA'];			//13 Comuna Recepcion
				$NOM_CIUDAD			= $result_dte[0]['NOM_CIUDAD'];			//14 Ciudad Recepcion
				$DP					= $result_dte[0]['DIRECCION'];			//15 Direcci�n Postal
				$COP				= $result_dte[0]['NOM_COMUNA'];			//16 Comuna Postal
				$CIP				= $result_dte[0]['NOM_CIUDAD'];			//17 Ciudad Postal
				
				//DATOS DE TOTALES number_format($result_dte[$i]['TOTAL_FA'], 0, ',', '.');
				$TOTAL_NETO			= number_format($result_dte[0]['TOTAL_NETO'], 1, ',', '');		//18 Monto Neto
				$PORC_IVA			= number_format($result_dte[0]['PORC_IVA'], 1, ',', '');		//19 Tasa IVA
				$MONTO_IVA			= number_format($result_dte[0]['MONTO_IVA'], 1, ',', '');		//20 Monto IVA
				$TOTAL_CON_IVA		= number_format($result_dte[0]['TOTAL_CON_IVA'], 1, ',', '');	//21 Monto Total
				$D1					= 'D1';															//22 Tipo de Mov 1 (Desc/Rec)
				$P1					= '$';															//23 Tipo de valor de Desc/Rec 1
				$MONTO_DSCTO1		= number_format($result_dte[0]['MONTO_DSCTO1'], 1, ',', '');	//24 Valor del Desc/Rec 1
				$D2					= 'D2';															//25 Tipo de Mov 2 (Desc/Rec)
				$P2					= '$';															//26 Tipo de valor de Desc/Rec 2
				$MONTO_DSCTO2		= number_format($result_dte[0]['MONTO_DSCTO2'], 1, ',', '');	//27 Valor del Desc/Rec 2
				$D3					= 'D3';															//28 Tipo de Mov 3 (Desc/Rec)
				$P3					= '$';															//29 Tipo de valor de Desc/Rec 3
				$MONTO_DSCTO3		= '';															//30 Valor del Desc/Rec 3
				$NOM_FORMA_PAGO		= $result_dte[0]['NOM_FORMA_PAGO'];								//Dato Especial forma de pago adicional
				$NRO_ORDEN_COMPRA	= $result_dte[0]['NRO_ORDEN_COMPRA'];							//Numero de Orden Pago
				$NRO_NOTA_VENTA		= $result_dte[0]['COD_DOC'];									//Numero de Nota Venta
				$OBSERVACIONES		= $result_dte[0]['OBS'];										//si la factura tiene notas u observaciones
				$OBSERVACIONES		=  eregi_replace("[\n|\r|\n\r]", ' ', $OBSERVACIONES); //elimina los saltos de linea. entre otros caracteres
				$TOTAL_EN_PALABRAS	= $result_dte[0]['TOTAL_EN_PALABRAS'];							//Total en palabras: Posterior al campo Notas
	
				//GENERA EL NOMBRE DEL ARCHIVO
				if($PORC_IVA != 0){
					$TIPO_FACT = 33;	//FACTURA AFECTA
				}else{
					$TIPO_FACT = 34;	//FACTURA EXENTA
				}
	
				//GENERA EL ALFANUMERICO ALETORIO Y LLENA LA VARIABLE $RES = ALETORIO
				$length = 36;
				$source = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$source .= '1234567890';
				
				if($length>0){
			        $RES = "";
			        $source = str_split($source,1);
			        for($i=1; $i<=$length; $i++){
			            mt_srand((double)microtime() * 1000000);
			            $num	= mt_rand(1,count($source));
			            $RES	.= $source[$num-1];
			        }
				 
			    }			
				
				//GENERA ESPACIOS EN BLANCO
				$space = ' ';
				$i = 0; 
				while($i<=100){
					$space .= ' ';
				$i++;
				}
				
				//GENERA ESPACIOS CON CEROS
				$llena_cero = 0;
				$i = 0; 
				while($i<=100){
					$llena_cero .= 0;
				$i++;
				}
				
				//Asignando espacios en blanco Factura
				//LINEA 3
				$NRO_FACTURA	= substr($NRO_FACTURA.$space, 0, 10);		// 1 Numero Factura
				$FECHA_FACTURA	= substr($FECHA_FACTURA.$space, 0, 10);		// 2 Fecha Factura
				$TD				= substr($TD.$space, 0, 1);					// 3 Tipo Despacho
				$TT				= substr($TT.$space, 0, 1);					// 4 Tipo Traslado
				$PAGO_DTE		= substr($PAGO_DTE.$space, 0, 1);			// 5 Forma de Pago
				$FV				= substr($FV.$space, 0, 10);				// 6 Fecha Vencimiento
				$RUT_EMPRESA	= substr($RUT_EMPRESA.$space, 0, 10);		// 7 Rut Empresa
				$NOM_EMPRESA	= substr($NOM_EMPRESA.$space, 0, 100);		// 8 Razol Social_Nombre Empresa
				$GIRO			= substr($GIRO.$space, 0, 40);				// 9 Giro Empresa
				$DIRECCION		= substr($DIRECCION.$space, 0, 60);			//10 Direccion empresa
				$MAIL_CARGO_PERSONA = substr($MAIL_CARGO_PERSONA.$space, 0, 60);//11 E-Mail Contacto
				$TELEFONO		= substr($TELEFONO.$space, 0, 15);			//12 Telefono Empresa
				$REFERENCIA		= substr($REFERENCIA.$space, 0, 80);
				$NRO_GUIA_DESPACHO	= substr($NRO_GUIA_DESPACHO.$space, 0, 20);//Solicitado a VE por SP
				$GENERA_SALIDA	= substr($GENERA_SALIDA.$space, 0, 30);		//DESPACHADO
				$CANCELADA		= substr($CANCELADA.$space, 0, 30);			//CANCELADO
				$SUBTOTAL		= substr($SUBTOTAL.$space, 0, 18);			//Solicitado a VE por SP "SUBTOTAL"
				$PORC_DSCTO1	= substr($PORC_DSCTO1.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO1"
				$PORC_DSCTO2	= substr($PORC_DSCTO2.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO2"
				$EMISOR_FACTURA	= substr($EMISOR_FACTURA.$space, 0, 50);	//Solicitado a VE por SP "EMISOR_FACTURA"
				//LINEA4
				$NOM_COMUNA		= substr($NOM_COMUNA.$space, 0, 20);		//13 Comuna Recepcion
				$NOM_CIUDAD		= substr($NOM_CIUDAD.$space, 0, 20);		//14 Ciudad Recepcion
				$DP				= substr($DP.$space, 0, 60);				//15 Direcci�n Postal
				$COP			= substr($COP.$space, 0, 20);				//16 Comuna Postal
				$CIP			= substr($CIP.$space, 0, 20);				//17 Ciudad Postal
	
				//Asignando espacios en blanco Totales de Factura
				$TOTAL_NETO		= substr($TOTAL_NETO.$space, 0, 18);		//18 Monto Neto
				$PORC_IVA		= substr($PORC_IVA.$space, 0, 5);			//19 Tasa IVA
				$MONTO_IVA		= substr($MONTO_IVA.$space, 0, 18);			//20 Monto IVA
				$TOTAL_CON_IVA	= substr($TOTAL_CON_IVA.$space, 0, 18);		//21 Monto Total
				$D1				= substr($D1.$space, 0, 1);					//22 Tipo de Mov 1 (Desc/Rec)
				$P1				= substr($P1.$space, 0, 1);					//23 Tipo de valor de Desc/Rec 1
				$MONTO_DSCTO1	= substr($MONTO_DSCTO1.$space, 0, 18);		//24 Valor del Desc/Rec 1
				$D2				= substr($D2.$space, 0, 1);					//25 Tipo de Mov 2 (Desc/Rec)
				$P2				= substr($P2.$space, 0, 1);					//26 Tipo de valor de Desc/Rec 2
				$MONTO_DSCTO2	= substr($MONTO_DSCTO2.$space, 0, 18);		//27 Valor del Desc/Rec 2
				$D3				= substr($D3.$space, 0, 1);					//28 Tipo de Mov 3 (Desc/Rec)
				$P3				= substr($P3.$space, 0, 1);					//29 Tipo de valor de Desc/Rec 3
				$MONTO_DSCTO3	= substr($MONTO_DSCTO3.$space, 0, 18);		//30 Valor del Desc/Rec 3
				$NOM_FORMA_PAGO = substr($NOM_FORMA_PAGO.$space, 0, 80);	//Dato Especial forma de pago adicional
				$NRO_ORDEN_COMPRA= substr($NRO_ORDEN_COMPRA.$space, 0, 20);	//Numero de Orden Pago
				$NRO_NOTA_VENTA = substr($NRO_NOTA_VENTA.$space, 0, 20);	//Numero de Nota Venta
				$OBSERVACIONES = substr($OBSERVACIONES.$space.$space.$space, 0, 250); //si la factura tiene notas u observaciones
				$TOTAL_EN_PALABRAS = substr($TOTAL_EN_PALABRAS.' PESOS.'.$space.$space, 0, 200);	//Total en palabras: Posterior al campo Notas
				
				$name_archivo = $TIPO_FACT."_NPG_".$RES.".SPF";
				$fname = tempnam("/tmp", $name_archivo);
				$handle = fopen($fname,"w");
				//DATOS DE FACTURA A EXPORTAR 
				//linea 1 y 2
				fwrite($handle, "\r\n"); //salto de linea
				fwrite($handle, "\r\n"); //salto de linea
				//linea 3		
				fwrite($handle, ' ');									// 0 space 2
				fwrite($handle, $NRO_FACTURA.$this->separador);			// 1 Numero Factura
				fwrite($handle, $FECHA_FACTURA.$this->separador);		// 2 Fecha Factura
				fwrite($handle, $TD.$this->separador);					// 3 Tipo Despacho
				fwrite($handle, $TT.$this->separador);					// 4 Tipo Traslado
				fwrite($handle, $PAGO_DTE.$this->separador);			// 5 Forma de Pago
				fwrite($handle, $FV.$this->separador);					// 6 Fecha Vencimiento
				fwrite($handle, $RUT_EMPRESA.$this->separador);			// 7 Rut Empresa
				fwrite($handle, $NOM_EMPRESA.$this->separador);			// 8 Razol Social_Nombre Empresa
				fwrite($handle, $GIRO.$this->separador);				// 9 Giro Empresa
				fwrite($handle, $DIRECCION.$this->separador);			//10 Direccion empresa
				//Personalizados Linea 3
				fwrite($handle, $MAIL_CARGO_PERSONA.$this->separador);	//11 E-Mail Contacto 
				fwrite($handle, $TELEFONO.$this->separador);			//12 Telefono Empresa
				fwrite($handle, $REFERENCIA.$this->separador);			//Referencia de la Factura
				fwrite($handle, $NRO_GUIA_DESPACHO.$this->separador);	//Solicitado a VE por SP
				fwrite($handle, $GENERA_SALIDA.$this->separador);		//DESPACHADO Solicitado a VE por SP
				fwrite($handle, $CANCELADA.$this->separador);			//CANCELADO Solicitado a VE por SP
				fwrite($handle, $SUBTOTAL.$this->separador);			//Solicitado a VE por SP "SUBTOTAL"
				fwrite($handle, $PORC_DSCTO1.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO1"
				fwrite($handle, $PORC_DSCTO2.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO2"
				fwrite($handle, $EMISOR_FACTURA.$this->separador);		//Solicitado a VE por SP "EMISOR_FACTURA"
				fwrite($handle, "\r\n"); //salto de linea
				
				//linea 4
				fwrite($handle, ' ');									// 0 space 2
				fwrite($handle, $NOM_COMUNA.$this->separador);			//13 Comuna Recepcion
				fwrite($handle, $NOM_CIUDAD.$this->separador);			//14 Ciudad Recepcion
				fwrite($handle, $DP.$this->separador);					//15 Direcci�n Postal
				fwrite($handle, $COP.$this->separador);					//16 Comuna Postal
				fwrite($handle, $CIP.$this->separador);					//17 Ciudad Postal
				fwrite($handle, $TOTAL_NETO.$this->separador);			//18 Monto Neto
				fwrite($handle, $PORC_IVA.$this->separador);			//19 Tasa IVA
				fwrite($handle, $MONTO_IVA.$this->separador);			//20 Monto IVA
				fwrite($handle, $TOTAL_CON_IVA.$this->separador);		//21 Monto Total
				fwrite($handle, $D1.$this->separador);					//22 Tipo de Mov 1 (Desc/Rec)
				fwrite($handle, $P1.$this->separador);					//23 Tipo de valor de Desc/Rec 1
				fwrite($handle, $MONTO_DSCTO1.$this->separador);		//24 Valor del Desc/Rec 1
				fwrite($handle, $D2.$this->separador);					//25 Tipo de Mov 2 (Desc/Rec)
				fwrite($handle, $P2.$this->separador);					//26 Tipo de valor de Desc/Rec 2
				fwrite($handle, $MONTO_DSCTO2.$this->separador);		//27 Valor del Desc/Rec 2
				fwrite($handle, $D3.$this->separador);					//28 Tipo de Mov 3 (Desc/Rec)
				fwrite($handle, $P3.$this->separador);					//29 Tipo de valor de Desc/Rec 3			
				fwrite($handle, $MONTO_DSCTO3.$this->separador);		//30 Valor del Desc/Rec 2
				fwrite($handle, $NOM_FORMA_PAGO.$this->separador);		//Dato Especial forma de pago adicional
				fwrite($handle, $NRO_ORDEN_COMPRA.$this->separador);	//Numero de Orden Pago
				fwrite($handle, $NRO_NOTA_VENTA.$this->separador);		//Numero de Nota Venta
				fwrite($handle, $OBSERVACIONES.$this->separador);		//si la factura tiene notas u observaciones
				fwrite($handle, $TOTAL_EN_PALABRAS.$this->separador);	//Total en palabras: Posterior al campo Notas
				fwrite($handle, "\r\n"); //salto de linea
				
				///////////////////////
				// DTE de Rental imprime solo una linea
				// para las FA de arriendo la cantidad es siempre 1
				if ($result_dte[0]['COD_TIPO_FACTURA']==2) {
					$count = 1;
					$sql_aux = "exec spdw_factura_print $cod_factura, 'PRINT', $cod_usuario_impresion, '$TOTAL_EN_PALABRAS'";
					$result_aux = $db->build_results($sql_aux);
					
					$result_dte[0]['ORDEN'] = 10;	
					$result_dte[0]['COD_PRODUCTO'] = $result_aux[0]['COD_PRODUCTO'];
					$result_dte[0]['NOM_PRODUCTO'] = $result_aux[0]['NOM_PRODUCTO'];
					$result_dte[0]['CANTIDAD'] = 1;
					$result_dte[0]['PRECIO'] = $result_aux[0]['PRECIO'];
					$result_dte[0]['TOTAL_FA'] = $result_aux[0]['TOTAL_FA'];
				}
				///////////////////////
				
				//datos de dw_item_factura linea 5 a 34
				for ($i = 0; $i < 30; $i++){
					if($i < $count){
						fwrite($handle, ' '); //0 space 2
						$ORDEN		= $result_dte[$i]['ORDEN'];	
						$MODELO		= $result_dte[$i]['COD_PRODUCTO'];
						$NOM_PRODUCTO = substr($result_dte[$i]['NOM_PRODUCTO'], 0, 45);
						$CANTIDAD	= number_format($result_dte[$i]['CANTIDAD'], 1, ',', '');
						$P_UNITARIO	= number_format($result_dte[$i]['PRECIO'], 1, ',', '');
						$TOTAL		= number_format($result_dte[$i]['TOTAL_FA'], 1, ',', '');
						$DESCRIPCION= $MODELO; // se repite el modelo
						$CANTIDAD_DETALLE = $CANTIDAD; // se repite el $CANTIDAD
						
						//Asignando espacios en blanco dw_item_factura
						$ORDEN = $ORDEN / 10; //ELIMINA EL CERO
						$ORDEN		= substr($ORDEN.$space, 0, 2);
						$MODELO		= substr($MODELO.$space, 0, 35);
						$NOM_PRODUCTO= substr($NOM_PRODUCTO.$space, 0, 80);
						$CANTIDAD	= substr($CANTIDAD.$space, 0, 18);
						$P_UNITARIO	= substr($P_UNITARIO.$space, 0, 18);
						$TOTAL		= substr($TOTAL.$space, 0, 18);
						$DESCRIPCION= substr($DESCRIPCION.$space, 0, 59);
						$CANTIDAD_DETALLE = substr($CANTIDAD_DETALLE.$space, 0, 18);
	
						//DATOS DE ITEM_FACTURA A EXPORTAR
						fwrite($handle, $ORDEN.$this->separador);		//31 N�mero de L�nea
						fwrite($handle, $MODELO.$this->separador);		//32 C�digo item
						fwrite($handle, $NOM_PRODUCTO.$this->separador);//33 Nombre del Item
						fwrite($handle, $CANTIDAD.$this->separador);	//34 Cantidad
						fwrite($handle, $P_UNITARIO.$this->separador);	//35 Precio Unitario
						fwrite($handle, $TOTAL.$this->separador);		//36 Valor por linea de detalle
						fwrite($handle, $DESCRIPCION.$this->separador);	//37 personalizados Zona Detalles(Modelo �tem)
						fwrite($handle, $CANTIDAD_DETALLE.$this->separador);	//personalizados Zona Detalles SE REPITE $CANTIDAD
					}
					fwrite($handle, "\r\n");
				}
				
				//LINEA 35 SOLICITU DE V ESPINOIZA FA MINERAS
				$sql_ref = "SELECT	 NRO_ORDEN_COMPRA
									,CONVERT(VARCHAR(10), FECHA_ORDEN_COMPRA_CLIENTE ,103) FECHA_OC
							FROM 	FACTURA 
							WHERE 	COD_FACTURA = $cod_factura";
				
				$result_ref = $db->build_results($sql_ref);
				$NRO_OC_FACTURA	= $result_ref[0]['NRO_ORDEN_COMPRA'];
				$FECHA_REF_OC	= $result_ref[0]['FECHA_OC'];
				
				//($a == $b) && ($c > $b)
				if(($NRO_OC_FACTURA == '') or ($FECHA_REF_OC == '')){
					//no existe OC en factura
					//Linea 36 a 44	Referencia
					$TDR	= $this->llena_cero;
					$FR		= $this->llena_cero;
					$FECHA_R= $this->vacio;
					$CR		= $this->llena_cero;
					$RER	= $this->vacio;
					
					//Asignando espacios en blanco Referencia
					$TDR	= substr($TDR.$space, 0, 3);
					$FR		= substr($FR.$space, 0, 18);
					$FECHA_R= substr($FECHA_R.$space, 0, 10);
					$CR		= substr($CR.$space, 0, 1);
					$RER	= substr($RER.$space, 0, 100);					
					
					fwrite($handle, ' '); //0 space 2
					fwrite($handle, $TDR.$this->separador);			//38 Tipo documento referencia
					fwrite($handle, $FR.$this->separador);			//39 Folio Referencia
					fwrite($handle, $FECHA_R.$this->separador);		//40 Fecha de Referencia
					fwrite($handle, $CR.$this->separador);			//41 C�digo de Referencia
					fwrite($handle, $RER.$this->separador);			//42 Raz�n expl�cita de la referencia
				}else{
					$TIPO_COD_REF		= '801';
					$NRO_OC_FACTURA		= $result_ref[0]['NRO_ORDEN_COMPRA'];	
					$FECHA_REF_OC		= $result_ref[0]['FECHA_OC'];
					$CR					= '1';
					$RAZON_REF_OC		= 'ORDEN DE COMPRA';
					
					$TIPO_COD_REF	= substr($TIPO_COD_REF.$space, 0, 3);
					$NRO_OC_FACTURA	= substr($NRO_OC_FACTURA.$space, 0, 18);
					$FECHA_REF_OC	= substr($FECHA_REF_OC.$space, 0, 10);
					$CR				= substr($CR.$space, 0, 1);
					$RAZON_REF_OC	= substr($RAZON_REF_OC.$space, 0, 100);
					
					fwrite($handle, ' '); //0 space 2
					fwrite($handle, $TIPO_COD_REF.$this->separador);			//TIPOCODREF. SOLI 
					fwrite($handle, $NRO_OC_FACTURA.$this->separador);			//FOLIOREF......Folio Referencia
					fwrite($handle, $FECHA_REF_OC.$this->separador);			//FECHA OC C�digo de Referencia
					fwrite($handle, $CR.$this->separador);						//41 C�digo de Referencia
					fwrite($handle, $RAZON_REF_OC.$this->separador);			//RAZON  KJNSK... Raz�n expl�cita de la referencia
				}
				fclose($handle);
				/*
				header("Content-Type: application/x-msexcel; name=\"$name_archivo\"");
				header("Content-Disposition: inline; filename=\"$name_archivo\"");
				$fh=fopen($fname, "rb");
				fpassthru($fh);*/
				
				$upload = $this->Envia_DTE($name_archivo, $fname);
				$NRO_FACTURA	= trim($NRO_FACTURA);
				if (!$upload) {
					$db->ROLLBACK_TRANSACTION();
					$this->_load_record();
					//$this->alert('No se pudo enviar Fatura Electronica N� '.$NRO_FACTURA.', Por favor contacte a IntegraSystem.');
					$this->alert('No se pudo enviar Fatura Electronica , Por favor contacte a IntegraSystem.');
													
				}else{
					$db->COMMIT_TRANSACTION();
					$this->_load_record();
					if ($con_alerta) {
						if ($PORC_IVA == 0){
							$this->alert('Gesti�n Realizada con ex�to. Factura Exenta Electronica N� '.$NRO_FACTURA.'.');
						}else{
							$this->alert('Gesti�n Realizada con ex�to. Factura Electronica N� '.$NRO_FACTURA.'.');
						}
					}								
				}
				unlink($fname);
			}else{
				$db->ROLLBACK_TRANSACTION();
				return false;
			}
			$this->unlock_record();
		}
}
class print_factura extends print_factura_base {	
	function print_factura($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::print_factura_base($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
	}			
	///////////FACTURA CON IVA BODEGA BIGGI/////////////////////////////////////////
	function print_con_iva_fa_Bodega_Biggi(&$pdf, $x, $y) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		$count = count($result);
		
		$fecha = $result[0]['FECHA_FACTURA'];		
		// CABECERA		
		$cod_factura = $result[0]['COD_FACTURA'];		
		$nro_factura = $result[0]['NRO_FACTURA'];
		$nom_empresa = $result[0]['NOM_EMPRESA'];
		$rut = number_format($result[0]['RUT'], 0, ',', '.')."-".$result[0]['DIG_VERIF'];
		$oc = $result[0]['NRO_ORDEN_COMPRA'];
		$direccion = $result[0]['DIRECCION'];
		$comuna = $result[0]['NOM_COMUNA'];		
		$ciudad = $result[0]['NOM_CIUDAD'];		
		$giro = $result[0]['GIRO'];
		
		$fono = $result[0]['TELEFONO'];
		$total_en_palabras = $result[0]['TOTAL_EN_PALABRAS'];
		
		$subtotal = number_format($result[0]['SUBTOTAL'], 0, ',', '.');
		$porc_dscto1 = number_format($result[0]['PORC_DSCTO1'], 1, ',', '.');
		$monto_dscto1 = number_format($result[0]['MONTO_DSCTO1'], 0, ',', '.');
		$porc_dscto2 = number_format($result[0]['PORC_DSCTO2'], 1, ',', '.');
		$monto_dscto2 = number_format($result[0]['MONTO_DSCTO2'], 0, ',', '.');
		$total_dscto = number_format($result[0]['TOTAL_DSCTO'], 0, ',', '.');
		$neto = number_format($result[0]['TOTAL_NETO'], 0, ',', '.');
		$porc_iva = number_format($result[0]['PORC_IVA'], 1, ',', '.');
		$monto_iva = number_format($result[0]['MONTO_IVA'], 0, ',', '.');
		$total_con_iva = number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.');
		$guia_despacho = $result[0]['NRO_GUIAS_DESPACHO'];
		$cond_venta = $result[0]['NOM_FORMA_PAGO'];
		$cond_venta_otro = $result[0]['NOM_FORMA_PAGO_OTRO'];
		$retirado_por = $result[0]['RETIRADO_POR'];
		$GENERA_SALIDA	= $result[0]['GENERA_SALIDA'];
		if ($result[0]['REFERENCIA']=='')
			$REFERENCIA	= '';
		else
			$REFERENCIA	= 'REF: '.substr ($result[0]['REFERENCIA'], 0, 53);

		$sql = "select dbo.f_fa_NV_COMERCIAL(COD_FACTURA) COD_NOTA_VENTA
				from FACTURA
				where COD_FACTURA = $cod_factura";
		$result_NV = $db->build_results($sql);
		$COD_NV		= $result_NV[0]['COD_NOTA_VENTA'];	
		
		$OBS		= $result[0]['OBS'];
		$linea	= '______________________________';
		$CANCELADA	=	$result[0]['CANCELADA']; 

		$retirado_por_rut = number_format($result[0]['RUT_RETIRADO_POR'], 0, ',', '.');
		if ($retirado_por_rut == 0) {
			$retirado_por_rut = '';
		}else {
			$retirado_por_rut = number_format($result[0]['RUT_RETIRADO_POR'], 0, ',', '.')."-".$result[0]['DIG_VERIF_RETIRADO_POR'];
		}
				
		$retira_fecha = $result[0]['HORA'];
		if($cond_venta == 'OTRO')
			 $cond_venta = $cond_venta_otro;		
		
		if(strlen($cond_venta) > 30)
			$cond_venta = substr($cond_venta, 0, 30);

		// DIBUJANDO LA CABECERA	
		$pdf->SetFont('Arial','',11);		
		$pdf->Text($x-11, $y-4, $fecha);
		
		$pdf->SetFont('Arial','',8);		
		$pdf->Text($x+339, $y-40, $nro_factura);
		
		$pdf->SetFont('Arial','',11);
		$pdf->SetXY($x-16, $y+8);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(250, 15,"$nom_empresa");
		
		$pdf->Text($x+350, $y+16, $rut);
		
		$pdf->SetFont('Arial','',11);
		$pdf->Text($x+350, $y+45, $oc);
		
		$pdf->SetXY($x-16, $y+65);
		$pdf->MultiCell(250,10,"$direccion");
		
		$pdf->SetFont('Arial','',10);
		$pdf->Text($x+350, $y+70, $comuna);
		
		$pdf->Text($x-29, $y+98, $ciudad);
		
		$pdf->SetXY($x+126, $y+81);
		$pdf->MultiCell(120, 8,"$giro", 0, 'L');
		
		$pdf->Text($x+350, $y+98, $fono);
		
		$pdf->Text($x+25, $y+115, $guia_despacho);
		
		$pdf->Text($x+375, $y+125, $cond_venta);	
					
		$pdf->SetFont('Arial','B',10);
		$pdf->Text($x, $y+170, "$REFERENCIA");
		
		$pdf->SetFont('Arial','',9);	
		//DIBUJANDO LOS ITEMS DE LA FACTURA	
		for($i=0; $i<$count; $i++){
			$item = $result[$i]['ITEM'];
			$cantidad = $result[$i]['CANTIDAD'];
			$modelo = $result[$i]['COD_PRODUCTO'];
			$detalle = substr ($result[$i]['NOM_PRODUCTO'], 0, 45);	
			$p_unitario = number_format($result[$i]['PRECIO'], 0, ',', '.');
			$total = number_format($result[$i]['TOTAL_FA'], 0, ',', '.');
			// por cada pasada le asigna una nueva posicion	
			$pdf->Text($x-61, $y+188+(15*$i), $item);			
			$pdf->Text($x-31, $y+188+(15*$i), $cantidad);
			$pdf->Text($x+3, $y+188+(15*$i), $modelo);			
			$pdf->SetXY($x+54, $y+185+(15*$i));
			$pdf->Cell(300, 0, "$detalle");
			$pdf->SetXY($x+310, $y+181+(15*$i));
			$pdf->MultiCell(80,7, $p_unitario,0, 'R');		
			$pdf->SetXY($x+390, $y+181+(15*$i));
			$pdf->MultiCell(80,7, $total,0, 'R');							
		}					
									
		// DIBUJANDO TOTALES
		$pdf->SetFont('Arial','',12);
		$pdf->SetXY($x+48,$y+455);
		$pdf->MultiCell(270,10,'Son: '.$total_en_palabras.' pesos.');
		
		if($total_dscto <> 0){//tiene dscto
			if(($monto_dscto1 <> 0 && $monto_dscto2 == 0) || ($monto_dscto2 <> 0 && $monto_dscto1 == 0)){//solo tiene un DSCTO 1
				$pdf->SetXY($x+346, $y+490);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
			
				$pdf->SetXY($x+378, $y+490);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$subtotal,0, 'R');

				if($monto_dscto1 <> 0){
					$pdf->SetXY($x+343, $y+505);
					$pdf->SetFont('Arial','',9);
					$pdf->MultiCell(80,4,$porc_dscto1.' % DSCTO  $ ',0, 'R');

					$pdf->SetXY($x+378, $y+505);
					$pdf->SetFont('Arial','B',11);
					$pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');
				}
				else{
					$pdf->SetXY($x+333, $y+505);
					$pdf->SetFont('A4ial','',9);
					$pdf->MultiCell(80,4,$porc_dscto2.' % DSCTO: $ ',0, 'R');

					$pdf->SetXY($x+378, $y+505);
					$pdf->SetFont('Arial','B',11);
					$pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');				
				}				
			}else if($monto_dscto1 <> 0 && $monto_dscto2 <> 0){//tiene ambos DSCTO

				$pdf->SetXY($x+346, $y+475);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
			
				$pdf->SetXY($x+378, $y+475);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$subtotal,0, 'R');

				$pdf->SetXY($x+340, $y+490);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(85,4,$porc_dscto1.' % DSCTO1 $ ',0, 'R');

				$pdf->SetXY($x+378, $y+490);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');	
				
				$pdf->SetXY($x+346, $y+505);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4,$porc_dscto2.' % DSCTO2 $ ',0, 'R');

				$pdf->SetXY($x+378, $y+505);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');
			}
		}

		
		
		$pdf->SetXY($x+346, $y+520);
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(80,4, 'TOTAL NETO $ ',0, 'R');
		$pdf->SetXY($x+378, $y+520);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$neto,0, 'R');
		$pdf->SetXY($x+346, $y+535);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(80,4, $porc_iva.' % IVA  $ ',0, 'R');
		$pdf->SetXY($x+378, $y+535);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$monto_iva,0, 'R');
		$pdf->Rect($x+360, $y+544, 120, 2, 'f');
		$pdf->SetXY($x+346, $y+555);
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(80,4,'TOTAL  $ ',0, 'R');
		$pdf->SetXY($x+378, $y+555);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$total_con_iva,0, 'R');	


		//DIBUJANDO PERSONA QUE RETIRA PRODUCTOS 
		$pdf->SetFont('Arial','B',11);
		if ($GENERA_SALIDA == 'S'){
			$pdf->Rect($x-53, $y+510, 90, 15, 'f');
			$pdf->Text($x-47, $y+522, 'DESPACHADO');
		}	
		
		if ($CANCELADA == 'S'){
			$pdf->Rect($x-53, $y+550, 90, 14, 'f');
			$pdf->Text($x-47, $y+562, 'CANCELADA');
		}
		
		$pdf->SetFont('Arial','',13);
		$pdf->Text($x-52, $y+543, $COD_NV);
		
		$pdf->SetFont('Arial','',9);
		$pdf->SetXY($x-70, $y+481);
		$pdf->MultiCell(380, 8, "$OBS");
		
		$pdf->SetFont('Arial','',9);
		$pdf->Text($x+83, $y+488, $retirado_por);
		$pdf->Text($x+83, $y+508, $retirado_por_rut);
		$pdf->Text($x+249, $y+530, $retira_fecha);
	}
	
///////////FIN FACTURA CON IVA BODEGA BIGGI/////////////////////////////////////////
	function modifica_pdf(&$pdf){
		$pdf->AutoPageBreak=false;		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		$porc_iva = $result[0]['PORC_IVA'];
		
		//USUARIOS
		$USUARIO_IMPRESION = $result[0]['USUARIO_IMPRESION'];
		$ADM = 1;

		//BODEGA BIGGI NO IMPRIME FA SIN IVA
		if($porc_iva != 0){
			if($USUARIO_IMPRESION == $ADM){ //Admin en Bodega Biggi
				$this->print_con_iva_fa_Bodega_Biggi($pdf, 85, 145);
			}else{//otros usuarios
				$this->print_con_iva_fa($pdf, 100, 145);
			}
		} else {
			if($USUARIO_IMPRESION == $ADM){ //Admin en Bodega Biggi
				$this->print_sin_iva_fa_Bodega_Biggi($pdf, 100, 145);
			}else{//otros usuarios
				$this->print_sin_iva_fa($pdf, 79, 155);
			}
		}
	}
	
}
?>
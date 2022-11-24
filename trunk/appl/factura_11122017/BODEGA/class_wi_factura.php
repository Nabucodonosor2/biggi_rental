<?php
////////////////////////////////////////
/////////// BODEGA_BIGGI ///////////////
////////////////////////////////////////
class wi_factura extends wi_factura_base {
	const K_BODEGA_TERMINADO = 2;
	const K_TIPO_FA_OC_COMERCIAL = 3;
	
	function wi_factura($cod_item_menu) {
		parent::wi_factura_base($cod_item_menu);
	}
	function new_record() {
		parent::new_record();
		$this->dws['dw_factura']->set_item(0, 'COD_BODEGA', self::K_BODEGA_TERMINADO);
		$this->dws['dw_factura']->set_item(0, 'GENERA_SALIDA', 'S');
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
				from BIGGI.dbo.ORDEN_COMPRA O
				where O.COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
        $result_oc = $db->build_results($sql);
        $cod_nota_venta_comercial = $result_oc[0]['COD_NOTA_VENTA']; 
        
        $sql = "select N.REFERENCIA
        				,U.INI_USUARIO
						,CC.NOM_CENTRO_COSTO 
				from BIGGI.dbo.NOTA_VENTA N,  BIGGI.dbo.USUARIO U, BIGGI.dbo.CENTRO_COSTO CC
				where N.COD_NOTA_VENTA = $cod_nota_venta_comercial
				  and U.COD_USUARIO = N.COD_USUARIO_VENDEDOR1
				  and CC.COD_CENTRO_COSTO = BIGGI.dbo.f_emp_get_cc(N.COD_EMPRESA)";
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
				from BIGGI.dbo.ITEM_ORDEN_COMPRA I, PRODUCTO P
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
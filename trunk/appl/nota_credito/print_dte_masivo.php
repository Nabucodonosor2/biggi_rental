<?php
ini_set('display_errors', 'Off');
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/TCPDF-master/tcpdf.php");
ini_set('memory_limit', '480M');
ini_set('max_execution_time', 900);

$lista_cod_factura	= $_REQUEST['lista_cods'];
$cant_copias		= $_REQUEST['cant_copias'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('Isaias');
$pdf->SetAuthor('Isaias');
$pdf->SetTitle('NOTA DE CREDITO');
$pdf->SetSubject('DOCUMENTOS TRIBUTARIOS ELECTRONICOS');
$pdf->SetKeywords('DTE');
$pdf->setPrintHeader(false);//PARA EVITAR QUE SE DIBUJE UNA LINEA EN LA CABECERA
$pdf->setPrintFooter(false);//PARA EVITAR QUE SE DIBUJE UNA LINEA EN EL PIE DE PAGINA
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(false, $margin=0);

$Sqlpdf = " SELECT XML_DTE
				  ,COD_NOTA_CREDITO
			FROM NOTA_CREDITO
			WHERE COD_NOTA_CREDITO in ($lista_cod_factura)";  
$Result_pdf = $db->build_results($Sqlpdf);

for($var1=0 ; $var1 < count($Result_pdf) ; $var1++){
	
    $cod_documento	= $Result_pdf[$var1]['COD_NOTA_CREDITO'];
	$XML_DTE		= $Result_pdf[$var1]['XML_DTE'];
	$XML_DTE		= base64_decode($XML_DTE);
	$xml_resolucion = simplexml_load_string($XML_DTE);
	$folio			= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio;
	$resolucion		= $xml_resolucion->SetDTE->Caratula->NroResol;
	$fecha_resolucion = $xml_resolucion->SetDTE->Caratula->FchResol;
	
	$XML_DTE = strstr($XML_DTE, '<TED'); //separo el xml en el string "<TED"
	$len = strlen(strstr($XML_DTE, '</TED')); //realiazo la lectura en donde termina el tag "</TED" len
	$XML_DTE = substr($XML_DTE,0,-$len+6);   //resto resto del len y suno 6 cacarterers del </TED>           
	
	for($var2=0 ; $var2 < $cant_copias ; $var2++){
		$pdf->AddPage();
		
		$x_timbre = 20;
		$x = 20;
		$y = 222;
		$w = 70;
		$ecl = version_compare(phpversion(), '7.0.0', '<') ? -1 : 5;
		$style = array(
		                'border' => false,
		                'padding' => 0,
		                'hpadding' => 0,
		                'vpadding' => 0,
		                'module_width' => 1, // width of a single module in points
		                'module_height' => 1, // height of a single module in points
		                'fgcolor' => array(0,0,0),
		                'bgcolor' => false//, // [255,255,255]
		            );
		
		$pdf->write2DBarcode($XML_DTE, 'PDF417,,'.$ecl, $x_timbre, $y+7, $w, 0, $style, 'B');
		$pdf->SetFont('helvetica','B',5.40);
		$pdf->Text(40, 259+7,utf8_encode('Timbre Electr�nico SII'));
		$pdf->Text(22, 262+7,utf8_encode("Resoluci�n $resolucion del $fecha_resolucion Verifique este documento en www.sii.cl"));
		
		$imagen = dirname(__FILE__)."/../../images_appl/BIGGI_LOGO_DTE.png";
		$pdf->Image($imagen,9,5,67,28);
		$pdf->SetDrawColor(255,0,0);
		$pdf->SetLineWidth(1);
		$pdf->Rect(136, 10, 69, 30);
		$pdf->SetTextColor(255,0,0);
		$pdf->SetFont('helvetica','B',12.25);
		$pdf->Text(149, 12,'R.U.T.: 91.462.001-5');
		$pdf->Text(149, 19,'NOTA DE CREDITO');
		$pdf->Text(155, 26,'ELECTRONICA');
		$pdf->Text(161, 34,utf8_encode('N�: ').$folio);
		$pdf->SetFont('helvetica','B',8.75);
		$pdf->Text(149, 41,'S.I.I - SANTIAGO CENTRO');
		$pdf->SetTextColor(0,0,0);
		$pdf->Text(10, 40-7,'COMERCIAL BIGGI CHILE S.A.');
		$pdf->SetFont('helvetica','',7.15);
		$pdf->Text(10, 44-7,'ARTEFACTOS PARA LA MANIPULACION COCCION Y TRANSPORTE DE ALIMENTOS');
		$pdf->Text(10, 48-7,utf8_encode('PORTUGAL N� 1726 - SANTIAGO - CHILE'));
		$pdf->Text(10, 51-7,'FONOS: (56-2)2412-6200 - 25552849 - FAX(56-2)24126201 - 25512750');
		$pdf->SetLineWidth(0.3);
		$pdf->SetDrawColor(0,0,0);
		$pdf->RoundedRect(10, 55-7, 196, 37, 3.5);
		
		/*SELECT CABECERA*/
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql ="SELECT FA.NRO_ORDEN_COMPRA
					,N.MAIL
					,N.TELEFONO
					,N.REFERENCIA
					,TNC.NOM_TIPO_NOTA_CREDITO
					,N.OBS
					,U.NOM_USUARIO
					,(SELECT COD_DOC FROM FACTURA WHERE COD_FACTURA = N.COD_DOC)NRO_NV
					,N.COD_TIPO_NC_INTERNO_SII
					,N.PORC_IVA
					,N.PORC_DSCTO1
					,N.PORC_DSCTO2
					,case when N.MONTO_IVA = 0
						then N.TOTAL_NETO
						else null
					end	TOTAL_EXENTO
					,CI.NOM_CIUDAD
			FROM NOTA_CREDITO N, FACTURA FA,COMUNA C, CIUDAD CI,TIPO_NOTA_CREDITO TNC, USUARIO U
			WHERE N.COD_NOTA_CREDITO = $cod_documento
			AND N.COD_DOC = FA.COD_FACTURA
			AND C.COD_COMUNA = N.COD_COMUNA
			AND N.COD_CIUDAD = CI.COD_CIUDAD
			and N.COD_TIPO_NOTA_CREDITO = TNC.COD_TIPO_NOTA_CREDITO
			AND U.COD_USUARIO = N.COD_USUARIO";
		$result = $db->build_results($sql);
		
		$ARR_FECHA_NOTA_CREDITO	= explode("-", $xml_resolucion->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
		$FECHA_NOTA_CREDITO		= $ARR_FECHA_NOTA_CREDITO[2]."/".$ARR_FECHA_NOTA_CREDITO[1]."/".$ARR_FECHA_NOTA_CREDITO[0];
		$ARR_RUT_COMPLETO		= explode("-", $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Receptor->RUTRecep);
		$RUT_COMPLETO			= number_format($ARR_RUT_COMPLETO[0], 0, '', '.')."-".$ARR_RUT_COMPLETO[1];
		$NOM_EMPRESA			= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Receptor->RznSocRecep;
		$DIRECCION				= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Receptor->DirRecep;
		$GIRO					= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Receptor->GiroRecep;
		$NOM_COMUNA				= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Receptor->CmnaRecep;
		$NRO_FACTURA			= $xml_resolucion->SetDTE->DTE->Documento->Referencia->FolioRef;
		$ARR_FECHA_FACTURA		= explode("-", $xml_resolucion->SetDTE->DTE->Documento->Referencia->FchRef);
		$FECHA_FACTURA			= $ARR_FECHA_FACTURA[2]."/".$ARR_FECHA_FACTURA[1]."/".$ARR_FECHA_FACTURA[0];
		$NRO_FACTURA			= $xml_resolucion->SetDTE->DTE->Documento->Referencia->FolioRef;
		$MONTO_DSCTO1_V			= $xml_resolucion->SetDTE->DTE->Documento->DscRcgGlobal[0]->ValorDR;
		$MONTO_DSCTO2_V			= $xml_resolucion->SetDTE->DTE->Documento->DscRcgGlobal[1]->ValorDR;
		$TOTAL_NETO_V			= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto;
		$TOTAL_CON_IVA_V		= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal;
		
		$MONTO_DSCTO1			= $MONTO_DSCTO1_V;
		$MONTO_DSCTO2			= $MONTO_DSCTO2_V;
		$TOTAL_NETO				= $TOTAL_NETO_V;
		$SUBTOTAL				= $TOTAL_NETO_V + $MONTO_DSCTO1_V + $MONTO_DSCTO2_V;
		$MONTO_IVA				= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Totales->IVA;
		$TOTAL_CON_IVA			= $TOTAL_CON_IVA_V;
		
		$TOTAL_EN_PALABRAS		= Numbers_Words::toWords($TOTAL_CON_IVA_V,"es");
		$TOTAL_EN_PALABRAS		= strtr($TOTAL_EN_PALABRAS, "�����", "aeiou");
		$TOTAL_EN_PALABRAS		= strtoupper($TOTAL_EN_PALABRAS);
		
		$MAIL					= utf8_encode($result[0]['MAIL']);
		$TELEFONO				= utf8_encode($result[0]['TELEFONO']);
		$REFERENCIA				= utf8_encode($result[0]['REFERENCIA']);
		$NOM_TIPO_NOTA_CREDITO	= utf8_encode($result[0]['NOM_TIPO_NOTA_CREDITO']);
		$OBS					= utf8_encode($result[0]['OBS']);
		$EMISOR					= utf8_encode($result[0]['NOM_USUARIO']);
		$NRO_NV					= utf8_encode($result[0]['NRO_NV']);
		$COD_TIPO_NC			= $result[0]['COD_TIPO_NC_INTERNO_SII'];
		$PORC_IVA				= $result[0]['PORC_IVA'];
		$PORC_DSCTO1			= $result[0]['PORC_DSCTO1'];
		$PORC_DSCTO2			= $result[0]['PORC_DSCTO2'];
		$TOTAL_EXENTO			= $result[0]['TOTAL_EXENTO'];
		$NRO_ORDEN_COMPRA		= utf8_encode($result[0]['NRO_ORDEN_COMPRA']);
		$NOM_CIUDAD				= utf8_encode($result[0]['NOM_CIUDAD']);
		
		if ($TOTAL_EXENTO == '')
		    $TOTAL_EXENTO = '';
	    else
	        $TOTAL_EXENTO = number_format($TOTAL_EXENTO,0,'.','.');
	        
        if($MONTO_IVA == "")
            $MONTO_IVA = 0;
		            
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(12, 56-7);
        $pdf->MultiCell(20, 30,"FECHA",0);
        $pdf->SetXY(35, 56-7);
        $pdf->MultiCell(20, 30,":");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(38, 56-7);
        $pdf->MultiCell(20, 30,$FECHA_NOTA_CREDITO);
        $pdf->SetFont('helvetica','B',7.15);
        $pdf->SetXY(145, 56-7);
        $pdf->MultiCell(20, 30,"RUT.");
        $pdf->SetXY(160, 56-7);
        $pdf->MultiCell(20, 30,":");
        $pdf->SetXY(163, 56-7);
        $pdf->MultiCell(40, 30,$RUT_COMPLETO);
        
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(12, 60-7);
        $pdf->MultiCell(40, 30,utf8_encode("SE�OR(ES)"));
        $pdf->SetXY(35, 60-7);
        $pdf->MultiCell(20, 30,":");
        $pdf->SetXY(38, 60-7);
        $pdf->MultiCell(100, 2,substr($NOM_EMPRESA,0,186),0,"L");
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(145, 60-7);
        $pdf->MultiCell(20, 30,"O.C.");
        $pdf->SetXY(160, 60-7);
        $pdf->MultiCell(20, 60,":");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(163, 60-7);
        $pdf->MultiCell(20, 30,$NRO_ORDEN_COMPRA);
        
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(12, 67-7);
        $pdf->MultiCell(40, 30,utf8_encode("DIRECCI�N"));
        $pdf->SetXY(35, 67-7);
        $pdf->MultiCell(20, 1,":");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(38, 67-7);
        $pdf->MultiCell(95, 3,substr($DIRECCION,0,186),0,'L');
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(145, 67-7);
        $pdf->MultiCell(40, 30,"CIUDAD");
        $pdf->SetXY(160, 67-7);
        $pdf->MultiCell(20, 30,":");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(163, 67-7);
        $pdf->MultiCell(40, 30,$NOM_CIUDAD,0,'L');
        
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(12, 73-7);
        $pdf->MultiCell(40, 1,"GIRO");
        $pdf->SetXY(35, 73-7);
        $pdf->MultiCell(20, 1,":");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(38, 73-7);
        $pdf->MultiCell(100, 1,substr($GIRO,0,65),0,'L');
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(145, 73-7);
        $pdf->MultiCell(40, 30,"COMUNA");
        $pdf->SetXY(160, 73-7);
        $pdf->MultiCell(20, 30,":");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(163, 73-7);
        $pdf->MultiCell(40, 1,$NOM_COMUNA,0,'L');
        
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(12, 77-7);
        $pdf->MultiCell(40, 1,"E-MAIL");
        $pdf->SetXY(35, 77-7);
        $pdf->MultiCell(20, 1,":");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(38, 77-7);
        $pdf->MultiCell(110, 1,substr($MAIL,0,75),0,'L');
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(145, 77-7);
        $pdf->MultiCell(40, 30,"FONO");
        $pdf->SetXY(160, 77-7);
        $pdf->MultiCell(20, 30,":");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(163, 77-7);
        $pdf->MultiCell(36, 1,$TELEFONO,0,'L');
        
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(12, 81-7);
        $pdf->MultiCell(40, 1,"COND. DE VENTA",0,'L');
        $pdf->SetXY(35, 81-7);
        $pdf->MultiCell(20, 30,":");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(38, 81-7);
        //$pdf->MultiCell(100, 1,$NOM_FORMA_PAGO,0,'L');
        $pdf->MultiCell(100, 1,'',0,'L');
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(145, 81-7);
        $pdf->MultiCell(40, 1,"EMISOR");
        $pdf->SetXY(160, 81-7);
        $pdf->MultiCell(20, 30,":");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(163, 81-7);
        $pdf->MultiCell(50, 1,substr($EMISOR,0,27),0,'L');
        
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->SetXY(12, 85.5-7);
        $pdf->MultiCell(40, 1,"REFERENCIA",0,'L');
        $pdf->SetXY(35, 85.5-7);
        $pdf->MultiCell(3, 1,":",0);
        $pdf->SetXY(38, 85.5-7);
        $pdf->MultiCell(90, 2,$REFERENCIA,0,'L');
        $pdf->Text(129,85.5-7,utf8_encode("N� NV : $NRO_NV"));
        
        $pdf->Line(10,78,206,78);
        
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->Text(10,86.5,"TIPO REFERENCIA");
        $pdf->Text(70,86.5,"FOLIO");
        $pdf->Text(100,86.5,"FECHA");
        $pdf->Text(140,86.5,"MOTIVO REFERENCIA");
        
        $pdf->SetFont('helvetica','',7.15);
        
        if($PORC_IVA > 0)
            $pdf->Text(10,90.5,utf8_encode('Factura Afecta Electr�nica'));
        else
            $pdf->Text(10,90.5,utf8_encode('Factura Exenta Electr�nica'));
        
        $pdf->Text(70,90.5,$NRO_FACTURA);
        $pdf->Text(100,90.5,$FECHA_FACTURA);
        
        if($COD_TIPO_NC == 1 || $COD_TIPO_NC == 1)
            $pdf->Text(140,90.5,'Anula documento de referencia');
        else if($COD_TIPO_NC == 3 || $COD_TIPO_NC == 4)
            $pdf->Text(140,90.5,'Corrige montos');
        else if($COD_TIPO_NC == 5)
            $pdf->Text(140,90.5,'Corrige texto documento de referencia');
		                                
        $pdf->RoundedRect(10, 92+7, 196, 77.5, 3.5);
        $pdf->SetFont('helvetica','B',8);
        $pdf->SetXY(10, 93+7);
        $pdf->MultiCell(10, 1,"IT",0,'C');
        $pdf->Line(20,92+7,20,169.5+7);
        $pdf->SetXY(20, 93+7);
        $pdf->MultiCell(15, 1,"CT",0,'C');
        $pdf->Line(35,92+7,35,169.5+7);
        $pdf->SetXY(35, 93+7);
        $pdf->MultiCell(23, 1,"MODELO",0,'C');
        $pdf->Line(58,92+7,58,169.5+7);
        $pdf->SetXY(58, 93+7);
        $pdf->MultiCell(103, 1,"DETALLE",0,'C');
        $pdf->Line(161,92+7,161,169.5+7);
        $pdf->SetXY(161, 93+7);
        $pdf->MultiCell(21, 1,"P.UNIT.",0,'C');
        $pdf->Line(182,92+7,182,169.5+7);
        $pdf->SetXY(182, 93+7);
        $pdf->MultiCell(24, 1,"TOTAL",0,'C');
        
        $pdf->Line(10,85+19,206,85+19);
		                                
        /*************ITEMS***********/
        $x = 2;
        $i = 2;
        $y = $pdf->GetY()-7.5;
        
        $sql = "SELECT COD_TIPO_NOTA_CREDITO
    			FROM NOTA_CREDITO
			WHERE COD_NOTA_CREDITO = $cod_documento";
        $contenido = $db->build_results($sql);
                            
        for($it=0 ; $it < count($xml_resolucion->SetDTE->DTE->Documento->Detalle) ; $it++){
            $nro_linea		= $it+1;
            $vlrcodigo		= $xml_resolucion->SetDTE->DTE->Documento->Detalle[$it]->CdgItem->VlrCodigo;
            $nmbitem		= $xml_resolucion->SetDTE->DTE->Documento->Detalle[$it]->NmbItem;
            $monto_total	= $xml_resolucion->SetDTE->DTE->Documento->Detalle[$it]->MontoItem;
            $cantidad		= $xml_resolucion->SetDTE->DTE->Documento->Detalle[$it]->QtyItem;
            $precio			= $xml_resolucion->SetDTE->DTE->Documento->Detalle[$it]->PrcItem;
	                                    
            if($contenido[0]['COD_TIPO_NOTA_CREDITO'] == 2){
                $cantidad = 0;
                $precio = 0;
            }
		                                    
            $pdf->SetFont('helvetica','',6.25);
            $pdf->SetXY($x+8, $y+(4*$i));
            $pdf->MultiCell(10, 1, $nro_linea,0,'C');
            $pdf->SetXY($x+18, $y+(4*$i));
            $pdf->MultiCell(15, 1, $cantidad,0,'C');
            $pdf->SetXY($x+33, $y+(4*$i));
            $pdf->MultiCell(23, 1,substr($vlrcodigo,0,15) ,0,'C');
            $pdf->SetXY($x+56, $y+(4*$i));
            $pdf->MultiCell(103, 1,substr($nmbitem,0,85) ,0,'L');
            $pdf->SetXY($x+159, $y+(4*$i));
            $pdf->MultiCell(21, 1, number_format("$precio",0,'.','.'),0,'R');
            $pdf->SetXY($x+180, $y+(4*$i));
            $pdf->MultiCell(24, 1, number_format("$monto_total",0,'.','.'),0,'R');
		                                    
            $i++;
        }
        /*****************************************PIE PAGINA************************************************/
        /*************OBSERVACIONES********/
        $pdf->RoundedRect(10, 171.5+7, 140, 47, 3.5);
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->Text(15, 173+7,"SON : ");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(23, 173+7);
        $pdf->MultiCell(124, 3,"$TOTAL_EN_PALABRAS PESOS.",0,'L');
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->Text(12, 180.7+7,"NOTAS : ");
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetXY(23, 180.7+7);
        $pdf->MultiCell(124,30,substr($OBS,0,735) ,0,'L');
        $pdf->SetFont('helvetica','B',6.95);
        $pdf->Text(12, 213+7,"ESTADO PAGO : ");
        $pdf->Text(100, 213+7,"ESTADO SALIDA : ");
		                                
        /*************TOTALES***********/
        if($PORC_DSCTO1 == 0 && $PORC_DSCTO2 == 0){
		                                    
            $pdf->RoundedRect(155, 171.5+7, 51, 22, 3.5);
            $pdf->Line(189,171.5+7,189,200.5);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(168.5, 173+7,"TOTAL EXENTO");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 173+7);
            $pdf->MultiCell(26, 1,$TOTAL_EXENTO,0,"R");
            $pdf->Line(155,177+7,206,177+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(171.8, 178.2+7,"TOTAL NETO");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 178.2+7);
            $pdf->MultiCell(26, 1,number_format("$TOTAL_NETO",0,'.','.'),0,"R");
            $pdf->Line(155,182.5+7,206,182.5+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(176, 183.5+7,utf8_encode("19% I.V.A."));
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 183.5+7);
            $pdf->MultiCell(26, 1,number_format("$MONTO_IVA",0,'.','.'),0,"R");
            $pdf->Line(155,188+7,206,188+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(179, 189+7,"TOTAL");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 189+7);
            $pdf->MultiCell(26, 1,number_format("$TOTAL_CON_IVA",0,'.','.'),0,"R");
            
        }else if($PORC_DSCTO1 > 0 && $PORC_DSCTO2 == 0){
		                                    
            $pdf->RoundedRect(155, 171.5+7, 51, 33, 3.5);
            $pdf->Line(189,171.5+7,189,211.3);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(174, 173+7,"SUBTOTAL");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 173+7);
            $pdf->MultiCell(26, 1,number_format("$SUBTOTAL",0,'.','.'),0,"R");
            $pdf->Line(155,177+7,206,177+7);
            $pdf->SetFont('helvetica','',6.95);
            $pdf->Text(161.6, 185.2,number_format("$PORC_DSCTO1",2,'.','.').' %',0,"R");
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(171.8, 178.2+7,"DESCUENTO");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 178.2+7);
            $pdf->MultiCell(26, 1,number_format("$MONTO_DSCTO1",0,'.','.'),0,"R");
            $pdf->Line(155,182.5+7,206,182.5+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(168.5, 183.5+7,"TOTAL EXENTO");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 183.5+7);
            $pdf->MultiCell(26, 1,$TOTAL_EXENTO,0,"R");
            $pdf->Line(155,188+7,206,188+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(171.8, 189+7,"TOTAL NETO");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 189+7);
            $pdf->MultiCell(26, 1,number_format("$TOTAL_NETO",0,'.','.'),0,"R");
            $pdf->Line(155,193.5+7,206,193.5+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(176, 194.5+7,utf8_encode("19% I.V.A."));
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 194.5+7);
            $pdf->MultiCell(26, 1,number_format("$MONTO_IVA",0,'.','.'),0,"R");
            $pdf->Line(155,198.5+7,206,198.5+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(179, 199.8+7,"TOTAL");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 199.8+7);
            $pdf->MultiCell(26, 1,number_format("$TOTAL_CON_IVA",0,'.','.'),0,"R");
            
        }else if($PORC_DSCTO1 > 0 && $PORC_DSCTO2 > 0){
		                                    
            $pdf->RoundedRect(155, 171.5+7, 51, 38.5, 3.5);
            $pdf->Line(189,171.5+7,189,210+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(174, 173+7,"SUBTOTAL");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 173+7);
            $pdf->MultiCell(26, 1,number_format("$SUBTOTAL",0,'.','.'),0,"R");
            $pdf->Line(155,177+7,206,177+7);
            $pdf->SetFont('helvetica','',6.95);
            $pdf->Text(161.6, 185.2,number_format("$PORC_DSCTO1",2,'.','.').' %',0,"R");
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(171.8, 178.2+7,"DESCUENTO");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 178.2+7);
            $pdf->MultiCell(26, 1,number_format("$MONTO_DSCTO1",0,'.','.'),0,"R");
            $pdf->Line(155,182.5+7,206,182.5+7);
            $pdf->SetFont('helvetica','',6.95);
            $pdf->Text(155, 190.5,number_format("$PORC_DSCTO2",2,'.','.').' %',0,"R");
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(165, 183.5+7,"DESCUENTO ADIC.");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 183.5+7);
            $pdf->MultiCell(26, 1,number_format("$MONTO_DSCTO2",0,'.','.'),0,"R");
            $pdf->Line(155,188+7,206,188+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(168.5, 189+7,"TOTAL EXENTO");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 189+7);
            $pdf->MultiCell(26, 1,$TOTAL_EXENTO,0,"R");
            $pdf->Line(155,193.5+7,206,193.5+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(171.8, 194.5+7,"TOTAL NETO");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 194.5+7);
            $pdf->MultiCell(26, 1,number_format("$TOTAL_NETO",0,'.','.'),0,"R");
            $pdf->Line(155,198.5+7,206,198.5+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(176, 199.5+7,utf8_encode("19% I.V.A."));
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 200+7);
            $pdf->MultiCell(26, 1,number_format("$MONTO_IVA",0,'.','.'),0,"R");
            $pdf->Line(155,204.5+7,206,204.5+7);
            $pdf->SetFont('helvetica','B',6.95);
            $pdf->Text(179, 205.5+7,"TOTAL");
            $pdf->SetFont('helvetica','',7.15);
            $pdf->SetXY(180, 205.5+7);
            $pdf->MultiCell(26, 1,number_format("$TOTAL_CON_IVA",0,'.','.'),0,"R");
            
        }
        /*******************************/
		                                
        $pdf->SetDrawColor(255,0,0);
        $pdf->SetLineWidth(4);
        $pdf->Line(10,275,206,275);
        $pdf->SetFont('helvetica','',7.15);
        $pdf->SetTextColor(255,255,255);
        $pdf->Text(97, 273.5,"www.biggi.cl");
	}	
}

$pdf->Output("61_masivo.pdf", 'I');	
?>
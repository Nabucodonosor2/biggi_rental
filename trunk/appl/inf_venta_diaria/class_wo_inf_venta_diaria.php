<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");

class dw_docs extends datawindow {	
	function dw_docs($fecha1, $fecha2,$no_incluye_relacionado) {
		$sql = "select TDP.NOM_TIPO_DOC_PAGO
						,COUNT(*) CANT
						,SUM(MDA.MONTO_DOC_ASIGNADO) MONTO
				from FACTURA F, INGRESO_PAGO_FACTURA IPF, DOC_INGRESO_PAGO DIP, TIPO_DOC_PAGO TDP, MONTO_DOC_ASIGNADO MDA, INGRESO_PAGO IP
				where F.FECHA_FACTURA between $fecha1 and $fecha2
				  and ('$no_incluye_relacionado'='S' or F.COD_EMPRESA not in (1, 37,44,38))	/*COMERCIAL,BODEGA_BIGGI,CATERING,SERVINDUS*/
				  and F.COD_ESTADO_DOC_SII in (2,3)
				  and IPF.COD_DOC = F.COD_FACTURA
				  and DIP.COD_INGRESO_PAGO = IPF.COD_INGRESO_PAGO
				  and TDP.COD_TIPO_DOC_PAGO = DIP.COD_TIPO_DOC_PAGO
				  and MDA.COD_DOC_INGRESO_PAGO = DIP.COD_DOC_INGRESO_PAGO
				  and MDA.COD_INGRESO_PAGO_FACTURA = IPF.COD_INGRESO_PAGO_FACTURA
				  and IP.COD_INGRESO_PAGO = DIP.COD_INGRESO_PAGO
				  and IP.COD_ESTADO_INGRESO_PAGO <> 3  -- anulada
				group by TDP.NOM_TIPO_DOC_PAGO";
		parent::datawindow($sql, 'DW_DOCS');

		$this->add_control(new static_num('MONTO'));
		$this->accumulate('MONTO', '', false);
	}
}
class wo_inf_venta_diaria extends w_informe_pantalla {
	var $dw_docs;
    function wo_inf_venta_diaria() {
		$fecha = session::get("inf_venta_diaria.FECHA");
		$fecha1 = $this->str2date($fecha);
		$fecha2 = $this->str2date($fecha, '23:59:59');
		$no_incluye_relacionado = session::get("inf_venta_diaria.NO_INCLUYE_RELACIONADO");
		
		$sql = "select f.NRO_FACTURA
					  ,CONVERT(varchar, F.FECHA_FACTURA, 103) FECHA
					  ,E.NOM_EMPRESA
					  ,F.TOTAL_CON_IVA
					  ,FP.NOM_FORMA_PAGO
					  ,dbo.f_fa_total_ingreso_pago(F.COD_FACTURA) MONTO_PAGOS
					  ,dbo.f_fa_saldo(F.COD_FACTURA) SALDO
				  	  ,F.COD_ESTADO_DOC_SII
				      ,'FA' TIPO_DOC                     
					  ,F.COD_FACTURA COD_DOC                      
				from FACTURA F, EMPRESA E, FORMA_PAGO FP
				where F.FECHA_FACTURA between $fecha1 and $fecha2
				  and ('$no_incluye_relacionado'='S' or F.COD_EMPRESA not in (1, 37,44,38))	/*COMERCIAL,BODEGA_BIGGI,CATERING,SERVINDUS*/
				  and F.COD_ESTADO_DOC_SII in (2,3,4)
				  and E.COD_EMPRESA = F.COD_EMPRESA
				  and FP.COD_FORMA_PAGO = F.COD_FORMA_PAGO
				order by f.NRO_FACTURA desc";  
				
		parent::w_informe_pantalla('inf_venta_diaria', $sql, $_REQUEST['cod_item_menu']);
		$this->b_print_visible = true;
		
		// headers	
		$this->add_header(new header_num('NRO_FACTURA', 'F.NRO_FACTURA', 'Nro FA'));
		$this->add_header(new header_date('FECHA', 'F.FECHA_FACTURA', 'Fecha'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Cliente'));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'F.TOTAL_CON_IVA', 'Total c/IVA', 0, true, 'SUM'));
		$this->add_header(new header_text('NOM_FORMA_PAGO', 'FP.NOM_FORMA_PAGO', 'Forma pago'));
		$this->add_header(new header_num('MONTO_PAGOS', 'dbo.f_fa_total_ingreso_pago(F.COD_FACTURA)', 'Monto Pagos', 0, true, 'SUM'));
		$this->add_header(new header_num('SALDO', 'dbo.f_fa_saldo(F.COD_FACTURA)', 'Saldo', 0, true, 'SUM'));
		
		// controls
		$this->dw->add_control(new static_num('TOTAL_CON_IVA'));
		$this->dw->add_control(new static_num('MONTO_PAGOS'));
		$this->dw->add_control(new static_num('SALDO'));
		$this->dw->add_control(new static_num('DSCTO_PERIMITIDO',1));
		$this->dw->add_control(new static_num('PORC_DSCTO',1));
		
		$this->row_per_page = 500;
		
		$this->dw_docs = new dw_docs($fecha1, $fecha2,$no_incluye_relacionado);
	}
	function redraw(&$temp) {
		parent::redraw(&$temp);
		$this->dw_docs->retrieve();
		$this->dw_docs->habilitar($temp, true);
	}
	function print_informe() {
		// reporte
		$sql = $this->dw->get_sql();
		$sql_dw2 = $this->dw_docs->get_sql();

		$xml = session::get('K_ROOT_DIR').'appl/inf_venta_diaria/venta_diaria.xml';
		$labels = array();

		$fecha = session::get("inf_venta_diaria.FECHA");
		$no_incluye_relacionado = session::get("inf_venta_diaria.NO_INCLUYE_RELACIONADO");

		switch ($no_incluye_relacionado) {
		    case 'S':
		        $filtro = 'Si';
		        break;
			case 'N':
		        $filtro = 'No';
		        break;
		}

		$labels['str_Fecha'] = $fecha;
		$labels['str_Filtro'] = $filtro;
		$rpt = new print_venta_diaria($sql, $sql_dw2, $xml, $labels, "Ventas Diaria.pdf", 'logo');
		$this->_redraw();
	}
}
class print_venta_diaria extends reporte {
	var $sql_dw2;
	function print_venta_diaria($sql, $sql_dw2, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
		$this->sql_dw2 = $sql_dw2;
	}
	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		$result_dw2 = $db->build_results($this->sql_dw2);	

		///////////////////////IMPRIME SOLO LA 1 HOJA////////////////////////////////
		$pdf->SetTextColor(0,0,128);//TEXTOS AZULES
		// DIBUJANDO LA CABECERA
		$pdf->SetXY(5, 190);
		$pdf->SetFont('Arial','',11);
		$pdf->MultiCell(50,20, 'Nro FA','LTB','C');

		$pdf->SetXY(55, 190);
		$pdf->SetFont('Arial','',11);
		$pdf->MultiCell(60,20, 'Fecha','LTB','C');

		$pdf->SetXY(115, 190);
		$pdf->SetFont('Arial','',11);
		$pdf->MultiCell(190, 20, 'Cliente','LTB','C');

		$pdf->SetXY(305, 190);
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(95,20, 'Forma pago','LTB','C');
		
		$pdf->SetXY(400, 190);
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(70,20, 'Total c/IVA','LTB','C');

		$pdf->SetXY(470, 190);
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(60,20, 'Monto pago','LTB','C');

		$pdf->SetXY(530, 190);
		$pdf->SetFont('Arial','',11);
		$pdf->MultiCell(60, 20, 'Saldo','LRTB','C');

		$pdf->SetTextColor(100,100,100);//TEXTOS NEGROS

		///////////////////////IMPRIME SOLO LA 1 HOJA////////////////////////////////
		$sum_total_con_iva	 =	0;
		$sum_monto_pagos	 =	0;
		$sum_saldo			 =	0;
		$count = count($result);
		for($i=0, $p=0, $ii=0; $i<$count; $i++, $p++, $ii++){
			if ($ii == 25) {
				$ii = 0;
				$pdf->AddPage();
				$y_ini = $pdf->GetY() +5;
				$p=0;

				$pdf->SetTextColor(0,0,128);//TITULOS AZULES
	
				$pdf->SetXY(5, $y_ini+15);
				$pdf->SetFont('Arial','',11);
				$pdf->MultiCell(50,20, 'Nro FA','LTB','C');
	
				$pdf->SetXY(55, $y_ini+15);
				$pdf->SetFont('Arial','',11);
				$pdf->MultiCell(60,20, 'Fecha','LTB','C');
	
				$pdf->SetXY(115, $y_ini+15);
				$pdf->SetFont('Arial','',11);
				$pdf->MultiCell(190, 20, 'Cliente','LTB','C');

				$pdf->SetXY(305, $y_ini+15);
				$pdf->SetFont('Arial','',10);
				$pdf->MultiCell(95,20, 'Forma pago','LTB','C');
				
				$pdf->SetXY(400, $y_ini+15);
				$pdf->SetFont('Arial','',10);
				$pdf->MultiCell(70,20, 'Total c/IVA','LTB','C');
				
				$pdf->SetXY(470, $y_ini+15);
				$pdf->SetFont('Arial','',10);
				$pdf->MultiCell(0,20, 'Monto pago','LTB','C');
	
				$pdf->SetXY(530, $y_ini+15);
				$pdf->SetFont('Arial','',11);
				$pdf->MultiCell(60, 20, 'Saldo','LRTB','C');
	
				$pdf->SetTextColor(100,100,100);//TEXTOS NEGROS
	
				$pdf->SetTextColor(100,100,100);
			}
			$nro_factura	 =	$result[$i]['NRO_FACTURA'];
			$fecha			 =	$result[$i]['FECHA'];
			$nom_empresa	 =	$result[$i]['NOM_EMPRESA'];
			$total_con_iva	 =	number_format($result[$i]['TOTAL_CON_IVA'],0,',','.');
			$nom_forma_pago	 =	$result[$i]['NOM_FORMA_PAGO'];
			$monto_pagos	 =	number_format($result[$i]['MONTO_PAGOS'], 0, ',', '.');
			$saldo			 =	number_format($result[$i]['SALDO'], 0, ',', '.');

			$nom_tipo_doc_pago	=	$result_dw2[$i]['NOM_TIPO_DOC_PAGO'];
			$cant	=	$result_dw2[$i]['CANT'];
			$monto	=	number_format($result_dw2[$i]['MONTO'], 0, ',', '.');

			//1 TABLA
			$pdf->SetFont('Arial','',10);

			$pdf->SetXY(5, 210+(16*$p));
			$pdf->MultiCell(50, 15, $nro_factura, 'LBR', 'C');

			$pdf->SetXY(55, 210+(16*$p));
			$pdf->MultiCell(60, 15, $fecha, 'B', 'C');

			$pdf->SetFont('Arial','',8.4);
			$pdf->SetXY(115, 210+(16*$p));
			$pdf->MultiCell(190, 15, substr($nom_empresa, 0, 35), 'LBR', 'L');

			$pdf->SetFont('Arial','',7);
			$pdf->SetXY(305, 210+(16*$p));
			$pdf->MultiCell(95, 15, substr($nom_forma_pago, 0, 15), 'B', 'L');
			
			$pdf->SetFont('Arial','',10);
			$pdf->SetXY(400, 210+(16*$p));
			$pdf->MultiCell(70, 15, $total_con_iva, 'LB', 'R');

			$pdf->SetFont('Arial','',10);
			$pdf->SetXY(470, 210+(16*$p));
			$pdf->MultiCell(60, 15, $monto_pagos, 'LB', 'R');

			$pdf->SetXY(530, 210+(16*$p));
			$pdf->MultiCell(60, 15, $saldo, 'LBR', 'R');
		
			$sum_total_con_iva	 =	$result[$i]['TOTAL_CON_IVA'] + $sum_total_con_iva;
			$sum_monto_pagos	 =	$result[$i]['MONTO_PAGOS'] + $sum_monto_pagos;
			$sum_saldo			 =	$result[$i]['SALDO'] + $sum_saldo;
		}
		$pdf->SetFont('Arial','',12);
		$pdf->SetXY(305, 210+(16*$p));
		$pdf->MultiCell(95, 15, 'Totales', 'LB', 'R');
		
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY(400, 210+(16*$p));
		$pdf->MultiCell(70, 15, number_format($sum_total_con_iva,0,',','.'), 'LB', 'R');
		
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY(470, 210+(16*$p));
		$pdf->MultiCell(60, 15, number_format($sum_monto_pagos,0,',','.'), 'LB', 'R');
		$pdf->SetXY(530, 210+(16*$p));
		$pdf->MultiCell(60, 15, number_format($sum_saldo,0,',','.'), 'LBR', 'R');
		
		
		///////////////////2 TABLA//////////////////////
		// DIBUJANDO LA CABECERA 2 TABLA
		$y_ini = $pdf->GetY() +20;
		
		$pdf->SetTextColor(0,0,128);
		// DIBUJANDO LA CABECERA 2 TABLA
		$pdf->SetXY(5, $y_ini);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(70,20, 'Documentos', 'C');

		$pdf->SetXY(5, $y_ini + 20);
		$pdf->SetFont('Arial','',11);
		$pdf->MultiCell(190, 20, 'Tipo doc','LTB', 'C');

		$pdf->SetXY(195, $y_ini + 20);
		$pdf->SetFont('Arial','',11);
		$pdf->MultiCell(190, 20, 'Cantidad','LTB', 'C');

		$pdf->SetXY(385, $y_ini + 20);
		$pdf->SetFont('Arial','',11);
		$pdf->MultiCell(200, 20, 'Monto',1, 'C');
		
		$sum_monto = 0;
		$count = count($result_dw2);
		for($i=0, $p=0, $ii=0; $i<$count; $i++, $p++, $ii++){
			$fin_hoja = $pdf->GetY();
			if ($fin_hoja == 600) {
				$ii = 0;
				$pdf->AddPage();
				$y_ini = $pdf->GetY() +5;
				$p=0;
			
				$pdf->SetTextColor(100,100,100);
				
				$pdf->SetFont('Arial','',10);
			
			}
			$pdf->SetTextColor(100,100,100);
			$nom_tipo_doc_pago	=	$result_dw2[$i]['NOM_TIPO_DOC_PAGO'];
			$cant	=	$result_dw2[$i]['CANT'];
			$monto	=	number_format($result_dw2[$i]['MONTO'], 0, ',', '.');
			$pdf->SetXY(5, $y_ini+40+(15*$p));
			$pdf->MultiCell(190, 15, $nom_tipo_doc_pago,  'LB', 'L');
	
			$pdf->SetXY(195, $y_ini+40+(15*$p));
			$pdf->MultiCell(190, 15, $cant, 'LBR', 'C');
	
			$pdf->SetXY(385, $y_ini+40+(15*$p));
			$pdf->MultiCell(200, 15, $monto, 'BR', 'R');
			
			$sum_monto = $result_dw2[$i]['MONTO'] + $sum_monto;
		}
		$pdf->SetXY(195, $y_ini+40+(15*$p));
		$pdf->MultiCell(190, 15, 'Totales:', 'LBR', 'R');
		$pdf->SetXY(385, $y_ini+40+(15*$p));
		$pdf->MultiCell(200, 15, number_format($sum_monto,0,',','.'), 'LBR', 'R');
	}
}
?>
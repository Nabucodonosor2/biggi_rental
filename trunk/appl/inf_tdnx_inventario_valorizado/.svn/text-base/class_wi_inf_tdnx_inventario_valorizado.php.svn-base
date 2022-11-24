<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_param_informe_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_reporte_biggi.php");

class wi_inf_tdnx_inventario_valorizado extends w_param_informe_biggi {
	
	function wi_inf_tdnx_inventario_valorizado($cod_item_menu) {
		$xml = session::get('K_ROOT_DIR').'appl/inf_tdnx_inventario_valorizado/inf_tdnx_inv_valor_detalle.xml';
		parent::w_param_informe_biggi('inf_tdnx_inventario_valorizado',$cod_item_menu,'Inventario Valorizado',$xml,'','spi_tdnx_inv_valor_detalle');
		
		// del 1ero del mes hasta hoy
		$sql = "SELECT  '' COD_BODEGA
						,CONVERT(VARCHAR, GETDATE(), 103) FECHA
						,NULL COD_CLASIF_INVENTARIO
						,'RESUMEN' RESUMEN
						,'RESUMEN' DETALLE";
		$this->dws['dw_param'] = new datawindow($sql);
		
		$sql_bodega="SELECT COD_BODEGA
							,NOM_BODEGA
					FROM	BODEGA
					WHERE	COD_BODEGA = 1
					ORDER BY COD_BODEGA ASC";
		$this->dws['dw_param']->add_control(new drop_down_dw('COD_BODEGA', $sql_bodega, 0, '', false));
		$this->dws['dw_param']->add_control(new edit_date('FECHA'));
		$this->dws['dw_param']->add_control(new edit_radio_button('RESUMEN', 'RESUMEN', 'DETALLE', 'Resumen', 'TIPO'));
		$this->dws['dw_param']->add_control(new edit_radio_button('DETALLE', 'DETALLE', 'RESUMEN', 'Detalle', 'TIPO'));
		$sql_clasif="SELECT COD_CLASIF_INVENTARIO
							,NOM_CLASIF_INVENTARIO
					FROM	CLASIF_INVENTARIO
					ORDER BY COD_CLASIF_INVENTARIO ASC";
		$this->dws['dw_param']->add_control(new drop_down_dw('COD_CLASIF_INVENTARIO', $sql_clasif, 0, '', false));
		
		// mandatorys		
		$this->dws['dw_param']->set_mandatory('COD_BODEGA', 'Bodega');	
		$this->dws['dw_param']->set_mandatory('FECHA', 'Fecha');
		$this->dws['dw_param']->set_mandatory('COD_CLASIF_INVENTARIO', 'Clasificación');
	}
	
	function make_filtro() {
		$fecha 				= $this->dws['dw_param']->get_item(0, 'FECHA'); 
		$cod_bodega			= $this->dws['dw_param']->get_item(0, 'COD_BODEGA');  
		$nom_bodega 		= $this->dws['dw_param']->controls['COD_BODEGA']->get_label_from_value($cod_bodega);
		$cod_clasif			= $this->dws['dw_param']->get_item(0, 'COD_CLASIF_INVENTARIO');
		$nom_clasif 		= $this->dws['dw_param']->controls['COD_CLASIF_INVENTARIO']->get_label_from_value($cod_clasif);
		$resumen 			= $this->dws['dw_param']->get_item(0, 'RESUMEN');
		
		//FILTRO
		$this->filtro = "Fecha = $fecha \n";
		$this->filtro = "Bodega = $nom_bodega";
		$this->filtro = "Clasificación = $nom_clasif";

		// Arma los paramaetros para el SP
		$fecha = $this->str2date($fecha, '23:59:59');
		
		if($resumen =='RESUMEN'){
			$this->sp = 'spi_tdnx_inv_valor_resumen';
			$xml = session::get('K_ROOT_DIR').'appl/inf_tdnx_inventario_valorizado/inf_tdnx_inv_valor_resumen.xml';
			$this->xml = $xml;
			$this->param = "$cod_bodega, $fecha";
		}
		else if($resumen =='DETALLE'){
			$this->sp = 'spi_tdnx_inv_valor_detalle';
			$xml = session::get('K_ROOT_DIR').'appl/inf_tdnx_inventario_valorizado/inf_tdnx_inv_valor_detalle.xml';					
			$this->param = "$cod_bodega, $fecha, $cod_clasif";
		}
	}
	function genera_pdf($labels = array(), $con_logo = true,$orientation='P',$unit='pt',$format='letter') {
		$resumen = $this->dws['dw_param']->get_item(0, 'RESUMEN');
		if($resumen =='RESUMEN') {
			$rpt = new reporte_inv_resumen($this->sql_informe, $this->xml, $labels, $this->nom_informe, $con_logo, true, $this->sp, $this->param,$orientation,$unit,$format);
			$this->redraw();
		}
		else {
			parent::genera_pdf($labels, $con_logo,$orientation,$unit,$format);
		}
	}
}

class reporte_inv_resumen extends reporte_biggi {
	function reporte_inv_resumen($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false, $sp='', $param='',$orientation='P',$unit='pt',$format='letter') {
		parent::reporte_biggi("select getdate() FECHA", $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion, $sp, $param,$orientation,$unit,$format);
		$this->sp = '';		// para evitar que se ejecute el sp
	}
	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "exec spi_tdnx_inv_valor_resumen ".$this->param;
		$result = $db->build_results($sql);
		$count = count($result);
		
		
		$pdf->SetFont('Helvetica','B',16);
		$pdf->SetTextColor(0,0,190);
		$pdf->SetXY(100, 110);
		$pdf->MultiCell(350, 20,'INVENTARIO VALORIZADO RESUMEN', 0, 'R');
		
		$pdf->SetFont('Helvetica','B',10);
		$pdf->SetXY(241, 127);
		$pdf->MultiCell(80, 15, 'Al  '.$result[0]['FECHA'], 0, 'R');
		
		$pdf->SetFont('Helvetica','B',10);
		$pdf->SetTextColor(0,0,0);
		$pdf->SetXY(500, 120);
		$pdf->MultiCell(80, 15, 'Nº Pag: '.$pdf->PageNo(), 0, 'R');
		
		
		
		//////Tabla Encabezado//////
		$pdf->SetFont('Helvetica','B',12);
		$pdf->SetXY(40,170);
		$pdf->MultiCell(30, 15, 'CO', 1, 'C');
		$pdf->SetXY(70,170);
		$pdf->MultiCell(255, 15, 'Nombre', 1, 'C');
		$pdf->SetXY(325,170);
		$pdf->MultiCell(80, 15, 'Total M $', 1, 'C');
		$pdf->SetXY(405,170);
		$pdf->MultiCell(50, 15, '%', 1, 'C');
		
		// sumar todo
		$suma = 0;
		$suma_total = 0;
		for ($i=0; $i<$count; $i++){
			if ($result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'DE' || $result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'NA')
			continue;
			
			$suma = $suma + ($result[$i]['COSTO_TOTAL']);
			$cost_total = substr($result[$i]['COSTO_TOTAL'],0,-3);
			$suma_total = $suma_total + $cost_total;
		}
		for ($i=0; $i<$count; $i++){
			$Y = $pdf->gety();
			if ($result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'DE' || $result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'NA')
			continue;
			
			$NOM_CORTO = $result[$i]['NOM_CORTO_CLASIF_INVENTARIO'];
			$NOM_CLASIF = $result[$i]['NOM_CLASIF_INVENTARIO'];
			$COSTO_TOTAL = substr($result[$i]['COSTO_TOTAL'],0,-3);
			$PORC = (($result[$i]['COSTO_TOTAL'] / $suma)*100);
			
	 		$pdf->SetFont('Helvetica','',10);
			$pdf->SetXY(40,$Y);
			$pdf->MultiCell(30, 15,$NOM_CORTO, 1, 'C');
			$pdf->SetXY(70,$Y);
			$pdf->MultiCell(255, 15,$NOM_CLASIF, 1, 'L');
			$pdf->SetXY(325,$Y);
			$pdf->MultiCell(80, 15,$detalle = number_format($COSTO_TOTAL,0,'','.'), 1, 'R');
			$pdf->SetXY(405,$Y);
			$pdf->MultiCell(50, 15,$detalle = number_format($PORC,1,',','.').' %', 1, 'R');
		}
		$pdf->SetFont('Helvetica','B',10);
		$pdf->SetXY(70,$Y+15);
		$pdf->MultiCell(255,15,'Total', 0, 'R');
		$pdf->SetFont('Helvetica','',10);
		$pdf->SetXY(325,$Y+15);
		 
		$pdf->MultiCell(80, 15,$detalle = number_format($suma_total,0,'','.'), 0, 'R');
		$pdf->Ln();
		$pdf->Ln();
		
		$suma = 0;
		for ($i=0; $i<$count; $i++){
			if ($result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'AI' || $result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'EQ')
			continue;
			if ($result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'PP' || $result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'RI')
			continue;
			$suma = $suma + (substr($result[$i]['COSTO_TOTAL'],0,-3));
			
			
		}
		for ($i=0; $i<$count; $i++){
			$Y = $pdf->gety();
			if ($result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'AI' || $result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'EQ')
			continue;
			if ($result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'PP' || $result[$i]['NOM_CORTO_CLASIF_INVENTARIO'] == 'RI')
			continue;
			
			$NOM_CORTO_NA = $result[$i]['NOM_CORTO_CLASIF_INVENTARIO'];
			$NOM_CLASIF_NA = $result[$i]['NOM_CLASIF_INVENTARIO'];
			$COSTO_TOTAL_NA = substr($result[$i]['COSTO_TOTAL'],0,-3);
			
			$pdf->SetXY(40,$Y);
			$pdf->MultiCell(30, 15,$NOM_CORTO_NA, 1, 'C');
			$pdf->SetXY(70,$Y);
			$pdf->MultiCell(255, 15,$NOM_CLASIF_NA, 1, 'L');
			$pdf->SetXY(325,$Y);
			$pdf->MultiCell(80, 15,$detalle = number_format($COSTO_TOTAL_NA,0,'','.'), 1, 'R');
		}
		
		$pdf->SetFont('Helvetica','B',10);
		$pdf->SetXY(70,$Y+15);
		$pdf->MultiCell(255,15,'Total (DE + NA)', 0, 'R');
		$pdf->SetFont('Helvetica','',10);
		$pdf->SetXY(325,$Y+15);
		$pdf->MultiCell(80, 15,$detalle = number_format($suma,0,'','.'), 0, 'R');
	}
}
?>
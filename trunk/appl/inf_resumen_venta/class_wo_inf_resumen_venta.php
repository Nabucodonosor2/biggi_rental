<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_modelo.php");

class wo_inf_resumen_venta extends w_informe_pantalla {
	var $dw_totales;
	var $dw_titulos;
	
   function wo_inf_resumen_venta() {
   		// Construye el resultado del informe en un tabla AUXILIA de INFORME
		
		$ano1 = session::get("inf_resumen_venta.ANO1");
		$ano2 = session::get("inf_resumen_venta.ANO2");
		$mes_desde = session::get("inf_resumen_venta.MES_DESDE");
		$mes_hasta = session::get("inf_resumen_venta.MES_HASTA");
		
		
		$param = "$ano1 ,$ano2, $mes_desde, $mes_hasta";
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->EXECUTE_SP("spi_resumen_venta", $param);
		
		$sql = "select COD_INF_RESUMEN_VENTA
					  ,COD_EMPRESA
					  ,NOM_EMPRESA		
					  ,MONTO_ANO		
					  ,MONTO_ANO_II
					  ,MONTO_ANO TOTAL_ANO
					  ,MONTO_ANO_II TOTAL_ANO_II
				from INF_RESUMEN_VENTA
				order by COD_INF_RESUMEN_VENTA";  
		
		parent::w_informe_pantalla('inf_resumen_venta', $sql, $_REQUEST['cod_item_menu']);
		$this->b_print_visible = true;
		
		// headers	
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Nombre Empresa'));
		$this->add_header(new header_num('MONTO_ANO', 'MONTO_ANO', 'Monto Año '.$ano1));
		$this->add_header(new header_num('MONTO_ANO_II', 'MONTO_ANO_II', 'Monto Año '.$ano2));
		$this->add_header(new header_num('TOTAL_ANO', 'MONTO_ANO', 'TOTAL_ANO'));
		$this->add_header(new header_num('TOTAL_ANO_II', 'MONTO_ANO_II', 'MONTO_ANO_II'));
		
		// controls
		$this->dw->add_control(new static_num('MONTO_ANO'));
		$this->dw->add_control(new static_num('MONTO_ANO_II'));
		
		//titulos
		$sql = "select 'Nombre Empresa'		TIT_EMPRESA
						,'Monto Neto Año $ano1' 	TIT_ANO
						,'Monto Neto Año $ano2' 	TIT_ANO_II";
		$this->dw_titulos = new datawindow($sql);
		$this->dw_titulos->retrieve();
		
		// totales
		$sql = "select 'Ventas'				LABEL
					  ,sum(MONTO_ANO)		VENTA_ANT
					  ,sum(MONTO_ANO_II)	VENTA_ACT
				  	  ,1					ORDEN
					  from INF_RESUMEN_VENTA
				where COD_EMPRESA not in (-3, -4)	--no arriendo
				union								
				select 'Arriendos'			LABEL
					  ,sum(MONTO_ANO)		VENTA_ANT
					  ,sum(MONTO_ANO_II)	VENTA_ACT
				  	  ,2					ORDEN
				from INF_RESUMEN_VENTA
				where COD_EMPRESA in (-3, -4)		--si arriendo
				union				
				select 'Total'				LABEL
					   ,sum(MONTO_ANO)		VENTA_ANT
					  ,sum(MONTO_ANO_II)	VENTA_ACT
				  	  ,3					ORDEN
				from INF_RESUMEN_VENTA
				order by ORDEN";	
		$this->dw_totales = new datawindow($sql, 'TOTALES');
		$this->dw_totales->add_control(new static_num('VENTA_ANT'));
		$this->dw_totales->add_control(new static_num('VENTA_ACT'));
		$this->dw_totales->retrieve();
	}
	function redraw(&$temp) {
		parent::redraw($temp);
		$mes_desde = session::get("inf_resumen_venta.MES_DESDE");
		$mes_hasta = session::get("inf_resumen_venta.MES_HASTA");
		$temp->setVar('MES_DESDE',base::nom_mes($mes_desde));
		$temp->setVar('MES_HASTA',base::nom_mes($mes_hasta));
		
		$this->dw_totales->habilitar($temp, false);
		$this->dw_titulos->habilitar($temp, false);
	}
	function print_informe() {
		// reporte
		$sql = $this->dw->get_sql();
		$xml = session::get('K_ROOT_DIR').'appl/inf_resumen_venta/inf_resumen_venta.xml';
		$labels = array();
		$ano1 = session::get("inf_resumen_venta.ANO1");
		$ano2 = session::get("inf_resumen_venta.ANO2");
		$mes_desde = session::get("inf_resumen_venta.MES_DESDE");
		$mes_hasta = session::get("inf_resumen_venta.MES_HASTA");
		
		$labels['str_fecha'] = $this->current_date();
		$labels['str_filtro'] = $this->nom_filtro;
		$labels['str_ano_1'] = $ano1;
		$labels['str_ano_2'] = $ano2;
		$labels['str_mes_desde'] = base::nom_mes($mes_desde);
		$labels['str_mes_hasta'] = base::nom_mes($mes_hasta);
		
		$rpt = new reporte($sql, $xml, $labels, "Resumen Venta.pdf", true);

		$this->_redraw();
	}
}
?>
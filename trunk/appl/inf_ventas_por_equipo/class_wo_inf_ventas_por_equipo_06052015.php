<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_modelo.php");

class wo_inf_ventas_por_equipo extends w_informe_pantalla {
	var $dw_stock;
	
   function wo_inf_ventas_por_equipo() {
   		// Construye el resultado del informe en un tabla AUXILIA de INFORME
		$ano = session::get("inf_ventas_por_equipo.ANO");
		session::un_set("inf_ventas_por_equipo.ANO");
		$cod_usuario = session::get("COD_USUARIO");;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_ventas_por_equipo", "$cod_usuario, $ano"); 
		$sql = "select	I.MES
						,I.ANO
					    ,I.COD_PRODUCTO                 
					    ,I.TIPO_DOC                     
					    ,I.COD_DOC                      
					    ,I.NRO_DOC                      
						,convert(varchar, I.FECHA_DOC, 103) FECHA_DOC
						,I.FECHA_DOC DATE_DOC
					    ,I.NOM_EMPRESA                  
					    ,I.CANTIDAD                     
					    ,I.PRECIO                       
					    ,I.TOTAL                            
					    ,case when I.CANTIDAD >=0 then I.CANTIDAD else 0 end CANT_FA                      
					    ,case when I.CANTIDAD < 0 then I.CANTIDAD else 0 end CANT_NC                      
					    ,case when I.CANTIDAD >=0 then I.TOTAL else 0 end TOT_FA                      
					    ,case when I.CANTIDAD < 0 then I.TOTAL else 0 end TOT_NC     
				FROM INF_VENTAS_POR_EQUIPO I
				where I.COD_USUARIO = $cod_usuario
				  and I.ANO = $ano
				order by DATE_DOC, I.NRO_DOC";  
		parent::w_informe_pantalla('inf_ventas_por_equipo', $sql, $_REQUEST['cod_item_menu']);
		$this->b_print_visible = false;
		
		// controls
		$this->dw->add_control(new static_num('PRECIO'));
		$this->dw->add_control(new static_num('TOTAL'));
		
		// headers
		$this->add_header($h_mes = new header_mes('MES', 'I.MES', 'Mes'));
		$this->add_header($h_cod_producto = new header_modelo('COD_PRODUCTO', 'I.COD_PRODUCTO', 'Modelo'));
		$this->add_header(new header_text('TIPO_DOC', 'I.TIPO_DOC', 'Tipo Doc'));
		$this->add_header(new header_num('NRO_DOC', 'I.NRO_DOC', 'Nro Doc'));
		$this->add_header($control = new header_date('FECHA_DOC', 'I.FECHA_DOC', 'Fecha'));
		$control->field_bd_order = 'I.DATE_DOC';
		$this->add_header(new header_text('NOM_EMPRESA', 'I.NOM_EMPRESA', 'Cliente'));
		$this->add_header(new header_num('CANTIDAD', 'I.CANTIDAD', 'CT', 0, true, 'SUM'));
		$this->add_header(new header_num('PRECIO', 'I.PRECIO', 'Precio'));
		$this->add_header(new header_num('TOTAL', 'I.TOTAL', 'Total', 0, true, 'SUM'));
		
		$this->add_header(new header_num('CANT_FA', 'case when I.CANTIDAD >=0 then I.CANTIDAD else 0 end', 'CANT_FA', 0, true, 'SUM'));
		$this->add_header(new header_num('CANT_NC', 'case when I.CANTIDAD < 0 then I.CANTIDAD else 0 end', 'CANT_NC', 0, true, 'SUM'));
		$this->add_header(new header_num('TOT_FA', 'case when I.CANTIDAD >=0 then I.TOTAL else 0 end', 'TOT_FA', 0, true, 'SUM'));
		$this->add_header(new header_num('TOT_NC', 'case when I.CANTIDAD < 0 then I.TOTAL else 0 end', 'TOT_NC', 0, true, 'SUM'));

   		// Filtro inicial
		$mes_desde = session::get("inf_ventas_por_equipo.MES_DESDE");
		$mes_hasta = session::get("inf_ventas_por_equipo.MES_HASTA");
		session::un_set("inf_ventas_por_equipo.MES_DESDE");
		session::un_set("inf_ventas_por_equipo.MES_HASTA");
		$h_mes->valor_filtro = $mes_desde;
		$h_mes->valor_filtro2 = $mes_hasta;
		
		$cod_producto = session::get("inf_ventas_por_equipo.COD_PRODUCTO");
		$find_exacto = session::get("inf_ventas_por_equipo.FIND_EXACTO");
		session::un_set("inf_ventas_por_equipo.COD_PRODUCTO");
		session::un_set("inf_ventas_por_equipo.FIND_EXACTO");
		if ($cod_producto != '') {
			$h_cod_producto->valor_filtro = $cod_producto."|".$find_exacto;
		}
		
		$this->row_per_page = 500;
		$this->make_filtros();	// filtro incial

		// dw stock
		if ($find_exacto=='S' && K_CLIENTE=='TODOINOX') 
			$sql = "select 'Stock' LABEL_STOCK
							,dbo.f_bodega_stock('$cod_producto', 1, getdate()) STOCK";
		else                 
			$sql = "select null LABEL_STOCK
							,null STOCK";
		$this->dw_stock = new datawindow($sql);
		$this->dw_stock->add_control(new static_num('STOCK'));
		$this->dw_stock->retrieve();
   }
	function redraw(&$temp) {
		$this->dw_stock->habilitar($temp, false);
	}
}
?>
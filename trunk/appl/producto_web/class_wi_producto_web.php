<?php
require_once(dirname(__FILE__) . "/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__) . "/../empresa/class_dw_help_empresa.php");

class dw_familia_accesorio extends datawindow{
	function dw_familia_accesorio(){
		$sql = "SELECT COD_FAMILIA_ACCESORIO
	   					,ORDEN FA_ORDEN
	   					,COD_FAMILIA_PRODUCTO
	   					,COD_PRODUCTO
	   					,COD_FAMILIA COD_FAMILIA_ACC  
				FROM FAMILIA_ACCESORIO
				WHERE COD_PRODUCTO = '{KEY1}'
				ORDER BY	FA_ORDEN ASC";
		
		parent::datawindow($sql, 'FAMILIA_ACCESORIO', true, true);
		$this->add_control(new edit_num('FA_ORDEN', 10));
		
		$sql = "SELECT COD_FAMILIA COD_FAMILIA_ACC
					  ,NOM_FAMILIA
				 FROM FAMILIA
				ORDER BY NOM_FAMILIA";
		$this->add_control(new drop_down_dw('COD_FAMILIA_ACC', $sql,300));
		
		// asigna los mandatorys
		$this->set_mandatory('FA_ORDEN', 'Orden');
		$this->set_mandatory('NOM_FAMILIA_PRODUCTO', 'Familia');

		// Setea el focus en NOM_ATRIBUTO_PRODUCTO para las nuevas lineas
		$this->set_first_focus('NOM_FAMILIA_PRODUCTO');
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'FA_ORDEN', $this->row_count() * 10);
		return $row;
	}
	function update($db){
		$sp = 'spu_familia_accesorio';	
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_familia_accesorio = $this->get_item($i, 'COD_FAMILIA_ACCESORIO');
			$orden = $this->get_item($i, 'FA_ORDEN');
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$cod_familia_producto = $this->get_item($i, 'COD_FAMILIA_PRODUCTO');
			$cod_familia_cc = $this->get_item($i, 'COD_FAMILIA_ACC');
			
			$cod_familia_accesorio = ($cod_familia_accesorio == '') ? "null" : $cod_familia_accesorio;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			
			$param = "'$operacion',$cod_familia_accesorio, NULL,'$cod_producto', $orden,$cod_familia_cc";
			
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++){
			//$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED){
				continue;
			}
			$cod_familia_accesorio = $this->get_item($i, 'COD_FAMILIA_ACCESORIO', 'delete');
			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_familia_accesorio")){
				return false;
			}
		}
		return true;
	}
}

class dw_familia_producto extends datawindow{
	
	function dw_familia_producto(){
		
		$sql = "SELECT	COD_FAMILIA_PRODUCTO
						,ORDEN FP_ORDEN
						,NOM_FAMILIA_PRODUCTO
						,COD_PRODUCTO
						,COD_PRODUCTO COD_PRODUCTO_FP
						,COD_FAMILIA
				FROM FAMILIA_PRODUCTO
				WHERE COD_PRODUCTO = '{KEY1}'
				ORDER BY FP_ORDEN ASC";
		
		parent::datawindow($sql, 'FAMILIA_PRODUCTO', true, true);
		$this->add_control(new edit_num('FP_ORDEN', 10));
		
		$sql = "SELECT COD_FAMILIA
					  ,NOM_FAMILIA
				 FROM FAMILIA
				ORDER BY NOM_FAMILIA";
		$this->add_control(new drop_down_dw('COD_FAMILIA', $sql,300));
		// asigna los mandatorys
		$this->set_mandatory('FP_ORDEN', 'Orden');
		$this->set_mandatory('NOM_FAMILIA_PRODUCTO', 'Familia');
		// Setea el focus en NOM_ATRIBUTO_PRODUCTO para las nuevas lineas
		$this->set_first_focus('NOM_FAMILIA_PRODUCTO');
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'FP_ORDEN', $this->row_count() * 10);
		return $row;
	}
	function update($db){
		$sp = 'spu_familia_prod';	
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_familia_producto = $this->get_item($i, 'COD_FAMILIA_PRODUCTO');
			$orden = $this->get_item($i, 'FP_ORDEN');
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$nom_familia_producto = $this->get_item($i, 'NOM_FAMILIA_PRODUCTO');
			$cod_familia = $this->get_item($i, 'COD_FAMILIA');
			
			$cod_familia_producto = ($cod_familia_producto == '') ? "null" : $cod_familia_producto;
			$nom_familia_producto = ($nom_familia_producto == '') ? "null" : $nom_familia_producto;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			$param = "'$operacion'
						,$cod_familia_producto
						,$nom_familia_producto
						, $cod_familia
						,'$cod_producto'
						, $orden";
						
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++){

			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
			
			$cod_familia_producto = $this->get_item($i, 'COD_FAMILIA_PRODUCTO', 'delete');
			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_familia_producto")){
				return false;
			}
		}
		return true;
	}
}

class dw_atributo_producto extends datawindow{
	
	function dw_atributo_producto(){
		
		$sql = "select    	COD_ATRIBUTO_PRODUCTO
	                        ,ORDEN AP_ORDEN
	                        ,NOM_ATRIBUTO_PRODUCTO
	                        ,COD_PRODUCTO
              	from      	ATRIBUTO_PRODUCTO
              	where      	COD_PRODUCTO = '{KEY1}'
              	order by	AP_ORDEN asc";
		
		parent::datawindow($sql, 'ATRIBUTO_PRODUCTO', true, true);
		$this->add_control(new edit_num('AP_ORDEN', 10));
		$this->add_control(new edit_text('NOM_ATRIBUTO_PRODUCTO', 100, 1000));
		// asigna los mandatorys
		$this->set_mandatory('AP_ORDEN', 'Orden');
		$this->set_mandatory('NOM_ATRIBUTO_PRODUCTO', 'Atributo');

		// Setea el focus en NOM_ATRIBUTO_PRODUCTO para las nuevas lineas
		$this->set_first_focus('NOM_ATRIBUTO_PRODUCTO');
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'AP_ORDEN', $this->row_count() * 10);
		return $row;
	}
	function update($db){
		$sp = 'spu_atributo_producto';	
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_atributo_producto = $this->get_item($i, 'COD_ATRIBUTO_PRODUCTO');
			$orden = $this->get_item($i, 'AP_ORDEN');
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$nom_atributo_producto = $this->get_item($i, 'NOM_ATRIBUTO_PRODUCTO');
			$cod_atributo_producto = ($cod_atributo_producto == '') ? "null" : $cod_atributo_producto;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			$param = "'$operacion',$cod_atributo_producto, '$nom_atributo_producto','$cod_producto', $orden";

			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++){
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED){
				continue;
			}
			$cod_atributo_producto = $this->get_item($i, 'COD_ATRIBUTO_PRODUCTO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_atributo_producto")){
				return false;
			}
		}
		//  Ordernar
		if ($this->row_count() > 0){
			$cod_producto = $this->get_item(0, 'COD_PRODUCTO');
			$parametros_sp = "'ATRIBUTO_PRODUCTO','PRODUCTO', null, '$cod_producto'";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)){
				return false;
			}
		}
		return true;
	}
}

class wi_producto_web extends w_input{
	
	function wi_producto_web($cod_item_menu){
		parent::w_input('producto_web', $cod_item_menu);
		
		$sql = "select   P.COD_PRODUCTO
						,P.COD_PRODUCTO COD_PRODUCTO_PRINCIPAL
						,P.COD_PRODUCTO COD_PRODUCTO_H
			            ,P.NOM_PRODUCTO NOM_PRODUCTO_PRINCIPAL
			            ,LARGO
			            ,ANCHO
			            ,ALTO
			            ,PESO
			            ,(LARGO/100 * ANCHO/100 * ALTO/100) VOLUMEN
			            ,LARGO_EMBALADO
			            ,ANCHO_EMBALADO
			            ,ALTO_EMBALADO
			            ,PESO_EMBALADO
			            ,(LARGO_EMBALADO/100 * ANCHO_EMBALADO/100 * ALTO_EMBALADO/100) VOLUMEN_EMBALADO
			            ,P.COD_PRODUCTO COD_PRODUCTO_NO_ING 
			            ,P.NOM_PRODUCTO NOM_PRODUCTO_NO_ING
			            ,P.PRECIO_VENTA_PUBLICO PRECIO_VENTA_PUBLICO_NO_ING
			            ,NOM_TIPO_PRODUCTO
			            ,USA_ELECTRICIDAD
			            ,NRO_FASES MONOFASICO
			            ,NRO_FASES TRIFASICO
			            ,CONSUMO_ELECTRICIDAD
			            ,RANGO_TEMPERATURA
			            ,VOLTAJE
			            ,FRECUENCIA
			            ,NRO_CERTIFICADO_ELECTRICO
			            ,USA_GAS
			            ,POTENCIA
			            ,CONSUMO_GAS
			            ,USA_VAPOR
			            ,NRO_CERTIFICADO_GAS
			            ,CONSUMO_VAPOR
			            ,PRESION_VAPOR
			            ,USA_AGUA_FRIA
			            ,USA_AGUA_CALIENTE
			            ,CAUDAL
			            ,PRESION_AGUA
			            ,DIAMETRO_CANERIA
			            ,USA_VENTILACION
			            ,CAIDA_PRESION
			            ,DIAMETRO_DUCTO
			            ,VOLUMEN VOLUMEN_ESP
			            ,NRO_FILTROS
			            ,USA_DESAGUE
			            ,DIAMETRO_DESAGUE
			            ,ES_OFERTA 
			            ,ES_RECICLADO 
			            ,PRECIO_OFERTA
        from   			PRODUCTO P
        				,TIPO_PRODUCTO TP
        where			P.COD_PRODUCTO = '{KEY1}'
        AND P.COD_TIPO_PRODUCTO = TP.COD_TIPO_PRODUCTO";
		$this->dws['dw_producto_web'] = new datawindow($sql);

		$this->set_first_focus('COD_PRODUCTO_PRINCIPAL');
		
		$this->dws['dw_producto_web']->add_control(new edit_text('COD_PRODUCTO_H',10, 10, 'hidden'));
		$this->dws['dw_producto_web']->add_control(new edit_text_upper('NOM_PRODUCTO_PRINCIPAL', 100, 100));
		$this->dws['dw_producto_web']->add_control(new edit_num('LARGO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('ANCHO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('ALTO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('PESO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('LARGO_EMBALADO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('ANCHO_EMBALADO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('ALTO_EMBALADO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('PESO_EMBALADO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('VOLUMEN_ESP'));
		$this->dws['dw_producto_web']->set_computed('VOLUMEN', '[LARGO] * [ANCHO] * [ALTO] / 1000000', 4);
		$this->dws['dw_producto_web']->set_computed('VOLUMEN_EMBALADO', '[LARGO_EMBALADO] * [ANCHO_EMBALADO] * [ALTO_EMBALADO] / 1000000', 4);
		
		//especificaciones
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_ELECTRICIDAD', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_radio_button('TRIFASICO', 'T', 'M', 'TRIFASICO', 'NRO_FASES'));
		$this->dws['dw_producto_web']->add_control(new edit_radio_button('MONOFASICO', 'M', 'T', 'MONOFASICO', 'NRO_FASES'));
		$this->dws['dw_producto_web']->add_control(new edit_num('CONSUMO_ELECTRICIDAD', 16, 16, 2));
		$this->dws['dw_producto_web']->add_control(new edit_num('RANGO_TEMPERATURA'));
		$this->dws['dw_producto_web']->add_control(new edit_num('VOLTAJE'));
		$this->dws['dw_producto_web']->add_control(new edit_num('FRECUENCIA'));
		$this->dws['dw_producto_web']->add_control(new edit_text_upper('NRO_CERTIFICADO_ELECTRICO', 100, 100));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_GAS', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_num('POTENCIA'));
		// VMC, 17-08-2011 se deja no ingresable por solicitud de JJ a traves de MH 
		$this->dws['dw_producto_web']->add_control(new edit_num('CONSUMO_GAS'));
		$this->dws['dw_producto_web']->add_control(new edit_text_upper('NRO_CERTIFICADO_GAS', 100, 100));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_VAPOR', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_num('POTENCIA_KW'));
		$this->dws['dw_producto_web']->add_control(new edit_num('CONSUMO_VAPOR'));
		$this->dws['dw_producto_web']->add_control(new edit_num('PRESION_VAPOR'));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_AGUA_FRIA', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_AGUA_CALIENTE', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_num('CAUDAL'));
		$this->dws['dw_producto_web']->add_control(new edit_num('PRESION_AGUA'));
		$this->dws['dw_producto_web']->add_control(new edit_text('DIAMETRO_CANERIA', 10, 10));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_VENTILACION', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_num('CAIDA_PRESION'));
		$this->dws['dw_producto_web']->add_control(new edit_num('DIAMETRO_DUCTO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('NRO_FILTROS'));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_DESAGUE', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_text('DIAMETRO_DESAGUE', 10, 10));
		$this->dws['dw_producto_web']->add_control(new edit_text('FOTO_CON_CAMBIO', 10, 10, 'hidden'));
		
		$this->dws['dw_producto_web']->add_control(new edit_check_box('ES_OFERTA', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('ES_RECICLADO', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_num('PRECIO_OFERTA'));
		
		$this->dws['dw_atributo_producto'] = new dw_atributo_producto();
		$this->dws['dw_familia_producto'] = new dw_familia_producto();
		$this->dws['dw_familia_accesorio'] = new dw_familia_accesorio();
		
		$this->add_auditoria('NOM_PRODUCTO');
		
		$this->add_auditoria('LARGO');
		$this->add_auditoria('ANCHO');
		$this->add_auditoria('ALTO');
		$this->add_auditoria('PESO');
		
		$this->add_auditoria('LARGO_EMBALADO');
		$this->add_auditoria('ANCHO_EMBALADO');
		$this->add_auditoria('ALTO_EMBALADO');
		$this->add_auditoria('PESO_EMBALADO');
		
		$this->add_auditoria('USA_ELECTRICIDAD');
		$this->add_auditoria('CONSUMO_ELECTRICIDAD');
		$this->add_auditoria('VOLTAJE');
		$this->add_auditoria('NRO_FASES');
		$this->add_auditoria('RANGO_TEMPERATURA');
		$this->add_auditoria('FRECUENCIA');
		$this->add_auditoria('NRO_CERTIFICADO_ELECTRICO');
		$this->add_auditoria('USA_GAS');
		$this->add_auditoria('POTENCIA');
		
		$this->add_auditoria('CONSUMO_GAS');
		$this->add_auditoria('USA_VAPOR');
		$this->add_auditoria('CONSUMO_VAPOR');
		$this->add_auditoria('PRESION_VAPOR');
		$this->add_auditoria('USA_AGUA_FRIA');
		$this->add_auditoria('USA_AGUA_CALIENTE');
		$this->add_auditoria('CAUDAL');
		
		$this->add_auditoria('PRESION_AGUA');
		$this->add_auditoria('DIAMETRO_CANERIA');
		$this->add_auditoria('USA_VENTILACION');
		$this->add_auditoria('VOLUMEN');
		$this->add_auditoria('CAIDA_PRESION');
		$this->add_auditoria('DIAMETRO_DUCTO');
		$this->add_auditoria('NRO_FILTROS');
		$this->add_auditoria('USA_DESAGUE');
		$this->add_auditoria('DIAMETRO_DESAGUE');
		
		$this->add_auditoria('FOTO_GRANDE');
		$this->add_auditoria('FOTO_CHICA');
		
		$this->add_auditoria('ES_OFERTA');
		$this->add_auditoria('PRECIO_OFERTA');
		$this->add_auditoria('ES_RECICLADO');
		
		$this->add_auditoria_relacionada('ATRIBUTO_PRODUCTO','AP_ORDEN', 'ORDEN');
		$this->add_auditoria_relacionada('ATRIBUTO_PRODUCTO','NOM_ATRIBUTO_PRODUCTO');
		
		$this->add_auditoria_relacionada('FAMILIA_PRODUCTO','FP_ORDEN','ORDEN');
		$this->add_auditoria_relacionada('FAMILIA_PRODUCTO','COD_FAMILIA');
		
		$this->add_auditoria_relacionada('FAMILIA_ACCESORIO','FA_ORDEN','ORDEN');
		$this->add_auditoria_relacionada('FAMILIA_ACCESORIO','COD_FAMILIA_ACC','COD_FAMILIA');
		
		
	}
	function habilitar($temp, $habilita){		
		$html = '';
		for ($i = 0; $i < $this->dws['dw_atributo_producto']->row_count(); $i++)
		$html .= '<img src="../../../../commonlib/trunk/images/ico2.gif"width="14"height="15">' . $this->dws['dw_atributo_producto']->get_item($i, 'NOM_ATRIBUTO_PRODUCTO') . '<br>';
		$temp->setVar("LISTA_ATRIBUTOS", $html);
	}
	function make_sql_auditoria_relacionada($tabla) {
		
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'PRODUCTO';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla; 
		return $sql;
		
	}
	function make_sql_auditoria() {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'PRODUCTO';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla; 
		return $sql;
	}
	function load_record(){
		$cod_producto = $this->get_item_wo($this->current_record, 'COD_PRODUCTO');
		$this->dws['dw_producto_web']->retrieve($cod_producto);
		$this->dws['dw_atributo_producto']->retrieve($cod_producto);
		$this->dws['dw_familia_producto']->retrieve($cod_producto);
		$this->dws['dw_familia_accesorio']->retrieve($cod_producto);
		
	}

	function get_key(){
		
		$cod_producto = $this->dws['dw_producto_web']->get_item(0, 'COD_PRODUCTO_PRINCIPAL');
		return "'" . $cod_producto . "'";
	}

	function save_record($db){		
		$cod_producto 				= $this->dws['dw_producto_web']->get_item(0, 'COD_PRODUCTO_PRINCIPAL');
		$nom_producto 				= $this->dws['dw_producto_web']->get_item(0, 'NOM_PRODUCTO_PRINCIPAL');
		
		$largo 						= $this->dws['dw_producto_web']->get_item(0, 'LARGO');
		$ancho 						= $this->dws['dw_producto_web']->get_item(0, 'ANCHO');
		$alto 						= $this->dws['dw_producto_web']->get_item(0, 'ALTO');
		$peso 						= $this->dws['dw_producto_web']->get_item(0, 'PESO');
		$largo_embalado 			= $this->dws['dw_producto_web']->get_item(0, 'LARGO_EMBALADO');
		$ancho_embalado 			= $this->dws['dw_producto_web']->get_item(0, 'ANCHO_EMBALADO');
		$alto_embalado 				= $this->dws['dw_producto_web']->get_item(0, 'ALTO_EMBALADO');
		$peso_embalado 				= $this->dws['dw_producto_web']->get_item(0, 'PESO_EMBALADO');
		
		$usa_electricidad 			= $this->dws['dw_producto_web']->get_item(0, 'USA_ELECTRICIDAD');
		$nro_fases 					= $this->dws['dw_producto_web']->get_item(0, 'TRIFASICO');
		$consumo_electricidad 		= $this->dws['dw_producto_web']->get_item(0, 'CONSUMO_ELECTRICIDAD');
		$rango_temperatura 			= $this->dws['dw_producto_web']->get_item(0, 'RANGO_TEMPERATURA');
		$voltaje 					= $this->dws['dw_producto_web']->get_item(0, 'VOLTAJE');
		$nro_certificado_electrico 	= $this->dws['dw_producto_web']->get_item(0, 'NRO_CERTIFICADO_ELECTRICO');
		$frecuencia 				= $this->dws['dw_producto_web']->get_item(0, 'FRECUENCIA');
		$usa_gas 					= $this->dws['dw_producto_web']->get_item(0, 'USA_GAS');
		$potencia 					= $this->dws['dw_producto_web']->get_item(0, 'POTENCIA');
		$consumo_gas 				= $this->dws['dw_producto_web']->get_item(0, 'CONSUMO_GAS');
		$nro_certificado_gas 		= $this->dws['dw_producto_web']->get_item(0, 'NRO_CERTIFICADO_GAS');
		$usa_vapor 					= $this->dws['dw_producto_web']->get_item(0, 'USA_VAPOR');
		$consumo_vapor 				= $this->dws['dw_producto_web']->get_item(0, 'CONSUMO_VAPOR');
		$presion_vapor 				= $this->dws['dw_producto_web']->get_item(0, 'PRESION_VAPOR');
		$usa_agua_fria 				= $this->dws['dw_producto_web']->get_item(0, 'USA_AGUA_FRIA');
		$usa_agua_caliente 			= $this->dws['dw_producto_web']->get_item(0, 'USA_AGUA_CALIENTE');
		$caudal 					= $this->dws['dw_producto_web']->get_item(0, 'CAUDAL');
		$presion_agua 				= $this->dws['dw_producto_web']->get_item(0, 'PRESION_AGUA');
		$diametro_caneria 			= $this->dws['dw_producto_web']->get_item(0, 'DIAMETRO_CANERIA');
		$usa_ventilacion 			= $this->dws['dw_producto_web']->get_item(0, 'USA_VENTILACION');
		$volumen					= $this->dws['dw_producto_web']->get_item(0, 'VOLUMEN_ESP');
		$caida_presion 				= $this->dws['dw_producto_web']->get_item(0, 'CAIDA_PRESION');
		$diametro_ducto 			= $this->dws['dw_producto_web']->get_item(0, 'DIAMETRO_DUCTO');
		$nro_filtros 				= $this->dws['dw_producto_web']->get_item(0, 'NRO_FILTROS');
		$usa_desague 				= $this->dws['dw_producto_web']->get_item(0, 'USA_DESAGUE');
		$diametro_desague			= $this->dws['dw_producto_web']->get_item(0, 'DIAMETRO_DESAGUE');
		
		$es_oferta 				= $this->dws['dw_producto_web']->get_item(0, 'ES_OFERTA');
		$precio_oferta			= $this->dws['dw_producto_web']->get_item(0, 'PRECIO_OFERTA');
		if($precio_oferta == ''){
		$precio_oferta = 0; 
		}
		$es_reciclado			= $this->dws['dw_producto_web']->get_item(0, 'ES_RECICLADO');
		$nom_producto_ingles 		= ($nom_producto_ingles == '') ? "null" : "'$nom_producto_ingles'";
		$cod_familia_producto 		= ($cod_familia_producto == '') ? "null" : $cod_familia_producto;
		$nro_fases 					= ($nro_fases == '') ? "null" : "'$nro_fases'";
		$consumo_electricidad		= ($consumo_electricidad == '') ? "null" : $consumo_electricidad;
		$rango_temperatura 			= ($rango_temperatura == '') ? "null" : "'$rango_temperatura'";
		$voltaje 					= ($voltaje == '') ? "null" : $voltaje;
		$frecuencia 				= ($frecuencia == '') ? "null" : $frecuencia;
		$nro_certificado_electrico	= ($nro_certificado_electrico == '') ? "null" : "'$nro_certificado_electrico'";
		$potencia 					= ($potencia == '') ? "null" : $potencia;
		$consumo_gas 				= ($consumo_gas == '') ? "null" : $consumo_gas;
		$nro_certificado_gas 		= ($nro_certificado_gas == '') ? "null" : "'$nro_certificado_gas'";
		$consumo_vapor 				= ($consumo_vapor == '') ? "null" : $consumo_vapor;
		$presion_vapor 				= ($presion_vapor == '') ? "null" : $presion_vapor;
		$caudal 					= ($caudal == '') ? "null" : $caudal;
		$presion_agua 				= ($presion_agua == '') ? "null" : $presion_agua;
		$diametro_caneria 			= ($diametro_caneria == '') ? "null" : "'$diametro_caneria'";
		$volumen 					= ($volumen == '') ? "null" : $volumen;
		$caida_presion 				= ($caida_presion == '') ? "null" : $caida_presion;
		$potencia_kw				= ($potencia_kw == '') ? "null" : $potencia_kw;
		$diametro_ducto 			= ($diametro_ducto == '') ? "null" : $diametro_ducto;
		$nro_filtros 				= ($nro_filtros == '') ? "null" : $nro_filtros;
		$diametro_desague 			= ($diametro_desague == '') ? "null" : "'$diametro_desague'";
		$stock_critico 				= ($stock_critico == '') ? "null" : $stock_critico;
		$foto_grande 				= ($foto_grande == '') ? "null" : $foto_grande;
		$foto_chica 				= ($foto_chica == '') ? "null" : $foto_chica;
		$cod_producto 				= ($cod_producto == '') ? "null" : $cod_producto;
		$cod_producto_local			= ($cod_producto_local == '') ? "null" : $cod_producto_local;
		
		$sp = 'spu_producto_web';
		$operacion = 'UPDATE';
		$param = "'$operacion'
				,'$cod_producto'
				,'$nom_producto'
				,$largo
				,$ancho
				,$alto
				,$peso
				,$largo_embalado
				,$ancho_embalado
				,$alto_embalado
				,$peso_embalado
				,'$usa_electricidad'
				,$nro_fases
				,$consumo_electricidad
				,$rango_temperatura
				,$voltaje
				,$nro_certificado_electrico
				,$frecuencia
				,'$usa_gas'
				,$potencia
				,$consumo_gas
				,$nro_certificado_gas
				,'$usa_vapor'
				,$consumo_vapor
				,$presion_vapor
				,'$usa_agua_fria'
				,'$usa_agua_caliente'
				,$caudal
				,$presion_agua
				,$diametro_caneria
				,'$usa_ventilacion'
				,$volumen
				,$caida_presion
				,$diametro_ducto
				,$nro_filtros
				,'$usa_desague'
				,$diametro_desague
				,'$es_oferta'
				,$precio_oferta
				,'$es_reciclado'";
			
		if ($db->EXECUTE_SP($sp, $param)){
			
				
			
			for ($i = 0; $i < $this->dws['dw_atributo_producto']->row_count(); $i++){
				$this->dws['dw_atributo_producto']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			for ($i = 0; $i < $this->dws['dw_familia_producto']->row_count(); $i++){
				$this->dws['dw_familia_producto']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			
			for ($i = 0; $i < $this->dws['dw_familia_accesorio']->row_count(); $i++){
					$this->dws['dw_familia_accesorio']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			
			if (!$this->dws['dw_atributo_producto']->update($db))
				return false;
				
			if (!$this->dws['dw_familia_producto']->update($db))
				return false;

			if (!$this->dws['dw_familia_accesorio']->update($db))
				return false;	

			if (!$this->subir_imagen($db, $cod_producto))
				return false;
				
			
			return true;
		}
		return false;
	}
function subir_imagen($db, $cod_producto){
		$foto_chica = $_FILES['FOTO_CHICA']['tmp_name'];
		
		echo $foto_chica.'<br>';
		$foto_grande = $_FILES['FOTO_GRANDE']['tmp_name'];
		echo '---'.$foto_grande.'---';

		If ($foto_chica <> ''){
			$datastring_chica = file_get_contents($foto_chica);
			$data_chica = unpack("H*hex", $datastring_chica);
			$hexa_chica = '0x' . $data_chica['hex'];
		}
		else {
			$hexa_chica = '0x';
		}

		If ($foto_grande <> ''){
			$datastring_grande = file_get_contents($foto_grande);
			$data_grande = unpack("H*hex", $datastring_grande);
			$hexa_grande = '0x' . $data_grande['hex'];
		}
		else {
			$hexa_grande = '0x';
		}

		$sp = 'sp_subir_imagen';
		$param = "$hexa_chica, $hexa_grande, '$cod_producto'";

		if ($db->EXECUTE_SP($sp, $param)){
			return true;
		}
		return false;
	}
}
?>
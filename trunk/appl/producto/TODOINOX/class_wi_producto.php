<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

class dw_producto_compuesto extends dw_producto_compuesto_base{
	function dw_producto_compuesto(){
		parent::dw_producto_compuesto_base();
	
		$this->add_control(new edit_check_box('ARMA_COMPUESTO','S','N'));
						
		$this->controls['NOM_PRODUCTO']->size = 52;
		$this->controls['COD_PRODUCTO']->size = 11;
		
		$this->set_first_focus('COD_PRODUCTO');
	}

	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN_PC', $this->row_count() * 10);
		return $row;
	}


		
	function update($db){
		$sp = 'spu_producto_compuesto';
		for ($i = 0; $i < $this->row_count(); $i++){			
			$statuts = $this->get_status_row($i);
			
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}			
				
			$cod_producto_compuesto = $this->get_item($i, 'COD_PRODUCTO_COMPUESTO');
			$cod_producto_principal = $this->get_item($i, 'COD_PRODUCTO_PRINCIPAL');
			$cod_producto_hijo 		= $this->get_item($i, 'COD_PRODUCTO');
			$orden 					= $this->get_item($i, 'ORDEN_PC');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');
			$genera_compra 			= $this->get_item($i, 'GENERA_COMPRA');
			$arma_compuesto			= $this->get_item($i, 'ARMA_COMPUESTO');
			
			$cod_producto_compuesto = ($cod_producto_compuesto == '') ? "null" : "$cod_producto_compuesto";
			
			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
						
			$param = "'$operacion', $cod_producto_compuesto,'$cod_producto_principal','$cod_producto_hijo',$orden,$cantidad, '$genera_compra', '$arma_compuesto'";
			
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {			
			$cod_producto_compuesto = $this->get_item($i, 'COD_PRODUCTO_COMPUESTO', 'delete');			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_producto_compuesto")){			
			}			
		}		
		//Ordernar
		if ($this->row_count() > 0){
			$cod_producto = $this->get_item(0, 'COD_PRODUCTO_PRINCIPAL');			
			$parametros_sp = "'PRODUCTO_COMPUESTO','PRODUCTO', null, '$cod_producto'";			 
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)){
				return false;
			}
		}		
		
		return true;
	}
}

class wi_producto extends wi_producto_base {
	const K_BODEGA_EQ_TERMINADO = 1;
	const K_DOLAR_COMERCIAL = 5;
	const K_MENU_PRODUCTO = '995005';			
	function wi_producto($cod_item_menu) {
		parent::wi_producto_base($cod_item_menu);
		 
		$cod_usuario = session::get("COD_USUARIO");
		//$sql_original = $this->dws['dw_producto']->get_sql();
		$sql = "select   P.COD_PRODUCTO COD_PRODUCTO_PRINCIPAL 
						,P.COD_PRODUCTO COD_PRODUCTO_H
			            ,P.NOM_PRODUCTO NOM_PRODUCTO_PRINCIPAL
			            ,TP.COD_TIPO_PRODUCTO
			            ,NOM_TIPO_PRODUCTO
			            ,P.COD_MARCA
			            ,'' MARCA_H
			            ,NOM_MARCA
			            ,NOM_MARCA NOM_MARCA_NO_ING
			            ,NOM_PRODUCTO_INGLES
			            ,NOM_PRODUCTO_INGLES NOM_PRODUCTO_INGLES_NO_ING
			            ,COD_FAMILIA_PRODUCTO
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
			            ,FACTOR_VENTA_INTERNO
			            ,PRECIO_VENTA_INTERNO
			            ,PRECIO_VENTA_INTERNO PRECIO_VENTA_INTERNO_NO_ING
			            ,FACTOR_VENTA_PUBLICO
			            ,PRECIO_VENTA_PUBLICO			            
			            ,PRECIO_VENTA_PUBLICO PRECIO_VENTA_PUBLICO_H
			            ,dbo.f_redondeo_tdnx(PRECIO_VENTA_INTERNO * FACTOR_VENTA_PUBLICO) PRECIO_VENTA_PUB_SUG
			            ,'none' PRECIO_INTERNO_ALTO
			            ,'none' PRECIO_INTERNO_BAJO			            
			            ,'none' PRECIO_PUBLICO_ALTO
			            ,'none' PRECIO_PUBLICO_BAJO
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
			            ,NRO_FILTROS
			            ,USA_DESAGUE
			            ,VOLUMEN VOLUMEN_ESP
			            ,P.POTENCIA_KW
			            ,DIAMETRO_DESAGUE
			            ,MANEJA_INVENTARIO
			            ,STOCK_CRITICO
			            ,TIEMPO_REPOSICION
		                ,FOTO_GRANDE
		                ,FOTO_CHICA
		                ,'' FOTO_CON_CAMBIO
		                ,PL.ES_COMPUESTO
		                ,P.COD_CLASIF_INVENTARIO
		                ,PRECIO_LIBRE
		                ,ES_DESPACHABLE
		                ,'' TABLE_PRODUCTO_COMPUESTO
		                ,'' ULTIMO_REG_INGRESO
		                ,case 
	                    when (dbo.f_get_autoriza_menu($cod_usuario, ".self::K_MENU_PRODUCTO.") = 'E') then dbo.number_format(dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_EQ_TERMINADO.", GETDATE()),0,',','.')
                    	when (dbo.f_get_autoriza_menu($cod_usuario, ".self::K_MENU_PRODUCTO.") = 'N') and (dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_EQ_TERMINADO.", GETDATE()) > 0)  then 'HAY'
                    	else 'NO HAY'
                		end STOCK
		                ,COD_EQUIPO_OC_EX
		                ,DESC_EQUIPO_OC_EX
						,dbo.f_prod_RI(P.COD_PRODUCTO, 'NUMERO_REGISTRO_INGRESO') NUMERO_REGISTRO_INGRESO 
						,convert(varchar(10),dbo.f_get_fecha_registro_ingreso(P.COD_PRODUCTO),103) FECHA_REGISTRO_INGRESO 
						,dbo.f_prod_RI(P.COD_PRODUCTO, 'PRECIO') COSTO_EX_FCA 
						,dbo.f_prod_RI(P.COD_PRODUCTO, 'FACTOR_IMP') FACTOR_IMP 
						,round(dbo.f_prod_RI(P.COD_PRODUCTO, 'PRECIO') * dbo.f_prod_RI(P.COD_PRODUCTO, 'FACTOR_IMP'),2) COSTO_BASE_DOLAR
						,round(dbo.f_prod_RI(P.COD_PRODUCTO, 'PRECIO') * dbo.f_prod_RI(P.COD_PRODUCTO, 'FACTOR_IMP'),2) COSTO_BASE_DOLAR_H
						,dbo.f_prod_get_costo_base(P.COD_PRODUCTO) COSTO_BASE_PI
						,dbo.f_prod_get_costo_base(P.COD_PRODUCTO) COSTO_BASE_PESOS
						,dbo.f_prod_get_costo_base(P.COD_PRODUCTO) COSTO_BASE_PESOS_H
		                ,dbo.f_get_parametro(".self::K_DOLAR_COMERCIAL.") DOLAR   
				        ,(SELECT CONVERT(VARCHAR(10),max(E.FECHA_ENTRADA_BODEGA),103)
							FROM ITEM_ENTRADA_BODEGA I
								 ,ENTRADA_BODEGA E			
							WHERE I.COD_PRODUCTO = P.COD_PRODUCTO	
							AND E.COD_ENTRADA_BODEGA = I.COD_ENTRADA_BODEGA) FECHA_ENTRADA_BODEGA
				        ,(SELECT max(E.COD_ENTRADA_BODEGA)
							FROM ITEM_ENTRADA_BODEGA I
								 ,ENTRADA_BODEGA E			
							WHERE I.COD_PRODUCTO = P.COD_PRODUCTO	
							AND E.COD_ENTRADA_BODEGA = I.COD_ENTRADA_BODEGA) NRO_ENTRADA_BODEGA
				        ,(SELECT CONVERT(VARCHAR(10),max(E.FECHA_SALIDA_BODEGA),103)
							FROM ITEM_SALIDA_BODEGA I
								 ,SALIDA_BODEGA E			
							WHERE I.COD_PRODUCTO = P.COD_PRODUCTO	
							AND E.COD_SALIDA_BODEGA = I.COD_SALIDA_BODEGA) FECHA_SALIDA_BODEGA
				        ,(SELECT max(E.COD_SALIDA_BODEGA)
							FROM ITEM_SALIDA_BODEGA I
								 ,SALIDA_BODEGA E			
							WHERE I.COD_PRODUCTO = P.COD_PRODUCTO	
							AND E.COD_SALIDA_BODEGA = I.COD_SALIDA_BODEGA) NRO_SALIDA_BODEGA
			            ,dbo.f_redondeo_tdnx(dbo.f_prod_get_costo_base(P.COD_PRODUCTO) * FACTOR_VENTA_INTERNO) PRECIO_VENTA_INT_SUG
			            ,dbo.f_redondeo_tdnx(dbo.f_prod_get_costo_base(P.COD_PRODUCTO) * FACTOR_VENTA_INTERNO) PRECIO_VENTA_INT_SUG_H
	        			,dbo.f_bodega_pmp_us(P.COD_PRODUCTO,".self::K_BODEGA_EQ_TERMINADO.",getdate()) PMP_US
	        			,dbo.f_bodega_pmp(P.COD_PRODUCTO,".self::K_BODEGA_EQ_TERMINADO.",getdate()) PMP_PESOS
        from   			PRODUCTO P 
        				,MARCA M
        				,TIPO_PRODUCTO TP
        				,PRODUCTO_LOCAL PL
        where			P.COD_PRODUCTO = '{KEY1}'
        				AND P.COD_MARCA = M.COD_MARCA
        				AND PL.COD_PRODUCTO = P.COD_PRODUCTO
        				AND P.COD_TIPO_PRODUCTO = TP.COD_TIPO_PRODUCTO";
		$this->dws['dw_producto']->set_sql($sql);
		$this->dws['dw_producto']->retrieve();
		
		
		// asigna los formatos
		$this->dws['dw_producto']->add_control(new static_text('COD_EQUIPO_OC_EX'));
		$this->dws['dw_producto']->add_control(new static_text('DESC_EQUIPO_OC_EX'));
		
		$this->dws['dw_producto']->add_control(new static_text('NUMERO_REGISTRO_INGRESO'));
		$this->dws['dw_producto']->add_control(new static_text('FECHA_REGISTRO_INGRESO'));
		$this->dws['dw_producto']->add_control(new static_num('COSTO_EX_FCA', 2));
		$this->dws['dw_producto']->add_control(new static_num('FACTOR_IMP', 1));
		$this->dws['dw_producto']->add_control(new static_num('DOLAR'));
		$this->dws['dw_producto']->add_control(new static_num('COSTO_BASE_DOLAR', 2));

		$this->dws['dw_producto']->add_control(new static_text('FECHA_ENTRADA_BODEGA'));
		$this->dws['dw_producto']->add_control(new static_text('NRO_ENTRADA_BODEGA'));
		$this->dws['dw_producto']->add_control(new static_text('FECHA_SALIDA_BODEGA'));
		$this->dws['dw_producto']->add_control(new static_text('NRO_SALIDA_BODEGA'));
		$this->dws['dw_producto']->add_control(new edit_text('COSTO_BASE_DOLAR_H', 20, 20, 'hidden'));
		$this->dws['dw_producto']->add_control(new edit_text('COSTO_BASE_PESOS_H', 20, 20, 'hidden'));
		$this->dws['dw_producto']->add_control(new edit_text('PRECIO_VENTA_INT_SUG_H', 20, 20, 'hidden'));
		$this->dws['dw_producto']->add_control(new static_num('PMP_US'));
		$this->dws['dw_producto']->add_control(new static_num('PMP_PESOS'));
		
	}
	
	function load_record(){
		parent::load_record();
		
		$costo_base_pesos = $this->dws['dw_producto']->get_item(0, 'COSTO_BASE_PESOS_H');
		$costo_base_pesos	=  number_format($costo_base_pesos, 0, ',', '.');
		$this->dws['dw_producto']->set_item(0, 'COSTO_BASE_PI', $costo_base_pesos);
		$this->dws['dw_producto']->set_item(0, 'MARCA_H', '');
	}
	function save_record($db){	
		//parent::save_record();	
		$cod_producto 				= $this->dws['dw_producto']->get_item(0, 'COD_PRODUCTO_PRINCIPAL');
		$nom_producto 				= $this->dws['dw_producto']->get_item(0, 'NOM_PRODUCTO_PRINCIPAL');
		$cod_tipo_producto 			= $this->dws['dw_producto']->get_item(0, 'COD_TIPO_PRODUCTO');
		$cod_marca 					= $this->dws['dw_producto']->get_item(0, 'COD_MARCA');
		$nom_producto_ingles 		= $this->dws['dw_producto']->get_item(0, 'NOM_PRODUCTO_INGLES');
		$cod_familia_producto 		= $this->dws['dw_producto']->get_item(0, 'COD_FAMILIA_PRODUCTO');
		$largo 						= $this->dws['dw_producto']->get_item(0, 'LARGO');
		$ancho 						= $this->dws['dw_producto']->get_item(0, 'ANCHO');
		$alto 						= $this->dws['dw_producto']->get_item(0, 'ALTO');
		$peso 						= $this->dws['dw_producto']->get_item(0, 'PESO');
		$largo_embalado 			= $this->dws['dw_producto']->get_item(0, 'LARGO_EMBALADO');
		$ancho_embalado 			= $this->dws['dw_producto']->get_item(0, 'ANCHO_EMBALADO');
		$alto_embalado 				= $this->dws['dw_producto']->get_item(0, 'ALTO_EMBALADO');
		$peso_embalado 				= $this->dws['dw_producto']->get_item(0, 'PESO_EMBALADO');
		$factor_venta_interno 		= $this->dws['dw_producto']->get_item(0, 'FACTOR_VENTA_INTERNO');
		$precio_venta_interno 		= $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_INTERNO');
		$factor_venta_publico 		= $this->dws['dw_producto']->get_item(0, 'FACTOR_VENTA_PUBLICO');
		$precio_venta_publico 		= $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_PUBLICO');	
		$usa_electricidad 			= $this->dws['dw_producto']->get_item(0, 'USA_ELECTRICIDAD');
		$nro_fases 					= $this->dws['dw_producto']->get_item(0, 'TRIFASICO');
		$consumo_electricidad 		= $this->dws['dw_producto']->get_item(0, 'CONSUMO_ELECTRICIDAD');
		$rango_temperatura 			= $this->dws['dw_producto']->get_item(0, 'RANGO_TEMPERATURA');
		$voltaje 					= $this->dws['dw_producto']->get_item(0, 'VOLTAJE');
		$nro_certificado_electrico 	= $this->dws['dw_producto']->get_item(0, 'NRO_CERTIFICADO_ELECTRICO');
		$frecuencia 				= $this->dws['dw_producto']->get_item(0, 'FRECUENCIA');
		$usa_gas 					= $this->dws['dw_producto']->get_item(0, 'USA_GAS');
		$potencia 					= $this->dws['dw_producto']->get_item(0, 'POTENCIA');
		$consumo_gas 				= $this->dws['dw_producto']->get_item(0, 'CONSUMO_GAS');
		$nro_certificado_gas 		= $this->dws['dw_producto']->get_item(0, 'NRO_CERTIFICADO_GAS');
		$usa_vapor 					= $this->dws['dw_producto']->get_item(0, 'USA_VAPOR');
		$consumo_vapor 				= $this->dws['dw_producto']->get_item(0, 'CONSUMO_VAPOR');
		$presion_vapor 				= $this->dws['dw_producto']->get_item(0, 'PRESION_VAPOR');
		$usa_agua_fria 				= $this->dws['dw_producto']->get_item(0, 'USA_AGUA_FRIA');
		$usa_agua_caliente 			= $this->dws['dw_producto']->get_item(0, 'USA_AGUA_CALIENTE');
		$caudal 					= $this->dws['dw_producto']->get_item(0, 'CAUDAL');
		$presion_agua 				= $this->dws['dw_producto']->get_item(0, 'PRESION_AGUA');
		$diametro_caneria 			= $this->dws['dw_producto']->get_item(0, 'DIAMETRO_CANERIA');
		$usa_ventilacion 			= $this->dws['dw_producto']->get_item(0, 'USA_VENTILACION');
		$volumen				= $this->dws['dw_producto']->get_item(0, 'VOLUMEN_ESP');
		$caida_presion 				= $this->dws['dw_producto']->get_item(0, 'CAIDA_PRESION');
		$diametro_ducto 			= $this->dws['dw_producto']->get_item(0, 'DIAMETRO_DUCTO');
		$nro_filtros 				= $this->dws['dw_producto']->get_item(0, 'NRO_FILTROS');
		$usa_desague 				= $this->dws['dw_producto']->get_item(0, 'USA_DESAGUE');
		$diametro_desague			= $this->dws['dw_producto']->get_item(0, 'DIAMETRO_DESAGUE');
		$maneja_inventario 			= 'N';
		$stock_critico 				= 0;
		$tiempo_reposicion			= 0;
		$foto_grande 				= $this->dws['dw_producto']->get_item(0, 'FOTO_GRANDE');
		$foto_chica 				= $this->dws['dw_producto']->get_item(0, 'FOTO_CHICA');
		$es_compuesto 				= $this->dws['dw_producto']->get_item(0, 'ES_COMPUESTO');
		$precio_libre 				= $this->dws['dw_producto']->get_item(0, 'PRECIO_LIBRE');
		$es_despachable 			= $this->dws['dw_producto']->get_item(0, 'ES_DESPACHABLE');	
		$potencia_kw				= $this->dws['dw_producto']->get_item(0, 'POTENCIA_KW');
		$cod_clasif_inventario		= $this->dws['dw_producto']->get_item(0, 'COD_CLASIF_INVENTARIO');

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
		$cod_clasif_inventario		= ($cod_clasif_inventario == '') ? "null" : $cod_clasif_inventario;
		
		
		
		$sp = 'spu_producto';
		
		if ($this->is_new_record()){
			$operacion = 'INSERT';
		}
		else{
			$operacion = 'UPDATE';
		}

		/*marca en campo SISTEMA_VALIDO para que sistema es v�lido el equipo
		 * solo en el insert del equipo se asignar� valor, por lo tanto no tiene update
		 */
		$sistema = $this->get_parametro(self::K_PARAM_SISTEMA);
		if ($sistema == 'COMERCIAL')
			$sistema_valido = 'SSSS';
		else if ($sistema == 'BODEGA')
			$sistema_valido = 'NSNN';
		else if ($sistema == 'CATERING')
			$sistema_valido = 'NNSN';
		else if ($sistema == 'TODOINOX')	
			$sistema_valido = 'NNNS';
		else if ($sistema == 'DEMO')	
			$sistema_valido = 'SNNN';	
		
			
		$param = "'$operacion','$cod_producto','$nom_producto',$cod_tipo_producto,$cod_marca,$nom_producto_ingles,
		$cod_familia_producto,$largo,$ancho,$alto,$peso,$largo_embalado,$ancho_embalado,
		$alto_embalado,$peso_embalado,$factor_venta_interno,$precio_venta_interno,
		$factor_venta_publico,$precio_venta_publico,'$usa_electricidad',$nro_fases,
		$consumo_electricidad,$rango_temperatura,$voltaje,$frecuencia,$nro_certificado_electrico,
		'$usa_gas',$potencia,$consumo_gas,$nro_certificado_gas,'$usa_vapor',$consumo_vapor,
		$presion_vapor,'$usa_agua_fria','$usa_agua_caliente',$caudal,$presion_agua,$diametro_caneria,
		'$usa_ventilacion',$volumen,$caida_presion,$diametro_ducto,$nro_filtros,'$usa_desague',
		$diametro_desague,'$maneja_inventario',$stock_critico,$tiempo_reposicion,'$precio_libre', '$es_despachable', '$sistema_valido',$potencia_kw,$cod_clasif_inventario";
		
		if ($db->EXECUTE_SP($sp, $param)) {
			for ($i = 0; $i < $this->dws['dw_producto_proveedor']->row_count(); $i++){
				$this->dws['dw_producto_proveedor']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			for ($i = 0; $i < $this->dws['dw_producto_compuesto']->row_count(); $i++){
				$this->dws['dw_producto_compuesto']->set_item($i, 'COD_PRODUCTO_PRINCIPAL', $cod_producto);
			}
			for ($i = 0; $i < $this->dws['dw_atributo_producto']->row_count(); $i++){
				$this->dws['dw_atributo_producto']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			
			// TAB PROVEEDORES //
			/*
			if ($es_compuesto == 'S'){
				// SI ES COMPUESTO SE ELIMINAN LOS PROVEEDORES Y SE ESCONDE EL TAB PROVEEDORES
				for ($i = 0; $i < $this->dws['dw_producto_proveedor']->row_count(); $i++) {			
					$cod_producto_proveedor = $this->dws['dw_producto_proveedor']->get_item($i, 'COD_PRODUCTO_PROVEEDOR');					 
					$sp = 'spu_producto_proveedor';
					$db->EXECUTE_SP($sp, "'DELETE', $cod_producto_proveedor");
				}				
				//$this->dws['dw_producto']->set_item(0, "TAB_".self::K_IT_MENU_TAB_PROVEE_VISIBLE,'none');
				
				if (!$this->dws['dw_producto_compuesto']->update($db))					
					return false;					
			}
			else{				
				// si no es compuesto se eliminan los productos compuestos de este producto			
				for ($i = 0; $i < $this->dws['dw_producto_compuesto']->row_count(); $i++) {			
					$cod_producto_compuesto = $this->dws['dw_producto_compuesto']->get_item($i, 'COD_PRODUCTO_COMPUESTO');
					$sp = 'spu_producto_compuesto';
					$db->EXECUTE_SP($sp, "'DELETE', $cod_producto_compuesto");
				}
				if (!$this->dws['dw_producto_proveedor']->update($db)){					
					return false;
				}	
			}
			*/
			if (!$this->dws['dw_producto_proveedor']->update($db))					
					return false;
					
			if (!$this->dws['dw_producto_compuesto']->update($db))				
				return false;
						
			if (!$this->dws['dw_atributo_producto']->update($db))
				return false;
				
			$sql ="SELECT COD_PRODUCTO_LOCAL  
					FROM PRODUCTO_LOCAL 
					WHERE COD_PRODUCTO = '$cod_producto'";	
			$result = $db->build_results($sql);		
			$cod_producto_local = $result[0]['COD_PRODUCTO_LOCAL'];
			$cod_producto_local			= ($cod_producto_local == '') ? "null" : $cod_producto_local;
			$param = "'$operacion',$cod_producto_local,'$cod_producto','$es_compuesto'";
			
			
				if(count($result) == 0 && $operacion == 'INSERT'){	
						
					if (!$db->EXECUTE_SP('spu_producto_local', $param)){
							return false;
					}
					if (!$db->EXECUTE_SP('BIGGI.dbo.spu_producto_local', $param)){
							return false;
					}
					
				}elseif($operacion == 'UPDATE'){
				if (!$db->EXECUTE_SP('spu_producto_local', $param)){
							return false;
					}
				}	
				
			if (!$this->subir_imagen($db, $cod_producto))
				return false;
			
			return true;
		}
		return false;
	}
}

?>
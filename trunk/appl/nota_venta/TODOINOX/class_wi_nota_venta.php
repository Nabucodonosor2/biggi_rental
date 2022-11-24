<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
////////////////////////////////////////
/////////// TODOINOX ///////////////
////////////////////////////////////////

class wi_nota_venta extends wi_nota_venta_base {
	const K_ESTADO_EMITIDA 			= 1;	
	const K_ESTADO_CERRADA			= 2;
	const K_ESTADO_ANULADA			= 3;
	const K_ESTADO_CONFIRMADA		= 4;
	const K_PARAM_NOM_EMPRESA 		= 6;
	const K_PARAM_DIR_EMPRESA 		= 10;
	const K_PARAM_TEL_EMPRESA 		= 11;
	const K_PARAM_FAX_EMPRESA 		= 12;
	const K_PARAM_MAIL_EMPRESA 		= 13;
	const K_PARAM_CIUDAD_EMPRESA	= 14;
	const K_PARAM_PAIS_EMPRESA 		= 15; 
	const K_PARAM_GTE_VTA 			= 16;
	const K_PARAM_RUT_EMPRESA 		= 20;
	const K_PARAM_SITIO_WEB_EMPRESA	= 25;
	const K_PARAM_RANGO_DOC_NOTA_VENTA = 27;
	const K_AUTORIZA_CIERRE 		 = '991005';
	const K_CAMBIA_DSCTO_CORPORATIVO = '991010';
	const K_MODIFICA_NOTA_VENTA		 = '991020';
	const K_AUTORIZA_ANULACION		 = '991025';
	function wi_nota_venta($cod_item_menu) {
		parent::wi_nota_venta_base($cod_item_menu); 
		
		// valida si el usuario puede autorizar cierre y modificar la fecha de cierre
		if ($this->tiene_privilegio_opcion(self::K_AUTORIZA_CIERRE))
			$autoriza_cierre = 'S';
		else
			$autoriza_cierre = 'N';
		// valida si el usuario puede modificar los desctos corporativos
		if ($this->tiene_privilegio_opcion(self::K_CAMBIA_DSCTO_CORPORATIVO))
			$cambia_dscto_corp = 'S';
		else
			$cambia_dscto_corp = 'N';
		// valida si el usuario puede modificar Nota Venta cuando esta confirmada
		if ($this->tiene_privilegio_opcion(self::K_MODIFICA_NOTA_VENTA))
			$modifica_nv = 'S';
		else
			$modifica_nv = 'N';
		//valida si el usuario puede modificar Nota Venta cuando esta confirmada
		if ($this->tiene_privilegio_opcion(self::K_AUTORIZA_ANULACION))
			$autoriza_anulacion = 'S';
		else
			$autoriza_anulacion = 'N';

		// Obtiene el perfil del usuario, si es administrador PREORDEN son siempre visible 
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select COD_PERFIL
				from USUARIO
				where COD_USUARIO = $this->cod_usuario";
		$result = $db->build_results($sql);
		$cod_perfil = $result[0]['COD_PERFIL'];
			
		$sql = "select COD_NOTA_VENTA, 
					convert(varchar, FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA,
					NV.COD_USUARIO,
					U.NOM_USUARIO,
					NRO_ORDEN_COMPRA,
					--CIERRE_SINPART,
					CENTRO_COSTO_CLIENTE,
					COD_COTIZACION,
					NV.COD_ESTADO_NOTA_VENTA,
					NV.COD_ESTADO_NOTA_VENTA COD_ESTADO_NOTA_VENTA_H,
					ENV.NOM_ESTADO_NOTA_VENTA, 
					COD_MONEDA,
					VALOR_TIPO_CAMBIO,  
					COD_USUARIO_VENDEDOR1, 
					PORC_VENDEDOR1, 
					COD_USUARIO_VENDEDOR2, 
					PORC_VENDEDOR2,
					'none' DISPLAY_DESCARGA,
					'' ELIMINA_DOC,
					NV.COD_CUENTA_CORRIENTE, 
					CC.NOM_CUENTA_CORRIENTE, 
					CC.NRO_CUENTA_CORRIENTE, 
					REFERENCIA,
					NV.COD_EMPRESA,
					E.ALIAS,
					E.RUT,
					'none' LL_LLAMADO,
					Convert(varchar, NV.FECHA_ORDEN_COMPRA_CLIENTE, 103) FECHA_ORDEN_COMPRA_CLIENTE,
					E.DIG_VERIF,
					E.NOM_EMPRESA,
					E.GIRO, 
					COD_SUCURSAL_DESPACHO, 
					dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_DESPACHO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_DESPACHO,
					COD_SUCURSAL_FACTURA, 
					dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA,
					COD_PERSONA,
					dbo.f_emp_get_mail_cargo_persona(COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA,
					Convert(varchar, FECHA_ENTREGA, 103) FECHA_ENTREGA, 
					OBS_DESPACHO,
					OBS,
					Convert(varchar, FECHA_PLAZO_CIERRE, 103) FECHA_PLAZO_CIERRE, 
					-- historial de modificacion fecha_plazo_cierre
					(select count(*) from LOG_CAMBIO LG, DETALLE_CAMBIO DC where LG.NOM_TABLA = 'NOTA_VENTA' and
					LG.KEY_TABLA = CAST(NV.COD_NOTA_VENTA AS VARCHAR) and LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and 
					DC.NOM_CAMPO = 'FECHA_PLAZO_CIERRE') CANT_CAMBIO_FECHA_PLAZO_CIERRE,
					SUBTOTAL SUM_TOTAL, 
					PORC_DSCTO1, 
					MONTO_DSCTO1,
					PORC_DSCTO2, 
					MONTO_DSCTO2, 
					null D_COD_NOTA_ENCRIPT,
					TOTAL_NETO,
					TOTAL_NETO STATIC_TOTAL_NETO,
					TOTAL_NETO STATIC_TOTAL_NETO2,
					PORC_IVA, 
					MONTO_IVA, 
					TOTAL_CON_IVA,   
					TOTAL_CON_IVA STATIC_TOTAL_CON_IVA,
					TOTAL_CON_IVA STATIC_TOTAL_CON_IVA2,
					dbo.f_nv_total_pago(NV.COD_NOTA_VENTA) TOTAL_PAGO,
					TOTAL_CON_IVA - dbo.f_nv_total_pago(NV.COD_NOTA_VENTA) TOTAL_POR_PAGAR,
					COD_FORMA_PAGO, 
					NOM_FORMA_PAGO_OTRO,
					CANTIDAD_DOC_FORMA_PAGO_OTRO,
					INGRESO_USUARIO_DSCTO1,  
					INGRESO_USUARIO_DSCTO2,
					V1.PORC_DESCUENTO_PERMITIDO PORC_DSCTO_MAX,
					datediff(d, FECHA_NOTA_VENTA, getdate()) EMITIDA_HACE,
					--resultados
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'VENTA_NETA') VENTA_NETA,
					PORC_DSCTO_CORPORATIVO,
					PORC_DSCTO_CORPORATIVO PORC_DSCTO_CORPORATIVO_STATIC, 
					PORC_DSCTO_CORPORATIVO PORC_DSCTO_CORPORATIVO_H,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') MONTO_DSCTO_CORPORATIVO,
					dbo.f_get_parametro_porc('AA', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_AA,
					dbo.f_get_parametro_porc('GF', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_GF,
					dbo.f_get_parametro_porc('GV', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_GV,
					dbo.f_get_parametro_porc('ADM', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_ADM,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DIRECTORIO') MONTO_DIRECTORIO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_GASTO_FIJO') MONTO_GASTO_FIJO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUM_OC_TOTAL') SUM_OC_TOTAL,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') RESULTADO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') STATIC_RESULTADO,			
					case NV.TOTAL_NETO when 0 then
						0
					else
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'PORC_RESULTADO')
					end PORC_RESULTADO,
					case NV.TOTAL_NETO when 0 then
						0
					else
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'PORC_RESULTADO')
					end STATIC_PORC_RESULTADO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V1')COMISION_V1,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V2')COMISION_V2,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_GV')COMISION_GV,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_ADM')COMISION_ADM,					
					(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1) VENDEDOR1,
					(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR2) VENDEDOR2,
					dbo.f_get_parametro(".self::K_PARAM_GTE_VTA.") GTE_VTA,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'REMANENTE') REMANENTE,
					-- no modificable en tab de resultados la comision del vendedor
					PORC_VENDEDOR1 PORC_VENDEDOR1_R,  
					PORC_VENDEDOR2 PORC_VENDEDOR2_R,
					dbo.f_get_parametro(".self::K_PARAM_RANGO_DOC_NOTA_VENTA.") RANGO_DOC_NOTA_VENTA,
					(select isnull(sum(MONTO_DOC),0) from DOC_NOTA_VENTA DNV where DNV.COD_NOTA_VENTA = NV.COD_NOTA_VENTA) TOTAL_MONTO_DOC,
					'".$cambia_dscto_corp."' CAMBIA_DSCTO_CORPORATIVO,
					-- despachado
					case NV.SUBTOTAL when 0 then
						0
					else
						Round((select isnull(sum((CANTIDAD - dbo.f_nv_cant_por_despachar(COD_ITEM_NOTA_VENTA, default)) * PRECIO), 0) 	from ITEM_NOTA_VENTA IT where IT.COD_NOTA_VENTA = NV.COD_NOTA_VENTA) * 100 / NV.SUBTOTAL, 1)
					end PORC_GD,
					-- facturado
					dbo.f_nv_porc_facturado(NV.COD_NOTA_VENTA) PORC_FACTURA,
					-- pagado
					Round((dbo.f_nv_total_pago(NV.COD_NOTA_VENTA) / TOTAL_CON_IVA) * 100, 1) PORC_PAGOS,
					-- historial de modificacion descto. corporativo
					(select count(*)
					from LOG_CAMBIO LG, DETALLE_CAMBIO DC
					where LG.NOM_TABLA = 'NOTA_VENTA' and LG.KEY_TABLA = CAST(NV.COD_NOTA_VENTA AS VARCHAR) and
						LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
						DC.NOM_CAMPO = 'PORC_DSCTO_CORPORATIVO') CANT_CAMBIO_PORC_DESCTO_CORP,
					-- datos cierre NV
					convert(varchar(20), FECHA_CIERRE, 103) +'  '+ convert(varchar(20), FECHA_CIERRE, 8) FECHA_CIERRE,
					COD_USUARIO_CIERRE,			
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_CERRADA." then ''
						else 'none'
					end TABLE_CIERRE_DISPLAY,
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_CERRADA." then 'none'
						else ''
					end BOTON_CIERRE_DISPLAY,
					'' TABLE_PENDIENTE_DISPLAY,
					'N' CIERRE_H,
					'N' CIERRE_SIN_P_H,
					''  MOTIVO_CIERRE_SIN_PART_H,
					'none' BOTON_CIERRE_SIN_P,
					'".$autoriza_cierre."'	AUTORIZA_CIERRE,
					'".$autoriza_cierre."'	VALIDA_MOTIVO_CIERRE_H,
					'".$modifica_nv."' 		MODIFICA_NV,
					'".$autoriza_anulacion."' AUTORIZA_ANULACION,

					case dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'NOTA_VENTA',NV.COD_USUARIO_VENDEDOR1, NV.COD_USUARIO_VENDEDOR2)
						when 1 then 'S'
						else 'N'
					end ES_VENDEDOR,
					
					-- datos anulación
					convert(varchar(20), NV.FECHA_ANULA, 103) +'  '+ convert(varchar(20), NV.FECHA_ANULA, 8) FECHA_ANULA,
					MOTIVO_ANULA,
					COD_USUARIO_ANULA,
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_ANULADA." then ''
						else 'none'
					end TR_DISPLAY_ANULADA,
					COD_USUARIO_CONFIRMA COD_USUARIO_CONFIRMA_H,
					FECHA_CONFIRMA,
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_CERRADA." then 'CERRADA'
						when ".self::K_ESTADO_ANULADA." then 'ANULADA'
						when ".self::K_ESTADO_CONFIRMADA." then 'CONFIRMADA' 
						else ''
					end TITULO_ESTADO_NOTA_VENTA
					,case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_EMITIDA." then '' 
						else case $cod_perfil
								when 1 then ''
								else 'none'
							 end
					end DISPLAY_PREORDEN
					,case 
						when (INGRESO_USUARIO_DSCTO1='M' and MONTO_DSCTO1>0) then 'Descuento ingresado como monto'
						else ''
					end ETIQUETA_DESCT1
					,case 
						when (INGRESO_USUARIO_DSCTO2='M' and MONTO_DSCTO2>0)  then 'Descuento ingresado como monto'
						else ''
					end ETIQUETA_DESCT2
				from NOTA_VENTA NV, USUARIO U, EMPRESA E, ESTADO_NOTA_VENTA ENV, CUENTA_CORRIENTE CC, USUARIO V1
				where COD_NOTA_VENTA = {KEY1} and
					U.COD_USUARIO = NV.COD_USUARIO and
					E.COD_EMPRESA = NV.COD_EMPRESA and
					ENV.COD_ESTADO_NOTA_VENTA = NV.COD_ESTADO_NOTA_VENTA and
					CC.COD_CUENTA_CORRIENTE = NV.COD_CUENTA_CORRIENTE
					and V1.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1";
		$this->dws['dw_nota_venta']->set_sql($sql);
		//$this->sql_original = $sql;
	}
}
?>
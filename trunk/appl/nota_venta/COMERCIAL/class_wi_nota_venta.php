<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
////////////////////////////////////////
/////////// COMERCIAL ///////////////
////////////////////////////////////////
class dw_llamado extends datawindow {
	function dw_llamado() {
		
		$sql = "SELECT LL.COD_LLAMADO LL_COD_LLAMADO
						,CONVERT (VARCHAR(10), LL.FECHA_LLAMADO, 103) LL_FECHA_LLAMADO
						,LLA.NOM_LLAMADO_ACCION LL_NOM_LLAMADO_ACCION
						,C.NOM_CONTACTO LL_NOM_CONTACTO
						,dbo.f_llamado_telefono(LL.COD_CONTACTO, 'EMPRESA') LL_TELEFONO_CONTACTO
						,CP.NOM_PERSONA LL_NOM_PERSONA
						,dbo.f_llamado_telefono(LL.COD_CONTACTO_PERSONA, 'PERSONA') LL_TELEFONO_PERSONA
						,LL.MENSAJE LL_MENSAJE
					FROM LLAMADO LL
						,LLAMADO_ACCION LLA
						,CONTACTO C
						,CONTACTO_PERSONA CP
				   WHERE LL.TIPO_DOC_REALIZADO = 'NOTA VENTA'
				   	 AND LL.COD_DOC_REALIZADO = {KEY1}
					 AND LLA.COD_LLAMADO_ACCION = LL.COD_LLAMADO_ACCION
					 AND C.COD_CONTACTO = LL.COD_CONTACTO
					 AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA";

		parent::datawindow($sql);

		$this->add_control($control = new edit_num('LL_COD_LLAMADO'));
		$control->set_onChange('find_1_llamado(this);');
		
		$this->add_control(new static_text('LL_FECHA_LLAMADO'));
		$this->add_control(new static_text('LL_NOM_LLAMADO_ACCION'));
		$this->add_control(new static_text('LL_NOM_CONTACTO'));
		$this->add_control(new static_text('LL_TELEFONO_CONTACTO'));
		$this->add_control(new static_text('LL_NOM_PERSONA'));
		$this->add_control(new static_text('LL_TELEFONO_PERSONA'));
		$this->add_control(new edit_text_multiline('LL_MENSAJE', 80, 3));
		$this->set_entrable('LL_MENSAJE', false);
	}
}

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
		
		$sql="select COD_NOTA_VENTA, 
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
					CREADA_EN_SV, 
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
		
		
		$this->dws['dw_llamado'] = new dw_llamado();
		$this->dws['dw_nota_venta']->add_control(new edit_check_box('CREADA_EN_SV','S','N'));
	}
	function new_record() {
		if (session::is_set('NV_CREADA_DESDE')) {
			//echo 'cod_cotizacion'.session::get('NV_CREADA_DESDE');
			//return;
			$cod_cotizacion = session::get('NV_CREADA_DESDE');			
			$this->creada_desde($cod_cotizacion);
			$this->dws['dw_nota_venta']->set_item(0, 'DISPLAY_DESCARGA','none');
			$this->dws['dw_llamado']->insert_row();
			session::un_set('NV_CREADA_DESDE');
			return;
		}
		$this->dws['dw_nota_venta']->insert_row();
		$this->dws['dw_nota_venta']->set_item(0, 'FECHA_NOTA_VENTA', $this->current_date());
		$this->dws['dw_nota_venta']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_nota_venta']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_nota_venta']->set_item(0, 'COD_ESTADO_NOTA_VENTA', self::K_ESTADO_EMITIDA);
		$this->dws['dw_nota_venta']->set_item(0, 'COD_ESTADO_NOTA_VENTA_H', self::K_ESTADO_EMITIDA);
		$this->dws['dw_nota_venta']->set_item(0, 'NOM_ESTADO_NOTA_VENTA', 'EMITIDA');
		$this->dws['dw_nota_venta']->set_item(0, 'COD_MONEDA', $this->get_orden_min('MONEDA'));
		$this->dws['dw_nota_venta']->set_item(0, 'VALOR_TIPO_CAMBIO', 1);
		$this->dws['dw_nota_venta']->controls['NOM_FORMA_PAGO_OTRO']->set_type('hidden');
		$this->dws['dw_nota_venta']->controls['CANTIDAD_DOC_FORMA_PAGO_OTRO']->set_type('hidden');
		$this->dws['dw_nota_venta']->set_item(0, 'PORC_DSCTO_CORPORATIVO', '0');
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_DSCTO_CORPORATIVO', 1));

		$this->valores_default_vend();
		
		$this->dws['dw_nota_venta']->set_item(0, 'RANGO_DOC_NOTA_VENTA',$this->get_parametro(self::K_PARAM_RANGO_DOC_NOTA_VENTA));
				
		// no se ven tablas asociadas al cierre
		$this->dws['dw_nota_venta']->set_item(0, 'TABLE_CIERRE_DISPLAY', 'none');
		$this->dws['dw_nota_venta']->set_item(0, 'BOTON_CIERRE_DISPLAY', 'none');
		$this->dws['dw_nota_venta']->set_item(0, 'TABLE_PENDIENTE_DISPLAY', 'none');
		
		//datos de anulación
		$this->dws['dw_nota_venta']->set_item(0, 'TR_DISPLAY_ANULADA', 'none');
		$this->dws['dw_nota_venta']->set_item(0, 'DISPLAY_DESCARGA','none');
		$this->dws['dw_llamado']->insert_row();
	}
function load_record() {
		$COD_NOTA_VENTA = $this->get_item_wo($this->current_record, 'COD_NOTA_VENTA');
		$this->dws['dw_nota_venta']->retrieve($COD_NOTA_VENTA);
		
		$COD_EMPRESA = $this->dws['dw_nota_venta']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_nota_venta']->controls['COD_SUCURSAL_FACTURA']->retrieve($COD_EMPRESA);
		$this->dws['dw_nota_venta']->controls['COD_SUCURSAL_DESPACHO']->retrieve($COD_EMPRESA);
		$this->dws['dw_nota_venta']->controls['COD_PERSONA']->retrieve($COD_EMPRESA);		
		$this->dws['dw_lista_guia_despacho']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_lista_guia_recepcion']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_lista_factura']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_lista_nota_credito']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_lista_pago']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_participacion']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_item_nota_venta']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_orden_compra']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_nv_backcharge']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_pre_orden_compra']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_item_nota_venta_resultado']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_doc_nota_venta']->retrieve($COD_NOTA_VENTA);
		$MODIFICA_NV = $this->dws['dw_nota_venta']->get_item(0, 'MODIFICA_NV');
		$AUTORIZA_ANULACION = $this->dws['dw_nota_venta']->get_item(0, 'AUTORIZA_ANULACION');
		$this->dws['dw_nota_venta']->set_item(0, 'D_COD_NOTA_ENCRIPT',$COD_NOTA_VENTA);
		
		
		//DISPLAY_DESCARGA
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql="	SELECT COUNT(*) TIENE_DESCARGA
				  FROM NOTA_VENTA_DOCS NVD
				 WHERE NVD.ES_OC = 'S'
		           AND NVD.COD_NOTA_VENTA = $COD_NOTA_VENTA";
		           
		 $result = $db->build_results($sql);					
		 $tiene_descarga = $result[0]['TIENE_DESCARGA'];
		 
		 if($tiene_descarga == 0){
		 $this->dws['dw_nota_venta']->set_item(0, 'DISPLAY_DESCARGA','none');	
		 }else{
		 $this->dws['dw_nota_venta']->set_item(0, 'DISPLAY_DESCARGA','');	
		 }
		$cod_forma_pago		= $this->dws['dw_nota_venta']->get_item(0, 'COD_FORMA_PAGO');
		if ($cod_forma_pago==1){
			$this->dws['dw_nota_venta']->controls['NOM_FORMA_PAGO_OTRO']->set_type('text');
			$this->dws['dw_nota_venta']->controls['CANTIDAD_DOC_FORMA_PAGO_OTRO']->set_type('text');
		}	
		else{
			$this->dws['dw_nota_venta']->controls['NOM_FORMA_PAGO_OTRO']->set_type('hidden');
			$this->dws['dw_nota_venta']->controls['CANTIDAD_DOC_FORMA_PAGO_OTRO']->set_type('hidden');
		}	
		
		$COD_ESTADO_NOTA_VENTA = $this->dws['dw_nota_venta']->get_item(0, 'COD_ESTADO_NOTA_VENTA_H');
		
		$this->b_print_visible 	 = true;
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible  = true;
		$this->b_delete_visible  = true;		
		if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_EMITIDA){	
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			if (!$db->EXECUTE_SP('spu_tipo_pendiente_nota_venta', "'LOAD', $COD_NOTA_VENTA"))
				return false;
			if ($AUTORIZA_ANULACION == 'S'){
				//si su perfil tiene permiso de anular nuestra el codigo de anulacion
				$sql = "select COD_ESTADO_NOTA_VENTA,
							NOM_ESTADO_NOTA_VENTA
						from ESTADO_NOTA_VENTA
						where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_EMITIDA." or
							COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_CONFIRMADA." or
							COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_ANULADA."
						order by ORDEN";
			}else{
				//si su perfil no tiene permiso de anular elimina el codigo de anulacion
				$sql = "select COD_ESTADO_NOTA_VENTA,
							NOM_ESTADO_NOTA_VENTA
						from ESTADO_NOTA_VENTA
						where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_EMITIDA." or
							COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_CONFIRMADA."
						order by ORDEN";
			}

			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control($control = new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$control->set_onChange("cambia_estado(this);");
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			$this->dws['dw_nota_venta']->add_control(new edit_text_upper('MOTIVO_ANULA',100, 100));
			
			$this->dws['dw_item_nota_venta']->unset_protect('COD_PRODUCTO');
			$this->dws['dw_item_nota_venta']->unset_protect('NOM_PRODUCTO');				
		   
		}
		else if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_CONFIRMADA){
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			if (!$db->EXECUTE_SP('spu_tipo_pendiente_nota_venta', "'LOAD', $COD_NOTA_VENTA"))
				return false;

			// si estado = confirmada se puede CERRAR, ANULAR
			$sql = "select COD_ESTADO_NOTA_VENTA,
						NOM_ESTADO_NOTA_VENTA
					from ESTADO_NOTA_VENTA
					where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_CONFIRMADA." or
						COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_ANULADA."
					order by ORDEN";


			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control($control = new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$control->set_onChange("cambia_estado(this);");
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			$this->dws['dw_nota_venta']->add_control(new edit_text_upper('MOTIVO_ANULA',100, 100));
			
			if ($AUTORIZA_ANULACION == 'S'){
				//permite cambiar a anulado siempre que su perfil lo autorize
				$this->dws['dw_nota_venta']->set_entrable('COD_ESTADO_NOTA_VENTA'	, true);
			}else{
				$this->dws['dw_nota_venta']->set_entrable('COD_ESTADO_NOTA_VENTA'	, false);
			}
			
			if ($MODIFICA_NV == 'N'){
				// deja no entrable campos tab1 Nota Venta
				$this->dws['dw_nota_venta']->set_entrable('NRO_ORDEN_COMPRA'        , false);
				$this->dws['dw_nota_venta']->set_entrable('FECHA_ORDEN_COMPRA_CLIENTE', false);
				$this->dws['dw_nota_venta']->set_entrable('CENTRO_COSTO_CLIENTE'    , false);
				$this->dws['dw_nota_venta']->set_entrable('REFERENCIA'       		, false);
				$this->dws['dw_nota_venta']->set_entrable('COD_EMPRESA'        	 	, false);
				$this->dws['dw_nota_venta']->set_entrable('ALIAS'        			, false);
				$this->dws['dw_nota_venta']->set_entrable('RUT'        			 	, false);
				$this->dws['dw_nota_venta']->set_entrable('NOM_EMPRESA'        	 	, false);
				$this->dws['dw_nota_venta']->set_entrable('COD_SUCURSAL_DESPACHO'   , false);
				$this->dws['dw_nota_venta']->set_entrable('COD_SUCURSAL_FACTURA'    , false);
				$this->dws['dw_nota_venta']->set_entrable('COD_PERSONA'        	 	, false);
				$this->dws['dw_item_nota_venta']->set_entrable_dw(false);
			}
			else{
				// deja entrable campos tab1 Nota Venta siempre que su perfil este autorizado
				$this->dws['dw_nota_venta']->set_entrable('NRO_ORDEN_COMPRA'        , true);
				$this->dws['dw_nota_venta']->set_entrable('FECHA_ORDEN_COMPRA_CLIENTE', true);
				$this->dws['dw_nota_venta']->set_entrable('CENTRO_COSTO_CLIENTE'    , true);
				$this->dws['dw_nota_venta']->set_entrable('REFERENCIA'       		, true);
				$this->dws['dw_nota_venta']->set_entrable('COD_EMPRESA'        	 	, true);
				$this->dws['dw_nota_venta']->set_entrable('ALIAS'        			, true);
				$this->dws['dw_nota_venta']->set_entrable('RUT'        			 	, true);
				$this->dws['dw_nota_venta']->set_entrable('NOM_EMPRESA'        	 	, true);
				$this->dws['dw_nota_venta']->set_entrable('COD_SUCURSAL_DESPACHO'   , true);
				$this->dws['dw_nota_venta']->set_entrable('COD_SUCURSAL_FACTURA'    , true);
				$this->dws['dw_nota_venta']->set_entrable('COD_PERSONA'        	 	, true);
			}
			
			// deja no entrable campos tab Compras
			$this->dws['dw_pre_orden_compra']->set_entrable_dw(false);
		}
		else if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_ANULADA) {
			$sql = "select COD_ESTADO_NOTA_VENTA,
						NOM_ESTADO_NOTA_VENTA
					from ESTADO_NOTA_VENTA
					where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_ANULADA;

			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control(new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;		
		}
		else if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_CERRADA) {
			$sql = "select COD_ESTADO_NOTA_VENTA,
						NOM_ESTADO_NOTA_VENTA
					from ESTADO_NOTA_VENTA
					where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_CERRADA;

			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control(new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;		
		}
		
		if ($this->tiene_privilegio_opcion(self::K_AUTORIZA_CIERRE))
			$this->dws['dw_nota_venta']->add_control(new edit_date('FECHA_PLAZO_CIERRE'));
		
		else
			$this->dws['dw_nota_venta']->add_control(new static_text('FECHA_PLAZO_CIERRE'));
			
		
			
		$AUTORIZA_CIERRE = $this->dws['dw_nota_venta']->get_item(0, 'AUTORIZA_CIERRE');
		if ($AUTORIZA_CIERRE == 'S')
			$this->dws['dw_tipo_pendiente_nota_venta']->set_entrable_dw(true);
		else
			$this->dws['dw_tipo_pendiente_nota_venta']->set_entrable_dw(false);
			
		$this->dws['dw_tipo_pendiente_nota_venta']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_docs']->retrieve($COD_NOTA_VENTA);
		
		$cod_usuario = session::get("COD_USUARIO"); 
		 $sql_usuario = "select COD_PERFIL
					from USUARIO
					where COD_USUARIO =$cod_usuario";			 
		 $result_usuario = $db->build_results($sql_usuario);					
		 $cod_perfil = $result_usuario[0]['COD_PERFIL'];
		 $item_menu = '991040';
		 $sql_autoriza ="SELECT AUTORIZA_MENU
							FROM AUTORIZA_MENU
						WHERE COD_ITEM_MENU = $item_menu
						AND COD_PERFIL = $cod_perfil"; 
		$result_autoriza = $db->build_results($sql_autoriza);					
		 $autoriza = $result_autoriza[0]['AUTORIZA_MENU'];		
			for($i=0; $i<$this->dws['dw_docs']->row_count(); $i++) {
				 if($autoriza == 'E'){
				 $this->dws['dw_docs']->set_item($i, 'ELIMINA_DOC', '');	
				 $this->dws['dw_nota_venta']->set_item(0, 'ELIMINA_DOC', '');
				 }else{
				 $this->dws['dw_docs']->set_item($i, 'ELIMINA_DOC', 'none');	
				 $this->dws['dw_nota_venta']->set_item(0, 'ELIMINA_DOC', 'none');
				 }
			}
		$this->dws['dw_llamado']->retrieve($COD_NOTA_VENTA);
		if ($this->dws['dw_llamado']->row_count()==0)
			$this->dws['dw_llamado']->insert_row();
			
		$this->dws['dw_nota_venta']->set_item(0, 'LL_LLAMADO','');
	$sql = "SELECT LL.COD_LLAMADO LL_COD_LLAMADO
						,CONVERT (VARCHAR(10), LL.FECHA_LLAMADO, 103) LL_FECHA_LLAMADO
						,LLA.NOM_LLAMADO_ACCION LL_NOM_LLAMADO_ACCION
						,C.NOM_CONTACTO LL_NOM_CONTACTO
						,dbo.f_llamado_telefono(LL.COD_CONTACTO, 'EMPRESA') LL_TELEFONO_CONTACTO
						,CP.NOM_PERSONA LL_NOM_PERSONA
						,dbo.f_llamado_telefono(LL.COD_CONTACTO_PERSONA, 'PERSONA') LL_TELEFONO_PERSONA
						,LL.MENSAJE LL_MENSAJE
					FROM LLAMADO LL
						,LLAMADO_ACCION LLA
						,CONTACTO C
						,CONTACTO_PERSONA CP
				   WHERE LL.TIPO_DOC_REALIZADO = 'NOTA VENTA'
				   	 AND LL.COD_DOC_REALIZADO = $COD_NOTA_VENTA
					 AND LLA.COD_LLAMADO_ACCION = LL.COD_LLAMADO_ACCION
					 AND C.COD_CONTACTO = LL.COD_CONTACTO
					 AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA";

					 $result = $db->build_results($sql);
					 $cod_llamado = $result[0]['LL_COD_LLAMADO'];
			 
		 if($cod_llamado <> ''){
		$this->dws['dw_nota_venta']->set_item(0,'LL_LLAMADO','');
        $this->dws['dw_llamado']->set_item(0,'LL_COD_LLAMADO', $result[0]['LL_COD_LLAMADO']);
		$this->dws['dw_llamado']->set_item(0,'LL_FECHA_LLAMADO', $result[0]['LL_FECHA_LLAMADO']);
		$this->dws['dw_llamado']->set_item(0,'LL_NOM_LLAMADO_ACCION', $result[0]['LL_NOM_LLAMADO_ACCION']);
		$this->dws['dw_llamado']->set_item(0,'LL_NOM_CONTACTO', $result[0]['LL_NOM_CONTACTO']);
		$this->dws['dw_llamado']->set_item(0,'LL_TELEFONO_CONTACTO', $result[0]['LL_TELEFONO_CONTACTO']);
		$this->dws['dw_llamado']->set_item(0,'LL_NOM_PERSONA', $result[0]['LL_NOM_PERSONA']);
		$this->dws['dw_llamado']->set_item(0,'LL_TELEFONO_PERSONA', $result[0]['LL_TELEFONO_PERSONA']);
		$this->dws['dw_llamado']->set_item(0,'LL_MENSAJE', $result[0]['LL_MENSAJE']);
		}	
		
		 $item_menu = '991050';
		 $sql_autoriza_cierre ="SELECT AUTORIZA_MENU
							FROM AUTORIZA_MENU
						WHERE COD_ITEM_MENU = $item_menu
						AND COD_PERFIL = $cod_perfil"; 
		$result_autoriza_cierre = $db->build_results($sql_autoriza_cierre);					
		$autoriza_cierre = $result_autoriza_cierre[0]['AUTORIZA_MENU'];
		
		if($autoriza_cierre == 'E'){
		$this->dws['dw_nota_venta']->set_item(0,'BOTON_CIERRE_SIN_P','');
		}				
		
	}
function save_record($db) {
		//$db->debug=1;
		
		$COD_NOTA_VENTA = $this->get_key();	
		$FECHA_NOTA_VENTA = $this->dws['dw_nota_venta']->get_item(0, 'FECHA_NOTA_VENTA');
		$COD_USUARIO = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO');
		$NRO_ORDEN_COMPRA = $this->dws['dw_nota_venta']->get_item(0, 'NRO_ORDEN_COMPRA');
		$FECHA_ORDEN_COMPRA_CLIENTE = $this->dws['dw_nota_venta']->get_item(0, 'FECHA_ORDEN_COMPRA_CLIENTE');
		$CENTRO_COSTO_CLIENTE = $this->dws['dw_nota_venta']->get_item(0, 'CENTRO_COSTO_CLIENTE');
		$COD_COTIZACION = $this->dws['dw_nota_venta']->get_item(0, 'COD_COTIZACION');
		$COD_ESTADO_NOTA_VENTA = $this->dws['dw_nota_venta']->get_item(0, 'COD_ESTADO_NOTA_VENTA_H');
		$COD_MONEDA = $this->dws['dw_nota_venta']->get_item(0, 'COD_MONEDA');
		$VALOR_TIPO_CAMBIO = $this->dws['dw_nota_venta']->get_item(0, 'VALOR_TIPO_CAMBIO');
		$COD_USUARIO_VENDEDOR1 = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_VENDEDOR1');
		$PORC_VENDEDOR1 = $this->dws['dw_nota_venta']->get_item(0, 'PORC_VENDEDOR1');
		$COD_USUARIO_VENDEDOR2 = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_VENDEDOR2');
		$PORC_VENDEDOR2 = $this->dws['dw_nota_venta']->get_item(0, 'PORC_VENDEDOR2');
		$COD_ORIGEN_VENTA = "null";
		$COD_CUENTA_CORRIENTE = $this->dws['dw_nota_venta']->get_item(0, 'COD_CUENTA_CORRIENTE');
		$REFERENCIA = $this->dws['dw_nota_venta']->get_item(0, 'REFERENCIA');
		$REFERENCIA = str_replace("'", "''", $REFERENCIA);
		$COD_EMPRESA = $this->dws['dw_nota_venta']->get_item(0, 'COD_EMPRESA');
		$COD_SUCURSAL_DESPACHO = $this->dws['dw_nota_venta']->get_item(0, 'COD_SUCURSAL_DESPACHO');
		$COD_SUCURSAL_FACTURA = $this->dws['dw_nota_venta']->get_item(0, 'COD_SUCURSAL_FACTURA');
		$COD_PERSONA = $this->dws['dw_nota_venta']->get_item(0, 'COD_PERSONA');
		$MOTIVO_ANULA = $this->dws['dw_nota_venta']->get_item(0, 'MOTIVO_ANULA');
		$MOTIVO_ANULA = str_replace("'", "''", $MOTIVO_ANULA);
		$COD_USUARIO_ANULA = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_ANULA');
		$FECHA_ENTREGA = $this->dws['dw_nota_venta']->get_item(0, 'FECHA_ENTREGA');
		$OBS_DESPACHO = $this->dws['dw_nota_venta']->get_item(0, 'OBS_DESPACHO');
		$OBS_DESPACHO = str_replace("'", "''", $OBS_DESPACHO);
		$OBS = $this->dws['dw_nota_venta']->get_item(0, 'OBS');
		$OBS = str_replace("'", "''", $OBS);
		$SUBTOTAL = $this->dws['dw_nota_venta']->get_item(0, 'SUM_TOTAL');
		$PORC_DSCTO1 = $this->dws['dw_nota_venta']->get_item(0, 'PORC_DSCTO1');
		$MONTO_DSCTO1 = $this->dws['dw_nota_venta']->get_item(0, 'MONTO_DSCTO1');
		$PORC_DSCTO2 = $this->dws['dw_nota_venta']->get_item(0, 'PORC_DSCTO2');
		$MONTO_DSCTO2 = $this->dws['dw_nota_venta']->get_item(0, 'MONTO_DSCTO2');
		$PORC_IVA = $this->dws['dw_nota_venta']->get_item(0, 'PORC_IVA');
		
		$MONTO_IVA = $this->dws['dw_nota_venta']->get_item(0, 'MONTO_IVA');
		$TOTAL_CON_IVA = $this->dws['dw_nota_venta']->get_item(0, 'TOTAL_CON_IVA');
		$TOTAL_NETO = $this->dws['dw_nota_venta']->get_item(0, 'TOTAL_NETO');
		$INGRESO_USUARIO_DSCTO1 = $this->dws['dw_nota_venta']->get_item(0, 'INGRESO_USUARIO_DSCTO1');
		$INGRESO_USUARIO_DSCTO2 = $this->dws['dw_nota_venta']->get_item(0, 'INGRESO_USUARIO_DSCTO2');
		$PORC_DSCTO_CORPORATIVO = $this->dws['dw_nota_venta']->get_item(0, 'PORC_DSCTO_CORPORATIVO_H');
		
		$CIERRE_H			= $this->dws['dw_nota_venta']->get_item(0, 'CIERRE_H');
		$CIERRE_SIN_P_H		= $this->dws['dw_nota_venta']->get_item(0, 'CIERRE_SIN_P_H');
		$MOTIVO_CIERRE_SIN_PART_H		= $this->dws['dw_nota_venta']->get_item(0, 'MOTIVO_CIERRE_SIN_PART_H');
		$CREADA_EN_SV = $this->dws['dw_nota_venta']->get_item(0, 'CREADA_EN_SV');
		if($CIERRE_SIN_P_H == 'N'){
			$MOTIVO_CIERRE_SIN_PART_H = "null";
		}else{
			$MOTIVO_CIERRE_SIN_PART_H = "'$MOTIVO_CIERRE_SIN_PART_H'";
			
		}
		
		
		$FECHA_PLAZO_CIERRE = $this->dws['dw_nota_venta']->get_item(0, 'FECHA_PLAZO_CIERRE');
		$COD_USUARIO_CONFIRMA = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_CONFIRMA_H');
		
		
		if (($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_CONFIRMADA) && ($COD_USUARIO_CONFIRMA == ''))// se confirma
			$COD_USUARIO_CONFIRMA		= $this->cod_usuario;
		else
			$COD_USUARIO_CONFIRMA		= "null";
		
		if ($CIERRE_H == 'S' ){ // se da clic en boton cerrar
			$COD_USUARIO_CIERRE = $this->cod_usuario;
		}elseif($CIERRE_SIN_P_H == 'S'){
			$COD_USUARIO_CIERRE = $this->cod_usuario;
		}else{
			$COD_USUARIO_CIERRE			= "null";
		}	
		if (($MOTIVO_ANULA != '') && ($COD_USUARIO_ANULA == '')) // se anula 
			$COD_USUARIO_ANULA			= $this->cod_usuario;
		else
			$COD_USUARIO_ANULA			= "null";

		$NRO_ORDEN_COMPRA = ($NRO_ORDEN_COMPRA =='') ? "null" : "'$NRO_ORDEN_COMPRA'";
		$FECHA_ORDEN_COMPRA_CLIENTE = $this->str2date($FECHA_ORDEN_COMPRA_CLIENTE);
		$CENTRO_COSTO_CLIENTE = ($CENTRO_COSTO_CLIENTE =='') ? "null" : "'$CENTRO_COSTO_CLIENTE'";
		$COD_COTIZACION	= ($COD_COTIZACION =='') ? "null" : $COD_COTIZACION;
		$COD_USUARIO_VENDEDOR2 = ($COD_USUARIO_VENDEDOR2 =='') ? "null" : $COD_USUARIO_VENDEDOR2;
		$PORC_VENDEDOR2	= ($PORC_VENDEDOR2 =='') ? "null" : $PORC_VENDEDOR2;		
		$COD_CUENTA_CORRIENTE	= ($COD_CUENTA_CORRIENTE =='') ? "null" : $COD_CUENTA_CORRIENTE;
		$OBS_DESPACHO = ($OBS_DESPACHO =='') ? "null" : "'$OBS_DESPACHO'";
		$OBS = ($OBS =='') ? "null" : "'$OBS'";
		$MOTIVO_ANULA = ($MOTIVO_ANULA =='') ? "null" : "'$MOTIVO_ANULA'";
		$INGRESO_USUARIO_DSCTO1 = ($INGRESO_USUARIO_DSCTO1 =='') ? "null" : "'$INGRESO_USUARIO_DSCTO1'";
		$INGRESO_USUARIO_DSCTO2 = ($INGRESO_USUARIO_DSCTO2 =='') ? "null" : "'$INGRESO_USUARIO_DSCTO2'";
		$COD_ORIGEN_VENTA = ($COD_ORIGEN_VENTA =='') ? "null" : $COD_ORIGEN_VENTA;		
		
		$SUBTOTAL = ($SUBTOTAL == '' ? 0: "$SUBTOTAL");
		$PORC_DSCTO1 = ($PORC_DSCTO1 == '' ? 0: "$PORC_DSCTO1");
		$MONTO_DSCTO1 = ($MONTO_DSCTO1 == '' ? 0: "$MONTO_DSCTO1");
		$PORC_DSCTO2 = ($PORC_DSCTO2 == '' ? 0: "$PORC_DSCTO2");
		$MONTO_DSCTO2 = ($MONTO_DSCTO2 == '' ? 0: "$MONTO_DSCTO2");
		$PORC_IVA = ($PORC_IVA == '' ? 0: "$PORC_IVA");
		$MONTO_IVA = ($MONTO_IVA == '' ? 0: "$MONTO_IVA");
		$TOTAL_CON_IVA = ($TOTAL_CON_IVA == '' ? 0: "$TOTAL_CON_IVA");
		$TOTAL_NETO = ($TOTAL_NETO == '' ? 0: "$TOTAL_NETO");
		$PORC_DSCTO_CORPORATIVO = ($PORC_DSCTO_CORPORATIVO == '' ? 0: "$PORC_DSCTO_CORPORATIVO");
		
		$COD_FORMA_PAGO = $this->dws['dw_nota_venta']->get_item(0, 'COD_FORMA_PAGO');
		if ($COD_FORMA_PAGO==1){ // forma de pago = OTRO
			$NOM_FORMA_PAGO_OTRO= $this->dws['dw_nota_venta']->get_item(0, 'NOM_FORMA_PAGO_OTRO');
			$CANTIDAD_DOC_FORMA_PAGO_OTRO= $this->dws['dw_nota_venta']->get_item(0, 'CANTIDAD_DOC_FORMA_PAGO_OTRO');
			
		}else{
			$NOM_FORMA_PAGO_OTRO= "";
			$CANTIDAD_DOC_FORMA_PAGO_OTRO= "";
		}
		$NOM_FORMA_PAGO_OTRO= ($NOM_FORMA_PAGO_OTRO =='') ? "null" : "'$NOM_FORMA_PAGO_OTRO'";
		$CANTIDAD_DOC_FORMA_PAGO_OTRO= ($CANTIDAD_DOC_FORMA_PAGO_OTRO =='') ? "null" : "$CANTIDAD_DOC_FORMA_PAGO_OTRO";
		
		$COD_NOTA_VENTA = ($COD_NOTA_VENTA=='') ? "null" : $COD_NOTA_VENTA;		
    		
		$sp = 'spu_nota_venta';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
		$param	= "	'$operacion',
					$COD_NOTA_VENTA,
					'$FECHA_NOTA_VENTA',
					$COD_USUARIO,  
					$COD_ESTADO_NOTA_VENTA, 
					$NRO_ORDEN_COMPRA,
					$FECHA_ORDEN_COMPRA_CLIENTE,
					$CENTRO_COSTO_CLIENTE, 
					$COD_MONEDA, 
					$VALOR_TIPO_CAMBIO, 
					$COD_COTIZACION,
					$COD_USUARIO_VENDEDOR1,
					$PORC_VENDEDOR1,
					$COD_USUARIO_VENDEDOR2,
					$PORC_VENDEDOR2,
					$COD_CUENTA_CORRIENTE,
					$COD_ORIGEN_VENTA,
					'$REFERENCIA',
					$COD_EMPRESA,
					$COD_SUCURSAL_DESPACHO,
					$COD_SUCURSAL_FACTURA,
					$COD_PERSONA,
					$SUBTOTAL,
					$PORC_DSCTO1,
					$MONTO_DSCTO1,
					$PORC_DSCTO2,
					$MONTO_DSCTO2,
					$PORC_IVA,
					$MONTO_IVA,
					$TOTAL_CON_IVA,
					'$FECHA_ENTREGA',
					$OBS_DESPACHO,
					$OBS,	
					$COD_FORMA_PAGO,
					$MOTIVO_ANULA,
					$COD_USUARIO_ANULA,
					$TOTAL_NETO, 
					$INGRESO_USUARIO_DSCTO1,
					$INGRESO_USUARIO_DSCTO2,
					$NOM_FORMA_PAGO_OTRO,
					$CANTIDAD_DOC_FORMA_PAGO_OTRO,
					$PORC_DSCTO_CORPORATIVO,
					$COD_USUARIO_CIERRE,
					'$FECHA_PLAZO_CIERRE',
					$COD_USUARIO_CONFIRMA,
					$MOTIVO_CIERRE_SIN_PART_H,
					'$CIERRE_SIN_P_H',
					'$CREADA_EN_SV'";	
			
					
					
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				//$COD_NOTA_VENTA = $db->GET_IDENTITY();
				$sql = "select max(COD_NOTA_VENTA) COD_NOTA_VENTA from NOTA_VENTA";
				$result = $db->build_results($sql);
				$COD_NOTA_VENTA = $result[0]['COD_NOTA_VENTA'];
				/////
				
				$this->dws['dw_nota_venta']->set_item(0, 'COD_NOTA_VENTA', $COD_NOTA_VENTA);
				$this->f_envia_mail('EMITIDO');//$operacion, $COD_ESTADO_NOTA_VENTA, $COD_NOTA_VENTA);
			}
		
			if($COD_USUARIO_CONFIRMA != "null")
				$this->f_envia_mail('CONFIRMADO');
				
			if (($MOTIVO_ANULA != 'null') && ($COD_USUARIO_ANULA != 'null')){ // se anula 
				$this->f_envia_mail('ANULADA');
			}
			for ($i=0; $i<$this->dws['dw_item_nota_venta']->row_count(); $i++) 
				$this->dws['dw_item_nota_venta']->set_item($i, 'COD_NOTA_VENTA', $COD_NOTA_VENTA);
			
			if (!$this->dws['dw_item_nota_venta']->update($db, $this->dws['dw_pre_orden_compra'])) return false;
		
			$parametros_sp = "'item_nota_venta','nota_venta',$COD_NOTA_VENTA";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)) return false;
			
			
			$cod_llamado	= $this->dws['dw_llamado']->get_item(0, 'LL_COD_LLAMADO');
			$parametros_sp="'REALIZADO_WEB'
							,$cod_llamado
							,null
							,null
							,null
							,null
							,null
							,null
							,'S'
							,null
							,'NOTA VENTA'
							,$COD_NOTA_VENTA";
			
		if($cod_llamado <> ''){				
				if (!$db->EXECUTE_SP('spu_llamado', $parametros_sp)){
						  return false;
				}else{
					$param="'INSERT',
								NULL,
								$cod_llamado,
								NULL,
								'realizado con exito',
								'S',
								'N'";	
						
						if (!$db->EXECUTE_SP('spu_llamado_conversa', $param))
							return false;						
				}
			}							
							
			
			if (!$this->dws['dw_pre_orden_compra']->update($db)) return false;
		
			for ($i=0; $i<$this->dws['dw_doc_nota_venta']->row_count(); $i++)
				$this->dws['dw_doc_nota_venta']->set_item($i, 'COD_NOTA_VENTA', $COD_NOTA_VENTA);

			if (!$this->dws['dw_doc_nota_venta']->update($db)) return false;
		
			if (!$this->dws['dw_tipo_pendiente_nota_venta']->update($db)) return false;
		
			if (!$this->dws['dw_docs']->update($db, $COD_NOTA_VENTA)) return false;

			$COD_USUARIO_CONFIRMA = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_CONFIRMA_H');
			if (($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_CONFIRMADA) && ($COD_USUARIO_CONFIRMA == '')){// se confirma
				$parametros_sp = "$COD_NOTA_VENTA";
				if (!$db->EXECUTE_SP('sp_nv_crea_orden_compra', $parametros_sp)) return false;
			}			
			$parametros_sp = "'RECALCULA',$COD_NOTA_VENTA";   
            if (!$db->EXECUTE_SP('spu_nota_venta', $parametros_sp))
                return false;
                
            return true;
		}
		return false;	
					
	}
}
?>
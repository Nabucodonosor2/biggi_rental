alter PROCEDURE spu_registro_ingreso(	@ve_operacion	varchar(20)
										,@VE_NRO_PROFORMA	numeric(10,0) = null
										,@VE_FECHA_PROF		datetime = null
										,@VE_COD_PROV		varchar(10) = null
										,@VE_NUMERO_OC		varchar(20) = null
										,@VE_FECHA_OC		datetime = null
										,@VE_NUMERO_REGISTRO_INGRESO numeric(10,0)	
										,@VE_OBS varchar(2000)	
										,@VE_TOTAL_EX_FCA numeric(10,2) = null	
										,@VE_EMBALAJE	numeric(10,2) = null
										,@VE_FLETE_INTERNO	numeric(10,2) = null
										,@VE_TOTAL_OTROS	numeric(10,2) = null
										,@VE_TOTAL_FOB	numeric(10,2) = null
										,@VE_FLETE	numeric(10,2) = null
										,@VE_SEGURO	numeric(10,2) = null
										,@VE_TOTAL_CIF	numeric(10,2) = null
										,@VE_TOTAL_CIF_PESOS	numeric(10,2) = null
										,@VE_AD_VALOREM_PORC	numeric(10,2) = null
										,@VE_AD_VALOREM	numeric(10,2) = null
										,@VE_TASA_AEREO_PORC numeric(10,2) = null
										,@VE_TASA_AEREO	numeric(10,2) = null
										,@VE_AGENTE_ADUANA_POR	numeric(10,2) = null
										,@VE_AGENTE_ADUANA	numeric(10,2) = null
										,@VE_FLETE_CHILE	numeric(10,2) = null
										,@VE_OTROS1	numeric(10,2) = null
										,@VE_TOTAL_DTD	numeric(10,2) = null
										,@VE_MES_DOLAR	numeric(10,0) = null
										,@VE_NRO_EMBARQUE	varchar(100) = null
										,@VE_REFERENCIA	varchar(100) = null
										,@VE_NUM_IMPORT	varchar(2000) = null
										,@VE_FECHA_IMPORT	datetime = null
										,@VE_CLAUSULA	varchar(20) = null
										,@VE_FECHA_REGISTRO_INGRESO	datetime = null
										,@VE_EN_PESOS varchar(1) = null
										,@VE_XX varchar(1) = null
										,@VE_TOTAL_GASTOS	numeric(10,2) = null
										,@VE_TOTAL_GASTOS_US numeric(10,2) = null	
										,@VE_FACTOR_IMP	numeric(15,2) = null
										,@VE_COBRANZA	varchar(100) = null
										,@VE_CD_NUM	numeric(10,0) = null
										,@VE_FECHA_VTO_BANCO datetime = null
										,@VE_NAVE	varchar(100) = null
										,@VE_BL	 varchar(100) = null
										,@VE_FECHA_EMBARQUE	datetime = null
										,@VE_FECHA_BODEGA	datetime = null
										,@VE_TOTAL_GASTO numeric(10,0) = null
										,@VE_PAGO_COBERTURA	numeric(10,2) = null
										,@VE_FECHA_COBERTURA	datetime = null	
										,@VE_FECHA_FLETE	datetime = null
										,@VE_FECHA_SEGURO	datetime = null
										,@VE_FORMA_PAGO	varchar(50) = null
										,@VE_COD_MES numeric(10,0) = null
										,@VE_VALOR_DOLAR numeric(10,2) = null
										,@VE_ALIAS_PROV	varchar(20) = null
										,@VE_RUT_PROV	varchar(10) = null
										,@VE_VALOR_DOLAR_ACUERDO	numeric = null
										,@VE_GRUA	numeric(10,2) = null
										,@VE_PERMISO_MUNI	numeric(10,2) = null
										,@VE_DESCONSOLIDACION	numeric(10,2) = null
										,@VE_CARTA_CREDITO	numeric(10,2) = null
										,@VE_ALMACENAJE	numeric(10,2) = null
										,@VE_OTROS	numeric(10,2) = null
										,@VE_ANO_PROF	numeric(4,0) = null
										,@VE_GASTO_ORDEN_PAGO	numeric(10,2) = null
										,@VE_SUBTOTAL_EX_FCA	numeric(10,2) = null
										,@VE_DESCTO_EX_FCA	numeric(10,2) = null
										,@VE_FLETE_SCL	numeric(10,2) = null
										,@VE_IMPORTADO_DESDE_4D varchar(1) = null)

AS
begin 
		
		if (@ve_operacion='INSERT') begin
			
				insert into REGISTRO_INGRESO_4D (NRO_PROFORMA
												,FECHA_PROF
												,COD_PROV	
												,NUMERO_OC	
												,FECHA_OC	
												,NUMERO_REGISTRO_INGRESO	
												,OBS	
												,TOTAL_EX_FCA	
												,EMBALAJE	
												,FLETE_INTERNO	
												,TOTAL_OTROS	
												,TOTAL_FOB	
												,FLETE	
												,SEGURO	
												,TOTAL_CIF	
												,TOTAL_CIF_PESOS	
												,AD_VALOREM_PORC	
												,AD_VALOREM	
												,TASA_AEREO_PORC	
												,TASA_AEREO	
												,AGENTE_ADUANA_POR	
												,AGENTE_ADUANA	
												,FLETE_CHILE	
												,OTROS1	
												,TOTAL_DTD	
												,COD_MES
												,NRO_EMBARQUE	
												,REFERENCIA	
												,NUM_IMPORT	
												,FECHA_IMPORT	
												,CLAUSULA	
												,FECHA_REGISTRO_INGRESO	
												,EN_PESOS	
												,XX	
												,TOTAL_GASTOS	
												,TOTAL_GASTOS_US	
												,FACTOR_IMP	
												,COBRANZA	
												,CD_NUM	
												,FECHA_VTO_BANCO	
												,NAVE	
												,BL	
												,FECHA_EMBARQUE	
												,FECHA_BODEGA	
												,TOTAL_GASTO	
												,PAGO_COBERTURA	
												,FECHA_COBERTURA	
												,FECHA_FLETE	
												,FECHA_SEGURO	
												,FORMA_PAGO	
												,VALOR_DOLAR	
												,ALIAS_PROV	
												,RUT_PROV	
												,VALOR_DOLAR_ACUERDO	
												,GRUA	
												,PERMISO_MUNI	
												,DESCONSOLIDACION	
												,CARTA_CREDITO	
												,ALMACENAJE	
												,OTROS	
												,ANO_PROF	
												,GASTO_ORDEN_PAGO	
												,SUBTOTAL_EX_FCA	
												,DESCTO_EX_FCA	
												,FLETE_SCL	
												,IMPORTADO_DESDE_4D)
										 values(@VE_NRO_PROFORMA
												,@VE_FECHA_PROF
												,@VE_COD_PROV
												,@VE_NUMERO_OC
												,@VE_FECHA_OC
												,@VE_NUMERO_REGISTRO_INGRESO
												,@VE_OBS
												,@VE_TOTAL_EX_FCA
												,@VE_EMBALAJE
												,@VE_FLETE_INTERNO
												,@VE_TOTAL_OTROS
												,@VE_TOTAL_FOB
												,@VE_FLETE
												,@VE_SEGURO
												,@VE_TOTAL_CIF
												,@VE_TOTAL_CIF_PESOS
												,@VE_AD_VALOREM_PORC
												,@VE_AD_VALOREM
												,@VE_TASA_AEREO_PORC 
												,@VE_TASA_AEREO	
												,@VE_AGENTE_ADUANA_POR
												,@VE_AGENTE_ADUANA
												,@VE_FLETE_CHILE
												,@VE_OTROS1
												,@VE_TOTAL_DTD
												,MONTH(getdate()) 		
												,@VE_NRO_EMBARQUE
												,@VE_REFERENCIA
												,@VE_NUM_IMPORT
												,@VE_FECHA_IMPORT
												,@VE_CLAUSULA
												,getdate()
												,@VE_EN_PESOS
												,@VE_XX
												,@VE_TOTAL_GASTOS
												,@VE_TOTAL_GASTOS_US
												,@VE_FACTOR_IMP
												,@VE_COBRANZA
												,@VE_CD_NUM
												,@VE_FECHA_VTO_BANCO
												,@VE_NAVE
												,@VE_BL
												,@VE_FECHA_EMBARQUE
												,@VE_FECHA_BODEGA
												,@VE_TOTAL_GASTO
												,@VE_PAGO_COBERTURA
												,@VE_FECHA_COBERTURA
												,@VE_FECHA_FLETE
												,@VE_FECHA_SEGURO
												,@VE_FORMA_PAGO
												,@VE_VALOR_DOLAR
												,@VE_ALIAS_PROV
												,@VE_RUT_PROV
												,@VE_VALOR_DOLAR_ACUERDO
												,@VE_GRUA
												,@VE_PERMISO_MUNI
												,@VE_DESCONSOLIDACION
												,@VE_CARTA_CREDITO
												,@VE_ALMACENAJE
												,@VE_OTROS
												,@VE_ANO_PROF
												,@VE_GASTO_ORDEN_PAGO
												,@VE_SUBTOTAL_EX_FCA
												,@VE_DESCTO_EX_FCA
												,@VE_FLETE_SCL
												,@VE_IMPORTADO_DESDE_4D)
		
		
		
		end
		if (@ve_operacion='UPDATE') begin
		update REGISTRO_INGRESO_4D 
		set NRO_PROFORMA 		= @VE_NRO_PROFORMA
			,FECHA_PROF			= @VE_FECHA_PROF
			,COD_PROV 			= @VE_COD_PROV
			,NUMERO_OC			= @VE_NUMERO_OC
			,FECHA_OC			= @VE_FECHA_OC
			,OBS				= @VE_OBS
			,TOTAL_EX_FCA		= @VE_TOTAL_EX_FCA
			,EMBALAJE			= @VE_EMBALAJE
			,FLETE_INTERNO		= @VE_FLETE_INTERNO
			,TOTAL_OTROS		= @VE_TOTAL_OTROS
			,TOTAL_FOB 			= @VE_TOTAL_FOB	
			,FLETE				= @VE_FLETE
			,SEGURO	 			= @VE_SEGURO
			,TOTAL_CIF			= @VE_TOTAL_CIF
			,TOTAL_CIF_PESOS 	= @VE_TOTAL_CIF_PESOS
			,AD_VALOREM_PORC	= @VE_AD_VALOREM_PORC
			,AD_VALOREM			= @VE_AD_VALOREM
			,AGENTE_ADUANA_POR	= @VE_AGENTE_ADUANA_POR
			,AGENTE_ADUANA		= @VE_AGENTE_ADUANA
			,FLETE_CHILE 		= @VE_FLETE_CHILE	
			,OTROS1				= @VE_OTROS1
			,TOTAL_DTD			= @VE_TOTAL_DTD
			,NRO_EMBARQUE		= @VE_NRO_EMBARQUE
			,REFERENCIA	 		= @VE_REFERENCIA
			,NUM_IMPORT	 		= @VE_NUM_IMPORT
			,FECHA_IMPORT		= @VE_FECHA_IMPORT
			,CLAUSULA			= @VE_CLAUSULA
			,EN_PESOS			= @VE_EN_PESOS
			,TOTAL_GASTOS		= @VE_TOTAL_GASTOS
			,TOTAL_GASTOS_US	= @VE_TOTAL_GASTOS_US
			,FACTOR_IMP			= @VE_FACTOR_IMP
			,COBRANZA			= @VE_COBRANZA
			,CD_NUM	 			= @VE_CD_NUM
			,FECHA_VTO_BANCO	= @VE_FECHA_VTO_BANCO 
			,NAVE				= @VE_NAVE
			,BL					= @VE_BL
			,FECHA_EMBARQUE		= @VE_FECHA_EMBARQUE
			,FECHA_BODEGA		= @VE_FECHA_BODEGA
			,TOTAL_GASTO		= @VE_TOTAL_GASTO
			,PAGO_COBERTURA		= @VE_PAGO_COBERTURA
			,FECHA_COBERTURA	= @VE_FECHA_COBERTURA	
			,FECHA_FLETE		= @VE_FECHA_FLETE
			,FECHA_SEGURO		= @VE_FECHA_SEGURO
			,FORMA_PAGO			= @VE_FORMA_PAGO
			,COD_MES			= @VE_COD_MES
			,VALOR_DOLAR		= @VE_VALOR_DOLAR
			,ALIAS_PROV			= @VE_ALIAS_PROV
			,RUT_PROV			= @VE_RUT_PROV
			,VALOR_DOLAR_ACUERDO	= @VE_VALOR_DOLAR_ACUERDO	
			,GRUA				= @VE_GRUA
			,PERMISO_MUNI		= @VE_PERMISO_MUNI
			,DESCONSOLIDACION	= @VE_DESCONSOLIDACION
			,CARTA_CREDITO		= @VE_CARTA_CREDITO
			,ALMACENAJE			= @VE_ALMACENAJE
			,OTROS				= @VE_OTROS
			,ANO_PROF			= @VE_ANO_PROF
			,GASTO_ORDEN_PAGO	= @VE_GASTO_ORDEN_PAGO
			,SUBTOTAL_EX_FCA	= @VE_SUBTOTAL_EX_FCA
			,DESCTO_EX_FCA		= @VE_DESCTO_EX_FCA
			,FLETE_SCL			= @VE_FLETE_SCL
		where NUMERO_REGISTRO_INGRESO = @VE_NUMERO_REGISTRO_INGRESO
	end
	else if (@ve_operacion='DELETE') begin
		delete REGISTRO_INGRESO_4D 
		where NUMERO_REGISTRO_INGRESO = @VE_NUMERO_REGISTRO_INGRESO
	end	

end 	
-------------------------- sp_fa_arriendo --------------------------
ALTER PROCEDURE [dbo].[sp_fa_arriendo](@ve_lista_contrato		varchar(8000)
								,@ve_agrupar_contrato	varchar(1)
								,@ve_cod_usuario		numeric
								,@ve_fecha_stock		datetime = null)
AS
/*
Crea facturas de arriendo para la lista de contratos @ve_lista_contrato
@ve_lista_contrato : lista de contratos de la forma "cod_contrato1|cod_contrato2|....|cod_contratoN"
@ve_agrupar_contrato : 'S' indica que crea facturas agrpadas donde cada linea es un contrato de arrindo
						'N' indica que se debe hacer 1 contrato una factura, donde los items son los items del contrato
						NO usado por ahora SIMEPRE viene en 'S'
*/
BEGIN  
	declare C_CONTRATO CURSOR FOR  
	select item 
	from  f_split(@ve_lista_contrato, '|')	

	declare
		@vc_cod_arriendo			numeric
		,@vl_cod_factura			numeric
		,@vl_cod_log_cambio			numeric
		,@vl_orden					numeric
		,@vc_cod_producto			varchar(30)
		,@vc_nom_producto			varchar(100)
		,@vc_cantidad				T_CANTIDAD
		,@vc_precio					numeric
		,@vl_count_contrato			numeric
		,@vl_obs					varchar(100)
		,@vl_str_cod_sodxo			varchar(100)
		,@vc_cod_empresa			varchar(50)
		,@vl_exige_cheque			varchar(1)
		,@vl_cod_cheque				numeric
		,@vl_monto_asignado			numeric
		,@vl_monto_iva				numeric
		,@vl_con_fecha_stock		varchar(1)
		,@vl_saldo_ch				numeric
		,@vl_fecha_orden_compra		datetime
		
	if (@ve_fecha_stock is null)
		set @vl_con_fecha_stock = 'N' 
	else
		set @vl_con_fecha_stock = 'S' 
	
	select @vl_count_contrato = COUNT(*)
	from  f_split(@ve_lista_contrato, '|')	

	set @vl_cod_factura = null
	set @vl_orden = 1
	OPEN C_CONTRATO
	FETCH C_CONTRATO INTO @vc_cod_arriendo
	WHILE @@FETCH_STATUS = 0 BEGIN	
		if (@vl_cod_factura is null) begin		-- 1ra vez que entra al loop se debe crear FA
			declare 
				@K_FA_RENTAL				numeric
				,@K_PARAM_IVA				numeric
				,@vl_cod_empresa			numeric
				,@vl_cod_persona			numeric
				,@vl_referencia				varchar(100) 
				,@vl_cod_sucursal_factura	numeric
				,@vl_porc_iva				T_PORCENTAJE
				,@vl_nom_forma_pago_otro	varchar(100)
				,@vl_referencia_arr			varchar(100) 
				,@vl_nro_orden_compra		varchar(100) 

			set @K_FA_RENTAL = 2
			set @K_PARAM_IVA = 1
			set @vl_porc_iva = convert(decimal, dbo.f_get_parametro(@K_PARAM_IVA))
			
			if(@vl_count_contrato = 1) begin
				select @vl_cod_empresa = cod_empresa
					,@vl_cod_persona = cod_persona
					,@vl_cod_sucursal_factura = cod_sucursal
				from arriendo
				where cod_arriendo = @vc_cod_arriendo
			end
			else begin
				--marcelo pidio este cambio para cuando la lista de contrato a facturar es mayor a uno 
				--que obtenga la direccion de facturacion de la empresa no del contrato
				--EV 14/11/2019
				select @vl_cod_empresa = a.cod_empresa
					,@vl_cod_persona = cod_persona
					,@vl_cod_sucursal_factura = s.cod_sucursal
				from arriendo a, EMPRESA e, SUCURSAL s
				where cod_arriendo =@vc_cod_arriendo
				and e.COD_EMPRESA = a.COD_EMPRESA
				and s.COD_EMPRESA = e.COD_EMPRESA
				and s.DIRECCION_FACTURA = 'S'
			end

			--10-12-2015 mail de RE asunto "FW: ERROR FACTURA AKY" del 10-12-2015
			set @ve_fecha_stock = getdate()
			
			select @vl_referencia = 'CONTRATOS DE ARRIENDO  DE ' + upper(m.nom_mes) + ' ' + CONVERT(varchar, year(@ve_fecha_stock))
			from mes m
			where m.cod_mes = month(@ve_fecha_stock)

			if (@vl_count_contrato=1) begin
				select @vl_referencia_arr = REFERENCIA
						,@vl_nro_orden_compra = NRO_ORDEN_COMPRA
				from ARRIENDO
				where COD_ARRIENDO = @vc_cod_arriendo
				set @vl_obs =  'CONTRATO Nº '+CONVERT(varchar, @vc_cod_arriendo)+' ('+@vl_referencia_arr+')'
			end
			else begin
				set @vl_obs = null
				set @vl_nro_orden_compra = null
			end
			
			-- Si coincide con los cod_empresa de sodexo pasa automaticamente a todoinox
			/*declare C_COD_SDXO CURSOR FOR
			SELECT ITEM
			from  dbo.f_split((SELECT dbo.f_get_parametro(60)), '|')
			
			OPEN C_COD_SDXO
			FETCH C_COD_SDXO INTO @vc_cod_empresa
			WHILE @@FETCH_STATUS = 0 BEGIN
				if(@vl_cod_empresa = @vc_cod_empresa)begin
					/*UPDATE ARRIENDO
					SET COD_EMPRESA = 4					-- TODOINOX
					   ,COD_SUCURSAL = 213				-- CASA MATRIZ
					   ,COD_PERSONA	= 200				-- MARGARITA SCIANCA
					WHERE COD_ARRIENDO = @vc_cod_arriendo*/
					
					set @vl_cod_empresa = 4				-- TODOINOX
					set @vl_cod_sucursal_factura = 213	-- CASA MATRIZ
					set @vl_cod_persona = 200			-- MARGARITA SCIANCA
					BREAK
				END 
				FETCH C_COD_SDXO INTO @vc_cod_empresa
			END
			CLOSE C_COD_SDXO
			DEALLOCATE C_COD_SDXO*/
			----------------------------------------------------------------------------
			
			if(@vl_cod_empresa = 99) --CASINO EXPRESS S.A.
				set @vl_nro_orden_compra = null
			
			set @vl_fecha_orden_compra = GETDATE()
			
			execute spu_factura 
			'INSERT' 					-- ve_operacion
			,NULL 						-- ve_cod_factura = identity
			,NULL 						-- cod_usuario_impresion
			,@ve_cod_usuario 
			,NULL 						-- ve_nro_factura
			,NULL						-- FECHA_FACTURA	
			,1 							-- cod_estado_doc_sii = emitida
			,@vl_cod_empresa 
			,@vl_cod_sucursal_factura	-- ve_cod_sucursal_factura*
			,@vl_cod_persona
			,@vl_referencia 
			,@vl_nro_orden_compra		-- nro_orden_compra
			,@vl_fecha_orden_compra		-- fecha_orden_compra_cliente
			,@vl_obs 					-- obs
			,NULL 						-- retirado_por
			,NULL 						-- rut_retirado_por
			,NULL 						-- dig_verif_retirado_por
			,NULL 						-- guia_transporte
			,NULL 						-- patente
			,NULL 						-- cod_bodega
			,@K_FA_RENTAL				-- cod_tipo_factura = arriendo
			,null						-- cod_doc
			,NULL 						-- motivo_anula
			,NULL 						-- cod_usuario_anula 
			,@ve_cod_usuario 			-- vendedor1
			,0							-- @porc_vendedor1			
			,null						-- vendedor 2
			,0							-- porc_vendedor2		
			,7							-- cod_forma_pago = CCF-30 DIAS
			,null						-- cod_origen_venta		
			,0							-- subtotal
			,0							-- porc_dscto1		
			,'P' 	
			,0 							-- monto_dscto1		
			,0							-- porc_dscto2		
			,'P' 	
			,0 							-- monto_dscto2		
			,0							-- total_neto			
			,@vl_porc_iva			
			,0							-- monto_iva			 
			,0							-- total_con_iva		
			,NULL						-- @ve_porc_factura_parcial
			,null						-- nom_forma_pago_otro
			,'N'						-- genera_salida
			,'ARRIENDO'	
			,'N'						-- CANCELADA
			,NULL						--@ve_cod_centro_costo
	        ,NULL						--@ve_cod_vendedor_sofland
	        ,NULL						--@ve_ws_origen
	        ,NULL						--@ve_xml_dte
	        ,NULL						--@ve_track_id_dte
	        ,NULL						--@ve_resp_emitir_dte
	        ,NULL						--@ve_no_tiene_oc
	        ,'CREAR_DESDE'				--@ve_origen_factura

			set @vl_cod_factura = @@identity
			
			if (@vl_con_fecha_stock = 'S') begin
				execute sp_log_cambio
				'FACTURA_ARRIENDO'
				,@vl_cod_factura
				,@ve_cod_usuario
				,'I'
				set @vl_cod_log_cambio = @@identity
			end
		end

		declare C_ITEM CURSOR FOR  
		select distinct i.cod_producto
				,p.nom_producto
				,dbo.f_bodega_stock(i.cod_producto, a.cod_bodega, @ve_fecha_stock)
				,i.precio
		from item_mod_arriendo i, mod_arriendo m, arriendo a, producto p
		where m.cod_arriendo = @vc_cod_arriendo
		  and i.cod_mod_arriendo = m.cod_mod_arriendo
		  and a.cod_arriendo = m.cod_arriendo
		  and p.cod_producto = i.cod_producto
		  and dbo.f_bodega_stock(i.cod_producto, a.cod_bodega, @ve_fecha_stock) > 0

		OPEN C_ITEM
		FETCH C_ITEM INTO @vc_cod_producto, @vc_nom_producto, @vc_cantidad, @vc_precio
		WHILE @@FETCH_STATUS = 0 BEGIN	
			insert into ITEM_FACTURA
				(COD_FACTURA
				,ORDEN
				,ITEM
				,COD_PRODUCTO
				,NOM_PRODUCTO
				,CANTIDAD
				,PRECIO
				,COD_ITEM_DOC
				,COD_TIPO_TE
				,MOTIVO_TE
				,TIPO_DOC
				)
			values
				(@vl_cod_factura				--COD_FACTURA
				,@vl_orden						--ORDEN
				,convert(varchar, @vl_orden)	--ITEM
				,@vc_cod_producto				--COD_PRODUCTO
				,@vc_nom_producto				--NOM_PRODUCTO
				,@vc_cantidad					--CANTIDAD
				,@vc_precio						--PRECIO
				,@vc_cod_arriendo				--COD_ITEM_DOC
				,null							--COD_TIPO_TE
				,null							--MOTIVO_TE
				,'ARRIENDO'						--TIPO_DOC
				)
			set @vl_orden = @vl_orden + 1
			
			if (@vl_con_fecha_stock = 'S') begin
				declare @vl_valor_antiguo varchar(20)
						,@vl_valor_nuevo varchar(20)
						
				set @vl_valor_antiguo = convert(varchar(20), getdate(), 103)
				set @vl_valor_nuevo = convert(varchar(20), @ve_fecha_stock, 103)
				
				execute sp_detalle_cambio
				@vl_cod_log_cambio
				,'FECHA_STOCK'
				,@vl_valor_antiguo
				,@vl_valor_nuevo
			end
			FETCH C_ITEM INTO @vc_cod_producto, @vc_nom_producto, @vc_cantidad, @vc_precio
		END
		CLOSE C_ITEM
		DEALLOCATE C_ITEM		
		
		
		
		FETCH C_CONTRATO INTO @vc_cod_arriendo
	END
	CLOSE C_CONTRATO
	DEALLOCATE C_CONTRATO		

	execute spu_factura 'RECALCULA', @vl_cod_factura
	
	declare C_CONTRATO CURSOR FOR  
	select item 
	from  f_split(@ve_lista_contrato, '|')
	
	OPEN C_CONTRATO
	FETCH C_CONTRATO INTO @vc_cod_arriendo
	WHILE @@FETCH_STATUS = 0 BEGIN

		INSERT INTO FACTURA_CONTRATO (COD_FACTURA, COD_ARRIENDO) VALUES(@vl_cod_factura, @vc_cod_arriendo)

		SELECT @vl_exige_cheque = EXIGE_CHEQUE
		FROM ARRIENDO
		WHERE COD_ARRIENDO = @vc_cod_arriendo
		
		if(@vl_exige_cheque = 'S')BEGIN
			set @vl_cod_cheque = dbo.f_arr_1er_cheque(@vc_cod_arriendo)
			
			if (@vl_cod_cheque is not null) begin
				SELECT @vl_monto_asignado = SUM(PRECIO * CANTIDAD)
				FROM ITEM_FACTURA
				WHERE COD_FACTURA = @vl_cod_factura
				AND COD_ITEM_DOC =  @vc_cod_arriendo
				
				--agregar el iva
				SET @vl_monto_iva = round(@vl_monto_asignado * @vl_porc_iva / 100, 0)
				SET @vl_monto_asignado = @vl_monto_asignado + @vl_monto_iva
				
				set @vl_saldo_ch = dbo.f_ch_saldo_por_usar(@vl_cod_cheque)
				
				if (@vl_monto_asignado > @vl_saldo_ch)
					set @vl_monto_asignado = @vl_saldo_ch
				 
				INSERT INTO CHEQUE_FACTURA(
							COD_CHEQUE,
							COD_FACTURA,
							COD_INGRESO_PAGO,
							COD_ARRIENDO,
							MONTO_ASIGNADO)
					 values(@vl_cod_cheque,
							@vl_cod_factura,
							null,
							@vc_cod_arriendo,
							@vl_monto_asignado)
				
			END 
		END 
		
		FETCH C_CONTRATO INTO @vc_cod_arriendo
	END
	CLOSE C_CONTRATO
	DEALLOCATE C_CONTRATO
END
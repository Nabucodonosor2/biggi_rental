-------------------------- sp_fa_arriendo_contrato --------------------------
ALTER PROCEDURE sp_fa_arriendo_contrato(@ve_cod_arriendo		numeric(10)
									    ,@ve_cod_usuario		numeric(10))
AS
BEGIN  
	DECLARE
		@vl_cod_factura				numeric,
		@vl_monto_recuperacion		numeric,
		@vl_cod_sucursal_factura	numeric,
		@vl_nom_producto			varchar(1000),
		@K_FA_RENTAL				numeric,
		@K_PARAM_IVA				numeric,
		@ve_fecha_stock				datetime,
		@vl_referencia				varchar(500),
		@vl_cod_empresa				numeric,
		@vl_cod_persona				numeric,
		@vl_referencia_arr			varchar(500),
		@vl_obs						varchar(1000),
		@vl_fecha_orden_compra		datetime,
		@vl_nro_orden_compra		varchar(100),
		@vc_cod_empresa				numeric
	
	set @K_FA_RENTAL	= 2
	set @K_PARAM_IVA	= 1
	set @ve_fecha_stock = getdate()
	
	select @vl_referencia = 'CONTRATOS DE ARRIENDO DE ' + upper(m.nom_mes) + ' ' + CONVERT(varchar, year(@ve_fecha_stock))
	from mes m
	where m.cod_mes = month(@ve_fecha_stock)
	
	SELECT @vl_monto_recuperacion = MONTO_ADICIONAL_RECUPERACION
		  ,@vl_cod_empresa = COD_EMPRESA
		  ,@vl_cod_sucursal_factura = COD_SUCURSAL
		  ,@vl_cod_persona = COD_PERSONA
		  ,@vl_referencia_arr = REFERENCIA
	FROM ARRIENDO
	WHERE COD_ARRIENDO = @ve_cod_arriendo
	
	set @vl_obs =  'CONTRATO Nº '+CONVERT(varchar, @ve_cod_arriendo)+' ('+@vl_referencia_arr+')'
	
	-- Si coincide con los cod_empresa de sodexo pasa automaticamente a todoinox
	declare C_COD_SDXO CURSOR FOR
	SELECT ITEM
	from  dbo.f_split((SELECT dbo.f_get_parametro(60)), '|')
	
	OPEN C_COD_SDXO
	FETCH C_COD_SDXO INTO @vc_cod_empresa
	WHILE @@FETCH_STATUS = 0 BEGIN
		if(@vl_cod_empresa = @vc_cod_empresa)begin
			set @vl_cod_empresa = 4				-- TODOINOX
			set @vl_cod_sucursal_factura = 213	-- CASA MATRIZ
			set @vl_cod_persona = 200			-- MARGARITA SCIANCA
			BREAK
		END 
		FETCH C_COD_SDXO INTO @vc_cod_empresa
	END
	CLOSE C_COD_SDXO
	DEALLOCATE C_COD_SDXO
	--------------------------------------------------------
	
	if(@vl_cod_empresa = 99) --CASINO EXPRESS S.A.
		set @vl_nro_orden_compra = NULL
	ELSE BEGIN
		select @vl_nro_orden_compra = NRO_ORDEN_COMPRA
		from ARRIENDO A
			,EMPRESA E
			,SUCURSAL S
			,PERSONA P
		where COD_ARRIENDO = @ve_cod_arriendo
		and @vl_cod_empresa = E.COD_EMPRESA
		and S.COD_SUCURSAL = A.COD_SUCURSAL
		and P.COD_PERSONA = A.COD_PERSONA
	END 	
	
	INSERT INTO FACTURA (FECHA_REGISTRO
					     ,COD_USUARIO
					     ,NRO_FACTURA
					     ,FECHA_FACTURA
					     ,COD_ESTADO_DOC_SII
					     ,COD_EMPRESA
					     ,COD_SUCURSAL_FACTURA
					     ,COD_PERSONA
					     ,REFERENCIA
					     ,NRO_ORDEN_COMPRA
					     ,OBS
					     ,RETIRADO_POR
					     ,RUT_RETIRADO_POR
					     ,DIG_VERIF_RETIRADO_POR
					     ,GUIA_TRANSPORTE
					     ,PATENTE
					     ,GENERA_SALIDA
					     ,COD_BODEGA
					     ,COD_TIPO_FACTURA
					     ,COD_DOC
					     ,FECHA_ANULA
					     ,MOTIVO_ANULA
					     ,COD_USUARIO_ANULA
					     ,RUT
					     ,DIG_VERIF
					     ,NOM_EMPRESA
					     ,GIRO
					     ,NOM_SUCURSAL
					     ,DIRECCION
					     ,COD_COMUNA
					     ,COD_CIUDAD
					     ,COD_PAIS
					     ,TELEFONO
					     ,FAX
					     ,NOM_PERSONA
					     ,MAIL
					     ,COD_CARGO
					     ,COD_USUARIO_IMPRESION
					     ,COD_USUARIO_VENDEDOR1
					     ,PORC_VENDEDOR1
					     ,COD_USUARIO_VENDEDOR2
					     ,PORC_VENDEDOR2
					     ,COD_FORMA_PAGO
					     ,COD_ORIGEN_VENTA
					     ,SUBTOTAL
					     ,PORC_DSCTO1
					     ,INGRESO_USUARIO_DSCTO1
					     ,MONTO_DSCTO1
					     ,PORC_DSCTO2
					     ,INGRESO_USUARIO_DSCTO2
					     ,MONTO_DSCTO2
					     ,TOTAL_NETO
					     ,PORC_IVA
					     ,MONTO_IVA
					     ,TOTAL_CON_IVA
					     ,PORC_FACTURA_PARCIAL
					     ,NOM_FORMA_PAGO_OTRO
					     ,TIPO_DOC
					     ,CANCELADA
					     ,FECHA_ORDEN_COMPRA_CLIENTE
					     ,COD_CENTRO_COSTO
					     ,COD_VENDEDOR_SOFLAND
					     ,DESDE_4D
					     ,NOM_COMUNA
					     ,NOM_CIUDAD
					     ,NOM_PAIS
					     ,NOM_FORMA_PAGO
					     ,NO_TIENE_OC
					     ,COD_COTIZACION
					     ,WS_ORIGEN
					     ,COD_CONTRATO_ANTICIPO)
	select GETDATE()
		  ,@ve_cod_usuario
		  ,NULL
		  ,NULL
		  ,1
		  ,@vl_cod_empresa
		  ,@vl_cod_sucursal_factura
		  ,@vl_cod_persona
		  ,@vl_referencia
		  ,@vl_nro_orden_compra
		  ,@vl_obs
		  ,NULL
		  ,NULL
		  ,NULL
		  ,NULL
		  ,NULL
		  ,'N'
		  ,A.COD_BODEGA
		  ,@K_FA_RENTAL
		  ,NULL
		  ,NULL
		  ,NULL
		  ,NULL
		  ,RUT
		  ,DIG_VERIF
		  ,NOM_EMPRESA
		  ,GIRO
		  ,NOM_SUCURSAL
		  ,DIRECCION
		  ,COD_COMUNA
		  ,COD_CIUDAD
		  ,COD_PAIS
		  ,S.TELEFONO
		  ,S.FAX
		  ,NOM_PERSONA
		  ,EMAIL
		  ,COD_CARGO
		  ,NULL
		  ,@ve_cod_usuario
		  ,0
		  ,NULL
		  ,0
		  ,6
		  ,NULL
		  ,0
		  ,0
		  ,'P'
		  ,0
		  ,0
		  ,'P'
		  ,0
		  ,0
		  ,dbo.f_get_parametro(1)
		  ,0
		  ,0
		  ,NULL
		  ,NULL
		  ,'ARRIENDO'
		  ,'N'
		  ,NULL
		  ,NULL
		  ,NULL
		  ,'N'
		  ,(SELECT NOM_COMUNA FROM COMUNA C WHERE C.COD_COMUNA = S.COD_COMUNA)
		  ,(SELECT NOM_CIUDAD FROM CIUDAD C WHERE C.COD_CIUDAD = S.COD_CIUDAD)
		  ,(SELECT NOM_PAIS FROM PAIS P WHERE P.COD_PAIS = S.COD_PAIS)
		  ,NULL
		  ,'N'
		  ,NULL
		  ,NULL
		  ,@ve_cod_arriendo
	from ARRIENDO A
		,EMPRESA E
		,SUCURSAL S
		,PERSONA P
	where COD_ARRIENDO = @ve_cod_arriendo
	and @vl_cod_empresa = E.COD_EMPRESA
	and S.COD_SUCURSAL = A.COD_SUCURSAL
	and P.COD_PERSONA = A.COD_PERSONA
	
	set @vl_cod_factura = @@IDENTITY
	set @vl_nom_producto = 'COSTO POR RECUPERACION EQUIPOS CONTRATO N° '+CONVERT(VARCHAR, @vl_cod_factura)
	
	INSERT ITEM_FACTURA (COD_FACTURA
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
					    ,COD_TIPO_GAS
					    ,COD_TIPO_ELECTRICIDAD
					    ,COD_PRODUCTO_4D)
				VALUES  (@vl_cod_factura
						,1
						,'1'
						,'TE'
						,@vl_nom_producto
						,1
						,@vl_monto_recuperacion
						,@ve_cod_arriendo
						,null
						,null
						,'ARRIENDO'
						,NULL
						,NULL
						,NULL)
						
	exec spu_factura 'RECALCULA', @vl_cod_factura
END
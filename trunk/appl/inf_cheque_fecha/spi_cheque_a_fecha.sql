--------------- spi_cheque_a_fecha --------------
ALTER PROCEDURE spi_cheque_a_fecha(@ve_fecha	datetime
								  ,@ve_cod_usuario numeric)
AS
BEGIN
	
	declare
	@vl_fecha_actual		datetime

	set @vl_fecha_actual = getdate()
	
	delete INF_CHEQUE_FECHA
	where cod_usuario = @ve_cod_usuario
	
	UPDATE DOC_INGRESO_PAGO
	SET NEW_FECHA_DOC = FECHA_DOC
	WHERE NEW_FECHA_DOC IS NULL
	
	insert into INF_CHEQUE_FECHA
		(FECHA_INF_CHEQUE_FECHA
		,COD_USUARIO
		,COD_NOTA_VENTA			
		,NOM_EMPRESA
		,RUT
		,COD_INGRESO_PAGO
		,TIPO_REGISTRO
		,FECHA_DOC			
		,NRO_DOC				
		,MONTO_DOC
		,COD_DOC_INGRESO_PAGO
		,COD_BANCO
		,COD_INGRESO_CHEQUE
		)
	select @vl_fecha_actual
			,@ve_cod_usuario 
			,null														
			,e.NOM_EMPRESA											
			,CONVERT(VARCHAR,dbo.number_format(e.RUT, 0, ',', '.'))+'-'+CONVERT(VARCHAR, e.DIG_VERIF)
			,ip.COD_INGRESO_PAGO										
			,'ING PAGO'
			,dip.NEW_FECHA_DOC											
			,dip.NRO_DOC												
			,dip.MONTO_DOC												
			,dip.COD_DOC_INGRESO_PAGO
			,dip.COD_BANCO												
			,NULL
	from doc_ingreso_pago dip, INGRESO_PAGO ip, EMPRESA e
	where dip.COD_TIPO_DOC_PAGO in (2, 12)	--cheque, cheque a fecha
	and dip.NEW_FECHA_DOC >= @ve_fecha
	and dip.COD_CHEQUE IS NULL
	and ip.COD_INGRESO_PAGO = dip.COD_INGRESO_PAGO
	and ip.COD_ESTADO_INGRESO_PAGO = 2	--confirmado
	and e.COD_EMPRESA = ip.COD_EMPRESA
	UNION
	SELECT @vl_fecha_actual
		  ,@ve_cod_usuario
		  ,NULL
		  ,E.NOM_EMPRESA
		  ,CONVERT(VARCHAR ,dbo.number_format(E.RUT, 0, ',', '.'))+'-'+CONVERT(VARCHAR, e.DIG_VERIF)
		  ,IC.COD_INGRESO_CHEQUE
		  ,'REG CHEQUE'
		  ,FECHA_DOC NEW_FECHA_DOC
		  ,NRO_DOC
		  ,MONTO_DOC
		  ,NULL
		  ,COD_BANCO
		  ,IC.COD_INGRESO_CHEQUE
	FROM CHEQUE C
		,INGRESO_CHEQUE IC
		,EMPRESA E
	WHERE FECHA_DOC >= @ve_fecha
	/*
	15/05/2023 MH: No se usa esta funcion ya que solo tiene que desplegar informacion de los cheques en el informe
	AND RENTAL.dbo.f_ch_saldo(COD_CHEQUE) > 0
	*/
	AND IC.COD_ESTADO_INGRESO_CHEQUE = 2
	AND COD_TIPO_DOC_PAGO in (2, 12)
	AND ES_GARANTIA = 'N'
	AND IC.COD_INGRESO_CHEQUE = C.COD_INGRESO_CHEQUE
	AND IC.COD_EMPRESA = E.COD_EMPRESA
	ORDER BY NEW_FECHA_DOC ASC

	/*declare C_TEMP INSENSITIVE  cursor for
	select COD_INGRESO_PAGO
	from INF_CHEQUE_FECHA
	
	declare
		@vc_cod_ingreso_pago		numeric
		,@vc_cod_nota_venta			numeric
		,@vl_NVs					varchar(100)

	OPEN C_TEMP
	FETCH C_TEMP INTO @vc_cod_ingreso_pago
	WHILE @@FETCH_STATUS = 0 BEGIN
		set @vl_NVs = ''
		--pagos a FA
		declare C_NV_FA INSENSITIVE  cursor for
		select f.COD_DOC	
		from ingreso_pago_factura ipf, FACTURA f
		where ipf.COD_INGRESO_PAGO = @vc_cod_ingreso_pago
		  and ipf.TIPO_DOC = 'FACTURA'
		  and f.COD_FACTURA = ipf.COD_DOC
		  and f.COD_TIPO_FACTURA = 1	--venta (desde NV)
		  
		OPEN C_NV_FA
		FETCH C_NV_FA INTO @vc_cod_nota_venta
		WHILE @@FETCH_STATUS = 0 BEGIN
			set @vl_NVs = @vl_NVs + CONVERT(varchar, @vc_cod_nota_venta) + '-'
				
			FETCH C_NV_FA INTO @vc_cod_nota_venta
		END
		CLOSE C_NV_FA
		DEALLOCATE C_NV_FA
	
		--pagos a NV
		declare C_NV INSENSITIVE  cursor for
		select n.cod_nota_venta
		from ingreso_pago_factura ipf, nota_venta n
		where ipf.COD_INGRESO_PAGO = @vc_cod_ingreso_pago
		  and ipf.TIPO_DOC = 'NOTA_VENTA'
		  and n.COD_nota_venta = ipf.COD_DOC
		  
		OPEN C_NV
		FETCH C_NV INTO @vc_cod_nota_venta
		WHILE @@FETCH_STATUS = 0 BEGIN
			set @vl_NVs = @vl_NVs + CONVERT(varchar, @vc_cod_nota_venta) + '-'

			FETCH C_NV INTO @vc_cod_nota_venta
		END
		CLOSE C_NV
		DEALLOCATE C_NV
		
		if (@vl_NVs <> '')
			set @vl_NVs = LEFT(@vl_NVs, len(@vl_NVs) - 1)	--borra el ultimo "-"
			
		update INF_CHEQUE_FECHA
		set COD_NOTA_VENTA = @vl_NVs
		where cod_ingreso_pago = @vc_cod_ingreso_pago
	
		FETCH C_TEMP INTO @vc_cod_ingreso_pago
	END
	CLOSE C_TEMP
	DEALLOCATE C_TEMP*/
END
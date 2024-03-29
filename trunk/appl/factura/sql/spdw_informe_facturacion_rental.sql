alter PROCEDURE spdw_informe_facturacion_rental(@ve_tipo_informe	varchar(80)
												,@ve_fecha			varchar(10))
AS
BEGIN
	DECLARE
		@vl_fecha_date	DATETIME = dbo.to_date(@ve_fecha),
		@vl_numero		NUMERIC(10) = 0

	DECLARE @TEMPO TABLE 	 
		    (FIELD_UNO		VARCHAR(100)
			,FIELD_DOS		VARCHAR(100)
			,FIELD_TRES		VARCHAR(100)
			,FIELD_CUATRO	VARCHAR(100)
			,FIELD_CINCO	VARCHAR(100)
			,FIELD_SEIS		VARCHAR(100)
			,FIELD_SIETE	VARCHAR(100)
			,FIELD_OCHO		VARCHAR(100)
			,FIELD_NUEVE	VARCHAR(100)
			,FIELD_DIES		VARCHAR(100))

	IF(@ve_tipo_informe = 'FACTURACION')BEGIN
		DECLARE @vl_total_neto_nc		NUMERIC(10) = 0
				,@vl_resultado			NUMERIC(10)
				,@vc_rut				VARCHAR(12)
				,@vc_cod_empresa		NUMERIC(10)
				,@vc_nom_empresa		VARCHAR(100)
				,@vc_sum_total_neto		NUMERIC(10)
		
		DECLARE C_TEMPO insensitive CURSOR FOR 
		SELECT dbo.number_format(RUT, 0, ',', '.') +'-'+DIG_VERIF 
			  ,COD_EMPRESA
			  ,NOM_EMPRESA
			  ,SUM(TOTAL_NETO)
		FROM FACTURA
		WHERE MONTH(FECHA_FACTURA) = MONTH(@vl_fecha_date)
		AND YEAR(FECHA_FACTURA) = YEAR(@vl_fecha_date)
		AND COD_ESTADO_DOC_SII = 3	-- ENVIADA A SII
		AND COD_TIPO_FACTURA = 2	-- ARRIENDO
		GROUP BY dbo.number_format(RUT, 0, ',', '.') +'-'+DIG_VERIF , COD_EMPRESA, NOM_EMPRESA
		ORDER BY NOM_EMPRESA

		OPEN C_TEMPO
		FETCH C_TEMPO INTO @vc_rut, @vc_cod_empresa, @vc_nom_empresa, @vc_sum_total_neto
		WHILE @@FETCH_STATUS = 0 BEGIN
			SET @vl_numero = @vl_numero + 1

			SELECT @vl_total_neto_nc = ISNULL(SUM(TOTAL_NETO), 0)
			FROM NOTA_CREDITO
			WHERE COD_ESTADO_DOC_SII = 3
			AND COD_DOC in (SELECT COD_FACTURA
							FROM FACTURA
							WHERE COD_ESTADO_DOC_SII = 3	-- ENVIADA A SII
							AND COD_TIPO_FACTURA = 2		-- ARRIENDO
							AND MONTH(FECHA_FACTURA) = MONTH(@vl_fecha_date)
							AND YEAR(FECHA_FACTURA) = YEAR(@vl_fecha_date)
							AND COD_EMPRESA = @vc_cod_empresa)

			SET @vl_resultado = @vc_sum_total_neto - ISNULL(@vl_total_neto_nc, 0)

			INSERT INTO @TEMPO (FIELD_UNO	,FIELD_DOS	,FIELD_TRES			,FIELD_CUATRO		,FIELD_CINCO		,FIELD_SEIS)
						VALUES (@vl_numero	,@vc_rut	,@vc_nom_empresa	,@vc_sum_total_neto	,@vl_total_neto_nc	,@vl_resultado)
		
			FETCH C_TEMPO INTO @vc_rut, @vc_cod_empresa, @vc_nom_empresa, @vc_sum_total_neto
		END
		CLOSE C_TEMPO
		DEALLOCATE C_TEMPO
	END
	IF(@ve_tipo_informe = 'FACTURACION_VENTA')BEGIN	
		
		DECLARE @vc_cod_factura			NUMERIC(10)
			   ,@vc_cliente				VARCHAR(100)
			   ,@vc_total_neto			NUMERIC(10)
			   ,@vl_nom_producto		VARCHAR(100)
			   ,@vl_sum_total_neto_nc	NUMERIC(10)
			   ,@vl_fa_total_nc			NUMERIC(10)

		DECLARE C_TEMPO insensitive CURSOR FOR
		SELECT COD_FACTURA 
			  ,CONVERT(VARCHAR, RUT)+'-'+DIG_VERIF +' / '+ NOM_EMPRESA +' / '+ CONVERT(VARCHAR, NRO_FACTURA)
			  ,TOTAL_NETO
		FROM FACTURA
		WHERE MONTH(FECHA_FACTURA) = MONTH(@vl_fecha_date)
		AND YEAR(FECHA_FACTURA) = YEAR(@vl_fecha_date)
		AND COD_ESTADO_DOC_SII = 3	-- ENVIADA A SII
		AND COD_TIPO_FACTURA = 1	-- VENTA
		ORDER BY NOM_EMPRESA ASC

		OPEN C_TEMPO
		FETCH C_TEMPO INTO @vc_cod_factura, @vc_cliente, @vc_total_neto
		WHILE @@FETCH_STATUS = 0 BEGIN
			SET @vl_numero = @vl_numero + 1

			SELECT TOP 1 @vl_nom_producto = NOM_PRODUCTO
			FROM ITEM_FACTURA
			WHERE COD_FACTURA = @vc_cod_factura
			ORDER BY COD_ITEM_FACTURA ASC

			SELECT @vl_sum_total_neto_nc = ISNULL(SUM(TOTAL_NETO), 0)
			FROM NOTA_CREDITO
			WHERE COD_DOC = @vc_cod_factura
			AND COD_ESTADO_DOC_SII = 3		-- ENVIADA A SII

			SET @vl_fa_total_nc = @vc_total_neto - @vl_sum_total_neto_nc

			INSERT INTO @TEMPO (FIELD_UNO	,FIELD_DOS		,FIELD_TRES			,FIELD_CUATRO		,FIELD_CINCO			,FIELD_SEIS)
						VALUES (@vl_numero	,@vc_cliente	,@vl_nom_producto	,@vc_total_neto		,@vl_sum_total_neto_nc	,@vl_fa_total_nc)

			FETCH C_TEMPO INTO @vc_cod_factura, @vc_cliente, @vc_total_neto
		END
		CLOSE C_TEMPO
		DEALLOCATE C_TEMPO

	END
	IF(@ve_tipo_informe = 'FACTURACION_RESUMEN')BEGIN
		DECLARE
			@vl_fact_arriendo_neto	numeric(10),
			@vl_nc_arriendo_neto	numeric(10),
			@vl_total_arriendo_neto	numeric(10),
			@vl_fact_neto			numeric(10),
			@vl_nc_neto				numeric(10),
			@vl_total_neto			numeric(10),
			@vl_oc_internas			numeric(10),
			@vl_count				numeric(10) = 1,
			@vl_mes					varchar(100),
			@vl_year				varchar(4),
			@vl_monto_total			numeric
		
		SET @vl_year = SUBSTRING(CONVERT(VARCHAR, YEAR(@vl_fecha_date)), 3, 4)
		
		WHILE(@vl_count <= 12) BEGIN
			
			SELECT @vl_mes = CASE
					WHEN @vl_count = 1 THEN 'ENERO ' + @vl_year
					WHEN @vl_count = 2 THEN 'FEBRERO ' + @vl_year
					WHEN @vl_count = 3 THEN 'MARZO ' + @vl_year
					WHEN @vl_count = 4 THEN 'ABRIL ' + @vl_year
					WHEN @vl_count = 5 THEN 'MAYO ' + @vl_year
					WHEN @vl_count = 6 THEN 'JUNIO ' + @vl_year
					WHEN @vl_count = 7 THEN 'JULIO ' + @vl_year
					WHEN @vl_count = 8 THEN 'AGOSTO ' + @vl_year
					WHEN @vl_count = 9 THEN 'SEPTIEMBRE ' + @vl_year
					WHEN @vl_count = 10 THEN 'OCTUBRE ' + @vl_year
					WHEN @vl_count = 11 THEN 'NOVIEMBRE ' + @vl_year
					WHEN @vl_count = 12 THEN 'DICIEMBRE ' + @vl_year
				END

			if(@vl_count <= MONTH(@vl_fecha_date))BEGIN
				
				SELECT @vl_fact_arriendo_neto = SUM(TOTAL_NETO)
				FROM FACTURA
				WHERE COD_ESTADO_DOC_SII = 3	-- ENVIADA A SII
				AND COD_TIPO_FACTURA = 2		-- ARRIENDO
				AND MONTH(FECHA_FACTURA) = @vl_count
				AND YEAR(FECHA_FACTURA) = YEAR(@vl_fecha_date)

				SELECT @vl_nc_arriendo_neto = ISNULL(SUM(TOTAL_NETO), 0)
				FROM NOTA_CREDITO
				WHERE COD_ESTADO_DOC_SII = 3	-- ENVIADA A SII
				AND COD_DOC in (SELECT COD_FACTURA
								FROM FACTURA
								WHERE COD_ESTADO_DOC_SII = 3	-- ENVIADA A SII
								AND COD_TIPO_FACTURA = 2		-- ARRIENDO
								AND MONTH(FECHA_FACTURA) = @vl_count
								AND YEAR(FECHA_FACTURA) = YEAR(@vl_fecha_date))

				SET @vl_total_arriendo_neto = @vl_fact_arriendo_neto - @vl_nc_arriendo_neto

				SELECT @vl_fact_neto = SUM(TOTAL_NETO)
				FROM FACTURA
				WHERE COD_ESTADO_DOC_SII = 3	-- ENVIADA A SII
				AND COD_TIPO_FACTURA = 1		-- VENTA
				AND MONTH(FECHA_FACTURA) = @vl_count
				AND YEAR(FECHA_FACTURA) = YEAR(@vl_fecha_date)

				SELECT @vl_nc_neto = ISNULL(SUM(TOTAL_NETO), 0)
				FROM NOTA_CREDITO
				WHERE COD_ESTADO_DOC_SII = 3	-- ENVIADA A SII
				AND COD_DOC in (SELECT COD_FACTURA
								FROM FACTURA
								WHERE COD_ESTADO_DOC_SII = 3	-- ENVIADA A SII
								AND COD_TIPO_FACTURA = 1		-- VENTA
								AND MONTH(FECHA_FACTURA) = @vl_count
								AND YEAR(FECHA_FACTURA) = YEAR(@vl_fecha_date))

				SET @vl_total_neto = @vl_fact_neto - @vl_nc_neto

				SET @vl_oc_internas = 0 --POR IMPLEMENTAR
				SET @vl_monto_total = (@vl_fact_arriendo_neto + @vl_fact_neto) - (@vl_nc_arriendo_neto + @vl_nc_neto)

				INSERT INTO @TEMPO (FIELD_UNO
								   ,FIELD_DOS
								   ,FIELD_TRES
								   ,FIELD_CUATRO
								   ,FIELD_CINCO
								   ,FIELD_SEIS
								   ,FIELD_SIETE
								   ,FIELD_OCHO
								   ,FIELD_NUEVE
								   ,FIELD_DIES)
							VALUES (@vl_count
								   ,@vl_mes
								   ,@vl_fact_arriendo_neto
								   ,@vl_nc_arriendo_neto
								   ,@vl_total_arriendo_neto
								   ,@vl_fact_neto
								   ,@vl_nc_neto
								   ,@vl_total_neto
								   ,@vl_oc_internas
								   ,@vl_monto_total)

			END
			ELSE BEGIN
				INSERT INTO @TEMPO (FIELD_UNO
								   ,FIELD_DOS
								   ,FIELD_TRES
								   ,FIELD_CUATRO
								   ,FIELD_CINCO
								   ,FIELD_SEIS
								   ,FIELD_SIETE
								   ,FIELD_OCHO
								   ,FIELD_NUEVE
								   ,FIELD_DIES)
							VALUES (@vl_count
								   ,@vl_mes
								   ,0
								   ,0
								   ,0
								   ,0
								   ,0
								   ,0
								   ,0
								   ,0)
			END
	
			SET @vl_count = @vl_count + 1
		END
	END
	
	select *
	from @TEMPO
END
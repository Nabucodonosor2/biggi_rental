CREATE PROCEDURE spi_ch_por_cobrar(@ve_cod_usuario numeric(10))
AS
BEGIN
	DECLARE
		@vc_cod_empresa				numeric,
		@vl_item					numeric,
		@vl_cliente					varchar(100),
		@vl_rut						varchar(50),
		@vl_ct_ch_registrado_ic		numeric(10),
		@vl_monto_ch_registrados_ic	numeric(10),
		@vl_ct_ch_cobrados_ic		numeric(10),
		@vl_monto_ch_cobrados_ic	numeric(10),
		@vl_ct_ch_cartera_ic		numeric(10),
		@vl_monto_ch_cartera_ic		numeric(10),
		@vl_ct_ch_registrado_ip		numeric(10),
		@vl_monto_ch_registrados_ip	numeric(10),
		@vl_ct_ch_cobrados_ip		numeric(10),
		@vl_monto_ch_cobrados_ip	numeric(10),
		@vl_ct_ch_cartera_ip		numeric(10),
		@vl_monto_ch_cartera_ip		numeric(10),
		@vl_monto_fact_x_cobrar		numeric(10)
	
	DELETE INF_CH_POR_COBRAR
	WHERE COD_USUARIO = @ve_cod_usuario

	DECLARE C_ITEM insensitive CURSOR FOR 
	SELECT DISTINCT CASE
			WHEN IC.COD_EMPRESA IS NULL THEN IP.COD_EMPRESA
			ELSE IC.COD_EMPRESA
		  END COD_EMPRESA	
	FROM CHEQUE C LEFT OUTER JOIN INGRESO_CHEQUE IC ON C.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE
				  LEFT OUTER JOIN INGRESO_PAGO IP ON C.COD_INGRESO_PAGO_ORIGEN = IP.COD_INGRESO_PAGO
	ORDER BY COD_EMPRESA			  
	
	set @vl_item = 1
	
	OPEN C_ITEM
	FETCH C_ITEM INTO @vc_cod_empresa	
	WHILE @@FETCH_STATUS = 0 BEGIN
		
		SELECT @vl_cliente = NOM_EMPRESA
			  ,@vl_rut = (dbo.number_format(RUT,0,',', '.')+'-'+DIG_VERIF)
		FROM EMPRESA
		WHERE COD_EMPRESA = @vc_cod_empresa	  
			  		
		----- ingreso cheque
		SELECT @vl_ct_ch_registrado_ic = COUNT(*) 
			 ,@vl_monto_ch_registrados_ic = ISNULL(SUM(MONTO_DOC), 0) 
		FROM CHEQUE C
			,INGRESO_CHEQUE IC
			,EMPRESA E
		WHERE IC.COD_EMPRESA = @vc_cod_empresa
		AND IC.COD_EMPRESA = E.COD_EMPRESA
		AND C.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE
		
		SELECT @vl_ct_ch_cobrados_ic = COUNT(*)
			  ,@vl_monto_ch_cobrados_ic = ISNULL(SUM(MONTO_DOC), 0)
		FROM CHEQUE C
			,INGRESO_CHEQUE IC
			,EMPRESA E
		WHERE IC.COD_EMPRESA = @vc_cod_empresa
		AND C.DEPOSITADO = 'S'
		AND C.LIBERADO = 'S'
		AND IC.COD_EMPRESA = E.COD_EMPRESA
		AND C.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE
		
		SELECT @vl_ct_ch_cartera_ic = COUNT(*)
			  ,@vl_monto_ch_cartera_ic = ISNULL(SUM(MONTO_DOC), 0)
		FROM CHEQUE C
			,INGRESO_CHEQUE IC
			,EMPRESA E
		WHERE IC.COD_EMPRESA = @vc_cod_empresa
		AND (C.DEPOSITADO <> 'S' OR C.LIBERADO <> 'S')
		AND IC.COD_EMPRESA = E.COD_EMPRESA
		AND C.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE
		
		----- ingreso pago
		SELECT @vl_ct_ch_registrado_ip = COUNT(*) 
			 ,@vl_monto_ch_registrados_ip = ISNULL(SUM(MONTO_DOC), 0) 
		FROM CHEQUE C
			,INGRESO_PAGO IP
			,EMPRESA E
		WHERE IP.COD_EMPRESA = @vc_cod_empresa
		AND IP.COD_EMPRESA = E.COD_EMPRESA
		AND C.COD_INGRESO_PAGO_ORIGEN = IP.COD_INGRESO_PAGO
		
		SELECT @vl_ct_ch_cobrados_ip = COUNT(*)
			  ,@vl_monto_ch_cobrados_ip = ISNULL(SUM(MONTO_DOC), 0)
		FROM CHEQUE C
			,INGRESO_PAGO IP
			,EMPRESA E
		WHERE IP.COD_EMPRESA = @vc_cod_empresa
		AND C.DEPOSITADO = 'S'
		AND C.LIBERADO = 'S'
		AND IP.COD_EMPRESA = E.COD_EMPRESA
		AND C.COD_INGRESO_PAGO_ORIGEN = IP.COD_INGRESO_PAGO
		
		SELECT @vl_ct_ch_cartera_ip = COUNT(*)
			  ,@vl_monto_ch_cartera_ip = ISNULL(SUM(MONTO_DOC), 0)
		FROM CHEQUE C
			,INGRESO_PAGO IP
			,EMPRESA E
		WHERE IP.COD_EMPRESA = @vc_cod_empresa
		AND (C.DEPOSITADO <> 'S' OR C.LIBERADO <> 'S')
		AND IP.COD_EMPRESA = E.COD_EMPRESA
		AND C.COD_INGRESO_PAGO_ORIGEN = IP.COD_INGRESO_PAGO
		
		--suma de los cheques con ambos origenes
		set @vl_ct_ch_registrado_ic		= @vl_ct_ch_registrado_ic + @vl_ct_ch_registrado_ip
		set @vl_monto_ch_registrados_ic	= @vl_monto_ch_registrados_ic + @vl_monto_ch_registrados_ip
		set @vl_ct_ch_cobrados_ic		= @vl_ct_ch_cobrados_ic + @vl_ct_ch_cobrados_ip
		set @vl_monto_ch_cobrados_ic	= @vl_monto_ch_cobrados_ic + @vl_monto_ch_cobrados_ip
		set @vl_ct_ch_cartera_ic		= @vl_ct_ch_cartera_ic + @vl_ct_ch_cartera_ip
		set @vl_monto_ch_cartera_ic		= @vl_monto_ch_cartera_ic + @vl_monto_ch_cartera_ip

		SELECT @vl_monto_fact_x_cobrar = ISNULL(SUM(dbo.f_fa_saldo(COD_FACTURA)), 0)
		FROM FACTURA
		WHERE COD_EMPRESA = @vc_cod_empresa
		AND COD_ESTADO_DOC_SII = 3 --ENVIADA A SII
		
		--insert
		INSERT INTO INF_CH_POR_COBRAR(ITEM
									 ,CLIENTE
									 ,RUT
									 ,CT_CH_REGISTRADO
									 ,MONTO_CH_REGISTRADOS
									 ,CT_CH_COBRADOS
									 ,MONTO_CH_COBRADOS
									 ,CT_CH_CARTERA
									 ,MONTO_CH_CARTERA
									 ,MONTO_FACT_X_COBRAR
									 ,COD_USUARIO)
							   VALUES(@vl_item
							   		 ,@vl_cliente
							   		 ,@vl_rut
							   		 ,@vl_ct_ch_registrado_ic
							   		 ,@vl_monto_ch_registrados_ic
							   		 ,@vl_ct_ch_cobrados_ic
							   		 ,@vl_monto_ch_cobrados_ic
							   		 ,@vl_ct_ch_cartera_ic
							   		 ,@vl_monto_ch_cartera_ic
							   		 ,@vl_monto_fact_x_cobrar
							   		 ,@ve_cod_usuario)		 	
		
		set @vl_item = @vl_item + 1
		
		FETCH C_ITEM INTO @vc_cod_empresa	
	END
	CLOSE C_ITEM
	DEALLOCATE C_ITEM
END
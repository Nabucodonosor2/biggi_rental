CREATE PROCEDURE [dbo].[spx_resuelve_fa_rechazadas]
AS
BEGIN
	DECLARE @vc_cod_factura_rechazada	numeric,
			@vc_nro_nota_credito		numeric,
			@vc_cod_re_factura			numeric,
			@vc_cod_factura				numeric,
			@vc_total_neto				numeric,
			@vc_cod_empresa				numeric,
			@vl_cant_arr_fa				numeric,
			@vl_cant_arr_refa			numeric
	
	DECLARE C_CURSOR CURSOR FOR	
	SELECT COD_FACTURA_RECHAZADA
		  ,dbo.f_get_nc_from_fa(F.COD_FACTURA) NRO_NOTA_CREDITO
		  ,dbo.f_get_reFA(F.COD_FACTURA, F.TOTAL_NETO, F.COD_EMPRESA, 'COD') NRO_RE_FACTURA
		  ,F.COD_FACTURA
		  ,TOTAL_NETO
		  ,F.COD_EMPRESA
	FROM FACTURA_RECHAZADA FR LEFT OUTER JOIN USUARIO U ON U.COD_USUARIO = FR.COD_USUARIO_RESUELTA
		,FACTURA F
		,USUARIO UV1
	WHERE RESUELTA = 'N'
	AND FR.COD_FACTURA = F.COD_FACTURA
	AND UV1.COD_USUARIO = F.COD_USUARIO_VENDEDOR1
	ORDER BY COD_FACTURA_RECHAZADA DESC
	
	OPEN C_CURSOR 
	FETCH C_CURSOR INTO @vc_cod_factura_rechazada, @vc_nro_nota_credito, @vc_cod_re_factura, @vc_cod_factura, @vc_total_neto, @vc_cod_empresa
	WHILE @@FETCH_STATUS = 0
	BEGIN
		IF(@vc_nro_nota_credito IS NOT NULL AND @vc_cod_re_factura IS NOT NULL)BEGIN
			
			SELECT @vl_cant_arr_fa = COUNT(*)
			FROM FACTURA_CONTRATO
			WHERE COD_FACTURA = @vc_cod_factura

			SELECT @vl_cant_arr_refa = COUNT(*)
			FROM FACTURA_CONTRATO
			WHERE COD_FACTURA = @vc_cod_re_factura

			IF(@vl_cant_arr_fa = @vl_cant_arr_refa)BEGIN
				UPDATE FACTURA_RECHAZADA
				SET RESUELTA = 'S'
				WHERE COD_FACTURA_RECHAZADA = @vc_cod_factura_rechazada
			END
		END
		FETCH C_CURSOR INTO @vc_cod_factura_rechazada, @vc_nro_nota_credito, @vc_cod_re_factura, @vc_cod_factura, @vc_total_neto, @vc_cod_empresa
	END
	CLOSE C_CURSOR
	DEALLOCATE C_CURSOR
END
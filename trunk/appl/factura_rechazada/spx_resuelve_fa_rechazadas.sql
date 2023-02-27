alter PROCEDURE [dbo].[spx_resuelve_fa_rechazadas]
AS
BEGIN
	DECLARE @vc_cod_factura_rechazada	numeric,
			@vc_nro_nota_credito		numeric,
			@vc_cod_factura				numeric,
			@vc_total_neto				numeric,
			@vc_cod_empresa				numeric,
			@vc_re_factura				numeric,
			@vc_cod_factura2			numeric,
			@vc_nro_factura				numeric,
			@vl_cant_arr_fa				numeric,
			@vl_cant_arr_refa			numeric,
			@vl_count					numeric
			
	
	DECLARE C_CURSOR CURSOR FOR	
	SELECT COD_FACTURA_RECHAZADA
		  ,dbo.f_get_nc_from_fa(F.COD_FACTURA) NRO_NOTA_CREDITO
		  ,F.COD_FACTURA
		  ,TOTAL_NETO
		  ,F.COD_EMPRESA
		  ,NRO_RE_FACTURA
	FROM FACTURA_RECHAZADA FR LEFT OUTER JOIN USUARIO U ON U.COD_USUARIO = FR.COD_USUARIO_RESUELTA
		,FACTURA F
		,USUARIO UV1
	WHERE RESUELTA = 'N'
	AND FR.COD_FACTURA = F.COD_FACTURA
	AND UV1.COD_USUARIO = F.COD_USUARIO_VENDEDOR1
	ORDER BY COD_FACTURA_RECHAZADA DESC
	
	OPEN C_CURSOR 
	FETCH C_CURSOR INTO @vc_cod_factura_rechazada, @vc_nro_nota_credito, @vc_cod_factura, @vc_total_neto, @vc_cod_empresa, @vc_re_factura
	WHILE @@FETCH_STATUS = 0
	BEGIN

		IF(@vc_re_factura IS NULL)BEGIN
			SELECT @vl_count = COUNT(*)
			FROM FACTURA_CONTRATO FC
				,FACTURA F
			WHERE F.COD_FACTURA > @vc_cod_factura
			AND COD_EMPRESA = @vc_cod_empresa
			AND TOTAL_NETO = @vc_total_neto
			and  COD_ESTADO_DOC_SII in (2, 3)
			AND F.COD_FACTURA = FC.COD_FACTURA

			IF(@vl_count = 1)BEGIN
				
				SELECT TOP 1 @vc_re_factura = F.NRO_FACTURA
				FROM FACTURA_CONTRATO FC
					,FACTURA F
				WHERE F.COD_FACTURA > @vc_cod_factura
				AND COD_EMPRESA = @vc_cod_empresa
				AND TOTAL_NETO = @vc_total_neto
				and  COD_ESTADO_DOC_SII in (2, 3)
				AND F.COD_FACTURA = FC.COD_FACTURA
				ORDER BY FECHA_FACTURA ASC

				UPDATE FACTURA_RECHAZADA
				SET NRO_RE_FACTURA = @vc_re_factura
				WHERE COD_FACTURA = @vc_cod_factura

			END
			ELSE IF(@vl_count > 1)BEGIN
				DECLARE C_CURSOR2 CURSOR FOR
				SELECT F.COD_FACTURA
					  ,F.NRO_FACTURA
				FROM FACTURA_CONTRATO FC
					,FACTURA F
				WHERE F.COD_FACTURA > @vc_cod_factura
				AND COD_EMPRESA = @vc_cod_empresa
				AND TOTAL_NETO = @vc_total_neto
				and  COD_ESTADO_DOC_SII in (2, 3)
				AND F.COD_FACTURA = FC.COD_FACTURA
				ORDER BY FECHA_FACTURA ASC

				OPEN C_CURSOR2 
				FETCH C_CURSOR2 INTO @vc_cod_factura2, @vc_nro_factura
				WHILE @@FETCH_STATUS = 0
				BEGIN

					SELECT @vl_cant_arr_fa = COUNT(*)
					FROM FACTURA_CONTRATO
					WHERE COD_FACTURA = @vc_cod_factura

					SELECT @vl_cant_arr_refa = COUNT(*)
					FROM FACTURA_CONTRATO
					WHERE COD_FACTURA = @vc_cod_factura2

					IF(@vl_cant_arr_fa = @vl_cant_arr_refa)BEGIN
						set @vc_re_factura = @vc_cod_factura2

						UPDATE FACTURA_RECHAZADA
						SET NRO_RE_FACTURA = @vc_nro_factura
						WHERE COD_FACTURA = @vc_cod_factura

						break
					END

					FETCH C_CURSOR2 INTO @vc_cod_factura2, @vc_nro_factura

				END
				CLOSE C_CURSOR2
				DEALLOCATE C_CURSOR2

			END
		END

		IF(@vc_nro_nota_credito IS NOT NULL AND @vc_re_factura IS NOT NULL)BEGIN
			UPDATE FACTURA_RECHAZADA
			SET RESUELTA = 'S'
			WHERE COD_FACTURA_RECHAZADA = @vc_cod_factura_rechazada

		END
		FETCH C_CURSOR INTO @vc_cod_factura_rechazada, @vc_nro_nota_credito, @vc_cod_factura, @vc_total_neto, @vc_cod_empresa, @vc_re_factura
	END
	CLOSE C_CURSOR
	DEALLOCATE C_CURSOR
END
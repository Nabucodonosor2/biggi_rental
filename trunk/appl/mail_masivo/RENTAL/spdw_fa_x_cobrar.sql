alter PROCEDURE [dbo].[spdw_fa_x_cobrar](@ve_operacion		varchar(100))
/*
exec spdw_fa_x_cobrar 'RESUMEN'
exec spdw_fa_x_cobrar 'OTROS_ANTIGUAS'
exec spdw_fa_x_cobrar 'OTROS_MONTO_ALTO'
exec spdw_fa_x_cobrar 'OTROS_DETALLE'
exec spdw_fa_x_cobrar 'ARRIENDO_DETALLE'
exec spdw_fa_x_cobrar 'SERVINDUS_ANTIGUAS'
exec spdw_fa_x_cobrar 'SERVINDUS_MONTO_ALTO'
exec spdw_fa_x_cobrar 'SERVINDUS_DETALLE'
*/
AS
BEGIN
	declare
			@vl_mas_90			numeric				
			,@vl_mas_60			numeric			
			,@vl_mas_30			numeric			
			,@vl_menos_30		numeric
			,@vl_fecha_30		datetime
			,@vl_fecha_60		datetime
			,@vl_fecha_90		datetime
			,@vc_cod_empresa	numeric

		set @vl_fecha_30 = DATEADD(day, -30, getdate())
		set @vl_fecha_60 = DATEADD(day, -60, getdate())
		set @vl_fecha_90 = DATEADD(day, -90, getdate())
	
		DECLARE @TEMP_RELACIONADA TABLE     
		(COD_EMPRESA					numeric
		,NOM_EMPRESA					varchar(100)
		,MAS_90_TOTAL					numeric
		,MAS_60_TOTAL					numeric
		,MAS_30_TOTAL					numeric
		,MENOS_30_TOTAL					numeric
		,TOTAL							numeric)
	
	if (@ve_operacion = 'RESUMEN') begin
		INSERT INTO @TEMP_RELACIONADA
		   (COD_EMPRESA						
			,NOM_EMPRESA					
			,MAS_90_TOTAL							
			,MAS_60_TOTAL							
			,MAS_30_TOTAL							
			,MENOS_30_TOTAL						
			,TOTAL							
			)
		SELECT COD_EMPRESA						
			,NOM_EMPRESA					
			,0		--MAS_90							
			,0		--MAS_60							
			,0		--MAS_30							
			,0		--MENOS_30						
			,0		--TOTAL							
		from EMPRESA
		where COD_EMPRESA in (SELECT DISTINCT (COD_EMPRESA)
							  FROM FACTURA
							  WHERE TIPO_DOC = 'ARRIENDO'
							  AND dbo.f_fa_saldo(COD_FACTURA) > 0)

		DECLARE C_TEMPO CURSOR FOR  
		SELECT COD_EMPRESA
		from @TEMP_RELACIONADA

		
		OPEN C_TEMPO
		FETCH C_TEMPO INTO @vc_cod_empresa
		WHILE @@FETCH_STATUS = 0 BEGIN	
			SELECT @vl_mas_90 = isnull(sum(dbo.f_fa_saldo(F.COD_FACTURA)), 0)
			FROM	FACTURA F
			WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
			and     F.COD_EMPRESA = @vc_cod_empresa
			AND		F.COD_ESTADO_DOC_SII in (2,3)	
			and		F.FECHA_FACTURA <= @vl_fecha_90
			
			SELECT @vl_mas_60 = isnull(sum(dbo.f_fa_saldo(F.COD_FACTURA)), 0)
			FROM	FACTURA F
			WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
			and     F.COD_EMPRESA = @vc_cod_empresa
			AND		F.COD_ESTADO_DOC_SII in (2,3)	
			and		F.FECHA_FACTURA <= @vl_fecha_60
			and		F.FECHA_FACTURA > @vl_fecha_90
			
			SELECT @vl_mas_30 = isnull(sum(dbo.f_fa_saldo(F.COD_FACTURA)), 0)
			FROM	FACTURA F
			WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
			and     F.COD_EMPRESA = @vc_cod_empresa
			AND		F.COD_ESTADO_DOC_SII in (2,3)	
			and		F.FECHA_FACTURA <= @vl_fecha_30
			and		F.FECHA_FACTURA > @vl_fecha_60
			
			SELECT @vl_menos_30 = isnull(sum(dbo.f_fa_saldo(F.COD_FACTURA)), 0)
			FROM	FACTURA F
			WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
			and     F.COD_EMPRESA = @vc_cod_empresa
			AND		F.COD_ESTADO_DOC_SII in (2,3)	
			and		F.FECHA_FACTURA > @vl_fecha_30
			
			update @TEMP_RELACIONADA
			set MAS_90_TOTAL		= @vl_mas_90
				,MAS_60_TOTAL		= @vl_mas_60
				,MAS_30_TOTAL		= @vl_mas_30	
				,MENOS_30_TOTAL	= @vl_menos_30				
			where COD_EMPRESA = @vc_cod_empresa
			
			FETCH C_TEMPO INTO @vc_cod_empresa
		END
		CLOSE C_TEMPO
		DEALLOCATE C_TEMPO
		
		UPDATE @TEMP_RELACIONADA
		SET TOTAL = MAS_90_TOTAL + MAS_60_TOTAL	+ MAS_30_TOTAL + MENOS_30_TOTAL

		SELECT * FROM @TEMP_RELACIONADA	
		ORDER BY TOTAL DESC
	end
	else if(@ve_operacion = 'RESUMEN_OTRO')begin
		-----------------------
		-- OTROS
		SELECT @vl_mas_90 = isnull(sum(dbo.f_fa_saldo(F.COD_FACTURA)), 0)
		FROM	FACTURA F
		WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
		and		F.COD_EMPRESA not in (SELECT DISTINCT (COD_EMPRESA)
									  FROM FACTURA
									  WHERE TIPO_DOC = 'ARRIENDO'
									  AND dbo.f_fa_saldo(COD_FACTURA) > 0)
		AND		F.COD_ESTADO_DOC_SII in (2,3)	
		and		F.FECHA_FACTURA <= @vl_fecha_90
		
		SELECT @vl_mas_60 = isnull(sum(dbo.f_fa_saldo(F.COD_FACTURA)), 0)
		FROM	FACTURA F
		WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
		and		F.COD_EMPRESA not in (SELECT DISTINCT (COD_EMPRESA)
									  FROM FACTURA
									  WHERE TIPO_DOC = 'ARRIENDO'
									  AND dbo.f_fa_saldo(COD_FACTURA) > 0)
		AND		F.COD_ESTADO_DOC_SII in (2,3)	
		and		F.FECHA_FACTURA <= @vl_fecha_60
		and		F.FECHA_FACTURA > @vl_fecha_90
		
		SELECT @vl_mas_30 = isnull(sum(dbo.f_fa_saldo(F.COD_FACTURA)), 0)
		FROM	FACTURA F
		WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
		and		F.COD_EMPRESA not in (SELECT DISTINCT (COD_EMPRESA)
									  FROM FACTURA
									  WHERE TIPO_DOC = 'ARRIENDO'
									  AND dbo.f_fa_saldo(COD_FACTURA) > 0)
		AND		F.COD_ESTADO_DOC_SII in (2,3)	
		and		F.FECHA_FACTURA <= @vl_fecha_30
		and		F.FECHA_FACTURA > @vl_fecha_60
		
		SELECT @vl_menos_30 = isnull(sum(dbo.f_fa_saldo(F.COD_FACTURA)), 0)
		FROM	FACTURA F
		WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
		and		F.COD_EMPRESA not in (SELECT DISTINCT (COD_EMPRESA)
									  FROM FACTURA
									  WHERE TIPO_DOC = 'ARRIENDO'
									  AND dbo.f_fa_saldo(COD_FACTURA) > 0)
		AND		F.COD_ESTADO_DOC_SII in (2,3)	
		and		F.FECHA_FACTURA > @vl_fecha_30

		insert into @TEMP_RELACIONADA
		   (COD_EMPRESA						
			,NOM_EMPRESA					
			,MAS_90_TOTAL							
			,MAS_60_TOTAL							
			,MAS_30_TOTAL							
			,MENOS_30_TOTAL						
			,TOTAL							
			)
		values 
			(-1					--COD_EMPRESA						
			,'OTROS'	--NOM_EMPRESA					
			,@vl_mas_90			--MAS_90							
			,@vl_mas_60			--MAS_60							
			,@vl_mas_30			--MAS_30							
			,@vl_menos_30		--MENOS_30						
			,0		--TOTAL							
			)
		-----------------------------

		UPDATE @TEMP_RELACIONADA
		SET TOTAL = MAS_90_TOTAL + MAS_60_TOTAL	+ MAS_30_TOTAL + MENOS_30_TOTAL
		
		SELECT * FROM @TEMP_RELACIONADA	
		ORDER BY TOTAL
	END
	ELSE IF (@ve_operacion = 'OTROS_DETALLE') BEGIN
		DECLARE
			@vl_por_cobrar_otros			NUMERIC
			,@vc_monto						NUMERIC
			,@vc_nom_empresa				VARCHAR(100)
			,@vl_nom_empresa_old			VARCHAR(100)
			,@vl_cod_empresa_old			NUMERIC
			,@vl_total_empresa				NUMERIC
			
		DECLARE @TEMP_DETALLE TABLE     
	    (NRO_FACTURA				NUMERIC
		,FECHA_FACTURA				VARCHAR(100)
		,COD_EMPRESA				NUMERIC
		,NOM_EMPRESA				VARCHAR(100)
		,MONTO						NUMERIC
		,PORC						NUMERIC
		,NOM_EMPRESA_COPIA			VARCHAR(100))	
	
		SELECT @vl_por_cobrar_otros = ISNULL(SUM(dbo.f_fa_saldo(F.COD_FACTURA)), 0)
		FROM	FACTURA F
		WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
		AND		F.COD_EMPRESA not in (SELECT DISTINCT (COD_EMPRESA)
									  FROM FACTURA
									  WHERE TIPO_DOC = 'ARRIENDO'
									  AND dbo.f_fa_saldo(COD_FACTURA) > 0)
		AND		F.COD_ESTADO_DOC_SII in (2,3)	
	
		INSERT INTO @TEMP_DETALLE 
		   (NRO_FACTURA					
			,FECHA_FACTURA		
			,COD_EMPRESA		
			,NOM_EMPRESA				
			,MONTO						
			,PORC						
			,NOM_EMPRESA_COPIA)
		SELECT F.NRO_FACTURA
				,dbo.f_format_date(F.FECHA_FACTURA, 1) FECHA_FACTURA
				,F.COD_EMPRESA
				,F.NOM_EMPRESA
				,dbo.f_fa_saldo(F.COD_FACTURA) MONTO
				,dbo.f_fa_saldo(F.COD_FACTURA) * 100/@vl_por_cobrar_otros PORC
				,F.NOM_EMPRESA
		FROM	FACTURA F
		WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
		and		F.COD_EMPRESA not in (SELECT DISTINCT (COD_EMPRESA)
									  FROM FACTURA
									  WHERE TIPO_DOC = 'ARRIENDO'
									  AND dbo.f_fa_saldo(COD_FACTURA) > 0)
		AND		F.COD_ESTADO_DOC_SII in (2,3)	

		DECLARE C_TEMPO CURSOR FOR  
		SELECT COD_EMPRESA, MONTO, NOM_EMPRESA
		FROM @TEMP_DETALLE
		ORDER BY COD_EMPRESA
		
		set @vl_cod_empresa_old = 0
		set @vl_nom_empresa_old = ''
		set @vl_total_empresa = 0
		
		OPEN C_TEMPO
		FETCH C_TEMPO INTO @vc_cod_empresa, @vc_monto, @vc_nom_empresa
		WHILE @@FETCH_STATUS = 0 BEGIN	
			IF (@vl_cod_empresa_old <> @vc_cod_empresa) BEGIN
				IF (@vl_cod_empresa_old <> 0) BEGIN
					INSERT INTO @TEMP_DETALLE 
					   (NRO_FACTURA					
						,FECHA_FACTURA				
						,COD_EMPRESA				
						,NOM_EMPRESA				
						,MONTO						
						,PORC						
						,NOM_EMPRESA_COPIA)
					values 
					   (null											--NRO_FACTURA				
						,null											--FECHA_FACTURA	
						,@vl_cod_empresa_old							--COD_EMPRESA			
						,'SUBTOTAL'										--NOM_EMPRESA				
						,@vl_total_empresa								--MONTO						
						,@vl_total_empresa * 100/@vl_por_cobrar_otros	--PORC		
						,@vl_nom_empresa_old)							--NOM_EMPRESA_COPIA
				end
				
				set @vl_cod_empresa_old = @vc_cod_empresa
				set @vl_nom_empresa_old = @vc_nom_empresa
				set @vl_total_empresa = 0
			end
			set @vl_total_empresa = @vl_total_empresa + @vc_monto
			
			FETCH C_TEMPO INTO @vc_cod_empresa, @vc_monto, @vc_nom_empresa
		END
		CLOSE C_TEMPO
		DEALLOCATE C_TEMPO

		if (@vl_cod_empresa_old <> 0) begin
			insert into @TEMP_DETALLE 
			   (NRO_FACTURA					
				,FECHA_FACTURA				
				,COD_EMPRESA				
				,NOM_EMPRESA				
				,MONTO						
				,PORC	
				,NOM_EMPRESA_COPIA)
			values 
			   (null											--NRO_FACTURA				
				,null											--FECHA_FACTURA				
				,@vl_cod_empresa_old							--COD_EMPRESA			
				,'SUBTOTAL'										--NOM_EMPRESA				
				,@vl_total_empresa								--MONTO						
				,@vl_total_empresa * 100/@vl_por_cobrar_otros	--PORC		
				,@vl_nom_empresa_old)
		end
		
		SELECT * FROM @TEMP_DETALLE
		ORDER BY NOM_EMPRESA_COPIA, ISNULL(FECHA_FACTURA, dbo.f_makedate(31,12,9999))
	end
END
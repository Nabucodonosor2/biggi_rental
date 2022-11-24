--------------------- spi_resumen_venta -------------------
alter PROCEDURE spi_resumen_venta(@ve_ano1			numeric
									,@ve_ano2		numeric
									,@ve_mes_desde	numeric
									,@ve_mes_hasta	numeric)
AS
BEGIN
	declare @TEMP TABLE     
		(COD_EMPRESA		numeric
		,NOM_EMPRESA		varchar(100)
		,MONTO_ANO1			NUMERIC
		,MONTO_ANO2			NUMERIC
		)

	--empresas a resumir	
	insert into @TEMP
		(COD_EMPRESA		
		,NOM_EMPRESA		
		,MONTO_ANO1			
		,MONTO_ANO2			
		)
	select r.COD_EMPRESA
			,e.NOM_EMPRESA
			,0
			,0
	from RESUMEN_VENTA_EMPRESA r, EMPRESA e
	where e.COD_EMPRESA = r.COD_EMPRESA
	
	--OTRAS empresas a resumir	
	insert into @TEMP
		(COD_EMPRESA		
		,NOM_EMPRESA		
		,MONTO_ANO1			
		,MONTO_ANO2			
		)
	select -1
			,'OTROS'
			,0
			,0

	--NOTA CREDITO
	insert into @TEMP
		(COD_EMPRESA		
		,NOM_EMPRESA		
		,MONTO_ANO1			
		,MONTO_ANO2			
		)
	select -2
			,'(NOTA CREDITO)'
			,0
			,0


	declare C_TEMP cursor for
		select	COD_EMPRESA
		FROM	@TEMP
		where   COD_EMPRESA > 0
		
	declare
		@vc_cod_empresa		numeric
		,@vl_fecha_desde1	datetime
		,@vl_fecha_hasta1	datetime
		,@vl_fecha_desde2	datetime
		,@vl_fecha_hasta2	datetime
		,@vl_total_neto1	numeric
		,@vl_total_neto2	numeric
		
	set @vl_fecha_desde1 = dbo.f_makedate(1, @ve_mes_desde, @ve_ano1)
	set @vl_fecha_hasta1 = dateadd(day, 31, dbo.f_makedate(1, @ve_mes_hasta, @ve_ano1))
	set @vl_fecha_hasta1 = dateadd(second, 3600*24 - 1, DATEADD(day, -day(@vl_fecha_hasta1), @vl_fecha_hasta1))
	
	set @vl_fecha_desde2 = dbo.f_makedate(1, @ve_mes_desde, @ve_ano2)
	set @vl_fecha_hasta2 = dateadd(day, 31, dbo.f_makedate(1, @ve_mes_hasta, @ve_ano2))
	set @vl_fecha_hasta2 = dateadd(second, 3600*24 - 1, DATEADD(day, -day(@vl_fecha_hasta2), @vl_fecha_hasta2))
	
	OPEN C_TEMP
	FETCH C_TEMP INTO   @vc_cod_empresa
	WHILE @@FETCH_STATUS = 0 BEGIN
		select  @vl_total_neto1 = isnull(SUM(f.TOTAL_NETO), 0)
		from FACTURA f
		where f.COD_EMPRESA = @vc_cod_empresa
		  and f.COD_ESTADO_DOC_SII in (2,3)
		  and f.FECHA_FACTURA between @vl_fecha_desde1 and @vl_fecha_hasta1
		
		select  @vl_total_neto2 = isnull(SUM(f.TOTAL_NETO), 0)
		from FACTURA f
		where f.COD_EMPRESA = @vc_cod_empresa
		  and f.COD_ESTADO_DOC_SII in (2,3)
		  and f.FECHA_FACTURA between @vl_fecha_desde2 and @vl_fecha_hasta2
		
		update @TEMP
		set MONTO_ANO1 = @vl_total_neto1
			,MONTO_ANO2 = @vl_total_neto2
		where COD_EMPRESA = @vc_cod_empresa
		
		FETCH C_TEMP INTO   @vc_cod_empresa
	END
	CLOSE C_TEMP
	DEALLOCATE C_TEMP
	
	--facturacion OTROS
	select  @vl_total_neto1 = isnull(SUM(f.TOTAL_NETO), 0)
	from FACTURA f
	where f.COD_EMPRESA not in (select COD_EMPRESA from RESUMEN_VENTA_EMPRESA)
	  and f.COD_ESTADO_DOC_SII in (2,3)
	  and f.FECHA_FACTURA between @vl_fecha_desde1 and @vl_fecha_hasta1
		
	select  @vl_total_neto2 = isnull(SUM(f.TOTAL_NETO), 0)
	from FACTURA f
	where f.COD_EMPRESA not in (select COD_EMPRESA from RESUMEN_VENTA_EMPRESA)
	  and f.COD_ESTADO_DOC_SII in (2,3)
	  and f.FECHA_FACTURA between @vl_fecha_desde2 and @vl_fecha_hasta2
		
	update @TEMP
	set MONTO_ANO1 = @vl_total_neto1
		,MONTO_ANO2 = @vl_total_neto2
	where COD_EMPRESA = -1
	
	--NC
	select  @vl_total_neto1 = isnull(SUM(n.TOTAL_NETO), 0)
	from NOTA_CREDITO n
	where n.COD_ESTADO_DOC_SII in (2,3)
	  and n.FECHA_NOTA_CREDITO between @vl_fecha_desde1 and @vl_fecha_hasta1
		
	select  @vl_total_neto2 = isnull(SUM(n.TOTAL_NETO), 0)
	from NOTA_CREDITO n
	where n.COD_ESTADO_DOC_SII in (2,3)
	  and n.FECHA_NOTA_CREDITO between @vl_fecha_desde2 and @vl_fecha_hasta2
		
	update @TEMP
	set MONTO_ANO1 = -@vl_total_neto1
		,MONTO_ANO2 = -@vl_total_neto2
	where COD_EMPRESA = -2

	DELETE inf_resumen_venta

	--resultado final
	insert into inf_resumen_venta
	select COD_EMPRESA
		,NOM_EMPRESA		
		,MONTO_ANO1			
		,MONTO_ANO2		
	from @TEMP
	order by case COD_EMPRESA 
				when -1 then 'ZZ1'
				when -2 then 'ZZ2'
				else NOM_EMPRESA
	END
END

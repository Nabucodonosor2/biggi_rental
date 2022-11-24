--------------------  f_arr_esta_facturado  ----------------
ALTER FUNCTION [dbo].[f_arr_esta_facturado](@ve_cod_arriendo	numeric
									,@ve_fecha			datetime)
RETURNS numeric
AS
/*
retorna 1 si el arriendo ya esta en alguna factura para el mes y año de la ve_fecha
0 en otro caso
*/
BEGIN
declare
	@vl_count				numeric,
	@vl_cod_factura			numeric,
	@vl_fa_total_con_iva	numeric,
	@vl_nc_total_con_iva	numeric,
	@vl_nc_count			numeric

	select @vl_count = count(*)
	from factura f, item_factura i
	where month(isnull(f.fecha_factura, f.fecha_registro)) = month(@ve_fecha)
	  and year(isnull(f.fecha_factura, f.fecha_registro)) = year(@ve_fecha)
	  and f.cod_estado_doc_sii in (1, 2,3)
	  and f.cod_tipo_factura = 2	-- arriendo
	  and i.cod_factura = f.cod_factura
	  and i.TIPO_DOC = 'ARRIENDO'
	  and i.cod_item_doc = @ve_cod_arriendo

	if (@vl_count > 0)BEGIN
		select top 1 @vl_cod_factura = F.COD_FACTURA
				,@vl_fa_total_con_iva = TOTAL_CON_IVA
		from factura f, item_factura i
		where month(isnull(f.fecha_factura, f.fecha_registro)) = month(@ve_fecha)
		  and year(isnull(f.fecha_factura, f.fecha_registro)) = year(@ve_fecha)
		  and f.cod_estado_doc_sii in (1, 2,3)
		  and f.cod_tipo_factura = 2	-- arriendo
		  and i.cod_factura = f.cod_factura
		  and i.TIPO_DOC = 'ARRIENDO'
		  and i.cod_item_doc = @ve_cod_arriendo
		ORDER BY F.COD_FACTURA DESC
		
		select @vl_nc_count = COUNT(*)
		from NOTA_CREDITO
		WHERE COD_ESTADO_DOC_SII = 3 -- ENVIADA A SII
		and month(isnull(FECHA_NOTA_CREDITO, FECHA_REGISTRO)) = month(@ve_fecha)
		and year(isnull(FECHA_NOTA_CREDITO, FECHA_REGISTRO)) = year(@ve_fecha)
		AND COD_DOC = @vl_cod_factura
		
		if(@vl_nc_count > 0)BEGIN
			select @vl_nc_total_con_iva = TOTAL_CON_IVA
			from NOTA_CREDITO
			WHERE COD_ESTADO_DOC_SII = 3 -- ENVIADA A SII
			and month(isnull(FECHA_NOTA_CREDITO, FECHA_REGISTRO)) = month(@ve_fecha)
			and year(isnull(FECHA_NOTA_CREDITO, FECHA_REGISTRO)) = year(@ve_fecha)
			AND COD_DOC = @vl_cod_factura
			
			if(@vl_nc_total_con_iva = @vl_fa_total_con_iva)
				return 0
				
		END 
		
		return 1
	END
		
	return 0
END
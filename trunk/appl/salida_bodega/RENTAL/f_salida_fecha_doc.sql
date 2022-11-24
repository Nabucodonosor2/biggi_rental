create FUNCTION f_salida_fecha_doc(@ve_cod_salida_bodega numeric)
RETURNS datetime
AS
BEGIN
	declare
		@vl_tipo_doc		varchar(100)
		,@vl_fecha_doc		datetime

	set @vl_fecha_doc = null

	select @vl_tipo_doc	= tipo_doc		
	from salida_bodega 
	where cod_salida_bodega = @ve_cod_salida_bodega
	
	if (@vl_tipo_doc = 'GUIA_DESPACHO') begin
		select @vl_fecha_doc = gd.fecha_guia_despacho
		from salida_bodega s, guia_despacho gd
		where s.cod_salida_bodega = @ve_cod_salida_bodega
		and gd.cod_guia_despacho = s.cod_doc
	end
	else if (@vl_tipo_doc = 'GUIA_RECEPCION') begin
		select @vl_fecha_doc = gr.fecha_guia_recepcion
		from salida_bodega s, guia_recepcion gr
		where s.cod_salida_bodega = @ve_cod_salida_bodega
		and gr.cod_guia_recepcion = s.cod_doc
	end
	
	return @vl_fecha_doc 
END


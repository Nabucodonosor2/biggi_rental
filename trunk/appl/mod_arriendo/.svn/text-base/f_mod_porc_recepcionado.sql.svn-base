------------------ f_mod_porc_recepcionado ----------------
create function f_mod_porc_recepcionado(@ve_cod_mod_arriendo	numeric)
RETURNS numeric(10,2)
AS
-- Retorna el porcentaje recepcionado
BEGIN
	declare 
		@vl_porc					numeric(10,2)
		,@vl_tipo_mod_arriendo		varchar(100)
		,@vl_total					numeric
		,@vl_recepcionado			numeric
		
	select @vl_tipo_mod_arriendo = tipo_mod_arriendo
	from mod_arriendo
	where cod_mod_arriendo = @ve_cod_mod_arriendo
	
	if (@vl_tipo_mod_arriendo <> 'ELIMINAR')
		return 0
		
	select @vl_total = isnull(sum(cantidad * precio_venta), 0)
	from ITEM_MOD_ARRIENDO
	where cod_mod_arriendo = @ve_cod_mod_arriendo
		
	select @vl_recepcionado = isnull(sum(ima.cantidad * ima.precio), 0)
	from guia_recepcion gr, item_guia_recepcion i, item_mod_arriendo ima
	where gr.cod_estado_guia_recepcion = 2	--confirmada
	  and gr.tipo_doc = 'MOD_ARRIENDO'
	  and gr.cod_doc = @ve_cod_mod_arriendo
	  and i.cod_guia_recepcion = gr.cod_guia_recepcion 
	  and i.tipo_doc = 'ITEM_MOD_ARRIENDO'
	  and ima.cod_item_mod_arriendo = i.cod_item_doc

	if (@vl_total=0)
		set @vl_porc = 0
	else
		set @vl_porc = (@vl_recepcionado * 100.00)/ @vl_total
				
	return @vl_porc
END

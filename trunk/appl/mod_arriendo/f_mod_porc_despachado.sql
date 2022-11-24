------------------ f_mod_porc_despachado ----------------
alter function f_mod_porc_despachado(@ve_cod_mod_arriendo	numeric)
RETURNS numeric(10,2)
AS
-- Retorna el porcentaje despachado
BEGIN
	declare 
		@vl_porc					numeric(10,2)
		,@vl_tipo_mod_arriendo		varchar(100)
		,@vl_total					numeric
		,@vl_despachado				numeric
		
	select @vl_tipo_mod_arriendo = tipo_mod_arriendo
	from mod_arriendo
	where cod_mod_arriendo = @ve_cod_mod_arriendo
	
	if (@vl_tipo_mod_arriendo <> 'AGREGAR')
		return 0
		
	select @vl_total = isnull(sum(cantidad * precio_venta), 0)
	from ITEM_MOD_ARRIENDO
	where cod_mod_arriendo = @ve_cod_mod_arriendo
		
	select @vl_despachado = isnull(sum(ima.cantidad * ima.precio), 0)
	from guia_despacho gd, item_guia_despacho igd, item_mod_arriendo ima
	where gd.cod_estado_doc_sii in (2, 3)
	  and gd.cod_tipo_guia_despacho = 5	--mod_arriendo
	  and gd.cod_doc = @ve_cod_mod_arriendo
	  and igd.cod_guia_despacho = gd.cod_guia_despacho 
	  and igd.tipo_doc = 'ITEM_MOD_ARRIENDO'
	  and ima.cod_item_mod_arriendo = igd.cod_item_doc


	if (@vl_total=0)
		set @vl_porc = 0
	else
		set @vl_porc = (@vl_despachado * 100.00)/ @vl_total
				
	return @vl_porc
END

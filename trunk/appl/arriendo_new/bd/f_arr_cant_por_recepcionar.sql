----------------------------------  f_arr_cant_por_recepcionar ------------------------------------
create FUNCTION f_arr_cant_por_recepcionar(@ve_cod_item_mod_arriendo numeric, @ve_filtro varchar(20)=NULL)
RETURNS T_CANTIDAD 
AS
BEGIN

	declare 
		@cantidad 				T_CANTIDAD
		,@cantidad_recepcionada	T_CANTIDAD
		,@res					T_CANTIDAD

	select @cantidad = cantidad
	from item_mod_arriendo 
	where cod_item_mod_arriendo = @ve_cod_item_mod_arriendo

	--total recepcionada
	select @cantidad_recepcionada = isnull(sum(cantidad), 0) 
	from item_guia_recepcion i, guia_recepcion gr
	where i.cod_item_doc = @ve_cod_item_mod_arriendo and
		  i.tipo_doc = 'ITEM_MOD_ARRIENDO' and
			i.cod_guia_recepcion = gr.cod_guia_recepcion and
		((gr.cod_estado_guia_recepcion = 2) or ( gr.cod_estado_guia_recepcion=1 and @ve_filtro = 'TODO_ESTADO'))

	if (@cantidad <= @cantidad_recepcionada)
		set @res = 0
	else
		set @res = @cantidad - @cantidad_recepcionada
		
	return @res
	
END

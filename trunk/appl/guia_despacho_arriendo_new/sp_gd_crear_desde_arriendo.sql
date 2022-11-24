-------------------------------- sp_gd_crear_desde_arriendo --------------------
ALTER PROCEDURE [dbo].[sp_gd_crear_desde_arriendo](@ve_cod_mod_arriendo numeric, @ve_cod_usuario numeric)
AS
BEGIN  
	declare 
		@K_BODEGA_RENTAL		numeric,
		@K_PARAM_MAX_IT_GD		numeric,
		@vl_cod_guia_despacho		numeric,
		@vc_cod_item_mod_arriendo	numeric,
		@vl_orden			numeric,
		@vl_valor_max_cant_it_gd	varchar(100),
		@vl_i				numeric


	set @K_BODEGA_RENTAL = 1
	set @K_PARAM_MAX_IT_GD = 28
	
	set @vl_valor_max_cant_it_gd = dbo.f_get_parametro(@K_PARAM_MAX_IT_GD)
	
	exec sp_crear_gd_arriendo @ve_cod_mod_arriendo, @ve_cod_usuario
	set @vl_cod_guia_despacho = @@identity

	declare C_ITEM cursor for 
	select i.cod_item_mod_arriendo
	from MOD_ARRIENDO m, ITEM_MOD_ARRIENDO i
	where m.COD_MOD_ARRIENDO = @ve_cod_mod_arriendo
	  and m.COD_ESTADO_MOD_ARRIENDO = 2	--confirmado
	  and i.COD_MOD_ARRIENDO = m.COD_MOD_ARRIENDO
	  and dbo.f_arr_cant_por_despachar(i.cod_item_mod_arriendo, null) > 0
				
	set @vl_orden = 10
	set @vl_i = 1
	open C_ITEM 
	fetch C_ITEM into @vc_cod_item_mod_arriendo
	while @@fetch_status = 0 begin

		if (@vl_i > @vl_valor_max_cant_it_gd)begin
			exec sp_crear_gd_arriendo @ve_cod_mod_arriendo, @ve_cod_usuario
			set @vl_cod_guia_despacho = @@identity
			set @vl_i = 1
			set @vl_orden = 10
		end

		insert into item_guia_despacho(
			cod_guia_despacho,
			orden,
			item,
			cod_producto,
			nom_producto,
			cantidad,
			precio,
			cod_item_doc,
			tipo_doc)
		select @vl_cod_guia_despacho,
			@vl_orden,
			CONVERT(varchar, floor(@vl_orden / 10)),
			i.cod_producto,
			i.nom_producto,
			dbo.f_arr_cant_por_despachar(i.cod_item_mod_arriendo, null),
			case 
				when isnull(p.PRECIO_ARRIENDO_PUBLICO, 0) > 0 then p.PRECIO_ARRIENDO_PUBLICO
				when ISNULL(i.precio_venta, 0) >0 then i.precio_venta
				else i.PRECIO
			 end,
			@vc_cod_item_mod_arriendo,
			'ITEM_MOD_ARRIENDO'
		from item_mod_arriendo i left outer join PRODUCTO p on p.cod_producto = i.COD_PRODUCTO
		where i.COD_ITEM_MOD_ARRIENDO = @vc_cod_item_mod_arriendo
		
		set @vl_orden = @vl_orden + 10
		set @vl_i = @vl_i + 1

		fetch C_ITEM into @vc_cod_item_mod_arriendo
	end
	close C_ITEM
	deallocate C_ITEM
END
GO

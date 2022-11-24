-------------------------------- sp_gd_crear_desde_arriendo --------------------
ALTER PROCEDURE [dbo].[sp_gd_crear_desde_arriendo](@ve_cod_mod_arriendo numeric, @ve_cod_usuario numeric)
AS
BEGIN  
	declare 
		@K_BODEGA_RENTAL			numeric
		,@K_DESPACHO_RENTAL			numeric
		,@vl_cod_guia_despacho		numeric
		,@vl_cod_empresa			numeric
		,@vl_cod_sucursal_despacho	numeric
		,@vl_cod_persona			numeric
		,@vl_referencia				varchar(100)
		,@vl_nro_orden_compra		varchar(20)
		,@vc_cod_item_mod_arriendo	numeric
		,@vl_orden					numeric

	set @K_BODEGA_RENTAL = 1
	set @K_DESPACHO_RENTAL = 5

	--	obtiene los datos del ARRIENDO
	select @vl_cod_empresa = a.cod_empresa
	      ,@vl_cod_sucursal_despacho = a.cod_sucursal
	      ,@vl_cod_persona = a.cod_persona
	      ,@vl_referencia = a.referencia
	      ,@vl_nro_orden_compra = a.nro_orden_compra
	from mod_arriendo m, arriendo a
	where m.cod_mod_arriendo = @ve_cod_mod_arriendo
	  and a.COD_ARRIENDO = m.COD_ARRIENDO
	-- crea la GD
	execute spu_guia_despacho 
		'INSERT' 
		,NULL -- cod_guia_despacho = identity
		,NULL -- cod_usuario_impresion
		,@ve_cod_usuario 
		,NULL -- nro_guia_despacho		
		,1 -- cod_estado_doc_sii = emitida
		,@vl_cod_empresa 
		,@vl_cod_sucursal_despacho
		,@vl_cod_persona
		,@vl_referencia 
		,@vl_nro_orden_compra
		,NULL -- obs
		,NULL -- retirado_por
		,NULL -- rut_retirado_por
		,NULL -- dig_verif_retirado_por
		,NULL -- guia_transporte
		,NULL -- patente
		,NULL -- cod_factura
		,@K_BODEGA_RENTAL -- cod_bodega RENTAL
		,@K_DESPACHO_RENTAL -- cod_tipo_guia_despacho = arriendo
		,@ve_cod_mod_arriendo 
		,NULL -- motivo_anula
		,NULL -- cod_usuario_anula 		 

	set @vl_cod_guia_despacho = @@identity

	declare C_ITEM cursor for 
	select i.cod_item_mod_arriendo
	from MOD_ARRIENDO m, ITEM_MOD_ARRIENDO i
	where m.COD_MOD_ARRIENDO = @ve_cod_mod_arriendo
	  and m.COD_ESTADO_MOD_ARRIENDO = 2	--confirmado
	  and i.COD_MOD_ARRIENDO = m.COD_MOD_ARRIENDO
	  and dbo.f_arr_cant_por_despachar(i.cod_item_mod_arriendo, null) > 0
				
	set @vl_orden = 10
	open C_ITEM 
	fetch C_ITEM into @vc_cod_item_mod_arriendo
	while @@fetch_status = 0 begin
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
				when isnull(p.precio_venta_publico, 0) > 0 then p.precio_venta_publico
				when ISNULL(i.precio_venta, 0) >0 then i.precio_venta
				else i.PRECIO
			 end,
			@vc_cod_item_mod_arriendo,
			'ITEM_MOD_ARRIENDO'
		from item_mod_arriendo i left outer join PRODUCTO p on p.cod_producto = i.COD_PRODUCTO
		where i.COD_ITEM_MOD_ARRIENDO = @vc_cod_item_mod_arriendo
		
		set @vl_orden = @vl_orden + 10

		fetch C_ITEM into @vc_cod_item_mod_arriendo
	end
	close C_ITEM
	deallocate C_ITEM
END

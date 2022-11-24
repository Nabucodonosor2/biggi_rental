-------------------------------- sp_gr_crear_desde_arriendo --------------------
create PROCEDURE sp_gr_crear_desde_arriendo(@ve_cod_mod_arriendo numeric, @ve_cod_usuario numeric)
AS
BEGIN  
	declare 
		@K_BODEGA_RENTAL			numeric
		,@K_DESPACHO_RENTAL			numeric
		,@vl_cod_guia_recepcion		numeric
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
	  
	  
	-- crea la GR
	exec spu_guia_recepcion
			'INSERT'
			,null	--@ve_cod_guia_recepcion			numeric
			,@ve_cod_usuario				
			,@vl_cod_empresa
			,@vl_cod_sucursal_despacho
			,@vl_cod_persona
			,1		--estado inicial "ingresada"
			,4		--GR de arriendo
			,'MOD_ARRIENDO'
			,@ve_cod_mod_arriendo
			,@ve_cod_mod_arriendo
			,null	--@ve_obs						
			,null	--@ve_cod_usuario_anula			
			,null	--@ve_motivo_anula				
			
	set @vl_cod_guia_recepcion = @@identity

	declare C_ITEM cursor for 
	select i.cod_item_mod_arriendo
	from ITEM_MOD_ARRIENDO i
	where i.COD_MOD_ARRIENDO = @ve_cod_mod_arriendo
	and dbo.f_arr_cant_por_recepcionar(i.cod_item_mod_arriendo, null) > 0		
				
				
	open C_ITEM 
	fetch C_ITEM into @vc_cod_item_mod_arriendo
	while @@fetch_status = 0 begin
		insert into item_guia_recepcion(
			cod_guia_recepcion,
			cod_producto,
			nom_producto,
			cantidad,
			cod_item_doc,
			tipo_doc)
		select @vl_cod_guia_recepcion,
			cod_producto,
			nom_producto,
			dbo.f_arr_cant_por_recepcionar(cod_item_mod_arriendo, null),
			@vc_cod_item_mod_arriendo,
			'ITEM_MOD_ARRIENDO'
		from item_mod_arriendo
		where COD_ITEM_MOD_ARRIENDO = @vc_cod_item_mod_arriendo
		
		fetch C_ITEM into @vc_cod_item_mod_arriendo
	end
	close C_ITEM
	deallocate C_ITEM
END
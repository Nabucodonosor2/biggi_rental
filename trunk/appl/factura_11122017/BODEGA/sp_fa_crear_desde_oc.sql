alter PROCEDURE [dbo].[sp_fa_crear_desde_oc] 
(
	@ve_cod_orden_compra numeric, 
	@ve_cod_usuario numeric
)
AS
BEGIN  

		declare @vl_cod_max_cant_it_fa numeric,
			@vl_valor_max_cant_it_fa varchar(100),
			@vl_cod_fa  numeric,
			@vl_cod_item_orden_compra numeric,
			@vl_i numeric,
			@vl_orden numeric,
			@vl_item varchar(10),
			@vl_cod_producto varchar(30),
			@vl_nom_producto varchar(100),
			@vl_cantidad T_CANTIDAD,
			@vl_precio T_PRECIO,
			@vl_cod_item_doc numeric,
			@suma_monto_dscto1 T_PRECIO,
			@suma_monto_dscto2 T_PRECIO,
			@vl_total_fa_monto_dscto1 T_PRECIO,
			@vl_total_fa_monto_dscto2 T_PRECIO,
			@vl_nv_monto_dscto1 T_PRECIO,
			@vl_nv_monto_dscto2 T_PRECIO,
			@suma_nc_monto_dscto1		T_PRECIO,
			@suma_nc_monto_dscto2		T_PRECIO


		set @vl_cod_max_cant_it_fa = 29
		
		select @vl_valor_max_cant_it_fa = valor
		from parametro
		where cod_parametro = @vl_cod_max_cant_it_fa
		

		execute sp_fa_crear_oc @ve_cod_orden_compra, @ve_cod_usuario	
		set @vl_cod_fa = @@identity
		
		declare c_cursor cursor for 
		select cod_item_orden_compra
		from BIGGI.dbo.ITEM_ORDEN_COMPRA
		where COD_ORDEN_COMPRA = @ve_cod_orden_compra 
		  and dbo.f_fa_OC_Comercial_por_facturar (COD_ITEM_ORDEN_COMPRA) > 0
		  and precio > 0

		
		open c_cursor 
		fetch c_cursor into @vl_cod_item_orden_compra

		set @suma_monto_dscto1 = 0
		set @suma_monto_dscto2 = 0

		select @suma_monto_dscto1 = isnull(sum(monto_dscto1), 0)
				,@suma_monto_dscto2 = isnull(sum(monto_dscto2), 0)
		from factura
		where cod_tipo_factura = 1 -- venta
		  and cod_doc = @ve_cod_orden_compra
		  and cod_estado_doc_sii in (2,3)

		--- VMC, 27-12-2010
		-- Se deben descontar los descuentos de NC asociadas a FA de la OC
		set @suma_nc_monto_dscto2 = 0
		set @suma_nc_monto_dscto2 = 0

		select @suma_nc_monto_dscto1 = isnull(sum(monto_dscto1), 0)
				,@suma_nc_monto_dscto2 = isnull(sum(monto_dscto2), 0)
		from nota_credito
		where cod_estado_doc_sii in (2,3)
		  and cod_doc in (select cod_factura
						from factura
						where cod_tipo_factura = 1 -- venta
						  and cod_doc = @ve_cod_orden_compra
						  and cod_estado_doc_sii in (2,3))


		set @suma_monto_dscto1 = @suma_monto_dscto1  - @suma_nc_monto_dscto1
		set @suma_monto_dscto2 = @suma_monto_dscto2 - @suma_nc_monto_dscto2
		-----------------

		set @vl_i = 1
		while @@fetch_status = 0 
		begin
			if (@vl_i > @vl_valor_max_cant_it_fa)
			begin
				execute spu_factura 'RECALCULA', @vl_cod_fa

				-- se acumula los dscto
				select @vl_total_fa_monto_dscto1 = isnull(monto_dscto1, 0)
						,@vl_total_fa_monto_dscto2 = isnull(monto_dscto2, 0)
				from factura
				where cod_factura = @vl_cod_fa

				set @suma_monto_dscto1 = @suma_monto_dscto1 + @vl_total_fa_monto_dscto1
				set @suma_monto_dscto2 = @suma_monto_dscto2 + @vl_total_fa_monto_dscto2

				execute sp_fa_crear_oc @ve_cod_orden_compra, @ve_cod_usuario
				set @vl_cod_fa = @@identity
				set @vl_i = 1
			end		
			
			
			select @vl_orden = orden
			    ,@vl_item = item
			    ,@vl_cod_producto = cod_producto
			    ,@vl_nom_producto = nom_producto
			    ,@vl_cantidad = dbo.f_fa_OC_Comercial_por_facturar(cod_item_orden_compra)
			    ,@vl_precio = precio
				,@vl_cod_item_doc = cod_item_orden_comprea
			from BIGGI.dbo.item_orden_compra
			where cod_item_orden_compra = @vl_cod_item_orden_compra
			
			insert into item_factura
			(
				COD_FACTURA, 
				ORDEN, 
				ITEM, 
				COD_PRODUCTO, 
				NOM_PRODUCTO, 
				CANTIDAD, 
				PRECIO, 
				COD_ITEM_DOC,
				TIPO_DOC
			)
			values(
				@vl_cod_fa,
				@vl_orden,
				@vl_item,
				@vl_cod_producto,
				@vl_nom_producto,
				@vl_cantidad,
				@vl_precio,
				@vl_cod_item_doc,
				'ITEM_NOTA_VENTA') 

			set @vl_i = @vl_i + 1
			fetch c_cursor into @vl_cod_item_orden_compra
		end
		close c_cursor
		deallocate c_cursor

		-- descto de la NV
		select @vl_nv_monto_dscto1 = isnull(monto_dscto1, 0)
				,@vl_nv_monto_dscto2 = isnull(monto_dscto2, 0)
		from biggi.dbo.ORDEN_COMPRA where cod_orden_compra = @ve_cod_orden_compra

		update factura
		set monto_dscto1 = @vl_nv_monto_dscto1 - @suma_monto_dscto1,
			monto_dscto2 = @vl_nv_monto_dscto2 - @suma_monto_dscto2,
			ingreso_usuario_dscto1 = 'M',
			ingreso_usuario_dscto2 = 'M'
		where cod_factura = @vl_cod_fa
		
		execute spu_factura'RECALCULA', @vl_cod_fa
END	  
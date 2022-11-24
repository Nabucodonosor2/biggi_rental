-------------------- spu_mod_arriendo---------------------------------
alter PROCEDURE spu_mod_arriendo(@ve_operacion						varchar(20)
								,@ve_cod_mod_arriendo				numeric = null
								,@ve_cod_usuario					numeric=null
								,@ve_cod_arriendo					numeric=null
								,@ve_cod_estado_mod_arriendo		numeric=null
								,@ve_referencia						varchar(100)=null
								,@ve_subtotal						numeric=null
								,@ve_total_neto						numeric=null
								,@ve_porc_iva						T_PORCENTAJE=null
								,@ve_monto_iva						numeric=null
								,@ve_total_con_iva					numeric=null
								,@ve_tipo_mod_arriendo				varchar(100)=null)
AS
BEGIN
	if (@ve_operacion='INSERT')
		insert into MOD_ARRIENDO
			(FECHA_MOD_ARRIENDO
			,COD_USUARIO
			,COD_ARRIENDO
			,COD_ESTADO_MOD_ARRIENDO
			,REFERENCIA
			,SUBTOTAL
			,TOTAL_NETO
			,PORC_IVA
			,MONTO_IVA
			,TOTAL_CON_IVA
			,TIPO_MOD_ARRIENDO
			)
		values
			(getdate()							--FECHA_MOD_ARRIENDO
			,@ve_cod_usuario					--COD_USUARIO
			,@ve_cod_arriendo					--COD_ARRIENDO
			,@ve_cod_estado_mod_arriendo		--COD_ESTADO_MOD_ARRIENDO
			,@ve_referencia						--REFERENCIA
			,@ve_subtotal						--SUBTOTAL
			,@ve_total_neto						--TOTAL_NETO
			,@ve_porc_iva						--PORC_IVA
			,@ve_monto_iva						--MONTO_IVA
			,@ve_total_con_iva					--TOTAL_CON_IVA
			,@ve_tipo_mod_arriendo
			)
	else if (@ve_operacion='UPDATE') begin
		update MOD_ARRIENDO
		set COD_USUARIO = @ve_cod_usuario
			,COD_ARRIENDO = @ve_cod_arriendo
			,COD_ESTADO_MOD_ARRIENDO = @ve_cod_estado_mod_arriendo
			,REFERENCIA = @ve_referencia
			,SUBTOTAL = @ve_subtotal
			,TOTAL_NETO = @ve_total_neto
			,PORC_IVA = @ve_porc_iva
			,MONTO_IVA = @ve_monto_iva
			,TOTAL_CON_IVA = @ve_total_con_iva
		where COD_MOD_ARRIENDO = @ve_cod_mod_arriendo
	end
	else if (@ve_operacion='DELETE') begin
		delete ITEM_MOD_ARRIENDO 
		where COD_MOD_ARRIENDO = @ve_cod_mod_arriendo

		delete MOD_ARRIENDO 
		where COD_MOD_ARRIENDO = @ve_cod_mod_arriendo
	end		
	else if (@ve_operacion='APROBAR') begin
		-- obtiene el max ORDEN existente en ITEM_ARRIENDO
		declare
			@orden						numeric
			,@cod_arriendo				numeric
			,@vc_cod_item_arriendo		numeric
			,@vc_cod_item_mod_arriendo	numeric
			,@vl_cod_producto_TE		varchar(30)

		update item_mod_arriendo
		set COD_PRODUCTO = 'TE' + convert(varchar, cod_item_mod_arriendo)
		where cod_mod_arriendo = @ve_cod_mod_arriendo
		  and cod_producto = 'TE'
	end
	else if (@ve_operacion='DESDE_ARRIENDO') begin
		
		declare @vl_fecha_mod_arriendo  	datetime,
				@vl_cod_usuario				numeric ,
				@vl_subtotal				numeric , 
				@vl_total_neto				numeric ,
				@vl_porc_iva				T_PORCENTAJE,
				@vl_monto_iva				numeric,
				@vl_total_con_iva			numeric,
				@vl_tipo_mod_arriendo		varchar(10),
				@vl_referencia				varchar(100),
				@vc_orden					numeric,
				@vc_item					varchar(4),
				@vc_cod_prodcuto			varchar(30),
				@vc_nom_producto			varchar(100),
				@vc_cantidad				T_CANTIDAD,
				@vc_precio					T_PRECIO,
				@vc_precio_venta			T_PRECIO,
				@vc_cod_tipo_te				numeric(10),
				@vc_motivo_te				varchar(100),
				@cod_mod_arriendo			numeric(10)
				
		SELECT 	@vl_cod_usuario = COD_USUARIO,
	  			@vl_subtotal = SUBTOTAL,
	  			@vl_total_neto = TOTAL_NETO,
	  			@vl_porc_iva = PORC_IVA,
	  			@vl_monto_iva = MONTO_IVA,
	  			@vl_total_con_iva = TOTAL_CON_IVA,
	  			@vl_tipo_mod_arriendo = 'AGREGAR'
	  	  FROM ARRIENDO
	  	  WHERE COD_ARRIENDO = @ve_cod_arriendo
	  	  
	  	  select @vl_referencia= 'INVENTARIO INICIAL ARRIENDO Nº'+ convert(varchar ,@ve_cod_arriendo);
	  	  
	  	  insert into MOD_ARRIENDO 
			(FECHA_MOD_ARRIENDO
			,COD_USUARIO
			,COD_ARRIENDO
			,COD_ESTADO_MOD_ARRIENDO
			,REFERENCIA
			,SUBTOTAL
			,TOTAL_NETO
			,PORC_IVA
			,MONTO_IVA
			,TOTAL_CON_IVA
			,TIPO_MOD_ARRIENDO
			)
	  	  values
	  	  	(getdate(),
			@vl_cod_usuario ,
			@ve_cod_arriendo ,
			2 ,	--confirmado
			@vl_referencia ,
			@vl_subtotal , 
			@vl_total_neto ,
			@vl_porc_iva ,
			@vl_monto_iva ,
			@vl_total_con_iva , 
			@vl_tipo_mod_arriendo)
								
										
	  	 set @cod_mod_arriendo = @@identity 
		declare C_IT_MOD_ARRIENDO CURSOR FOR  
				SELECT ORDEN ,
					   ITEM ,
					   COD_PRODUCTO ,
					   NOM_PRODUCTO , 
					   CANTIDAD ,
					   PRECIO ,
					   PRECIO_VENTA ,
					   COD_TIPO_TE ,
					   MOTIVO_TE
				FROM ITEM_ARRIENDO 
				WHERE COD_ARRIENDO = @ve_cod_arriendo
		
		OPEN C_IT_MOD_ARRIENDO
		FETCH C_IT_MOD_ARRIENDO INTO @vc_orden,@vc_item,@vc_cod_prodcuto,@vc_nom_producto,@vc_cantidad,@vc_precio,@vc_precio_venta,@vc_cod_tipo_te,@vc_motivo_te 	
		WHILE @@FETCH_STATUS = 0 BEGIN	
			
			insert into item_mod_arriendo values (@cod_mod_arriendo,
												  @vc_orden,
												  @vc_item,
												  @vc_cod_prodcuto,
												  @vc_nom_producto,
												  @vc_cantidad,
												  @vc_precio,
												  @vc_precio_venta,
												  @vc_cod_tipo_te,
												  @vc_motivo_te) 
			
		FETCH C_IT_MOD_ARRIENDO INTO @vc_orden,@vc_item,@vc_cod_prodcuto,@vc_nom_producto,@vc_cantidad,@vc_precio,@vc_precio_venta,@vc_cod_tipo_te,@vc_motivo_te
		END
		CLOSE C_IT_MOD_ARRIENDO
		DEALLOCATE C_IT_MOD_ARRIENDO
		
		update item_mod_arriendo
		set COD_PRODUCTO = 'TE' + convert(varchar, cod_item_mod_arriendo)
		where cod_mod_arriendo = @cod_mod_arriendo
		  and cod_producto = 'TE'
		  
		exec spu_mod_arriendo 'RECALCULA', @ve_cod_mod_arriendo
	end 
	else if(@ve_operacion='RECALCULA')
	begin
		declare
			@vl_sub_total		numeric
		
		select @vl_porc_iva = isnull(porc_iva, 0)
		from mod_ARRIENDO
		where cod_mod_arriendo = @ve_cod_mod_arriendo

		select @vl_sub_total = isnull(sum(round(cantidad * precio, 0)), 0)
		from ITEM_MOD_ARRIENDO
		where cod_mod_arriendo = @ve_cod_mod_arriendo
		
		set @vl_total_neto = @vl_sub_total 
		set @vl_monto_iva = round(@vl_total_neto * @vl_porc_iva / 100, 0) 
		set @vl_total_con_iva = @vl_total_neto + @vl_monto_iva

		update MOD_ARRIENDO		
		set	subtotal					=	@vl_sub_total		
			,total_neto					=	@vl_total_neto				
			,monto_iva					=	@vl_monto_iva		
			,total_con_iva				=	@vl_total_con_iva	
		where cod_mod_arriendo = @ve_cod_mod_arriendo
	end
END

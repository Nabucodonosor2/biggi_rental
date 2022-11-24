CREATE PROCEDURE [dbo].[spu_item_cot_arriendo](@ve_operacion varchar(20),
											   @ve_cod_item_cot_arriendo numeric,
											   @ve_cod_cot_arriendo numeric=NULL, 
											   @ve_orden numeric=NULL,
											   @ve_item varchar(10)=NULL, 
											   @ve_cod_producto varchar(100)=NULL, 
											   @ve_nom_producto varchar(100)=NULL, 
											   @ve_cantidad T_CANTIDAD=NULL, 
											   @ve_precio T_PRECIO=NULL,
											   @ve_precio_arriendo T_PRECIO=NULL,
											   @ve_motivo_mod_precio varchar(100)=NULL, 
											   @ve_cod_usuario_mod_precio numeric=NULL,
											   @ve_tipo_te varchar(100)=NULL,
											   @ve_motivo_te varchar(100)=NULL)
AS
	declare @precio_old numeric
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into item_cot_arriendo(
					cod_cot_arriendo,
					orden,
					item,
					cod_producto,
					nom_producto,
					cantidad,
					precio,
					precio_arriendo,
					cod_tipo_te,		
					motivo_te)
		values		(
					@ve_cod_cot_arriendo,
					@ve_orden,
					@ve_item,
					@ve_cod_producto,
					@ve_nom_producto,
					@ve_cantidad,
					@ve_precio,
					@ve_precio_arriendo,
					@ve_tipo_te,
					@ve_motivo_te
					) 
	
		set @ve_cod_item_cot_arriendo = @@identity
		if(@ve_motivo_mod_precio<>'') -- tiene motivo, por lo tanto se modificó el precio
		begin
			select @precio_old = precio_venta_publico
			from producto
			where cod_producto = @ve_cod_producto	

			insert into modifica_precio_cot_arriendo 
			values (@ve_cod_item_cot_arriendo, @ve_cod_usuario_mod_precio, getdate(), @precio_old, @ve_precio, @ve_motivo_mod_precio)	
		end
	end 
	else if (@ve_operacion='UPDATE') begin
		select @precio_old = precio
		from item_cot_arriendo
		where cod_item_cot_arriendo = @ve_cod_item_cot_arriendo
	
		if(@ve_motivo_mod_precio<>'') -- tiene motivo, por lo tanto se modificó el precio
			insert into modifica_precio_cot_arriendo 
			values (@ve_cod_item_cot_arriendo, @ve_cod_usuario_mod_precio, getdate(), @precio_old, @ve_precio, @ve_motivo_mod_precio)
		
		update item_cot_arriendo
		set cod_cot_arriendo		=	@ve_cod_cot_arriendo,
			orden					=	@ve_orden,
			item					=	@ve_item,
			cod_producto			=	@ve_cod_producto,
			nom_producto			=	@ve_nom_producto,
			cantidad				=	@ve_cantidad,
			precio					=	@ve_precio,
			precio_arriendo			=	@ve_precio_arriendo,
			cod_tipo_te				=	@ve_tipo_te,				
			motivo_te				=	@ve_motivo_te   
		where cod_item_cot_arriendo	=	@ve_cod_item_cot_arriendo
	end
	else if (@ve_operacion='DELETE') begin
		delete modifica_precio_cot_arriendo
		where COD_ITEM_COT_ARRIENDO = @ve_cod_item_cot_arriendo
	
		delete  item_cot_arriendo 
	    where COD_ITEM_COT_ARRIENDO = @ve_cod_item_cot_arriendo
	end	
END
go



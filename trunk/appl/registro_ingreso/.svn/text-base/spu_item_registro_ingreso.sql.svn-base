ALTER PROCEDURE [dbo].[spu_item_registro_ingreso]( @ve_operacion varchar(20),
													@ve_cod_item_registro_4d    numeric,
													@ve_numero_registro_ingreso numeric = null,
													@ve_item numeric = null,
													@ve_modelo varchar(30) = null,
													@ve_cantidad numeric(5,2) = null,
													@ve_precio numeric(14,2) = null,
													@ve_total numeric(14,2) = null,
													@ve_cu_us numeric = null,
													@ve_cu_pesos numeric = null,
													@ve_precio_vta_sug numeric = null)
													
	
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into ITEM_REGISTRO_4D(
					numero_registro_ingreso,
					item,
					modelo,
					cantidad,
					precio,
					total,
					cu_us,		
					cu_pesos,
					precio_vta_sug)
		values		(
					@ve_numero_registro_ingreso,
					@ve_item,
					@ve_modelo ,
					@ve_cantidad,
					@ve_precio,
					@ve_total,
					@ve_cu_us,
					@ve_cu_pesos,
					@ve_precio_vta_sug) 
	
	end 
	else if (@ve_operacion='UPDATE') begin
		
		update ITEM_REGISTRO_4D
			set modelo				=	@ve_modelo,
				item				=	@ve_item,
				cantidad			= 	@ve_cantidad,
				precio				= 	@ve_precio,
				total				= 	@ve_total,
				cu_us				=	@ve_cu_us,	
				cu_pesos			= 	@ve_cu_pesos,
				precio_vta_sug   	=	@ve_precio_vta_sug
		where cod_item_registro_4d	=	@ve_cod_item_registro_4d
		
		
	end
	else if (@ve_operacion='DELETE') begin
		delete  ITEM_REGISTRO_4D 
	    where cod_item_registro_4d	=	@ve_cod_item_registro_4d
	end	
END

ALTER PROCEDURE [dbo].[sp_update_costo_producto](@ve_empresa					varchar(50)
											,@ve_cod_producto			varchar(50)
											,@ve_precio					numeric(14,2)
											)
AS
BEGIN
DECLARE	@vl_cod_producto_proveedor		numeric(10)
		,@vl_cod_empresa				numeric(10)
		,@vl_cod_usuario				numeric(3)
		,@vl_cod_log_cambio				numeric
		
		--se deja el usuario 1 momentaneamente el cual se tiene que cambiar al 
		--subir los cambios
		set @vl_cod_usuario = 16
		
	IF (@ve_empresa = 'TODOINOX') or (@ve_empresa = 'BODEGA')
	BEGIN
		IF @ve_empresa = 'TODOINOX'
			SET @vl_cod_empresa = 4
		IF (@ve_empresa = 'BODEGA')
			SET @vl_cod_empresa = 28
	
		SET @vl_cod_producto_proveedor = 0
		
			select @vl_cod_producto_proveedor = COD_PRODUCTO_PROVEEDOR
			from PRODUCTO_PROVEEDOR
			where cod_producto = @ve_cod_producto
			and COD_EMPRESA = @vl_cod_empresa
		
		IF @vl_cod_producto_proveedor != 0
		BEGIN
			INSERT INTO COSTO_PRODUCTO(COD_PRODUCTO_PROVEEDOR,FECHA_INICIO_VIGENCIA,PRECIO,COD_USUARIO)
			VALUES(@vl_cod_producto_proveedor,GETDATE(),@ve_precio,@vl_cod_usuario)
			
			exec sp_log_cambio 'PRODUCTO', @ve_cod_producto, @vl_cod_usuario, 'U'
			set @vl_cod_log_cambio = @@IDENTITY
		END
		
		exec sp_detalle_cambio @vl_cod_log_cambio, 'PRECIO_VENTA_INTERNO', 0, @ve_precio
	END
	
END

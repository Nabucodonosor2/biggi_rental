-------------------- sp_bodega_cierre ---------------------------------
CREATE PROCEDURE sp_bodega_cierre(@ve_fecha_cierre	datetime)
AS
BEGIN
	declare
		@vl_fecha_registro		datetime

	set @vl_fecha_registro = getdate()

	insert into CIERRE_BODEGA
		(FECHA_REGISTRO     
		,COD_BODEGA         
		,COD_PRODUCTO       
		,FECHA_CIERRE       
		,CANTIDAD_CIERRE    
		,PRECIO_PMP         
		,PRECIO_PMP_US      
		)
	select @vl_fecha_registro
			,b.cod_bodega
			,p.cod_producto
			,@ve_fecha_cierre
			,dbo.f_bodega_stock(p.cod_producto, b.cod_bodega, @ve_fecha_cierre)
			,dbo.f_bodega_pmp(p.cod_producto, b.cod_bodega, @ve_fecha_cierre)
			,dbo.f_bodega_pmp_us(p.cod_producto, b.cod_bodega, @ve_fecha_cierre)
	from bodega b, producto p
END


	

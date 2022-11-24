---[f_prod_get_costo_base]---CAMBIAR TAMBIEN EN ECLIPSE-----
ALTER FUNCTION [dbo].[f_prod_get_costo_base](@ve_cod_producto varchar(50))
RETURNS numeric
AS
BEGIN
	declare 
		@ve_costo_base numeric	
	
	SET @ve_costo_base = dbo.f_redondeo_tdnx(dbo.f_prod_RI(@ve_cod_producto, 'PRECIO') * dbo.f_prod_RI(@ve_cod_producto, 'FACTOR_IMP') * convert(numeric, dbo.f_get_parametro(5)))
			
return @ve_costo_base;
END
go
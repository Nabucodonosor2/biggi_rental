ALTER FUNCTION [dbo].[f_arr_vigente](@ve_cod_arriendo  numeric)
RETURNS VARCHAR(15)
AS
BEGIN
DECLARE
	 @stock NUMERIC
	,@result VARCHAR(15)

	SELECT @stock = SUM(A.STOCK)
	FROM
	(SELECT DISTINCT I.COD_PRODUCTO
					,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) STOCK 
	 FROM ITEM_MOD_ARRIENDO I
		 ,MOD_ARRIENDO M
		 ,ARRIENDO A
	WHERE M.COD_ARRIENDO = @ve_cod_arriendo
		  AND A.COD_ARRIENDO = M.COD_ARRIENDO
		  AND I.COD_MOD_ARRIENDO = M.COD_MOD_ARRIENDO
		  AND DBO.F_BODEGA_STOCK(I.COD_PRODUCTO, A.COD_BODEGA, GETDATE()) > 0)	A
		  
	if (@stock > 0)
		set @result	= 'VIGENTE'
	else
		set @result = 'NO VIGENTE'	  
	
RETURN @result

END
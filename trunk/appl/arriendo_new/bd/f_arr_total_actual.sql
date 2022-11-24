--------------------  f_arr_total_actual  ----------------
alter FUNCTION f_arr_total_actual(@ve_cod_arriendo numeric
									,@ve_fecha_arriendo datetime)
RETURNS numeric
AS
BEGIN
declare
	@total		numeric

	select @total = SUM(A.STOCK * A.PRECIO)
	from 
	(select distinct I.COD_PRODUCTO
			,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, @ve_fecha_arriendo) STOCK
			,I.PRECIO 
	from ITEM_MOD_ARRIENDO I, MOD_ARRIENDO M, ARRIENDO A
	WHERE M.COD_ARRIENDO = @ve_cod_arriendo
	  AND A.COD_ARRIENDO = M.COD_ARRIENDO
	  AND I.COD_MOD_ARRIENDO = M.COD_MOD_ARRIENDO
	  AND DBO.F_BODEGA_STOCK(I.COD_PRODUCTO, A.COD_BODEGA, @ve_fecha_arriendo) > 0
	) A
	
	RETURN @total
END

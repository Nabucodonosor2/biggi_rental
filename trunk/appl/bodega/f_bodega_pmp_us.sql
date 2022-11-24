--------------------  f_bodega_pmp_us  ----------------
alter FUNCTION f_bodega_pmp_us(@ve_cod_producto varchar(20), @ve_cod_bodega numeric, @ve_fecha datetime)
-- Es igual a f_bodega_pmp pero con el precio_us
RETURNS numeric(10,2)
AS
BEGIN
declare
	@fecha_cierre		datetime
	,@vl_pmp_us			T_PRECIO

if (@ve_cod_bodega is null)
	return 0

---
select @fecha_cierre = max(fecha_cierre)
from 	cierre_bodega
WHERE  	cod_bodega = @ve_cod_bodega and
      	cod_producto = @ve_cod_producto and
      	fecha_cierre < @ve_fecha

if (@fecha_cierre is not null)
	SELECT 	 @vl_pmp_us = precio_pmp_us
	FROM	cierre_bodega
	WHERE  	cod_bodega = @ve_cod_bodega and
      		cod_producto = @ve_cod_producto and
      		fecha_cierre = @fecha_cierre
	order by fecha_registro desc
-----

if (@fecha_cierre is null)
begin
   set @vl_pmp_us = 0
   SET @fecha_cierre = '01-01-2000'
end

----------------------------
declare c_entrada cursor for 
select  i.cantidad
		,i.precio_us
		,e.fecha_entrada_bodega
from  	item_entrada_bodega i, entrada_bodega e
where 	e.cod_entrada_bodega = i.cod_entrada_bodega and
      	e.cod_bodega = @ve_cod_bodega and
      	i.cod_producto = @ve_cod_producto and
      	e.fecha_entrada_bodega <= @ve_fecha and
		e.fecha_entrada_bodega > @fecha_cierre
order by e.fecha_entrada_bodega

declare
	@vc_cantidad		T_CANTIDAD
	,@vc_precio_us			T_PRECIO
	,@vc_fecha_entrada	datetime
	,@vl_cant_ant		T_CANTIDAD

open c_entrada 
fetch c_entrada into @vc_cantidad, @vc_precio_us, @vc_fecha_entrada
WHILE (@@FETCH_STATUS = 0)BEGIN	
	set @vl_cant_ant = dbo.f_bodega_stock(@ve_cod_producto, @ve_cod_bodega, dateadd(second, -1, @vc_fecha_entrada))
	if (@vl_cant_ant <= 0)
		set @vl_pmp_us = @vc_precio_us
	else
		set @vl_pmp_us = ((@vl_cant_ant * @vl_pmp_us) + (@vc_cantidad * @vc_precio_us)) / (@vl_cant_ant + @vc_cantidad)

	fetch c_entrada into @vc_cantidad, @vc_precio_us, @vc_fecha_entrada
end
close c_entrada
deallocate c_entrada

return @vl_pmp_us

END
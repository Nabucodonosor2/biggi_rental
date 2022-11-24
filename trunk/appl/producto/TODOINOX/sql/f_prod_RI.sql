--------------------  f_prod_RI  ----------------
alter FUNCTION f_prod_RI(@ve_cod_producto	varchar(100), @ve_dato	varchar(100))
-- retorna el dato solicitado desde el ultimo RI 
RETURNS numeric(14,2)
AS
BEGIN
declare
	@vl_numero_registro_ingreso		numeric
	,@vl_cod_item_registro_4d		numeric
	,@vl_cu_us						numeric(14,2)
	,@vl_precio						numeric(14,2)
	,@vl_factor_imp					numeric(14,2)

	select top 1
			@vl_numero_registro_ingreso = r.numero_registro_ingreso
			,@vl_cod_item_registro_4d = i.cod_item_registro_4d
			,@vl_cu_us = i.cu_us
			,@vl_precio = i.precio
			,@vl_factor_imp = r.factor_imp
	from item_registro_4d i, registro_ingreso_4d r
	where r.numero_registro_ingreso = i.numero_registro_ingreso
	  and i.modelo = @ve_cod_producto
	order by fecha_registro_ingreso desc

	if (@ve_dato = 'NUMERO_REGISTRO_INGRESO')
		return @vl_numero_registro_ingreso
	else if (@ve_dato = 'CU_US') 
		return @vl_cu_us
	else if (@ve_dato = 'PRECIO') 
		return @vl_precio
	else if (@ve_dato = 'FACTOR_IMP') 
		return @vl_factor_imp


	return null
END

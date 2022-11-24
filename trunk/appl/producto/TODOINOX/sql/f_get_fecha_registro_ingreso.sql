ALTER function f_get_fecha_registro_ingreso(@ve_cod_producto varchar(100))
RETURNS datetime
AS
BEGIN
	declare 
		@vl_fecha_registro_ingreso			datetime
		,@vl_numero_registro_ingreso		numeric
	
	set @vl_numero_registro_ingreso = dbo.f_prod_RI(@ve_cod_producto, 'NUMERO_REGISTRO_INGRESO')
		
	SELECT @vl_fecha_registro_ingreso = FECHA_REGISTRO_INGRESO
	FROM REGISTRO_INGRESO_4D
	WHERE NUMERO_REGISTRO_INGRESO = @vl_numero_registro_ingreso
			
	return @vl_fecha_registro_ingreso;
END

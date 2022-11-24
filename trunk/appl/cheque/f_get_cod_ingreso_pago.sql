CREATE FUNCTION f_get_cod_ingreso_pago(@ve_cod_cheque numeric)
RETURNS VARCHAR(1000)
AS
BEGIN
	DECLARE
		@vl_cod_ingreso_pago_str	varchar(1000),
		@vc_cod_ingreso_pago		numeric
	
	DECLARE C_ING_PAGO CURSOR FOR
	SELECT COD_INGRESO_PAGO
	FROM CHEQUE_FACTURA
	WHERE COD_CHEQUE = @ve_cod_cheque
	AND COD_INGRESO_PAGO IS NOT NULL

	OPEN C_ING_PAGO
	FETCH C_ING_PAGO INTO @vc_cod_ingreso_pago
	WHILE @@FETCH_STATUS = 0 BEGIN	
	
		set @vl_cod_ingreso_pago_str = @vl_cod_ingreso_pago_str + CONVERT(VARCHAR, @vc_cod_ingreso_pago) + ' - '
		
		FETCH C_ING_PAGO INTO @vc_cod_ingreso_pago
	END
	CLOSE C_ING_PAGO
	DEALLOCATE C_ING_PAGO
	
	if (@vl_cod_ingreso_pago_str <> '')
		set @vl_cod_ingreso_pago_str = substring(@vl_cod_ingreso_pago_str, 1, len(@vl_cod_ingreso_pago_str)-1)
		
	return @vl_cod_ingreso_pago_str
END
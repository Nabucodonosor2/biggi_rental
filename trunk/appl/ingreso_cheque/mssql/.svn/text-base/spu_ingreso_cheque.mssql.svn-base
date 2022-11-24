CREATE PROCEDURE spu_ingreso_cheque(@ve_operacion					varchar(20)
									,@ve_cod_ingreso_cheque			numeric(10)	= null
									,@ve_fecha_ingreso_cheque		DATETIME
									,@ve_cod_usuario				numeric(10)
									,@ve_cod_empresa				numeric(10)
									,@ve_cod_estado_ingreso_cheque	numeric(10)
									,@ve_referencia					varchar(100) = NULL
									)
AS
BEGIN
	
	IF(@ve_operacion='INSERT')
	BEGIN
		INSERT INTO INGRESO_CHEQUE(FECHA_INGRESO_CHEQUE
									,COD_USUARIO
									,COD_EMPRESA
									,COD_ESTADO_INGRESO_CHEQUE
									,REFERENCIA)
							VALUES(@ve_fecha_ingreso_cheque
									,@ve_cod_usuario
									,@ve_cod_empresa
									,@ve_cod_estado_ingreso_cheque
									,@ve_referencia
									)
	END
	ELSE IF(@ve_operacion='UPDATE')
	BEGIN
		UPDATE INGRESO_CHEQUE
		SET FECHA_INGRESO_CHEQUE		= @ve_fecha_ingreso_cheque
			,COD_USUARIO				= @ve_cod_usuario	
			,COD_EMPRESA				= @ve_cod_empresa
			,COD_ESTADO_INGRESO_CHEQUE	= @ve_cod_estado_ingreso_cheque
			,REFERENCIA					= @ve_referencia
		WHERE COD_INGRESO_CHEQUE		= @ve_cod_ingreso_cheque
	END
	ELSE IF(@ve_operacion='DELETE')
	BEGIN
		DELETE INGRESO_CHEQUE
		WHERE COD_INGRESO_CHEQUE	= @ve_cod_ingreso_cheque
	END 
END 
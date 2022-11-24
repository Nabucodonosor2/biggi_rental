ALTER PROCEDURE spu_ingreso_cheque(@ve_operacion					varchar(20)
									,@ve_cod_ingreso_cheque			numeric(10)	= null
									,@ve_fecha_ingreso_cheque		DATETIME	= NULL
									,@ve_cod_usuario				numeric(10)	= NULL
									,@ve_cod_empresa				numeric(10)	= NULL
									,@ve_cod_estado_ingreso_cheque	numeric(10) = NULL
									,@ve_referencia					varchar(100)= NULL
									,@ve_cat_cheque					numeric(3)	= NULL
									,@ve_fecha_primer_cheque		DATETIME	= NULL)
AS
BEGIN
	
	IF(@ve_operacion='INSERT')
	BEGIN
		INSERT INTO INGRESO_CHEQUE(FECHA_INGRESO_CHEQUE
									,COD_USUARIO
									,COD_EMPRESA
									,COD_ESTADO_INGRESO_CHEQUE
									,REFERENCIA
									,CANT_CHEQUE
									,FECHA_PRIMER_CHEQUE)
							VALUES(GETDATE()
									,@ve_cod_usuario
									,@ve_cod_empresa
									,@ve_cod_estado_ingreso_cheque
									,@ve_referencia
									,@ve_cat_cheque
									,@ve_fecha_primer_cheque)
	END
	ELSE IF(@ve_operacion='UPDATE')
	BEGIN
		UPDATE INGRESO_CHEQUE
		SET COD_USUARIO				= @ve_cod_usuario	
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
CREATE PROCEDURE spu_cheque(@ve_operacion				varchar(20)
							,@ve_cod_cheque				numeric(10)	= null
							,@ve_cod_ingreso_cheque		numeric(10)	= null
							,@ve_cod_banco				numeric(10)	= null
							,@ve_cod_plaza				numeric(10)	= null
							,@ve_nro_doc				numeric(10)	= null
							,@ve_fecha_doc				DATETIME	= null
							,@ve_monto_doc				numeric(10)	= null
							,@ve_cod_tipo_doc_pago		numeric(10)	= null
							)
AS
BEGIN
	
	IF(@ve_operacion='INSERT')
	BEGIN
		INSERT INTO CHEQUE(COD_INGRESO_CHEQUE
							,COD_BANCO
							,COD_PLAZA
							,NRO_DOC
							,FECHA_DOC
							,MONTO_DOC
							,COD_TIPO_DOC_PAGO)
					VALUES(@ve_cod_ingreso_cheque
							,@ve_cod_banco
							,@ve_cod_plaza
							,@ve_nro_doc
							,@ve_fecha_doc
							,@ve_monto_doc
							,@ve_cod_tipo_doc_pago
							)
	END
	ELSE IF(@ve_operacion='UPDATE')
	BEGIN
		UPDATE CHEQUE
		SET COD_INGRESO_CHEQUE	= @ve_cod_ingreso_cheque
			,COD_BANCO			= @ve_cod_banco
			,COD_PLAZA			= @ve_cod_plaza
			,NRO_DOC			= @ve_nro_doc
			,FECHA_DOC			= @ve_fecha_doc
			,MONTO_DOC			= @ve_monto_doc
			,COD_TIPO_DOC_PAGO	= @ve_cod_tipo_doc_pago
		WHERE COD_CHEQUE		= @ve_cod_cheque
	END
	ELSE IF(@ve_operacion='DELETE')
	BEGIN
		DELETE CHEQUE
		WHERE COD_CHEQUE		= @ve_cod_cheque
	END 
END 
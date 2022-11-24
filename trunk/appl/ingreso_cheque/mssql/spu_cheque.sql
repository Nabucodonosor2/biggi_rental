CREATE PROCEDURE spu_cheque(@ve_operacion				varchar(20)
							,@ve_cod_cheque				numeric(10)	= null
							,@ve_cod_ingreso_cheque		numeric(10)	= null
							,@ve_cod_banco				numeric(10)	= null
							,@ve_cod_plaza				numeric(10)	= null
							,@ve_nro_doc				numeric(10)	= null
							,@ve_fecha_doc				DATETIME	= null
							,@ve_monto_doc				numeric(10)	= null
							,@ve_cod_tipo_doc_pago		numeric(10)	= null
							,@ve_depositado				varchar(1)	= null
							,@ve_fecha_depositado		DATETIME	= null
							,@ve_usu_depositado			numeric		= null
							,@ve_liberado				varchar(1)	= null
							,@ve_fecha_liberado			DATETIME	= null
							,@ve_usu_liberado			numeric		= null
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
							,COD_TIPO_DOC_PAGO
							,DEPOSITADO
							,LIBERADO)
					VALUES(@ve_cod_ingreso_cheque
							,@ve_cod_banco
							,@ve_cod_plaza
							,@ve_nro_doc
							,@ve_fecha_doc
							,@ve_monto_doc
							,@ve_cod_tipo_doc_pago
							,'N'
							,'N'
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
	ELSE IF(@ve_operacion='UPDATE_ESP')
	BEGIN
		DECLARE
			@vl_fecha_registro_depositado DATETIME,
			@vl_fecha_registro_liberado	  DATETIME
		
		SELECT @vl_fecha_registro_depositado = FECHA_REGISTRO_DEPOSITADO
			  ,@vl_fecha_registro_liberado = FECHA_REGISTRO_LIBERADO
		FROM CHEQUE
		WHERE COD_CHEQUE		= @ve_cod_cheque
		
		if(@ve_depositado = 'S' AND @vl_fecha_registro_depositado IS NULL)BEGIN
			UPDATE CHEQUE
			SET DEPOSITADO					= @ve_depositado
			    ,FECHA_DEPOSITADO			= @ve_fecha_depositado
			    ,FECHA_REGISTRO_DEPOSITADO	= GETDATE()
			    ,COD_USUARIO_DEPOSITADO		= @ve_usu_depositado
			WHERE COD_CHEQUE				= @ve_cod_cheque
		END
		
		if(@ve_liberado = 'S' AND @vl_fecha_registro_liberado IS NULL)BEGIN
			UPDATE CHEQUE
			SET LIBERADO					= @ve_liberado
			    ,FECHA_LIBERADO				= @ve_fecha_liberado
			    ,FECHA_REGISTRO_LIBERADO	= GETDATE()
			    ,COD_USUARIO_LIBERADO		= @ve_usu_liberado
			WHERE COD_CHEQUE				= @ve_cod_cheque
		END
	END
END 
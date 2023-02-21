CREATE FUNCTION f_get_reFA(@ve_cod_factura	NUMERIC
						  ,@ve_total_neto	NUMERIC
						  ,@ve_cod_empresa	NUMERIC
                          ,@ve_return_tipo  VARCHAR(3))	
RETURNS NUMERIC
AS
BEGIN
DECLARE	@vl_cod_doc NUMERIC
    IF(@ve_return_tipo = 'NRO')BEGIN
        SELECT TOP 1 @vl_cod_doc = NRO_FACTURA
        FROM FACTURA_CONTRATO FC
            ,FACTURA F
        WHERE F.COD_FACTURA > @ve_cod_factura
        AND COD_EMPRESA = @ve_cod_empresa
        AND TOTAL_NETO = @ve_total_neto
        and  COD_ESTADO_DOC_SII in (2, 3)
        AND F.COD_FACTURA = FC.COD_FACTURA
        ORDER BY FECHA_FACTURA ASC
	END
    ELSE IF(@ve_return_tipo = 'COD')BEGIN
        SELECT TOP 1 @vl_cod_doc = F.COD_FACTURA
        FROM FACTURA_CONTRATO FC
            ,FACTURA F
        WHERE F.COD_FACTURA > @ve_cod_factura
        AND COD_EMPRESA = @ve_cod_empresa
        AND TOTAL_NETO = @ve_total_neto
        and  COD_ESTADO_DOC_SII in (2, 3)
        AND F.COD_FACTURA = FC.COD_FACTURA
        ORDER BY FECHA_FACTURA ASC
	END

	RETURN @vl_cod_doc
END
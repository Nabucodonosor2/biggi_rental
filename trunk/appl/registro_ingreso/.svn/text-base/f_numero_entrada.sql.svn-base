alter FUNCTION [dbo].[f_numero_entrada](@ve_numero_registro_ingreso numeric)
RETURNS numeric
AS
BEGIN
	
	DECLARE @vl_cod_entrada_bodega numeric 
		
	SELECT  @vl_cod_entrada_bodega = COD_ENTRADA_BODEGA
	FROM ENTRADA_BODEGA
	WHERE TIPO_DOC = 'REGISTRO INGRESO'
	AND COD_DOC = @VE_NUMERO_REGISTRO_INGRESO

RETURN @vl_cod_entrada_bodega

END

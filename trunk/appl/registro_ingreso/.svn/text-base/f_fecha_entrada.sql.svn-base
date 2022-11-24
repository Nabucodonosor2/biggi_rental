alter FUNCTION [dbo].[f_fecha_entrada](@ve_numero_registro_ingreso numeric)
RETURNS varchar(10)
AS
BEGIN
	
	DECLARE @vl_fecha_entrada_bodega varchar(10) 
		
	SELECT  @vl_fecha_entrada_bodega = convert(varchar(10),FECHA_ENTRADA_BODEGA,103)
	FROM ENTRADA_BODEGA
	WHERE TIPO_DOC = 'REGISTRO INGRESO'
	AND COD_DOC = @VE_NUMERO_REGISTRO_INGRESO

RETURN @vl_fecha_entrada_bodega

END
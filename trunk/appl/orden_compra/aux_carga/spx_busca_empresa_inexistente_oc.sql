CREATE PROCEDURE [dbo].[spx_busca_empresa_inexistente_oc]
AS
BEGIN  
	DECLARE @NRO_OC		NUMERIC
			,@COUNT		NUMERIC
			,@RUT		NUMERIC

	declare @TEMPO TABLE 	 
				(NRO_OC NUMERIC
				,RUT	NUMERIC)

	DECLARE C_AUX_OC	CURSOR FOR
	SELECT DISTINCT CONVERT (NUMERIC, SUBSTRING (RUT_PROVEEDOR, 1, LEN (RUT_PROVEEDOR)-1)) RUT
	FROM AUX_ORDEN_COMPRA
	WHERE RUT_PROVEEDOR IS NOT NULL

	OPEN	C_AUX_OC
	FETCH	C_AUX_OC	INTO	@RUT
	WHILE	@@FETCH_STATUS	=	0	BEGIN
		SELECT	@COUNT	=	COUNT(*) FROM	EMPRESA	WHERE RUT	=	@RUT
		IF	(@COUNT=0)	BEGIN
			SELECT TOP 1 @NRO_OC	= NUMERO_OC 
			FROM AUX_ORDEN_COMPRA	WHERE	CONVERT(NUMERIC, SUBSTRING(RUT_PROVEEDOR, 1, LEN(RUT_PROVEEDOR) - 1))=@RUT
			INSERT INTO @TEMPO VALUES (@NRO_OC, @RUT)
		END
	
		FETCH C_AUX_OC INTO @RUT
	END
	CLOSE C_AUX_OC
	DEALLOCATE C_AUX_OC

	SELECT * FROM @TEMPO ORDER BY NRO_OC
	
END
go
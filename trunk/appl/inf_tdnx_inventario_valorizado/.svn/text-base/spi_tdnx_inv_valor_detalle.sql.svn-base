ALTER PROCEDURE [dbo].[spi_tdnx_inv_valor_detalle](@ve_cod_bodega				NUMERIC
													,@ve_fecha					DATETIME
													,@ve_cod_clasif_inventario	NUMERIC)
AS
BEGIN
	DECLARE @TEMP_VALOR_DETALLE TABLE
				(MODELO			VARCHAR(100)
				,EQUIPO			VARCHAR(100)
				,MARCA			VARCHAR(100)
				,STOCK			NUMERIC
				,C_UNIT			NUMERIC
				,COSTO_TOTAL	NUMERIC)
		INSERT INTO @TEMP_VALOR_DETALLE
						(MODELO
						,EQUIPO
						,MARCA
						,STOCK
						,C_UNIT)
				SELECT	P.COD_PRODUCTO
						,P.NOM_PRODUCTO
						,M.NOM_MARCA
						,DBO.F_BODEGA_STOCK(P.COD_PRODUCTO,@ve_cod_bodega,@ve_fecha) STOCK
						,DBO.F_BODEGA_PMP(P.COD_PRODUCTO, @ve_cod_bodega, @ve_fecha) C_UNIT
				FROM	PRODUCTO P LEFT OUTER JOIN MARCA M ON M.COD_MARCA = P.COD_MARCA
				WHERE	SUBSTRING(SISTEMA_VALIDO, 4, 1) = 'S'
				AND		(P.COD_CLASIF_INVENTARIO = 0 OR P.COD_CLASIF_INVENTARIO = @ve_cod_clasif_inventario)
				
		UPDATE @TEMP_VALOR_DETALLE
		SET COSTO_TOTAL = case 
							when STOCK  <= 0 then 0
							else STOCK * C_UNIT
						  end
		
	SELECT * FROM @TEMP_VALOR_DETALLE order by MARCA, EQUIPO
END


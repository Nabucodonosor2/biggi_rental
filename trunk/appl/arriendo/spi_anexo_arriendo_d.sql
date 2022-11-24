alter PROCEDURE [dbo].[spi_anexo_arriendo_d](@ve_cod_arriendo	NUMERIC)
AS
BEGIN
	DECLARE @TEMPO TABLE  
        (ITEM								NUMERIC
        ,ST_COD_PRODUCTO					VARCHAR(30)
		,ST_NOM_PRODUCTO					VARCHAR(100)
		,ST_CANTIDAD						NUMERIC(10,2)
		,PRECIO								NUMERIC(10,2)
		,ST_TOTAL							NUMERIC(10,2)
		,TOTAL_NETO							NUMERIC(10,2)
		,PORC_IVA							NUMERIC(5,2)
		,MONTO_IVA							NUMERIC(14,2)
		,TOTAL_CON_IVA						NUMERIC(10,2)
		,NOM_EMPRESA						VARCHAR(100)
		,RUT								NUMERIC
		,DIG_VERIF							VARCHAR(1)
		,DIRECCION							VARCHAR(100)
		,TELEFONO							VARCHAR(100)
		,NRO_MESES							numeric(10)
		,FECHA_PRIMERA_GUIA_DESPACHO		DATETIME
		,NRO_ORDEN_COMPRA					VARCHAR(100)
		,NOM_COMUNA							VARCHAR(100)
		,NOM_CIUDAD							VARCHAR(100)
		,NOM_PAIS							VARCHAR(100)
		,CENTRO_COSTO_CLIENTE				VARCHAR(100)
		,NOM_ARRIENDO						VARCHAR(100)
		,UBICACION_CONTRATO					VARCHAR(300)
		,EJECUTIVO_CONTRATO					VARCHAR(300)
		,USUARIO							VARCHAR(100)
		,NOM_USUARIO_VENDEDOR1				VARCHAR(100)
		,NOM_PERSONA						VARCHAR(100))
		
	DECLARE
		 @ITEM								NUMERIC
        ,@ST_COD_PRODUCTO					VARCHAR(30)
		,@ST_NOM_PRODUCTO					VARCHAR(100)
		,@ST_CANTIDAD						NUMERIC(10,2)
		,@PRECIO							NUMERIC(10,2)
		,@ST_TOTAL							NUMERIC(10,2)
		,@TOTAL_NETO						NUMERIC(10,2)
		,@PORC_IVA							NUMERIC(5,2)
		,@MONTO_IVA							NUMERIC(14,2)
		,@TOTAL_CON_IVA						NUMERIC(10,2)
		,@NOM_EMPRESA						VARCHAR(100)
		,@RUT								NUMERIC
		,@DIG_VERIF							VARCHAR(1)
		,@DIRECCION							VARCHAR(100)
		,@TELEFONO							VARCHAR(100)
		,@NRO_MESES							numeric(10)
		,@FECHA_PRIMERA_GUIA_DESPACHO		DATETIME
		,@NRO_ORDEN_COMPRA					VARCHAR(100)
		,@NOM_COMUNA						VARCHAR(100)
		,@NOM_CIUDAD						VARCHAR(100)
		,@NOM_PAIS							VARCHAR(100)
		,@CENTRO_COSTO_CLIENTE				VARCHAR(100)
		,@NOM_ARRIENDO						VARCHAR(100)
		,@UBICACION_CONTRATO				VARCHAR(300)
		,@EJECUTIVO_CONTRATO				VARCHAR(300)
		,@USUARIO							VARCHAR(100)
		,@NOM_USUARIO_VENDEDOR1				VARCHAR(100)
		,@NOM_PERSONA						VARCHAR(100)	
	
	DECLARE C_ANEXO CURSOR FOR 
	SELECT DISTINCT	I.COD_PRODUCTO ST_COD_PRODUCTO
								,I.NOM_PRODUCTO ST_NOM_PRODUCTO
								,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) ST_CANTIDAD
								,I.PRECIO
								,(dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) * I.PRECIO ) ST_TOTAL
								,A.TOTAL_NETO
								,A.PORC_IVA
								,A.MONTO_IVA
								,A.TOTAL_CON_IVA
								,E.NOM_EMPRESA
								,E.RUT
								,E.DIG_VERIF
								,SU.DIRECCION
								,SU.TELEFONO + ' - ' + SU.FAX TELEFONO
								,A.NRO_MESES
								,A.FECHA_PRIMERA_GUIA_DESPACHO
								,A.NRO_ORDEN_COMPRA
								,CO.NOM_COMUNA
								,CI.NOM_CIUDAD
								,PA.NOM_PAIS
								,A.CENTRO_COSTO_CLIENTE
								,A.NOM_ARRIENDO
								,(A.UBICACION_DIRECCION +' - '+ A.UBICACION_COMUNA + ' - ' +A.UBICACION_CIUDAD)	UBICACION_CONTRATO
								,(A.EJECUTIVO_CONTACTO +' - '+ A.EJECUTIVO_TELEFONO + ' - ' +A.EJECUTIVO_MAIL)	EJECUTIVO_CONTRATO
								,U.NOM_USUARIO USUARIO
								,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = A.COD_USUARIO_VENDEDOR1) NOM_USUARIO_VENDEDOR1
								,(SELECT NOM_PERSONA FROM PERSONA WHERE COD_PERSONA = A.COD_PERSONA) NOM_PERSONA
				FROM 			ITEM_MOD_ARRIENDO I
					 			,MOD_ARRIENDO M
					 			,ARRIENDO A
					 			,PERSONA P
					 			,USUARIO U
					 			,EMPRESA E
					 			,SUCURSAL SU LEFT OUTER JOIN COMUNA CO ON SU.COD_COMUNA = CO.COD_COMUNA
                                  			 LEFT OUTER JOIN CIUDAD CI ON SU.COD_CIUDAD = CI.COD_CIUDAD
                                  			 LEFT OUTER JOIN PAIS PA ON SU.COD_PAIS = PA.COD_PAIS
				WHERE M.COD_ARRIENDO = @ve_cod_arriendo
				  AND I.COD_MOD_ARRIENDO = M.COD_MOD_ARRIENDO
				  AND A.COD_ARRIENDO = M.COD_ARRIENDO
				  AND SU.COD_SUCURSAL = A.COD_SUCURSAL
				  AND P.COD_PERSONA = A.COD_PERSONA
				  AND U.COD_USUARIO = A.COD_USUARIO
				  AND E.COD_EMPRESA = A.COD_EMPRESA
				  AND DBO.F_BODEGA_STOCK(I.COD_PRODUCTO, A.COD_BODEGA, GETDATE()) > 0
	
	SET @ITEM = 0			  
	OPEN C_ANEXO
	FETCH C_ANEXO INTO @ST_COD_PRODUCTO					
						,@ST_NOM_PRODUCTO					
						,@ST_CANTIDAD						
						,@PRECIO							
						,@ST_TOTAL							
						,@TOTAL_NETO	
						,@PORC_IVA
						,@MONTO_IVA						
						,@TOTAL_CON_IVA						
						,@NOM_EMPRESA						
						,@RUT								
						,@DIG_VERIF							
						,@DIRECCION							
						,@TELEFONO							
						,@NRO_MESES							
						,@FECHA_PRIMERA_GUIA_DESPACHO		
						,@NRO_ORDEN_COMPRA					
						,@NOM_COMUNA						
						,@NOM_CIUDAD						
						,@NOM_PAIS							
						,@CENTRO_COSTO_CLIENTE
						,@NOM_ARRIENDO		
						,@UBICACION_CONTRATO
						,@EJECUTIVO_CONTRATO
						,@USUARIO
						,@NOM_USUARIO_VENDEDOR1
						,@NOM_PERSONA
	WHILE @@FETCH_STATUS = 0 BEGIN
	SET @ITEM = @ITEM + 1
	
	INSERT INTO @TEMPO (ITEM		
				        ,ST_COD_PRODUCTO			
						,ST_NOM_PRODUCTO			
						,ST_CANTIDAD				
						,PRECIO						
						,ST_TOTAL					
						,TOTAL_NETO	
						,PORC_IVA
						,MONTO_IVA
						,TOTAL_CON_IVA				
						,NOM_EMPRESA				
						,RUT						
						,DIG_VERIF					
						,DIRECCION					
						,TELEFONO					
						,NRO_MESES					
						,FECHA_PRIMERA_GUIA_DESPACHO
						,NRO_ORDEN_COMPRA		
						,NOM_COMUNA				
						,NOM_CIUDAD				
						,NOM_PAIS				
						,CENTRO_COSTO_CLIENTE	
						,NOM_ARRIENDO			
						,UBICACION_CONTRATO		
						,EJECUTIVO_CONTRATO		
						,USUARIO				
						,NOM_USUARIO_VENDEDOR1
						,NOM_PERSONA)
	VALUES	(@ITEM				
				        ,@ST_COD_PRODUCTO					
						,@ST_NOM_PRODUCTO					
						,@ST_CANTIDAD						
						,@PRECIO							
						,@ST_TOTAL							
						,@TOTAL_NETO
						,@PORC_IVA
						,@MONTO_IVA
						,@TOTAL_CON_IVA						
						,@NOM_EMPRESA						
						,@RUT								
						,@DIG_VERIF							
						,@DIRECCION							
						,@TELEFONO							
						,@NRO_MESES							
						,@FECHA_PRIMERA_GUIA_DESPACHO		
						,@NRO_ORDEN_COMPRA					
						,@NOM_COMUNA						
						,@NOM_CIUDAD						
						,@NOM_PAIS							
						,@CENTRO_COSTO_CLIENTE
						,@NOM_ARRIENDO		
						,@UBICACION_CONTRATO
						,@EJECUTIVO_CONTRATO
						,@USUARIO
						,@NOM_USUARIO_VENDEDOR1
						,@NOM_PERSONA)												  
	
		FETCH C_ANEXO INTO 	@ST_COD_PRODUCTO					
						,@ST_NOM_PRODUCTO					
						,@ST_CANTIDAD						
						,@PRECIO							
						,@ST_TOTAL							
						,@TOTAL_NETO
						,@PORC_IVA
						,@MONTO_IVA
						,@TOTAL_CON_IVA						
						,@NOM_EMPRESA						
						,@RUT								
						,@DIG_VERIF							
						,@DIRECCION							
						,@TELEFONO							
						,@NRO_MESES							
						,@FECHA_PRIMERA_GUIA_DESPACHO		
						,@NRO_ORDEN_COMPRA					
						,@NOM_COMUNA						
						,@NOM_CIUDAD						
						,@NOM_PAIS							
						,@CENTRO_COSTO_CLIENTE
						,@NOM_ARRIENDO		
						,@UBICACION_CONTRATO
						,@EJECUTIVO_CONTRATO
						,@USUARIO
						,@NOM_USUARIO_VENDEDOR1
						,@NOM_PERSONA
	END
	CLOSE C_ANEXO
	DEALLOCATE C_ANEXO
	
	SELECT ITEM		
				        ,ST_COD_PRODUCTO			
						,ST_NOM_PRODUCTO			
						,ST_CANTIDAD				
						,PRECIO						
						,ST_TOTAL					
						,TOTAL_NETO	
						,PORC_IVA
						,MONTO_IVA
						,TOTAL_CON_IVA				
						,NOM_EMPRESA				
						,RUT						
						,DIG_VERIF					
						,DIRECCION					
						,TELEFONO					
						,NRO_MESES					
						,convert(varchar(100),FECHA_PRIMERA_GUIA_DESPACHO,103) FECHA_PRIMERA_GUIA_DESPACHO
						,NRO_ORDEN_COMPRA		
						,NOM_COMUNA				
						,NOM_CIUDAD				
						,NOM_PAIS				
						,CENTRO_COSTO_CLIENTE	
						,NOM_ARRIENDO			
						,UBICACION_CONTRATO		
						,EJECUTIVO_CONTRATO		
						,USUARIO				
						,NOM_USUARIO_VENDEDOR1
						,NOM_PERSONA
		FROM @TEMPO	
END
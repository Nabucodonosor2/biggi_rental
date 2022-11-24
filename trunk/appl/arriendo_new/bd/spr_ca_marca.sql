ALTER PROCEDURE [dbo].[spr_ca_marca](@ve_cod_mod_arriendo numeric, @ve_item varchar(4000))
AS
BEGIN
	-- @ve_item contiene cod_item1|cantidad1|..|cod_itemN|cantidadN|
	declare	@pos int,
			@cod_item_ca varchar(2000),
			@cant_item_ca int

	declare @TEMPO TABLE 	 
				(COD_ARRIENDO NUMERIC NOT NULL,
				NOM_EMPRESA VARCHAR(100) NOT NULL,
				NOM_PERSONA VARCHAR(100) NOT NULL,
				NRO_ORDEN_COMPRA VARCHAR(20) NULL,
				ITEM VARCHAR(10) NOT NULL,
				COD_PRODUCTO VARCHAR(30) NOT NULL,
				NOM_PRODUCTO VARCHAR(100) NOT NULL,
				NOM_EMPRESA_EMISOR VARCHAR(100) NOT NULL,
				DIR_EMPRESA VARCHAR(100) NOT NULL,
				TEL_EMPRESA VARCHAR(100) NOT NULL,
				FAX_EMPRESA VARCHAR(100) NOT NULL,
				MAIL_EMPRESA VARCHAR(100) NOT NULL,
				CIUDAD_EMPRESA VARCHAR(100) NOT NULL,
				PAIS_EMPRESA VARCHAR(100) NOT NULL,
				NOM_ARRIENDO VARCHAR(100) NOT NULL,
				UBICACION_DIRECCION VARCHAR(100) NULL,
				UBICACION_COMUNA VARCHAR(100) NULL,
				UBICACION_CIUDAD VARCHAR(100) NULL,
				CENTRO_COSTO_CLIENTE VARCHAR(100) NULL,
				EJECUTIVO_CONTACTO VARCHAR(100) NULL)	

while (@ve_item<>'')
begin	
			set @pos = CHARINDEX('|', @ve_item) 
			set @cod_item_ca = substring(@ve_item, 1, @pos - 1) 
			set @ve_item = substring(@ve_item, @pos + 1, len(@ve_item) - @pos)
		
			set @pos = CHARINDEX('|', @ve_item) 
			set @cant_item_ca = convert(int, substring(@ve_item, 1, @pos - 1))
			set @ve_item = substring(@ve_item, @pos + 1, len(@ve_item) - @pos)
			
			while (@cant_item_ca<>0)
			begin
				insert into @TEMPO
				  SELECT A.COD_ARRIENDO
					  ,E.NOM_EMPRESA
					  ,P.NOM_PERSONA
					  ,A.NRO_ORDEN_COMPRA
					  ,IMA.ITEM
					  ,IMA.COD_PRODUCTO
					  ,IMA.NOM_PRODUCTO
					  ,dbo.f_get_parametro(6) NOM_EMPRESA_EMISOR
					  ,dbo.f_get_parametro(10) DIR_EMPRESA
					  ,dbo.f_get_parametro(11) TEL_EMPRESA
					  ,dbo.f_get_parametro(12) FAX_EMPRESA
					  ,dbo.f_get_parametro(13) MAIL_EMPRESA
					  ,dbo.f_get_parametro(14) CIUDAD_EMPRESA
					  ,dbo.f_get_parametro(15) PAIS_EMPRESA	  
					  ,A.NOM_ARRIENDO
					  ,A.UBICACION_DIRECCION
					  ,A.UBICACION_COMUNA
					  ,A.UBICACION_CIUDAD
					  ,A.CENTRO_COSTO_CLIENTE
					  ,A.EJECUTIVO_CONTACTO	
				FROM ITEM_MOD_ARRIENDO IMA, MOD_ARRIENDO MA, ARRIENDO A,
						EMPRESA E, PERSONA P
				WHERE IMA.COD_MOD_ARRIENDO = @ve_cod_mod_arriendo AND
				COD_ITEM_MOD_ARRIENDO = @cod_item_ca AND
				MA.COD_MOD_ARRIENDO = IMA.COD_MOD_ARRIENDO AND
				A.COD_ARRIENDO = MA.COD_ARRIENDO AND
				A.COD_EMPRESA = E.COD_EMPRESA AND
				P.COD_PERSONA = A.COD_PERSONA

		     		set  @cant_item_ca = @cant_item_ca - 1
			end	
end
	SELECT COD_ARRIENDO,
			NOM_EMPRESA,
			NOM_PERSONA,
			NRO_ORDEN_COMPRA,
			ITEM,
			COD_PRODUCTO,
			NOM_PRODUCTO,
			NOM_EMPRESA_EMISOR,
			DIR_EMPRESA,
			TEL_EMPRESA,
			FAX_EMPRESA,
			MAIL_EMPRESA,
			CIUDAD_EMPRESA,
			PAIS_EMPRESA,
			NOM_ARRIENDO,
			UBICACION_DIRECCION,
			UBICACION_COMUNA,
			UBICACION_CIUDAD,
			CENTRO_COSTO_CLIENTE,
			EJECUTIVO_CONTACTO
	FROM @TEMPO
	
END
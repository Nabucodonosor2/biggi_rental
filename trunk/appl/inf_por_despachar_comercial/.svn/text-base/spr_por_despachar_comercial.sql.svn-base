---------------------- spr_por_despachar_comercial ------------------------
ALTER PROCEDURE [dbo].[spr_por_despachar_comercial](@ve_cod_usuario numeric)
AS
/*
Busca los equipos por despachar del usuario @ve_cod_usuario
@ve_cod_usuario: 0 indica todos
*/
BEGIN
	-- declara tabla temporal
	declare @TEMPO TABLE 	 (
		    cod_producto			varchar (30),
			nom_producto			varchar(100),
			cantidad_por_despachar numeric (10,2))		

	-- declara variables
	declare  	
			@NOM_PRODUCTO				varchar(100)
			,@COD_PRODUCTO				varchar (30)
			,@CANTIDAD_POR_DESPACHAR	T_CANTIDAD 
			,@ES_COMPUESTO				VARCHAR(1)
			,@vl_CANTIDAD				T_CANTIDAD
			,@vl_COD_PRODUCTO_HIJO		NUMERIC
			,@vl_NOM_PRODUCTO			VARCHAR(100)
			,@COD_NOTA_VENTA			NUMERIC
			
			
	-- declara cursor
	declare C_CURSOR cursor for
	select inv.COD_PRODUCTO
        ,inv.NOM_PRODUCTO
        ,SUM(dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default)) CANTIDAD
        ,P.ES_COMPUESTO
        ,INV.COD_NOTA_VENTA 
		from    BIGGI.dbo.NOTA_VENTA NV, BIGGI.dbo.ITEM_NOTA_VENTA INV, BIGGI.dbo.PRODUCTO P, PRODUCTO PB
		where    NV.COD_NOTA_VENTA = INV.COD_NOTA_VENTA AND
            	P.COD_PRODUCTO = INV.COD_PRODUCTO AND
                dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default) > 0 and
                NV.cod_estado_nota_venta <> 3    -- Anulada
                and P.COD_PRODUCTO = PB.COD_PRODUCTO
		and      PB.maneja_inventario = 'S'
		group by inv.COD_PRODUCTO,inv.NOM_PRODUCTO,P.ES_COMPUESTO,INV.COD_NOTA_VENTA
		ORDER BY COD_PRODUCTO
		
	-- abre cursor
	OPEN C_CURSOR
	FETCH C_CURSOR INTO 
			
			@COD_PRODUCTO
			,@NOM_PRODUCTO
			,@CANTIDAD_POR_DESPACHAR
			,@ES_COMPUESTO	
			,@COD_NOTA_VENTA							

	-- recorre cursor		
	WHILE @@FETCH_STATUS = 0
	BEGIN
		
		insert into @TEMPO
				(COD_PRODUCTO
				,NOM_PRODUCTO
				,CANTIDAD_POR_DESPACHAR)		

			values
				(@COD_PRODUCTO
				,@NOM_PRODUCTO
				,@CANTIDAD_POR_DESPACHAR)
			

		-- debe hacer insert de los compuestos
		if (@ES_COMPUESTO = 'S')
		begin
			select 	@vl_CANTIDAD = CANTIDAD
					,@vl_COD_PRODUCTO_HIJO = COD_PRODUCTO_HIJO
					,@vl_NOM_PRODUCTO = P.NOM_PRODUCTO 
			from PRODUCTO_COMPUESTO PC, 
				 PRODUCTO P
			where PC.COD_PRODUCTO = @COD_PRODUCTO and 
				  COD_PRODUCTO_HIJO = P.COD_PRODUCTO
			
	insert into @TEMPO
				(cod_producto
				,nom_producto
				,cantidad_por_despachar)		
		values
				(@vl_COD_PRODUCTO_HIJO
				,'***'+@vl_NOM_PRODUCTO
				,@CANTIDAD_POR_DESPACHAR * @vl_CANTIDAD)
		end
		FETCH C_CURSOR INTO 
			 
			@COD_PRODUCTO
			,@NOM_PRODUCTO
			,@CANTIDAD_POR_DESPACHAR
			,@ES_COMPUESTO	
			,@COD_NOTA_VENTA	
	END
	CLOSE C_CURSOR
	DEALLOCATE C_CURSOR
	
	if (@ve_cod_usuario=0)
		select COD_PRODUCTO
				,NOM_PRODUCTO
				,CANTIDAD_POR_DESPACHAR
		from @TEMPO T,USUARIO U
		where  CANTIDAD_POR_DESPACHAR > 0
		GROUP BY COD_PRODUCTO,NOM_PRODUCTO,CANTIDAD_POR_DESPACHAR 
	else
		select NV.COD_NOTA_VENTA
			,convert(varchar(20), NV.FECHA_NOTA_VENTA, 3) FECHA_NOTA_VENTA
			,E.NOM_EMPRESA
			,NV.COD_USUARIO_VENDEDOR1
			,U.NOM_USUARIO
			,U.INI_USUARIO
			,INV.ITEM
			,INV.COD_PRODUCTO
			,INV.NOM_PRODUCTO
			,INV.CANTIDAD
			,dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default) CANTIDAD_POR_DESPACHAR
			,convert(varchar(20), NV.FECHA_ENTREGA, 3) FECHA_ENTREGA
			,case 
				when ((DATEDIFF ( day , NV.FECHA_ENTREGA , getdate())) < 0) then 0 
				else DATEDIFF ( day , NV.FECHA_ENTREGA , getdate())
			end  DIAS_ATRASO
			,P.ES_COMPUESTO
			,dbo.f_format_date(getdate(), 3) FECHA
			,dbo.f_get_parametro(6) NOM_EMPRESA_EMISOR
			,dbo.f_get_parametro(10) DIR_EMPRESA
			,dbo.f_get_parametro(11) TEL_EMPRESA
			,dbo.f_get_parametro(12) FAX_EMPRESA
			,dbo.f_get_parametro(13) MAIL_EMPRESA
			,dbo.f_get_parametro(14) CIUDAD_EMPRESA
			,dbo.f_get_parametro(15) PAIS_EMPRESA
			,dbo.f_get_parametro(25) SITIO_WEB_EMPRESA
			,dbo.f_get_parametro(20) RUT_EMPRESA 
	from	NOTA_VENTA NV, ITEM_NOTA_VENTA INV, PRODUCTO P, EMPRESA E, USUARIO U
	where	NV.COD_NOTA_VENTA = INV.COD_NOTA_VENTA AND
			P.COD_PRODUCTO = INV.COD_PRODUCTO AND
			E.COD_EMPRESA = NV.COD_EMPRESA  AND
			NV.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO AND
			and U.NOM_USUARIO IN (select u.NOM_USUARIO
										from biggi.dbo.USUARIO bu , USUARIO u
										where bu.NOM_USUARIO = u.NOM_USUARIO)
			dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default) > 0 and
			NV.cod_estado_nota_venta <> 3	-- Anulada
		
			
			
			
END

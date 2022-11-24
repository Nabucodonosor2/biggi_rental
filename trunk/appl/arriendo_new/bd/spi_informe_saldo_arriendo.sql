ALTER PROCEDURE [dbo].[spi_informe_saldo_arriendo](@ve_cod_arriendo		numeric
													,@ve_tipo_mod_arriendo	varchar(20))
AS
BEGIN
	declare @TEMPO TABLE(
		COD_MOD_ARRIENDO			numeric
		,ITEM						varchar(4)
		,COD_PRODUCTO				varchar(30)
		,NOM_PRODUCTO				varchar(100)
		,CANTIDAD					numeric(10,2)
		,CANTIDAD_DESPACHADA		numeric(10,2)
		,CANTIDAD_POR_DESPACHAR		numeric(10,2)
	)
	
	declare
		@COD_MOD_ARRIENDO			numeric
		,@ITEM						varchar(4)
		,@COD_PRODUCTO				varchar(30)
		,@NOM_PRODUCTO				varchar(100)
		,@CANTIDAD					numeric(10,2)
		,@CANTIDAD_DESPACHADA		numeric(10,2)
		,@CANTIDAD_POR_DESPACHAR	numeric(10,2)
	
	if(@ve_tipo_mod_arriendo = 'AGREGAR') begin
		declare c_despachar cursor for
		SELECT IMA.COD_MOD_ARRIENDO
				,IMA.ITEM
				,IMA.COD_PRODUCTO
				,IMA.NOM_PRODUCTO
				,IMA.CANTIDAD
				,IMA.CANTIDAD - dbo.f_arr_cant_por_despachar(IMA.COD_ITEM_MOD_ARRIENDO, default) CANTIDAD_DESPACHADA
				,dbo.f_arr_cant_por_despachar(IMA.COD_ITEM_MOD_ARRIENDO, default) CANTIDAD_POR_DESPACHAR
		FROM MOD_ARRIENDO MA, ITEM_MOD_ARRIENDO IMA
		WHERE MA.COD_ARRIENDO = @ve_cod_arriendo
		AND MA.TIPO_MOD_ARRIENDO = @ve_tipo_mod_arriendo
		AND MA.COD_ESTADO_MOD_ARRIENDO = 2
		AND MA.COD_MOD_ARRIENDO = IMA.COD_MOD_ARRIENDO
		
		open c_despachar 
		fetch c_despachar into @COD_MOD_ARRIENDO, @ITEM,@COD_PRODUCTO, @NOM_PRODUCTO, @CANTIDAD
							  ,@CANTIDAD_DESPACHADA, @CANTIDAD_POR_DESPACHAR
		while @@fetch_status = 0 begin
		
		if(@CANTIDAD_POR_DESPACHAR <> 0)
			INSERT INTO @TEMPO
			VALUES (@COD_MOD_ARRIENDO
					,@ITEM,@COD_PRODUCTO
					,@NOM_PRODUCTO
					,@CANTIDAD
					,@CANTIDAD_DESPACHADA
					,@CANTIDAD_POR_DESPACHAR)
		
			fetch c_despachar into @COD_MOD_ARRIENDO, @ITEM,@COD_PRODUCTO, @NOM_PRODUCTO, @CANTIDAD
									,@CANTIDAD_DESPACHADA, @CANTIDAD_POR_DESPACHAR
		end
		close c_despachar
		deallocate c_despachar
	end
	if (@ve_tipo_mod_arriendo = 'ELIMINAR')begin
		declare c_recibir cursor for
		SELECT	IMA.COD_MOD_ARRIENDO
				,IMA.ITEM
				,IMA.COD_PRODUCTO
				,IMA.NOM_PRODUCTO
				,IMA.CANTIDAD
				,IMA.CANTIDAD - dbo.f_arr_cant_por_recepcionar(IMA.COD_ITEM_MOD_ARRIENDO, default) CANTIDAD_RECIBIDA
				,dbo.f_arr_cant_por_recepcionar(IMA.COD_ITEM_MOD_ARRIENDO, default) CANTIDAD_POR_RECIBIR
		FROM ITEM_MOD_ARRIENDO IMA, MOD_ARRIENDO MA, ARRIENDO A
		WHERE MA.COD_ARRIENDO = @ve_cod_arriendo
		AND MA.TIPO_MOD_ARRIENDO = @ve_tipo_mod_arriendo
		AND MA.COD_ESTADO_MOD_ARRIENDO = 2
		AND IMA.COD_MOD_ARRIENDO = MA.COD_MOD_ARRIENDO
		AND A.COD_ARRIENDO = MA.COD_ARRIENDO
		
		open c_recibir 
		fetch c_recibir into @COD_MOD_ARRIENDO, @ITEM,@COD_PRODUCTO, @NOM_PRODUCTO, @CANTIDAD
							  ,@CANTIDAD_DESPACHADA, @CANTIDAD_POR_DESPACHAR
		while @@fetch_status = 0 begin
		
		if(@CANTIDAD_POR_DESPACHAR <> 0)
			INSERT INTO @TEMPO
			VALUES (@COD_MOD_ARRIENDO
					,@ITEM,@COD_PRODUCTO
					,@NOM_PRODUCTO
					,@CANTIDAD
					,@CANTIDAD_DESPACHADA
					,@CANTIDAD_POR_DESPACHAR)
		
			fetch c_recibir into @COD_MOD_ARRIENDO, @ITEM,@COD_PRODUCTO, @NOM_PRODUCTO, @CANTIDAD
									,@CANTIDAD_DESPACHADA, @CANTIDAD_POR_DESPACHAR
		end
		close c_recibir
		deallocate c_recibir
	end	 
	
	select * from @TEMPO
END

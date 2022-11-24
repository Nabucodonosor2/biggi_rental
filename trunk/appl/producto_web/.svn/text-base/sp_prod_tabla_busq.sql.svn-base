ALTER PROCEDURE [dbo].[sp_prod_tabla_busq]
AS 
BEGIN

DECLARE C_PROD CURSOR FOR

SELECT COD_PRODUCTO
	  ,NOM_PRODUCTO
FROM PRODUCTO
where dbo.f_prod_valido (COD_PRODUCTO) = 'S'
	
declare 
	@vc_cod_producto				varchar(100)
	,@vc_nom_producto				varchar(100)
	,@vc_nom_atributo_producto		varchar(1000)
	,@vc_cod_atributo_producto		numeric
		
OPEN C_PROD
FETCH C_PROD INTO @vc_cod_producto, @vc_nom_producto
WHILE @@FETCH_STATUS = 0 BEGIN
	--borra lo del producto
	delete producto_busqueda
	where COD_PRODUCTO = @vc_cod_producto

	--se inserta como palabra el cod_producto
	insert into producto_busqueda
		(COD_PRODUCTO
		,PALABRA
		,CAMPO_UBICACION
		,COD_ATRIBUTO_PRODUCTO
		)
	values
		(@vc_cod_producto
		,@vc_cod_producto
		,'COD_PRODUCTO'
		,null
		)
		
	--buscamos las pabras en el nom_producto
	insert into producto_busqueda
		(COD_PRODUCTO
		,PALABRA
		,CAMPO_UBICACION
		,COD_ATRIBUTO_PRODUCTO
		)
	select @vc_cod_producto
			,item
			,'NOM_PRODUCTO'
			,null
	from dbo.f_prod_busq_palabra(@vc_nom_producto)
	
	--ATRIBUTOS
	DECLARE C_ATRIB CURSOR FOR 
	SELECT nom_atributo_producto
			,cod_atributo_producto
	FROM atributo_producto
	where cod_producto = @vc_cod_producto

	OPEN C_ATRIB
	FETCH C_ATRIB INTO @vc_nom_atributo_producto, @vc_cod_atributo_producto
	WHILE @@FETCH_STATUS = 0 BEGIN
		insert into producto_busqueda
			(COD_PRODUCTO
			,PALABRA
			,CAMPO_UBICACION
			,COD_ATRIBUTO_PRODUCTO
			)
		select @vc_cod_producto
				,item
				,'NOM_ATRIBUTO'
				,@vc_cod_atributo_producto
		from dbo.f_prod_busq_palabra(@vc_nom_atributo_producto)
	
		FETCH C_ATRIB INTO @vc_nom_atributo_producto, @vc_cod_atributo_producto
	END
	CLOSE C_ATRIB
	DEALLOCATE C_ATRIB

	
	FETCH C_PROD INTO @vc_cod_producto, @vc_nom_producto
END
CLOSE C_PROD
DEALLOCATE C_PROD

END
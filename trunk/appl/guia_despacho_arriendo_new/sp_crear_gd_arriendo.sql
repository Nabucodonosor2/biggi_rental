-------------------------------- sp_crear_gd_arriendo --------------------
CREATE PROCEDURE sp_crear_gd_arriendo(@ve_cod_mod_arriendo numeric, @ve_cod_usuario numeric)
AS
BEGIN
	DECLARE
		@vl_cod_empresa			numeric
		,@vl_cod_sucursal_despacho	numeric
		,@vl_cod_persona		numeric
		,@vl_referencia			varchar(100)
		,@vl_nro_orden_compra		varchar(20)
		,@vl_obs			varchar(5000) -- es un text, no se pueden declarar variables text, para usarlas de forma local
		,@K_DESPACHO_RENTAL		numeric
	
	set @K_DESPACHO_RENTAL = 5

	--	obtiene los datos del ARRIENDO
	select @vl_cod_empresa = a.cod_empresa
	      ,@vl_cod_sucursal_despacho = a.cod_sucursal
	      ,@vl_cod_persona = a.cod_persona
	      ,@vl_referencia = a.referencia
	      ,@vl_nro_orden_compra = a.nro_orden_compra
	      ,@vl_obs = a.obs
	from mod_arriendo m, arriendo a
	where m.cod_mod_arriendo = @ve_cod_mod_arriendo
	  and a.COD_ARRIENDO = m.COD_ARRIENDO

	-- crea la GD
	execute spu_guia_despacho 
		'INSERT' 
		,NULL -- cod_guia_despacho = identity
		,NULL -- cod_usuario_impresion
		,@ve_cod_usuario 
		,NULL -- nro_guia_despacho		
		,1 -- cod_estado_doc_sii = emitida
		,@vl_cod_empresa 
		,@vl_cod_sucursal_despacho
		,@vl_cod_persona
		,@vl_referencia 
		,@vl_nro_orden_compra
		,@vl_obs -- obs
		,NULL -- retirado_por
		,NULL -- rut_retirado_por
		,NULL -- dig_verif_retirado_por
		,NULL -- guia_transporte
		,NULL -- patente
		,NULL -- cod_factura
		,NULL -- cod_bodega
		,@K_DESPACHO_RENTAL -- cod_tipo_guia_despacho = arriendo
		,@ve_cod_mod_arriendo 
		,NULL -- motivo_anula
		,NULL -- cod_usuario_anula
		,6	  -- cod_indicador_arriendo = otros traslados no venta
END

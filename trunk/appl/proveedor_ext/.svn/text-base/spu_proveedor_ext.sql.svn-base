-------------------- spu_region ---------------------------------
CREATE PROCEDURE [dbo].[spu_proveedor_ext](@ve_operacion varchar(20)
										 ,@ve_cod_proveedor_ext numeric
										 ,@ve_nom_proveedor_ext varchar(100)=NULL
										 ,@ve_alias_proveedor_ext varchar(100)=NULL
										 ,@ve_web_site varchar(100)=NULL
										 ,@ve_direccion varchar(100)=NULL
										 ,@ve_cod_ciudad numeric = NULL
										 ,@ve_cod_pais numeric = NULL
										 ,@ve_telefono varchar(100)
										 ,@ve_fax varchar(100)
										 ,@ve_post_office_box varchar(100)
										 ,@ve_obs text
										 ,@ve_cod_proveedor_ext_4d varchar(100)
										 ,@ve_nom_ciudad	varchar(100)
										 ,@ve_nom_pais	varchar(100))
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into proveedor_ext (nom_proveedor_ext
								  ,alias_proveedor_ext
								  ,web_site
								  ,direccion
								  ,cod_ciudad
								  ,cod_pais
								  ,telefono
								  ,fax
								  ,post_office_box
								  ,obs
								  ,cod_proveedor_ext_4d
								  ,nom_ciudad
								  ,nom_pais)
						values (@ve_nom_proveedor_ext
							   ,@ve_alias_proveedor_ext
							   ,@ve_web_site
							   ,@ve_direccion
							   ,@ve_cod_ciudad
							   ,@ve_cod_pais
							   ,@ve_telefono
							   ,@ve_fax
							   ,@ve_post_office_box
							   ,@ve_obs
							   ,@ve_cod_proveedor_ext_4d
							   ,@ve_nom_ciudad
							    @ve_nom_pais)
	end 
	if (@ve_operacion='UPDATE') begin
		update proveedor_ext 
		set nom_proveedor_ext 		= @ve_nom_proveedor_ext
			,alias_proveedor_ext 	= @ve_alias_proveedor_ext
			,web_site 				= @ve_web_site
			,direccion 				= @ve_direccion
			,cod_ciudad 			= @ve_cod_ciudad
			,cod_pais 				= @ve_cod_pais
			,telefono 				= @ve_telefono
			,fax 					= @ve_fax
			,post_office_box 		= @ve_post_office_box
			,obs 					= @ve_obs
			,cod_proveedor_ext_4d 	= @ve_cod_proveedor_ext_4d
			,nom_ciudad 			= @ve_nom_ciudad
			,nom_pais				= @ve_nom_pais
	    where cod_proveedor_ext 	= @ve_cod_proveedor_ext
	end
	else if (@ve_operacion='DELETE') begin
		delete proveedor_ext 
    	where cod_proveedor_ext = @ve_cod_proveedor_ext
	end
END
go
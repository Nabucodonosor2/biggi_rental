ALTER PROCEDURE [dbo].[spu_contacto] (@ve_operacion varchar(20)
								,@ve_cod_contacto numeric
								,@ve_nom_contacto varchar(100) = NULL
								,@ve_rut numeric = NULL
								,@ve_dig_verif varchar(1) = NULL
								,@ve_direccion varchar(100) = NULL
								,@ve_cod_ciudad numeric = NULL
								,@ve_cod_comuna numeric = NULL
								,@ve_origen_contacto varchar(20) = NULL
								,@ve_nom_ciudad varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into contacto (nom_contacto, rut, dig_verif, direccion, cod_ciudad, cod_comuna,origen_contacto,nom_ciudad)
		values (@ve_nom_contacto, @ve_rut, @ve_dig_verif, @ve_direccion, @ve_cod_ciudad, @ve_cod_comuna,@ve_origen_contacto,@ve_nom_ciudad)
	end 
	if (@ve_operacion='UPDATE') begin
		update contacto 
		set nom_contacto = @ve_nom_contacto
			,rut = @ve_rut 
			,dig_verif = @ve_dig_verif
			,direccion = @ve_direccion
			,cod_ciudad = @ve_cod_ciudad 
			,cod_comuna = @ve_cod_comuna
	    where cod_contacto = @ve_cod_contacto
	end	
END

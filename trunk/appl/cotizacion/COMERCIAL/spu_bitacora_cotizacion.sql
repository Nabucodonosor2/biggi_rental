----------------------- spu_bitacora_cotizacion -------------------------------
alter PROCEDURE spu_bitacora_cotizacion(@ve_operacion				varchar(20)
									, @ve_cod_bitacora_cotizacion	numeric
									, @ve_cod_usuario				numeric=NULL
									, @ve_cod_cotizacion			numeric=NULL
									, @ve_cod_accion_cotizacion		numeric=NULL
									, @ve_contacto					varchar(100)=NULL
									, @ve_telefono					varchar(100)=NULL
									, @ve_mail						varchar(100)=NULL
									, @ve_glosa						text=NULL
									, @ve_tiene_compromiso			varchar(1)=NULL
									, @ve_fecha_compromiso			datetime=NULL
									, @ve_glosa_compromiso			text=NULL
									, @ve_compromiso_realizado		varchar(1)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into BITACORA_COTIZACION
			(FECHA_BITACORA
			,COD_USUARIO
			,COD_COTIZACION
			,COD_ACCION_COTIZACION
			,CONTACTO
			,TELEFONO
			,MAIL
			,GLOSA
			,TIENE_COMPROMISO
			,FECHA_COMPROMISO
			,GLOSA_COMPROMISO
			,COMPROMISO_REALIZADO
			,FECHA_REALIZADO
			,COD_USUARIO_REALIZADO)
		values
			(getdate()
			,@ve_cod_usuario
			,@ve_cod_cotizacion
			,@ve_cod_accion_cotizacion
			,@ve_contacto
			,@ve_telefono
			,@ve_mail
			,@ve_glosa
			,@ve_tiene_compromiso
			,@ve_fecha_compromiso
			,@ve_glosa_compromiso
			,null
			,null
			,null) 	
	end 
	if (@ve_operacion='UPDATE') begin
		declare
			@fecha_realizado		datetime
			,@cod_usuario_realizado	numeric

		if (@ve_compromiso_realizado = 'S') begin
			set @fecha_realizado = getdate()
			set @cod_usuario_realizado = @ve_cod_usuario
		end 
		else begin
			set @fecha_realizado = null
			set @cod_usuario_realizado = null
		end 
		update BITACORA_COTIZACION
		set COD_ACCION_COTIZACION = @ve_cod_accion_cotizacion
			,CONTACTO = @ve_contacto
			,TELEFONO = @ve_telefono
			,MAIL = @ve_mail
			,GLOSA = @ve_glosa
			,TIENE_COMPROMISO = @ve_tiene_compromiso
			,FECHA_COMPROMISO = @ve_fecha_compromiso
			,GLOSA_COMPROMISO = @ve_glosa_compromiso
			,COMPROMISO_REALIZADO = @ve_compromiso_realizado
			,FECHA_REALIZADO = @fecha_realizado
			,COD_USUARIO_REALIZADO = @cod_usuario_realizado
	    where COD_BITACORA_COTIZACION = @ve_cod_bitacora_cotizacion
	end
	else if (@ve_operacion='DELETE') begin
		delete BITACORA_COTIZACION
    	where COD_BITACORA_COTIZACION = @ve_cod_bitacora_cotizacion
	end
END


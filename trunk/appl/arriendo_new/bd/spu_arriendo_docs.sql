------------------  spu_arriendo_docs  --------------------------
CREATE PROCEDURE spu_arriendo_docs(@ve_operacion			varchar(20),
								   @ve_cod_arriendo_docs	numeric,
								   @ve_cod_arriendo			numeric=NULL,
								   @ve_cod_usuario			numeric=NULL, 
								   @ve_ruta_archivo			varchar(500)=NULL,
								   @ve_nom_archivo			varchar(100)=NULL, 
								   @ve_obs					text=NULL)
AS
BEGIN
	if(@ve_operacion='INSERT')begin
			insert into arriendo_docs (
					COD_ARRIENDO, 
					COD_USUARIO, 
					RUTA_ARCHIVO,
					NOM_ARCHIVO, 
					FECHA_REGISTRO, 
					OBS)
			values(@ve_cod_arriendo, 
				   @ve_cod_usuario, 
				   @ve_ruta_archivo, 
				   @ve_nom_archivo, 
				   GETDATE(), 
				   @ve_obs)
	end
	else if (@ve_operacion='UPDATE')begin						
			update arriendo_docs
			set COD_ARRIENDO = @ve_cod_arriendo, 
				COD_USUARIO = @ve_cod_usuario, 
				RUTA_ARCHIVO = @ve_ruta_archivo,
				NOM_ARCHIVO = @ve_nom_archivo,  
				OBS  = @ve_obs
			where cod_arriendo_docs = @ve_cod_arriendo_docs
	end 
	else if (@ve_operacion='DELETE')begin
		delete arriendo_docs
		where cod_arriendo_docs = @ve_cod_arriendo_docs
	end	
END
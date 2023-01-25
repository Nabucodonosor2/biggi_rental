-------------------- spu_faprov ---------------------------------
ALTER PROCEDURE spu_faprov
			(@ve_operacion					varchar(20)
			,@ve_cod_faprov					numeric
			,@ve_cod_usuario				numeric		= NULL
			,@ve_cod_empresa				numeric		= NULL
			,@ve_cod_tipo_faprov			numeric		= NULL
			,@ve_cod_estado_faprov			numeric		= NULL
			,@ve_nro_faprov					numeric		= NULL
			,@ve_fecha_faprov				varchar(10)	= NULL
			,@ve_total_neto					T_PRECIO	= NULL
			,@ve_monto_iva					T_PRECIO	= NULL
			,@ve_total_con_iva				T_PRECIO	= NULL
			,@ve_cod_usuario_anula			numeric		= NULL
			,@ve_motivo_anula				varchar(100)= NULL
			,@ve_origen_faprov				varchar(20)= NULL
			,@ve_cod_cuenta_compra			numeric		= NULL
			,@ws_origen						varchar(50)	= NULL
			,@ve_es_normalizacion			T_SI_NO		= NULL)

AS
BEGIN

	declare		@kl_cod_estado_faprov_anulada numeric,
				@vl_cod_usuario_anula numeric,
				@vl_cod_estado_faprov numeric
	
	if(@ve_es_normalizacion IS NULL)
		set @ve_es_normalizacion = 'N'

	set @kl_cod_estado_faprov_anulada = 5  --- estado de la faprov = anulada


		if (@ve_operacion='UPDATE') 
			begin
				UPDATE faprov		
				SET		
							cod_empresa					=	@ve_cod_empresa	
							,cod_tipo_faprov			=	@ve_cod_tipo_faprov
							,cod_estado_faprov			=	@ve_cod_estado_faprov	
							,nro_faprov					=	@ve_nro_faprov	
							,fecha_faprov				=	dbo.to_date(@ve_fecha_faprov)
							,total_neto					=	@ve_total_neto	
							,monto_iva					=	@ve_monto_iva		
							,total_con_iva				=	@ve_total_con_iva
							,cod_cuenta_compra			=	@ve_cod_cuenta_compra
							,es_normalizacion			=	@ve_es_normalizacion
			
				WHERE cod_faprov = @ve_cod_faprov
				if (@ve_cod_estado_faprov = @kl_cod_estado_faprov_anulada) and (@vl_cod_usuario_anula is NULL) -- estado de la faprov = anulada 
					update faprov
					set fecha_anula			= getdate ()
						,motivo_anula		= @ve_motivo_anula			
						,cod_usuario_anula	= @ve_cod_usuario_anula				
					where cod_faprov  = @ve_cod_faprov
			end
		else if (@ve_operacion='INSERT') 
			begin
				declare
					@vl_count	numeric
				
				/*Se verifica que no exista el nro_faprov ingresada por procedimiento*/
				if(@ws_origen IS NOT NULL)BEGIN
					SELECT @vl_count = COUNT(*)
					FROM FAPROV
					WHERE NRO_FAPROV = @ve_nro_faprov
					AND COD_EMPRESA = @ve_cod_empresa
					
					if(@vl_count > 0)
						return
				END
				
				insert into faprov
					(fecha_registro
					,fecha_faprov
					,cod_usuario
					,cod_empresa
					,cod_tipo_faprov
					,cod_estado_faprov
					,nro_faprov
					,total_neto
					,monto_iva
					,total_con_iva
					,origen_faprov
					,cod_cuenta_compra
					,ws_origen
					,es_normalizacion)
				values 
					(getdate()
					,dbo.to_date(@ve_fecha_faprov)
					,@ve_cod_usuario	
					,@ve_cod_empresa	
					,@ve_cod_tipo_faprov	
					,@ve_cod_estado_faprov
					,@ve_nro_faprov
					,@ve_total_neto		
					,@ve_monto_iva		
					,@ve_total_con_iva
					,@ve_origen_faprov
					,@ve_cod_cuenta_compra
					,@ws_origen
					,@ve_es_normalizacion)
			end 
		else if (@ve_operacion='DELETE_ALL') 
				begin
					delete item_faprov
    				where cod_faprov = @ve_cod_faprov 
					
					delete faprov
					where cod_faprov = @ve_cod_faprov
				end 
		else if (@ve_operacion='ASIGNAR') 
				begin
					UPDATE faprov		
					SET		cod_cuenta_compra = @ve_cod_usuario	-- Para esta opcion 'ASIGNAR' se sobreusa el parametro de entrada @ve_cod_usuario para indicar el cod_cuenta_compra
    				where cod_faprov = @ve_cod_faprov 
				end
		else if (@ve_operacion='CAMBIO_ESTADO')begin
			SELECT @vl_cod_estado_faprov = COD_ESTADO_FAPROV
			FROM FAPROV
			WHERE COD_FAPROV = @ve_cod_faprov
			
			if(@vl_cod_estado_faprov = 1)BEGIN --Ingresada
				UPDATE FAPROV
				SET COD_ESTADO_FAPROV = 2 --Aprobada
				WHERE COD_FAPROV = @ve_cod_faprov
			END
		end		 
END

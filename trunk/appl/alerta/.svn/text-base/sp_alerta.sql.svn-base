------------------    sp_alerta ----------------------------
alter PROCEDURE sp_alerta(@ve_cod_usuario		numeric)
AS
BEGIN
	declare
		@vc_cod_alerta			numeric
		,@vl_gf_por_autorizar	varchar(1)
		
	declare @TEMPO TABLE     
	   (COD_ALERTA			numeric
		,ALERTA_MENSAJE		varchar(100)
		)


	DECLARE C_ALERTA CURSOR FOR  
	SELECT cod_alerta
	from alerta_usuario
	where cod_usuario = @ve_cod_usuario

	OPEN C_ALERTA
	FETCH C_ALERTA INTO @vc_cod_alerta
	WHILE @@FETCH_STATUS = 0 BEGIN	
		if (@vc_cod_alerta = 1)	begin -- OC de GF po autorizar
			select @vl_gf_por_autorizar = dbo.f_oc_GF_por_autorizar()
			if (@vl_gf_por_autorizar = 'S')
				insert into @TEMPO values (@vc_cod_alerta, 'Ud. tiene GF por autorizar')
		end
		
		FETCH C_ALERTA INTO @vc_cod_alerta
	END
	CLOSE C_ALERTA
	DEALLOCATE C_ALERTA

	select * from @TEMPO
END
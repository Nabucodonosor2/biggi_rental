CREATE PROCEDURE [dbo].[spu_ano](@ve_operacion varchar(20), @ve_cod_ano numeric,@ve_ano numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into ANO (ano)
			 	values (@ve_ano)
	end 
	if (@ve_operacion='UPDATE') begin
		update ANO 
		set ano = @ve_ano
		where cod_ano = @ve_cod_ano
	end
	else if (@ve_operacion='DELETE') begin
		delete ANO 
		where cod_ano = @ve_cod_ano
	end	
END
go			
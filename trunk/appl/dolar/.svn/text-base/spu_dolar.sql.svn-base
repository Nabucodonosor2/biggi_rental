CREATE PROCEDURE [dbo].[spu_dolar](@ve_operacion varchar(20)
								    ,@ve_cod_dolar_todoinox numeric
									,@ve_cod_mes numeric=NULL
									,@ve_ano numeric=NULL
									,@ve_dolar_aduanero numeric(15,2)=NULL
									,@ve_dolar_acordado numeric(15,2)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into dolar_todoinox (cod_mes,cod_ano,dolar_aduanero,dolar_acuerdo)
			 				values (@ve_cod_mes,@ve_ano,@ve_dolar_aduanero,@ve_dolar_acordado)
	end 
	if (@ve_operacion='UPDATE') begin
		update dolar_todoinox 
		set DOLAR_ADUANERO  = @ve_dolar_aduanero
		   ,DOLAR_ACUERDO = @ve_dolar_acordado
		where cod_dolar_todoinox = @ve_cod_dolar_todoinox
	end
	else if (@ve_operacion='DELETE') begin
		delete dolar_todoinox 
		where cod_dolar_todoinox = @ve_cod_dolar_todoinox
	end	
END
go
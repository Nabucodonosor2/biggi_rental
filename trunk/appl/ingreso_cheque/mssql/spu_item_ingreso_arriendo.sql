CREATE PROCEDURE [dbo].[spu_item_ingreso_arriendo](@ve_operacion varchar(20)
													,@ve_cod_ingreso_arriendo numeric
													,@ve_cod_ingreso_cheque numeric=NULL
													,@ve_cod_arriendo numeric=NULL)
AS

BEGIN
	if (@ve_operacion='INSERT') begin

		INSERT INTO INGRESO_ARRIENDO(
					cod_ingreso_cheque,
					cod_arriendo)
		VALUES		(
					@ve_cod_ingreso_cheque,
					@ve_cod_arriendo)
	end 	
END
go
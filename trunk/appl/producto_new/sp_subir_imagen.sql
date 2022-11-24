ALTER PROCEDURE [dbo].[sp_subir_imagen](@ve_data_chica IMAGE = null
										,@ve_data_grande IMAGE = null
										,@ve_cod_producto VARCHAR(30)=null)
AS
BEGIN
Declare 
	@ve_largo_chica		numeric,
	@ve_largo_grande	numeric
	BEGIN
		set @ve_largo_chica		= DATALENGTH(@ve_data_chica)
		set @ve_largo_grande	= DATALENGTH(@ve_data_grande)
		if @ve_largo_chica <> 0
		begin
			UPDATE	PRODUCTO 
			SET			FOTO_CHICA		= @ve_data_chica
			WHERE		COD_PRODUCTO	= @ve_cod_producto
		end 
		if @ve_largo_grande <> 0
		begin
			UPDATE	PRODUCTO 
			SET			FOTO_GRANDE		= @ve_data_grande
			WHERE		COD_PRODUCTO	= @ve_cod_producto
		end
	end
END
go
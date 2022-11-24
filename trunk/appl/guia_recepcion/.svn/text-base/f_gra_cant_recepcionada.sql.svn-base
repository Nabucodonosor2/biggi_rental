ALTER FUNCTION [dbo].[f_gra_cant_recepcionada](@ve_cod_item_doc numeric, @ve_cod_estado_guia_recepcion numeric)
RETURNS T_CANTIDAD
AS
BEGIN
	declare @vl_cant			T_CANTIDAD
	
	if(@ve_cod_estado_guia_recepcion = 1)begin
		select @vl_cant = ISNULL(SUM(CANTIDAD), 0)  
		from ITEM_GUIA_RECEPCION IGR, GUIA_RECEPCION GR 
		where COD_ITEM_DOC = @ve_cod_item_doc  
		AND GR.COD_GUIA_RECEPCION  = IGR.COD_GUIA_RECEPCION  
		and COD_ESTADO_GUIA_RECEPCION = 2 --IMPRESA
		and GR.cod_tipo_guia_recepcion = 4 --ARRIENDO
	end
	else begin
		set @vl_cant = 0
	end 
	
	
	return @vl_cant;
END

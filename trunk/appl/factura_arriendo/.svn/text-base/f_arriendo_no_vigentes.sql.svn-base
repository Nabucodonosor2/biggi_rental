-----------------------------  f_arriendo_no_vigentes ------------------
CREATE FUNCTION f_arriendo_no_vigentes(@ve_cod_arriendo numeric)
RETURNS numeric
AS
-- Obtiene los arriendos no vigentes definidos por MH
BEGIN
	declare 
		@vl_cod_arriendo			numeric

	select @vl_cod_arriendo = cod_arriendo
	from ARRIENDO_NO_VIGENTE
	where cod_arriendo = @ve_cod_arriendo
	
	if (@vl_cod_arriendo is null)
		set @vl_cod_arriendo = 0
		
	return @vl_cod_arriendo
END	
alter FUNCTION f_tipo_fa(@ve_nom_estado_doc_sii		varchar(100))
RETURNS varchar(100)
AS
BEGIN 
	declare 
	   @vl_tipo_fa  varchar(100)
	
	if(@ve_nom_estado_doc_sii = 'EMITIDA')begin
		set @vl_tipo_fa = 'Sin tipo'
	end 
	else if(@ve_nom_estado_doc_sii = 'IMPRESA')begin
		set @vl_tipo_fa = 'Papel' 
	end 
	else if(@ve_nom_estado_doc_sii = 'ENVIADA A SII')begin
		set @vl_tipo_fa = 'Electrónica' 
	end	
	else if(@ve_nom_estado_doc_sii = 'ANULADA')begin
		set @vl_tipo_fa = 'Papel' 
	end
		return @vl_tipo_fa
END
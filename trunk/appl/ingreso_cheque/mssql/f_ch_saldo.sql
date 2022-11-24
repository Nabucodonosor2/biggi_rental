ALTER FUNCTION [dbo].[f_ch_saldo](@ve_cod_cheque numeric)
RETURNS numeric
AS
BEGIN

declare
	@vl_cod_estado	numeric

--busca en ingreso_cheque confirmado
set @vl_cod_estado = 0

select @vl_cod_estado = i.cod_estado_ingreso_cheque
from cheque c, ingreso_cheque i
where c.cod_cheque = @ve_cod_cheque
and i.cod_ingreso_cheque = c.cod_ingreso_cheque

if (@vl_cod_estado <> 2)	--confirmado
	return 0

--busca que no ese cambiado
set @vl_cod_estado = 0

select @vl_cod_estado = i.cod_estado_cambio_cheque
from cheque_old c, cambio_cheque i
where c.cod_cheque = @ve_cod_cheque
and i.cod_cambio_cheque = c.cod_cambio_cheque

if (@vl_cod_estado <> 0 and @vl_cod_estado <> 1 and @vl_cod_estado <> 2)	--emitido y confirmado
	return 0

--busca usado en ingreso_pago
declare
	@vl_monto_usado	numeric
	,@vl_monto_doc	numeric
	
select @vl_monto_usado =  isnull(sum(monto_doc), 0)
from doc_ingreso_pago dip, ingreso_pago ip
where dip.cod_cheque = @ve_cod_cheque
and ip.cod_ingreso_pago = dip.cod_ingreso_pago
and ip.cod_estado_ingreso_pago in (1,2)

select @vl_monto_doc = isnull(monto_doc, 0)
from cheque
where cod_cheque = @ve_cod_cheque

return @vl_monto_doc - @vl_monto_usado
END

-------------------- spu_inf_cheque_fecha ---------------------------------
CREATE PROCEDURE spu_inf_cheque_fecha(@ve_operacion				varchar(20)
									 ,@ve_cod_doc_ingreso_pago	numeric = NULL
									 ,@ve_new_fecha_doc			datetime
									 ,@ve_cod_ingreso_pago		numeric = NULL
									 ,@ve_nro_doc				numeric = NULL)

AS
BEGIN
	if (@ve_operacion='CAMBIAR_FECHA') 
	begin
		update doc_ingreso_pago
		set new_fecha_doc	=	@ve_new_fecha_doc
		where cod_doc_ingreso_pago = @ve_cod_doc_ingreso_pago
	end
	if (@ve_operacion='CAMBIAR_FECHA_UNO') 
	begin
		update doc_ingreso_pago
		set new_fecha_doc	=	@ve_new_fecha_doc
		where cod_ingreso_pago = @ve_cod_ingreso_pago
		and nro_doc	= @ve_nro_doc
	end
END
go












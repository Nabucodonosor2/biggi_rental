create PROCEDURE [dbo].[sp_arr_x_facturar](@ve_cod_usuario numeric)
AS
BEGIN
	delete from ARR_X_FACTURAR where cod_usuario = @ve_cod_usuario
	
	insert into ARR_X_FACTURAR select
				  A.COD_ARRIENDO
        		,A.NOM_ARRIENDO
        		,A.REFERENCIA
        		,dbo.f_arr_total_actual(a.COD_ARRIENDO,getdate()) 
        		,E.NOM_EMPRESA 
        		,E.RUT 
        		,E.DIG_VERIF 
        		,@ve_cod_usuario
        		FROM ARRIENDO A 
        		,EMPRESA E
        		WHERE dbo.f_arr_total_actual(A.COD_ARRIENDO,getdate()) > 0
        		AND dbo.f_arr_esta_facturado(A.COD_ARRIENDO, GETDATE()) = 0
        		AND A.COD_ARRIENDO  <> dbo.f_arriendo_no_vigentes( A.COD_ARRIENDO)
        		AND E.COD_EMPRESA = A.COD_EMPRESA
               
END
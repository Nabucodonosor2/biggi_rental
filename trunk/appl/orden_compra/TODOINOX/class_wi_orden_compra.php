<?php
/////////////////////////////////////////
/// TODOINOX
/////////////////////////////////////////

class wi_orden_compra extends wi_orden_compra_base {
	const K_ESTADO_ANULADA  = 2;
	function wi_orden_compra($cod_item_menu) {
		parent::wi_orden_compra_base($cod_item_menu);
		$cod_usuario = session::get('COD_USUARIO');
		
		
		$sql = "SELECT	 O.COD_ORDEN_COMPRA
						,substring(convert(varchar(20), O.FECHA_ORDEN_COMPRA, 103) + ' ' + convert(varchar(20), O.FECHA_ORDEN_COMPRA, 108), 1, 16) FECHA_ORDEN_COMPRA
						,O.COD_USUARIO
						,U.NOM_USUARIO
						,O.COD_USUARIO_SOLICITA						
						,O.COD_MONEDA				
						,EOC.NOM_ESTADO_ORDEN_COMPRA	
						,O.COD_ESTADO_ORDEN_COMPRA
						,O.COD_ESTADO_ORDEN_COMPRA COD_ESTADO_ORDEN_COMPRA_H
						,O.COD_NOTA_VENTA						
						,O.COD_CUENTA_CORRIENTE
						,O.COD_CUENTA_CORRIENTE AS NRO_CUENTA_CORRIENTE
						,O.REFERENCIA
						,O.NRO_ORDEN_COMPRA_4D
						,O.COD_EMPRESA
						,E.ALIAS
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,E.GIRO													
						,COD_SUCURSAL AS COD_SUCURSAL_FACTURA	 				
						,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA						
						,O.COD_PERSONA
						,dbo.f_emp_get_mail_cargo_persona(COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA
						,O.SUBTOTAL	SUM_TOTAL					
						,O.PORC_DSCTO1
						,O.MONTO_DSCTO1  
						,O.PORC_DSCTO2
						,O.MONTO_DSCTO2  
						,O.TOTAL_NETO
						,O.PORC_IVA
						,O.MONTO_IVA
						,O.TOTAL_CON_IVA
						,O.OBS
						,substring(convert(varchar(20), O.FECHA_ANULA, 103) + ' ' + convert(varchar(20), O.FECHA_ANULA, 108), 1, 16) FECHA_ANULA						
						,O.MOTIVO_ANULA
						,O.COD_USUARIO_ANULA	
						,INGRESO_USUARIO_DSCTO1  
						,INGRESO_USUARIO_DSCTO2	
						,case O.COD_ESTADO_ORDEN_COMPRA
							when ".self::K_ESTADO_ANULADA." then '' 
							else 'none'
						end TR_DISPLAY
						,(SELECT ISNULL(PORC_MODIFICA_PRECIO_OC, 0) FROM USUARIO WHERE COD_USUARIO = $cod_usuario) PORC_MODIFICA_PRECIO_OC_H	
						,O.COD_DOC	
						,O.TIPO_ORDEN_COMPRA
						,O.AUTORIZADA
						, dbo.f_last_mod('NOM_USUARIO', 'ORDEN_COMPRA', 'AUTORIZADA', O.COD_ORDEN_COMPRA) USUARIO_AUTORIZA
                        , dbo.f_last_mod('FECHA_CAMBIO', 'ORDEN_COMPRA', 'AUTORIZADA', O.COD_ORDEN_COMPRA) FECHA_AUTORIZA
                        , 'none' TR_AUTORIZADA_TE
						,convert(numeric(10),(TOTAL_NETO_ORIGINAL * 0.2) + TOTAL_NETO_ORIGINAL) MAXIMO_PRECIO_OC_H --20%
						, 'none' TR_MODIF_20_PORC
						, AUTORIZADA_20_PROC
						, dbo.f_last_mod('NOM_USUARIO', 'ORDEN_COMPRA', 'AUTORIZADA_20_PROC', O.COD_ORDEN_COMPRA) USUARIO_AUTORIZA_20_PROC
                        , dbo.f_last_mod('FECHA_CAMBIO', 'ORDEN_COMPRA', 'AUTORIZADA_20_PROC', O.COD_ORDEN_COMPRA) FECHA_AUTORIZA_20_PROC
                        , ES_INVENTARIO
                FROM 	ORDEN_COMPRA O, USUARIO U, EMPRESA E, ESTADO_ORDEN_COMPRA EOC
				WHERE	O.COD_ORDEN_COMPRA = {KEY1} and
						U.COD_USUARIO = O.COD_USUARIO AND
						E.COD_EMPRESA = O.COD_EMPRESA AND
						EOC.COD_ESTADO_ORDEN_COMPRA = O.COD_ESTADO_ORDEN_COMPRA";
				
				$this->dws['dw_orden_compra']->set_sql($sql);

				$this->dws['dw_orden_compra']->add_control(new edit_check_box('ES_INVENTARIO', 'S', 'N'));		
						
			
			$this->dws['dw_orden_compra']-> unset_mandatory('COD_NOTA_VENTA');	
	}
	function save_record($db) {	
		$cod_orden_compra 	= $this->get_key();		
		$cod_usuario 		= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO');
		$cod_usuario_sol 	= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO_SOLICITA');		
		$cod_moneda			= $this->dws['dw_orden_compra']->get_item(0, 'COD_MONEDA');		
		$cod_est_oc			= $this->dws['dw_orden_compra']->get_item(0, 'COD_ESTADO_ORDEN_COMPRA');
		$cod_nota_venta		= $this->dws['dw_orden_compra']->get_item(0, 'COD_NOTA_VENTA');
		$cod_nota_venta		= ($cod_nota_venta =='') ? "null" : "$cod_nota_venta";
		
		$cod_cta_cte		= $this->dws['dw_orden_compra']->get_item(0, 'COD_CUENTA_CORRIENTE');	 	
		$referencia			= $this->dws['dw_orden_compra']->get_item(0, 'REFERENCIA');
		$referencia 		= str_replace("'", "''", $referencia);
		
		$nro_orden_compra_4d		= $this->dws['dw_orden_compra']->get_item(0, 'NRO_ORDEN_COMPRA_4D');
		$nro_orden_compra_4d		= ($nro_orden_compra_4d =='') ? "null" : "$nro_orden_compra_4d";
		
		$cod_empresa		= $this->dws['dw_orden_compra']->get_item(0, 'COD_EMPRESA');
		$cod_suc_factura	= $this->dws['dw_orden_compra']->get_item(0, 'COD_SUCURSAL_FACTURA');
		$cod_persona		= $this->dws['dw_orden_compra']->get_item(0, 'COD_PERSONA');
		$cod_persona		= ($cod_persona =='') ? "null" : "$cod_persona";			
		$sub_total			= $this->dws['dw_orden_compra']->get_item(0, 'SUM_TOTAL');
		$sub_total      	= ($sub_total =='') ? 0 : "$sub_total";
		
		$porc_descto1		= $this->dws['dw_orden_compra']->get_item(0, 'PORC_DSCTO1');
		$porc_descto1		= ($porc_descto1 =='') ? "null" : "$porc_descto1";
				
		$monto_dscto1		= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_DSCTO1');
		$monto_dscto1		= ($monto_dscto1 =='') ? 0 : "$monto_dscto1";
		
		$porc_descto2		= $this->dws['dw_orden_compra']->get_item(0, 'PORC_DSCTO2');
		$porc_descto2		= ($porc_descto2 =='') ? "null" : "$porc_descto2";
				
		$monto_dscto2		= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_DSCTO2');
		$monto_dscto2		= ($monto_dscto2 =='') ? 0 : "$monto_dscto2";
		
		$total_neto			= $this->dws['dw_orden_compra']->get_item(0, 'TOTAL_NETO');
		$total_neto			= ($total_neto =='') ? 0 : "$total_neto";
		
		$porc_iva			= $this->dws['dw_orden_compra']->get_item(0, 'PORC_IVA');		
		
		$monto_iva			= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_IVA');
		$monto_iva			= ($monto_iva =='') ? 0 : "$monto_iva";
		
		$total_con_iva		= $this->dws['dw_orden_compra']->get_item(0, 'TOTAL_CON_IVA');
		$total_con_iva		= ($total_con_iva =='') ? 0 : "$total_con_iva";
				
		$obs				= $this->dws['dw_orden_compra']->get_item(0, 'OBS');
		$obs		 		= str_replace("'", "''", $obs);
		$obs				= ($obs =='') ? "null" : "'$obs'";
							// NOTA: para el manejo de fecha se debe pasar un string dd/mm/yyyy y en el sp llamar a to_date ber eje en spi_orden_trabajo
		$motivo_anula		= $this->dws['dw_orden_compra']->get_item(0, 'MOTIVO_ANULA');
		$motivo_anula		= str_replace("'", "''", $motivo_anula);
		$motivo_anula		= ($motivo_anula =='') ? "null" : "'$motivo_anula'";
		
		$cod_user_anula		= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO_ANULA');
		$autorizada			= $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZADA');
		$autorizada_20_proc	= $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZADA_20_PROC');
		
		$es_inventario = $this->dws['dw_orden_compra']->get_item(0, 'ES_INVENTARIO');
		
		//si volvio a modificar la OC se vuelve a estado Por Autorizar
		/*if (!$this->is_new_record()) {
			$maximo_precio_oc_h = $this->dws['dw_orden_compra']->get_item(0, 'MAXIMO_PRECIO_OC_H');
			$maximo_precio_oc_h	= ($maximo_precio_oc_h =='') ? 0 : "$maximo_precio_oc_h";
			
			$sql = "select AUTORIZADA_20_PROC
							,TOTAL_NETO
						from orden_compra
						where cod_orden_compra = $cod_orden_compra";
			$result = $db->build_results($sql);		
			$autorizada_20_proc_old = $result[0]['AUTORIZADA_20_PROC'];
			$total_neto_old = $result[0]['TOTAL_NETO'];
			
			if($total_neto <> $total_neto_old){
				if($total_neto > $maximo_precio_oc_h){
					if($autorizada_20_proc_old == 'S'){
						$autorizada_20_proc = 'N';
					}
				}else{
					if($autorizada_20_proc_old == 'N'){
						$autorizada_20_proc = 'S';
					}
				}
			}
		}*/
		//si volvio a modificar la OC se vuelve a estado Por Autorizar
		
		if (($motivo_anula!= '') && ($cod_user_anula == '')) // se anula 
			$cod_user_anula			= $this->cod_usuario;
		else
			$cod_usuario_anula			= "null";
		
		$ingreso_usuario_dscto1 = $this->dws['dw_orden_compra']->get_item(0, 'INGRESO_USUARIO_DSCTO1');;
		$ingreso_usuario_dscto1 = ($ingreso_usuario_dscto1 =='') ? "null" : "'$ingreso_usuario_dscto1'";
		
		
		$ingreso_usuario_dscto2 = $this->dws['dw_orden_compra']->get_item(0, 'INGRESO_USUARIO_DSCTO2');;
		$ingreso_usuario_dscto2 = ($ingreso_usuario_dscto2 =='') ? "null" : "'$ingreso_usuario_dscto2'";

		$tipo_orden_compra = $this->dws['dw_orden_compra']->get_item(0, 'TIPO_ORDEN_COMPRA');;
		$cod_doc = $this->dws['dw_orden_compra']->get_item(0, 'COD_DOC');;
		$cod_doc = ($cod_doc=='') ? "null" : $cod_doc;		

		$cod_orden_compra = ($cod_orden_compra=='') ? "null" : $cod_orden_compra;		
    
		$sp = 'spu_orden_compra';
	    if ($this->is_new_record()) {
	    	$operacion = 'INSERT';

			$sql = "select COD_ESTADO_NOTA_VENTA
					from NOTA_VENTA
					where COD_NOTA_VENTA = $cod_nota_venta";
			$result = $db->build_results($sql);		
			$cod_estado_nota_venta = $result[0]['COD_ESTADO_NOTA_VENTA'];
			if ($cod_estado_nota_venta==self::K_NV_CERRADA)
				$tipo_orden_compra = 'BACKCHARGE';
			else 
				$tipo_orden_compra = 'NOTA_VENTA';
	    }
	    else
	    	$operacion = 'UPDATE';
	    
	
		$param	= "'$operacion'
					,$cod_orden_compra				
					,$cod_usuario 		
					,$cod_usuario_sol 									
					,$cod_moneda		
					,$cod_est_oc
					,$cod_nota_venta			
					,$cod_cta_cte
					,'$referencia'																						
					,$cod_empresa		
					,$cod_suc_factura	
					,$cod_persona			
					,$sub_total		
					,$porc_descto1		
					,$monto_dscto1		
					,$porc_descto2		
					,$monto_dscto2		
					,$total_neto		
					,$porc_iva		
					,$monto_iva		
					,$total_con_iva				
					,$obs
					,$motivo_anula
					,$cod_user_anula
					,$ingreso_usuario_dscto1
					,$ingreso_usuario_dscto2
					,$tipo_orden_compra
					,$cod_doc
					,$autorizada
					,$autorizada_20_proc
					,$nro_orden_compra_4d
					,$es_inventario";
					
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_orden_compra = $db->GET_IDENTITY();
				$this->dws['dw_orden_compra']->set_item(0, 'COD_ORDEN_COMPRA', $cod_orden_compra);
			}
			 for ($i=0; $i<$this->dws['dw_item_orden_compra']->row_count(); $i++)
				$this->dws['dw_item_orden_compra']->set_item($i, 'COD_ORDEN_COMPRA', $this->dws['dw_orden_compra']->get_item(0, 'COD_ORDEN_COMPRA'), 'primary', false);				
			 if (!$this->dws['dw_item_orden_compra']->update($db))			
			 	return false;			
			
			$parametros_sp = "'RECALCULA',$cod_orden_compra";	
			if (!$db->EXECUTE_SP('spu_orden_compra', $parametros_sp))
				return false;

			$parametros_sp = "'item_orden_compra','orden_compra',$cod_orden_compra";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
			
			return true;
		}	
		return false;				
	}
	
}
?>
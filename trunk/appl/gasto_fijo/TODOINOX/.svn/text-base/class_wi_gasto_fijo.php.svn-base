<?php
/////////////////////////////////////////
/// TODOINOX
/////////////////////////////////////////

require_once(dirname(__FILE__)."/../../orden_compra/class_dw_item_orden_compra.php");

class wi_gasto_fijo extends wi_gasto_fijo_base {
	function wi_gasto_fijo($cod_item_menu) {
		parent::wi_gasto_fijo_base($cod_item_menu);

		// Usa el dw_item normnal de la OC
	/*	unset($this->dws['dw_item_orden_compra']);
		$this->dws['dw_item_orden_compra'] = new dw_item_orden_compra();*/
	}
	function load_record(){
		parent::load_record();
		$this->dws['dw_orden_compra']->set_entrable('COD_ESTADO_ORDEN_COMPRA', true);
		
	}
	function save_record($db) {	
		$cod_orden_compra 	= $this->get_key();		
		$cod_usuario 		= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO');
		$cod_usuario_sol 	= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO'); //el solicitante será el mismo que el emisor		
		$cod_moneda			= self::K_MONEDA;	//la moneda será $						
		$cod_est_oc			= $this->dws['dw_orden_compra']->get_item(0, 'COD_ESTADO_ORDEN_COMPRA');
		$cod_nota_venta		= "null";
		
		$cod_cta_cte		= $this->dws['dw_orden_compra']->get_item(0, 'COD_CUENTA_CORRIENTE');	 	
		$referencia			= $this->dws['dw_orden_compra']->get_item(0, 'REFERENCIA');
		$referencia 		= str_replace("'", "''", $referencia);
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
		
		$porc_descto2		= 0;//$this->dws['dw_orden_compra']->get_item(0, 'PORC_DSCTO2');
		$porc_descto2		= ($porc_descto2 =='') ? "null" : "$porc_descto2";
				
		$monto_dscto2		= 0;// $this->dws['dw_orden_compra']->get_item(0, 'MONTO_DSCTO2');
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
		
		if (($motivo_anula!= '') && ($cod_user_anula == '')) // se anula 
			$cod_user_anula			= $this->cod_usuario;
		else
			$cod_usuario_anula			= "null";
		
		$ingreso_usuario_dscto1 = $this->dws['dw_orden_compra']->get_item(0, 'INGRESO_USUARIO_DSCTO1');;
		$ingreso_usuario_dscto1 = ($ingreso_usuario_dscto1 =='') ? "null" : "'$ingreso_usuario_dscto1'";
			
		$ingreso_usuario_dscto2 = $this->dws['dw_orden_compra']->get_item(0, 'INGRESO_USUARIO_DSCTO2');;
		$ingreso_usuario_dscto2 = ($ingreso_usuario_dscto2 =='') ? "null" : "'$ingreso_usuario_dscto2'";

		$cod_orden_compra = ($cod_orden_compra=='') ? "null" : $cod_orden_compra;		
    
		$sp = 'spu_orden_compra';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
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
						,'GASTO_FIJO'
						, null
						, 'S'
						, 'S'";

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
			
			return true;
		}	
		return false;				
	}
}
?>
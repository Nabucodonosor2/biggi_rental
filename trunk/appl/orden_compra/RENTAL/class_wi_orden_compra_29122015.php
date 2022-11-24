<?php
class wi_orden_compra extends wi_orden_compra_base {
	const K_COD_CC					= 3;
	const K_NRO_CC					= 3;
	const K_ESTADO_APROBADA			= '4';
	const K_AUTORIZA_APROBACION_OC	= '991555';
	const K_MODIFICAR_OC_AUTORIZADA	= '991560';
	
	function wi_orden_compra($cod_item_menu) {
		parent::wi_orden_compra_base($cod_item_menu);
		$this->dws['dw_orden_compra']-> unset_mandatory('COD_NOTA_VENTA');	
	}
	function new_record() {
		parent::new_record();
		
		$this->dws['dw_orden_compra']->set_item(0, 'COD_CUENTA_CORRIENTE', self::K_COD_CC);
		$this->dws['dw_orden_compra']->set_item(0, 'NRO_CUENTA_CORRIENTE', self::K_NRO_CC);
	}
	
	function load_record(){
		parent::load_record();
		
		$COD_ESTADO_ORDEN_COMPRA = $this->dws['dw_orden_compra']->get_item(0, 'COD_ESTADO_ORDEN_COMPRA');
		
		if ($COD_ESTADO_ORDEN_COMPRA == self::K_ESTADO_EMITIDA){
			$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_APROBACION_OC, $this->cod_usuario);
			if($priv == 'E')
				$sql = "select 	COD_ESTADO_ORDEN_COMPRA
								,NOM_ESTADO_ORDEN_COMPRA
								,ORDEN
						from ESTADO_ORDEN_COMPRA
						where COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_EMITIDA." or
								COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_ANULADA." or
								 COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_APROBADA."
						order by COD_ESTADO_ORDEN_COMPRA";
			else
				$sql = "select 	COD_ESTADO_ORDEN_COMPRA
								,NOM_ESTADO_ORDEN_COMPRA
								,ORDEN
						from ESTADO_ORDEN_COMPRA		
						where COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_EMITIDA." or
								COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_ANULADA."
						order by COD_ESTADO_ORDEN_COMPRA";
			
			unset($this->dws['dw_orden_compra']->controls['COD_ESTADO_ORDEN_COMPRA']);
			$this->dws['dw_orden_compra']->add_control($control = new drop_down_dw('COD_ESTADO_ORDEN_COMPRA',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			
		}else if($COD_ESTADO_ORDEN_COMPRA == self::K_ESTADO_APROBADA){
			$sql = "SELECT 	COD_ESTADO_ORDEN_COMPRA
							,NOM_ESTADO_ORDEN_COMPRA
							,ORDEN
					FROM ESTADO_ORDEN_COMPRA
					WHERE COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_APROBADA." OR
							COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_ANULADA."
					ORDER BY COD_ESTADO_ORDEN_COMPRA";
				
			unset($this->dws['dw_orden_compra']->controls['COD_ESTADO_ORDEN_COMPRA']);
			$this->dws['dw_orden_compra']->add_control($control = new drop_down_dw('COD_ESTADO_ORDEN_COMPRA',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_orden_compra']->controls['NOM_ESTADO_ORDEN_COMPRA']->type = 'hidden';
			
			$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_APROBACION_OC, $this->cod_usuario);
			if($priv=='E')
				$this->dws['dw_orden_compra']->set_entrable('COD_ESTADO_ORDEN_COMPRA', true);
			else
				$this->dws['dw_orden_compra']->set_entrable('COD_ESTADO_ORDEN_COMPRA', false);
			
			$priv2 = $this->get_privilegio_opcion_usuario(self::K_MODIFICAR_OC_AUTORIZADA, $this->cod_usuario);
			if ($priv2 == 'E'){
				$this->dws['dw_orden_compra']->set_entrable('COD_USUARIO_SOLICITA', true);
				$this->dws['dw_orden_compra']->set_entrable('NRO_CUENTA_CORRIENTE', true);
				$this->dws['dw_orden_compra']->set_entrable('COD_CUENTA_CORRIENTE', true);
				$this->dws['dw_orden_compra']->set_entrable('REFERENCIA'		  , true);
				$this->dws['dw_orden_compra']->set_entrable('COD_EMPRESA'		  , true);
				$this->dws['dw_orden_compra']->set_entrable('ALIAS'				  , true);
				$this->dws['dw_orden_compra']->set_entrable('RUT'				  , true);
				$this->dws['dw_orden_compra']->set_entrable('NOM_EMPRESA'		  , true);
				$this->dws['dw_orden_compra']->set_entrable('COD_SUCURSAL_FACTURA', true);
				$this->dws['dw_orden_compra']->set_entrable('COD_PERSONA'		  , true);
				$this->dws['dw_orden_compra']->set_entrable('OBS'				  , true);
				$this->dws['dw_orden_compra']->set_entrable('PORC_DSCTO1'	  	  , true);
				$this->dws['dw_orden_compra']->set_entrable('MONTO_DSCTO1'	  	  , true);
				$this->dws['dw_orden_compra']->set_entrable('PORC_IVA'		  	  , true);
				$this->dws['dw_orden_compra']->set_entrable('PORC_DSCTO2'	  	  , true);
				$this->dws['dw_orden_compra']->set_entrable('MONTO_DSCTO2'	  	  , true);
				
				$this->dws['dw_item_orden_compra']->set_entrable_dw(true);
			}else{
				$this->dws['dw_orden_compra']->set_entrable('COD_USUARIO_SOLICITA', false);
				$this->dws['dw_orden_compra']->set_entrable('NRO_CUENTA_CORRIENTE', false);
				$this->dws['dw_orden_compra']->set_entrable('COD_CUENTA_CORRIENTE', false);
				$this->dws['dw_orden_compra']->set_entrable('REFERENCIA'		  , false);
				$this->dws['dw_orden_compra']->set_entrable('COD_EMPRESA'		  , false);
				$this->dws['dw_orden_compra']->set_entrable('ALIAS'				  , false);
				$this->dws['dw_orden_compra']->set_entrable('RUT'				  , false);
				$this->dws['dw_orden_compra']->set_entrable('NOM_EMPRESA'		  , false);
				$this->dws['dw_orden_compra']->set_entrable('COD_SUCURSAL_FACTURA', false);
				$this->dws['dw_orden_compra']->set_entrable('COD_PERSONA'		  , false);
				$this->dws['dw_orden_compra']->set_entrable('OBS'				  , false);
				$this->dws['dw_orden_compra']->set_entrable('PORC_DSCTO1'	  	  , false);
				$this->dws['dw_orden_compra']->set_entrable('MONTO_DSCTO1'	  	  , false);
				$this->dws['dw_orden_compra']->set_entrable('PORC_IVA'		  	  , false);
				$this->dws['dw_orden_compra']->set_entrable('PORC_DSCTO2'	  	  , false);
				$this->dws['dw_orden_compra']->set_entrable('MONTO_DSCTO2'	  	  , false);
				
				$this->dws['dw_item_orden_compra']->set_entrable_dw(false);
			}
		}
	}
	
	function habilitar(&$temp, $habilita){
		parent::habilitar($temp, $habilita);
		
		$COD_ESTADO_ORDEN_COMPRA = $this->dws['dw_orden_compra']->get_item(0, 'COD_ESTADO_ORDEN_COMPRA');
		
		if($COD_ESTADO_ORDEN_COMPRA == 4)// autorizada	
			$this->habilita_boton($temp, 'print', true);
		else
			$this->habilita_boton($temp, 'print', false);
	}
}
?>
<?php
/////////////////////////////////////////
/// TODOINOX
/////////////////////////////////////////

class wi_orden_compra extends wi_orden_compra_base {
	const K_COD_CC 	= 3;
	const K_NRO_CC 	= 3;
	
	function wi_orden_compra($cod_item_menu) {
		parent::wi_orden_compra_base($cod_item_menu);
		$this->dws['dw_orden_compra']-> unset_mandatory('COD_NOTA_VENTA');	
	}
	function new_record() {
		parent::new_record();
		
		$this->dws['dw_orden_compra']->set_item(0, 'COD_CUENTA_CORRIENTE', self::K_COD_CC);
		$this->dws['dw_orden_compra']->set_item(0, 'NRO_CUENTA_CORRIENTE', self::K_NRO_CC);
	
	}
	

}
?>
<?php
class wi_inf_bodega_tarjeta_existencia extends wi_inf_bodega_tarjeta_existencia_base {	
	function wi_inf_bodega_tarjeta_existencia($cod_item_menu) {
		parent::wi_inf_bodega_tarjeta_existencia_base($cod_item_menu);

		$sql_bodega="SELECT COD_BODEGA
							,NOM_BODEGA
					FROM BODEGA
					where COD_TIPO_BODEGA = 1	
					ORDER BY COD_BODEGA ASC";
		$this->dws['dw_param']->add_control(new drop_down_dw('COD_BODEGA', $sql_bodega, 0, '', false));
	}
}
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../inf_ventas_por_equipo/class_wi_inf_ventas_por_equipo.php");

class wi_inf_venta_diaria extends wi_inf_ventas_por_equipo {
	function wi_inf_venta_diaria($cod_item_menu) {
		parent::wi_inf_ventas_por_equipo($cod_item_menu);
		parent::w_input('inf_venta_diaria', $cod_item_menu);
		$this->nom_template = "wi_inf_ventas_por_equipo.htm";
	}
}
?>
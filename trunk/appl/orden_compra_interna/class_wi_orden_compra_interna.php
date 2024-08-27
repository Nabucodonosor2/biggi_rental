<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class dw_item_orden_compra_it extends datawindow{
	function dw_item_orden_compra_it(){
		$sql = "SELECT COD_ITEM_ORDEN_COMPRA
					,COD_ORDEN_COMPRA
					,ORDEN
					,ITEM
					,COD_PRODUCTO
					,NOM_PRODUCTO
					,CANTIDAD
					,PRECIO
				FROM BIGGI.dbo.ITEM_ORDEN_COMPRA
				WHERE COD_ORDEN_COMPRA = {KEY1}";
		
		parent::datawindow($sql, 'ITEM_ORDEN_COMPRA_IT');
		
		$this->add_control(new static_num('PRECIO'));
	}
}

class wi_orden_compra_interna extends w_input {
	function wi_orden_compra_interna($cod_item_menu) {
		parent::w_input('orden_compra_interna', $cod_item_menu);

		$sql = "SELECT COD_ORDEN_COMPRA
                    ,CONVERT(VARCHAR, FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
                    ,FECHA_ORDEN_COMPRA DATE_FECHA_ORDEN_COMPRA
                    ,REFERENCIA
                    ,COD_NOTA_VENTA
                    ,INI_USUARIO
					,NOM_USUARIO
                    ,TOTAL_NETO
                FROM BIGGI.dbo.ORDEN_COMPRA OC
                    ,BIGGI.dbo.USUARIO U
                WHERE COD_ORDEN_COMPRA = {KEY1}
                AND OC.COD_USUARIO = U.COD_USUARIO";	
	
		$this->dws['dw_orden_compra_interna'] = new datawindow($sql);
		$this->dws['dw_item_orden_compra_it'] = new dw_item_orden_compra_it();
		
		// asigna los formatos	
		// $this->dws['dw_orden_compra_interna']->add_control(new drop_down_dw('COD_CIUDAD',$sql_ciudad,180));								
		// $this->dws['dw_orden_compra_interna']->add_control(new edit_text_upper('NOM_COMUNA', 80, 100));
	}

	function load_record(){
		$cod_orden_compra = $this->get_item_wo($this->current_record, 'COD_ORDEN_COMPRA');
		$this->dws['dw_orden_compra_interna']->retrieve($cod_orden_compra);
		$this->dws['dw_item_orden_compra_it']->retrieve($cod_orden_compra);
	}

	function get_key(){
		return $this->dws['dw_orden_compra_interna']->get_item(0, 'COD_ORDEN_COMPRA');
	}	
}
?>
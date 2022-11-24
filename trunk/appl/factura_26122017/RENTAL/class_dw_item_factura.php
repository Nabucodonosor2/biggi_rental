<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../class_dw_item_factura.php");


class dw_item_factura extends dw_item_factura_base {
		const K_ESTADO_SII_EMITIDA 			= 1;
	function dw_item_factura() {
		parent::dw_item_factura_base();
		$sql = "SELECT ifa.COD_ITEM_FACTURA,
						ifa.COD_FACTURA,
						ifa.ORDEN,
						ifa.ITEM,
						case f.desde_4d
							when 'S' then ifa.COD_PRODUCTO_4D
							else COD_PRODUCTO
						end COD_PRODUCTO,
						ifa.COD_PRODUCTO COD_PRODUCTO_OLD,
						ifa.NOM_PRODUCTO,
						ifa.CANTIDAD,
						ifa.PRECIO,
						ifa.COD_ITEM_DOC,
						ifa.TIPO_DOC,
						case ifa.TIPO_DOC
							when 'ITEM_NOTA_VENTA' then dbo.f_nv_cant_por_facturar(ifa.COD_ITEM_DOC, default)
							when 'ITEM_GUIA_DESPACHO' then dbo.f_gd_cant_por_facturar(ifa.COD_ITEM_DOC, default)
						end CANTIDAD_POR_FACTURAR,
						case
							when f.COD_DOC IS not NULL and f.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
							else 'none'
						end TD_DISPLAY_CANT_POR_FACT,	
						case
							when f.COD_DOC IS NULL then ''
							else 'none'
						end TD_DISPLAY_ELIMINAR,
						COD_TIPO_TE,
						MOTIVO_TE,
						'' BOTON_PRECIO, -- se utiliza en funcion comun js 'ingreso_TE'
						COD_TIPO_GAS,
						COD_TIPO_ELECTRICIDAD
				FROM    ITEM_FACTURA ifa, factura f
				WHERE   f.cod_factura = ifa.cod_factura 
					and ifa.COD_FACTURA = {KEY1}
				ORDER BY ORDEN";
		$this->set_sql($sql);
	
	}
}	
?>
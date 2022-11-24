<?php
class wo_producto extends wo_producto_base{
	const K_BODEGA_TERMINADO = 1;
	const K_MENU_PRODUCTO = '995005';
	
	function wo_producto(){
		$cod_usuario = session::get("COD_USUARIO");
		// Es igual al BASE, solo cambia elk sql donde se agrega stock
		$sql = "select	P.COD_PRODUCTO
						,NOM_PRODUCTO
						,PRECIO_VENTA_PUBLICO
						,NOM_TIPO_PRODUCTO
						 ,case 
		                    when (dbo.f_get_autoriza_menu($cod_usuario,".self::K_MENU_PRODUCTO.") = 'E') then dbo.number_format(dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_TERMINADO.", GETDATE()),0,',','.')
		                    when (dbo.f_get_autoriza_menu($cod_usuario, ".self::K_MENU_PRODUCTO.") = 'N') and (dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_TERMINADO.", GETDATE()) > 0)  then 'HAY'
		                    else 'NO HAY'
		                end STOCK
						,M.NOM_MARCA
			from 		PRODUCTO P
						,TIPO_PRODUCTO TP
						,MARCA M
			where		P.COD_TIPO_PRODUCTO = TP.COD_TIPO_PRODUCTO
						AND dbo.f_prod_valido (COD_PRODUCTO) = 'S'
						AND P.COD_MARCA = M.COD_MARCA 
			order by 	COD_PRODUCTO";
			
		parent::w_output('producto', $sql, $_REQUEST['cod_item_menu']);

		// headers
		$this->add_header(new header_modelo('COD_PRODUCTO', 'P.COD_PRODUCTO', 'Modelo'));
		$this->add_header(new header_text('NOM_PRODUCTO', 'NOM_PRODUCTO', 'Descripción'));
		$this->dw->add_control(new edit_precio('PRECIO_VENTA_PUBLICO'));
		$this->add_header(new header_num('PRECIO_VENTA_PUBLICO', 'PRECIO_VENTA_PUBLICO', 'Precio'));
		
		$this->add_header(new header_text('NOM_MARCA', 'M.NOM_MARCA', 'Marca'));
		
		$sql_tipo_producto = "select COD_TIPO_PRODUCTO ,NOM_TIPO_PRODUCTO from TIPO_PRODUCTO order by	ORDEN";
		$this->add_header($header = new header_drop_down('NOM_TIPO_PRODUCTO', 'TP.COD_TIPO_PRODUCTO', 'Tipo Producto', $sql_tipo_producto));
		$this->add_header(new header_num('STOCK', 'dbo.f_bodega_stock(P.COD_PRODUCTO, 1, GETDATE())', 'Stock'));

		// formatos de columnas
		$this->dw->add_control(new static_num('PRECIO_VENTA_PUBLICO'));
	}
}
?>
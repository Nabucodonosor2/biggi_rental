<?php
class wo_producto extends wo_producto_base{
	const K_BODEGA_TERMINADO = 2;
	
	function wo_producto(){
		// Es igual al BASE, solo cambia elk sql donde se agrega stock
		$sql = "select	P.COD_PRODUCTO
						,P.NOM_PRODUCTO
						,P.PRECIO_VENTA_PUBLICO
						,TP.COD_TIPO_PRODUCTO
						,TP.NOM_TIPO_PRODUCTO
						,dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_TERMINADO.", getdate()) STOCK
			from 		PRODUCTO P
						,TIPO_PRODUCTO TP
			where		P.COD_TIPO_PRODUCTO = TP.COD_TIPO_PRODUCTO
						AND dbo.f_prod_valido (COD_PRODUCTO) = 'S'
			order by 	COD_PRODUCTO";
			
		parent::w_output('producto', $sql, $_REQUEST['cod_item_menu']);

		// headers
		$this->add_header(new header_modelo('COD_PRODUCTO', 'P.COD_PRODUCTO', 'Modelo'));
		$this->add_header(new header_text('NOM_PRODUCTO', 'NOM_PRODUCTO', 'Descripci�n'));
		$this->add_header(new header_num('PRECIO_VENTA_PUBLICO', 'PRECIO_VENTA_PUBLICO', 'Precio P�blico'));
		$sql_tipo_producto = "select COD_TIPO_PRODUCTO ,NOM_TIPO_PRODUCTO from TIPO_PRODUCTO order by ORDEN";
		$this->add_header($header = new header_drop_down('NOM_TIPO_PRODUCTO', 'TP.COD_TIPO_PRODUCTO', 'Tipo Producto', $sql_tipo_producto));
		$this->add_header($control = new header_num('STOCK', "dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_TERMINADO.", getdate())", 'Stock'));
		$control->field_bd_order = 'STOCK';
		// formatos de columnas
		$this->dw->add_control(new edit_num('PRECIO_VENTA_PUBLICO'));

		// Filtro inicial
		$header->valor_filtro = '1';
		$this->make_filtros();
	}
}

?>
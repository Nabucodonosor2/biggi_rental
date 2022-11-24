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
function update($db)	{
		$sp = 'spu_item_factura';
		
		for ($i = 0; $i < $this->row_count(); $i++) {
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$COD_ITEM_FACTURA		= $this->get_item($i, 'COD_ITEM_FACTURA');
			$COD_FACTURA 			= $this->get_item($i, 'COD_FACTURA');
			$ORDEN 					= $this->get_item($i, 'ORDEN');
			$ITEM 					= $this->get_item($i, 'ITEM');
			$COD_PRODUCTO 			= $this->get_item($i, 'COD_PRODUCTO');
			$NOM_PRODUCTO 			= $this->get_item($i, 'NOM_PRODUCTO');
			$PRECIO 				= $this->get_item($i, 'PRECIO');
			$COD_ITEM_DOC			= $this->get_item($i, 'COD_ITEM_DOC');			
			$CANTIDAD 				= $this->get_item($i, 'CANTIDAD');		
			$COD_TIPO_TE			= $this->get_item($i, 'COD_TIPO_TE');
			$COD_TIPO_GAS 			= $this->get_item($i, 'COD_TIPO_GAS');
			$COD_TIPO_ELECTRICIDAD 	= $this->get_item($i, 'COD_TIPO_ELECTRICIDAD');
			
			$COD_TIPO_TE			= ($COD_TIPO_TE =='') ? "null" : "$COD_TIPO_TE";			
			$MOTIVO_TE		 		= $this->get_item($i, 'MOTIVO_TE');			
			$MOTIVO_TE		 		= ($MOTIVO_TE =='') ? "null" : "'".$MOTIVO_TE."'";
			$TIPO_DOC				="null";
			$COD_TIPO_GAS			="null";
			$COD_TIPO_ELECTRICIDAD  ="null";
			if ($PRECIO=='') $PRECIO = 0;		
		
			$COD_ITEM_FACTURA   = ($COD_ITEM_FACTURA=='') ? "null" : $COD_ITEM_FACTURA;
			$COD_ITEM_DOC = ($COD_ITEM_DOC=='') ? "null" : $COD_ITEM_DOC;
			$COD_TIPO_GAS = ($COD_TIPO_GAS=='') ? "null" : $COD_TIPO_GAS;
			$COD_TIPO_ELECTRICIDAD = ($COD_TIPO_ELECTRICIDAD=='') ? "null" : $COD_TIPO_ELECTRICIDAD;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			else if ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';		
				
			$param = "'$operacion', $COD_ITEM_FACTURA, $COD_FACTURA, $ORDEN, '$ITEM', '$COD_PRODUCTO', '$NOM_PRODUCTO', $CANTIDAD, $PRECIO, $COD_ITEM_DOC, $COD_TIPO_TE, $MOTIVO_TE, $TIPO_DOC, $COD_TIPO_GAS, $COD_TIPO_ELECTRICIDAD";
			
			if (!$db->EXECUTE_SP($sp, $param)) 
				return false;
			else {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$COD_ITEM_FACTURA = $db->GET_IDENTITY();
					$this->set_item($i, 'COD_ITEM_FACTURA', $COD_ITEM_FACTURA);		
				}
			}
		}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_ITEM_FACTURA = $this->get_item($i, 'COD_ITEM_FACTURA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_ITEM_FACTURA"))
				return false;
		}	
		return true;
	}
}	
?>
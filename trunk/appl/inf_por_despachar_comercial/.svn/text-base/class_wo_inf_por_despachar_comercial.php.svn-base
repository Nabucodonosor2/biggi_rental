<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_modelo.php");

class wo_inf_por_despachar_comercial extends w_informe_pantalla {
   function wo_inf_por_despachar_comercial() {
   		/* El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		   hasta despues de llamar al ancestro
   		 */  
   		$cod_usuario =  session::get("COD_USUARIO");
	   $sql = "SELECT  P.COD_PRODUCTO
        				,P.NOM_PRODUCTO
        				,SUM(DBO.F_NV_CANT_POR_DESPACHAR(INV.COD_ITEM_NOTA_VENTA, DEFAULT)) CANTIDAD
				 FROM   BIGGI.DBO.NOTA_VENTA NV, BIGGI.DBO.ITEM_NOTA_VENTA INV, BIGGI.DBO.PRODUCTO P, PRODUCTO PB
				WHERE   NV.COD_NOTA_VENTA = INV.COD_NOTA_VENTA 
				  AND   P.COD_PRODUCTO = INV.COD_PRODUCTO 
				  AND	DBO.F_NV_CANT_POR_DESPACHAR(INV.COD_ITEM_NOTA_VENTA, DEFAULT) > 0 
				  AND   NV.COD_ESTADO_NOTA_VENTA <> 3    -- ANULADA
           		  AND	P.COD_PRODUCTO = PB.COD_PRODUCTO
				  AND   PB.MANEJA_INVENTARIO = 'S'
			 GROUP BY   P.COD_PRODUCTO,P.NOM_PRODUCTO
			 ORDER BY   COD_PRODUCTO";
		parent::w_informe_pantalla('inf_por_despachar_comercial', $sql, $_REQUEST['cod_item_menu']);
		
		// headers
		$this->add_header(new header_num('COD_PRODUCTO', 'P.COD_PRODUCTO', 'Codigo'));
		$this->add_header(new header_text('NOM_PRODUCTO', "P.NOM_PRODUCTO", 'Nombre'));
		$this->add_header(new header_num('CANTIDAD', 'CANTIDAD', 'Cantidad'));
   }
	function print_informe() {
		$cod_usuario = $_POST['wo_hidden'];

		// reporte
		$sql = "exec spr_por_despachar_comercial $cod_usuario";
		// selecciona xml
		if ($cod_usuario==0)
			$xml = session::get('K_ROOT_DIR').'appl/inf_por_despachar/inf_por_despachar_global_comercial.xml';
		else
			$xml = session::get('K_ROOT_DIR').'appl/inf_por_despachar/inf_por_despachar_comercial.xml';
		$labels = array();
		$labels['str_fecha'] = $this->current_date();
		$rpt = new reporte($sql, $xml, $labels, "Por despachar", true);
		
		$this->_redraw();
	}
}
?>
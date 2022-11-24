<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_param_informe_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_reporte_biggi.php");

class wi_inf_tdnx_inventario extends w_param_informe_biggi {
	
	function wi_inf_tdnx_inventario($cod_item_menu) {
		$xml = session::get('K_ROOT_DIR').'appl/inf_tdnx_inventario/inf_tdnx_inventario.xml';
		parent::w_param_informe_biggi('inf_tdnx_inventario',$cod_item_menu,'Inventario',$xml,'','spi_tdnx_inventario');
		
		// del 1ero del mes hasta hoy
		$sql = "SELECT  '' COD_BODEGA
						,CONVERT(VARCHAR, GETDATE(), 103) FECHA
						,NULL COD_CLASIF_INVENTARIO
						,'RESUMEN' RESUMEN
						,'RESUMEN' DETALLE";
		$this->dws['dw_param'] = new datawindow($sql);
		
		$sql_bodega="SELECT COD_BODEGA
							,NOM_BODEGA
					FROM	BODEGA
					WHERE	COD_BODEGA = 1
					ORDER BY COD_BODEGA ASC";
		$this->dws['dw_param']->add_control(new drop_down_dw('COD_BODEGA', $sql_bodega, 0, '', false));
		$this->dws['dw_param']->add_control(new edit_date('FECHA'));
		$this->dws['dw_param']->add_control(new edit_radio_button('RESUMEN', 'RESUMEN', 'DETALLE', 'Resumen', 'TIPO'));
		$this->dws['dw_param']->add_control(new edit_radio_button('DETALLE', 'DETALLE', 'RESUMEN', 'Detalle', 'TIPO'));
		$sql_clasif="SELECT COD_CLASIF_INVENTARIO
							,NOM_CLASIF_INVENTARIO
					FROM	CLASIF_INVENTARIO
					ORDER BY COD_CLASIF_INVENTARIO ASC";
		$this->dws['dw_param']->add_control(new drop_down_dw('COD_CLASIF_INVENTARIO', $sql_clasif, 0, '', false));
		
		// mandatorys		
		$this->dws['dw_param']->set_mandatory('COD_BODEGA', 'Bodega');	
		$this->dws['dw_param']->set_mandatory('FECHA', 'Fecha');
		$this->dws['dw_param']->set_mandatory('COD_CLASIF_INVENTARIO', 'Clasificación');
	}
	
	function make_filtro() {
		$fecha 				= $this->dws['dw_param']->get_item(0, 'FECHA'); 
		$cod_bodega			= $this->dws['dw_param']->get_item(0, 'COD_BODEGA');  
		$nom_bodega 		= $this->dws['dw_param']->controls['COD_BODEGA']->get_label_from_value($cod_bodega);
		$cod_clasif			= $this->dws['dw_param']->get_item(0, 'COD_CLASIF_INVENTARIO');
		$nom_clasif 		= $this->dws['dw_param']->controls['COD_CLASIF_INVENTARIO']->get_label_from_value($cod_clasif);
		
		//FILTRO
		$this->filtro = "Fecha = $fecha \n";
		$this->filtro = "Bodega = $nom_bodega";
		$this->filtro = "Clasificación = $nom_clasif";

		// Arma los paramaetros para el SP
		$fecha = $this->str2date($fecha, '23:59:59');				
		$this->param = "$cod_bodega, $fecha, $cod_clasif";
	}
}
?>
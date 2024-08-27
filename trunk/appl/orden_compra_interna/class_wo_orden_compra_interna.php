<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../../appl.ini");

class header_vendedor_biggi extends header_drop_down {
	function header_vendedor_biggi($field, $field_bd, $nom_header) {
		$sql = "SELECT U.COD_USUARIO, U.NOM_USUARIO 
			  FROM BIGGI.dbo.USUARIO U 
			 WHERE U.VENDEDOR_VISIBLE_FILTRO = 1 
		  ORDER BY NOM_USUARIO ASC";
		parent::header_drop_down($field, $field_bd, $nom_header, $sql);
	}
	function make_java_script() {
		return '"return dlg_find_vendedor(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', \''.$this->sql.'\', this);"';		
	}
}

class wo_orden_compra_interna extends w_output_biggi {
   	function wo_orden_compra_interna() {
   		
   		$sql = "SELECT COD_ORDEN_COMPRA
                    ,CONVERT(VARCHAR, FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
                    ,FECHA_ORDEN_COMPRA DATE_FECHA_ORDEN_COMPRA
                    ,REFERENCIA
                    ,COD_NOTA_VENTA
                    ,INI_USUARIO
                    ,TOTAL_NETO
                    ,OC.COD_USUARIO
                FROM BIGGI.dbo.ORDEN_COMPRA OC
                    ,BIGGI.dbo.USUARIO U
                WHERE OC.COD_EMPRESA = 1337
                AND ES_OCI_RENTAL = 'S'
                AND OC.COD_USUARIO = U.COD_USUARIO
                ORDER BY COD_ORDEN_COMPRA DESC";		
			
   		parent::w_output_biggi('orden_compra_interna', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_nro_doc('COD_ORDEN_COMPRA','ORDEN_COMPRA'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
		
		// headers
		$this->add_header(new header_num('COD_ORDEN_COMPRA', 'COD_ORDEN_COMPRA', 'Nº OC'));
		$this->add_header($control = new header_date('FECHA_ORDEN_COMPRA', 'FECHA_ORDEN_COMPRA', 'Fecha OC'));
		$control->field_bd_order = 'DATE_ORDEN_COMPRA';
		$this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
		$this->add_header(new header_num('COD_NOTA_VENTA', 'COD_NOTA_VENTA', 'Nº NV'));
        $this->add_header(new header_vendedor_biggi('INI_USUARIO', 'OC.COD_USUARIO', 'V1'));
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));  
   	}

    function make_menu($temp) {
	    $menu = session::get('menu_appl');
	    $menu_original = $menu->ancho_completa_menu;
	    $menu->ancho_completa_menu = 217;
	    $menu->draw($temp);
	    $menu->ancho_completa_menu = $menu_original;    // volver a setear el tamaño original
	}
}
?>
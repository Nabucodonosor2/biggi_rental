<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_cot_arriendo extends w_output_biggi{
	function wo_cot_arriendo() {

		$sql = "SELECT	C.COD_COT_ARRIENDO
						,CONVERT(VARCHAR(20), C.FECHA_COTIZACION, 103) FECHA_COTIZACION
						,C.FECHA_COTIZACION DATE_COTIZACION
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,C.REFERENCIA
						,C.COD_USUARIO_VENDEDOR1
						,U.INI_USUARIO
						,EC.NOM_ESTADO_COTIZACION
						,C.TOTAL_NETO
						,NV.COD_NOTA_VENTA
			FROM 		COT_ARRIENDO C LEFT OUTER JOIN NOTA_VENTA NV ON NV.COD_COTIZACION = C.COD_COT_ARRIENDO
						,EMPRESA E
						,USUARIO U
						,ESTADO_COTIZACION EC
			WHERE		C.COD_EMPRESA = E.COD_EMPRESA AND 
						C.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO AND 
						C.COD_ESTADO_COTIZACION = EC.COD_ESTADO_COTIZACION
						AND dbo.f_get_tiene_acceso(".$this->cod_usuario.", 'COTIZACION',C.COD_USUARIO_VENDEDOR1, C.COD_USUARIO_VENDEDOR2) = 1
			ORDER BY	C.COD_COT_ARRIENDO DESC";
			
     	parent::w_output_biggi('cot_arriendo', $sql, $_REQUEST['cod_item_menu']);
			
		$this->dw->add_control(new edit_nro_doc('COD_COT_ARRIENDO','COT_ARRIENDO'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
      	$this->dw->add_control(new static_num('RUT'));
			
	      // headers
      	$this->add_header($control = new header_date('FECHA_COTIZACION', 'C.FECHA_COTIZACION', 'Fecha'));
	    $control->field_bd_order = 'DATE_COTIZACION';
	    $this->add_header(new header_num('COD_COT_ARRIENDO', 'C.COD_COT_ARRIENDO', 'Nº Cot.'));
	    $this->add_header(new header_rut('RUT', 'E', 'Rut'));
	      
	    $this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Razón Social'));
	    $this->add_header(new header_text('REFERENCIA', 'C.REFERENCIA', 'Referencia'));
		$this->add_header(new header_vendedor('INI_USUARIO', 'C.COD_USUARIO_VENDEDOR1', 'Vend'));

	    $this->add_header($control = new header_num('COD_NOTA_VENTA', 'isnull(NV.COD_NOTA_VENTA, 0)', 'NV'));
	    $control->field_bd_order = 'COD_NOTA_VENTA';
	    
	    $this->add_header(new header_num('TOTAL_NETO', 'C.TOTAL_NETO', 'Total Neto'));
  	}
	function crear_cot_from_cot($cod_cot_arriendo) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_COT_ARRIENDO FROM COT_ARRIENDO WHERE COD_COT_ARRIENDO = $cod_cot_arriendo";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La cotización arriendo Nº '.$cod_cot_arriendo.' no existe.');								
			return;
		}
			
		session::set('COT_CREADA_DESDE', $cod_cot_arriendo);
		$this->add();
	}
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='create' && $habilita){
			$ruta_over = "'../../../../commonlib/trunk/images/b_create_over.jpg'";
			$ruta_out = "'../../../../commonlib/trunk/images/b_create.jpg'";
			$ruta_click = "'../../../../commonlib/trunk/images/b_create_click.jpg'";
			$temp->setVar("WO_ADD_DESDE", '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
							'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../../../commonlib/trunk/images/b_'.$boton.'.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
							'onClick="request_crear_desde(\'Ingrese Nº de Cotización\',\'\');" />');
			
		}else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	function redraw(&$temp) {
		parent::redraw($temp);
		$this->habilita_boton($temp, 'create', true);		
			
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_cot_from_cot($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>
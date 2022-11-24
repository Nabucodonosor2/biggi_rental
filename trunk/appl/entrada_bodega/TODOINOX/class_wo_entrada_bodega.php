<?php
//require_once(dirname(__FILE__) . "/../../../../../commonlib/trunk/php/auto_load.php");
class wo_entrada_bodega extends wo_entrada_bodega_base {
	function wo_entrada_bodega() {      
		parent::wo_entrada_bodega_base(); 
	}
	function crear_entrada_desde($valor_devuelto) {
		
		list($opcion, $nro_opcion)=split('[|]', $valor_devuelto);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if ($opcion=='desde_ri'){

			
				$sql = "select NUMERO_REGISTRO_INGRESO 				
						from REGISTRO_INGRESO_4D 
						where NUMERO_REGISTRO_INGRESO = $nro_opcion";
			$result = $db->build_results($sql);
			if (count($result) == 0){
				$this->_redraw();
				$this->alert('El Nº Registro Ingreso '.$nro_opcion.' no existe');								
				return;
			}
			
			session::set('IB_CREAR_DESDE_RI', $nro_opcion);
			$this->add();
			
		}else if($opcion=='desde_oc'){
			$sql = "select cod_orden_compra
					from orden_compra
					where cod_orden_compra = $nro_opcion";
					
		$result = $db->build_results($sql);
			if (count($result) == 0){
				$this->_redraw();
				$this->alert('El Nº Orden Compra '.$nro_opcion.' no existe');								
				return;
			}
			session::set('IB_CREAR_DESDE_OC', $nro_opcion);
			$this->add();
		}
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_entrada_desde($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>
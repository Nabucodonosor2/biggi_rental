<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_arriendo = $_REQUEST['cod_arriendo'];
	 $K_ARRIENDO_APROBADO	= 2;
	 $K_TIPO_ARRIENDO		= 5;
	 $K_ESTADO_SII_EMITIDA	= 1;
   
 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		///valida que exista
		$sql = "select COD_ESTADO_ARRIENDO 
				from ARRIENDO 
				where COD_ARRIENDO = $cod_arriendo";
		 		
		$result = $db->build_results($sql);
		if (count($result) == 0){
		//	$this->_redraw();
			$respuesta = 'NO_EXISTE';
			print "$respuesta|$cod_arriendo";
			return false;
			//$this->alert('El contrato de arriendo Nº '.$cod_arriendo.' no existe.');								
			//return;
		}
		if ($result[0]['COD_ESTADO_ARRIENDO']!= $K_ARRIENDO_APROBADO) {	
		//	$this->_redraw();
			$respuesta = 'NO_CONFIRMADO';
			print "$respuesta|$cod_arriendo";
			return false;
			//$this->alert('El contrato de arriendo Nº '.$cod_arriendo.' no esta confirmado.');								
			//return;
		}
		/* valida que el ARR no tenga GDs anteriores en estado = emitida
		ya que es suceptible a errores tener varias GD en estado emitida, ya que la cantidad por despachar siempre será la misma 
		cantidad de la NV.
		*/
		$sql = "select COD_GUIA_DESPACHO
				from GUIA_DESPACHO
				where COD_DOC in (select cod_mod_arriendo from MOD_ARRIENDO where COD_ARRIENDO= $cod_arriendo) 
				and COD_TIPO_GUIA_DESPACHO = ".$K_TIPO_ARRIENDO."
				and COD_ESTADO_DOC_SII = ".$K_ESTADO_SII_EMITIDA;
		$result = $db->build_results($sql);
		if (count($result) != 0){
			//$this->_redraw();
			$respuesta='PENDIENTES';
			print "$respuesta|$cod_arriendo";//$this->alert('El contrato de arriendo Nº '.$cod_arriendo.' tiene Guía(s) pendientes(s) en estado emitido. Para poder generar más guías deberá imprimir los documentos emitidos.');						
			return false;
			//return;
		}
		// valida que hayan item por despachar
		$sql = "select isnull(SUM(dbo.f_arr_cant_por_despachar(i.cod_item_mod_arriendo, null)),0) POR_DESPACHAR
				from MOD_ARRIENDO m, ITEM_MOD_ARRIENDO i
				where m.COD_ARRIENDO = $cod_arriendo
				and m.COD_ESTADO_MOD_ARRIENDO = 2	--confirmado
				and m.TIPO_MOD_ARRIENDO = 'AGREGAR'
				and i.COD_MOD_ARRIENDO = m.COD_MOD_ARRIENDO
				and dbo.f_arr_cant_por_despachar(i.cod_item_mod_arriendo, null) > 0";
		$result = $db->build_results($sql);
		$por_despachar = $result[0]['POR_DESPACHAR'];
		if ($por_despachar <= 0){
			//$this->_redraw();
			$respuesta='DESPACHADO';
			print "$respuesta|$cod_arriendo";
			return false;
		}
?>
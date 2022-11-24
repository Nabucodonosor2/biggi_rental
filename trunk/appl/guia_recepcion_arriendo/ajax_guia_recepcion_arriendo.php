<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_arriendo = $_REQUEST['cod_arriendo'];
	 $K_ARRIENDO_APROBADO	= 2;
	 $K_TIPO_ARRIENDO		= 4;
	 $K_ESTADO_EMITIDA	= 1;
   
 
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
			//$this->alert('El contrato de arriendo N� '.$cod_arriendo.' no existe.');								
			//return;
		}
		else if ($result[0]['COD_ESTADO_ARRIENDO']!= $K_ARRIENDO_APROBADO) {	
		//	$this->_redraw();
			$respuesta = 'NO_CONFIRMADO';
			print "$respuesta|$cod_arriendo"; 
			//$this->alert('El contrato de arriendo N� '.$cod_arriendo.' no esta confirmado.');								
			//return;
		}
		else{
		
			$sql = "select COD_GUIA_RECEPCION
					from GUIA_RECEPCION
					where COD_DOC in (select cod_mod_arriendo from MOD_ARRIENDO where COD_ARRIENDO= $cod_arriendo) 
					and COD_TIPO_GUIA_RECEPCION = ".$K_TIPO_ARRIENDO."
					and COD_ESTADO_GUIA_RECEPCION = ".$K_ESTADO_EMITIDA;
			$result = $db->build_results($sql);
			if (count($result) != 0){
				//$this->_redraw();
				$respuesta = 'PENDIENTES';
				print "$respuesta|$cod_arriendo"; //$this->alert('El contrato de arriendo N� '.$cod_arriendo.' tiene Gu�a(s) pendientes(s) en estado emitido. Para poder generar m�s gu�as deber� imprimir los documentos emitidos.');						
				//return;
			}else{
		
				// valida que hayan item por despachar
				$sql = "select isnull(SUM(dbo.f_arr_cant_por_recepcionar(i.cod_item_mod_arriendo, null)),0) POR_RECEPCIONAR
						from MOD_ARRIENDO m, ITEM_MOD_ARRIENDO i
						where m.COD_ARRIENDO = $cod_arriendo
						and m.COD_ESTADO_MOD_ARRIENDO = 2	--confirmado
						and m.TIPO_MOD_ARRIENDO = 'ELIMINAR'
						and i.COD_MOD_ARRIENDO = m.COD_MOD_ARRIENDO
						and dbo.f_arr_cant_por_recepcionar(i.cod_item_mod_arriendo, null) > 0";
				$result = $db->build_results($sql);
				$por_recepcionar = $result[0]['POR_RECEPCIONAR'];
				if ($por_recepcionar <= 0){
					//$this->_redraw();
					$respuesta ='RECEPCIONADO';
					print "$respuesta|$cod_arriendo";
				}
			}	
		}
?>
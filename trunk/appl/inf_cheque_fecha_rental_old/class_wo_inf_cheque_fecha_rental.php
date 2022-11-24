<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_inf_cheque_fecha_rental extends w_informe_pantalla{
	function wo_inf_cheque_fecha_rental(){

		$sql = "SELECT COD_CHEQUE
						,CASE 
							WHEN C.COD_INGRESO_PAGO_ORIGEN IS NULL THEN (SELECT NOM_EMPRESA 
																		 FROM EMPRESA
																		 WHERE COD_EMPRESA = IC.COD_EMPRESA)
							ELSE (SELECT NOM_EMPRESA 
								  FROM EMPRESA
								  WHERE COD_EMPRESA = IP.COD_EMPRESA)
						END NOM_EMPRESA
						,CASE 
							WHEN C.COD_INGRESO_PAGO_ORIGEN IS NULL THEN (SELECT CONVERT(VARCHAR, RUT) +'-'+ DIG_VERIF
																		 FROM EMPRESA 
																		 WHERE COD_EMPRESA = IC.COD_EMPRESA)
							ELSE (SELECT CONVERT(VARCHAR, RUT) +'-'+ DIG_VERIF 
								  FROM EMPRESA
								  WHERE COD_EMPRESA = IP.COD_EMPRESA)
						END RUT
						,CASE 
							WHEN C.COD_INGRESO_PAGO_ORIGEN IS NULL THEN IC.COD_INGRESO_CHEQUE
							ELSE IP.COD_INGRESO_PAGO
						END COD_ORIGEN
						,CASE 
							WHEN C.COD_INGRESO_PAGO_ORIGEN IS NULL THEN IC.COD_INGRESO_CHEQUE
							ELSE IP.COD_INGRESO_PAGO
						END COD_INGRESO_PAGO
						,CASE 
							WHEN C.COD_INGRESO_PAGO_ORIGEN IS NULL THEN IC.COD_INGRESO_CHEQUE
							ELSE IP.COD_INGRESO_PAGO
						END COD_INGRESO_CHEQUE
						,CASE 
							WHEN C.COD_INGRESO_PAGO_ORIGEN IS NULL THEN 'Registro Cheque'
							ELSE 'Ingreso pago'
						END TIPO
						,CASE
							WHEN C.COD_INGRESO_PAGO_ORIGEN IS NULL THEN 'S'
							ELSE 'N'
						END ES_REGISTRO_CHEQUE
						,CONVERT(VARCHAR, C.FECHA_DOC, 103) FECHA_DOC
						,C.FECHA_DOC DATE_FECHA_DOC
						,C.NRO_DOC
						,B.NOM_BANCO
						,C.MONTO_DOC
						,C.DEPOSITADO
						,C.LIBERADO
						,0 CANT_DOC
						,NULL SELECCION
				FROM CHEQUE C LEFT OUTER JOIN INGRESO_CHEQUE IC ON C.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE
							  LEFT OUTER JOIN INGRESO_PAGO IP ON IP.COD_INGRESO_PAGO = C.COD_INGRESO_PAGO_ORIGEN
					,BANCO B
				WHERE B.COD_BANCO = C.COD_BANCO			  
				ORDER BY DATE_FECHA_DOC ASC";

		parent::w_informe_pantalla('inf_cheque_fecha_rental', $sql, $_REQUEST['cod_item_menu']);
		
		$this->dw->entrable = true;
		$this->dw->add_control(new edit_check_box('SELECCION', 'S', 'N'));
		$this->dw->add_control(new edit_check_box('DEPOSITADO', 'S', 'N'));
		$this->dw->add_control(new edit_check_box('LIBERADO', 'S', 'N'));
		$this->dw->add_control(new edit_text_hidden('ES_REGISTRO_CHEQUE'));
		$this->dw->add_control(new edit_text_hidden('COD_INGRESO_PAGO'));
		$this->dw->add_control(new edit_text_hidden('COD_INGRESO_CHEQUE'));
		$this->dw->add_control(new static_num('MONTO_DOC'));
		$this->dw->set_entrable('DEPOSITADO', false);
		$this->dw->set_entrable('LIBERADO', false);
		
		$sql = "SELECT 'S' DEPOSITADO
					  ,'Si' NOM_DEPOSITADO
				UNION
				SELECT 'N' DEPOSITADO
					  ,'No' NOM_DEPOSITADO";
		$this->add_header(new header_drop_down_string('DEPOSITADO', 'C.DEPOSITADO', 'Dep.', $sql));
		
		$sql = "SELECT 'S' LIBERADO
					  ,'Si' NOM_LIBERADO
				UNION
				SELECT 'N' LIBERADO
					  ,'No' NOM_LIBERADO";
		$this->add_header($liberado = new header_drop_down_string('LIBERADO', 'C.LIBERADO', 'Lib.', $sql));
		
		$this->add_header(new header_num('COD_CHEQUE', 'COD_CHEQUE', 'Cod.'));
		$this->add_header(new header_text('NOM_EMPRESA', "NOM_EMPRESA", 'Cliente'));
		$this->add_header(new header_text('RUT', "RUT", 'Rut'));
		$this->add_header(new header_num('COD_ORIGEN', 'COD_ORIGEN', 'Cod.'));
		$this->add_header(new header_text('TIPO', "TIPO", 'Tipo'));
		$this->add_header($header = new header_date('FECHA_DOC', 'CONVERT(VARCHAR, C.FECHA_DOC, 103)', 'Fecha'));
		$header->field_bd_order = 'DATE_FECHA_DOC';	
		$this->add_header(new header_num('NRO_DOC', 'NRO_DOC', 'Número'));
		$sql = "SELECT COD_BANCO, NOM_BANCO FROM BANCO order by	COD_BANCO";
		$this->add_header(new header_drop_down('NOM_BANCO', 'COD_BANCO', 'Banco', $sql));
		$this->add_header(new header_num('MONTO_DOC', 'C.MONTO_DOC', 'Monto', 0, true, 'SUM'));

		$liberado->valor_filtro = 'N';
		$this->make_filtros();
	}
	function redraw(&$temp){
		parent::redraw($temp);
		$this->habilita_boton($temp, 'b_export', false);
	}
	function paginacion(&$temp){
		parent::paginacion($temp);
		$temp->setVar("CANT_DOC", $this->row_count_output);
		$temp->setVar("CANT_REG_H", '<input id="CANT_REG_H_0" class="input_text" type="hidden" maxlength="100" size="100" value="'.$this->row_count_output.'" name="CANT_REG_H_0">');
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COUNT(*) CANT_TOT_NO_DEP
					  ,dbo.number_format(SUM(MONTO_DOC), 0, ',', '.') MONTO_TOT_NO_DEP
				FROM CHEQUE
				WHERE DEPOSITADO = 'N'";
		$result = $db->build_results($sql);
		$temp->setVar("CANT_TOT_NO_DEP", $result[0]['CANT_TOT_NO_DEP']." / $ ".$result[0]['MONTO_TOT_NO_DEP']);
		
		$sql = "SELECT COUNT(*) CANT_TOT_DEP_NO_LIB
					  ,dbo.number_format(SUM(MONTO_DOC), 0, ',', '.') MONTO_TOT_DEP_NO_LIB
				FROM CHEQUE
				WHERE DEPOSITADO = 'S'
				AND LIBERADO = 'N'";
		$result = $db->build_results($sql);
		$temp->setVar("CANT_TOT_DEP_NO_LIB", $result[0]['CANT_TOT_DEP_NO_LIB']." / $ ".$result[0]['MONTO_TOT_DEP_NO_LIB']);
	}
	
	function detalle_record($rec_no){
		session::set('DESDE_wo_inf_cheque_fecha_rental', 'true');
		$ROOT = $this->root_url;
		$this->dw->get_values_from_POST();

		/////////////////
		// busca la row
		for($i=0; $i<$this->dw->row_count();$i++)
			if ($this->dw->get_item($i, 'ROW')==$rec_no) {
				$row = $i;
				break;
			}
		/////////////////
			
		$es_registro_cheque = $this->dw->get_item($row, 'ES_REGISTRO_CHEQUE');
		
		if($es_registro_cheque == 'S'){
			session::set('DESDE_wo_ingreso_cheque', 'desde output');	// para indicar que viene del output
			$url = $ROOT.'appl/ingreso_cheque';
			header ('Location:'.$url.'/wi_ingreso_cheque.php?rec_no='.$rec_no.'&cod_item_menu=2575');
		}else{
			session::set('DESDE_wo_ingreso_pago', 'desde output');	// para indicar que viene del output
			$url = $ROOT.'appl/ingreso_pago';
			header ('Location:'.$url.'/wi_ingreso_pago.php?rec_no='.$rec_no.'&cod_item_menu=2505');
		}
	}
}
?>
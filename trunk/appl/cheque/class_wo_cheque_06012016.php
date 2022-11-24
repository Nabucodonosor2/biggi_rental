<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class edit_date_protect extends edit_date{
	var $entrable_protect = false;

	function edit_date_protect($field){
		parent::edit_date($field);
	}
	function draw_entrable($dato, $record){
		if ($this->entrable_protect)
			return parent::draw_entrable($dato, $record);
		else
		return parent::draw_no_entrable($dato, $record);
	}
}

class edit_check_box_protect extends edit_check_box{
	var $entrable_protect = false;

	function edit_check_box_protect($field, $value_true, $value_false){
		parent::edit_check_box($field, $value_true, $value_false);
	}
	function draw_entrable($dato, $record){
		if ($this->entrable_protect)
			return parent::draw_entrable($dato, $record);
		else
			return parent::draw_no_entrable($dato, $record);
	}
}

class wo_cheque extends w_output_biggi{
	function wo_cheque() {
		$cod_usuario =  session::get("COD_USUARIO");
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spo_cheque", "$cod_usuario"); 
   		$sql = "select	ORIGEN
					,COD_ORIGEN                 
					,COD_CHEQUE_H
					,NRO_DOC
					,CONVERT(VARCHAR,FECHA_DOC,103) FECHA_DOC
					,DATE_FECHA_DOC
					,DEPOSITADO
					,CONVERT(VARCHAR,FECHA_DEPOSITADO,103) FECHA_DEPOSITADO
					,DATE_FECHA_DEPOSITADO
					,LIBERADO
					,CONVERT(VARCHAR,FECHA_LIBERADO,103) FECHA_LIBERADO
					,DATE_FECHA_LIBERADO
					,MONTO_DOC
					,RUT
					,DIG_VERIF
					,NOM_EMPRESA
					,COD_INGRESO_PAGO
				FROM DEPOSITO_CHEQUE 
				where COD_USUARIO_GENERA =$cod_usuario
				ORDER BY DATE_FECHA_DOC";
		parent::w_output_biggi('cheque', $sql, $_REQUEST['cod_item_menu']);

		$this->dw->add_control($control = new edit_check_box_protect('DEPOSITADO', 'S', 'N'));
		$control->set_onChange('marcado_deposito(this);');
		$this->dw->add_control($control = new edit_check_box_protect('LIBERADO', 'S', 'N'));
		$control->set_onChange('valida_liberado(this); marcado_liberado(this);');
		$this->dw->add_control(new static_num('MONTO_DOC'));
		$this->dw->add_control(new edit_date_protect('FECHA_DEPOSITADO'));
		$this->dw->add_control($control = new edit_date_protect('FECHA_LIBERADO'));
		$control->set_onChange('valida_liberado(this);');
		$this->dw->add_control(new edit_text_hidden('COD_CHEQUE_H'));
		$this->dw->add_control(new static_num('RUT'));

		// 	headers
		$sql = "SELECT 'Ingreso Pago' ES_ORIGEN, 'Ingreso Pago' ORIGEN
				UNION 
				SELECT 'Registro Cheque' ES_ORIGEN , 'Registro Cheque' ORIGEN";
		$this->add_header(new header_drop_down_string('ORIGEN', 'ORIGEN', 'ORIGEN', $sql));
		
		$this->add_header(new header_num('COD_ORIGEN', 'COD_ORIGEN', 'Cod. Origen'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razon Social'));
		$this->add_header(new header_text('COD_INGRESO_PAGO', 'COD_INGRESO_PAGO', 'Ing. Pago'));
		$this->add_header(new header_rut('RUT', 'RUT', 'Rut'));
		$this->add_header(new header_num('NRO_DOC', 'NRO_DOC', 'Nro. Doc'));
		$this->add_header(new header_date('FECHA_DOC', 'CONVERT(VARCHAR, FECHA_DOC, 103)', 'Fecha Doc.'));
		//$control->field_bd_order = 'DATE_FECHA_DOC';

		$sql = "SELECT 'S' DEPOSITADO
					  ,'Si' NOM_DEPOSITADO
				UNION
				SELECT 'N' DEPOSITADO
					  ,'No' NOM_DEPOSITADO";
		$this->add_header(new header_drop_down_string('DEPOSITADO', "DEPOSITADO", 'Depos.', $sql));
		$this->add_header(new header_date('FECHA_DEPOSITADO', 'CONVERT(VARCHAR, FECHA_DEPOSITADO, 103)', 'Fecha Depósito'));

		$sql = "SELECT 'S' LIBERADO
					  ,'Si' NOM_LIBERADO
				UNION
				SELECT 'N' LIBERADO
					  ,'No' NOM_LIBERADO";
		$this->add_header($header = new header_drop_down_string('LIBERADO', "LIBERADO", 'Liberado', $sql));
		$this->add_header(new header_date('FECHA_LIBERADO', 'CONVERT(VARCHAR, FECHA_LIBERADO, 103)', 'Fecha Liberado'));
		$this->add_header(new header_num('MONTO_DOC', 'MONTO_DOC', 'Monto Doc.'));

		
		$header->valor_filtro = 'N';
		$this->row_per_page = 50;
		$this->make_filtros();
		
	}
	function redraw(&$temp) {
		parent::redraw($temp);
		$this->habilita_boton($temp, 'no_save', $this->modify);
		$this->habilita_boton($temp, 'save', $this->modify);
		$this->habilita_boton($temp, 'modify', !$this->modify);
	}
	function save() {
		$this->dw->get_values_from_POST();
		$sp = 'spu_cheque';
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->BEGIN_TRANSACTION();
		$error = false;
		$cod_usuario =  session::get("COD_USUARIO");

		$ind = $this->row_per_page * ($this->current_page - 1);
		// loop en los registros de la pagina visible
		$i = 0;
		while (($i < $this->row_per_page) && ($ind < $this->row_count_output)){
			$depositado			= $this->dw->get_item($i, 'DEPOSITADO');
			$liberado			= $this->dw->get_item($i, 'LIBERADO');
	
			if($depositado == 'S' || $liberado == 'S'){

				$fecha_depositado	= $this->dw->get_item($i, 'FECHA_DEPOSITADO');
				$fecha_liberado		= $this->dw->get_item($i, 'FECHA_LIBERADO');
				$cod_cheque			= $this->dw->get_item($i, 'COD_CHEQUE_H');
				
				$liberado			= ($liberado =='') ? "'N'" : "'$liberado'";
				$fecha_liberado		= ($fecha_liberado =='') ? "NULL" : $this->str2date($fecha_liberado);
	
				$param = "'UPDATE_ESP'
							,$cod_cheque
							,NULL
							,NULL
							,NULL
							,NULL
							,NULL
							,NULL
							,NULL
							,'$depositado'
							,".$this->str2date($fecha_depositado)."
							,$cod_usuario
							,$liberado
							,$fecha_liberado
							,$cod_usuario";
	
				if (!$db->EXECUTE_SP($sp, $param)) {
					$error = true;
					$db->ROLLBACK_TRANSACTION();
					$error_sp = $db->GET_ERROR();
					$this->alert('No se pudo grabar el registro.\n\n'.$db->make_msg_error_bd());
					break;
				}
			}
			$i++;
			$ind++;
		}
		if(!$error)
			$db->COMMIT_TRANSACTION();

		$this->modify = false;
		$this->dw->entrable = false;
		$this->retrieve();
	}
	function redraw_item(&$temp, $ind, $record) {
		$liberado	= $this->dw->get_item($record, 'LIBERADO');
		$depositado = $this->dw->get_item($record, 'DEPOSITADO');

		if ($liberado=='S'){
			$this->dw->controls['LIBERADO']->entrable_protect = false;
			$this->dw->controls['FECHA_LIBERADO']->entrable_protect = false;
		}else{
			$this->dw->controls['LIBERADO']->entrable_protect = true;
			$this->dw->controls['FECHA_LIBERADO']->entrable_protect = true;
		}
	
		if ($depositado=='S'){
			$this->dw->controls['DEPOSITADO']->entrable_protect = false;
			$this->dw->controls['FECHA_DEPOSITADO']->entrable_protect = false;
		}else{
			$this->dw->controls['DEPOSITADO']->entrable_protect = true;
			$this->dw->controls['FECHA_DEPOSITADO']->entrable_protect = true;
		}

		parent::redraw_item($temp, $ind, $record);
	}
	function procesa_event() {
		if(isset($_POST['b_modify_x'])){
			$this->modify = true;
			$this->dw->entrable = true;
			$this->_redraw();
		}
		elseif(isset($_POST['b_no_save_x'])){
			$this->modify = false;
			$this->dw->entrable = false;
			$this->_redraw();
		}
		elseif(isset($_POST['b_save_x'])){
			$this->save();
		}
		else
			parent::procesa_event();
	}
}
?>
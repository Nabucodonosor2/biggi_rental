<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

class wi_ingreso_cheque_base extends w_input {
	const K_ESTADO_INGRESO_CHEQUE_EMITIDA		= 1;
	const K_ESTADO_INGRESO_CHEQUE_CONFIRMADA	= 2;
	const K_ESTADO_INGRESO_CHEQUE_ANULADA		= 3;
	const K_AUTORIZA_CAMBIO_EMITIDO_CONFIRMADO	= '997005';
	const K_AUTORIZA_CAMBIO_CONFIRMADO_ANULADO	= '997010';
	
	function wi_ingreso_cheque_base($cod_item_menu) {	
		if (session::is_set('DESDE_wo_inf_cheque_fecha')) {
			session::un_set('DESDE_wo_inf_cheque_fecha');
			$this->desde_wo_inf_cheque_fecha = true;
		}else if (session::is_set('DESDE_wo_inf_cheque_fecha_rental')) {
			session::un_set('DESDE_wo_inf_cheque_fecha_rental');
			$this->desde_wo_inf_cheque_fecha_rental = true;
		}
		parent::w_input('ingreso_cheque', $cod_item_menu);
		$sql	= "SELECT IC.COD_INGRESO_CHEQUE
						,convert(varchar(10), IC.FECHA_INGRESO_CHEQUE, 103) FECHA_INGRESO_CHEQUE
						,IC.COD_EMPRESA
						,EIC.COD_ESTADO_INGRESO_CHEQUE
						,EIC.NOM_ESTADO_INGRESO_CHEQUE
						,U.NOM_USUARIO
						,U.COD_USUARIO
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,E.ALIAS
						,IC.REFERENCIA
						,0 SUM_ARRIENDO_TOTAL
						,0 SUM_TOTAL_MONTO_DOC
						,IC.CANT_CHEQUE
						,IC.CANT_CHEQUE CANT_CHEQUE_H
						,IC.CANT_CHEQUE CANT_CHEQUE_DOC
						,convert(varchar(10),IC.FECHA_PRIMER_CHEQUE,103) FECHA_PRIMER_CHEQUE 
					FROM 	INGRESO_CHEQUE IC
						,EMPRESA E
						, ESTADO_INGRESO_CHEQUE EIC
						, USUARIO U
					WHERE 	IC.COD_INGRESO_CHEQUE = {KEY1} 
					AND	IC.COD_EMPRESA = E.COD_EMPRESA 
					AND	IC.COD_ESTADO_INGRESO_CHEQUE = EIC.COD_ESTADO_INGRESO_CHEQUE
					AND	IC.COD_USUARIO = U.COD_USUARIO";
		
		// DATAWINDOWS INGRESO_CHEQUE
		$this->dws['dw_ingreso_cheque'] = new dw_ingreso_cheque($sql);
		
		// DATAWINDOWS doc_ingreso_pago 
		$this->dws['dw_cheque'] = new dw_cheque();
		$this->dws['dw_item_ingreso_cheque'] = new dw_item_ingreso_cheque();
		//al momento de crear un nuevo registro se iniciara en rut de empresa proveedor
		$this->set_first_focus('RUT');
		$this->dws['dw_ingreso_cheque']->add_control(new static_num('SUM_ARRIENDO_TOTAL',20,10));
		$this->dws['dw_ingreso_cheque']->add_control(new edit_text('CANT_CHEQUE_H',20,10,'hidden'));
		$this->dws['dw_ingreso_cheque']->add_control(new static_num('SUM_TOTAL_MONTO_DOC',20,10));
		$this->dws['dw_ingreso_cheque']->add_control($control  = new edit_date('FECHA_PRIMER_CHEQUE'));
		$control->set_onChange("actualiza_fecha();");
		
		$sql	= "SELECT	COD_ESTADO_INGRESO_CHEQUE
								,NOM_ESTADO_INGRESO_CHEQUE
						FROM ESTADO_INGRESO_CHEQUE";
		$this->dws['dw_ingreso_cheque']->add_control(new drop_down_dw('COD_ESTADO_INGRESO_CHEQUE',$sql,100));
	}
	function get_key() {
		return $this->dws['dw_ingreso_cheque']->get_item(0, 'COD_INGRESO_CHEQUE');
	}
	function new_record() {
		$this->dws['dw_ingreso_cheque']->insert_row();
		//$this->dws['dw_ingreso_cheque']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_ingreso_cheque']->set_item(0, 'FECHA_INGRESO_CHEQUE', substr($this->current_date(), 0, 16));
		$this->dws['dw_ingreso_cheque']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_ingreso_cheque']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_ingreso_cheque']->set_item(0, 'COD_ESTADO_INGRESO_CHEQUE', self::K_ESTADO_INGRESO_CHEQUE_EMITIDA);
		$this->dws['dw_ingreso_cheque']->set_item(0, 'NOM_ESTADO_INGRESO_CHEQUE', 'EMITIDA');
		$this->dws['dw_ingreso_cheque']->set_entrable('COD_ESTADO_INGRESO_CHEQUE',false);
	}
	function load_record(){
		$cod_ingreso_cheque = $this->get_item_wo($this->current_record, 'COD_INGRESO_CHEQUE');
		$this->dws['dw_ingreso_cheque']->retrieve($cod_ingreso_cheque);
		$this->dws['dw_cheque']->retrieve($cod_ingreso_cheque);
		$this->dws['dw_item_ingreso_cheque']->retrieve($cod_ingreso_cheque);
		
		$COD_ESTADO_INGRESO_CHEQUE = $this->dws['dw_ingreso_cheque']->get_item(0, 'COD_ESTADO_INGRESO_CHEQUE');
		
		// valida si el usuario tiene autorizar cambiar el estado confirmado
		if ($COD_ESTADO_INGRESO_CHEQUE == self::K_ESTADO_INGRESO_CHEQUE_EMITIDA){
			if ($this->tiene_privilegio_opcion(self::K_AUTORIZA_CAMBIO_EMITIDO_CONFIRMADO)){
				$this->dws['dw_ingreso_cheque']->set_entrable('COD_ESTADO_INGRESO_CHEQUE'		,true);
				$sql = "SELECT	COD_ESTADO_INGRESO_CHEQUE
									,NOM_ESTADO_INGRESO_CHEQUE
							FROM ESTADO_INGRESO_CHEQUE
							where COD_ESTADO_INGRESO_CHEQUE in (1,2,3)";

			unset($this->dws['dw_ingreso_cheque']->controls['COD_ESTADO_INGRESO_CHEQUE']);
			$this->dws['dw_ingreso_cheque']->add_control(new drop_down_dw('COD_ESTADO_INGRESO_CHEQUE',$sql,141));
			}else{
				$this->dws['dw_ingreso_cheque']->set_entrable('COD_ESTADO_INGRESO_CHEQUE'		,false);
			}
			
			/////Solo para el estado  Emitido
			$this->dws['dw_ingreso_cheque']->set_entrable_dw(true);
			$this->dws['dw_cheque']->set_entrable_dw(true);
			$this->dws['dw_ingreso_cheque']->set_entrable('FECHA_PRIMER_CHEQUE'		,true);
			$this->b_print_visible = true;
			$this->b_delete_visible = true;
			$this->b_save_visible = true;
			$this->b_no_save_visible = true;
			$this->b_modify_visible = true;
		// valida si el usuario tiene autorizar cambiar el estado anulada	
		}else if ($COD_ESTADO_INGRESO_CHEQUE == self::K_ESTADO_INGRESO_CHEQUE_CONFIRMADA){	
			if ($this->tiene_privilegio_opcion(self::K_AUTORIZA_CAMBIO_CONFIRMADO_ANULADO)){
				$this->dws['dw_ingreso_cheque']->set_entrable('COD_ESTADO_INGRESO_CHEQUE'		,true);
				$sql = "SELECT	COD_ESTADO_INGRESO_CHEQUE
						,NOM_ESTADO_INGRESO_CHEQUE
				FROM ESTADO_INGRESO_CHEQUE
				where COD_ESTADO_INGRESO_CHEQUE in (2,3)";

				unset($this->dws['dw_ingreso_cheque']->controls['COD_ESTADO_INGRESO_CHEQUE']);
				$this->dws['dw_ingreso_cheque']->add_control(new drop_down_dw('COD_ESTADO_INGRESO_CHEQUE',$sql,141));
			}else{
				$this->dws['dw_ingreso_cheque']->set_entrable('COD_ESTADO_INGRESO_CHEQUE'		,false);
			}

			/////Solo para el estado  confirmado
			$this->dws['dw_ingreso_cheque']->set_entrable_dw(true);
			$this->dws['dw_ingreso_cheque']->set_entrable('FECHA_PRIMER_CHEQUE'		,false);
			$this->dws['dw_cheque']->set_entrable_dw(false);
			$this->b_print_visible = true;
			$this->b_delete_visible = true;
			$this->b_save_visible = true;
			$this->b_no_save_visible = true;
			$this->b_modify_visible = true;
			
		// valida si el si esta en estado anulado no deje realizar ningun cambio
		}else if ($COD_ESTADO_INGRESO_CHEQUE == self::K_ESTADO_INGRESO_CHEQUE_ANULADA){	
			//Solo para el estado  anulado
			$this->dws['dw_ingreso_cheque']->set_entrable_dw(false);
			$this->dws['dw_cheque']->set_entrable_dw(false);
			$this->b_print_visible = false;
			$this->b_delete_visible = false;
			$this->b_save_visible = false;
			$this->b_no_save_visible = false;
			$this->b_modify_visible = false;
		}
			
		
		$this->dws['dw_item_ingreso_cheque']->set_entrable_dw(false);
		
		$this->dws['dw_ingreso_cheque']->set_entrable('COD_EMPRESA'		,false);
		$this->dws['dw_ingreso_cheque']->set_entrable('RUT'		,false);
		$this->dws['dw_ingreso_cheque']->set_entrable('ALIAS'		,false);
		$this->dws['dw_ingreso_cheque']->set_entrable('NOM_EMPRESA'		,false);
	}
	function load_wo(){
		if ($this->desde_wo_inf_cheque_fecha)
			$this->wo = session::get("wo_inf_cheque_fecha");
		else if ($this->desde_wo_inf_cheque_fecha_rental)
			$this->wo = session::get("wo_inf_cheque_fecha_rental");	
		else
			parent::load_wo();
	}
	function get_url_wo() {
		if ($this->desde_wo_inf_cheque_fecha) 
			return $this->root_url.'appl/inf_cheque_fecha/wo_inf_cheque_fecha.php';
		else if ($this->desde_wo_inf_cheque_fecha_rental) 
			return $this->root_url.'appl/inf_cheque_fecha_rental/wo_inf_cheque_fecha_rental.php';	
		else
			return parent::get_url_wo();
	}
	function save_record($db) {
		$COD_INGRESO_CHEQUE	 		= $this->get_key();
		$FECHA_INGRESO_CHEQUE		= $this->dws['dw_ingreso_cheque']->get_item(0, 'FECHA_INGRESO_CHEQUE');
		$COD_USUARIO				= $this->dws['dw_ingreso_cheque']->get_item(0, 'COD_USUARIO');
		$COD_EMPRESA				= $this->dws['dw_ingreso_cheque']->get_item(0, 'COD_EMPRESA');
		$COD_ESTADO_INGRESO_CHEQUE	= $this->dws['dw_ingreso_cheque']->get_item(0, 'COD_ESTADO_INGRESO_CHEQUE');
		$REFERENCIA					= $this->dws['dw_ingreso_cheque']->get_item(0, 'REFERENCIA');
		$CANT_CHEQUE				= $this->dws['dw_ingreso_cheque']->get_item(0, 'CANT_CHEQUE_H');
		$FECHA_PRIMER_CHEQUE		= $this->dws['dw_ingreso_cheque']->get_item(0, 'FECHA_PRIMER_CHEQUE');
		
		$COD_INGRESO_CHEQUE			= ($COD_INGRESO_CHEQUE =='') ? "NULL" : $COD_INGRESO_CHEQUE;
		$REFERENCIA					= ($REFERENCIA =='') ? "NULL" : "'$REFERENCIA'";
		$FECHA_INGRESO_CHEQUE		= $this->str2date($FECHA_INGRESO_CHEQUE);
		$FECHA_PRIMER_CHEQUE		= $this->str2date($FECHA_PRIMER_CHEQUE);
		
		$sp = 'spu_ingreso_cheque';
		
		if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion'
	    			,$COD_INGRESO_CHEQUE
	    			,$FECHA_INGRESO_CHEQUE
	    			,$COD_USUARIO
	    			,$COD_EMPRESA
	    			,$COD_ESTADO_INGRESO_CHEQUE
	    			,$REFERENCIA
	    			,$CANT_CHEQUE
	    			,$FECHA_PRIMER_CHEQUE";
    	if ($db->EXECUTE_SP($sp, $param)){
    		if ($this->is_new_record()) {
				$COD_INGRESO_CHEQUE = $db->GET_IDENTITY();
				$this->dws['dw_ingreso_cheque']->set_item(0, 'COD_INGRESO_CHEQUE', $COD_INGRESO_CHEQUE);
			}
			for ($i=0; $i<$this->dws['dw_cheque']->row_count(); $i++) 
				$this->dws['dw_cheque']->set_item($i, 'COD_INGRESO_CHEQUE', $COD_INGRESO_CHEQUE);
			if (!$this->dws['dw_cheque']->update($db))
				return false;
			//return true;
			
			for ($i=0; $i<$this->dws['dw_item_ingreso_cheque']->row_count(); $i++) 
				$this->dws['dw_item_ingreso_cheque']->set_item($i, 'COD_INGRESO_CHEQUE', $COD_INGRESO_CHEQUE);
			if (!$this->dws['dw_item_ingreso_cheque']->update($db))
				return false;
			return true;
    	}
    	
		return false;
	}
	function print_record() {
		$cod_ingreso_cheque = $this->get_key();
		$sql= "SELECT IC.COD_INGRESO_CHEQUE
						,convert(varchar(10), IC.FECHA_INGRESO_CHEQUE, 103) FECHA_INGRESO_CHEQUE
						,IC.COD_EMPRESA
						,EIC.COD_ESTADO_INGRESO_CHEQUE
						,EIC.NOM_ESTADO_INGRESO_CHEQUE
						,U.NOM_USUARIO
						,U.COD_USUARIO
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,E.ALIAS
						,IC.REFERENCIA
						,0 SUM_ARRIENDO_TOTAL
						,0 SUM_TOTAL_MONTO_DOC
						,IC.CANT_CHEQUE
						,IC.CANT_CHEQUE CANT_CHEQUE_H
						,IC.CANT_CHEQUE CANT_CHEQUE_DOC
						,convert(varchar(10),IC.FECHA_PRIMER_CHEQUE,103) FECHA_PRIMER_CHEQUE 
					FROM 	INGRESO_CHEQUE IC
						,EMPRESA E
						, ESTADO_INGRESO_CHEQUE EIC
						, USUARIO U
					WHERE 	IC.COD_INGRESO_CHEQUE = $cod_ingreso_cheque
					AND	IC.COD_EMPRESA = E.COD_EMPRESA 
					AND	IC.COD_ESTADO_INGRESO_CHEQUE = EIC.COD_ESTADO_INGRESO_CHEQUE
					AND	IC.COD_USUARIO = U.COD_USUARIO";
		$labels = array();
		$labels['strCOD_INGRESO_CHEQUE'] = $cod_ingreso_cheque;
		$rpt = new print_ingreso_cheque($sql, $this->root_dir.'appl/ingreso_cheque/ingreso_cheque.xml', $labels, "Ingreso Cheque".$cod_ingreso_cheque, 'logo');
		$this->_load_record();
		return true;
	}
}
class dw_ingreso_cheque extends dw_help_empresa{
	function dw_ingreso_cheque($sql) {	
		parent::dw_help_empresa($sql);
		
		$this->add_control(new edit_nro_doc('COD_INGRESO_CHEQUE','COD_INGRESO_CHEQUE'));
		
		
		$this->add_control(new static_text('NOM_ESTADO_INGRESO_CHEQUE'));
		$this->add_control(new static_text('FECHA_INGRESO_CHEQUE'));
		$this->add_control(new edit_text('REFERENCIA',121,100));
		$this->add_control(new static_num('CANT_CHEQUE'));
		$this->add_control(new static_num('CANT_CHEQUE_DOC'));
	}
	function fill_record(&$temp, $record) {	
		parent::fill_record($temp, $record);
	}
}
class dw_cheque extends datawindow {
	const K_TIPO_DOC_PAGO_CHEQUE			= 2;
	const K_TIPO_DOC_PAGO_CHEQUE_A_FECHA	= 12;
	
	function dw_cheque() {		
		$sql = "SELECT C.COD_CHEQUE
						,C.COD_INGRESO_CHEQUE
						,C.COD_BANCO
						,B.NOM_BANCO
						,C.COD_PLAZA
						,C.NRO_DOC
						,convert(varchar(20), C.FECHA_DOC, 103) FECHA_DOC
						,convert(varchar(20), C.FECHA_DOC, 103) FECHA_DOC_H
						,C.MONTO_DOC
						,C.MONTO_DOC MONTO_DOC_H
						,TDP.COD_TIPO_DOC_PAGO
						,TDP.NOM_TIPO_DOC_PAGO
						,C.COD_TIPO_DOC_PAGO COD_TIPO_DOC_PAGO_H
						,C.DEPOSITADO
				FROM 	CHEQUE C LEFT OUTER JOIN BANCO B ON C.COD_BANCO = B.COD_BANCO
						, TIPO_DOC_PAGO TDP		
				WHERE 	C.COD_INGRESO_CHEQUE = {KEY1} 
				AND		C.COD_TIPO_DOC_PAGO = TDP.COD_TIPO_DOC_PAGO";
					
					
		parent::datawindow($sql, 'CHEQUE', true, true);	
		
		$this->add_control(new edit_text_hidden('DEPOSITADO'));
		$this->add_control(new edit_text_upper('COD_CHEQUE',10, 10, 'hidden'));
		$sql_banco = " select	COD_BANCO
								,NOM_BANCO
						from	BANCO
						order by COD_BANCO asc";
		$this->add_control($control= new drop_down_dw('COD_BANCO',$sql_banco,190));
		$sql_palza = "SELECT COD_PLAZA
							,NOM_PLAZA
						FROM PLAZA";
		$control->set_onChange("cambia_banco(this);");
		$this->add_control($control = new drop_down_dw('COD_PLAZA',$sql_palza,145));
		$control->set_onChange("cambia_plaza(this);");
		$this->add_control($control = new edit_num('NRO_DOC',20, 10, 0, true, false, false));
		$control->set_onChange("cambia_nro_documento(this);");
		$this->add_control(new static_text('FECHA_DOC',20));
		$this->add_control(new edit_text('FECHA_DOC_H',20,10,'hidden'));
		$this->add_control(new static_num('MONTO_DOC'));
		$this->add_control(new edit_text('MONTO_DOC_H',20,10,'hidden'));
		$this->add_control(new static_text('NOM_TIPO_DOC_PAGO'));
		$this->add_control(new edit_text_upper('COD_TIPO_DOC_PAGO_H',10,10,'hidden'));
	}
	function fill_record(&$temp, $record) {
		$MONTO_DOC = $this->get_item($record, 'MONTO_DOC_H');
		$SUM_TOTAL_MONTO_DOC = $SUM_TOTAL_MONTO_DOC + $MONTO_DOC ;
		
		$temp->setVar('SUM_TOTAL_MONTO_DOC', number_format($SUM_TOTAL_MONTO_DOC, 0, ',', '.'));
		parent::fill_record($temp, $record);
	}
	function update($db)	{
		$sp = 'spu_cheque';
		
		for ($i = 0; $i < $this->row_count(); $i++){
				$statuts = $this->get_status_row($i);
				if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
					continue;
	
					$COD_CHEQUE				= $this->get_item($i, 'COD_CHEQUE');
					$COD_INGRESO_CHEQUE		= $this->get_item($i, 'COD_INGRESO_CHEQUE');
					$COD_TIPO_DOC_PAGO	 	= $this->get_item($i, 'COD_TIPO_DOC_PAGO_H');
					$FECHA_DOC 				= $this->get_item($i, 'FECHA_DOC_H');
					
					$COD_BANCO				= $this->get_item($i, 'COD_BANCO');
					$COD_PLAZA				= $this->get_item($i, 'COD_PLAZA');
					$NRO_DOC	 			= $this->get_item($i, 'NRO_DOC');
					
					$MONTO_DOC2 			= $this->get_item($i, 'MONTO_DOC_H');
					$MONTO_DOC 				= str_replace( ".", "", $MONTO_DOC2);
					
					$COD_CHEQUE				= ($COD_CHEQUE =='') ? "null" : $COD_CHEQUE;
					$COD_BANCO 			 	= ($COD_BANCO =='') ? "null" : $COD_BANCO;
					$COD_PLAZA 			 	= ($COD_PLAZA =='') ? "null" : $COD_PLAZA;
					$NRO_DOC 			 	= ($NRO_DOC =='') ? "null" : $NRO_DOC;
					$MONTO_DOC 			 	= ($MONTO_DOC =='') ? "null" : $MONTO_DOC;
					$FECHA_DOC				= $this->str2date($FECHA_DOC);
					
					if ($statuts == K_ROW_NEW_MODIFIED)
						$operacion = 'INSERT';
					else if ($statuts == K_ROW_MODIFIED)
						$operacion = 'UPDATE';		
						
					$param = "'$operacion'
								,$COD_CHEQUE
								,$COD_INGRESO_CHEQUE
								,$COD_BANCO
								,$COD_PLAZA
								,$NRO_DOC
								,$FECHA_DOC
								,$MONTO_DOC
								,$COD_TIPO_DOC_PAGO";
					if (!$db->EXECUTE_SP($sp, $param)) 
						return false;
					else {
						if ($statuts == K_ROW_NEW_MODIFIED) {
							$COD_CHEQUE = $db->GET_IDENTITY();
							$this->set_item($i, 'COD_CHEQUE', $COD_CHEQUE);		
						}
					}
				}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_CHEQUE = $this->get_item($i, 'COD_CHEQUE', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_CHEQUE"))
				return false;
		}	
		return true;
	}
}
class dw_item_ingreso_cheque extends datawindow {
	function dw_item_ingreso_cheque(){		
		$sql = "select	COD_INGRESO_ARRIENDO
						,IA.COD_INGRESO_CHEQUE
						,A.COD_ARRIENDO
						,A.COD_ARRIENDO COD_ARRIENDO_H
						,A.NOM_ARRIENDO
						,convert(varchar(20), FECHA_ARRIENDO, 103) FECHA_ARRIENDO
						,REFERENCIA
						,A.NRO_ORDEN_COMPRA
						,A.CENTRO_COSTO_CLIENTE	
						,A.NRO_MESES
						,A.NRO_MESES NRO_MESES_H
						,A.TOTAL_CON_IVA  
						,'S' CHECK_ARRIENDO              
			FROM 		ARRIENDO A
						,INGRESO_ARRIENDO IA
			WHERE		IA.COD_INGRESO_CHEQUE = {KEY1}
			AND			A.COD_ARRIENDO = IA.COD_ARRIENDO
			ORDER BY	IA.COD_ARRIENDO DESC";
					
		parent::datawindow($sql, 'ITEM_INGRESO_CHEQUE', true, true);	
		
		$this->add_control($control = new edit_check_box('CHECK_ARRIENDO','S','N'));
		$control->set_onChange("determina_meses(this);");
		$this->add_control(new static_text('COD_ARRIENDO'));
		$this->add_control(new edit_text('COD_ARRIENDO_H',10,10,'hidden'));
		$this->add_control(new static_text('NOM_ARRIENDO'));
		$this->add_control(new static_text('NRO_ORDEN_COMPRA'));
		$this->add_control(new static_text('CENTRO_COSTO_CLIENTE'));
		$this->add_control(new static_text('NRO_MESES'));
		$this->add_control(new edit_text('NRO_MESES_H',10,10,'hidden'));
		$this->add_control(new static_num('TOTAL_CON_IVA'));
	}
	function fill_record(&$temp, $record) {
		$TOTAL_CON_IVA = $this->get_item($record, 'TOTAL_CON_IVA');
		$SUM_ARRIENDO_TOTAL = $SUM_ARRIENDO_TOTAL + $TOTAL_CON_IVA ;
		
		$temp->setVar('SUM_ARRIENDO_TOTAL', number_format($SUM_ARRIENDO_TOTAL, 0, ',', '.'));
		
		parent::fill_record($temp, $record);
	}
	function update($db){
		$sp = 'spu_item_ingreso_arriendo';
		
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
			continue;
			$CHECK_ARRIENDO			= $this->get_item($i, 'CHECK_ARRIENDO');
			
			
			if ($CHECK_ARRIENDO == 'N')
			continue;
			
			$COD_INGRESO_ARRIENDO	= $this->get_item($i, 'COD_INGRESO_ARRIENDO');
			$COD_INGRESO_CHEQUE		= $this->get_item($i, 'COD_INGRESO_CHEQUE');
			$COD_ARRIENDO			= $this->get_item($i, 'COD_ARRIENDO_H');
			
			$COD_INGRESO_ARRIENDO	= ($COD_INGRESO_ARRIENDO =='') ? "null" : $COD_INGRESO_ARRIENDO;
			$COD_INGRESO_CHEQUE 	= ($COD_INGRESO_CHEQUE =='') ? "null" : $COD_INGRESO_CHEQUE;
			$COD_ARRIENDO		 	= ($COD_ARRIENDO =='') ? "null" : $COD_ARRIENDO;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			else if ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';		
				
			$param = "'$operacion'
						,$COD_INGRESO_ARRIENDO
						,$COD_INGRESO_CHEQUE
						,$COD_ARRIENDO";
			if (!$db->EXECUTE_SP($sp, $param)) 
				return false;
			else {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$COD_INGRESO_ARRIENDO = $db->GET_IDENTITY();
					$this->set_item($i, 'COD_INGRESO_ARRIENDO', $COD_INGRESO_ARRIENDO);		
				}
			}
		}
		return true;
	}
}
class print_ingreso_cheque extends reporte {	
	function print_ingreso_cheque($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	
	function modifica_pdf(&$pdf) {	
			
	      	$pdf->SetAutoPageBreak(true);
		
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$result = $db->build_results($this->sql);
			
			$cod_ingreso_cheque	=	$result[0]['COD_INGRESO_CHEQUE'];
			
			//select para factura
			/*$sql_factura = "SELECT	F.NRO_FACTURA
									,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
									,F.REFERENCIA
									,dbo.f_factura_get_saldo(F.COD_FACTURA) + IPF.MONTO_ASIGNADO SALDO
									,IPF.MONTO_ASIGNADO
									,((dbo.f_factura_get_saldo(F.COD_FACTURA) + IPF.MONTO_ASIGNADO) - MONTO_ASIGNADO ) SALDO_T
							FROM	INGRESO_PAGO_FACTURA IPF, INGRESO_PAGO IP, FACTURA F
							WHERE	IPF.COD_INGRESO_PAGO = $cod_ingreso_pago --1014
							AND		IPF.TIPO_DOC = 'FACTURA' 
							AND		IPF.COD_DOC = F.COD_FACTURA 
							AND		IP.COD_INGRESO_PAGO = IPF.COD_INGRESO_PAGO
							ORDER BY	F.COD_FACTURA asc";
			$result_factura = $db->build_results($sql_factura);
			//CANTIDAD DE FACTURA
			$count_factura = count($result_factura);	
			
			
			//select para nota venta
			$sql_nota_venta = "SELECT	NV.COD_NOTA_VENTA COD_DOC_NV
										,convert(varchar(20), NV.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
										,NV.REFERENCIA REFERENCIA_NV
										,dbo.f_nv_get_saldo(NV.COD_NOTA_VENTA) + IPF.MONTO_ASIGNADO SALDO_NV
										,IPF.MONTO_ASIGNADO MONTO_ASIGNADO_NV
										,((dbo.f_nv_get_saldo(NV.COD_NOTA_VENTA) + IPF.MONTO_ASIGNADO ) - IPF.MONTO_ASIGNADO) SALDO_T_NV
								FROM	INGRESO_PAGO_FACTURA IPF, INGRESO_PAGO IP, NOTA_VENTA NV
								WHERE	IPF.COD_INGRESO_PAGO = $cod_ingreso_pago --1012
								AND		IPF.TIPO_DOC = 'NOTA_VENTA' 
								AND		IPF.COD_DOC = NV.COD_NOTA_VENTA 
								AND		IP.COD_INGRESO_PAGO = IPF.COD_INGRESO_PAGO
								ORDER BY	NV.COD_NOTA_VENTA asc";
			$result_nota_venta = $db->build_results($sql_nota_venta);
			//CANTIDAD DE NOTA_VENTA
			$count_nota_venta = count($result_nota_venta);
			
			$y_ini = $pdf->GetY() + 20;
			$y_fa = $pdf->GetY() + 20;
			*/
			//dibujando los TITULOS  para Doctos.	
			$pdf->SetFont('Arial','B',14);
			$pdf->SetTextColor(4, 22, 114);
			$pdf->SetXY(275,80 + 5);
			$pdf->Cell(385,17,'REGISTRO CHEQUE CLIENTE N° '.$cod_ingreso_cheque, '', '','C');
			
			$pdf->SetFont('Arial','B',9);
			$pdf->SetXY(50,110 + 5);
			$pdf->Cell(30,17,'Razón Social: ', '', '','C');
			
			$pdf->SetXY(450,110 + 5);
			$pdf->Cell(30,17,'Rut: ', '', '','C');
			
			$pdf->SetXY(35,140 + 5);
			$pdf->Cell(30,17,'Fecha: ', '', '','C');
			
			$pdf->SetXY(250,140 + 5);
			$pdf->Cell(30,17,'Emisor: ', '', '','C');
			
			$pdf->SetXY(457,140 + 5);
			$pdf->Cell(30,17,'Estado: ', '', '','C');
			
			$pdf->SetXY(35,180 + 5);
			$pdf->Cell(530,17,'CONTRATO', '', '','');
			
			$pdf->SetXY(35,200 + 5);
			$pdf->Cell(80,30,'Cod. Arriendo', 'TLRB', '','C');
			
			$pdf->SetXY(115,200 + 5);
			$pdf->Cell(240,30,'Nom. Arriendo', 'TRB', '','C');
			
			$pdf->SetXY(355,200 + 5);
			$pdf->Cell(80,30,'Nro. Meses', 'TRB', '','C');
			
			$pdf->SetXY(435,200 + 5);
			$pdf->Cell(100,30,'Total', 'TRB', '','C');
			
			$sql = "select	COD_INGRESO_ARRIENDO
						,IA.COD_INGRESO_CHEQUE
						,A.COD_ARRIENDO
						,A.COD_ARRIENDO COD_ARRIENDO_H
						,A.NOM_ARRIENDO
						,convert(varchar(20), FECHA_ARRIENDO, 103) FECHA_ARRIENDO
						,REFERENCIA
						,A.NRO_ORDEN_COMPRA
						,A.CENTRO_COSTO_CLIENTE	
						,A.NRO_MESES
						,A.NRO_MESES NRO_MESES_H
						,A.TOTAL_CON_IVA  
						,'S' CHECK_ARRIENDO              
			FROM 		ARRIENDO A
						,INGRESO_ARRIENDO IA
			WHERE		IA.COD_INGRESO_CHEQUE = $cod_ingreso_cheque
			AND			A.COD_ARRIENDO = IA.COD_ARRIENDO
			ORDER BY	IA.COD_ARRIENDO DESC";
			
			$result_it = $db->build_results($sql);
			
			$pdf->SetTextColor(0, 0, 0);
			for($x = 0; $x< count($result_it);$x++){
				$y = 235 + (17*$x);

				$pdf->SetXY(35,$y);
				$pdf->Cell(80,17,$result_it[$x]['COD_ARRIENDO'], 'LRB', '','C');
				$pdf->SetXY(115,$y);
				$pdf->Cell(240,17,$result_it[$x]['NOM_ARRIENDO'], 'LRB', '','C');
				$pdf->SetXY(355,$y);
				$pdf->Cell(80,17,$result_it[$x]['NRO_MESES'], 'TRB', '','C');
				$pdf->SetXY(435,$y);
				$pdf->Cell(100,17,$result_it[$x]['TOTAL_CON_IVA'], 'TRB', '','C');
			}
			
			$pdf->SetXY(100,110 + 5);
			$pdf->Cell(340,17,$result[0]['NOM_EMPRESA'], 'B', '','');
			
			$pdf->SetXY(490,110 + 5);
			$pdf->Cell(60,17,$result[0]['RUT'], 'B', '','');
			
			$pdf->SetXY(80,140 + 5);
			$pdf->Cell(60,17,$result[0]['FECHA_INGRESO_CHEQUE'], 'B', '','');
						
			$pdf->SetXY(290,140 + 5);
			$pdf->Cell(100,17,$result[0]['NOM_USUARIO'], 'B', '','');
			
			$pdf->SetXY(490,140 + 5);
			$pdf->Cell(70,17,$result[0]['NOM_ESTADO_INGRESO_CHEQUE'], 'B', '','');
			
			$pdf->SetTextColor(4, 22, 114);
			$pdf->SetXY(35,$y+30);
			$pdf->Cell(530,17,'DOCUMENTOS', '', '','');
			
			$pdf->SetXY(35,$y+50);
			$pdf->Cell(80,30,'Fecha', 'TLRB', '','C');
			
			$pdf->SetXY(115,$y+50);
			$pdf->Cell(80,30,'Nro. Doc', 'TLRB', '','C');
			
			$pdf->SetXY(195,$y+50);
			$pdf->Cell(160,30,'Banco', 'TLRB', '','C');
			
			$pdf->SetXY(355,$y+50);
			$pdf->Cell(95,30,'Plaza', 'TLRB', '','C');
			
			$pdf->SetXY(450.5,$y+50);
			$pdf->Cell(95,30,'Monto', 'TLRB', '','C');
			
			$pdf->SetTextColor(0, 0, 0);
			$sql_doc ="SELECT C.NRO_DOC
							, CONVERT(VARCHAR,C.FECHA_DOC,103) FECHA_DOC
							, C.MONTO_DOC
							, B.NOM_BANCO
							, P.NOM_PLAZA 
						FROM CHEQUE C, BANCO B, PLAZA P
						WHERE COD_INGRESO_CHEQUE = $cod_ingreso_cheque
						AND C.COD_BANCO = B.COD_BANCO
						AND C.COD_PLAZA = P.COD_PLAZA";
			$result_doc = $db->build_results($sql_doc);
			for($x = 0; $x< count($result_doc);$x++){
				$z = ($y+80) + (17*$x);
				
				$pdf->SetXY(35,$z);
				$pdf->Cell(80,17,$result_doc[$x]['FECHA_DOC'], 'LRB', '','C');
				$pdf->SetXY(115,$z);
				$pdf->Cell(80,17,$result_doc[$x]['NRO_DOC'], 'LRB', '','C');
				$pdf->SetXY(195,$z);
				$pdf->Cell(160,17,$result_doc[$x]['NOM_BANCO'], 'LRB', '','C');
				$pdf->SetXY(355,$z);
				$pdf->Cell(95,17,$result_doc[$x]['NOM_PLAZA'], 'LRB', '','C');
				$pdf->SetXY(450.5,$z);
				$pdf->Cell(95,17,$result_doc[$x]['MONTO_DOC'], 'LRB', '','C');
			}
			/*
			$pdf->SetXY(30,$y_fa + 20);
			$pdf->Cell(65,15,'Tipo Docto.', 'LTB', '','C');
			$pdf->SetXY(95,$y_fa + 20);
			$pdf->Cell(60,15,'Nro Docto.', 'LTB', '','C');
			$pdf->SetXY(155,$y_fa + 20);
			$pdf->Cell(60,15,'Fecha', 'LTB', '','C');
			$pdf->SetXY(215,$y_fa + 20);
			$pdf->Cell(70,15,'Monto Docto.', 'LTRB', '','C');
			$pdf->SetXY(285,$y_fa + 20);
			$pdf->Cell(65,15,'Saldo Docto.', 'TRB', '','C');
			$pdf->SetXY(350,$y_fa + 20);
			$pdf->Cell(65, 15,'Monto Pago', 'TRB', '','C');

			
			//DETERMINAR SI EL SELECT FACTURA TIENE DATOS
			if($count_factura != 0){

				$pdf->SetFont('Arial','',9);		
					//DIBUJANDO LOS ITEMS DE LA FACTURA		
					$suma_factura = 0;
					$pdf->SetRightMargin(90);
					$pdf->SetLeftMargin(90);
					
					$altura_doc = 0;
					
					for($i=0, $ii = 0; $i<$count_factura; $i++, $ii++){
						$altura_doc = $pdf->gety();
					
						
						if ($altura_doc > 710) {
							$ii = 0;
							$y_fa = 90;
							$pdf->AddPage();
						}
						
						$nro_factura		=	$result_factura[$i]['NRO_FACTURA'];
						$fecha_factura		=	$result_factura[$i]['FECHA_FACTURA'];
						$monto_factura		=	number_format($result_factura[$i]['SALDO'], 0, ',', '.');
						$monto_pago_factura	=	$result_factura[$i]['MONTO_ASIGNADO'];
						$saldo_factura		=	number_format($result_factura[$i]['SALDO_T'], 0, ',', '.');
						$monto_pago_factura		=	$result_factura[$i]['MONTO_ASIGNADO'];
						$suma_factura 			=	$suma_factura + $monto_pago_factura;
						$monto_pago_factura	=	number_format($monto_pago_factura, 0, ',', '.');
						
						$pdf->SetXY(30,$y_fa + 35+(15*$ii));
						$pdf->Cell(65,15,'Factura', 'LTB', '','C');
						
						$pdf->SetXY(95,$y_fa + 35+(15*$ii));
						$pdf->Cell(60,15,$nro_factura, 'LTB', '','C');
						
						$pdf->SetXY(155,$y_fa + 35+(15*$ii));
						$pdf->Cell(60,15,$fecha_factura, 'LTRB', '','C');
						
						$pdf->SetXY(215,$y_fa + 35+(15*$ii));
						$pdf->Cell(70,15,$monto_factura, 'LTB', '','R');
						
						$pdf->SetXY(285,$y_fa + 35+(15*$ii));
						$pdf->Cell(65,15,$saldo_factura, 'LTRB', '','R');
						
						$pdf->SetXY(350,$y_fa + 35+(15*$ii));
						$pdf->Cell(65,15,$monto_pago_factura, 'TRB', '','R');
					}
					
					$suma_factura		=	number_format($suma_factura, 0, ',', '.');
					
					
					$pdf->SetFont('Arial','B',9);
					$pdf->SetXY(285,$y_fa + 35+(15*$ii));
					$pdf->Cell(65,15,'TOTAL  $', 'LTRB', '','R');			
					$pdf->SetXY(350,$y_fa + 35+(15*$ii));
					$pdf->Cell(65,15,$suma_factura, 'TRB', '','R');							
			}
			$en_cual_quedo=$pdf->gety();
			
			//DETERMINAR SI EL SELECT NOTA_VENTA TIENE DATOS
			if($count_nota_venta != 0){
				$pdf->SetRightMargin(90);
				$pdf->SetLeftMargin(90);
				//DIBUJANDO LOS ITEMS DE LA NOTA_VENTA
				$suma_nota_venta = 0;
				
				if($count_factura != 0)
					$y_fa = $en_cual_quedo + 15;
				else
					$y_fa = $en_cual_quedo - 20;
				$pdf->SetFont('Arial','',9);
				
				for($i=0, $ii = 0; $i<$count_nota_venta; $i++, $ii++){
					$altura_doc = $pdf->gety();
					if ($altura_doc > 710) {
							$ii = 0;
							$y_fa = 90;
							$pdf->AddPage();
						}
						
						
					$nro_nv			=	$result_nota_venta[$i]['COD_DOC_NV'];
					$fecha_nv		=	$result_nota_venta[$i]['FECHA_NOTA_VENTA'];
					$monto_nv		=	number_format($result_nota_venta[$i]['SALDO_NV'], 0, ',', '.');
					$monto_pago_nv	=	$result_nota_venta[$i]['MONTO_ASIGNADO_NV'];
					$saldo_nv		=	number_format($result_nota_venta[$i]['SALDO_T_NV'], 0, ',', '.');
					$suma_nota_venta 	=	$suma_nota_venta + $monto_pago_nv;
					$monto_pago_nv	=	number_format($result_nota_venta[$i]['MONTO_ASIGNADO_NV'], 0, ',', '.');
					
					$pdf->SetXY(30,$y_fa + 35+(15*$ii));
					$pdf->Cell(65,15,'Nota Venta', 'LTB', '','C');
					$pdf->SetXY(95,$y_fa + 35+(15*$ii));
					$pdf->Cell(60,15,$nro_nv, 'LTB', '','C');
					$pdf->SetXY(155,$y_fa + 35+(15*$ii));
					$pdf->Cell(60,15,$fecha_nv, 'LTRB', '','C');
					$pdf->SetXY(215,$y_fa + 35+(15*$ii));
					$pdf->Cell(70,15,$monto_nv, 'LTB', '','R');
					$pdf->SetXY(285,$y_fa + 35+(15*$ii));
					$pdf->Cell(65,15,$saldo_nv, 'LTRB', '','R');
					$pdf->SetXY(350,$y_fa + 35+(15*$ii));
					$pdf->Cell(65,15,$monto_pago_nv, 'TRB', '','R');				
				}
				
				$suma_nota_venta		=	number_format($suma_nota_venta, 0, ',', '.');
				
				
				$pdf->SetFont('Arial','B',9);
				$pdf->SetXY(285,$y_fa + 35+(15*$ii));
				$pdf->Cell(65,15,'TOTAL  $', 'LTRB', '','R');
				$pdf->SetXY(350,$y_fa + 35+(15*$ii));
				$pdf->Cell(65,15,$suma_nota_venta, 'TRB', '','R');		
			}else{
				
			}

		// ESTADO INGRESO PAGO
			$sql_estado = "select 		IP.COD_ESTADO_INGRESO_PAGO
						   from 		INGRESO_PAGO IP
						   where	    IP.COD_INGRESO_PAGO = $cod_ingreso_pago
						   ";
			$result_estado= $db->build_results($sql_estado);
			
			if ($result_estado[0]['COD_ESTADO_INGRESO_PAGO']== 3) 
			{
				$pdf->SetTextColor(250, 0, 0);
				$pdf->SetXY(470, 120);
				$pdf->Cell(1,1,'DOCUMENTO ANULADO', '', '','L');
			}*/

	}
}
// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_ingreso_cheque.php";
if (file_exists($file_name))
	require_once($file_name);
else {
	class wi_ingreso_cheque extends wi_ingreso_cheque_base {
		function wi_ingreso_cheque($cod_item_menu) {
			parent::wi_ingreso_cheque_base($cod_item_menu); 
		}
	}
}
?>
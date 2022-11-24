<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

class wi_ingreso_cheque_base extends w_input {
	const K_ESTADO_INGRESO_CHEQUE_EMITIDA		= 1;
	const K_ESTADO_INGRESO_CHEQUE_CONFIRMADA	= 2;
	const K_ESTADO_INGRESO_CHEQUE_ANULADA		= 3;
	const K_AUTORIZA_CAMBIO_ESTADO				= '997005';
	
	function wi_ingreso_cheque_base($cod_item_menu) {	
		if (session::is_set('DESDE_wo_inf_cheque_fecha')) {
			session::un_set('DESDE_wo_inf_cheque_fecha');
			$this->desde_wo_inf_cheque_fecha = true;
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
						,(SELECT COUNT(*) FROM CHEQUE CH WHERE CH.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE) COUNT_CHEQUE
						,(SELECT COUNT(*) FROM CHEQUE CH WHERE CH.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE) COUNT_CHEQUE_DOS
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
		
		//al momento de crear un nuevo registro se iniciara en rut de empresa proveedor
		$this->set_first_focus('RUT');
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
	}
	function load_record() {
		$cod_ingreso_cheque = $this->get_item_wo($this->current_record, 'COD_INGRESO_CHEQUE');
		$this->dws['dw_ingreso_cheque']->retrieve($cod_ingreso_cheque);
		$this->dws['dw_cheque']->retrieve($cod_ingreso_cheque);
		
		// valida si el usuario tiene autorizar cambiar el estado 
		if ($this->tiene_privilegio_opcion(self::K_AUTORIZA_CAMBIO_ESTADO))
			$this->dws['dw_ingreso_cheque']->set_entrable('COD_ESTADO_INGRESO_CHEQUE'		,true);
		else
			$this->dws['dw_ingreso_cheque']->set_entrable('COD_ESTADO_INGRESO_CHEQUE'		,false);
	}
	function load_wo(){
		if ($this->desde_wo_inf_cheque_fecha)
			$this->wo = session::get("wo_inf_cheque_fecha");
		else
			parent::load_wo();
	}
	function get_url_wo() {
		if ($this->desde_wo_inf_cheque_fecha) 
			return $this->root_url.'appl/inf_cheque_fecha/wo_inf_cheque_fecha.php';
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
		
		$COD_INGRESO_CHEQUE			= ($COD_INGRESO_CHEQUE =='') ? "NULL" : $COD_INGRESO_CHEQUE;
		$REFERENCIA					= ($REFERENCIA =='') ? "NULL" : "'$REFERENCIA'";
		$FECHA_INGRESO_CHEQUE		= $this->str2date($FECHA_INGRESO_CHEQUE);
		
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
	    			,$REFERENCIA";

    	if ($db->EXECUTE_SP($sp, $param)){
    		if ($this->is_new_record()) {
				$COD_INGRESO_CHEQUE = $db->GET_IDENTITY();
				$this->dws['dw_ingreso_cheque']->set_item(0, 'COD_INGRESO_CHEQUE', $COD_INGRESO_CHEQUE);
			}
			for ($i=0; $i<$this->dws['dw_cheque']->row_count(); $i++) 
				$this->dws['dw_cheque']->set_item($i, 'COD_INGRESO_CHEQUE', $COD_INGRESO_CHEQUE);
			if (!$this->dws['dw_cheque']->update($db))
				return false;
			return true;
    	}
    	
		return false;
	}
}
class dw_ingreso_cheque extends dw_help_empresa{
	
	function dw_ingreso_cheque($sql) {	
		parent::dw_help_empresa($sql);
		
		$this->add_control(new edit_nro_doc('COD_INGRESO_CHEQUE','COD_INGRESO_CHEQUE'));
		
		$sql_estado	= "SELECT	COD_ESTADO_INGRESO_CHEQUE
								,NOM_ESTADO_INGRESO_CHEQUE
						FROM ESTADO_INGRESO_CHEQUE";
		$this->add_control(new drop_down_dw('COD_ESTADO_INGRESO_CHEQUE',$sql_estado,100));
		$this->add_control(new static_text('NOM_ESTADO_INGRESO_CHEQUE'));
		$this->add_control(new static_text('FECHA_INGRESO_CHEQUE'));
		$this->add_control(new edit_text('REFERENCIA',121,10));
		$this->add_control(new static_text('COUNT_CHEQUE'));
		$this->add_control(new static_text('COUNT_CHEQUE_DOS'));
								
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
						,C.MONTO_DOC
						,TDP.COD_TIPO_DOC_PAGO
						,TDP.NOM_TIPO_DOC_PAGO
						,C.COD_TIPO_DOC_PAGO COD_TIPO_DOC_PAGO_H
						,0 MONTO_DOC_C
				FROM 	CHEQUE C LEFT OUTER JOIN BANCO B ON C.COD_BANCO = B.COD_BANCO
						, TIPO_DOC_PAGO TDP		
				WHERE 	C.COD_INGRESO_CHEQUE = {KEY1} 
				AND		C.COD_TIPO_DOC_PAGO = TDP.COD_TIPO_DOC_PAGO";
					
					
		parent::datawindow($sql, 'CHEQUE', true, true);	
		
		$this->add_control(new edit_text_upper('COD_CHEQUE',10, 10, 'hidden'));
		$sql_banco = " select	COD_BANCO
								,NOM_BANCO
						from	BANCO
						order by COD_BANCO asc";
		$this->add_control(new drop_down_dw('COD_BANCO',$sql_banco,190));
		$sql_palza = "SELECT COD_PLAZA
							,NOM_PLAZA
						FROM PLAZA";
		$this->add_control(new drop_down_dw('COD_PLAZA',$sql_palza,145));
		$this->add_control(new edit_num('NRO_DOC',20, 10, 0, true, false, false));
		$this->add_control(new edit_date('FECHA_DOC',20));
		$this->add_control($control = new edit_precio('MONTO_DOC',20,10));
		//$control->set_onChange("change_monto_doc();");
		$this->set_computed('MONTO_DOC_C', '[MONTO_DOC]');
		$this->controls['MONTO_DOC_C']->type = 'hidden';
		$this->accumulate('MONTO_DOC_C');
		$sql_tipo_doc = "select	COD_TIPO_DOC_PAGO
									,NOM_TIPO_DOC_PAGO
							from	TIPO_DOC_PAGO
							where COD_TIPO_DOC_PAGO IN (".self::K_TIPO_DOC_PAGO_CHEQUE_A_FECHA.",".self::K_TIPO_DOC_PAGO_CHEQUE.")
							order by ORDEN asc";
		$this->add_control($control = new drop_down_dw('COD_TIPO_DOC_PAGO',$sql_tipo_doc,142));
		//$control->set_onChange("valida_tipo_doc_pago(this); valida_asignacion_doc_pago(this)");
		$this->add_control(new edit_text_upper('COD_TIPO_DOC_PAGO_H',10,10,'hidden'));
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		return $row;
	}
	function fill_template(&$temp) {
		parent::fill_template($temp);
		if ($this->entrable) {
			$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_item(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
		}
	}
	function update($db)	{
		$sp = 'spu_cheque';
		
		for ($i = 0; $i < $this->row_count(); $i++){
				$statuts = $this->get_status_row($i);
				if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
					continue;
	
					$COD_CHEQUE				= $this->get_item($i, 'COD_CHEQUE');
					$COD_INGRESO_CHEQUE		= $this->get_item($i, 'COD_INGRESO_CHEQUE');
					$COD_BANCO				= $this->get_item($i, 'COD_BANCO');
					$COD_PLAZA				= $this->get_item($i, 'COD_PLAZA');
					$NRO_DOC	 			= $this->get_item($i, 'NRO_DOC');
					$FECHA_DOC 				= $this->get_item($i, 'FECHA_DOC');
					$MONTO_DOC2 			= $this->get_item($i, 'MONTO_DOC');
					$MONTO_DOC 				= str_replace( ".", "", $MONTO_DOC2);
					$COD_TIPO_DOC_PAGO_H 	= $this->get_item($i, 'COD_TIPO_DOC_PAGO_H');
					IF($statuts == K_ROW_NEW)
						$COD_TIPO_DOC_PAGO	= $COD_TIPO_DOC_PAGO_H;
					ELSE
						$COD_TIPO_DOC_PAGO	= $this->get_item($i, 'COD_TIPO_DOC_PAGO');
					
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
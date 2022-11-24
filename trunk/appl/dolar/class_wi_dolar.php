<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
/*
Clase : WI_DOLAR
*/
class wi_dolar extends w_input {
	function wi_dolar($cod_item_menu) {
		parent::w_input('dolar', $cod_item_menu);
		$this->valida_llave = true;		// valida que la PK sea unica

		$sql = "SELECT COD_ANO ,
						ANO
						FROM ANO 
					WHERE COD_ANO = {KEY1}";						
	
		$this->dws['dw_ano'] = new datawindow($sql);
		// asigna los formatos
		$this->dws['dw_ano']->add_control(new edit_ano('ANO', 4,4));
		// asigna los mandatorys
		$this->dws['dw_ano']->set_mandatory('ANO', 'Ano');
		
		$sql = "select d.COD_DOLAR_TODOINOX
				,d.COD_DOLAR_TODOINOX COD_DOLAR_TODOINOX_H
				,d.COD_MES
				,d.COD_MES COD_MES_H
				,m.NOM_MES
				,d.COD_ANO
				,d.DOLAR_ACUERDO
				,d.DOLAR_ADUANERO
		from DOLAR_TODOINOX d, MES m
		where d.COD_ANO = {KEY1}
			and d.COD_MES = m.COD_MES";
		 
		$this->dws['dw_dolar'] = new datawindow($sql, 'IT_DOLAR');

		$this->dws['dw_dolar']->add_control(new edit_num('DOLAR_ACUERDO', 16, 100, 2));
		$this->dws['dw_dolar']->add_control(new edit_num('DOLAR_ADUANERO', 16, 100, 2));
		$this->dws['dw_dolar']->add_control(new static_text('NOM_MES'));
		$this->dws['dw_dolar']->add_control(new edit_text('COD_MES_H',10, 10, 'hidden'));
		$this->dws['dw_dolar']->add_control(new edit_text('COD_DOLAR_TODOINOX_H',10, 10, 'hidden'));
		//$this->add_auditoria('DOLAR_ACUERDO');
		//$this->add_auditoria('DOLAR_ADUANERO');
		
		
		//$this->add_auditoria_relacionada('DOLAR_TODOINOX', 'DOLAR_ACUERDO');
		$this->add_auditoria_relacionada('DOLAR_TODOINOX', 'DOLAR_ADUANERO');
	}	
	function new_record() {	
		$this->dws['dw_ano']->insert_row();
		for($i=0; $i<12; $i++) {
			$row = $this->dws['dw_dolar']->insert_row();
			$this->dws['dw_dolar']->set_item($i, 'COD_MES', $i + 1);
		}
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);	
		$sql= "select COD_MES,NOM_MES from MES";
		$result = $db->build_results($sql);
		for($i=0; $i<count($result); $i++) {
			$this->dws['dw_dolar']->set_item($i, 'NOM_MES', $result[$i]['NOM_MES']);
			$this->dws['dw_dolar']->set_item($i, 'COD_MES_H', $result[$i]['COD_MES']);
		} 
		$this->dws['dw_ano']->set_item(0, 'ANO', date('Y'));
	}
	
	function load_record() {
		$COD_ANO = $this->get_item_wo($this->current_record, 'COD_ANO');
		$this->dws['dw_ano']->retrieve($COD_ANO);
		$this->dws['dw_dolar']->retrieve($COD_ANO);
	}
	function make_sql_auditoria_relacionada($tabla) {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'ANO';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla; 
		return $sql;
	}
	function validate_record() {
		$ano = $this->dws['dw_ano']->get_item(0, 'ANO');
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql="select ANO from ANO";
		$result = $db->build_results($sql);
		
		for($i=0; $i<count($result); $i++) {
			if($ano == $result[$i]['ANO']){
				//return 'Este año ya fue ingresado en el sistema';
			}
		} 
	}	
	function get_key() {
		return $this->dws['dw_ano']->get_item(0, 'COD_ANO');
	}	
	
	function save_record($db) {
		$COD_ANO = $this->get_key();
		$NOM_ANO = $this->dws['dw_ano']->get_item(0, 'ANO');
		$new_valor = $this->dws['dw_ano']->get_item(0, 'ANO');
			
		$COD_ANO = ($COD_ANO=='') ? "null" : $COD_ANO;		
    
		$sp = 'spu_ano';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion', $COD_ANO, $NOM_ANO";
	    	
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_ANO = $db->GET_IDENTITY();
				$this->dws['dw_ano']->set_item(0, 'COD_ANO', $COD_ANO);
			}

			for ($i=0; $i<$this->dws['dw_dolar']->row_count(); $i++){ 
				$this->dws['dw_dolar']->set_item($i, 'COD_ANO', $COD_ANO);
			}
			
			if (!$this->update($db)) return false;
			return true;
		}
		return false;		
	}

	function update($db)	{
			
		$sp = 'spu_dolar';
		for ($i = 0; $i < $this->dws['dw_dolar']->row_count(); $i++){
			$statuts = $this->dws['dw_dolar']->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$COD_DOLAR_TODOINOX			= $this->dws['dw_dolar']->get_item($i, 'COD_DOLAR_TODOINOX_H');
			$COD_MES					= $this->dws['dw_dolar']->get_item($i, 'COD_MES_H');
			$DOLAR_ADUANERO	 			= $this->dws['dw_dolar']->get_item($i, 'DOLAR_ADUANERO');
			$DOLAR_ACUERDO				= $this->dws['dw_dolar']->get_item($i, 'DOLAR_ACUERDO');
			$ANO 						= $this->dws['dw_ano']->get_item(0, 'COD_ANO');
			
			$COD_DOLAR_TODOINOX  = ($COD_DOLAR_TODOINOX =='') ? "null" : $COD_DOLAR_TODOINOX;
			$COD_MES  = ($COD_MES =='') ? "null" : $COD_MES;
			$DOLAR_ADUANERO		= ($DOLAR_ADUANERO =='') ? 0 : $DOLAR_ADUANERO;
			$DOLAR_ACUERDO			= ($DOLAR_ACUERDO =='') ? 0 : $DOLAR_ACUERDO;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion', $COD_DOLAR_TODOINOX, $COD_MES, $ANO, $DOLAR_ADUANERO, $DOLAR_ACUERDO";
				
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
			else {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$COD_DOLAR_TODOINOX = $db->GET_IDENTITY();
					$this->dws['dw_dolar']->set_item($i, 'COD_DOLAR_TODOINOX_H', $COD_DOLAR_TODOINOX);
				}
			}
		}
		for ($i = 0; $i < $this->dws['dw_dolar']->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_DOLAR_TODOINOX = $this->get_item($i, 'COD_DOLAR_TODOINOX', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_DOLAR_TODOINOX")){
				return false;				
			}			
		}
		return true;
	}
}

?>
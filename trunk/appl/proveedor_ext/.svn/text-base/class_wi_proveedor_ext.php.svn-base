<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_proveedor_ext extends w_input {
	function wi_proveedor_ext($cod_item_menu) {
		parent::w_input('proveedor_ext', $cod_item_menu);

		$sql = "select COD_PROVEEDOR_EXT,
					   NOM_PROVEEDOR_EXT,
					   ALIAS_PROVEEDOR_EXT,
					   WEB_SITE,
					   DIRECCION,
					   COD_CIUDAD,
					   COD_PAIS,
					   TELEFONO,
					   FAX,
					   POST_OFFICE_BOX,
					   OBS,
					   COD_PROVEEDOR_EXT_4D,
					   NOM_CIUDAD_4D,
					   NOM_PAIS_4D
					   from PROVEEDOR_EXT
				where COD_PROVEEDOR_EXT = {KEY1}";
						
		$this->dws['wi_proveedor_ext'] = new datawindow($sql);
		

		$this->dws['wi_proveedor_ext']->add_control(new edit_text_upper('ALIAS_PROVEEDOR_EXT', 30, 80));
		$this->dws['wi_proveedor_ext']->add_control(new edit_text_upper('NOM_PROVEEDOR_EXT', 80, 80));
		$this->dws['wi_proveedor_ext']->add_control(new edit_text_upper('DIRECCION', 80, 80));
		
		
		$this->dws['wi_proveedor_ext']->add_control(new edit_text_upper('NOM_PAIS_4D', 30, 80));
		$this->dws['wi_proveedor_ext']->add_control(new edit_text_upper('NOM_CIUDAD_4D', 30, 80));	
		
		
		$this->dws['wi_proveedor_ext']->add_control(new edit_text_upper('TELEFONO', 21, 80));
		$this->dws['wi_proveedor_ext']->add_control(new edit_text_upper('FAX', 21, 80));
		
		$this->dws['wi_proveedor_ext']->add_control(new edit_text_upper('WEB_SITE', 43, 80));
		$this->dws['wi_proveedor_ext']->add_control(new edit_text_upper('POST_OFFICE_BOX', 43, 80));
		$this->dws['wi_proveedor_ext']->add_control(new edit_text_multiline('OBS',54,4));
		// asigna los mandatorys
		$this->dws['wi_proveedor_ext']->set_mandatory('COD_PROVEEDOR_EXT', 'Código');
		$this->dws['wi_proveedor_ext']->set_mandatory('NOM_PROVEEDOR_EXT', 'Proveedor_ext');
			
	}
	function new_record() {
		//$this->dws['wi_proveedor_ext']->add_control(new edit_num('COD_PROVEEDOR_EXT',3,3));
		$this->dws['wi_proveedor_ext']->insert_row();
	}
	function load_record() {
		$cod_proveedor_ext = $this->get_item_wo($this->current_record, 'COD_PROVEEDOR_EXT');
		$this->dws['wi_proveedor_ext']->retrieve($cod_proveedor_ext);
	}
	function get_key() {
		return $this->dws['wi_proveedor_ext']->get_item(0, 'COD_PROVEEDOR_EXT');
	}
	
	function save_record($db) {
		$COD_PROVEEDOR_EXT 		= $this->get_key();
		$NOM_PROVEEDOR_EXT 		= $this->dws['wi_proveedor_ext']->get_item(0, 'NOM_PROVEEDOR_EXT');
		$ALIAS_PROVEEDOR_EXT 	= $this->dws['wi_proveedor_ext']->get_item(0, 'ALIAS_PROVEEDOR_EXT');
		$WEB_SITE 				= $this->dws['wi_proveedor_ext']->get_item(0, 'WEB_SITE');
		$DIRECCION 				= $this->dws['wi_proveedor_ext']->get_item(0, 'DIRECCION');
		
		$TELEFONO 				= $this->dws['wi_proveedor_ext']->get_item(0, 'TELEFONO');
		$FAX 					= $this->dws['wi_proveedor_ext']->get_item(0, 'FAX');
		$POST_OFFICE_BOX 		= $this->dws['wi_proveedor_ext']->get_item(0, 'POST_OFFICE_BOX');
		$OBS 					= $this->dws['wi_proveedor_ext']->get_item(0, 'OBS');
		$COD_PROVEEDOR_EXT_4D	= 'N';
		$NOM_CIUDAD 			= $this->dws['wi_proveedor_ext']->get_item(0, 'NOM_CIUDAD_4D');
		$NOM_PAIS 				= $this->dws['wi_proveedor_ext']->get_item(0, 'NOM_PAIS_4D');
		
		$COD_PROVEEDOR_EXT = ($COD_PROVEEDOR_EXT=='') ? "null" : $COD_PROVEEDOR_EXT;
		
			$sp = 'spu_proveedor_ext';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_PROVEEDOR_EXT,'$NOM_PROVEEDOR_EXT', '$ALIAS_PROVEEDOR_EXT','$WEB_SITE','$DIRECCION',NULL,NULL,'$TELEFONO','$FAX','$POST_OFFICE_BOX','$OBS','$COD_PROVEEDOR_EXT_4D','$NOM_CIUDAD','$NOM_PAIS'";
		echo $sp.''.$param; 
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				
				$cod_proveedor_ext = $db->GET_IDENTITY();
				$this->dws['wi_proveedor_ext']->set_item(0, 'COD_PROVEEDOR_EXT', $cod_proveedor_ext);				
			}
			return true;
		}
		return false;		
	}
}
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../guia_recepcion/class_wi_guia_recepcion.php");

class wi_guia_recepcion_arriendo extends wi_guia_recepcion {
	const K_TIPO_GR_ARRIENDO		= 4;
	
	function wi_guia_recepcion_arriendo($cod_item_menu) {		
		parent::wi_guia_recepcion($cod_item_menu);
		$this->nom_tabla = 'guia_recepcion_arriendo';
		$this->nom_template = "wi_".$this->nom_tabla.".htm";

		$sql = "select 	 COD_TIPO_GUIA_RECEPCION
						,NOM_TIPO_GUIA_RECEPCION
				from 	 TIPO_GUIA_RECEPCION
				where	COD_TIPO_GUIA_RECEPCION = ".self::K_TIPO_GR_ARRIENDO."
				order by ORDEN";
		$this->dws['dw_guia_recepcion']->controls['COD_TIPO_GUIA_RECEPCION']->set_sql($sql);
		$this->dws['dw_guia_recepcion']->controls['COD_TIPO_GUIA_RECEPCION']->retrieve();
		
		$this->dws['dw_guia_recepcion']->controls['TIPO_DOC']->aValues = array('', 'MOD_ARRIENDO');
		$this->dws['dw_guia_recepcion']->controls['TIPO_DOC']->aLabels = array('', 'CONTRATO ARRIENDO');
	
		// no se pueden agregar o eliminar items
		$this->dws['dw_item_guia_recepcion']->b_add_line_visible = false;
		$this->dws['dw_item_guia_recepcion']->b_del_line_visible = false;
		
		
	}
	function new_record() {
		parent::new_record();
		$this->dws['dw_guia_recepcion']->set_item(0, 'COD_TIPO_GUIA_RECEPCION', self::K_TIPO_GR_ARRIENDO);
		$this->dws['dw_guia_recepcion']->set_item(0, 'TR_DISPLAY_TIPO_DOC', '');
		$this->dws['dw_guia_recepcion']->set_item(0, ' ', 'MOD_ARRIENDO');
		$this->dws['dw_guia_recepcion']->set_item(0, 'VISIBLE_TAB', '');
		$this->dws['dw_guia_recepcion']->set_item(0, 'TD_DISPLAY_ELIMINAR', 'none');
		
		for($i=0; $i<count($result_item); $i++) {
		$this->dws['dw_item_guia_recepcion']->set_item($i, 'CANTIDAD', '0');
		
		}
	}
	function load_record(){
		parent::load_record();
		$this->dws['dw_guia_recepcion']->add_control(new edit_text_hidden('NRO_DOC'));
		$this->dws['dw_guia_recepcion']->set_entrable('TIPO_DOC', false);
		
		$COD_ESTADO_GUIA_RECEPCION = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
		
		if ($COD_ESTADO_GUIA_RECEPCION == self::K_ESTADO_GR_EMITIDA){
			unset($this->dws['dw_guia_recepcion']->controls['COD_ESTADO_GUIA_RECEPCION']);
			$this->dws['dw_guia_recepcion']->controls['NOM_ESTADO_GUIA_RECEPCION']->type = 'hidden';
			$this->dws['dw_guia_recepcion']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));
			
			$sql = "select 	COD_ESTADO_GUIA_RECEPCION
							,NOM_ESTADO_GUIA_RECEPCION
					from 	ESTADO_GUIA_RECEPCION
					where 	COD_ESTADO_GUIA_RECEPCION = ".self::K_ESTADO_GR_EMITIDA." or
							COD_ESTADO_GUIA_RECEPCION = ".self::K_ESTADO_GR_ANULADA."
					order by COD_ESTADO_GUIA_RECEPCION";
			$this->dws['dw_guia_recepcion']->add_control($control = new drop_down_dw('COD_ESTADO_GUIA_RECEPCION',$sql,150));
			$control->set_onChange("mostrarOcultar_Anula(this);");
		}
	}
	
	function load_wo() {
		if ($this->tiene_wo)
			$this->wo = session::get("wo_guia_recepcion_arriendo");
	}
	function make_sql_auditoria() {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'guia_recepcion';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function make_sql_auditoria_relacionada($tabla) {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'guia_recepcion';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function delete_record($db) {
		return $db->EXECUTE_SP("spu_guia_recepcion", "'DELETE', ".$this->get_key());
	}
	function print_record() {
		$cod_guia_recepcion = $this->get_key();
		$COD_ESTADO_GUIA_RECEPCION = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->BEGIN_TRANSACTION();
		$sp = 'spu_guia_recepcion';
		$param = "'PRINT', $cod_guia_recepcion, $this->cod_usuario";
	    
		$sql= "SELECT	GR.COD_GUIA_RECEPCION 
							,dbo.f_format_date(GR.FECHA_GUIA_RECEPCION,3)FECHA_GUIA_RECEPCION
							,GR.COD_TIPO_GUIA_RECEPCION
							,CASE GR.TIPO_DOC 
								WHEN 'GUIA_DESPACHO' THEN 'GUIA DESPACHO' 
								WHEN 'FACTURA' THEN 'FACTURA'
								WHEN 'ARRIENDO' THEN 'MOD ARRIENDO'
								WHEN 'MOD_ARRIENDO' THEN 'MOD ARRIENDO'
								ELSE NULL
							END TIPO_DOC
							,GR.NRO_DOC
							,GR.OBS
							,E.NOM_EMPRESA
							,E.RUT
							,E.DIG_VERIF
							,U.NOM_USUARIO
							,P.NOM_PERSONA
							,TGR.NOM_TIPO_GUIA_RECEPCION
							,S.DIRECCION
							,S.TELEFONO
							,S.FAX
							,IGR.COD_PRODUCTO
							,IGR.NOM_PRODUCTO
							,IGR.CANTIDAD
							,COM.NOM_COMUNA
							,CIU.NOM_CIUDAD
							,M.COD_ARRIENDO
							,M.COD_MOD_ARRIENDO
							,convert(varchar(10), M.FECHA_MOD_ARRIENDO,103)FECHA_MOD_ARRIENDO
					FROM	GUIA_RECEPCION GR,
							SUCURSAL S left outer join COMUNA COM on S.COD_COMUNA = COM.COD_COMUNA, 
							ITEM_GUIA_RECEPCION IGR, EMPRESA E, USUARIO U, PERSONA P,
							TIPO_GUIA_RECEPCION TGR, CIUDAD CIU,MOD_ARRIENDO M 
					WHERE	GR.COD_GUIA_RECEPCION = ".$cod_guia_recepcion." AND
							IGR.COD_GUIA_RECEPCION = GR.COD_GUIA_RECEPCION AND
							E.COD_EMPRESA = GR.COD_EMPRESA AND
							U.COD_USUARIO = GR.COD_USUARIO AND
							P.COD_PERSONA = GR.COD_PERSONA AND
							TGR.COD_TIPO_GUIA_RECEPCION = GR.COD_TIPO_GUIA_RECEPCION AND
							S.COD_SUCURSAL = GR.COD_SUCURSAL AND
							S.COD_CIUDAD = CIU.COD_CIUDAD
							AND M.COD_MOD_ARRIENDO = GR.COD_DOC";
		
		
		if ($db->EXECUTE_SP($sp, $param)) {		// aqui dentro del sp se cambia el estado y se graba todo lo relacionado
			$db->COMMIT_TRANSACTION();
			
				$estado_gr_impresa = self::K_ESTADO_GR_IMPRESA; 
				$cod_estado_guia_recepcion = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
				if ($cod_estado_guia_recepcion != $estado_gr_impresa)//es la 1era vez que se imprime la Guia de Despacho 
					$this->f_envia_mail('IMPRESO');

			//// reporte
			$labels = array();
			$labels['strCOD_GUIA_RECEPCION'] = $cod_guia_recepcion;
			$rpt = new print_guia_recepcion($sql, $this->root_dir.'appl/guia_recepcion_arriendo/guia_recepcion_arriendo.xml', $labels, "Guia de Recepcion ".$cod_guia_recepcion.".pdf", 1);
			$this->_load_record();
			return true;
			}
		else {
			//// reporte
			$db->COMMIT_TRANSACTION();
			$labels = array();
			$labels['strCOD_GUIA_RECEPCION'] = $cod_guia_recepcion;
			$rpt = new print_guia_recepcion($sql, $this->root_dir.'appl/guia_recepcion_arriendo/guia_recepcion_arriendo.xml', $labels, "Guia de Recepcion ".$cod_guia_recepcion.".pdf", 1);
			$this->_load_record();
			return true;
		}			
	}
}
?>
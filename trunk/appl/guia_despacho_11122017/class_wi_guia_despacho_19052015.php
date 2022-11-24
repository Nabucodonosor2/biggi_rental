<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

class dw_item_guia_despacho extends datawindow {
	const K_ESTADO_SII_EMITIDA 			= 1;
	
	function add_controls_producto_help() {		
		if (isset($this->controls['PRECIO']))
			$num_dec = $this->controls['PRECIO']->num_dec;
		else
			$num_dec = 0;
			$java_script = "help_producto(this, ".$num_dec.");";
			$this->add_control($control = new edit_text_upper('COD_PRODUCTO', 18, 30));
			$control->set_onChange($java_script);
			$this->add_control($control = new edit_text_upper('NOM_PRODUCTO', 102, 100));
			$control->set_onChange($java_script);
		
			// Se guarda el old para los casos en que una validaci�n necesite volver al valor OLD  
			$this->add_control($control = new edit_text_upper('COD_PRODUCTO_OLD', 30, 30, 'hidden'));
				
			// mandatorys
			$this->set_mandatory('COD_PRODUCTO', 'C�digo del producto');
			$this->set_mandatory('NOM_PRODUCTO', 'Descripci�n del producto');
	}	
	function dw_item_guia_despacho() {
		$sql = "SELECT IGD.COD_ITEM_GUIA_DESPACHO,
						IGD.COD_GUIA_DESPACHO,
						IGD.ORDEN,
						IGD.ITEM,
						IGD.COD_PRODUCTO,
						IGD.COD_PRODUCTO COD_PRODUCTO_OLD,
						IGD.NOM_PRODUCTO,
						IGD.CANTIDAD,
						dbo.f_nv_cant_por_despachar(IGD.COD_ITEM_DOC, default) CANTIDAD_POR_DESPACHAR,
						IGD.PRECIO,
						IGD.COD_ITEM_DOC,
						GD.COD_DOC,
						case
							when GD.COD_DOC IS not NULL and GD.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
						else 'none'
						end TD_DISPLAY_CANT_POR_DESP,
						case
							when IGD.COD_ITEM_DOC IS NULL then ''
						else 'none'
						end TD_DISPLAY_ELIMINAR,
						COD_TIPO_TE,
						MOTIVO_TE,
						'' BOTON_PRECIO -- se utiliza en funcion comun js 'ingreso_TE'
				FROM    ITEM_GUIA_DESPACHO IGD, GUIA_DESPACHO GD
				WHERE   IGD.COD_GUIA_DESPACHO = {KEY1}
						AND GD.COD_GUIA_DESPACHO  = IGD.COD_GUIA_DESPACHO 
				ORDER BY ORDEN";
		
		parent::datawindow($sql, 'ITEM_GUIA_DESPACHO', true, true);
		$this->add_control(new edit_text_upper('COD_ITEM_GUIA_DESPACHO',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text_upper('ITEM',4, 5));
		$this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		$control->set_onChange("this.value = valida_ct_x_despachar(this);");
		$this->add_control(new static_num('CANTIDAD_POR_DESPACHAR',1));
		$this->add_control(new edit_text('COD_TIPO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('MOTIVO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('BOTON_PRECIO',10, 10, 'hidden'));
		$this->add_control(new computed('PRECIO', 0));
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL', "calc_dscto();");
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		$this->controls['COD_PRODUCTO']->set_onChange("change_item_guia_despacho(this, 'COD_PRODUCTO');");
		$this->set_first_focus('COD_PRODUCTO');

		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'C�digo del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
		
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ITEM', $this->row_count());
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		$this->set_item($row, 'TD_DISPLAY_CANT_POR_DESP', 'none');
		return $row;
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		// si existe COD_DOC no despliega boton "-".
		$COD_DOC = $this->get_item(0, 'COD_DOC');
		if ($COD_DOC != ''){ 
			$row = $this->redirect($record);
			$eliminar = '<img src="../../../../commonlib/trunk/images/b_delete_line.jpg" onClick="del_line_item(\''.$this->label_record.'_'.$row.'\', \''.$this->nom_tabla.'\');" style="display:none">';
			$temp->setVar($this->label_record.".ELIMINAR_".strtoupper($this->label_record), $eliminar);
		}
	}
	function fill_template(&$temp) {
		parent::fill_template($temp);
		// si existe COD_DOC no despliega boton "+".
		if ($this->row_count()==0)
			$COD_DOC = '';		// debe ser == '' para que se agregue el boton "+"
		else
			$COD_DOC = $this->get_item(0, 'COD_DOC');
		if ($COD_DOC != ''){ 
			$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_item(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="display:none">';
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
		}
		if ($this->b_add_line_visible) {
			if ($this->entrable){
				if ($COD_DOC != '')
					$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_gd(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
				else
				{
					$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_gd(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
					$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
				}
			}else 
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line_d.jpg">';
		}
		
	}	
	function update($db) {
		$sp = 'spu_item_guia_despacho';
		
		for ($i = 0; $i < $this->row_count(); $i++){
				$statuts = $this->get_status_row($i);
				if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
					continue;
					
				$COD_ITEM_GUIA_DESPACHO = $this->get_item($i, 'COD_ITEM_GUIA_DESPACHO');
				$COD_GUIA_DESPACHO 		= $this->get_item($i, 'COD_GUIA_DESPACHO');
				$ORDEN 					= $this->get_item($i, 'ORDEN');
				$ITEM 					= $this->get_item($i, 'ITEM');
				$COD_PRODUCTO 			= $this->get_item($i, 'COD_PRODUCTO');
				$NOM_PRODUCTO 			= $this->get_item($i, 'NOM_PRODUCTO');
				$PRECIO 				= $this->get_item($i, 'PRECIO');
				$COD_ITEM_DOC			= $this->get_item($i, 'COD_ITEM_DOC');			
				$CANTIDAD 				= $this->get_item($i, 'CANTIDAD');		
				$COD_TIPO_TE			= $this->get_item($i, 'COD_TIPO_TE');
				$COD_TIPO_TE			= ($COD_TIPO_TE =='') ? "null" : "$COD_TIPO_TE";			
				$MOTIVO_TE		 		= $this->get_item($i, 'MOTIVO_TE');
				
				if ($PRECIO=='') $PRECIO = 0;		
				$COD_ITEM_GUIA_DESPACHO = ($COD_ITEM_GUIA_DESPACHO=='') ? "null" : $COD_ITEM_GUIA_DESPACHO;
				$COD_ITEM_DOC = ($COD_ITEM_DOC=='') ? "null" : $COD_ITEM_DOC;
				
				if ($statuts == K_ROW_NEW_MODIFIED)
					$operacion = 'INSERT';
				else if ($statuts == K_ROW_MODIFIED)
					$operacion = 'UPDATE';		
					
					$param = "'$operacion', $COD_ITEM_GUIA_DESPACHO, $COD_GUIA_DESPACHO, $ORDEN, '$ITEM', '$COD_PRODUCTO', '$NOM_PRODUCTO', $CANTIDAD, $PRECIO, $COD_ITEM_DOC, $COD_TIPO_TE, '$MOTIVO_TE'";
				
				if (!$db->EXECUTE_SP($sp, $param)) 
					return false;
				else {
					if ($statuts == K_ROW_NEW_MODIFIED) {
						$COD_ITEM_GUIA_DESPACHO = $db->GET_IDENTITY();
						$this->set_item($i, 'COD_ITEM_GUIA_DESPACHO', $COD_ITEM_GUIA_DESPACHO);		
					}
				}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_ITEM_GUIA_DESPACHO = $this->get_item($i, 'COD_ITEM_GUIA_DESPACHO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_ITEM_GUIA_DESPACHO"))
				return false;
		}	
		return true;
	}
}	
class dw_guia_despacho extends dw_help_empresa {
	const K_ESTADO_SII_EMITIDA 			= 1;	
	const K_ESTADO_SII_ANULADA			= 4;
		
	function dw_guia_despacho() {
		$ESTADO_ENVIADA_SII = 3;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT isnull(max(NRO_GUIA_DESPACHO), 0) + 1 NEW_NRO_GUIA_DESPACHO 
				FROM GUIA_DESPACHO
				WHERE COD_ESTADO_DOC_SII <> $ESTADO_ENVIADA_SII";		  
		$result = $db->build_results($sql);
		$NEW_NRO_GUIA_DESPACHO = $result[0]['NEW_NRO_GUIA_DESPACHO'];		
		
		$sql = "SELECT GD.COD_GUIA_DESPACHO,
					GD.FECHA_REGISTRO,
					GD.COD_USUARIO,
					U.NOM_USUARIO,
					GD.NRO_GUIA_DESPACHO,
					$NEW_NRO_GUIA_DESPACHO NEW_NRO_GUIA_DESPACHO,
					convert(varchar(20), GD.FECHA_GUIA_DESPACHO, 103) FECHA_GUIA_DESPACHO,
					GD.COD_ESTADO_DOC_SII,
					EDS.NOM_ESTADO_DOC_SII,
					GD.COD_EMPRESA,
					GD.COD_SUCURSAL_DESPACHO,
					dbo.f_get_direccion('GUIA_DESPACHO', GD.COD_GUIA_DESPACHO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_DESPACHO,
					GD.COD_PERSONA,
					dbo.f_emp_get_mail_cargo_persona(GD.COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA,
					GD.REFERENCIA,
					GD.NRO_ORDEN_COMPRA,
					GD.OBS,
					GD.RETIRADO_POR,
					GD.RUT_RETIRADO_POR,
					GD.DIG_VERIF_RETIRADO_POR,
					GD.GUIA_TRANSPORTE,
					GD.PATENTE,
					GD.COD_FACTURA,
					dbo.f_gd_nros_factura(COD_GUIA_DESPACHO) NRO_FACTURA,
					GD.COD_BODEGA,
					GD.COD_TIPO_GUIA_DESPACHO,
					GD.COD_DOC,
					convert(varchar(20), GD.FECHA_ANULA, 103) +'  '+ convert(varchar(20), GD.FECHA_ANULA, 8) FECHA_ANULA,
					GD.MOTIVO_ANULA,
					GD.COD_USUARIO_ANULA, 			
					GD.RUT RUT_GD,
					GD.DIG_VERIF DIG_VERIF_GD,
					GD.NOM_EMPRESA NOM_EMPRESA_GD,
					GD.GIRO GIRO_GD,
					GD.NOM_SUCURSAL,
					E.ALIAS,
					E.RUT,
					E.DIG_VERIF,
					E.NOM_EMPRESA,
					E.GIRO,
					GD.DIRECCION,
					GD.FAX,
					GD.NOM_PERSONA,
					GD.MAIL,
					GD.COD_CARGO,	
					GD.NOM_PERSONA,
					GD.COD_CARGO,
					GD.COD_USUARIO_IMPRESION,
					GD.COD_INDICADOR_TIPO_TRASLADO,
					(select valor from parametro where cod_parametro=28 ) VALOR_GD_H,
					--(select valor from parametro where cod_parametro=29 ) VALOR_FA_H,
					case GD.COD_ESTADO_DOC_SII 
						when ".self::K_ESTADO_SII_ANULADA." then ''
						else 'none'
					end TR_DISPLAY,
					'' VISIBLE_DTE,
					case
							when GD.COD_DOC IS not NULL and GD.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
						else 'none'
					end TD_DISPLAY_CANT_POR_DESP,	
					case
							when GD.COD_DOC IS NULL then ''
						else 'none'
					end TD_DISPLAY_ELIMINAR,
					(select COD_ARRIENDO from MOD_ARRIENDO where COD_MOD_ARRIENDO = GD.COD_DOC) COD_ARRIENDO,
					0 SUM_TOTAL_H -- se utiliza objeto virtual, necesario en el funcionamiento de TE. En general.js, funcion 'computed' se utiliza   
				FROM GUIA_DESPACHO GD LEFT OUTER JOIN FACTURA F 
				ON GD.COD_FACTURA = F.COD_FACTURA, USUARIO U, EMPRESA E, ESTADO_DOC_SII EDS 
				WHERE	GD.COD_GUIA_DESPACHO = {KEY1} AND
						GD.COD_USUARIO = U.COD_USUARIO AND
						E.COD_EMPRESA = GD.COD_EMPRESA AND
						EDS.COD_ESTADO_DOC_SII = GD.COD_ESTADO_DOC_SII";
		
		parent::dw_help_empresa($sql);
		
		$this->add_control(new edit_text('COD_GUIA_DESPACHO',10,10, 'hidden', false, true));
		$this->add_control(new edit_text_upper('NRO_ORDEN_COMPRA', 25, 40));
		
		// DATOS GUIA_DESPACHO
		$this->add_control(new static_text('NRO_GUIA_DESPACHO'));	
		$this->add_control(new edit_text('NEW_NRO_GUIA_DESPACHO', 10, 10, 'hidden', false, true));	
		
		$sql	= "select 	 COD_TIPO_GUIA_DESPACHO
							,NOM_TIPO_GUIA_DESPACHO
					from 	 TIPO_GUIA_DESPACHO
					order by COD_TIPO_GUIA_DESPACHO";
		$this->add_control(new drop_down_dw('COD_TIPO_GUIA_DESPACHO',$sql,150));
		$this->set_entrable('COD_TIPO_GUIA_DESPACHO', false);
		$this->add_control(new edit_text('COD_ESTADO_DOC_SII',10,10, 'hidden'));
		$this->add_control(new static_text('NOM_ESTADO_DOC_SII'));
		$this->add_control(new static_text('NRO_FACTURA'));		
		
		$this->add_control(new static_link('COD_DOC', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=guia_despacho&modulo_destino=nota_venta&cod_modulo_destino=[COD_DOC]&cod_item_menu=1510&current_tab_page=0'));
		//$this->add_control(new static_text('COD_DOC'));
		$this->add_control(new static_text('COD_ARRIENDO'));
		$this->add_control(new edit_text_upper('REFERENCIA',95, 100));			
		$this->add_control(new edit_text('SUM_TOTAL_H',10,10, 'hidden'));
	
		// usuario anulaci�n
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
		$this->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->set_entrable('COD_USUARIO_ANULA', false);			
		
		$sql	= "SELECT  COD_INDICADOR_TIPO_TRASLADO
						  ,NOM_INDICADOR_TIPO_TRASLADO
				   FROM	INDICADOR_TIPO_TRASLADO
				   ORDER BY ORDEN";
		$this->add_control(new drop_down_dw('COD_INDICADOR_TIPO_TRASLADO',$sql,150));
		
		//PARAMETROS GUIA DESPACHO, FACTURA - pc
		$this->add_control(new edit_text('VALOR_GD_H',10, 10, 'hidden'));
			
		// campos duplicados
		$this->add_control(new static_num('RUT_GD'));
		$this->add_control(new static_text('DIG_VERIF_GD'));
		$this->add_control(new static_text('NOM_EMPRESA_GD'));
		$this->add_control(new static_text('GIRO_GD'));
		$this->add_control(new static_text('NOM_SUCURSAL'));
		$this->add_control(new static_text('NOM_PERSONA'));
		$this->add_control(new edit_text_upper('RETIRADO_POR',37, 100));
		$this->add_control(new edit_text_upper('GUIA_TRANSPORTE',37, 100));
		$this->add_control(new edit_text_upper('PATENTE',37, 100));
		$this->add_control(new edit_text_multiline('OBS',54,3));
		$this->add_control(new edit_rut('RUT_RETIRADO_POR', 10, 10, 'DIG_VERIF_RETIRADO_POR'));
		$this->add_control(new edit_dig_verif('DIG_VERIF_RETIRADO_POR', 'RUT_RETIRADO_POR'));		
		
		// asigna los mandatorys
		$this->set_mandatory('COD_ESTADO_DOC_SII', 'Estado');
		$this->set_mandatory('COD_EMPRESA', 'Empresa');
		$this->set_mandatory('COD_SUCURSAL_DESPACHO', 'Sucursal de Despacho');
		$this->set_mandatory('COD_PERSONA', 'Persona');
		$this->set_mandatory('REFERENCIA', 'Referencia');
		$this->set_mandatory('COD_TIPO_GUIA_DESPACHO', 'Tipo Despacho');
		$this->set_mandatory('REFERENCIA', 'Referencia'); 
	}
	function fill_record(&$temp, $record) {	
		parent::fill_record($temp, $record);
		$COD_DOC = $this->get_item(0, 'COD_DOC');
		$COD_ESTADO_DOC_SII = $this->get_item(0, 'COD_ESTADO_DOC_SII');
		
		if (($COD_DOC != '') or ($COD_ESTADO_DOC_SII != 1))  //la GD viene desde NV, o estado <> emitida
			$temp->setVar('DISABLE_BUTTON', 'style="display:none"');
		else{	
			if ($this->entrable)
				$temp->setVar('DISABLE_BUTTON', '');
			else
				$temp->setVar('DISABLE_BUTTON', 'disabled="disabled"');
		}				
	}
}
class wi_guia_despacho extends w_input {
	const K_ESTADO_SII_EMITIDA 			= 1;
	const K_ESTADO_SII_IMPRESA			= 2;
	const K_ESTADO_SII_ENVIADA			= 3;	
	const K_ESTADO_SII_ANULADA			= 4;	
	const K_PUEDE_ENVIAR_GD_DTE 		= '994505';
	const K_AUTORIZA_VISIBLE_BTN_DTE	= '994510';
	const K_TIPO_GUIA_DESPACHO_VENTA 	= 1;
	const K_TIPO_GUIA_DESPACHO_TRASLADO = 4;
	const K_IP_FTP		= 42;		// Direccion del FTP
	const K_USER_FTP	= 43;		//usuario para el FTP
	const K_PASS_FTP	= 44;		// password para el FTP

	function wi_guia_despacho($cod_item_menu) {		
		parent::w_input('guia_despacho', $cod_item_menu);
		$this->add_FK_delete_cascada('ITEM_GUIA_DESPACHO');	
		$this->hide_menu_when_from = false;		// Cuando se crear desde no se debe esconder menu

		// tab guia despacho
		// DATAWINDOWS GUIA DESPACHO
		$this->dws['dw_guia_despacho'] = new dw_guia_despacho();
				
		// tab items
		// DATAWINDOWS ITEMS GUIA DESPACHO
		$this->dws['dw_item_guia_despacho'] = new dw_item_guia_despacho();
		$this->set_first_focus('NRO_ORDEN_COMPRA');
		
		$this->dws['dw_guia_despacho']->set_entrable('COD_INDICADOR_TIPO_TRASLADO',false);
		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_ESTADO_DOC_SII');
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('COD_SUCURSAL_DESPACHO');
		$this->add_auditoria('COD_PERSONA');		
	}
	function new_record() {
		$this->b_delete_visible  = false; //cuando es un registro nuevo no muestra el boton eliminar
		$this->dws['dw_guia_despacho']->insert_row();
		$this->dws['dw_guia_despacho']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_guia_despacho']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_guia_despacho']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_guia_despacho']->set_item(0, 'COD_ESTADO_DOC_SII', self::K_ESTADO_SII_EMITIDA);
		$this->dws['dw_guia_despacho']->set_item(0, 'NOM_ESTADO_DOC_SII', 'EMITIDA');
		$this->dws['dw_guia_despacho']->set_item(0, 'VISIBLE_DTE', 'none');
		$this->dws['dw_guia_despacho']->set_entrable('COD_TIPO_GUIA_DESPACHO',false);
		$this->dws['dw_guia_despacho']->set_item(0, 'COD_TIPO_GUIA_DESPACHO', self::K_TIPO_GUIA_DESPACHO_TRASLADO);
		$this->dws['dw_guia_despacho']->set_item(0, 'COD_INDICADOR_TIPO_TRASLADO', '6');
		$this->dws['dw_guia_despacho']->set_item(0, 'TD_DISPLAY_CANT_POR_DESP', 'none');
		$this->dws['dw_guia_despacho']->set_item(0, 'TD_DISPLAY_CANT_POR_DESP', 'none');
		
		//pc
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql_gd="select valor valor_gd from parametro where cod_parametro=28";
		$result = $db->build_results($sql_gd);
		$valor_gd = $result[0]['valor_gd'];
		$sql_fa="select valor valor_fa from parametro where cod_parametro=29";
		$result = $db->build_results($sql_fa);
		$valor_fa = $result[0]['valor_fa'];

		//seteo en el htm estas variables
		$this->dws['dw_guia_despacho']->set_item(0, 'VALOR_GD_H', $valor_gd);
	}
	function load_record() {
		$cod_guia_despacho = $this->get_item_wo($this->current_record, 'COD_GUIA_DESPACHO');
		$this->dws['dw_guia_despacho']->retrieve($cod_guia_despacho);
		$cod_empresa = $this->dws['dw_guia_despacho']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_guia_despacho']->controls['COD_SUCURSAL_DESPACHO']->retrieve($cod_empresa);
		$this->dws['dw_guia_despacho']->controls['COD_PERSONA']->retrieve($cod_empresa);
		$COD_ESTADO_DOC_SII = $this->dws['dw_guia_despacho']->get_item(0, 'COD_ESTADO_DOC_SII');
		
		$this->b_print_visible 	 = true;
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible	 = true;
		$this->b_delete_visible  = true;
		
		$this->dws['dw_guia_despacho']->set_entrable('NRO_ORDEN_COMPRA'      , true);
		$this->dws['dw_guia_despacho']->set_entrable('REFERENCIA'			 , true);
		$this->dws['dw_guia_despacho']->set_entrable('RETIRADO_POR'			 , true);
		$this->dws['dw_guia_despacho']->set_entrable('GUIA_TRANSPORTE'		 , true);
		$this->dws['dw_guia_despacho']->set_entrable('PATENTE'				 , true);
		$this->dws['dw_guia_despacho']->set_entrable('OBS'					 , true);
		$this->dws['dw_guia_despacho']->set_entrable('RUT_RETIRADO_POR'		 , true);
		$this->dws['dw_guia_despacho']->set_entrable('DIG_VERIF_RETIRADO_POR', true);
		$this->dws['dw_guia_despacho']->set_entrable('NOM_EMPRESA'			, true);
		$this->dws['dw_guia_despacho']->set_entrable('ALIAS'				, true);
		$this->dws['dw_guia_despacho']->set_entrable('COD_EMPRESA'			, true);
		$this->dws['dw_guia_despacho']->set_entrable('RUT'					, true);
		$this->dws['dw_guia_despacho']->set_entrable('COD_SUCURSAL_DESPACHO', true);
		$this->dws['dw_guia_despacho']->set_entrable('COD_PERSONA'			, true);
		
		// aqui se dejan no modificables los datos del tab items
		$this->dws['dw_item_guia_despacho']->set_entrable_dw(true);
		
		if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_EMITIDA) {
			unset($this->dws['dw_guia_despacho']->controls['COD_ESTADO_DOC_SII']);
			$this->dws['dw_guia_despacho']->add_control(new edit_text('COD_ESTADO_DOC_SII',10,10, 'hidden'));
			$this->dws['dw_guia_despacho']->controls['NOM_ESTADO_DOC_SII']->type = '';
			
			if($this->tiene_privilegio_opcion(self::K_PUEDE_ENVIAR_GD_DTE)){
				$this->dws['dw_guia_despacho']->set_item(0, 'VISIBLE_DTE', '');
			}else{
				$this->dws['dw_guia_despacho']->set_item(0, 'VISIBLE_DTE', 'none');
			}
			
			$COD_DOC = $this->dws['dw_guia_despacho']->get_item(0, 'COD_DOC');
			if ($COD_DOC  != '') {	
				$this->dws['dw_guia_despacho']->set_entrable('NRO_ORDEN_COMPRA'     , false);
				$this->dws['dw_guia_despacho']->set_entrable('REFERENCIA'			, false);			
				$this->dws['dw_guia_despacho']->set_entrable('RUT'					, false);
				$this->dws['dw_guia_despacho']->set_entrable('ALIAS'				, false);
				$this->dws['dw_guia_despacho']->set_entrable('COD_EMPRESA'			, false);
				$this->dws['dw_guia_despacho']->set_entrable('NOM_EMPRESA'			, false);
				$this->dws['dw_guia_despacho']->set_entrable('COD_SUCURSAL_DESPACHO', false);
				$this->dws['dw_guia_despacho']->set_entrable('COD_PERSONA'			, false);
				
				// aqui se dejan no modificables los datos del tab items
				$this->dws['dw_item_guia_despacho']->set_entrable('ORDEN'      		, false);
				$this->dws['dw_item_guia_despacho']->set_entrable('ITEM'      		, false);
				$this->dws['dw_item_guia_despacho']->set_entrable('COD_PRODUCTO'   	, false);
				$this->dws['dw_item_guia_despacho']->set_entrable('NOM_PRODUCTO'  	, false);
			}
		}
		else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_IMPRESA) {
			$this->dws['dw_guia_despacho']->set_item(0, 'VISIBLE_DTE', 'none');
			
			$sql = "select 	COD_ESTADO_DOC_SII
							,NOM_ESTADO_DOC_SII
					from ESTADO_DOC_SII
					where COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_IMPRESA." or
							COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_ANULADA."
					order by COD_ESTADO_DOC_SII";
					
			unset($this->dws['dw_guia_despacho']->controls['COD_ESTADO_DOC_SII']);
			$this->dws['dw_guia_despacho']->add_control($control = new drop_down_dw('COD_ESTADO_DOC_SII',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_guia_despacho']->controls['NOM_ESTADO_DOC_SII']->type = 'hidden';
			$this->dws['dw_guia_despacho']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));

			$this->dws['dw_guia_despacho']->set_entrable('NRO_ORDEN_COMPRA'      , false);
			$this->dws['dw_guia_despacho']->set_entrable('REFERENCIA'			 , false);
			$this->dws['dw_guia_despacho']->set_entrable('RETIRADO_POR'			 , false);
			$this->dws['dw_guia_despacho']->set_entrable('GUIA_TRANSPORTE'		 , false);
			$this->dws['dw_guia_despacho']->set_entrable('PATENTE'				 , false);
			$this->dws['dw_guia_despacho']->set_entrable('OBS'					 , false);
			$this->dws['dw_guia_despacho']->set_entrable('RUT_RETIRADO_POR'		 , false);
			$this->dws['dw_guia_despacho']->set_entrable('DIG_VERIF_RETIRADO_POR', false);
			$this->dws['dw_guia_despacho']->set_entrable('NOM_EMPRESA'			, false);
			$this->dws['dw_guia_despacho']->set_entrable('ALIAS'				, false);
			$this->dws['dw_guia_despacho']->set_entrable('COD_EMPRESA'			, false);
			$this->dws['dw_guia_despacho']->set_entrable('RUT'					, false);
			$this->dws['dw_guia_despacho']->set_entrable('COD_SUCURSAL_DESPACHO', false);
			$this->dws['dw_guia_despacho']->set_entrable('COD_PERSONA'			, false);
			
			// aqui se dejan no modificables los datos del tab items
			$this->dws['dw_item_guia_despacho']->set_entrable_dw(false);
			$this->b_delete_visible  = false;
		}
		else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_ENVIADA) {
			//SI USUARIO TIENE PRIVILEGIOS DE ENVIAR POR SEGUNDA VES LA GD-ELECTRONICA
			if($this->tiene_privilegio_opcion(self::K_AUTORIZA_VISIBLE_BTN_DTE)){
				$this->dws['dw_guia_despacho']->set_item(0, 'VISIBLE_DTE', '');
			}else{
				$this->dws['dw_guia_despacho']->set_item(0, 'VISIBLE_DTE', 'none');
			}
			$this->b_print_visible 	 = false;
			
			$sql = "select 	COD_ESTADO_DOC_SII
							,NOM_ESTADO_DOC_SII
					from ESTADO_DOC_SII
					where COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_ENVIADA." or
							COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_ANULADA."
					order by COD_ESTADO_DOC_SII";
					
			$this->dws['dw_guia_despacho']->set_entrable('NRO_ORDEN_COMPRA'      , false);
			$this->dws['dw_guia_despacho']->set_entrable('REFERENCIA'			 , false);
			$this->dws['dw_guia_despacho']->set_entrable('RETIRADO_POR'			 , false);
			$this->dws['dw_guia_despacho']->set_entrable('GUIA_TRANSPORTE'		 , false);
			$this->dws['dw_guia_despacho']->set_entrable('PATENTE'				 , false);
			$this->dws['dw_guia_despacho']->set_entrable('OBS'					 , false);
			$this->dws['dw_guia_despacho']->set_entrable('RUT_RETIRADO_POR'		 , false);
			$this->dws['dw_guia_despacho']->set_entrable('DIG_VERIF_RETIRADO_POR', false);
			$this->dws['dw_guia_despacho']->set_entrable('NOM_EMPRESA'			, false);
			$this->dws['dw_guia_despacho']->set_entrable('ALIAS'				, false);
			$this->dws['dw_guia_despacho']->set_entrable('COD_EMPRESA'			, false);
			$this->dws['dw_guia_despacho']->set_entrable('RUT'					, false);
			$this->dws['dw_guia_despacho']->set_entrable('COD_SUCURSAL_DESPACHO', false);
			$this->dws['dw_guia_despacho']->set_entrable('COD_PERSONA'			, false);
			$this->dws['dw_guia_despacho']->set_entrable('COD_INDICADOR_TIPO_TRASLADO', false);
			
			// aqui se dejan no modificables los datos del tab items
			$this->dws['dw_item_guia_despacho']->set_entrable_dw(false);
			$this->b_delete_visible  = false;
		}
		else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_ANULADA) {
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;
			$this->dws['dw_guia_despacho']->set_item(0, 'VISIBLE_DTE', 'none');
		}
		$this->dws['dw_item_guia_despacho']->retrieve($cod_guia_despacho);
		
		//campos duplicados
		if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_EMITIDA){ // estado = emitida
			$giro = $this->dws['dw_guia_despacho']->get_item(0, 'GIRO');
			$this->dws['dw_guia_despacho']->controls['RUT']->type = 'text';
			$this->dws['dw_guia_despacho']->controls['RUT_GD']->type = 'hidden';
			$this->dws['dw_guia_despacho']->controls['DIG_VERIF']->type = 'text';
			$this->dws['dw_guia_despacho']->controls['DIG_VERIF_GD']->type = 'hidden';
			$this->dws['dw_guia_despacho']->controls['NOM_EMPRESA']->type = 'text';
			$this->dws['dw_guia_despacho']->controls['NOM_EMPRESA_GD']->type = 'hidden';
			$this->dws['dw_guia_despacho']->controls['GIRO']->type = '';
			$this->dws['dw_guia_despacho']->controls['GIRO_GD']->type = 'hidden';
			$this->dws['dw_guia_despacho']->set_visible('COD_SUCURSAL_DESPACHO', true);
			$this->dws['dw_guia_despacho']->controls['NOM_SUCURSAL']->type = 'hidden';
			$this->dws['dw_guia_despacho']->set_visible('COD_PERSONA', true);
			$this->dws['dw_guia_despacho']->controls['NOM_PERSONA']->type = 'hidden';
		}
		else{
			$this->dws['dw_guia_despacho']->controls['RUT']->type = 'hidden';
			$this->dws['dw_guia_despacho']->controls['RUT_GD']->type = '';
			$this->dws['dw_guia_despacho']->controls['DIG_VERIF']->type = 'hidden';
			$this->dws['dw_guia_despacho']->controls['DIG_VERIF_GD']->type = '';	
			$this->dws['dw_guia_despacho']->controls['NOM_EMPRESA']->type = 'hidden';
			$this->dws['dw_guia_despacho']->controls['NOM_EMPRESA_GD']->type = '';	
			$this->dws['dw_guia_despacho']->controls['GIRO']->type = 'hidden';
			$this->dws['dw_guia_despacho']->controls['GIRO_GD']->type = '';	
			$this->dws['dw_guia_despacho']->set_visible('COD_SUCURSAL_DESPACHO', false);
			$this->dws['dw_guia_despacho']->controls['NOM_SUCURSAL']->type = '';	
			$this->dws['dw_guia_despacho']->set_visible('COD_PERSONA', false);
			$this->dws['dw_guia_despacho']->controls['NOM_PERSONA']->type = '';	
		}
	}
	function get_key() {
		return $this->dws['dw_guia_despacho']->get_item(0, 'COD_GUIA_DESPACHO');
	}
	function get_key_para_ruta_menu() {
		return $this->dws['dw_guia_despacho']->get_item(0, 'NRO_GUIA_DESPACHO');
	}
	function save_record($db) {
		$COD_GUIA_DESPACHO				= $this->get_key();
		$COD_USUARIO_IMPRESION			= $this->dws['dw_guia_despacho']->get_item(0, 'COD_USUARIO_IMPRESION');
		$COD_USUARIO					= $this->dws['dw_guia_despacho']->get_item(0, 'COD_USUARIO');
		$NRO_GUIA_DESPACHO				= $this->dws['dw_guia_despacho']->get_item(0, 'NRO_GUIA_DESPACHO');
		$COD_ESTADO_DOC_SII				= $this->dws['dw_guia_despacho']->get_item(0, 'COD_ESTADO_DOC_SII');
		$COD_EMPRESA					= $this->dws['dw_guia_despacho']->get_item(0, 'COD_EMPRESA');
		$COD_SUCURSAL_DESPACHO			= $this->dws['dw_guia_despacho']->get_item(0, 'COD_SUCURSAL_DESPACHO');
		$COD_PERSONA					= $this->dws['dw_guia_despacho']->get_item(0, 'COD_PERSONA');
		$REFERENCIA						= $this->dws['dw_guia_despacho']->get_item(0, 'REFERENCIA');
		$REFERENCIA 					= str_replace("'", "''", $REFERENCIA);
		$NRO_ORDEN_COMPRA				= $this->dws['dw_guia_despacho']->get_item(0, 'NRO_ORDEN_COMPRA');
		$OBS							= $this->dws['dw_guia_despacho']->get_item(0, 'OBS');
		$OBS 							= str_replace("'", "''", $OBS);
		$RETIRADO_POR					= $this->dws['dw_guia_despacho']->get_item(0, 'RETIRADO_POR');
		$RUT_RETIRADO_POR				= $this->dws['dw_guia_despacho']->get_item(0, 'RUT_RETIRADO_POR');
		$DIG_VERIF_RETIRADO_POR			= $this->dws['dw_guia_despacho']->get_item(0, 'DIG_VERIF_RETIRADO_POR');
		$GUIA_TRANSPORTE				= $this->dws['dw_guia_despacho']->get_item(0, 'GUIA_TRANSPORTE');
		$PATENTE						= $this->dws['dw_guia_despacho']->get_item(0, 'PATENTE');
		$COD_FACTURA					= $this->dws['dw_guia_despacho']->get_item(0, 'COD_FACTURA');
		$COD_BODEGA						= $this->dws['dw_guia_despacho']->get_item(0, 'COD_BODEGA');
		$COD_TIPO_GUIA_DESPACHO			= $this->dws['dw_guia_despacho']->get_item(0, 'COD_TIPO_GUIA_DESPACHO');
		$COD_DOC						= $this->dws['dw_guia_despacho']->get_item(0, 'COD_DOC');
		$MOTIVO_ANULA					= $this->dws['dw_guia_despacho']->get_item(0, 'MOTIVO_ANULA');
		$MOTIVO_ANULA 					= str_replace("'", "''", $MOTIVO_ANULA);
		$COD_USUARIO_ANULA				= $this->dws['dw_guia_despacho']->get_item(0, 'COD_USUARIO_ANULA');
		$COD_INDICADOR_TIPO_TRASLADO	= $this->dws['dw_guia_despacho']->get_item(0, 'COD_INDICADOR_TIPO_TRASLADO');
		
		if (($MOTIVO_ANULA != '') && ($COD_USUARIO_ANULA == '')) // se anula 
			$COD_USUARIO_ANULA			= $this->cod_usuario;
		else
			$COD_USUARIO_ANULA			= "null";
		
		$COD_GUIA_DESPACHO				= ($COD_GUIA_DESPACHO =='') ? "null" : $COD_GUIA_DESPACHO;
		$NRO_GUIA_DESPACHO				= ($NRO_GUIA_DESPACHO =='') ? "null" : $NRO_GUIA_DESPACHO;
		$NRO_ORDEN_COMPRA				= ($NRO_ORDEN_COMPRA =='') ? "null" : "'$NRO_ORDEN_COMPRA'";
		$OBS							= ($OBS =='') ? "null" : "'$OBS'";
		$RETIRADO_POR					= ($RETIRADO_POR =='') ? "null" : "'$RETIRADO_POR'";
		$RUT_RETIRADO_POR				= ($RUT_RETIRADO_POR =='') ? "null" : $RUT_RETIRADO_POR;
		$DIG_VERIF_RETIRADO_POR			= ($DIG_VERIF_RETIRADO_POR =='') ? "null" : "'$DIG_VERIF_RETIRADO_POR'";
		$GUIA_TRANSPORTE				= ($GUIA_TRANSPORTE =='') ? "null" : "'$GUIA_TRANSPORTE'"; 
		$PATENTE						= ($PATENTE =='') ? "null" : "'$PATENTE'"; 
		$COD_FACTURA					= ($COD_FACTURA =='') ? "null" : $COD_FACTURA;
		$COD_BODEGA						= ($COD_BODEGA =='') ? "null" : $COD_BODEGA; 
		$COD_DOC						= ($COD_DOC =='') ? "null" : $COD_DOC; 
		$MOTIVO_ANULA					= ($MOTIVO_ANULA =='') ? "null" : "'$MOTIVO_ANULA'";
		$COD_USUARIO_IMPRESION			= ($COD_USUARIO_IMPRESION =='') ? "null" : $COD_USUARIO_IMPRESION;
		$COD_INDICADOR_TIPO_TRASLADO	= ($COD_INDICADOR_TIPO_TRASLADO =='') ? "null" : $COD_INDICADOR_TIPO_TRASLADO;
	
		$sp = 'spu_guia_despacho';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';					
								
		$param	= "'$operacion'
				,$COD_GUIA_DESPACHO
				,$COD_USUARIO_IMPRESION
				,$COD_USUARIO	
				,$NRO_GUIA_DESPACHO
				,$COD_ESTADO_DOC_SII					
				,$COD_EMPRESA		
				,$COD_SUCURSAL_DESPACHO		
				,$COD_PERSONA				
				,'$REFERENCIA'					
				,$NRO_ORDEN_COMPRA			
				,$OBS						
				,$RETIRADO_POR				
				,$RUT_RETIRADO_POR			
				,$DIG_VERIF_RETIRADO_POR		
				,$GUIA_TRANSPORTE			
				,$PATENTE					
				,$COD_FACTURA					
				,$COD_BODEGA					
				,$COD_TIPO_GUIA_DESPACHO		
				,$COD_DOC							
				,$MOTIVO_ANULA				
				,$COD_USUARIO_ANULA
				,$COD_INDICADOR_TIPO_TRASLADO";	
				
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_GUIA_DESPACHO = $db->GET_IDENTITY();
				$this->dws['dw_guia_despacho']->set_item(0, 'COD_GUIA_DESPACHO', $COD_GUIA_DESPACHO);
			}
			for ($i=0; $i<$this->dws['dw_item_guia_despacho']->row_count(); $i++) 
			$this->dws['dw_item_guia_despacho']->set_item($i, 'COD_GUIA_DESPACHO', $COD_GUIA_DESPACHO);
		
			if (!$this->dws['dw_item_guia_despacho']->update($db)) return false;
			
			$parametros_sp = "'item_guia_despacho','guia_despacho',$COD_GUIA_DESPACHO";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)) return false;
			
			return true;
		}
		return false;							
	}
	function print_record() {
		if (!$this->lock_record())
			return false;
			$nro_guia_despacho = $_POST['wi_hidden'];
		if (!is_numeric($nro_guia_despacho)) {
			$this->redraw();
			$this->alert('El n�mero: '.$nro_guia_despacho.' no es un n�mero v�lido.');								
			return;
		}
		$cod_guia_despacho = $this->get_key();
		$cod_usuario_impresion = $this->cod_usuario;
		
	 	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT NRO_GUIA_DESPACHO FROM GUIA_DESPACHO WHERE NRO_GUIA_DESPACHO = $nro_guia_despacho AND COD_GUIA_DESPACHO <> $cod_guia_despacho";
		$result = $db->build_results($sql);
		$count = count($result);
		
		if ($count > 0){
			$this->redraw();		
			$this->dws['dw_guia_despacho']->message("Sr(a). Usuario: La gu�a de despacho N�".$nro_guia_despacho." ya existe.");	
			return false;
		}	
		else{
			//cuenta cuantos items hay
			$sql_cuenta="select count(*) CANTIDAD
						from ITEM_GUIA_DESPACHO
						where COD_GUIA_DESPACHO = $cod_guia_despacho";
			$result_cuenta = $db->build_results($sql_cuenta);
			$cantidad = $result_cuenta[0]['CANTIDAD'];
		
			//ve cant max de items permitidos
			$sql_max="select valor from parametro where cod_parametro = 28";
			$result_max = $db->build_results($sql_max);
			$cantidad_max = $result_max[0]['valor'];
			$cant_gd_a_hacer=ceil($cantidad/$cantidad_max);
			
			for ($i=0 ; $i < $cant_gd_a_hacer; $i++) {
				$db->BEGIN_TRANSACTION();
				$sp = 'spu_guia_despacho';
				$param = "'PRINT', $cod_guia_despacho, $cod_usuario_impresion, null, $nro_guia_despacho";
				
				if ($db->EXECUTE_SP($sp, $param)) {
					$db->COMMIT_TRANSACTION();
					
					$estado_sii_impresa = self::K_ESTADO_SII_IMPRESA; 
					$cod_estado_doc = $this->dws['dw_guia_despacho']->get_item(0, 'COD_ESTADO_DOC_SII');
	
					$sql= "SELECT GD.COD_GUIA_DESPACHO ,
									GD.NRO_GUIA_DESPACHO,
									dbo.f_format_date(FECHA_GUIA_DESPACHO,3)FECHA_GUIA_DES,
									case E.IMPRIMIR_EMP_MAS_SUC
										when 'S' then GD.NOM_EMPRESA +' - '+ GD.NOM_SUCURSAL 
									else GD.NOM_EMPRESA
									end NOM_EMPRESA,
									GD.COD_FACTURA,						
									GD.RUT,
									GD.GIRO,
									GD.DIG_VERIF,
									dbo.f_get_direccion_print_gd (GD.COD_EMPRESA, '[DIRECCION]') DIRECCION_FA,
									dbo.f_get_direccion_print_gd (GD.COD_EMPRESA, '[NOM_COMUNA]') NOM_COMUNA,
									dbo.f_get_direccion_print_gd (GD.COD_EMPRESA, '[NOM_CIUDAD]') NOM_CIUDAD,
									GD.TELEFONO,
									GD.FAX,
									NRO_ORDEN_COMPRA,
									NOM_PERSONA,
									REFERENCIA,
									ITEM,
									CANTIDAD,
									NOM_PRODUCTO,
									COD_PRODUCTO,
									PRECIO,
									PRECIO * CANTIDAD TOTAL_GD,
									OBS,
									RETIRADO_POR,
									RUT_RETIRADO_POR,
									DIG_VERIF_RETIRADO_POR,
									GUIA_TRANSPORTE,
									PATENTE,
									COD_DOC,
									convert(varchar(5), GETDATE(), 8) HORA,
									U.INI_USUARIO,
									COD_TIPO_GUIA_DESPACHO			
							FROM	GUIA_DESPACHO GD ,ITEM_GUIA_DESPACHO IGD, EMPRESA E, USUARIO U
							WHERE	GD.COD_GUIA_DESPACHO = $cod_guia_despacho
							AND		GD.COD_USUARIO = U.COD_USUARIO
							AND		IGD.COD_GUIA_DESPACHO = GD.COD_GUIA_DESPACHO
							AND		E.COD_EMPRESA = GD.COD_EMPRESA";
								
						// reporte
						$labels = array();
						$labels['strCOD_GUIA_DESPACHO'] = $cod_guia_despacho;					
						$file_name = $this->find_file('guia_despacho', 'guia_despacho.xml');					
						$rpt = new print_guia_despacho($sql, $file_name, $labels, "Gu�a Despacho".$cod_guia_despacho, 0);
						
						// Solo se hace load_record para la 1ra gd impresa (para el caso que sean mas de una
						if ($i==0)
							$this->_load_record();
							$this->b_delete_visible  = false;
						if ($cant_gd_a_hacer > 1) {
							// el alert solo 1ra gd impresa
							if ($i==0)
								$this->alert('Se har�n '.$cant_gd_a_hacer.' gu�as de despacho.');
							if ($i+1 < $cant_gd_a_hacer) {
								// sigtes gds
								$nro_guia_despacho = $nro_guia_despacho + 1;
								$sql = "select COD_DOC
											,COD_EMPRESA
									  	from GUIA_DESPACHO
										where COD_GUIA_DESPACHO = $cod_guia_despacho";
								$result = $db->build_results($sql);
								$cod_doc = $result[0]['COD_DOC'];
								$cod_empresa = $result[0]['COD_EMPRESA'];
								
								// busca por cod_doc y cod_empresa, para mayor seguridad en encontrar las nuevas gds creadas.
								$sql = "select COD_GUIA_DESPACHO
										from GUIA_DESPACHO
										where COD_GUIA_DESPACHO > $cod_guia_despacho
										  and COD_DOC = $cod_doc
										  and COD_EMPRESA = $cod_empresa";
								$result = $db->build_results($sql);
								$cod_guia_despacho = $result[0]['COD_GUIA_DESPACHO'];
							} 
						}
					}
					else {
						$db->ROLLBACK_TRANSACTION();
						return false;
					}	
				}// end for
				if ($cant_gd_a_hacer > 1) {
					// VM => Falta actualiza el output, se esta actualizando solo cuando sale del input 
					// (por eso no permite navegar en las demas gds desde el input)
				}
				return true;		
			}
			$this->unlock_record();
		}
		// esta funcio envia mail  cuando se imprime e documento de guia despacho 
		function Envia_DTE($name_archivo, $fname){
			//SOLO para el CHAITEN
			//if (K_SERVER <> "192.168.2.26")
			//	return false;
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql_ftp =	"select dbo.f_get_parametro(".self::K_IP_FTP.") DIRECCION_FTP
								,dbo.f_get_parametro(".self::K_USER_FTP.")	USER_FTP
								,dbo.f_get_parametro(".self::K_PASS_FTP.")	PASS_FTP";
			$result_ftp = $db->build_results($sql_ftp);
			
			// datos de FTP
			$file_name_ftp = (dirname(__FILE__)."/../../ftp_dte.php");
			if (file_exists($file_name_ftp)){
				require_once($file_name_ftp);
				$K_DIRECCION_FTP	= K_DIRECCION_FTP;	//Ip FTP
				$K_USUARIO_FTP		= K_USUARIO_FTP;		//Usuario FTP
				$K_PASSWORD_FTP		= K_PASSWORD_FTP;		//Password FTP
				$K_PORT 			= 21; 		// PUERTO DEL FTP
			}else{
				$K_DIRECCION_FTP	= $result_ftp[0]['DIRECCION_FTP'] ;	//Ip FTP
				$K_USUARIO_FTP		= $result_ftp[0]['USER_FTP'] ;		//Usuario FTP
				$K_PASSWORD_FTP		= $result_ftp[0]['PASS_FTP'] ;		//Password FTP
				$K_PORT 			= 21; 		// PUERTO DEL FTP
			}
			// establecer una conexi�n b�sica
			$conn_id = ftp_connect($K_DIRECCION_FTP);
			if ($conn_id===false)
				return false;	
			// iniciar una sesi�n con nombre de usuario y contrase�a
			$login_result = ftp_login($conn_id, $K_USUARIO_FTP, $K_PASSWORD_FTP);
			if($login_result === false)
				return false;

			ftp_pasv ($conn_id, true) ;
			// subir un archivo
			//$upload = ftp_put($conn_id, $name_archivo, $fname, FTP_BINARY);  
			if(!(ftp_put($conn_id, $name_archivo, $fname, FTP_BINARY)))
				return false;

			// cerrar la conexi�n ftp 
			ftp_close($conn_id);
			return true;
		}
   		function envia_GD_Electronica(){
			if (!$this->lock_record())
				return false;
   			
			$cod_guia_despacho = $this->get_key();	
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$count1= 0;
			
			$sql_valida="SELECT CANTIDAD 
				  		 FROM ITEM_GUIA_DESPACHO
				  		 WHERE COD_GUIA_DESPACHO = $cod_guia_despacho";
				  
			$result_valida = $db->build_results($sql_valida);

			for($i = 0 ; $i < count($result_valida) ; $i++){
				if($result_valida[$i] <> 0)
					$count1 = $count1 + 1;
			}
			if($count1 > 18){
				$this->_load_record();
				$this->alert('Se est� ingresando m�s item que la cantidad permitida, favor contacte a IntegraSystem.');
				return false;
			}	
				
			$this->sepa_decimales	= ',';	//Usar , como separador de decimales
			$this->vacio 			= ' ';	//Usar rellenos de blanco, CAMPO ALFANUMERICO
			$this->llena_cero		= 0;	//Usar rellenos con '0', CAMPO NUMERICO
			$this->separador		= ';';	//Usar ; como separador de campos
			$cod_usuario_impresion = $this->cod_usuario;

			$cod_impresora_dte = $_POST['wi_impresora_dte'];
			if($cod_impresora_dte == 100){
				$EMISOR_GD = 'SALA VENTA';
			}else{
			if ($cod_impresora_dte == '')
				$sql = "SELECT U.NOM_USUARIO 
						FROM USUARIO U
						where U.COD_USUARIO = $cod_usuario_impresion";
			else
				$sql = "SELECT NOM_REGLA NOM_USUARIO
						FROM IMPRESORA_DTE
						WHERE COD_IMPRESORA_DTE = $cod_impresora_dte";
			$result = $db->build_results($sql);
			$EMISOR_GD = $result[0]['NOM_USUARIO'] ;
			}
			$db->BEGIN_TRANSACTION();
			$sp = 'spu_guia_despacho';
			$param = "'ENVIA_DTE', $cod_guia_despacho, $cod_usuario_impresion";
				
			if ($db->EXECUTE_SP($sp, $param)) {
				$db->COMMIT_TRANSACTION();		
	
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		   		$sql_dte= "SELECT GD.COD_GUIA_DESPACHO ,
										GD.NRO_GUIA_DESPACHO,
										dbo.f_format_date(FECHA_GUIA_DESPACHO,1)FECHA_GUIA_DES,
										case E.IMPRIMIR_EMP_MAS_SUC
											when 'S' then GD.NOM_EMPRESA +' - '+ GD.NOM_SUCURSAL 
										else GD.NOM_EMPRESA
										end NOM_EMPRESA,
										GD.COD_FACTURA,
										GD.DIRECCION,						
										GD.RUT,
										GD.GIRO,
										GD.DIG_VERIF,
										dbo.f_get_direccion_print_gd (GD.COD_EMPRESA, '[DIRECCION]') DIRECCION_FA,
										dbo.f_get_direccion_print_gd (GD.COD_EMPRESA, '[NOM_COMUNA]') NOM_COMUNA,
										dbo.f_get_direccion_print_gd (GD.COD_EMPRESA, '[NOM_CIUDAD]') NOM_CIUDAD,
										GD.TELEFONO,
										GD.FAX,
										NRO_ORDEN_COMPRA,
										NOM_PERSONA,
										dbo.f_emp_get_mail_cargo_persona(COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA,
										REFERENCIA,
										dbo.f_gd_nros_factura(GD.COD_GUIA_DESPACHO) NRO_FACTURA,
										GD.RETIRADO_POR,
										GD.RUT_RETIRADO_POR,
										GD.DIG_VERIF_RETIRADO_POR,
										GD.GUIA_TRANSPORTE,
										GD.PATENTE,
										ITEM,
										CANTIDAD,
										NOM_PRODUCTO,
										COD_PRODUCTO,
										PRECIO,
										PRECIO * CANTIDAD TOTAL_GD,
										OBS,
										RETIRADO_POR,
										RUT_RETIRADO_POR,
										DIG_VERIF_RETIRADO_POR,
										GUIA_TRANSPORTE,
										PATENTE,
										COD_DOC,
										IGD.ORDEN,
										convert(varchar(5), GETDATE(), 8) HORA,
										'$EMISOR_GD' NOM_USUARIO
										,(SELECT COD_SII 
										  FROM INDICADOR_TIPO_TRASLADO
										  WHERE COD_INDICADOR_TIPO_TRASLADO	= GD.COD_INDICADOR_TIPO_TRASLADO) COD_INDICADOR_TIPO_TRASLADO
								FROM	GUIA_DESPACHO GD ,ITEM_GUIA_DESPACHO IGD, EMPRESA E, USUARIO U
								WHERE	GD.COD_GUIA_DESPACHO = $cod_guia_despacho
								AND		GD.COD_USUARIO = U.COD_USUARIO
								AND		IGD.COD_GUIA_DESPACHO = GD.COD_GUIA_DESPACHO
								AND		E.COD_EMPRESA = GD.COD_EMPRESA";
				$result_dte = $db->build_results($sql_dte);
				//CANTIDAD DE ITEM_GUIA_DESPACHO 
				$count = count($result_dte);   	
	
				// datos de Guia Despacho
				$NRO_GUIA_DESPACHO	= $result_dte[0]['NRO_GUIA_DESPACHO'] ;	// 1 Numero Guia Despacho
				$FECHA_GUIA_DES		= $result_dte[0]['FECHA_GUIA_DES'] ;	// 2 Fecha Guia Despacho
				//Email - VE: =>En el caso de las Guia Despacho y otros documentos, no aplica por lo que se dejan 0;0 
				$TD					= $this->llena_cero;					// 3 Tipo Despacho
				//Email - VE: =>
				if($result_dte[0]['COD_INDICADOR_TIPO_TRASLADO'] == NULL)
					$TT				= $this->llena_cero;								// 4 Tipo Traslado
				else
					$TT				= $result_dte[0]['COD_INDICADOR_TIPO_TRASLADO'];	// 4 Tipo Traslado
					
				$PAGO_DTE			= $this->vacio;							// 5 Forma de Pago
				$FV					= $this->vacio;							// 6 Fecha Vencimiento
				$RUT				= $result_dte[0]['RUT'];				
				$DIG_VERIF			= $result_dte[0]['DIG_VERIF'];
				$RUT_EMPRESA		= $RUT.'-'.$DIG_VERIF;					// 7 Rut Empresa
				$NOM_EMPRESA		= $result_dte[0]['NOM_EMPRESA'] ;		// 8 Razol Social_Nombre Empresa
				$GIRO				= $result_dte[0]['GIRO'];				// 9 Giro Empresa
				$DIRECCION			= $result_dte[0]['DIRECCION'];			//10 Direccion empresa
				$MAIL_CARGO_PERSONA	= $result_dte[0]['MAIL_CARGO_PERSONA'];	//11 E-Mail Contacto
				$TELEFONO			= $result_dte[0]['TELEFONO'];			//12 Telefono Empresa
				$REFERENCIA			= $result_dte[0]['REFERENCIA'];			//12 Referencia de la Guia Despacho  //datos olvidado por VE.
				$NRO_GD				= $this->vacio;							//NUMERO DE GUIA DESPACHO PARA FACTURA
				$GENERA_SALIDA		= $this->vacio;							//Solicitado a VE por SP "DESPACHADO"
				$CANCELADA			= $this->vacio;							//Solicitado a VE por SP "CANCELADO"
				$SUBTOTAL			= $this->vacio;							//Solicitado a VE por SP "SUBTOTAL"
				$PORC_DSCTO1		= $this->vacio;							//Solicitado a VE por SP "PORC_DSCTO1"
				$PORC_DSCTO2		= $this->vacio;							//Solicitado a VE por SP "PORC_DSCTO2"
				$EMISOR_GD			= $result_dte[0]['NOM_USUARIO'];		//Solicitado a VE por SP "EMISOR_GD"
				$NOM_COMUNA			= $result_dte[0]['NOM_COMUNA'];			//13 Comuna Recepcion
				$NOM_CIUDAD			= $result_dte[0]['NOM_CIUDAD'];			//14 Ciudad Recepcion
				$DP					= $result_dte[0]['DIRECCION'];			//15 Direcci�n Postal
				$COP				= $result_dte[0]['NOM_COMUNA'];			//16 Comuna Postal
				$CIP				= $result_dte[0]['NOM_CIUDAD'];			//17 Ciudad Postal
				
				//DATOS DE TOTALES 
				$TOTAL_NETO			= $this->vacio;							//18 Monto Neto
				$PORC_IVA			= $this->vacio;							//19 Tasa IVA
				$MONTO_IVA			= $this->vacio;							//20 Monto IVA
				$TOTAL_CON_IVA		= $this->vacio;							//21 Monto Total
				$D1					= 'D1';															//22 Tipo de Mov 1 (Desc/Rec)
				$P1					= '$';															//23 Tipo de valor de Desc/Rec 1
				$MONTO_DSCTO1		= $this->vacio;							//24 Valor del Desc/Rec 1
				$D2					= 'D2';															//25 Tipo de Mov 2 (Desc/Rec)
				$P2					= '$';															//26 Tipo de valor de Desc/Rec 2
				$MONTO_DSCTO2		= $this->vacio;							//27 Valor del Desc/Rec 2
				$D3					= 'D3';															//28 Tipo de Mov 3 (Desc/Rec)
				$P3					= '$';									//29 Tipo de valor de Desc/Rec 3
				$MONTO_DSCTO3		= $this->vacio;							//30 Valor del Desc/Rec 3
				$NOM_FORMA_PAGO		= $this->vacio;							//Dato Especial forma de pago adicional
				$NRO_ORDEN_COMPRA	= $result_dte[0]['NRO_ORDEN_COMPRA'];	//Numero de Orden Pago
				$NRO_NOTA_VENTA		= $result_dte[0]['COD_DOC'];			//Solicitado a VE por SP
				$OBSERVACIONES		= $result_dte[0]['OBS'];				//si la Guia Despacho tiene notas u observaciones
				$OBSERVACIONES		= eregi_replace("[\n|\r|\n\r]", ' ', $OBSERVACIONES); //elimina los saltos de linea. entre otros caracteres
				$TOTAL_EN_PALABRAS	= $this->vacio;							//Total en palabras: Posterior al campo Notas
				$ATENCION			= $result_dte[0]['NOM_PERSONA'];		// Nombre de Atencion
				$NRO_FACTURA		= $result_dte[0]['NRO_FACTURA'];		// Nro de FActura
				$RETIRA_RECINTO		= $result_dte[0]['RETIRADO_POR'];		// Persona que Retira de Recinto
				$RECINTO			= $this->vacio;							// Recinto
				$PATENTE			= $result_dte[0]['PATENTE'];			// Patente de Vehiculo que retira
				$RUT_RETIRADO_POR	= $result_dte[0]['RUT_RETIRADO_POR'];				
				$DIG_VERIF_RETIRADO_POR	= $result_dte[0]['DIG_VERIF_RETIRADO_POR'];
				if($RUT_RETIRADO_POR == ''){
					$RUT_RETIRA = '';
				}else{
					$RUT_RETIRA		= $RUT_RETIRADO_POR.'-'.$DIG_VERIF_RETIRADO_POR; // 27 Rut quien Retira
				}
				$FECHA_HORA_RETIRO	= $this->vacio;							// 28 Fecha y Hora de retiro del recinto
				
				//GENERA EL NOMBRE DEL ARCHIVO
				$TIPO_FACT = 52;	//GUIA DESPACHO
	
				//GENERA EL ALFANUMERICO ALETORIO Y LLENA LA VARIABLE $RES = ALETORIO
				$length = 36;
				$source = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$source .= '1234567890';
				
				if($length>0){
			        $RES = "";
			        $source = str_split($source,1);
			        for($i=1; $i<=$length; $i++){
			            mt_srand((double)microtime() * 1000000);
			            $num	= mt_rand(1,count($source));
			            $RES	.= $source[$num-1];
			        }
				 
			    }			
				
				//GENERA ESPACIOS EN BLANCO
				$space = ' ';
				$i = 0; 
				while($i<=100){
					$space .= ' ';
				$i++;
				}
				
				//GENERA ESPACIOS CON CEROS
				$llena_cero = 0;
				$i = 0; 
				while($i<=100){
					$llena_cero .= 0;
				$i++;
				}
				
				//Asignando espacios en blanco Guia Despacho
				//LINEA 3
				$NRO_GUIA_DESPACHO	= substr($NRO_GUIA_DESPACHO.$space, 0, 10);		// 1 Numero Guia Despacho
				$FECHA_GUIA_DES	= substr($FECHA_GUIA_DES.$space, 0, 10);		// 2 Fecha Guia Despacho
				$TD				= substr($TD.$space, 0, 1);					// 3 Tipo Despacho
				$TT				= substr($TT.$space, 0, 1);					// 4 Tipo Traslado
				$PAGO_DTE		= substr($PAGO_DTE.$space, 0, 1);			// 5 Forma de Pago
				$FV				= substr($FV.$space, 0, 10);				// 6 Fecha Vencimiento
				$RUT_EMPRESA	= substr($RUT_EMPRESA.$space, 0, 10);		// 7 Rut Empresa
				$NOM_EMPRESA	= substr($NOM_EMPRESA.$space, 0, 100);		// 8 Razol Social_Nombre Empresa
				$GIRO			= substr($GIRO.$space, 0, 40);				// 9 Giro Empresa
				$DIRECCION		= substr($DIRECCION.$space, 0, 60);			//10 Direccion empresa
				$MAIL_CARGO_PERSONA = substr($MAIL_CARGO_PERSONA.$space, 0, 60);//11 E-Mail Contacto
				$TELEFONO		= substr($TELEFONO.$space, 0, 15);			//12 Telefono Empresa
				$REFERENCIA		= substr($REFERENCIA.$space, 0, 80);
				$NRO_GD			= substr($NRO_GD.$space, 0, 20);			//Solicitado a VE por SP
				$GENERA_SALIDA	= substr($GENERA_SALIDA.$space, 0, 30);		//DESPACHADO
				$CANCELADA		= substr($CANCELADA.$space, 0, 30);			//CANCELADO
				$SUBTOTAL		= substr($SUBTOTAL.$space, 0, 18);			//Solicitado a VE por SP "SUBTOTAL"
				$PORC_DSCTO1	= substr($PORC_DSCTO1.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO1"
				$PORC_DSCTO2	= substr($PORC_DSCTO2.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO2"
				$EMISOR_GD		= substr($EMISOR_GD.$space, 0, 50);			//Solicitado a VE por SP "EMISOR_GUIA_DESPACHO"
				//LINEA4
				$NOM_COMUNA		= substr($NOM_COMUNA.$space, 0, 20);		//13 Comuna Recepcion
				$NOM_CIUDAD		= substr($NOM_CIUDAD.$space, 0, 20);		//14 Ciudad Recepcion
				$DP				= substr($DP.$space, 0, 60);				//15 Direcci�n Postal
				$COP			= substr($COP.$space, 0, 20);				//16 Comuna Postal
				$CIP			= substr($CIP.$space, 0, 20);				//17 Ciudad Postal
	
				//Asignando espacios en blanco Totales de Guia Despacho
				$TOTAL_NETO		= substr($TOTAL_NETO.$space, 0, 18);		//18 Monto Neto
				$PORC_IVA		= substr($PORC_IVA.$space, 0, 5);			//19 Tasa IVA
				$MONTO_IVA		= substr($MONTO_IVA.$space, 0, 18);			//20 Monto IVA
				$TOTAL_CON_IVA	= substr($TOTAL_CON_IVA.$space, 0, 18);		//21 Monto Total
				$D1				= substr($D1.$space, 0, 1);					//22 Tipo de Mov 1 (Desc/Rec)
				$P1				= substr($P1.$space, 0, 1);					//23 Tipo de valor de Desc/Rec 1
				$MONTO_DSCTO1	= substr($MONTO_DSCTO1.$space, 0, 18);		//24 Valor del Desc/Rec 1
				$D2				= substr($D2.$space, 0, 1);					//25 Tipo de Mov 2 (Desc/Rec)
				$P2				= substr($P2.$space, 0, 1);					//26 Tipo de valor de Desc/Rec 2
				$MONTO_DSCTO2	= substr($MONTO_DSCTO2.$space, 0, 18);		//27 Valor del Desc/Rec 2
				$D3				= substr($D3.$space, 0, 1);					//28 Tipo de Mov 3 (Desc/Rec)
				$P3				= substr($P3.$space, 0, 1);					//29 Tipo de valor de Desc/Rec 3
				$MONTO_DSCTO3	= substr($MONTO_DSCTO3.$space, 0, 18);		//30 Valor del Desc/Rec 3
				$NOM_FORMA_PAGO = substr($NOM_FORMA_PAGO.$space, 0, 80);	//Dato Especial forma de pago adicional
				$NRO_ORDEN_COMPRA= substr($NRO_ORDEN_COMPRA.$space, 0, 20);	//Numero de Orden Pago
				$NRO_NOTA_VENTA = substr($NRO_NOTA_VENTA.$space, 0, 20);	//Numero de Nota Venta
				$OBSERVACIONES = substr($OBSERVACIONES.$space.$space.$space, 0, 250); //si la Guia Despacho tiene notas u observaciones
				
				$ATENCION		= substr($ATENCION.$space, 0, 30);			// Atencion a persona del cliente
				$NRO_FACTURA	= substr($NRO_FACTURA.$space, 0, 10);		// Numero de Factura
				$RETIRA_RECINTO	= substr($RETIRA_RECINTO.$space, 0, 30);	// Persona que Retira de Recinto
				$RECINTO		= substr($RECINTO.$space, 0, 30);			// Recinto
				$PATENTE		= substr($PATENTE.$space, 0, 30);			// Patente Vehiculo que retira
				$RUT_RETIRA		= substr($RUT_RETIRA.$space, 0, 18);		// Rut quien retira
				$FECHA_HORA_RETIRO = substr($FECHA_HORA_RETIRO.$space, 0, 20); // Fecha y hora de retiro del Recinto
							
				$name_archivo = $TIPO_FACT."_NPG_".$RES.".SPF";
				$fname = tempnam("/tmp", $name_archivo);
				$handle = fopen($fname,"w");
				//DATOS DE GUIA_DESPACHO A EXPORTAR 
				//linea 1 y 2
				fwrite($handle, "\r\n"); //salto de linea
				fwrite($handle, "\r\n"); //salto de linea
				//linea 3		
				fwrite($handle, ' ');									// 0 space 2
				fwrite($handle, $NRO_GUIA_DESPACHO.$this->separador);	// 1 Numero Guia Despacho	//OK MH	Linea 5
				fwrite($handle, $FECHA_GUIA_DES.$this->separador);		// 2 Fecha Guia Despacho	//OK MH	Linea 4
				fwrite($handle, $TD.$this->separador);					// 3 Tipo Despacho		//OK MH	Linea 7
				fwrite($handle, $TT.$this->separador);					// 4 Tipo Traslado		//OK MH	Linea 8
				fwrite($handle, $PAGO_DTE.$this->separador);			// 5 Forma de Pago		//OK MH	Linea 13
				fwrite($handle, $FV.$this->separador);					// 6 Fecha Vencimiento	//OK MH	Linea 31
				fwrite($handle, $RUT_EMPRESA.$this->separador);			// 7 Rut Empresa		//OK MH	Linea 52
				fwrite($handle, $NOM_EMPRESA.$this->separador);			// 8 Razol Social_Nombre Empresa //OK MH Linea 54
				fwrite($handle, $GIRO.$this->separador);				// 9 Giro Empresa	//OK MH 	Linea 58
				fwrite($handle, $DIRECCION.$this->separador);			//10 Direccion empresa	//OK MH Linea 61
				//Personalizados Linea 3
				fwrite($handle, $MAIL_CARGO_PERSONA.$this->separador);	//11 E-Mail Contacto //OK MH Linea 60
				fwrite($handle, $TELEFONO.$this->separador);			//12 Telefono Empresa //OK MH Linea 59
				fwrite($handle, $REFERENCIA.$this->separador);			//Referencia de la Guia Despacho //OK MH Linea 298
				fwrite($handle, $NRO_GD.$this->separador);	//Solicitado a VE por SP //Pendiente Se debe enviar en la linea 221 tantas veces como guias tenga referenciada la factura
				fwrite($handle, $GENERA_SALIDA.$this->separador);		//DESPACHADO Solicitado a VE por SP //OK MH Linea 297
				fwrite($handle, $CANCELADA.$this->separador);			//CANCELADO Solicitado a VE por SP //OK MH Linea 296
				fwrite($handle, $SUBTOTAL.$this->separador);			//Solicitado a VE por SP "SUBTOTAL" //OK MH Linea 200 Columna 48
				fwrite($handle, $PORC_DSCTO1.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO1" //OK MH Linea 201 Columna 48
				fwrite($handle, $PORC_DSCTO2.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO2" //OK MH Linea 202 Columna 48
				fwrite($handle, $EMISOR_GD.$this->separador);		//Solicitado a VE por SP "EMISOR_GUIA_DESPACHO" //OK MH Linea 48
				$MONTO_TOTAL = 0;
				//sumatoria de precios unitartios en GD
				for ($i = 0; $i < $count; $i++){
						$P_UNITARIO	= $result_dte[$i]['CANTIDAD'] * $result_dte[$i]['PRECIO'];
						$MONTO_TOTAL += $P_UNITARIO;
				}
				$monto_total_palabras = $MONTO_TOTAL;
				$MONTO_TOTAL = number_format($MONTO_TOTAL, 1, ',', '');
				$MONTO_TOTAL = substr($MONTO_TOTAL.$space, 0, 18);
	
				fwrite($handle, $MONTO_TOTAL.$this->separador);			// Monto Total de todos los productos
				fwrite($handle, $ATENCION.$this->separador);			// Atencion a persona del cliente
				fwrite($handle, $NRO_FACTURA.$this->separador);			// Numero de Factura
				fwrite($handle, $RETIRA_RECINTO.$this->separador);		// Persona que Retira de Recinto
				fwrite($handle, $RECINTO.$this->separador);				// Recinto
				fwrite($handle, $PATENTE.$this->separador);				// Patente Vehiculo que retira
				fwrite($handle, $RUT_RETIRA.$this->separador);			// Rut quien retira
				fwrite($handle, $FECHA_HORA_RETIRO.$this->separador);	// Fecha y hora de retiro del Recinto
				fwrite($handle, "\r\n"); //salto de linea
				
				//TOTAL EN PALABRAS
				$TOTAL_EN_PALABRAS = Numbers_Words::toWords($monto_total_palabras,"es"); 
				$TOTAL_EN_PALABRAS = strtr($TOTAL_EN_PALABRAS, "�����", "aeiou");
				$TOTAL_EN_PALABRAS = strtoupper($TOTAL_EN_PALABRAS);
				$TOTAL_EN_PALABRAS = substr($TOTAL_EN_PALABRAS.' PESOS.'.$space.$space, 0, 200);	//Total en palabras: de la sumatoria de precios unitarios				
				
				//linea 4
				fwrite($handle, ' ');									// 0 space 2
				fwrite($handle, $NOM_COMUNA.$this->separador);			//13 Comuna Recepcion
				fwrite($handle, $NOM_CIUDAD.$this->separador);			//14 Ciudad Recepcion
				fwrite($handle, $DP.$this->separador);					//15 Direcci�n Postal
				fwrite($handle, $COP.$this->separador);					//16 Comuna Postal
				fwrite($handle, $CIP.$this->separador);					//17 Ciudad Postal
				fwrite($handle, $TOTAL_NETO.$this->separador);			//18 Monto Neto
				fwrite($handle, $PORC_IVA.$this->separador);			//19 Tasa IVA
				fwrite($handle, $MONTO_IVA.$this->separador);			//20 Monto IVA
				fwrite($handle, $TOTAL_CON_IVA.$this->separador);		//21 Monto Total
				fwrite($handle, $D1.$this->separador);					//22 Tipo de Mov 1 (Desc/Rec)
				fwrite($handle, $P1.$this->separador);					//23 Tipo de valor de Desc/Rec 1
				fwrite($handle, $MONTO_DSCTO1.$this->separador);		//24 Valor del Desc/Rec 1			//OK MH Linea 201 Columna 48
				fwrite($handle, $D2.$this->separador);					//25 Tipo de Mov 2 (Desc/Rec)
				fwrite($handle, $P2.$this->separador);					//26 Tipo de valor de Desc/Rec 2
				fwrite($handle, $MONTO_DSCTO2.$this->separador);		//27 Valor del Desc/Rec 2			//OK MH Linea 202 Columna 48
				fwrite($handle, $D3.$this->separador);					//28 Tipo de Mov 3 (Desc/Rec)
				fwrite($handle, $P3.$this->separador);					//29 Tipo de valor de Desc/Rec 3			
				fwrite($handle, $MONTO_DSCTO3.$this->separador);		//30 Valor del Desc/Rec 2
				fwrite($handle, $NOM_FORMA_PAGO.$this->separador);		//Dato Especial forma de pago adicional
				fwrite($handle, $NRO_ORDEN_COMPRA.$this->separador);	//Numero de Orden Pago
				fwrite($handle, $NRO_NOTA_VENTA.$this->separador);		//Numero de Nota Venta
				fwrite($handle, $OBSERVACIONES.$this->separador);		//si la Guia Despacho tiene notas u observaciones
				fwrite($handle, $TOTAL_EN_PALABRAS.$this->separador);	//Total en palabras: Posterior al campo Notas
				fwrite($handle, "\r\n"); //salto de linea
	
				//datos de dw_item_guia_despacho linea 5 a 34
				for ($i = 0; $i < 30; $i++){
					if($i < $count){
						fwrite($handle, ' '); //0 space 2
						$ORDEN		= $result_dte[$i]['ORDEN'];	
						$MODELO		= $result_dte[$i]['COD_PRODUCTO'];
						$NOM_PRODUCTO = substr($result_dte[$i]['NOM_PRODUCTO'], 0, 48);
						$CANTIDAD	= number_format($result_dte[$i]['CANTIDAD'], 1, ',', '');
						$P_UNITARIO	= number_format($result_dte[$i]['PRECIO'], 1, ',', '');
						$TOTAL 		= $result_dte[$i]['CANTIDAD'] * $result_dte[$i]['PRECIO'];
						$TOTAL		= number_format($TOTAL, 1, ',', '');
						$DESCRIPCION= $MODELO; // se repite el modelo
						$CANTIDAD_DETALLE = $CANTIDAD; // se repite el $CANTIDAD
						$ORDEN = $ORDEN / 10; //ELIMINA EL CERO
						
						//Asignando espacios en blanco dw_item_guia_despacho
						$ORDEN		= substr($ORDEN.$space, 0, 2);
						$MODELO		= substr($MODELO.$space, 0, 35);
						$NOM_PRODUCTO= substr($NOM_PRODUCTO.$space, 0, 80);
						$CANTIDAD	= substr($CANTIDAD.$space, 0, 18);
						$P_UNITARIO	= substr($P_UNITARIO.$space, 0, 18);
						$TOTAL		= substr($TOTAL.$space, 0, 18);
						$DESCRIPCION= substr($DESCRIPCION.$space, 0, 59);
						$CANTIDAD_DETALLE = substr($CANTIDAD_DETALLE.$space, 0, 18);
	
						//DATOS DE ITEM_GUIA_DESPACHO A EXPORTAR
						fwrite($handle, $ORDEN.$this->separador);		//31 N�mero de L�nea
						fwrite($handle, $MODELO.$this->separador);		//32 C�digo item
						fwrite($handle, $NOM_PRODUCTO.$this->separador);//33 Nombre del Item
						fwrite($handle, $CANTIDAD.$this->separador);	//34 Cantidad
						fwrite($handle, $P_UNITARIO.$this->separador);	//35 Precio Unitario
						fwrite($handle, $TOTAL.$this->separador);		//36 Valor por linea de detalle
						fwrite($handle, $DESCRIPCION.$this->separador);	//37 personalizados Zona Detalles(Modelo �tem)
						fwrite($handle, $CANTIDAD_DETALLE.$this->separador);	//personalizados Zona Detalles SE REPITE $CANTIDAD
					}
					fwrite($handle, "\r\n");
				}
				
				//Linea 35 a 44	Referencia
				//$count_NV = 1;
				for($i = 0; $i < 1; $i++){
					fwrite($handle, ' '); //0 space 2
						$TDR	= $this->llena_cero;
						$FR		= $this->llena_cero;
						$FECHA_R= $this->vacio;
						$CR		= $this->llena_cero;
						$RER	= $this->vacio;
						
						//Asignando espacios en blanco Referencia
						$TDR	= substr($TDR.$space, 0, 3);
						$FR		= substr($FR.$space, 0, 18);
						$FECHA_R= substr($FECHA_R.$space, 0, 10);
						$CR		= substr($CR.$space, 0, 1);
						$RER	= substr($RER.$space, 0, 100);					
						
						fwrite($handle, $TDR.$this->separador);			//38 Tipo documento referencia
						fwrite($handle, $FR.$this->separador);			//39 Folio Referencia
						fwrite($handle, $FECHA_R.$this->separador);		//40 Fecha de Referencia
						fwrite($handle, $CR.$this->separador);			//41 C�digo de Referencia
						fwrite($handle, $RER.$this->separador);			//42 Raz�n expl�cita de la referencia
					fwrite($handle, "\r\n");
				}
				/*fclose($handle);
				header("Content-Type: application/x-msexcel; name=\"$name_archivo\"");
				header("Content-Disposition: inline; filename=\"$name_archivo\"");
				$fh=fopen($fname, "rb");
				fpassthru($fh);*/

				$upload = $this->Envia_DTE($name_archivo, $fname);
				$NRO_GUIA_DESPACHO = trim($NRO_GUIA_DESPACHO);
				if (!$upload) {
					$this->_load_record();
					$this->alert('No se pudo enviar Guia Despacho Electronica N� '.$NRO_GUIA_DESPACHO.', Por favor contacte a IntegraSystem.');								
				}else{
					$this->_load_record();
					$this->alert('Gesti�n Realizada con ex�to. Guia Despacho Electronica N� '.$NRO_GUIA_DESPACHO.'.');								
				}
				unlink($fname);
			}else{
				$db->ROLLBACK_TRANSACTION();
				return false;
			}			
			$this->unlock_record();
   		}
   		
		function envia_GD_Electronica2(){
			if (!$this->lock_record())
				return false;
   			$this->sepa_decimales	= ',';	//Usar , como separador de decimales
			$this->vacio 			= ' ';	//Usar rellenos de blanco, CAMPO ALFANUMERICO
			$this->llena_cero		= 0;	//Usar rellenos con '0', CAMPO NUMERICO
			$this->separador		= ';';	//Usar ; como separador de campos
			$cod_guia_despacho = $this->get_key();
			$cod_usuario_impresion = $this->cod_usuario;

			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$cod_impresora_dte = $_POST['wi_impresora_dte'];
			if($cod_impresora_dte == 100){
				$EMISOR_GD = 'SALA VENTA';
			}else{
			if ($cod_impresora_dte == '')
				$sql = "SELECT U.NOM_USUARIO 
						FROM USUARIO U
						where U.COD_USUARIO = $cod_usuario_impresion";
			else
				$sql = "SELECT NOM_REGLA NOM_USUARIO
						FROM IMPRESORA_DTE
						WHERE COD_IMPRESORA_DTE = $cod_impresora_dte";
			$result = $db->build_results($sql);
			$EMISOR_GD = $result[0]['NOM_USUARIO'] ;
			}
			$db->BEGIN_TRANSACTION();
			$sp = 'spu_guia_despacho';
			$param = "'ENVIA_DTE', $cod_guia_despacho, $cod_usuario_impresion";
				
			if ($db->EXECUTE_SP($sp, $param)) {
				$db->COMMIT_TRANSACTION();		
	
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		   		$sql_dte= "SELECT GD.COD_GUIA_DESPACHO ,
										GD.NRO_GUIA_DESPACHO,
										dbo.f_format_date(FECHA_GUIA_DESPACHO,1)FECHA_GUIA_DES,
										case E.IMPRIMIR_EMP_MAS_SUC
											when 'S' then GD.NOM_EMPRESA +' - '+ GD.NOM_SUCURSAL 
										else GD.NOM_EMPRESA
										end NOM_EMPRESA,
										GD.COD_FACTURA,
										GD.DIRECCION,						
										GD.RUT,
										GD.GIRO,
										GD.DIG_VERIF,
										dbo.f_get_direccion_print_gd (GD.COD_EMPRESA, '[DIRECCION]') DIRECCION_FA,
										dbo.f_get_direccion_print_gd (GD.COD_EMPRESA, '[NOM_COMUNA]') NOM_COMUNA,
										dbo.f_get_direccion_print_gd (GD.COD_EMPRESA, '[NOM_CIUDAD]') NOM_CIUDAD,
										GD.TELEFONO,
										GD.FAX,
										NRO_ORDEN_COMPRA,
										NOM_PERSONA,
										dbo.f_emp_get_mail_cargo_persona(COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA,
										REFERENCIA,
										dbo.f_gd_nros_factura(GD.COD_GUIA_DESPACHO) NRO_FACTURA,
										GD.RETIRADO_POR,
										GD.RUT_RETIRADO_POR,
										GD.DIG_VERIF_RETIRADO_POR,
										GD.GUIA_TRANSPORTE,
										GD.PATENTE,
										ITEM,
										CANTIDAD,
										NOM_PRODUCTO,
										COD_PRODUCTO,
										PRECIO,
										PRECIO * CANTIDAD TOTAL_GD,
										OBS,
										RETIRADO_POR,
										RUT_RETIRADO_POR,
										DIG_VERIF_RETIRADO_POR,
										GUIA_TRANSPORTE,
										PATENTE,
										COD_DOC,
										IGD.ORDEN,
										convert(varchar(5), GETDATE(), 8) HORA,
										'$EMISOR_GD' NOM_USUARIO
										,(SELECT COD_SII 
										  FROM INDICADOR_TIPO_TRASLADO
										  WHERE COD_INDICADOR_TIPO_TRASLADO	= GD.COD_INDICADOR_TIPO_TRASLADO) COD_INDICADOR_TIPO_TRASLADO
								FROM	GUIA_DESPACHO GD ,ITEM_GUIA_DESPACHO IGD, EMPRESA E, USUARIO U
								WHERE	GD.COD_GUIA_DESPACHO = $cod_guia_despacho
								AND		GD.COD_USUARIO = U.COD_USUARIO
								AND		IGD.COD_GUIA_DESPACHO = GD.COD_GUIA_DESPACHO
								AND		E.COD_EMPRESA = GD.COD_EMPRESA";
				$result_dte = $db->build_results($sql_dte);
				//CANTIDAD DE ITEM_GUIA_DESPACHO 
				$count = count($result_dte);   	
	
				// datos de Guia Despacho
				$NRO_GUIA_DESPACHO	= $result_dte[0]['NRO_GUIA_DESPACHO'] ;	// 1 Numero Guia Despacho
				$FECHA_GUIA_DES		= $result_dte[0]['FECHA_GUIA_DES'] ;	// 2 Fecha Guia Despacho
				//Email - VE: =>En el caso de las Guia Despacho y otros documentos, no aplica por lo que se dejan 0;0 
				$TD					= $this->llena_cero;					// 3 Tipo Despacho
				//Email - VE: =>
				
				if($result_dte[0]['COD_INDICADOR_TIPO_TRASLADO'] == NULL)
					$TT				= $this->llena_cero;								// 4 Tipo Traslado
				else
					$TT				= $result_dte[0]['COD_INDICADOR_TIPO_TRASLADO'];	// 4 Tipo Traslado
				
				$PAGO_DTE			= $this->vacio;							// 5 Forma de Pago
				$FV					= $this->vacio;							// 6 Fecha Vencimiento
				$RUT				= $result_dte[0]['RUT'];				
				$DIG_VERIF			= $result_dte[0]['DIG_VERIF'];
				$RUT_EMPRESA		= $RUT.'-'.$DIG_VERIF;					// 7 Rut Empresa
				$NOM_EMPRESA		= $result_dte[0]['NOM_EMPRESA'] ;		// 8 Razol Social_Nombre Empresa
				$GIRO				= $result_dte[0]['GIRO'];				// 9 Giro Empresa
				$DIRECCION			= $result_dte[0]['DIRECCION'];			//10 Direccion empresa
				$MAIL_CARGO_PERSONA	= $result_dte[0]['MAIL_CARGO_PERSONA'];	//11 E-Mail Contacto
				$TELEFONO			= $result_dte[0]['TELEFONO'];			//12 Telefono Empresa
				$REFERENCIA			= $result_dte[0]['REFERENCIA'];			//12 Referencia de la Guia Despacho  //datos olvidado por VE.
				$NRO_GD				= $this->vacio;							//NUMERO DE GUIA DESPACHO PARA FACTURA
				$GENERA_SALIDA		= $this->vacio;							//Solicitado a VE por SP "DESPACHADO"
				$CANCELADA			= $this->vacio;							//Solicitado a VE por SP "CANCELADO"
				$SUBTOTAL			= $this->vacio;							//Solicitado a VE por SP "SUBTOTAL"
				$PORC_DSCTO1		= $this->vacio;							//Solicitado a VE por SP "PORC_DSCTO1"
				$PORC_DSCTO2		= $this->vacio;							//Solicitado a VE por SP "PORC_DSCTO2"
				$EMISOR_GD			= $result_dte[0]['NOM_USUARIO'];		//Solicitado a VE por SP "EMISOR_GD"
				$NOM_COMUNA			= $result_dte[0]['NOM_COMUNA'];			//13 Comuna Recepcion
				$NOM_CIUDAD			= $result_dte[0]['NOM_CIUDAD'];			//14 Ciudad Recepcion
				$DP					= $result_dte[0]['DIRECCION'];			//15 Direcci�n Postal
				$COP				= $result_dte[0]['NOM_COMUNA'];			//16 Comuna Postal
				$CIP				= $result_dte[0]['NOM_CIUDAD'];			//17 Ciudad Postal
				
				//DATOS DE TOTALES 
				$TOTAL_NETO			= $this->vacio;							//18 Monto Neto
				$PORC_IVA			= $this->vacio;							//19 Tasa IVA
				$MONTO_IVA			= $this->vacio;							//20 Monto IVA
				$TOTAL_CON_IVA		= $this->vacio;							//21 Monto Total
				$D1					= 'D';															//22 Tipo de Mov 1 (Desc/Rec)
				$P1					= '$';															//23 Tipo de valor de Desc/Rec 1
				$MONTO_DSCTO1		= $this->vacio;							//24 Valor del Desc/Rec 1
				$D2					= 'D';															//25 Tipo de Mov 2 (Desc/Rec)
				$P2					= '$';															//26 Tipo de valor de Desc/Rec 2
				$MONTO_DSCTO2		= $this->vacio;							//27 Valor del Desc/Rec 2
				$D3					= 'D';															//28 Tipo de Mov 3 (Desc/Rec)
				$P3					= '$';									//29 Tipo de valor de Desc/Rec 3
				$MONTO_DSCTO3		= $this->vacio;							//30 Valor del Desc/Rec 3
				$NOM_FORMA_PAGO		= $this->vacio;							//Dato Especial forma de pago adicional
				$NRO_ORDEN_COMPRA	= $result_dte[0]['NRO_ORDEN_COMPRA'];	//Numero de Orden Pago
				$NRO_NOTA_VENTA		= $result_dte[0]['COD_DOC'];			//Solicitado a VE por SP
				$OBSERVACIONES		= $result_dte[0]['OBS'];				//si la Guia Despacho tiene notas u observaciones
				$OBSERVACIONES		= eregi_replace("[\n|\r|\n\r]", ' ', $OBSERVACIONES); //elimina los saltos de linea. entre otros caracteres
				$TOTAL_EN_PALABRAS	= $this->vacio;							//Total en palabras: Posterior al campo Notas
				$ATENCION			= $result_dte[0]['NOM_PERSONA'];		// Nombre de Atencion
				$NRO_FACTURA		= $result_dte[0]['NRO_FACTURA'];		// Nro de FActura
				$RETIRA_RECINTO		= $result_dte[0]['RETIRADO_POR'];		// Persona que Retira de Recinto
				$RECINTO			= $this->vacio;							// Recinto
				$PATENTE			= $result_dte[0]['PATENTE'];			// Patente de Vehiculo que retira
				$RUT_RETIRADO_POR	= $result_dte[0]['RUT_RETIRADO_POR'];				
				$DIG_VERIF_RETIRADO_POR	= $result_dte[0]['DIG_VERIF_RETIRADO_POR'];
				if($RUT_RETIRADO_POR == ''){
					$RUT_RETIRA = '';
				}else{
					$RUT_RETIRA		= $RUT_RETIRADO_POR.'-'.$DIG_VERIF_RETIRADO_POR; // 27 Rut quien Retira
				}
				$FECHA_HORA_RETIRO	= $this->vacio;							// 28 Fecha y Hora de retiro del recinto
				
				//GENERA EL NOMBRE DEL ARCHIVO
				$TIPO_FACT = 52;	//GUIA DESPACHO
	
				//GENERA EL ALFANUMERICO ALETORIO Y LLENA LA VARIABLE $RES = ALETORIO
				$length = 36;
				$source = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$source .= '1234567890';
				
				if($length>0){
			        $RES = "";
			        $source = str_split($source,1);
			        for($i=1; $i<=$length; $i++){
			            mt_srand((double)microtime() * 1000000);
			            $num	= mt_rand(1,count($source));
			            $RES	.= $source[$num-1];
			        }
				 
			    }			
				
				//GENERA ESPACIOS EN BLANCO
				function space($string, $cant_espacio=NULL){
					if($cant_espacio <> NULL)
						$spacio = $cant_espacio;
					else
						$spacio = 49;
					$string .= str_repeat(" ", $spacio); 
					$string = substr($string, 0, $spacio); 
					if($cant_espacio == NULL)
						$string .= ': ';
					return $string;
				}
				
				//GENERA ESPACIOS CON CEROS
				$llena_cero = 0;
				$i = 0; 
				while($i<=100){
					$llena_cero .= 0;
				$i++;
				}
				
				//Asignando espacios en blanco Guia Despacho
				//LINEA 3
				$NRO_GUIA_DESPACHO	= substr($NRO_GUIA_DESPACHO.$space, 0, 10);		// 1 Numero Guia Despacho
				$FECHA_GUIA_DES	= substr($FECHA_GUIA_DES.$space, 0, 10);		// 2 Fecha Guia Despacho
				$TD				= substr($TD.$space, 0, 1);					// 3 Tipo Despacho
				$TT				= substr($TT.$space, 0, 1);					// 4 Tipo Traslado
				$PAGO_DTE		= substr($PAGO_DTE.$space, 0, 1);			// 5 Forma de Pago
				$FV				= substr($FV.$space, 0, 10);				// 6 Fecha Vencimiento
				$RUT_EMPRESA	= substr($RUT_EMPRESA.$space, 0, 10);		// 7 Rut Empresa
				$NOM_EMPRESA	= substr($NOM_EMPRESA.$space, 0, 100);		// 8 Razol Social_Nombre Empresa
				$GIRO			= substr($GIRO.$space, 0, 40);				// 9 Giro Empresa
				$DIRECCION		= substr($DIRECCION.$space, 0, 60);			//10 Direccion empresa
				$MAIL_CARGO_PERSONA = substr($MAIL_CARGO_PERSONA.$space, 0, 60);//11 E-Mail Contacto
				$TELEFONO		= substr($TELEFONO.$space, 0, 15);			//12 Telefono Empresa
				$REFERENCIA		= substr($REFERENCIA.$space, 0, 80);
				$NRO_GD			= substr($NRO_GD.$space, 0, 20);			//Solicitado a VE por SP
				$GENERA_SALIDA	= substr($GENERA_SALIDA.$space, 0, 30);		//DESPACHADO
				$CANCELADA		= substr($CANCELADA.$space, 0, 30);			//CANCELADO
				$SUBTOTAL		= substr($SUBTOTAL.$space, 0, 18);			//Solicitado a VE por SP "SUBTOTAL"
				$PORC_DSCTO1	= substr($PORC_DSCTO1.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO1"
				$PORC_DSCTO2	= substr($PORC_DSCTO2.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO2"
				$EMISOR_GD		= substr($EMISOR_GD.$space, 0, 50);			//Solicitado a VE por SP "EMISOR_GUIA_DESPACHO"
				//LINEA4
				$NOM_COMUNA		= substr($NOM_COMUNA.$space, 0, 20);		//13 Comuna Recepcion
				$NOM_CIUDAD		= substr($NOM_CIUDAD.$space, 0, 20);		//14 Ciudad Recepcion
				$DP				= substr($DP.$space, 0, 60);				//15 Direcci�n Postal
				$COP			= substr($COP.$space, 0, 20);				//16 Comuna Postal
				$CIP			= substr($CIP.$space, 0, 20);				//17 Ciudad Postal
	
				//Asignando espacios en blanco Totales de Guia Despacho
				$TOTAL_NETO		= substr($TOTAL_NETO.$space, 0, 18);		//18 Monto Neto
				$PORC_IVA		= substr($PORC_IVA.$space, 0, 5);			//19 Tasa IVA
				$MONTO_IVA		= substr($MONTO_IVA.$space, 0, 18);			//20 Monto IVA
				$TOTAL_CON_IVA	= substr($TOTAL_CON_IVA.$space, 0, 18);		//21 Monto Total
				$D1				= substr($D1.$space, 0, 1);					//22 Tipo de Mov 1 (Desc/Rec)
				$P1				= substr($P1.$space, 0, 1);					//23 Tipo de valor de Desc/Rec 1
				$MONTO_DSCTO1	= substr($MONTO_DSCTO1.$space, 0, 18);		//24 Valor del Desc/Rec 1
				$D2				= substr($D2.$space, 0, 1);					//25 Tipo de Mov 2 (Desc/Rec)
				$P2				= substr($P2.$space, 0, 1);					//26 Tipo de valor de Desc/Rec 2
				$MONTO_DSCTO2	= substr($MONTO_DSCTO2.$space, 0, 18);		//27 Valor del Desc/Rec 2
				$D3				= substr($D3.$space, 0, 1);					//28 Tipo de Mov 3 (Desc/Rec)
				$P3				= substr($P3.$space, 0, 1);					//29 Tipo de valor de Desc/Rec 3
				$MONTO_DSCTO3	= substr($MONTO_DSCTO3.$space, 0, 18);		//30 Valor del Desc/Rec 3
				$NOM_FORMA_PAGO = substr($NOM_FORMA_PAGO.$space, 0, 80);	//Dato Especial forma de pago adicional
				$NRO_ORDEN_COMPRA= substr($NRO_ORDEN_COMPRA.$space, 0, 20);	//Numero de Orden Pago
				$NRO_NOTA_VENTA = substr($NRO_NOTA_VENTA.$space, 0, 20);	//Numero de Nota Venta
				$OBSERVACIONES = substr($OBSERVACIONES.$space.$space.$space, 0, 250); //si la Guia Despacho tiene notas u observaciones
				
				$ATENCION		= substr($ATENCION.$space, 0, 30);			// Atencion a persona del cliente
				$NRO_FACTURA	= substr($NRO_FACTURA.$space, 0, 10);		// Numero de Factura
				$RETIRA_RECINTO	= substr($RETIRA_RECINTO.$space, 0, 30);	// Persona que Retira de Recinto
				$RECINTO		= substr($RECINTO.$space, 0, 30);			// Recinto
				$PATENTE		= substr($PATENTE.$space, 0, 30);			// Patente Vehiculo que retira
				$RUT_RETIRA		= substr($RUT_RETIRA.$space, 0, 18);		// Rut quien retira
				$FECHA_HORA_RETIRO = substr($FECHA_HORA_RETIRO.$space, 0, 20); // Fecha y hora de retiro del Recinto
							
				$name_archivo = $TIPO_FACT."_NPG_".$RES.".SPF";
				$fname = tempnam("/tmp", $name_archivo);
				$handle = fopen($fname,"w");
				//DATOS DE GUIA_DESPACHO A EXPORTAR 
				fwrite($handle, 'XXX INICIO DOCUMENTO');
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA IDENTIFICACION DEL DOCUMENTO');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Documento Tributario Electronico'));
				fwrite($handle, space($TIPO_FACT, 3)); 
				fwrite($handle, "\r\n");
				fwrite($handle, space('Folio Documento'));
				fwrite($handle, space($NRO_GUIA_DESPACHO, 10));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Emisi�n'));
				fwrite($handle, space($FECHA_GUIA_DES, 10)); 
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador No rebaja'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo de Despacho')); //Ver como implementar con MH
				fwrite($handle, $TD);
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador de Traslado '));//Ver como implementar con MH
				fwrite($handle, $TT);
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Impresi�n'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador de Servicio'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador de Montos Brutos'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador de Montos Netos'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Forma de Pago'));
				fwrite($handle, $PAGO_DTE);
				fwrite($handle, "\r\n");
				fwrite($handle, space('Forma de Pago Exportaci�n'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Cancelaci�n'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Cancelado'));
				fwrite($handle, space($TOTAL_CON_IVA, 18));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Saldo Insoluto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Periodo Desde'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Periodo Hasta'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Medio de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo de Cuenta de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Numero de Cuenta de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Banco de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Terminos de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Glosa del Termino de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('D�as del Termino de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha Vencimiento'));
				fwrite($handle, space($FV, 10));
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA EMISOR');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Rut Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Raz�n Social Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Giro del Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Telefono'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Correo Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('ACTECO'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Emisor Traslado Excepcional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Folio Autorizacion'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha Autorizacion'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Direcci�n de Origen Emisor'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Comuna de Origen Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Ciudad de Origen Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre Sucursal'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Sucursal'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Adicional Sucursal'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Vendedor'));//Revisar con MH
				fwrite($handle, space($EMISOR_GD, 60));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Identificador Adicional del Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('RUT Mandante'));//
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA RECEPTOR');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Rut Receptor'));
				fwrite($handle, space($RUT_EMPRESA, 10));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Interno Receptor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre o Raz�n Social Receptor'));
				fwrite($handle, space($NOM_EMPRESA, 100));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Numero Identificador Receptor Extranjero'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nacionalidad del Receptor Extranjero'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Identificador Adicional Receptor Extranjero'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Giro del negocio del Receptor'));
				fwrite($handle, space($GIRO, 40));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Contacto'));
				fwrite($handle, space($TELEFONO, 80));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Correo Receptor'));
				fwrite($handle, space($MAIL_CARGO_PERSONA, 80));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Direcci�n Receptor'));
				fwrite($handle, space($DIRECCION, 70));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Comuna Receptor'));
				fwrite($handle, space($NOM_COMUNA, 20));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Ciudad Receptor'));
				fwrite($handle, space($NOM_CIUDAD, 20));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Direcci�n Postal Receptor'));
				fwrite($handle, space($DP, 70));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Comuna Postal Receptor'));
				fwrite($handle, space($COP, 20));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Ciudad Postal Receptor'));
				fwrite($handle, space($CIP, 20));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Rut Solicitante de Factura'));//
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA TRANSPORTES');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Patente'));
				fwrite($handle, space($PATENTE, 8));
				fwrite($handle, "\r\n");
				fwrite($handle, space('RUT Transportista'));
				fwrite($handle, space($RUT_RETIRA, 10));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Rut Chofer'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre del Chofer'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Direcci�n Destino'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Comuna Destino'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Ciudad Destino'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Modalidad De Ventas'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Clausula de Venta Exportaci�n'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Total Clausula de Venta Exportacion'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Via de Transporte'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre del Medio de Transporte'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('RUT Compa��a de Transporte'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre Compa��a de Transporte'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Identificacion Adicional Compa��a de Transporte'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Booking'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Operador'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Puerto de Embarque'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Identificador Adicional Puerto de Embarque'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Puerto Desembarque'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Identificador Adicional Puerto de Desembarque'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tara'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Unidad de Medida Tara'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Total Peso Bruto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Unidad de Peso Bruto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Total Peso Neto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Unidad de Peso Neto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Total Items'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Total Bultos'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Tipo de Bulto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Tipo de Bulto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Tipo de Bulto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Tipo de Bulto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Flete'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Seguro'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Pais Receptor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Pais Destino'));//
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA TOTALES');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Moneda Transacci�n'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Neto'));
				fwrite($handle, space($TOTAL_NETO, 18));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Exento'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Base Faenamiento de Carne'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Base de Margen de  Comercializaci�n'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tasa IVA'));
				fwrite($handle, space($PORC_IVA, 5));
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA'));
				fwrite($handle, space($MONTO_IVA, 18));
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA Propio'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA Terceros'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA no Retenido'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Cr�dito Especial Emp. Constructoras'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Garant�a por Deposito de Envases'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Valor Neto Comisiones'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Valor Exento Comisiones'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA Comisiones'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Total'));
				fwrite($handle, space($TOTAL_CON_IVA, 18));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto No Facturable'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Periodo'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Saldo Anterior'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Valor a Pagar'));//
				fwrite($handle, "\r\n");
				fwrite($handle, '========== OTRA MONEDA');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Cambio'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Neto Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Exento Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Base Faenamiento de Carne Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Margen Comerc. Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Imp. Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tasa Imp. Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Valor Imp. Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA No Retenido Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Total Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, '========== DETALLE DE PRODUCTOS Y SERVICIOS');
				fwrite($handle, "\r\n");
				$j = 0;
				while($j < 30){
					if($j == 0){
						for($i=0 ; $i < $count ; $i++){
							$MODELO		= $result_dte[$i]['COD_PRODUCTO'];
							fwrite($handle, ' '); //Indicador Exenci�n
							fwrite($handle, space('', 3));	//Tipo Doc. Liquidaci�n
							fwrite($handle, space('', 10));	//Tipo Codigo (Mineras)
							fwrite($handle, space('', 35));	//Codigo Item (Mineras)
							fwrite($handle, space('', 10));	//Tipo Codigo
							fwrite($handle, space($MODELO, 35));	//Codigo Item
							fwrite($handle, space('', 10));	//Tipo Codigo
							fwrite($handle, space('', 35));	//Codigo Item
							fwrite($handle, space('', 2));	//Item Espectaculo
							fwrite($handle, space('', 10));	//RUT Mandante
							fwrite($handle, space('', 6));	//Folio Ticket
							fwrite($handle, space('', 10));	//Fecha Ticktet
							fwrite($handle, space('', 80));	//Nombre Evento
							fwrite($handle, space('', 10));	//Tipo Ticket
							fwrite($handle, space('', 5));	//Codigo Evento
							fwrite($handle, space('', 16));	//Fecha y Hora Evento
							fwrite($handle, space('', 80));	//Lugar del Evento
							fwrite($handle, space('', 20));	//Ubicaci�n del Evento
							fwrite($handle, space('', 3));	//Fila Ubicaci�n
							fwrite($handle, space('', 3));	//Asiento Ubicaci�n
							fwrite($handle, ' '); 			//Indicador Agente Retenedor
							fwrite($handle, space('', 18));	//Monto Base Faenamiento
							fwrite($handle, space('', 18));	//Monto Base Margenes
							fwrite($handle, space('', 18));	//Precio Unitario Neto Consumidor Final
							
							$NOM_PRODUCTO = $result_dte[$i]['NOM_PRODUCTO'];
							fwrite($handle, space($NOM_PRODUCTO, 80));
							fwrite($handle, space('', 1000));	
							fwrite($handle, space('', 18));	//Cantidad de Referencia
							fwrite($handle, space('', 4));	//Unidad de Referencia
							fwrite($handle, space('', 18));	//Precio de Referencia
							
							$CANTIDAD	= number_format($result_dte[$i]['CANTIDAD'], 6, '.', '');
							fwrite($handle, space($CANTIDAD, 18));
							fwrite($handle, space('', 18));	//Sub Cantidad Distribuida
							fwrite($handle, space('', 35));	//Codigo Sub Cantidad 
							fwrite($handle, space('', 10));	//Tipo Codigo Subcantidad
							fwrite($handle, space('', 18));	//Sub Cantidad Distribuida
							fwrite($handle, space('', 35));	//Codigo Sub Cantidad 
							fwrite($handle, space('', 10));	//Tipo Codigo Subcantidad
							fwrite($handle, space('', 18));	//Sub Cantidad Distribuida
							fwrite($handle, space('', 35));	//Codigo Sub Cantidad 
							fwrite($handle, space('', 10));	//Tipo Codigo Subcantidad
							fwrite($handle, space('', 18));	//Sub Cantidad Distribuida
							fwrite($handle, space('', 35));	//Codigo Sub Cantidad 
							fwrite($handle, space('', 10));	//Tipo Codigo Subcantidad
							fwrite($handle, space('', 18));	//Sub Cantidad Distribuida
							fwrite($handle, space('', 35));	//Codigo Sub Cantidad 
							fwrite($handle, space('', 10));	//Tipo Codigo Subcantidad
							
							$P_UNITARIO	= number_format($result_dte[$i]['PRECIO'], 6, '.', '');
							fwrite($handle, space($P_UNITARIO, 18));
							fwrite($handle, space('', 10));	//Fecha Elaboraci�n Item
							fwrite($handle, space('', 10));	//Fecha de Vencimiento 
							fwrite($handle, space('', 4));	//Unidad de Medida
							fwrite($handle, space('', 18));	//Precio Otra Moneda
							fwrite($handle, space('', 3));	//Moneda
							fwrite($handle, space('', 10));	//Factor de Conversi�n 
							fwrite($handle, space('', 18));	//Descuento Otra Moneda
							fwrite($handle, space('', 18));	//Recargo Otra Moneda
							fwrite($handle, space('', 18));	//Valor Item Otra Moneda
							fwrite($handle, space('', 18));	//Precio Otra Moneda
							fwrite($handle, space('', 3));	//Moneda
							fwrite($handle, space('', 10));	//Factor de Conversi�n 
							fwrite($handle, space('', 18));	//Descuento Otra Moneda
							fwrite($handle, space('', 18));	//Recargo Otra Moneda
							fwrite($handle, space('', 18));	//Valor Item Otra Moneda
							fwrite($handle, space('', 5));	//Porcentaje de Descuento
							fwrite($handle, space('', 18));	//Monto de Descuento
							fwrite($handle, ' ');	//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');	//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');	//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');	//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');	//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, space('', 5));	//Porcentaje de Recargo
							fwrite($handle, space('', 18));	//Monto de Recargo
							fwrite($handle, ' ');	//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo
							fwrite($handle, ' ');	//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo 
							fwrite($handle, ' ');	//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo
							fwrite($handle, ' ');	//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo
							fwrite($handle, ' ');	//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo 
							fwrite($handle, space('', 6));	//Codigo Impuesto Adicional
							fwrite($handle, space('', 6));	//Codigo Impuesto Adicional
							
							$TOTAL		= number_format($TOTAL, 6, '.', '');
							fwrite($handle, space($TOTAL, 18));
							fwrite($handle, space($MODELO, 30));	//Campo Personalizado Detalle
							fwrite($handle, space($CANTIDAD, 30));	//Campo Personalizado Detalle
							fwrite($handle, space($ORDEN, 30));	//Campo Personalizado Detalle
							fwrite($handle, space('', 30));	//Campo Personalizado Detalle
							fwrite($handle, space('', 30));	//Campo Personalizado Detalle
							fwrite($handle, space('', 30));	//Campo Personalizado Detalle
							fwrite($handle, space('', 5000));	//Descripci�n Extendida
							
							fwrite($handle, "\r\n");
							$j++;
						}
					}	
					
					fwrite($handle, "\r\n");
					$j++;
				}
				fwrite($handle, '========== FIN DETALLE');
				fwrite($handle, "\r\n");
				fwrite($handle, '========== SUB TOTALES INFORMATIVO');
				fwrite($handle, "\r\n");
				$j = 0;
				while($j < 20){
					fwrite($handle, space('', 2)); //Numero Sub Total Informativo
					fwrite($handle, space('', 40)); //Glosa
					fwrite($handle, space('', 2)); //Orden
					fwrite($handle, space('', 18)); //Subtotal Neto
					fwrite($handle, space('', 18)); //Subtotal IVA
					fwrite($handle, space('', 18)); //Subtotal Imp. Adidcional
					fwrite($handle, space('', 18)); //Subtotal Exento
					fwrite($handle, space('', 18)); //Valor Subtotal
					fwrite($handle, space('', 2)); //Lineas
					fwrite($handle, "\r\n");
					$j++;
				}
				fwrite($handle, '========== DESCUENTOS Y RECARGOS');
				fwrite($handle, "\r\n");
				// Subtotal
				fwrite($handle, $D1);
				fwrite($handle, space("SUBTOTAL", 45));
				fwrite($handle, $P1);
				fwrite($handle, space($SUBTOTAL, 18));
				fwrite($handle, " ");
				fwrite($handle, "\r\n");
				// Descuento 1
				fwrite($handle, $D2);
				fwrite($handle, space($PORC_DSCTO1."% "."DESCUENTO", 45));
				fwrite($handle, $P2);
				fwrite($handle, space($MONTO_DSCTO1, 18));
				fwrite($handle, " ");
				fwrite($handle, "\r\n");
				// Descuento 2
				fwrite($handle, $D3);
				fwrite($handle, space($PORC_DSCTO2."% "."DESCUENTO ADIC.", 45));
				fwrite($handle, $P3);
				fwrite($handle, space($MONTO_DSCTO2, 18));
				fwrite($handle, " ");
				$j = 0;
				while($j < 18){
					fwrite($handle, "\r\n");
					$j++;
				}
				fwrite($handle, '========== INFORMACION DE REFERENCIA');//Hablar este punto con MH
				fwrite($handle, "\r\n");
				$j = 0;
				while($j < 40){
					fwrite($handle, "\r\n");
					$j++;
				}
				fwrite($handle, '========== COMISIONES Y OTROS CARGOS');
				fwrite($handle, "\r\n");
				$j = 0;
				while($j < 20){
					fwrite($handle, "\r\n");
					$j++;
				}
				fwrite($handle, '========== CAMPOS PERSONALIZADOS');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Palabras'));
				fwrite($handle, space($total_en_palabras, 200));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($NOM_FORMA_PAGO, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($CANCELADA, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($GENERA_SALIDA, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($OBSERVACIONES, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($NRO_NOTA_VENTA, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($RUT_RETIRA, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($PATENTE, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($RETIRA_RECINTO, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($REFERENCIA, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, 'XXX FIN DOCUMENTO');		
				

				$upload = $this->Envia_DTE($name_archivo, $fname);
				$NRO_GUIA_DESPACHO = trim($NRO_GUIA_DESPACHO);
				if (!$upload) {
					$this->_load_record();
					$this->alert('No se pudo enviar Guia Despacho Electronica N� '.$NRO_GUIA_DESPACHO.', Por favor contacte a IntegraSystem.');								
				}else{
					$this->_load_record();
					$this->alert('Gesti�n Realizada con ex�to. Guia Despacho Electronica N� '.$NRO_GUIA_DESPACHO.'.');								
				}
				unlink($fname);
			}else{
				$db->ROLLBACK_TRANSACTION();
				return false;
			}			
			$this->unlock_record();
   		}
   		
		function procesa_event() {		
			if(isset($_POST['b_save_x'])) {
				if (isset($_POST['b_save'])) $this->current_tab_page = $_POST['b_save'];
				if ($this->_save_record()) {
					if ($_POST['wi_hidden']=='save_desde_print')		// Si el save es gatillado desde el boton print, se fuerza que se ejecute nuevamente el print
						print '<script type="text/javascript"> document.getElementById(\'b_print\').click(); </script>';
					elseif ($_POST['wi_hidden']=='save_desde_dte')		// Es es el codigo NUEVO
						print '<script type="text/javascript"> document.getElementById(\'b_print_dte\').click(); </script>';
				}
			}
			else if(isset($_POST['b_print_dte_x'])) {
				$this->envia_GD_Electronica();
			}
			else
				parent::procesa_event();
		}
}
class print_guia_despacho extends reporte {	
	const	K_GD_ARRIENDO = 5;
	
	function print_guia_despacho($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	function modifica_pdf(&$pdf) {
		$pdf->AutoPageBreak=false;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		$count = count($result);
		$fecha = $result[0]['FECHA_GUIA_DES'];	
		$nro_guia_despacho = $result[0]['NRO_GUIA_DESPACHO'];	
		// CABECERA		
		$cod_factura = $result[0]['COD_GUIA_DESPACHO'];
		$nom_empresa = $result[0]['NOM_EMPRESA'];
		$rut = number_format($result[0]['RUT'], 0, ',', '.')."-".$result[0]['DIG_VERIF'];
		$oc = $result[0]['NRO_ORDEN_COMPRA'];
		$direccion = $result[0]['DIRECCION_FA'];
		$comuna = $result[0]['NOM_COMUNA'];		
		$ciudad = $result[0]['NOM_CIUDAD'];	
		$atencion = "AT. ".$result[0]['NOM_PERSONA'];	
		$referencia = "REF. ".$result[0]['REFERENCIA'];
		$nota_venta = $result[0]['COD_DOC'];
		$titulo_obs = "NOTAS: ";
		$obs = $result[0]['OBS'];
		$retira = $result[0]['RETIRADO_POR'];  
		$INI_USUARIO	= $result[0]['INI_USUARIO'];
		$retira_rut = number_format($result[0]['RUT_RETIRADO_POR'], 0, ',', '.');
		if ($retira_rut == 0) {
			$retira_rut = '';
		}else {
			$retira_rut = number_format($result[0]['RUT_RETIRADO_POR'], 0, ',', '.')."-".$result[0]['DIG_VERIF_RETIRADO_POR'];
		}
		$retira_recinto = "RECINTO";
		$patente = "PATENTE: ".$result[0]['PATENTE'];
		$hora = $result[0]['HORA'];
		
		// DIBUJANDO LA CABECERA	
		// TAMA�O DE LA CABECERA SetFont
		$pdf->SetFont('Arial','',11);
		$pdf->Text(50, 146,$fecha);		
		$pdf->Text(417, 110, $nro_guia_despacho);
		$pdf->SetFont('Arial','',8);
		$pdf->Text(515, 110, $INI_USUARIO);
		$pdf->SetFont('Arial','',11);
		$pdf->SetRightMargin(200);
		$pdf->SetLeftMargin(85);
		$pdf->SetXY(45, 160);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(250, 15,"$nom_empresa");
		$pdf->Text(427, 190, $rut);
		$pdf->SetFont('Arial','',11);
		$pdf->Text(427, 237, $oc);	
		$pdf->Text(45, 217, $direccion);
		//$pdf->Text(427, 213, $fono);
		$pdf->Text(45, 237, $comuna);
		$pdf->Text(232, 237, $ciudad);
		$pdf->Text(87, 260, $atencion);
		$pdf->Text(87, 270, $referencia);
		$pdf->SetFont('Arial','',13);
		if ($result[0]['COD_TIPO_GUIA_DESPACHO']==self::K_GD_ARRIENDO) 
			$pdf->Text(25, 740, "MOD_ARRIENDO: $nota_venta");
		else
			$pdf->Text(25, 740, $nota_venta);
			$pdf->SetFont('Arial','',9);
			$pdf->Text(97, 635, $titulo_obs);	
			$pdf->SetXY(97, 636);
			$pdf->MultiCell(636, 10, $obs);
			$pdf->Text(192, 712, 'RETIRA: ');
			$pdf->SetXY(227, 705);
			$pdf->MultiCell(130,8, $retira,0, 'L');
			$pdf->Text(367, 712, $retira_rut);
			$pdf->Text(192, 730, $retira_recinto);
			$pdf->Text(357, 730, $patente);
			$pdf->Text(485, 730, $hora);
			$pdf->SetFont('Arial','',9);		
		//DIBUJANDO LOS ITEMS DE LA FACTURA		
		for($i=0; $i<$count; $i++){
			$item = $result[$i]['ITEM'];
			$cantidad = number_format($result[$i]['CANTIDAD'], 1, ',', ',');
			$modelo = $result[$i]['COD_PRODUCTO'];
			$detalle = substr ($result[$i]['NOM_PRODUCTO'], 0, 60);
			$p_unitario = number_format($result[$i]['PRECIO'], 0, ',', '.');
			$total = number_format($result[$i]['TOTAL_GD'], 0, ',', '.');
			// por cada pasada le asigna una nueva posicion	
			$pdf->Text(30, 324+(15*$i), $item);			
			$pdf->Text(65, 324+(15*$i), $cantidad);
			// esto es para leer el detalle en dos filas
			$pdf->SetRightMargin(90);
			$pdf->SetLeftMargin(90);
			$pdf->SetXY(97,321+(15*$i));
			$pdf->Cell(300, 0, "$detalle");	
			$pdf->SetXY(435,318+(15*$i));
			$pdf->MultiCell(60,5, $modelo,0, 'L');
			$pdf->SetXY(485,318+(15*$i));
			$pdf->MultiCell(60,5, $p_unitario,0, 'R');
		}
	}
}
?>
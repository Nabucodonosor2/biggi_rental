<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

class dw_item_nota_credito extends datawindow {
	const K_ESTADO_SII_EMITIDA 			= 1;
	
	function dw_item_nota_credito() {
		$sql = "SELECT ITNC.COD_ITEM_NOTA_CREDITO,
						ITNC.COD_NOTA_CREDITO,
						ITNC.ORDEN,
						ITNC.ITEM,
						ITNC.COD_PRODUCTO,
						ITNC.COD_PRODUCTO COD_PRODUCTO_OLD,
						ITNC.NOM_PRODUCTO,
						ITNC.CANTIDAD,
						ITNC.PRECIO,
						ITNC.COD_ITEM_DOC,
						dbo.f_fa_cant_por_nc(ITNC.COD_ITEM_DOC, default) CANTIDAD_POR_NC,
						case
							when NC.COD_DOC IS not NULL and NC.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
							else 'none'
						end TD_DISPLAY_CANT_POR_NC,
						case
							when NC.COD_DOC IS NULL then ''
							else 'none'
						end TD_DISPLAY_ELIMINAR,
						NC.COD_DOC,
						COD_TIPO_TE,
						MOTIVO_TE,
						'' BOTON_PRECIO -- se utiliza en funcion comun js 'ingreso_TE'
				FROM    ITEM_NOTA_CREDITO ITNC, NOTA_CREDITO NC
				WHERE   NC.COD_NOTA_CREDITO = ITNC.COD_NOTA_CREDITO AND
					    ITNC.COD_NOTA_CREDITO = {KEY1}
				ORDER BY ORDEN";
		
		 
		parent::datawindow($sql, 'ITEM_NOTA_CREDITO', true, true);
		
		$this->add_control(new edit_text_upper('COD_ITEM_NOTA_CREDITO',10, 10, 'hidden'));
		$this->add_control(new edit_text_upper('COD_DOC',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text_upper('ITEM',4, 5));
		$this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		$control->set_onChange("this.value = valida_ct_x_nc(this);");
		$this->add_control(new edit_text('COD_TIPO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('MOTIVO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('BOTON_PRECIO',10, 10, 'hidden'));
		$this->add_control(new static_num('CANTIDAD_POR_NC',1));
		
		$this->add_control(new computed('PRECIO', 0));		
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL', "calc_dscto();");
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		// Agrega script adicional a COD_PRODUCTO 
		$this->controls['COD_PRODUCTO']->set_onChange("change_item_nota_credito(this, 'COD_PRODUCTO');");
		
		$this->set_first_focus('COD_PRODUCTO');


		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
	}
	
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ITEM', $this->row_count());
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		$this->set_item($row, 'TD_DISPLAY_CANT_POR_NC', 'none');
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
					$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_nc(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
				else
				{
					$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_nc(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
					$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
				}
				
			}
			else 
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line_d.jpg">';
			
		}
		
	}
	
	
	
	function update($db)	{
		$sp = 'spu_item_nota_credito';
		
		for ($i = 0; $i < $this->row_count(); $i++){
				$statuts = $this->get_status_row($i);
				if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
					continue;
					
					$COD_ITEM_NOTA_CREDITO		= $this->get_item($i, 'COD_ITEM_NOTA_CREDITO');
					$COD_NOTA_CREDITO 			= $this->get_item($i, 'COD_NOTA_CREDITO');
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
					$MOTIVO_TE		 		= ($MOTIVO_TE =='') ? "null" : "'".$MOTIVO_TE."'";

					if ($PRECIO=='') $PRECIO = 0;		
					$COD_ITEM_NOTA_CREDITO   = ($COD_ITEM_NOTA_CREDITO=='') ? "null" : $COD_ITEM_NOTA_CREDITO;
					$COD_ITEM_DOC = ($COD_ITEM_DOC=='') ? "null" : $COD_ITEM_DOC;
				
					if ($statuts == K_ROW_NEW_MODIFIED)
						$operacion = 'INSERT';
					else if ($statuts == K_ROW_MODIFIED)
						$operacion = 'UPDATE';		

					$param = "'$operacion', $COD_ITEM_NOTA_CREDITO, $COD_NOTA_CREDITO, $ORDEN, '$ITEM', '$COD_PRODUCTO', '$NOM_PRODUCTO', $CANTIDAD, $PRECIO, $COD_ITEM_DOC, $COD_TIPO_TE, $MOTIVO_TE";

					if (!$db->EXECUTE_SP($sp, $param)) 
						return false;
					else {
						if ($statuts == K_ROW_NEW_MODIFIED) {
							$COD_ITEM_NOTA_CREDITO = $db->GET_IDENTITY();
							$this->set_item($i, 'COD_ITEM_NOTA_CREDITO', $COD_ITEM_NOTA_CREDITO);		
						}
					}
				}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_ITEM_NOTA_CREDITO = $this->get_item($i, 'COD_ITEM_NOTA_CREDITO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_ITEM_NOTA_CREDITO"))
				return false;
		}	
		return true;
	}
}

		
class dw_nota_credito extends dw_help_empresa {
	const K_ESTADO_SII_EMITIDA 			= 1;	
	const K_ESTADO_SII_ANULADA			= 4;
	const K_PARAM_PORC_DSCTO_MAX 		= 26;

	function dw_nota_credito() {
		
		
		$sql = "SELECT	NC.COD_NOTA_CREDITO,
					NC.FECHA_REGISTRO,
					NC.COD_USUARIO,
					U.NOM_USUARIO,
					NC.NRO_NOTA_CREDITO,
					convert(varchar(20), NC.FECHA_NOTA_CREDITO, 103) FECHA_NOTA_CREDITO,
					convert(varchar(20), NC.FECHA_NOTA_CREDITO, 103) FECHA_NOTA_CREDITO_I,
					NC.COD_ESTADO_DOC_SII,
					EDS.NOM_ESTADO_DOC_SII,
					NC.COD_EMPRESA,
					NC.COD_SUCURSAL_FACTURA,
					dbo.f_get_direccion('NOTA_CREDITO', NC.COD_NOTA_CREDITO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA,
					NC.COD_PERSONA,
					dbo.f_emp_get_mail_cargo_persona(NC.COD_PERSONA,  '[EMAIL]') MAIL_CARGO_PERSONA,
					NC.REFERENCIA,
					NC.OBS,
					NC.COD_BODEGA,
					NC.COD_TIPO_NOTA_CREDITO,
					NC.COD_DOC COD_DOC_H,
					NC.SUBTOTAL SUM_TOTAL,
					NC.TOTAL_NETO,
					NC.INGRESO_USUARIO_DSCTO1,
					NC.MONTO_DSCTO1,
					NC.PORC_DSCTO1,
					NC.PORC_DSCTO2,
					NC.INGRESO_USUARIO_DSCTO2,
					NC.MONTO_DSCTO2,	
					NC.PORC_IVA,
					NC.MONTO_IVA,
					NC.TOTAL_CON_IVA,
					convert(varchar(20), NC.FECHA_ANULA, 103) +'  '+ convert(varchar(20), NC.FECHA_ANULA, 8) FECHA_ANULA,
					NC.MOTIVO_ANULA,
					NC.COD_USUARIO_ANULA, 			
					NC.RUT RUT_NC,
					NC.DIG_VERIF DIG_VERIF_NC,
					NC.NOM_EMPRESA NOM_EMPRESA_NC,
					NC.GIRO GIRO_NC,
					NC.NOM_SUCURSAL,
					E.ALIAS,
					NC.COD_CENTRO_COSTO,
					E.RUT,
					E.DIG_VERIF,
					E.NOM_EMPRESA,
					E.GIRO,
					NC.DIRECCION,
					NC.COD_CARGO,	
					NC.TELEFONO,
					NC.FAX,
					NC.NOM_PERSONA,
					NC.MAIL,
					NC.COD_CARGO,
					NC.COD_USUARIO_IMPRESION,
					case NC.COD_ESTADO_DOC_SII 
						when ".self::K_ESTADO_SII_ANULADA." then '' 
						else 'none'
					end TR_DISPLAY 	,
					'' VISIBLE_DTE,	
					case
						when NC.COD_DOC IS NULL then ''
						else 'none'
					end TD_DISPLAY_ELIMINAR,
					case
						when NC.COD_DOC IS not NULL and NC.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
						else 'none'
					end TD_DISPLAY_CANT_POR_NC,
					(select valor from parametro where cod_parametro=40 ) VALOR_NC_H,
					FA.NRO_FACTURA,
					NC.COD_MOTIVO_NOTA_CREDITO,
					NC.GENERA_ENTRADA,
					NC.COD_TIPO_NC_INTERNO_SII,
					MNC.NOM_MOTIVO_NOTA_CREDITO,
					TNC.NOM_TIPO_NOTA_CREDITO		
				FROM  NOTA_CREDITO NC LEFT OUTER JOIN FACTURA FA ON NC.COD_DOC = FA.COD_FACTURA
					 ,USUARIO U
					 ,EMPRESA E
					 ,ESTADO_DOC_SII EDS
					 ,MOTIVO_NOTA_CREDITO MNC
					 ,TIPO_NOTA_CREDITO TNC
				WHERE NC.COD_NOTA_CREDITO = {KEY1} AND
					  NC.COD_USUARIO = U.COD_USUARIO AND
					  E.COD_EMPRESA = NC.COD_EMPRESA AND
					  EDS.COD_ESTADO_DOC_SII = NC.COD_ESTADO_DOC_SII AND
					  NC.COD_MOTIVO_NOTA_CREDITO = MNC.COD_MOTIVO_NOTA_CREDITO AND
					  NC.COD_TIPO_NOTA_CREDITO	= TNC.COD_TIPO_NOTA_CREDITO";
		
		parent::dw_help_empresa($sql);
		
		$this->add_control(new edit_text_hidden('COD_NOTA_CREDITO'));
		
		$this->add_control(new edit_text_hidden('COD_MOTIVO_NOTA_CREDITO'));
		$this->add_control(new edit_text_hidden('COD_TIPO_NOTA_CREDITO'));
		$this->add_control(new static_text('NOM_MOTIVO_NOTA_CREDITO'));
		$this->add_control(new static_text('NOM_TIPO_NOTA_CREDITO'));
		
		$this->add_control(new edit_nro_doc('NRO_NOTA_CREDITO','NOTA_CREDITO'));
		
		$this->add_control(new static_text('FECHA_NOTA_CREDITO_I'));
		$this->add_control($control = new edit_date('FECHA_NOTA_CREDITO'));
		$control->set_onChange("change_fecha();");
		
		/*$sql	= "select 	 COD_TIPO_NOTA_CREDITO
							,NOM_TIPO_NOTA_CREDITO
					from 	 TIPO_NOTA_CREDITO
					order by COD_TIPO_NOTA_CREDITO";
		$this->add_control(new drop_down_dw('COD_TIPO_NOTA_CREDITO',$sql,150));
		$this->set_entrable('COD_TIPO_NOTA_CREDITO', true);
		*/
		$sql	= "SELECT COD_TIPO_NC_INTERNO_SII
	  					 ,NOM_TIPO_NC_INTERNO_SII 
	  			   FROM TIPO_NC_INTERNO_SII";
		$this->add_control($control = new drop_down_dw('COD_TIPO_NC_INTERNO_SII',$sql,150));
		$control->set_onChange("change_tipo_motivo_nc();");
		
		$this->add_control(new edit_text('COD_ESTADO_DOC_SII',10,10, 'hidden'));
		$this->add_control(new static_text('NOM_ESTADO_DOC_SII'));
		
		$this->add_control(new edit_text('COD_DOC_H',10,10, 'hidden'));
		$this->add_control(new static_text('NRO_FACTURA'));
		$this->add_control(new edit_text_upper('REFERENCIA',200, 100));
		/*$sql	= "select 	 COD_MOTIVO_NOTA_CREDITO
							,NOM_MOTIVO_NOTA_CREDITO
					from 	 MOTIVO_NOTA_CREDITO
					order by COD_MOTIVO_NOTA_CREDITO";
		$this->add_control(new drop_down_dw('COD_MOTIVO_NOTA_CREDITO',$sql,150));*/
		$this->add_control(new edit_text_multiline('OBS',50,2));
				
		// usuario anulación
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
								
		$this->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->set_entrable('COD_USUARIO_ANULA', false);			
		
		// campos duplicados
		$this->add_control(new static_num('RUT_NC'));
		$this->add_control(new static_text('DIG_VERIF_NC'));
		$this->add_control(new static_text('NOM_EMPRESA_NC'));
		$this->add_control(new static_text('GIRO_NC'));
		
		$this->add_control(new static_text('NOM_SUCURSAL'));
		$this->add_control(new static_text('NOM_PERSONA'));
	
		// asigna los mandatorys
		$this->set_mandatory('COD_ESTADO_DOC_SII', 'Estado');
		$this->set_mandatory('FECHA_NOTA_CREDITO', 'Fecha de Nota Credito');
		$this->set_mandatory('COD_EMPRESA', 'Empresa');
		$this->set_mandatory('COD_SUCURSAL_FACTURA', 'Sucursal de factura');
		$this->set_mandatory('COD_PERSONA', 'Persona');
		$this->set_mandatory('REFERENCIA', 'Referencia');
		//$this->set_mandatory('COD_MOTIVO_NOTA_CREDITO', 'Motivo NC');
		//$this->set_mandatory('COD_TIPO_NOTA_CREDITO', 'Tipo NC');
		
		$this->add_control(new edit_text('COD_CIUDAD',10, 100, 'hidden'));
		$this->add_control(new edit_text('COD_PAIS',10, 100, 'hidden'));	

		$this->add_control(new edit_text('VALOR_NC_H',10, 10, 'hidden'));
		
	}
	
	function fill_record(&$temp, $record) {	
		parent::fill_record($temp, $record);
		
			$COD_DOC = $this->get_item(0, 'COD_DOC_H');
			$COD_ESTADO_DOC_SII = $this->get_item(0, 'COD_ESTADO_DOC_SII');
			
			if (($COD_DOC != '') or ($COD_ESTADO_DOC_SII != 1))  //la NC viene desde FA, o estado <> emitida
				$temp->setVar('DISABLE_BUTTON', 'style="display:none"');
			else{	
					if ($this->entrable)
						$temp->setVar('DISABLE_BUTTON', '');
					else
						$temp->setVar('DISABLE_BUTTON', 'disabled="disabled"');
			}				
	}

}

class wi_nota_credito_base extends w_cot_nv {
	const K_ESTADO_SII_EMITIDA 			= 1;
	const K_ESTADO_SII_IMPRESA			= 2;
	const K_ESTADO_SII_ENVIADA			= 3;
	const K_ESTADO_SII_ANULADA			= 4;	
	const K_TIPO_NOTA_CREDITO_VENTA 	= 1;
	const K_PUEDE_ENVIAR_NC_DTE			= '993520';
	//const K_AUTORIZA_TIPO_NC	 		= '993525';
	const K_AUTORIZA_VISIBLE_BTN_DTE	= '993525';
	const K_AUTORIZA_MOD_TIPO_NC_INTERNO= '993530';
	const K_IP_FTP		= 42;		// Direccion del FTP
	const K_USER_FTP	= 43;		//usuario para el FTP
	const K_PASS_FTP	= 44;		// password para el FTP	

	function wi_nota_credito_base($cod_item_menu) {		
		
		parent::w_cot_nv('nota_credito', $cod_item_menu);
		$this->add_FK_delete_cascada('ITEM_NOTA_CREDITO');

		// tab nota credito
		// DATAWINDOWS NOTA_CREDITO
		$this->dws['dw_nota_credito'] = new dw_nota_credito();
		$this->add_controls_cot_nv();

		// tab items
		// DATAWINDOWS ITEMS NOTA_CREDITO
		$this->dws['dw_item_nota_credito'] = new dw_item_nota_credito();

		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_ESTADO_DOC_SII');
		$this->add_auditoria('FECHA_NOTA_CREDITO');
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('COD_TIPO_NOTA_CREDITO');
		$this->add_auditoria('COD_SUCURSAL_FACTURA');
		$this->add_auditoria('COD_PERSONA');
	}

	function new_record() {
		$this->b_delete_visible  = false; //cuando es un registro nuevo no muestra el boton eliminar
				
		$this->dws['dw_nota_credito']->insert_row();
		$this->dws['dw_nota_credito']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_nota_credito']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_nota_credito']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		
		$this->dws['dw_nota_credito']->set_item(0, 'COD_ESTADO_DOC_SII', self::K_ESTADO_SII_EMITIDA);
		$this->dws['dw_nota_credito']->set_item(0, 'NOM_ESTADO_DOC_SII', 'EMITIDA');
		$this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');
		
		$this->dws['dw_nota_credito']->set_entrable('FECHA_NOTA_CREDITO',	false);
		
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_MOD_TIPO_NC_INTERNO, $this->cod_usuario);
		if($priv == 'E')
			$this->dws['dw_nota_credito']->controls['COD_TIPO_NC_INTERNO_SII']->enabled = true;
		else
			$this->dws['dw_nota_credito']->controls['COD_TIPO_NC_INTERNO_SII']->enabled = false;
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql="select valor valor_nc from parametro where cod_parametro=40";
		$result = $db->build_results($sql);
		$valor_nc = $result[0]['valor_nc'];
		$this->dws['dw_nota_credito']->set_item(0, 'VALOR_NC_H', $valor_nc);
		$this->dws['dw_nota_credito']->set_item(0, 'TD_DISPLAY_CANT_POR_NC', 'none');
		$this->dws['dw_nota_credito']->set_item(0, 'GENERA_ENTRADA', 'N');
		}
			
	function load_record() {
		$cod_nota_credito = $this->get_item_wo($this->current_record, 'COD_NOTA_CREDITO');
		$this->dws['dw_nota_credito']->retrieve($cod_nota_credito);
		$cod_empresa = $this->dws['dw_nota_credito']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_nota_credito']->controls['COD_SUCURSAL_FACTURA']->retrieve($cod_empresa);
		$this->dws['dw_nota_credito']->controls['COD_PERSONA']->retrieve($cod_empresa);
		$this->dws['dw_item_nota_credito']->retrieve($cod_nota_credito);
		
		$COD_ESTADO_DOC_SII			= $this->dws['dw_nota_credito']->get_item(0, 'COD_ESTADO_DOC_SII');
		$COD_TIPO_NC_INTERNO_SII	= $this->dws['dw_nota_credito']->get_item(0, 'COD_TIPO_NC_INTERNO_SII');
		
		$this->b_print_visible 	 = true;
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible	 = true;
		$this->b_delete_visible  = true;
				
		$this->dws['dw_nota_credito']->set_entrable('FECHA_NOTA_CREDITO'	,false);
		$this->dws['dw_nota_credito']->set_entrable('REFERENCIA'			, true);
		$this->dws['dw_nota_credito']->set_entrable('OBS'					, true);
		$this->dws['dw_nota_credito']->set_entrable('NOM_EMPRESA'			, true);
		$this->dws['dw_nota_credito']->set_entrable('ALIAS'					, true);
		$this->dws['dw_nota_credito']->set_entrable('COD_EMPRESA'			, true);
		$this->dws['dw_nota_credito']->set_entrable('RUT'					, true);
		$this->dws['dw_nota_credito']->set_entrable('COD_SUCURSAL_FACTURA'	, true);
		$this->dws['dw_nota_credito']->set_entrable('COD_PERSONA'			, true);
		// aqui se dejan no modificables los datos del tab items
		$this->dws['dw_item_nota_credito']->set_entrable_dw(true);
		
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_MOD_TIPO_NC_INTERNO, $this->cod_usuario);
		if($priv == 'E')
			$this->dws['dw_nota_credito']->set_entrable('COD_TIPO_NC_INTERNO_SII'	, true);
		else
			$this->dws['dw_nota_credito']->set_entrable('COD_TIPO_NC_INTERNO_SII'	, false);	
		
		if($COD_TIPO_NC_INTERNO_SII == 1 || $COD_TIPO_NC_INTERNO_SII == 2) //Anulación Factura Total
			$this->dws['dw_item_nota_credito']->controls['CANTIDAD']->readonly = true;
		else	
			$this->dws['dw_item_nota_credito']->controls['CANTIDAD']->readonly = false;
			
		if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_EMITIDA) {
			unset($this->dws['dw_nota_credito']->controls['COD_ESTADO_DOC_SII']);
			$this->dws['dw_nota_credito']->add_control(new edit_text('COD_ESTADO_DOC_SII',10,10, 'hidden'));
			$this->dws['dw_nota_credito']->controls['NOM_ESTADO_DOC_SII']->type = '';
			
			if($this->tiene_privilegio_opcion(self::K_PUEDE_ENVIAR_NC_DTE)){
				$this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', '');
			}else{
				$this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');
			}
			
			$COD_DOC = $this->dws['dw_nota_credito']->get_item(0, 'COD_DOC_H');
			if ($COD_DOC  != '') {
				$this->dws['dw_nota_credito']->set_entrable('RUT'					, false);
				$this->dws['dw_nota_credito']->set_entrable('ALIAS'					, false);
				$this->dws['dw_nota_credito']->set_entrable('COD_EMPRESA'			, false);
				$this->dws['dw_nota_credito']->set_entrable('NOM_EMPRESA'			, false);
				$this->dws['dw_nota_credito']->set_entrable('COD_SUCURSAL_FACTURA'   , false);
				$this->dws['dw_nota_credito']->set_entrable('COD_PERSONA'			, false);
				
				// aqui se dejan no modificables los datos del tab items
				$this->dws['dw_item_nota_credito']->set_entrable('ORDEN'      		, false);
				$this->dws['dw_item_nota_credito']->set_entrable('ITEM'      		, false);
				$this->dws['dw_item_nota_credito']->set_entrable('COD_PRODUCTO'   	, false);
				$this->dws['dw_item_nota_credito']->set_entrable('NOM_PRODUCTO'  	, false);
				
				// Es una FA desde NV por anticipo
				if ($this->dws['dw_item_nota_credito']->row_count()==1) {
					$cod_producto = $this->dws['dw_item_nota_credito']->get_item(0, 'COD_PRODUCTO');
					$nom_producto = $this->dws['dw_item_nota_credito']->get_item(0, 'NOM_PRODUCTO');
					if ($cod_producto=='TE' && $nom_producto=='__ADMINISTRATIVA__') {
						$this->dws['dw_item_nota_credito']->set_item(0, 'COD_PRODUCTO', '');
						$this->dws['dw_item_nota_credito']->set_item(0, 'NOM_PRODUCTO', '');
						$this->dws['dw_item_nota_credito']->set_item(0, 'CANTIDAD_POR_NC', 1);
						$this->dws['dw_item_nota_credito']->set_entrable('COD_PRODUCTO', true);
						$this->dws['dw_item_nota_credito']->controls['COD_PRODUCTO']->set_onChange("change_item_nc_adm(this);");
						$this->dws['dw_item_nota_credito']->set_entrable('NOM_PRODUCTO', true);
						$this->dws['dw_item_nota_credito']->controls['NOM_PRODUCTO']->set_readonly(true);

						// No permite el tipo ANULAR						
						/*$sql	= "select 	 COD_TIPO_NOTA_CREDITO
											,NOM_TIPO_NOTA_CREDITO
									from 	 TIPO_NOTA_CREDITO
									where 	COD_TIPO_NOTA_CREDITO in (2,3)
									order by COD_TIPO_NOTA_CREDITO";
						$this->dws['dw_nota_credito']->controls['COD_TIPO_NOTA_CREDITO']->set_sql($sql);
						$this->dws['dw_nota_credito']->controls['COD_TIPO_NOTA_CREDITO']->retrieve();
						$this->dws['dw_nota_credito']->set_item(0, 'COD_TIPO_NOTA_CREDITO', '');*/
					}
				}
			}
		}
		else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_IMPRESA) {
			$this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');
			$sql = "select 	COD_ESTADO_DOC_SII
							,NOM_ESTADO_DOC_SII
					from ESTADO_DOC_SII
					where COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_IMPRESA." or
							COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_ANULADA."
					order by COD_ESTADO_DOC_SII";
					
			unset($this->dws['dw_nota_credito']->controls['COD_ESTADO_DOC_SII']);
			$this->dws['dw_nota_credito']->add_control($control = new drop_down_dw('COD_ESTADO_DOC_SII',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_nota_credito']->controls['NOM_ESTADO_DOC_SII']->type = 'hidden';
			$this->dws['dw_nota_credito']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));

			$this->dws['dw_nota_credito']->set_entrable('FECHA_NOTA_CREDITO'	, true);
			$this->dws['dw_nota_credito']->set_entrable('REFERENCIA'			, false);
			$this->dws['dw_nota_credito']->set_entrable('OBS'					, false);
			$this->dws['dw_nota_credito']->set_entrable('NOM_EMPRESA'			, false);
			$this->dws['dw_nota_credito']->set_entrable('ALIAS'				    , false);
			$this->dws['dw_nota_credito']->set_entrable('COD_EMPRESA'			, false);
			$this->dws['dw_nota_credito']->set_entrable('RUT'					, false);
			$this->dws['dw_nota_credito']->set_entrable('COD_SUCURSAL_FACTURA'  , false);
			$this->dws['dw_nota_credito']->set_entrable('COD_PERSONA'			, false);

			$this->dws['dw_nota_credito']->set_entrable('PORC_DSCTO1'			, false);
			$this->dws['dw_nota_credito']->set_entrable('MONTO_DSCTO1'			, false);
			$this->dws['dw_nota_credito']->set_entrable('PORC_DSCTO2'			, false);
			$this->dws['dw_nota_credito']->set_entrable('MONTO_DSCTO2'			, false);
			$this->dws['dw_nota_credito']->set_entrable('PORC_IVA'				, false);		
		
			
			// aqui se dejan no modificables los datos del tab items
			$this->dws['dw_item_nota_credito']->set_entrable_dw(false);

			$this->b_delete_visible  = false;
				
		}
		else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_ENVIADA) {
			//SI USUARIO TIENE PRIVILEGIOS DE ENVIAR POR SEGUNDA VES LA NC-ELECTRONICA
			if($this->tiene_privilegio_opcion(self::K_AUTORIZA_VISIBLE_BTN_DTE)){
				$this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', '');
			}else{
				$this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');
			}
			$this->b_print_visible 	 = false;
			
			$sql = "select 	COD_ESTADO_DOC_SII
							,NOM_ESTADO_DOC_SII
					from ESTADO_DOC_SII
					where COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_ENVIADA." or
							COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_ANULADA."
					order by COD_ESTADO_DOC_SII";
					
			$this->dws['dw_nota_credito']->set_entrable('FECHA_NOTA_CREDITO'	, false);
			$this->dws['dw_nota_credito']->set_entrable('REFERENCIA'			, false);
			$this->dws['dw_nota_credito']->set_entrable('OBS'					, false);
			$this->dws['dw_nota_credito']->set_entrable('NOM_EMPRESA'			, false);
			$this->dws['dw_nota_credito']->set_entrable('ALIAS'				    , false);
			$this->dws['dw_nota_credito']->set_entrable('COD_EMPRESA'			, false);
			$this->dws['dw_nota_credito']->set_entrable('RUT'					, false);
			$this->dws['dw_nota_credito']->set_entrable('COD_SUCURSAL_FACTURA'  , false);
			$this->dws['dw_nota_credito']->set_entrable('COD_PERSONA'			, false);

			$this->dws['dw_nota_credito']->set_entrable('PORC_DSCTO1'			, false);
			$this->dws['dw_nota_credito']->set_entrable('MONTO_DSCTO1'			, false);
			$this->dws['dw_nota_credito']->set_entrable('PORC_DSCTO2'			, false);
			$this->dws['dw_nota_credito']->set_entrable('MONTO_DSCTO2'			, false);
			$this->dws['dw_nota_credito']->set_entrable('PORC_IVA'				, false);		
		
			
			// aqui se dejan no modificables los datos del tab items
			$this->dws['dw_item_nota_credito']->set_entrable_dw(false);

			$this->b_delete_visible  = false;
				
		}
		else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_ANULADA) {
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;
			$this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');			
		}
		
		//campos duplicados
		if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_EMITIDA){ // estado = emitida
			
			$giro = $this->dws['dw_nota_credito']->get_item(0, 'GIRO');
			
			$this->dws['dw_nota_credito']->controls['RUT']->type = 'text';
			$this->dws['dw_nota_credito']->controls['RUT_NC']->type = 'hidden';
			
			$this->dws['dw_nota_credito']->controls['DIG_VERIF']->type = 'text';
			$this->dws['dw_nota_credito']->controls['DIG_VERIF_NC']->type = 'hidden';
			
			$this->dws['dw_nota_credito']->controls['NOM_EMPRESA']->type = 'text';
			$this->dws['dw_nota_credito']->controls['NOM_EMPRESA_NC']->type = 'hidden';
			
			$this->dws['dw_nota_credito']->controls['GIRO']->type = '';
			$this->dws['dw_nota_credito']->controls['GIRO_NC']->type = 'hidden';
			
			$this->dws['dw_nota_credito']->set_visible('COD_SUCURSAL_FACTURA', true);
			$this->dws['dw_nota_credito']->controls['NOM_SUCURSAL']->type = 'hidden';
			
			$this->dws['dw_nota_credito']->set_visible('COD_PERSONA', true);
			$this->dws['dw_nota_credito']->controls['NOM_PERSONA']->type = 'hidden';
			
		}
			
		else{
			$this->dws['dw_nota_credito']->controls['RUT']->type = 'hidden';
			$this->dws['dw_nota_credito']->controls['RUT_NC']->type = '';

			$this->dws['dw_nota_credito']->controls['DIG_VERIF']->type = 'hidden';
			$this->dws['dw_nota_credito']->controls['DIG_VERIF_NC']->type = '';	

			$this->dws['dw_nota_credito']->controls['NOM_EMPRESA']->type = 'hidden';
			$this->dws['dw_nota_credito']->controls['NOM_EMPRESA_NC']->type = '';	
			
			$this->dws['dw_nota_credito']->controls['GIRO']->type = 'hidden';
			$this->dws['dw_nota_credito']->controls['GIRO_NC']->type = '';	
			
			$this->dws['dw_nota_credito']->set_visible('COD_SUCURSAL_FACTURA', false);
			$this->dws['dw_nota_credito']->controls['NOM_SUCURSAL']->type = '';	
			
			$this->dws['dw_nota_credito']->set_visible('COD_PERSONA', false);
			$this->dws['dw_nota_credito']->controls['NOM_PERSONA']->type = '';	
		
		}
		if (session::is_set("cant_nc_a_hacer")){
			$this->b_print_visible 	 = false;
			$this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');
		}					
	}

	function goto_record($record) {
		if (!session::is_set("cant_nc_a_hacer")) 
			parent::goto_record($record);
		else {
			$cant_nc_a_hacer = session::get("cant_nc_a_hacer");
			$this->current_record = $record;
			$this->load_record();
			$this->modify_record();
			session::un_set("cant_nc_a_hacer");
			if ($cant_nc_a_hacer > 1)
				$this->alert('Se harán '.$cant_nc_a_hacer.' Notas de Crédito de esta Factura.');
		}
	}
	
	function get_key() {
		return $this->dws['dw_nota_credito']->get_item(0, 'COD_NOTA_CREDITO');
	}
	
	function get_key_para_ruta_menu() {
		return $this->dws['dw_nota_credito']->get_item(0, 'NRO_NOTA_CREDITO');
	}

	function save_record($db) {
		$COD_NOTA_CREDITO			= $this->get_key();
		$COD_USUARIO				= $this->dws['dw_nota_credito']->get_item(0, 'COD_USUARIO');
		$NRO_NOTA_CREDITO			= $this->dws['dw_nota_credito']->get_item(0, 'NRO_NOTA_CREDITO');
		
		$FECHA_NOTA_CREDITO			= $this->dws['dw_nota_credito']->get_item(0, 'FECHA_NOTA_CREDITO');

		$COD_ESTADO_DOC_SII			= $this->dws['dw_nota_credito']->get_item(0, 'COD_ESTADO_DOC_SII');
		$COD_EMPRESA				= $this->dws['dw_nota_credito']->get_item(0, 'COD_EMPRESA');
		$COD_SUCURSAL_FACTURA		= $this->dws['dw_nota_credito']->get_item(0, 'COD_SUCURSAL_FACTURA');
		$COD_PERSONA				= $this->dws['dw_nota_credito']->get_item(0, 'COD_PERSONA');
		$REFERENCIA					= $this->dws['dw_nota_credito']->get_item(0, 'REFERENCIA');
		$OBS						= $this->dws['dw_nota_credito']->get_item(0, 'OBS');						
		$COD_BODEGA					= $this->dws['dw_nota_credito']->get_item(0, 'COD_BODEGA');
		$COD_TIPO_NOTA_CREDITO		= $this->dws['dw_nota_credito']->get_item(0, 'COD_TIPO_NOTA_CREDITO');
		$COD_DOC					= $this->dws['dw_nota_credito']->get_item(0, 'COD_DOC_H');
		$SUBTOTAL					= $this->dws['dw_nota_credito']->get_item(0, 'SUM_TOTAL');
		$TOTAL_NETO					= $this->dws['dw_nota_credito']->get_item(0, 'TOTAL_NETO');
		$PORC_DSCTO1				= $this->dws['dw_nota_credito']->get_item(0, 'PORC_DSCTO1');
		$PORC_DSCTO2				= $this->dws['dw_nota_credito']->get_item(0, 'PORC_DSCTO2');
		$INGRESO_USUARIO_DSCTO1		= $this->dws['dw_nota_credito']->get_item(0, 'INGRESO_USUARIO_DSCTO1');
		$MONTO_DSCTO1				= $this->dws['dw_nota_credito']->get_item(0, 'MONTO_DSCTO1');
		$INGRESO_USUARIO_DSCTO2		= $this->dws['dw_nota_credito']->get_item(0, 'INGRESO_USUARIO_DSCTO2');
		$MONTO_DSCTO2				= $this->dws['dw_nota_credito']->get_item(0, 'MONTO_DSCTO2');
		$PORC_IVA					= $this->dws['dw_nota_credito']->get_item(0, 'PORC_IVA');
		$MONTO_IVA					= $this->dws['dw_nota_credito']->get_item(0, 'MONTO_IVA');
		$TOTAL_CON_IVA				= $this->dws['dw_nota_credito']->get_item(0, 'TOTAL_CON_IVA');
		$MOTIVO_ANULA				= $this->dws['dw_nota_credito']->get_item(0, 'MOTIVO_ANULA');
		$COD_USUARIO_ANULA			= $this->dws['dw_nota_credito']->get_item(0, 'COD_USUARIO_ANULA');
		$COD_MOTIVO_NOTA_CREDITO	= $this->dws['dw_nota_credito']->get_item(0, 'COD_MOTIVO_NOTA_CREDITO');
		$GENERA_ENTRADA				= $this->dws['dw_nota_credito']->get_item(0, 'GENERA_ENTRADA');
		$COD_TIPO_NC_INTERNO_SII	= $this->dws['dw_nota_credito']->get_item(0, 'COD_TIPO_NC_INTERNO_SII');
		
		if (($MOTIVO_ANULA != '') && ($COD_USUARIO_ANULA == '')) // se anula 
			$COD_USUARIO_ANULA			= $this->cod_usuario;
		else
			$COD_USUARIO_ANULA			= "null";
			
		$COD_USUARIO_IMPRESION		= $this->dws['dw_nota_credito']->get_item(0, 'COD_USUARIO_IMPRESION');	

		$COD_NOTA_CREDITO			= ($COD_NOTA_CREDITO =='') ? "null" : $COD_NOTA_CREDITO;	
		$NRO_NOTA_CREDITO			= ($NRO_NOTA_CREDITO =='') ? "null" : $NRO_NOTA_CREDITO;
		$OBS						= ($OBS =='') ? "null" : "'$OBS'";
		$COD_BODEGA					= ($COD_BODEGA =='') ? "null" : $COD_BODEGA; 
		$COD_TIPO_NOTA_CREDITO		= ($COD_TIPO_NOTA_CREDITO =='') ? "null" : $COD_TIPO_NOTA_CREDITO; 
		$COD_DOC					= ($COD_DOC =='') ? "null" : $COD_DOC; 
		$SUBTOTAL 					= ($SUBTOTAL == '' ? 0: "$SUBTOTAL");
		$TOTAL_NETO = ($TOTAL_NETO == '' ? 0: "$TOTAL_NETO");	
		$PORC_DSCTO1 = ($PORC_DSCTO1 == '' ? 0: "$PORC_DSCTO1");
		$PORC_DSCTO2 = ($PORC_DSCTO2 == '' ? 0: "$PORC_DSCTO2");
		$INGRESO_USUARIO_DSCTO1 	= ($INGRESO_USUARIO_DSCTO1 =='') ? "null" : "'$INGRESO_USUARIO_DSCTO1'";
		$INGRESO_USUARIO_DSCTO2 	= ($INGRESO_USUARIO_DSCTO2 =='') ? "null" : "'$INGRESO_USUARIO_DSCTO2'";
		$MONTO_DSCTO1 				= ($MONTO_DSCTO1 == '' ? 0: "$MONTO_DSCTO1");
		$MONTO_DSCTO2 				= ($MONTO_DSCTO2 == '' ? 0: "$MONTO_DSCTO2");
		$PORC_IVA 					= ($PORC_IVA == '' ? 0: "$PORC_IVA");
		$MONTO_IVA 					= ($MONTO_IVA == '' ? 0: "$MONTO_IVA");
		$TOTAL_CON_IVA 				= ($TOTAL_CON_IVA == '' ? 0: "$TOTAL_CON_IVA");	
		$MOTIVO_ANULA				= ($MOTIVO_ANULA =='') ? "null" : "'$MOTIVO_ANULA'";
		$COD_USUARIO_IMPRESION		= ($COD_USUARIO_IMPRESION =='') ? "null" : $COD_USUARIO_IMPRESION;
		
	
		$sp = 'spu_nota_credito';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';							    	
	    	
		$param	= "'$operacion'
				,$COD_NOTA_CREDITO
				,$COD_USUARIO_IMPRESION
				,$COD_USUARIO
				,$NRO_NOTA_CREDITO
				,'$FECHA_NOTA_CREDITO'
				,$COD_ESTADO_DOC_SII
				,$COD_EMPRESA
				,$COD_SUCURSAL_FACTURA		
				,$COD_PERSONA		
				,'$REFERENCIA'
				,$OBS
				,$COD_BODEGA
				,$COD_TIPO_NOTA_CREDITO 
				,$COD_DOC	
				,$SUBTOTAL
				,$TOTAL_NETO
				,$PORC_DSCTO1
				,$PORC_DSCTO2
				,$INGRESO_USUARIO_DSCTO1
				,$MONTO_DSCTO1
				,$INGRESO_USUARIO_DSCTO2
				,$MONTO_DSCTO2
				,$PORC_IVA
				,$MONTO_IVA
				,$TOTAL_CON_IVA
				,$MOTIVO_ANULA
				,$COD_USUARIO_ANULA
				,$COD_MOTIVO_NOTA_CREDITO
				,'$GENERA_ENTRADA'
				,$COD_TIPO_NC_INTERNO_SII";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_NOTA_CREDITO = $db->GET_IDENTITY();
				$this->dws['dw_nota_credito']->set_item(0, 'COD_NOTA_CREDITO', $COD_NOTA_CREDITO);
			}
			if (($MOTIVO_ANULA != 'null') && ($COD_USUARIO_ANULA != 'null')){ // se anula 
				$this->f_envia_mail('ANULADA');
			}
			for ($i=0; $i<$this->dws['dw_item_nota_credito']->row_count(); $i++) 
				$this->dws['dw_item_nota_credito']->set_item($i, 'COD_NOTA_CREDITO', $COD_NOTA_CREDITO);
		
			if (!$this->dws['dw_item_nota_credito']->update($db)) 
				return false;
			
			$parametros_sp = "'item_nota_credito','nota_credito',$COD_NOTA_CREDITO";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)) 
				return false;
			
			$parametros_sp = "'RECALCULA', $COD_NOTA_CREDITO";
			if (!$db->EXECUTE_SP('spu_nota_credito', $parametros_sp))
				return false;					
			return true;
		}
		
		return false;							
	}
	
	function print_record() {
		if (!$this->lock_record())
			return false;
		$cod_nota_credito = $this->get_key();
		$cod_tipo_doc_sii = 3;
		$cod_usuario_impresion = $this->cod_usuario;
		
		$nro_nota_credito = $this->dws['dw_nota_credito']->get_item(0, 'NRO_NOTA_CREDITO');
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if($nro_nota_credito == ''){
			$sql = "select dbo.f_get_nro_doc_sii ($cod_tipo_doc_sii , $cod_usuario_impresion) NRO_NOTA_CREDITO";
			$result = $db->build_results($sql);
			$nro_nota_credito = $result[0]['NRO_NOTA_CREDITO'];
		}
		

		//declrar constante para que el monto con iva del reporte lo transpforme a palabras
		$sql = "select TOTAL_CON_IVA from NOTA_CREDITO where COD_NOTA_CREDITO = $cod_nota_credito";
		
		$resultado = $db->build_results($sql);
		$total_con_iva = $resultado [0] ['TOTAL_CON_IVA'] ;
		$total_en_palabras =  Numbers_Words::toWords($total_con_iva,"es");
		$total_en_palabras = strtr($total_en_palabras, "áéíóú", "aeiou");
			
		if ($nro_nota_credito == -1){
			$this->redraw();		
			$this->dws['dw_nota_credito']->message("Sr(a). Usuario: Ud. no tiene documentos asignados, para imprimir la Nota de Crédito.");	
			return false;

		}	
		else{
			$db->BEGIN_TRANSACTION();
			$sp = 'spu_nota_credito';
			$param = "'PRINT', $cod_nota_credito, $cod_usuario_impresion";
			
			if ($db->EXECUTE_SP($sp, $param)) {
				$db->COMMIT_TRANSACTION();
				
					$estado_nc_impresa = self::K_ESTADO_SII_IMPRESA; 
					$cod_estado_nc = $this->dws['dw_nota_credito']->get_item(0, 'COD_ESTADO_DOC_SII');
					if ($cod_estado_nc != $estado_nc_impresa)//es la 1era vez que se imprime la Guia de Despacho 
						$this->f_envia_mail('IMPRESO');
						
			$sql= "SELECT NC.COD_NOTA_CREDITO,
								NC.NRO_NOTA_CREDITO,
								dbo.f_format_date(NC.FECHA_NOTA_CREDITO,3)FECHA_NOTA_CREDITO,
								NC.COD_USUARIO_IMPRESION,
								NC.REFERENCIA,
								NC.NOM_EMPRESA,
								NC.GIRO,
								NC.RUT,
								NC.DIG_VERIF,
								NC.DIRECCION,
								NC.TELEFONO,
								NC.FAX,
								NC.SUBTOTAL,
								NC.PORC_DSCTO1,
								NC.MONTO_DSCTO1,
								NC.PORC_DSCTO2,
								NC.MONTO_DSCTO2,
								NC.MONTO_DSCTO1 + NC.MONTO_DSCTO2 TOTAL_DSCTO,
								NC.TOTAL_NETO,
								NC.PORC_IVA,
								NC.MONTO_IVA,
								NC.TOTAL_CON_IVA,
								COM.NOM_COMUNA,
								CIU.NOM_CIUDAD,
								ITNC.ITEM,
								ITNC.CANTIDAD,
								ITNC.COD_PRODUCTO,
								ITNC.NOM_PRODUCTO,
								ITNC.PRECIO,
								ITNC.PRECIO * ITNC.CANTIDAD TOTAL_NC,								 
								'".$total_en_palabras."' TOTAL_EN_PALABRAS,
								NC.REFERENCIA,
								convert(varchar(5), GETDATE(), 8) HORA,
								FA.NRO_FACTURA,
								U.INI_USUARIO
						FROM 	NOTA_CREDITO NC LEFT OUTER JOIN FACTURA FA ON NC.COD_DOC = FA.COD_FACTURA 
												LEFT OUTER JOIN COMUNA COM ON COM.COD_COMUNA = NC.COD_COMUNA
												LEFT OUTER JOIN CIUDAD CIU ON CIU.COD_CIUDAD = NC.COD_CIUDAD, ITEM_NOTA_CREDITO ITNC, USUARIO U
						WHERE 	NC.COD_NOTA_CREDITO = $cod_nota_credito
						AND		NC.COD_USUARIO = U.COD_USUARIO
						AND		ITNC.COD_NOTA_CREDITO = NC.COD_NOTA_CREDITO";
		
									// reporte
					$labels = array();
					$labels['strCOD_NOTA_CREDITO'] = $cod_nota_credito;					
					$file_name = $this->find_file('nota_credito', 'nota_credito.xml');					
					$rpt = new print_nota_credito($sql, $file_name, $labels, "Nota Crédito".$cod_nota_credito, 0);										
					$this->_load_record();
					$this->b_delete_visible  = false;	
					return true;
				}
				else {
					$db->ROLLBACK_TRANSACTION();
					return false;
				}			
		}
		$this->unlock_record();
	}
	
	
	// esta funcio envia mail  cuando se imprime e documento de guia despacho 
 	function f_envia_mail($estado_nota_credito){
 		$cod_nota_credito = $this->get_key();
 		$remitente = $this->nom_usuario;
        $cod_remitente = $this->cod_usuario;
        
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        $sql = "SELECT NRO_NOTA_CREDITO FROM NOTA_CREDITO WHERE COD_NOTA_CREDITO = $cod_nota_credito";
        $result = $db->build_results($sql);
        $nro_nota_credito = $result[0]['NRO_NOTA_CREDITO'];		
		
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        // obtiene el mail de quien creo la tarea y manda el mail
        $sql_remitente = "SELECT MAIL from USUARIO where COD_USUARIO = $cod_remitente";
        
        $result_remitente = $db->build_results($sql_remitente);
        $mail_remitente = $result_remitente[0]['MAIL'];
		
 		// Mail destinatarios
        $para_admin1 = 'mulloa@integrasystem.cl';
        $para_admin2 = 'mulloa@integrasystem.cl';
        /*
        $para_admin1 = 'mherrera@integrasystem.cl';
        $para_admin2 = 'imeza@integrasystem.cl';
		*/
        
        if($estado_nota_credito == 'IMPRESO')
		{
			$asunto = 'Impresion de Nota de Credito Nº '.$nro_nota_credito;
	        $mensaje = 'Se ha <b>IMPRESO</b> la <b>Nota de Credito Nº '.$nro_nota_credito.'</b> por el usuario <b><i>'.$remitente.'<i><b>';  
		}
	  	
	 	if($estado_nota_credito == 'ANULADA')
		{
	        $asunto = 'Anulacion de Nota de Credito Nº '.$nro_nota_credito;
	        $mensaje = 'Se ha <b>ANULADO</b> la <b>Nota de Credito Nº '.$nro_nota_credito.'</b> por el usuario <b><i>'.$remitente.'<i><b>';
		}

	  	$cabeceras  = 'MIME-Version: 1.0' . "\n";
        $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
        $cabeceras .= 'From: '.$mail_remitente. "\n";
        //se comenta el envio de mail por q ya no es necesario => Vmelo. 
        //mail($para_admin1, $asunto, $mensaje, $cabeceras);
        //mail($para_admin2, $asunto, $mensaje, $cabeceras);
 		return 0;
   	}
   	
	function Envia_DTE($name_archivo, $fname){
			//SOLO para el CHAITEN
		/*	if (K_SERVER <> "192.168.2.26")
				return false;*/
			
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql_ftp =	"select dbo.f_get_parametro(".self::K_IP_FTP.") DIRECCION_FTP
								,dbo.f_get_parametro(".self::K_USER_FTP.")	USER_FTP
								,dbo.f_get_parametro(".self::K_PASS_FTP.")	PASS_FTP";

			$result_ftp = $db->build_results($sql_ftp);
			
			// datos de FTP Local
			$file_name_ftp = (dirname(__FILE__)."/../../ftp_dte.php");
			if (file_exists($file_name_ftp)){ 
			require_once($file_name_ftp);
				$K_DIRECCION_FTP	= K_DIRECCION_FTP;
				$K_USUARIO_FTP		= K_USUARIO_FTP;
				$K_PASSWORD_FTP		= K_PASSWORD_FTP;
				$K_PORT 			= 21;
			}else{
			//datos de FTP
			$K_DIRECCION_FTP	= $result_ftp[0]['DIRECCION_FTP'] ;	//Ip FTP
			$K_USUARIO_FTP		= $result_ftp[0]['USER_FTP'] ;		//Usuario FTP
			$K_PASSWORD_FTP		= $result_ftp[0]['PASS_FTP'] ;		//Password FTP
			$K_PORT 			= 21; 		// PUERTO DEL FTP
			}
			// establecer una conexión básica
			$conn_id = ftp_connect($K_DIRECCION_FTP);
			if ($conn_id===false)
				return false; 
			
			// iniciar una sesión con nombre de usuario y contraseña
			$login_result = ftp_login($conn_id, $K_USUARIO_FTP, $K_PASSWORD_FTP);
			if($login_result === false)
				return false;

			ftp_pasv ($conn_id, true) ;
			// subir un archivo
			//$upload = ftp_put($conn_id, $name_archivo, $fname, FTP_BINARY);  
			if(!(ftp_put($conn_id, $name_archivo, $fname, FTP_BINARY)))
				return false;

			// cerrar la conexión ftp 
			ftp_close($conn_id);
			
			return true;
	}
   	
   	function envia_NC_Electronica(){
		if (!$this->lock_record())
			return false;
   		
		$cod_nota_credito = $this->get_key();
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$count1= 0;
		
		$sql_valida="SELECT CANTIDAD 
			  		 FROM ITEM_NOTA_CREDITO
			  		 WHERE COD_NOTA_CREDITO = $cod_nota_credito";
			  
		$result_valida = $db->build_results($sql_valida);

		for($i = 0 ; $i < count($result_valida) ; $i++){
			if($result_valida[$i] <> 0)
				$count1 = $count1 + 1;
		}
		if($count1 > 18){
			$this->_load_record();
			$this->alert('Se está ingresando más item que la cantidad permitida, favor contacte a IntegraSystem.');
			return false;
		}		
		
		$this->sepa_decimales	= ',';	//Usar , como separador de decimales
		$this->vacio 			= ' ';	//Usar rellenos de blanco, CAMPO ALFANUMERICO
		$this->llena_cero		= 0;	//Usar rellenos con '0', CAMPO NUMERICO
		$this->separador		= ';';	//Usar ; como separador de campos
		$cod_usuario_impresion = $this->cod_usuario;
		
		$cod_impresora_dte = $_POST['wi_impresora_dte'];
		if($cod_impresora_dte == 100){
		$EMISOR_NC = 'SALA VENTA';
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
		$EMISOR_NC = $result[0]['NOM_USUARIO'] ;
		}
		
		$db->BEGIN_TRANSACTION();
		$sp = 'spu_nota_credito';
		$param = "'ENVIA_DTE', $cod_nota_credito, $cod_usuario_impresion";
				
		if ($db->EXECUTE_SP($sp, $param)) {
			$db->COMMIT_TRANSACTION();	
				
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			//declrar constante para que el monto con iva del reporte lo transpforme a palabras
			$sql = "select TOTAL_CON_IVA from NOTA_CREDITO where COD_NOTA_CREDITO = $cod_nota_credito";
			
			$resultado = $db->build_results($sql);
			$total_con_iva = $resultado [0] ['TOTAL_CON_IVA'] ;
			$total_en_palabras =  Numbers_Words::toWords($total_con_iva,"es");
			$total_en_palabras = strtr($total_en_palabras, "áéíóú", "aeiou");
			$total_en_palabras = strtoupper($total_en_palabras);
	
	   		$sql_dte= "SELECT	NC.COD_NOTA_CREDITO,
								NC.NRO_NOTA_CREDITO,
								dbo.f_emp_get_mail_cargo_persona(NC.COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA,
								dbo.f_format_date(NC.FECHA_NOTA_CREDITO,1)FECHA_NOTA_CREDITO,
								NC.COD_USUARIO_IMPRESION,
								NC.REFERENCIA,
								NC.NOM_EMPRESA,
								NC.COD_TIPO_NOTA_CREDITO,
								NC.GIRO,
								NC.RUT,
								NC.DIG_VERIF,
								NC.DIRECCION,
								NC.TELEFONO,
								NC.FAX,
								NC.SUBTOTAL,
								NC.PORC_DSCTO1,
								NC.MONTO_DSCTO1,
								NC.PORC_DSCTO2,
								NC.MONTO_DSCTO2,
								NC.MONTO_DSCTO1 + NC.MONTO_DSCTO2 TOTAL_DSCTO,
								NC.TOTAL_NETO,
								NC.PORC_IVA,
								NC.MONTO_IVA,
								NC.TOTAL_CON_IVA,
								COM.NOM_COMUNA,
								CIU.NOM_CIUDAD,
								ITNC.ITEM,
								ITNC.CANTIDAD,
								ITNC.COD_PRODUCTO,
								ITNC.NOM_PRODUCTO,
								ITNC.PRECIO,
								ITNC.PRECIO * ITNC.CANTIDAD TOTAL_NC,								 
								'".$total_en_palabras."' TOTAL_EN_PALABRAS,
								convert(varchar(5), GETDATE(), 8) HORA,
								FA.NRO_FACTURA,
								FA.PORC_IVA PORC_IVA_FA,
								dbo.f_format_date(FA.FECHA_FACTURA,1) FECHA_FACTURA,
								'$EMISOR_NC' NOM_USUARIO,
								ITNC.ORDEN,
								NC.OBS
						FROM 	NOTA_CREDITO NC LEFT OUTER JOIN FACTURA FA ON NC.COD_DOC = FA.COD_FACTURA 
												LEFT OUTER JOIN COMUNA COM ON COM.COD_COMUNA = NC.COD_COMUNA
												LEFT OUTER JOIN CIUDAD CIU ON CIU.COD_CIUDAD = NC.COD_CIUDAD
								, ITEM_NOTA_CREDITO ITNC, USUARIO U											
						WHERE 	NC.COD_NOTA_CREDITO = $cod_nota_credito
						and NC.COD_USUARIO = U.COD_USUARIO
						AND		ITNC.COD_NOTA_CREDITO = NC.COD_NOTA_CREDITO";

			$result_dte = $db->build_results($sql_dte);
			//CANTIDAD DE ITEM_NOTA_CREDITO 
			$count = count($result_dte);

			// datos de Nota Credito
			$NRO_NOTA_CREDITO	= $result_dte[0]['NRO_NOTA_CREDITO'] ;			// 1 Numero Nota Credito
			$FECHA_NOTA_CREDITO	= $result_dte[0]['FECHA_NOTA_CREDITO'] ;		// 2 Fecha Nota Credito
			//Email - VE: =>En el caso de las Nota Credito y otros documentos, no aplica por lo que se dejan 0;0 
			$TD					= $this->llena_cero;					// 3 Tipo Despacho
			$TT					= $this->llena_cero;					// 4 Tipo Traslado
			//Email - VE: => 
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
			$REFERENCIA			= $result_dte[0]['REFERENCIA'];			//12 Referencia de la Nota Credito  //datos olvidado por VE.
			$NRO_FACTURA		= $result_dte[0]['NRO_FACTURA'];		//Solicitado a VE por SP
			$GENERA_SALIDA		= $this->vacio;							//Solicitado a VE por SP "DESPACHADO"
			$CANCELADA			= $this->vacio;							//Solicitado a VE por SP "CANCELADO"
			$SUBTOTAL			= number_format($result_dte[0]['SUBTOTAL'], 1, ',', '');	//Solicitado a VE por SP "SUBTOTAL"
			$PORC_DSCTO1		= number_format($result_dte[0]['PORC_DSCTO1'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO1"
			$PORC_DSCTO2		= number_format($result_dte[0]['PORC_DSCTO2'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO2"
			$EMISOR_NC			= $result_dte[0]['NOM_USUARIO'];		//Solicitado a VE por SP "EMISOR_NOTA_CREDITO"
			$NOM_COMUNA			= $result_dte[0]['NOM_COMUNA'];			//13 Comuna Recepcion
			$NOM_CIUDAD			= $result_dte[0]['NOM_CIUDAD'];			//14 Ciudad Recepcion
			$DP					= $result_dte[0]['DIRECCION'];			//15 Dirección Postal
			$COP				= $result_dte[0]['NOM_COMUNA'];			//16 Comuna Postal
			$CIP				= $result_dte[0]['NOM_CIUDAD'];			//17 Ciudad Postal
			
			//DATOS DE TOTALES number_format($result_dte[$i]['TOTAL_FA'], 0, ',', '.');
			$TOTAL_NETO			= number_format($result_dte[0]['TOTAL_NETO'], 1, ',', '');		//18 Monto Neto
			$PORC_IVA			= number_format($result_dte[0]['PORC_IVA'], 1, ',', '');		//19 Tasa IVA
			$MONTO_IVA			= number_format($result_dte[0]['MONTO_IVA'], 1, ',', '');		//20 Monto IVA
			$TOTAL_CON_IVA		= number_format($result_dte[0]['TOTAL_CON_IVA'], 1, ',', '');	//21 Monto Total
			$D1					= 'D1';															//22 Tipo de Mov 1 (Desc/Rec)
			$P1					= '$';															//23 Tipo de valor de Desc/Rec 1
			$MONTO_DSCTO1		= number_format($result_dte[0]['MONTO_DSCTO1'], 1, ',', '');	//24 Valor del Desc/Rec 1
			$D2					= 'D2';															//25 Tipo de Mov 2 (Desc/Rec)
			$P2					= '$';															//26 Tipo de valor de Desc/Rec 2
			$MONTO_DSCTO2		= number_format($result_dte[0]['MONTO_DSCTO2'], 1, ',', '');	//27 Valor del Desc/Rec 2
			$D3					= 'D3';															//28 Tipo de Mov 3 (Desc/Rec)
			$P3					= '$';															//29 Tipo de valor de Desc/Rec 3
			$MONTO_DSCTO3		= '';															//30 Valor del Desc/Rec 3
			$NOM_FORMA_PAGO		= $this->vacio;													//Dato Especial forma de pago adicional
			$NRO_ORDEN_COMPRA	= $this->vacio;													//Numero de Orden Pago
			$NRO_NOTA_VENTA		= $result_dte[0]['NRO_FACTURA'];									//Numero de Nota Venta
			$OBSERVACIONES		= $result_dte[0]['OBS'];										//si la Nota Credito tiene notas u observaciones
			$OBSERVACIONES		=  eregi_replace("[\n|\r|\n\r]", ' ', $OBSERVACIONES); 			//elimina los saltos de linea. entre otros caracteres
			$TOTAL_EN_PALABRAS	= $result_dte[0]['TOTAL_EN_PALABRAS'];							//Total en palabras: Posterior al campo Notas
   			$PORC_IVA_FA		= number_format($result_dte[0]['PORC_IVA_FA'], 1, ',', '');		//Tasa IVA Factura
			
			//datos que hacen referencia al documento NC - FA
			//Numero de Factura o Documento que hace referencia
			$FR					= $result_dte[0]['NRO_FACTURA'];								//39 Folio Referencia
			$FECHA_R			= $result_dte[0]['FECHA_FACTURA'];								//40 Fecha de Referencia
			//1 = Anula Documento de Referencia
			//2 = Corrige el Texto de Referencia
			//3 = Corrige el Monto de le Referencia 
			$CR					= $result_dte[0]['COD_TIPO_NOTA_CREDITO'];						//41 Código de Referencia
			$RER				= $result_dte[0]['REFERENCIA'];									//42 Razón explícita de la referencia

		   	//datos que hacen referencia al documento NC - FA
		   	
			if($FR != ''){
				if($PORC_IVA_FA != 0){ 
					//38 Tipo documento referencia
					$TDR = 33;	//La Nota Credito hace referencia a una FACTURA AFECTA
				}else{
					//38 Tipo documento referencia
					$TDR = 34;	//La Nota Credito hace referencia a una FACTURA EXENTA
				}
			}else{
				//41 Código de Referencia
				$CR = '';
				//38 Tipo documento referencia
				$TDR = '';	//La Nota Credito No hace referencia a una ningun Documento.
			}

			
			//GENERA EL NOMBRE DEL ARCHIVO
			$TIPO_FACT = 61;	//NOTA_CREDITO

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
			
			//Asignando espacios en blanco Nota Credito
			//LINEA 3
			$NRO_NOTA_CREDITO	= substr($NRO_NOTA_CREDITO.$space, 0, 10);		// 1 Numero Nota Credito
			$FECHA_NOTA_CREDITO	= substr($FECHA_NOTA_CREDITO.$space, 0, 10);		// 2 Fecha Nota Credito
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
			$NRO_FACTURA	= substr($NRO_FACTURA.$space, 0, 20);//Solicitado a VE por SP
			$GENERA_SALIDA	= substr($GENERA_SALIDA.$space, 0, 30);		//DESPACHADO
			$CANCELADA		= substr($CANCELADA.$space, 0, 30);			//CANCELADO
			$SUBTOTAL		= substr($SUBTOTAL.$space, 0, 18);			//Solicitado a VE por SP "SUBTOTAL"
			$PORC_DSCTO1	= substr($PORC_DSCTO1.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO1"
			$PORC_DSCTO2	= substr($PORC_DSCTO2.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO2"
			$EMISOR_NC		= substr($EMISOR_NC.$space, 0, 50);			//Solicitado a VE por SP "EMISOR_NOTA_CREDITO"
			//LINEA4
			$NOM_COMUNA		= substr($NOM_COMUNA.$space, 0, 20);		//13 Comuna Recepcion
			$NOM_CIUDAD		= substr($NOM_CIUDAD.$space, 0, 20);		//14 Ciudad Recepcion
			$DP				= substr($DP.$space, 0, 60);				//15 Dirección Postal
			$COP			= substr($COP.$space, 0, 20);				//16 Comuna Postal
			$CIP			= substr($CIP.$space, 0, 20);				//17 Ciudad Postal

			//Asignando espacios en blanco Totales de Nota Credito
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
			$OBSERVACIONES = substr($OBSERVACIONES.$space.$space.$space, 0, 250); //si la Nota Credito tiene notas u observaciones
			$TOTAL_EN_PALABRAS = substr($TOTAL_EN_PALABRAS.' PESOS.'.$space.$space, 0, 200);	//Total en palabras: Posterior al campo Notas
			
			$name_archivo = $TIPO_FACT."_NPG_".$RES.".SPF";
			$fname = tempnam("/tmp", $name_archivo);
			$handle = fopen($fname,"w");
			//DATOS DE NOTA_CREDITO A EXPORTAR 
			//linea 1 y 2
			fwrite($handle, "\r\n"); //salto de linea
			fwrite($handle, "\r\n"); //salto de linea
			//linea 3		
			fwrite($handle, ' ');									// 0 space 2
			fwrite($handle, $NRO_NOTA_CREDITO.$this->separador);	// 1 Numero Nota Credito //OK MH	Linea 5
			fwrite($handle, $FECHA_NOTA_CREDITO.$this->separador);	// 2 Fecha Nota Credito	//OK MH	Linea 4
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
			fwrite($handle, $REFERENCIA.$this->separador);			//Referencia de la Nota Credito //OK MH Linea 298
			fwrite($handle, $NRO_FACTURA.$this->separador);			//Solicitado a VE por SP //Pendiente Se debe enviar en la linea 221 tantas veces como guias tenga referenciada la factura
			fwrite($handle, $GENERA_SALIDA.$this->separador);		//DESPACHADO Solicitado a VE por SP //OK MH Linea 297
			fwrite($handle, $CANCELADA.$this->separador);			//CANCELADO Solicitado a VE por SP //OK MH Linea 296
			fwrite($handle, $SUBTOTAL.$this->separador);			//Solicitado a VE por SP "SUBTOTAL" //OK MH Linea 200 Columna 48
			fwrite($handle, $PORC_DSCTO1.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO1" //OK MH Linea 201 Columna 48
			fwrite($handle, $PORC_DSCTO2.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO2" //OK MH Linea 202 Columna 48
			fwrite($handle, $EMISOR_NC.$this->separador);			//Solicitado a VE por SP "EMISOR_NOTA_CREDITO" //OK MH Linea 48
			fwrite($handle, "\r\n"); //salto de linea
			
			//linea 4
			fwrite($handle, ' ');									// 0 space 2
			fwrite($handle, $NOM_COMUNA.$this->separador);			//13 Comuna Recepcion
			fwrite($handle, $NOM_CIUDAD.$this->separador);			//14 Ciudad Recepcion
			fwrite($handle, $DP.$this->separador);					//15 Dirección Postal
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
			fwrite($handle, $OBSERVACIONES.$this->separador);		//si la factura tiene notas u observaciones
			fwrite($handle, $TOTAL_EN_PALABRAS.$this->separador);	//Total en palabras: Posterior al campo Notas
			fwrite($handle, "\r\n"); //salto de linea
			
			//datos de dw_item_nota_credito linea 5 a 34
			for ($i = 0; $i < 30; $i++){
				if($i < $count){
					fwrite($handle, ' '); //0 space 2
					$ORDEN		= $result_dte[$i]['ORDEN'];
					$MODELO		= $result_dte[$i]['COD_PRODUCTO'];
					$NOM_PRODUCTO = substr($result_dte[$i]['NOM_PRODUCTO'], 0, 60);
					$CANTIDAD	= number_format($result_dte[$i]['CANTIDAD'], 1, ',', '');
					$P_UNITARIO	= number_format($result_dte[$i]['PRECIO'], 1, ',', '');
					$TOTAL		= number_format($result_dte[$i]['TOTAL_NC'], 1, ',', '');
					$DESCRIPCION= $MODELO; // se repite el modelo
					$CANTIDAD_DETALLE = $CANTIDAD; // se repite el $CANTIDAD
					$ORDEN = $ORDEN / 10; //ELIMINA EL CERO
					
					//Asignando espacios en blanco dw_item_nota_credito
					$ORDEN		= substr($ORDEN.$space, 0, 2);
					$MODELO		= substr($MODELO.$space, 0, 35);
					$NOM_PRODUCTO= substr($NOM_PRODUCTO.$space, 0, 80);
					$CANTIDAD	= substr($CANTIDAD.$space, 0, 18);
					$P_UNITARIO	= substr($P_UNITARIO.$space, 0, 18);
					$TOTAL		= substr($TOTAL.$space, 0, 18);
					$DESCRIPCION= substr($DESCRIPCION.$space, 0, 59);
					$CANTIDAD_DETALLE = substr($CANTIDAD_DETALLE.$space, 0, 18);

					//DATOS DE ITEM_NOTA_CREDITO A EXPORTAR
					fwrite($handle, $ORDEN.$this->separador);		//31 Número de Línea
					fwrite($handle, $MODELO.$this->separador);		//32 Código item
					fwrite($handle, $NOM_PRODUCTO.$this->separador);//33 Nombre del Item
					fwrite($handle, $CANTIDAD.$this->separador);	//34 Cantidad
					fwrite($handle, $P_UNITARIO.$this->separador);	//35 Precio Unitario
					fwrite($handle, $TOTAL.$this->separador);		//36 Valor por linea de detalle
					fwrite($handle, $DESCRIPCION.$this->separador);	//37 personalizados Zona Detalles(Modelo ítem)
					fwrite($handle, $CANTIDAD_DETALLE.$this->separador);	//personalizados Zona Detalles SE REPITE $CANTIDAD
				}
				fwrite($handle, "\r\n");
			}
			
			//Linea 35 a 44	Referencia
			//$count_NV = 1;
			for($i = 0; $i < 1; $i++){
				fwrite($handle, ' '); //0 space 2			
					//Asignando espacios en blanco Referencia
					$TDR	= substr($TDR.$space, 0, 3);
					$FR		= substr($FR.$space, 0, 18);
					$FECHA_R= substr($FECHA_R.$space, 0, 10);
					$CR		= substr($CR.$space, 0, 1);
					$RER	= substr($RER.$space, 0, 100);					
					
					fwrite($handle, $TDR.$this->separador);			//38 Tipo documento referencia
					fwrite($handle, $FR.$this->separador);			//39 Folio Referencia
					fwrite($handle, $FECHA_R.$this->separador);		//40 Fecha de Referencia
					fwrite($handle, $CR.$this->separador);			//41 Código de Referencia
					fwrite($handle, $RER.$this->separador);			//42 Razón explícita de la referencia
				fwrite($handle, "\r\n");
			}
			/*fclose($handle);
			header("Content-Type: application/x-msexcel; name=\"$name_archivo\"");
			header("Content-Disposition: inline; filename=\"$name_archivo\"");
			$fh=fopen($fname, "rb");
			fpassthru($fh);*/

			$upload = $this->Envia_DTE($name_archivo, $fname);
			$NRO_NOTA_CREDITO = trim($NRO_NOTA_CREDITO); 
			if (!$upload) {
				$this->_load_record();
				$this->alert('No se pudo enviar Nota Credito Electronica Nº '.$NRO_NOTA_CREDITO.', Por favor contacte a IntegraSystem.');								
			}else{
				$this->_load_record();
				$this->alert('Gestión Realizada con exíto. Nota Credito Electronica Nº '.$NRO_NOTA_CREDITO.'.');								
			}
			unlink($fname);
		}else{
			$db->ROLLBACK_TRANSACTION();
			return false;
		}
		$this->unlock_record();
   	}
   	
	function envia_NC_Electronica2(){
		if (!$this->lock_record())
			return false;
   		$this->sepa_decimales	= ',';	//Usar , como separador de decimales
		$this->vacio 			= ' ';	//Usar rellenos de blanco, CAMPO ALFANUMERICO
		$this->llena_cero		= 0;	//Usar rellenos con '0', CAMPO NUMERICO
		$this->separador		= ';';	//Usar ; como separador de campos
		$cod_nota_credito = $this->get_key();
		$cod_usuario_impresion = $this->cod_usuario;
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$cod_impresora_dte = $_POST['wi_impresora_dte'];
		if($cod_impresora_dte == 100){
		$EMISOR_NC = 'SALA VENTA';
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
		$EMISOR_NC = $result[0]['NOM_USUARIO'] ;
		}
		
		$db->BEGIN_TRANSACTION();
		$sp = 'spu_nota_credito';
		$param = "'ENVIA_DTE', $cod_nota_credito, $cod_usuario_impresion";
				
		if ($db->EXECUTE_SP($sp, $param)) {
			$db->COMMIT_TRANSACTION();	
				
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			//declrar constante para que el monto con iva del reporte lo transpforme a palabras
			$sql = "select TOTAL_CON_IVA from NOTA_CREDITO where COD_NOTA_CREDITO = $cod_nota_credito";
			
			$resultado = $db->build_results($sql);
			$total_con_iva = $resultado [0] ['TOTAL_CON_IVA'] ;
			$total_en_palabras =  Numbers_Words::toWords($total_con_iva,"es");
			$total_en_palabras = strtr($total_en_palabras, "áéíóú", "aeiou");
			$total_en_palabras = strtoupper($total_en_palabras);
	
	   		$sql_dte= "SELECT	NC.COD_NOTA_CREDITO,
								NC.NRO_NOTA_CREDITO,
								dbo.f_emp_get_mail_cargo_persona(NC.COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA,
								dbo.f_format_date(NC.FECHA_NOTA_CREDITO,1)FECHA_NOTA_CREDITO,
								NC.COD_USUARIO_IMPRESION,
								NC.REFERENCIA,
								NC.NOM_EMPRESA,
								NC.COD_TIPO_NOTA_CREDITO,
								NC.GIRO,
								NC.RUT,
								NC.DIG_VERIF,
								NC.DIRECCION,
								NC.TELEFONO,
								NC.FAX,
								NC.SUBTOTAL,
								NC.PORC_DSCTO1,
								NC.MONTO_DSCTO1,
								NC.PORC_DSCTO2,
								NC.MONTO_DSCTO2,
								NC.MONTO_DSCTO1 + NC.MONTO_DSCTO2 TOTAL_DSCTO,
								NC.TOTAL_NETO,
								NC.PORC_IVA,
								NC.MONTO_IVA,
								NC.TOTAL_CON_IVA,
								COM.NOM_COMUNA,
								CIU.NOM_CIUDAD,
								ITNC.ITEM,
								ITNC.CANTIDAD,
								ITNC.COD_PRODUCTO,
								ITNC.NOM_PRODUCTO,
								ITNC.PRECIO,
								ITNC.PRECIO * ITNC.CANTIDAD TOTAL_NC,								 
								'".$total_en_palabras."' TOTAL_EN_PALABRAS,
								convert(varchar(5), GETDATE(), 8) HORA,
								FA.NRO_FACTURA,
								FA.PORC_IVA PORC_IVA_FA,
								dbo.f_format_date(FA.FECHA_FACTURA,1) FECHA_FACTURA,
								'$EMISOR_NC' NOM_USUARIO,
								ITNC.ORDEN,
								NC.OBS
						FROM 	NOTA_CREDITO NC LEFT OUTER JOIN FACTURA FA ON NC.COD_DOC = FA.COD_FACTURA 
												LEFT OUTER JOIN COMUNA COM ON COM.COD_COMUNA = NC.COD_COMUNA
												LEFT OUTER JOIN CIUDAD CIU ON CIU.COD_CIUDAD = NC.COD_CIUDAD
								, ITEM_NOTA_CREDITO ITNC, USUARIO U											
						WHERE 	NC.COD_NOTA_CREDITO = $cod_nota_credito
						and NC.COD_USUARIO = U.COD_USUARIO
						AND		ITNC.COD_NOTA_CREDITO = NC.COD_NOTA_CREDITO";

			$result_dte = $db->build_results($sql_dte);
			//CANTIDAD DE ITEM_NOTA_CREDITO 
			$count = count($result_dte);

			// datos de Nota Credito
			$NRO_NOTA_CREDITO	= $result_dte[0]['NRO_NOTA_CREDITO'] ;			// 1 Numero Nota Credito
			$FECHA_NOTA_CREDITO	= $result_dte[0]['FECHA_NOTA_CREDITO'] ;		// 2 Fecha Nota Credito
			//Email - VE: =>En el caso de las Nota Credito y otros documentos, no aplica por lo que se dejan 0;0 
			$TD					= $this->llena_cero;					// 3 Tipo Despacho
			$TT					= $this->llena_cero;					// 4 Tipo Traslado
			//Email - VE: => 
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
			$REFERENCIA			= $result_dte[0]['REFERENCIA'];			//12 Referencia de la Nota Credito  //datos olvidado por VE.
			$NRO_FACTURA		= $result_dte[0]['NRO_FACTURA'];		//Solicitado a VE por SP
			$GENERA_SALIDA		= $this->vacio;							//Solicitado a VE por SP "DESPACHADO"
			$CANCELADA			= $this->vacio;							//Solicitado a VE por SP "CANCELADO"
			$SUBTOTAL			= number_format($result_dte[0]['SUBTOTAL'], 1, ',', '');	//Solicitado a VE por SP "SUBTOTAL"
			$PORC_DSCTO1		= number_format($result_dte[0]['PORC_DSCTO1'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO1"
			$PORC_DSCTO2		= number_format($result_dte[0]['PORC_DSCTO2'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO2"
			$EMISOR_NC			= $result_dte[0]['NOM_USUARIO'];		//Solicitado a VE por SP "EMISOR_NOTA_CREDITO"
			$NOM_COMUNA			= $result_dte[0]['NOM_COMUNA'];			//13 Comuna Recepcion
			$NOM_CIUDAD			= $result_dte[0]['NOM_CIUDAD'];			//14 Ciudad Recepcion
			$DP					= $result_dte[0]['DIRECCION'];			//15 Dirección Postal
			$COP				= $result_dte[0]['NOM_COMUNA'];			//16 Comuna Postal
			$CIP				= $result_dte[0]['NOM_CIUDAD'];			//17 Ciudad Postal
			
			//DATOS DE TOTALES number_format($result_dte[$i]['TOTAL_FA'], 0, ',', '.');
			$TOTAL_NETO			= number_format($result_dte[0]['TOTAL_NETO'], 1, ',', '');		//18 Monto Neto
			$PORC_IVA			= number_format($result_dte[0]['PORC_IVA'], 1, ',', '');		//19 Tasa IVA
			$MONTO_IVA			= number_format($result_dte[0]['MONTO_IVA'], 1, ',', '');		//20 Monto IVA
			$TOTAL_CON_IVA		= number_format($result_dte[0]['TOTAL_CON_IVA'], 1, ',', '');	//21 Monto Total
			$D1					= 'D';															//22 Tipo de Mov 1 (Desc/Rec)
			$P1					= '$';															//23 Tipo de valor de Desc/Rec 1
			$MONTO_DSCTO1		= number_format($result_dte[0]['MONTO_DSCTO1'], 1, ',', '');	//24 Valor del Desc/Rec 1
			$D2					= 'D';															//25 Tipo de Mov 2 (Desc/Rec)
			$P2					= '$';															//26 Tipo de valor de Desc/Rec 2
			$MONTO_DSCTO2		= number_format($result_dte[0]['MONTO_DSCTO2'], 1, ',', '');	//27 Valor del Desc/Rec 2
			$D3					= 'D';															//28 Tipo de Mov 3 (Desc/Rec)
			$P3					= '$';															//29 Tipo de valor de Desc/Rec 3
			$MONTO_DSCTO3		= '';															//30 Valor del Desc/Rec 3
			$NOM_FORMA_PAGO		= $this->vacio;													//Dato Especial forma de pago adicional
			$NRO_ORDEN_COMPRA	= $this->vacio;													//Numero de Orden Pago
			$NRO_NOTA_VENTA		= $result_dte[0]['NRO_FACTURA'];									//Numero de Nota Venta
			$OBSERVACIONES		= $result_dte[0]['OBS'];										//si la Nota Credito tiene notas u observaciones
			$OBSERVACIONES		=  eregi_replace("[\n|\r|\n\r]", ' ', $OBSERVACIONES); 			//elimina los saltos de linea. entre otros caracteres
			$TOTAL_EN_PALABRAS	= $result_dte[0]['TOTAL_EN_PALABRAS'];							//Total en palabras: Posterior al campo Notas
   			$PORC_IVA_FA		= number_format($result_dte[0]['PORC_IVA_FA'], 1, ',', '');		//Tasa IVA Factura
			
			//datos que hacen referencia al documento NC - FA
			//Numero de Factura o Documento que hace referencia
			$FR					= $result_dte[0]['NRO_FACTURA'];								//39 Folio Referencia
			$FECHA_R			= $result_dte[0]['FECHA_FACTURA'];								//40 Fecha de Referencia
			//1 = Anula Documento de Referencia
			//2 = Corrige el Texto de Referencia
			//3 = Corrige el Monto de le Referencia 
			$CR					= $result_dte[0]['COD_TIPO_NOTA_CREDITO'];						//41 Código de Referencia
			$RER				= $result_dte[0]['REFERENCIA'];									//42 Razón explícita de la referencia

		   	//datos que hacen referencia al documento NC - FA
		   	
			if($FR != ''){
				if($PORC_IVA_FA != 0){ 
					//38 Tipo documento referencia
					$TDR = 33;	//La Nota Credito hace referencia a una FACTURA AFECTA
				}else{
					//38 Tipo documento referencia
					$TDR = 34;	//La Nota Credito hace referencia a una FACTURA EXENTA
				}
			}else{
				//41 Código de Referencia
				$CR = '';
				//38 Tipo documento referencia
				$TDR = '';	//La Nota Credito No hace referencia a una ningun Documento.
			}

			
			//GENERA EL NOMBRE DEL ARCHIVO
			$TIPO_FACT = 61;	//NOTA_CREDITO

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
			
			//Asignando espacios en blanco Nota Credito
			//LINEA 3
			
			$NRO_NOTA_CREDITO	= substr($NRO_NOTA_CREDITO, 0, 10);		// 1 Numero Nota Credito
			$FECHA_NOTA_CREDITO	= substr($FECHA_NOTA_CREDITO, 0, 10);		// 2 Fecha Nota Credito
			$TD				= substr($TD, 0, 1);					// 3 Tipo Despacho
			$TT				= substr($TT, 0, 1);					// 4 Tipo Traslado
			$PAGO_DTE		= substr($PAGO_DTE, 0, 1);			// 5 Forma de Pago
			$FV				= substr($FV, 0, 10);				// 6 Fecha Vencimiento
			$RUT_EMPRESA	= substr($RUT_EMPRESA, 0, 10);		// 7 Rut Empresa
			$NOM_EMPRESA	= substr($NOM_EMPRESA, 0, 100);		// 8 Razol Social_Nombre Empresa
			$GIRO			= substr($GIRO, 0, 40);				// 9 Giro Empresa
			$DIRECCION		= substr($DIRECCION, 0, 60);			//10 Direccion empresa
			$MAIL_CARGO_PERSONA = substr($MAIL_CARGO_PERSONA, 0, 60);//11 E-Mail Contacto
			$TELEFONO		= substr($TELEFONO, 0, 15);			//12 Telefono Empresa
			$REFERENCIA		= substr($REFERENCIA, 0, 80);
			$NRO_FACTURA	= substr($NRO_FACTURA, 0, 20);//Solicitado a VE por SP
			$GENERA_SALIDA	= substr($GENERA_SALIDA, 0, 30);		//DESPACHADO
			$CANCELADA		= substr($CANCELADA, 0, 30);			//CANCELADO
			$SUBTOTAL		= substr($SUBTOTAL, 0, 18);			//Solicitado a VE por SP "SUBTOTAL"
			$PORC_DSCTO1	= substr($PORC_DSCTO1, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO1"
			$PORC_DSCTO2	= substr($PORC_DSCTO2, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO2"
			$EMISOR_NC		= substr($EMISOR_NC, 0, 50);			//Solicitado a VE por SP "EMISOR_NOTA_CREDITO"
			//LINEA4
			$NOM_COMUNA		= substr($NOM_COMUNA, 0, 20);		//13 Comuna Recepcion
			$NOM_CIUDAD		= substr($NOM_CIUDAD, 0, 20);		//14 Ciudad Recepcion
			$DP				= substr($DP, 0, 60);				//15 Dirección Postal
			$COP			= substr($COP, 0, 20);				//16 Comuna Postal
			$CIP			= substr($CIP, 0, 20);				//17 Ciudad Postal

			//Asignando espacios en blanco Totales de Nota Credito
			$TOTAL_NETO		= substr($TOTAL_NETO, 0, 18);		//18 Monto Neto
			$PORC_IVA		= substr($PORC_IVA, 0, 5);			//19 Tasa IVA
			$MONTO_IVA		= substr($MONTO_IVA, 0, 18);			//20 Monto IVA
			$TOTAL_CON_IVA	= substr($TOTAL_CON_IVA, 0, 18);		//21 Monto Total
			$D1				= substr($D1, 0, 1);					//22 Tipo de Mov 1 (Desc/Rec)
			$P1				= substr($P1, 0, 1);					//23 Tipo de valor de Desc/Rec 1
			$MONTO_DSCTO1	= substr($MONTO_DSCTO1, 0, 18);		//24 Valor del Desc/Rec 1
			$D2				= substr($D2, 0, 1);					//25 Tipo de Mov 2 (Desc/Rec)
			$P2				= substr($P2, 0, 1);					//26 Tipo de valor de Desc/Rec 2
			$MONTO_DSCTO2	= substr($MONTO_DSCTO2, 0, 18);		//27 Valor del Desc/Rec 2
			$D3				= substr($D3, 0, 1);					//28 Tipo de Mov 3 (Desc/Rec)
			$P3				= substr($P3, 0, 1);					//29 Tipo de valor de Desc/Rec 3
			$MONTO_DSCTO3	= substr($MONTO_DSCTO3, 0, 18);		//30 Valor del Desc/Rec 3
			$NOM_FORMA_PAGO = substr($NOM_FORMA_PAGO, 0, 80);	//Dato Especial forma de pago adicional
			$NRO_ORDEN_COMPRA= substr($NRO_ORDEN_COMPRA, 0, 20);	//Numero de Orden Pago
			$NRO_NOTA_VENTA = substr($NRO_NOTA_VENTA, 0, 20);	//Numero de Nota Venta
			$OBSERVACIONES = substr($OBSERVACIONES, 0, 250); //si la Nota Credito tiene notas u observaciones
			$TOTAL_EN_PALABRAS = substr($TOTAL_EN_PALABRAS.' PESOS.', 0, 200);	//Total en palabras: Posterior al campo Notas
			
			$name_archivo = $TIPO_FACT."_NPG_".$RES.".SPF";
			$fname = tempnam("/tmp", $name_archivo);
			$handle = fopen($fname,"w");
			//DATOS DE NOTA_CREDITO A EXPORTAR 
			fwrite($handle, 'XXX INICIO DOCUMENTO');
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA IDENTIFICACION DEL DOCUMENTO');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Documento Tributario Electronico'));
				fwrite($handle, space($TIPO_FACT, 3)); 
				fwrite($handle, "\r\n");
				fwrite($handle, space('Folio Documento'));
				fwrite($handle, space($NRO_NOTA_CREDITO, 10));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Emisión'));
				fwrite($handle, space($FECHA_NOTA_CREDITO, 10)); 
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador No rebaja'));	//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo de Despacho')); // Solo para guía despacho
				fwrite($handle, $TD);
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador de Traslado '));
				fwrite($handle, $TT);
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Impresión'));//
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
				fwrite($handle, space('Forma de Pago Exportación'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Cancelación'));//
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
				fwrite($handle, space('Días del Termino de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha Vencimiento'));
				fwrite($handle, space($FV, 10));
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA EMISOR');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Rut Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Razón Social Emisor'));//
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
				fwrite($handle, space('Dirección de Origen Emisor'));
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
				fwrite($handle, space('Codigo Vendedor'));
				fwrite($handle, space($EMISOR_NC, 60));
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
				fwrite($handle, space('Nombre o Razón Social Receptor'));
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
				fwrite($handle, space('Dirección Receptor'));
				fwrite($handle, space($DIRECCION, 70));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Comuna Receptor'));
				fwrite($handle, space($NOM_COMUNA, 20));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Ciudad Receptor'));
				fwrite($handle, space($NOM_CIUDAD, 20));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Dirección Postal Receptor'));
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
				fwrite($handle, space('Dirección Destino'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Comuna Destino'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Ciudad Destino'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Modalidad De Ventas'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Clausula de Venta Exportación'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Total Clausula de Venta Exportacion'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Via de Transporte'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre del Medio de Transporte'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('RUT Compañía de Transporte'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre Compañía de Transporte'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Identificacion Adicional Compañía de Transporte'));//
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
				fwrite($handle, space('Tipo Moneda Transacción'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Neto'));
				fwrite($handle, space($TOTAL_NETO, 18));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Exento'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Base Faenamiento de Carne'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Base de Margen de  Comercialización'));//
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
				fwrite($handle, space('Crédito Especial Emp. Constructoras'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Garantía por Deposito de Envases'));//
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
							fwrite($handle, ' '); //Indicador Exención
							fwrite($handle, space('', 3));	//Tipo Doc. Liquidación
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
							fwrite($handle, space('', 20));	//Ubicación del Evento
							fwrite($handle, space('', 3));	//Fila Ubicación
							fwrite($handle, space('', 3));	//Asiento Ubicación
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
							fwrite($handle, space($P_UNITARIO, 18));//Precio Unitario
							fwrite($handle, space('', 10));	//Fecha Elaboración Item
							fwrite($handle, space('', 10));	//Fecha de Vencimiento 
							fwrite($handle, space('', 4));	//Unidad de Medida
							fwrite($handle, space('', 18));	//Precio Otra Moneda
							fwrite($handle, space('', 3));	//Moneda
							fwrite($handle, space('', 10));	//Factor de Conversión 
							fwrite($handle, space('', 18));	//Descuento Otra Moneda
							fwrite($handle, space('', 18));	//Recargo Otra Moneda
							fwrite($handle, space('', 18));	//Valor Item Otra Moneda
							fwrite($handle, space('', 18));	//Precio Otra Moneda
							fwrite($handle, space('', 3));	//Moneda
							fwrite($handle, space('', 10));	//Factor de Conversión 
							fwrite($handle, space('', 18));	//Descuento Otra Moneda
							fwrite($handle, space('', 18));	//Recargo Otra Moneda
							fwrite($handle, space('', 18));	//Valor Item Otra Moneda
							fwrite($handle, space('', 5));	//Porcentaje de Descuento
							fwrite($handle, space('', 18));	//Monto de Descuento
							fwrite($handle, ' ');			//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');			//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');			//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');			//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');			//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, space('', 5));	//Porcentaje de Recargo
							fwrite($handle, space('', 18));	//Monto de Recargo
							fwrite($handle, ' ');			//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo
							fwrite($handle, ' ');			//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo 
							fwrite($handle, ' ');			//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo
							fwrite($handle, ' ');			//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo
							fwrite($handle, ' ');			//Tipo Recargo
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
							fwrite($handle, space('', 5000));	//Descripción Extendida
							
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
				fwrite($handle, " ");
				fwrite($handle, space("SUBTOTAL", 45));
				fwrite($handle, " ");
				fwrite($handle, space($SUBTOTAL, 18));
				fwrite($handle, " ");
				fwrite($handle, "\r\n");
				// Descuento 1
				fwrite($handle, " ");
				fwrite($handle, space($PORC_DSCTO1."% "."DESCUENTO", 45));
				fwrite($handle, " ");
				fwrite($handle, space($MONTO_DSCTO1, 18));
				fwrite($handle, " ");
				fwrite($handle, "\r\n");
				// Descuento 2
				fwrite($handle, " ");
				fwrite($handle, space($PORC_DSCTO2."% "."DESCUENTO ADIC.", 45));
				fwrite($handle, " ");
				fwrite($handle, space($MONTO_DSCTO2, 18));
				fwrite($handle, " ");
				$j = 0;
				while($j < 18){
					fwrite($handle, "\r\n");
					$j++;
				}
				fwrite($handle, '========== INFORMACION DE REFERENCIA'); //Revisar con MH
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
				fwrite($handle, space($REFERENCIA, 49));
				fwrite($handle, "\r\n");
				fwrite($handle, 'XXX FIN DOCUMENTO');
			//linea 3
			/*		
			fwrite($handle, ' ');									// 0 space 2
			fwrite($handle, $NRO_NOTA_CREDITO.$this->separador);			// 1 Numero Nota Credito
			fwrite($handle, $FECHA_NOTA_CREDITO.$this->separador);		// 2 Fecha Nota Credito
			fwrite($handle, $TD.$this->separador);					// 3 Tipo Despacho
			fwrite($handle, $TT.$this->separador);					// 4 Tipo Traslado
			fwrite($handle, $PAGO_DTE.$this->separador);			// 5 Forma de Pago
			fwrite($handle, $FV.$this->separador);					// 6 Fecha Vencimiento
			fwrite($handle, $RUT_EMPRESA.$this->separador);			// 7 Rut Empresa
			fwrite($handle, $NOM_EMPRESA.$this->separador);			// 8 Razol Social_Nombre Empresa
			fwrite($handle, $GIRO.$this->separador);				// 9 Giro Empresa
			fwrite($handle, $DIRECCION.$this->separador);			//10 Direccion empresa
			//Personalizados Linea 3
			fwrite($handle, $MAIL_CARGO_PERSONA.$this->separador);	//11 E-Mail Contacto 
			fwrite($handle, $TELEFONO.$this->separador);			//12 Telefono Empresa
			fwrite($handle, $REFERENCIA.$this->separador);			//Referencia de la Nota Credito
			fwrite($handle, $NRO_FACTURA.$this->separador);	//Solicitado a VE por SP
			fwrite($handle, $GENERA_SALIDA.$this->separador);		//DESPACHADO Solicitado a VE por SP
			fwrite($handle, $CANCELADA.$this->separador);			//CANCELADO Solicitado a VE por SP
			fwrite($handle, $SUBTOTAL.$this->separador);			//Solicitado a VE por SP "SUBTOTAL"
			fwrite($handle, $PORC_DSCTO1.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO1"
			fwrite($handle, $PORC_DSCTO2.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO2"
			fwrite($handle, $EMISOR_NC.$this->separador);			//Solicitado a VE por SP "EMISOR_NOTA_CREDITO"
			fwrite($handle, "\r\n"); //salto de linea
			
			//linea 4
			fwrite($handle, ' ');									// 0 space 2
			fwrite($handle, $NOM_COMUNA.$this->separador);			//13 Comuna Recepcion
			fwrite($handle, $NOM_CIUDAD.$this->separador);			//14 Ciudad Recepcion
			fwrite($handle, $DP.$this->separador);					//15 Dirección Postal
			fwrite($handle, $COP.$this->separador);					//16 Comuna Postal
			fwrite($handle, $CIP.$this->separador);					//17 Ciudad Postal
			fwrite($handle, $TOTAL_NETO.$this->separador);			//18 Monto Neto
			fwrite($handle, $PORC_IVA.$this->separador);			//19 Tasa IVA
			fwrite($handle, $MONTO_IVA.$this->separador);			//20 Monto IVA
			fwrite($handle, $TOTAL_CON_IVA.$this->separador);		//21 Monto Total
			fwrite($handle, $D1.$this->separador);					//22 Tipo de Mov 1 (Desc/Rec)
			fwrite($handle, $P1.$this->separador);					//23 Tipo de valor de Desc/Rec 1
			fwrite($handle, $MONTO_DSCTO1.$this->separador);		//24 Valor del Desc/Rec 1
			fwrite($handle, $D2.$this->separador);					//25 Tipo de Mov 2 (Desc/Rec)
			fwrite($handle, $P2.$this->separador);					//26 Tipo de valor de Desc/Rec 2
			fwrite($handle, $MONTO_DSCTO2.$this->separador);		//27 Valor del Desc/Rec 2
			fwrite($handle, $D3.$this->separador);					//28 Tipo de Mov 3 (Desc/Rec)
			fwrite($handle, $P3.$this->separador);					//29 Tipo de valor de Desc/Rec 3			
			fwrite($handle, $MONTO_DSCTO3.$this->separador);		//30 Valor del Desc/Rec 2
			fwrite($handle, $NOM_FORMA_PAGO.$this->separador);		//Dato Especial forma de pago adicional
			fwrite($handle, $NRO_ORDEN_COMPRA.$this->separador);	//Numero de Orden Pago
			fwrite($handle, $NRO_NOTA_VENTA.$this->separador);		//Numero de Nota Venta
			fwrite($handle, $OBSERVACIONES.$this->separador);		//si la Nota Credito tiene notas u observaciones
			fwrite($handle, $TOTAL_EN_PALABRAS.$this->separador);	//Total en palabras: Posterior al campo Notas
			fwrite($handle, "\r\n"); //salto de linea
			
			//datos de dw_item_nota_credito linea 5 a 34
			for ($i = 0; $i < 30; $i++){
				if($i < $count){
					fwrite($handle, ' '); //0 space 2
					$ORDEN		= $result_dte[$i]['ORDEN'];
					$MODELO		= $result_dte[$i]['COD_PRODUCTO'];
					$NOM_PRODUCTO = $result_dte[$i]['NOM_PRODUCTO'];
					$CANTIDAD	= number_format($result_dte[$i]['CANTIDAD'], 1, ',', '');
					$P_UNITARIO	= number_format($result_dte[$i]['PRECIO'], 1, ',', '');
					$TOTAL		= number_format($result_dte[$i]['TOTAL_NC'], 1, ',', '');
					$DESCRIPCION= $MODELO; // se repite el modelo
					$CANTIDAD_DETALLE = $CANTIDAD; // se repite el $CANTIDAD
					$ORDEN = $ORDEN / 10; //ELIMINA EL CERO
					
					//Asignando espacios en blanco dw_item_nota_credito
					$ORDEN		= substr($ORDEN.$space, 0, 2);
					$MODELO		= substr($MODELO.$space, 0, 35);
					$NOM_PRODUCTO= substr($NOM_PRODUCTO.$space, 0, 80);
					$CANTIDAD	= substr($CANTIDAD.$space, 0, 18);
					$P_UNITARIO	= substr($P_UNITARIO.$space, 0, 18);
					$TOTAL		= substr($TOTAL.$space, 0, 18);
					$DESCRIPCION= substr($DESCRIPCION.$space, 0, 59);
					$CANTIDAD_DETALLE = substr($CANTIDAD_DETALLE.$space, 0, 18);

					//DATOS DE ITEM_NOTA_CREDITO A EXPORTAR
					fwrite($handle, $ORDEN.$this->separador);		//31 Número de Línea
					fwrite($handle, $MODELO.$this->separador);		//32 Código item
					fwrite($handle, $NOM_PRODUCTO.$this->separador);//33 Nombre del Item
					fwrite($handle, $CANTIDAD.$this->separador);	//34 Cantidad
					fwrite($handle, $P_UNITARIO.$this->separador);	//35 Precio Unitario
					fwrite($handle, $TOTAL.$this->separador);		//36 Valor por linea de detalle
					fwrite($handle, $DESCRIPCION.$this->separador);	//37 personalizados Zona Detalles(Modelo ítem)
					fwrite($handle, $CANTIDAD_DETALLE.$this->separador);	//personalizados Zona Detalles SE REPITE $CANTIDAD
				}
				fwrite($handle, "\r\n");
			}
			
			//Linea 35 a 44	Referencia
			//$count_NV = 1;
			for($i = 0; $i < 1; $i++){
				fwrite($handle, ' '); //0 space 2			
					//Asignando espacios en blanco Referencia
					$TDR	= substr($TDR.$space, 0, 3);
					$FR		= substr($FR.$space, 0, 18);
					$FECHA_R= substr($FECHA_R.$space, 0, 10);
					$CR		= substr($CR.$space, 0, 1);
					$RER	= substr($RER.$space, 0, 100);					
					
					fwrite($handle, $TDR.$this->separador);			//38 Tipo documento referencia
					fwrite($handle, $FR.$this->separador);			//39 Folio Referencia
					fwrite($handle, $FECHA_R.$this->separador);		//40 Fecha de Referencia
					fwrite($handle, $CR.$this->separador);			//41 Código de Referencia
					fwrite($handle, $RER.$this->separador);			//42 Razón explícita de la referencia
				fwrite($handle, "\r\n");
			}
			/*fclose($handle);
			header("Content-Type: application/x-msexcel; name=\"$name_archivo\"");
			header("Content-Disposition: inline; filename=\"$name_archivo\"");
			$fh=fopen($fname, "rb");
			fpassthru($fh);*/

			$upload = $this->Envia_DTE($name_archivo, $fname);
			$NRO_NOTA_CREDITO = trim($NRO_NOTA_CREDITO); 
			if (!$upload) {
				$this->_load_record();
				$this->alert('No se pudo enviar Nota Credito Electronica Nº '.$NRO_NOTA_CREDITO.', Por favor contacte a IntegraSystem.');								
			}else{
				$this->_load_record();
				$this->alert('Gestión Realizada con exíto. Nota Credito Electronica Nº '.$NRO_NOTA_CREDITO.'.');								
			}
			unlink($fname);
		}else{
			$db->ROLLBACK_TRANSACTION();
			return false;
		}
		$this->unlock_record();
   	}

	function procesa_event() {		
		if(isset($_POST['b_print_dte_x']))
			$this->envia_NC_Electronica();
		else
			parent::procesa_event();
	}
}


class print_nota_credito_base extends reporte {	
	function print_nota_credito_base($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	
	function modifica_pdf(&$pdf) {
		$pdf->AutoPageBreak=false;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		$count = count($result);

		$fecha = $result[0]['FECHA_NOTA_CREDITO'];		
		// CABECERA		
		$cod_nota_credito = $result[0]['COD_NOTA_CREDITO'];		
		$nro_nota_credito = $result[0]['NRO_NOTA_CREDITO'];
		$nom_empresa = $result[0]['NOM_EMPRESA'];
		$rut = number_format($result[0]['RUT'], 0, ',', '.')."-".$result[0]['DIG_VERIF'];
		$direccion = $result[0]['DIRECCION'];
		$comuna = $result[0]['NOM_COMUNA'];		
		$ciudad = $result[0]['NOM_CIUDAD'];		
		$giro = $result[0]['GIRO'];
		$fono = $result[0]['TELEFONO'];
		$total_en_palabras = $result[0]['TOTAL_EN_PALABRAS'];
		$subtotal = number_format($result[0]['SUBTOTAL'], 0, ',', '.');
		$porc_dscto1 = number_format($result[0]['PORC_DSCTO1'], 1, ',', '.');
		$monto_dscto1 = number_format($result[0]['MONTO_DSCTO1'], 0, ',', '.');
		$porc_dscto2 = number_format($result[0]['PORC_DSCTO2'], 1, ',', '.');
		$monto_dscto2 = number_format($result[0]['MONTO_DSCTO2'], 0, ',', '.');
		$total_dscto = number_format($result[0]['TOTAL_DSCTO'], 0, ',', '.');
		$neto = number_format($result[0]['TOTAL_NETO'], 0, ',', '.');
		$porc_iva = number_format($result[0]['PORC_IVA'], 0, ',', '.');
		$monto_iva = number_format($result[0]['MONTO_IVA'], 0, ',', '.');
		$total_con_iva = number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.');
		$REFERENCIA	= 'REF: '.substr ($result[0]['REFERENCIA'], 0, 68);
		$NRO_FACTURA	= $result[0]['NRO_FACTURA'];
		$INI_USUARIO	= $result[0]['INI_USUARIO'];

		// DIBUJANDO LA CABECERA	
		$pdf->SetFont('Arial','',11);
		$pdf->Text(83, 143,$fecha);
		$pdf->SetFont('Arial','',8);
		$pdf->Text(433, 117, $nro_nota_credito);
		$pdf->Text(515, 117, $INI_USUARIO);
		$pdf->SetFont('Arial','',11);
		$pdf->SetXY(83,155);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(250,10,"$nom_empresa");
		$pdf->Text(398, 164, $rut);
		$pdf->SetFont('Arial','',11);
		//$pdf->Text(83, 213, $direccion);
		$pdf->SetXY(80,205);
		$pdf->MultiCell(250,9,"$direccion");	
		
		$pdf->Text(418, 213, $comuna);
		$pdf->Text(68, 236, $ciudad);

		$pdf->SetXY(217, 228);
		$pdf->MultiCell(150,9,"$giro");	

		$pdf->Text(408, 236, $fono);		
		$pdf->SetFont('Arial','B',10);
		$pdf->Text(150, 310, "$REFERENCIA");
		$pdf->SetFont('Arial','',10);
		
		//DIBUJANDO LOS ITEMS DE LA NOTA_CREDITO
		for($i=0; $i<$count; $i++){
			$item = $result[$i]['ITEM'];
			$cantidad = $result[$i]['CANTIDAD'];
			$modelo = $result[$i]['COD_PRODUCTO'];
			$detalle = substr ($result[$i]['NOM_PRODUCTO'], 0, 45);
			$p_unitario = number_format($result[$i]['PRECIO'], 0, ',', '.');
			$total = number_format($result[$i]['TOTAL_NC'], 0, ',', '.');
			// por cada pasada le asigna una nueva posicion	
			$pdf->Text(43, 334+(14*$i), $item);			
			$pdf->Text(73, 334+(14*$i), $cantidad);
			$pdf->Text(103, 334+(14*$i), $modelo);
			
			$pdf->SetXY(158,326+(14*$i));
			$pdf->Cell(4,1,"$detalle");

			$pdf->SetXY(400,329+(14*$i));
			$pdf->MultiCell(80,5, $p_unitario,0, 'R');		
			$pdf->SetXY(467,329+(14*$i));
			$pdf->MultiCell(80,5, $total,0, 'R');
}					
		
		// DIBUJANDO TOTALES
		$pdf->SetFont('Arial','',12);
		$pdf->Text(98, 630, 'Son: '.$total_en_palabras.' pesos.');

		if($total_dscto <> 0){//tiene dscto
			if(($monto_dscto1 <> 0 && $monto_dscto2 == 0) || ($monto_dscto2 <> 0 && $monto_dscto1 == 0)){//solo tiene un DSCTO 1
				$pdf->SetXY(412,649);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
			
				$pdf->SetXY(444,649);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$subtotal,0, 'R');

				if($monto_dscto1 <> 0){
					$pdf->SetXY(412,664);
					$pdf->SetFont('Arial','',9);
					$pdf->MultiCell(80,4,$porc_dscto1.' % DSCTO $ ',0, 'R');

					$pdf->SetXY(444,664);
					$pdf->SetFont('Arial','B',11);
					$pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');
				}
				else{
					$pdf->SetXY(412,664);
					$pdf->SetFont('Arial','',9);
					$pdf->MultiCell(80,4,$porc_dscto2.' % DSCTO $ ',0, 'R');

					$pdf->SetXY(444,664);
					$pdf->SetFont('Arial','B',11);
					$pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');				
				}				
			}else if($monto_dscto1 <> 0 && $monto_dscto2 <> 0){//tiene ambos DSCTO

				$pdf->SetXY(412,634);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
			
				$pdf->SetXY(444,634);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$subtotal,0, 'R');

				$pdf->SetXY(412,649);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4,$porc_dscto1.' % DSCTO 1 $ ',0, 'R');

				$pdf->SetXY(444,649);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');	
				
				$pdf->SetXY(412,664);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4,$porc_dscto2.' % DSCTO 2 $ ',0, 'R');

				$pdf->SetXY(444,664);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');	
			}
		}

		$pdf->SetXY(412,679);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(80,4, 'TOTAL NETO  $ ',0, 'R');
		$pdf->SetXY(444,679);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$neto,0, 'R');
		$pdf->SetXY(412,694);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(80,4, $porc_iva.' % IVA  $ ',0, 'R');
		$pdf->SetXY(444,694);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$monto_iva,0, 'R');
		$pdf->Rect( 430, 705, 120, 2, 'f');
		$pdf->SetXY(412,714);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(80,4,'TOTAL  $ ',0, 'R');
		$pdf->SetXY(444,714);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$total_con_iva,0, 'R');	


		//DIBUJANDO PERSONA QUE RETIRA PRODUCTOS 			
		$pdf->SetFont('Arial','',13);
		$pdf->Text(43, 685, $NRO_FACTURA);
		
	}
}

/////////////////////////////////////////////////////////////
// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_nota_credito.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wi_nota_credito extends wi_nota_credito_base {
		function wi_nota_credito($cod_item_menu) {
			parent::wi_nota_credito_base($cod_item_menu); 
		}
	}
	class print_nota_credito extends print_nota_credito_base {	
		function print_nota_credito($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
			parent::print_nota_credito_base($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
		}			
	}
}
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../orden_compra/class_wi_orden_compra.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/barcode/barcode.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/barcode/c128bobject.php");

class wi_orden_compra_arriendo extends wi_orden_compra_base {
	const K_ESTADO_APROBADA			= 4;
	const K_MODIFICAR_OC_AUTORIZADA	= '995505';
	const K_AUTORIZA_APROBACION_OC	= '995510';
	function wi_orden_compra_arriendo($cod_item_menu) {
		parent::wi_orden_compra_base($cod_item_menu);
		$this->nom_tabla = 'orden_compra_arriendo';
		$this->nom_template = "wi_".$this->nom_tabla.".htm";

	}
	function new_record() {
		parent::new_record();
		
		$cod_arriendo = session::get('ORDEN_COMPRA.CREAR_DESDE_ARRIENDO');
		session::un_set('ORDEN_COMPRA.CREAR_DESDE_ARRIENDO');
		$this->dws['dw_orden_compra']->set_item(0, 'COD_DOC', $cod_arriendo);	
	}
	
	function load_record() {
		parent::load_record();
		$COD_ESTADO_ORDEN_COMPRA = $this->dws['dw_orden_compra']->get_item(0, 'COD_ESTADO_ORDEN_COMPRA');

		if ($COD_ESTADO_ORDEN_COMPRA == self::K_ESTADO_EMITIDA) {
			$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_APROBACION_OC, $this->cod_usuario);
			if($priv == 'E')
				$sql = "select 	COD_ESTADO_ORDEN_COMPRA
								,NOM_ESTADO_ORDEN_COMPRA
								,ORDEN
						from ESTADO_ORDEN_COMPRA
						where COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_EMITIDA." or
								COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_ANULADA." or
								 COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_APROBADA."
						order by COD_ESTADO_ORDEN_COMPRA";
			else
				$sql = "select 	COD_ESTADO_ORDEN_COMPRA
								,NOM_ESTADO_ORDEN_COMPRA
								,ORDEN
						from ESTADO_ORDEN_COMPRA		
						where COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_EMITIDA." or
								COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_ANULADA."
						order by COD_ESTADO_ORDEN_COMPRA";	
				
			unset($this->dws['dw_orden_compra']->controls['COD_ESTADO_ORDEN_COMPRA']);
			$this->dws['dw_orden_compra']->add_control($control = new drop_down_dw('COD_ESTADO_ORDEN_COMPRA',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_orden_compra']->controls['NOM_ESTADO_ORDEN_COMPRA']->type = 'hidden';
			$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_ANULACION_OC, $this->cod_usuario);
			if ($priv=='E') {
				$this->dws['dw_orden_compra']->set_entrable('COD_ESTADO_ORDEN_COMPRA', true);
			}
			else {
				$this->dws['dw_orden_compra']->set_entrable('COD_ESTADO_ORDEN_COMPRA', false);
			}

			$this->dws['dw_orden_compra']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));
			
			$this->dws['dw_orden_compra']->set_entrable('COD_USUARIO_SOLICITA', true);
			$this->dws['dw_orden_compra']->set_entrable('NRO_CUENTA_CORRIENTE', true);
			$this->dws['dw_orden_compra']->set_entrable('COD_CUENTA_CORRIENTE', true);
			$this->dws['dw_orden_compra']->set_entrable('REFERENCIA'		  , true);
			$this->dws['dw_orden_compra']->set_entrable('COD_EMPRESA'		  , true);
			$this->dws['dw_orden_compra']->set_entrable('ALIAS'				  , true);
			$this->dws['dw_orden_compra']->set_entrable('RUT'				  , true);
			$this->dws['dw_orden_compra']->set_entrable('NOM_EMPRESA'		  , true);
			$this->dws['dw_orden_compra']->set_entrable('COD_SUCURSAL_FACTURA', true);
			$this->dws['dw_orden_compra']->set_entrable('COD_PERSONA'		  , true);
			$this->dws['dw_orden_compra']->set_entrable('OBS'				  , true);
			$this->dws['dw_orden_compra']->set_entrable('PORC_DSCTO1'	  	  , true);
			$this->dws['dw_orden_compra']->set_entrable('MONTO_DSCTO1'	  	  , true);
			$this->dws['dw_orden_compra']->set_entrable('PORC_IVA'		  	  , true);
			$this->dws['dw_orden_compra']->set_entrable('PORC_DSCTO2'	  	  , true);
			$this->dws['dw_orden_compra']->set_entrable('MONTO_DSCTO2'	  	  , true);
			
			$this->dws['dw_item_orden_compra']->set_entrable_dw(true);
			
				
		}
		if ($COD_ESTADO_ORDEN_COMPRA == self::K_ESTADO_APROBADA) {
			$sql = "select 	COD_ESTADO_ORDEN_COMPRA
							,NOM_ESTADO_ORDEN_COMPRA
							,ORDEN
					from ESTADO_ORDEN_COMPRA
					where COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_APROBADA." or
							COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_ANULADA."
					order by COD_ESTADO_ORDEN_COMPRA";
			
			unset($this->dws['dw_orden_compra']->controls['COD_ESTADO_ORDEN_COMPRA']);
			$this->dws['dw_orden_compra']->add_control($control = new drop_down_dw('COD_ESTADO_ORDEN_COMPRA',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_orden_compra']->controls['NOM_ESTADO_ORDEN_COMPRA']->type = 'hidden';
			$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_ANULACION_OC, $this->cod_usuario);
			if ($priv=='E') {
				$this->dws['dw_orden_compra']->set_entrable('COD_ESTADO_ORDEN_COMPRA', true);
			}
			else {
				$this->dws['dw_orden_compra']->set_entrable('COD_ESTADO_ORDEN_COMPRA', false);
			}
			
			$this->dws['dw_orden_compra']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));
			
			$priv2 = $this->get_privilegio_opcion_usuario(self::K_MODIFICAR_OC_AUTORIZADA, $this->cod_usuario);
			if ($priv2 == 'E'){
				$this->dws['dw_orden_compra']->set_entrable('COD_USUARIO_SOLICITA', true);
				$this->dws['dw_orden_compra']->set_entrable('NRO_CUENTA_CORRIENTE', true);
				$this->dws['dw_orden_compra']->set_entrable('COD_CUENTA_CORRIENTE', true);
				$this->dws['dw_orden_compra']->set_entrable('REFERENCIA'		  , true);
				$this->dws['dw_orden_compra']->set_entrable('COD_EMPRESA'		  , true);
				$this->dws['dw_orden_compra']->set_entrable('ALIAS'				  , true);
				$this->dws['dw_orden_compra']->set_entrable('RUT'				  , true);
				$this->dws['dw_orden_compra']->set_entrable('NOM_EMPRESA'		  , true);
				$this->dws['dw_orden_compra']->set_entrable('COD_SUCURSAL_FACTURA', true);
				$this->dws['dw_orden_compra']->set_entrable('COD_PERSONA'		  , true);
				$this->dws['dw_orden_compra']->set_entrable('OBS'				  , true);
				$this->dws['dw_orden_compra']->set_entrable('PORC_DSCTO1'	  	  , true);
				$this->dws['dw_orden_compra']->set_entrable('MONTO_DSCTO1'	  	  , true);
				$this->dws['dw_orden_compra']->set_entrable('PORC_IVA'		  	  , true);
				$this->dws['dw_orden_compra']->set_entrable('PORC_DSCTO2'	  	  , true);
				$this->dws['dw_orden_compra']->set_entrable('MONTO_DSCTO2'	  	  , true);
				
				$this->dws['dw_item_orden_compra']->set_entrable_dw(true);
			}else{
				$this->dws['dw_orden_compra']->set_entrable('COD_USUARIO_SOLICITA', false);
				$this->dws['dw_orden_compra']->set_entrable('NRO_CUENTA_CORRIENTE', false);
				$this->dws['dw_orden_compra']->set_entrable('COD_CUENTA_CORRIENTE', false);
				$this->dws['dw_orden_compra']->set_entrable('REFERENCIA'		  , false);
				$this->dws['dw_orden_compra']->set_entrable('COD_EMPRESA'		  , false);
				$this->dws['dw_orden_compra']->set_entrable('ALIAS'				  , false);
				$this->dws['dw_orden_compra']->set_entrable('RUT'				  , false);
				$this->dws['dw_orden_compra']->set_entrable('NOM_EMPRESA'		  , false);
				$this->dws['dw_orden_compra']->set_entrable('COD_SUCURSAL_FACTURA', false);
				$this->dws['dw_orden_compra']->set_entrable('COD_PERSONA'		  , false);
				$this->dws['dw_orden_compra']->set_entrable('OBS'				  , false);
				$this->dws['dw_orden_compra']->set_entrable('PORC_DSCTO1'	  	  , false);
				$this->dws['dw_orden_compra']->set_entrable('MONTO_DSCTO1'	  	  , false);
				$this->dws['dw_orden_compra']->set_entrable('PORC_IVA'		  	  , false);
				$this->dws['dw_orden_compra']->set_entrable('PORC_DSCTO2'	  	  , false);
				$this->dws['dw_orden_compra']->set_entrable('MONTO_DSCTO2'	  	  , false);
				
				$this->dws['dw_item_orden_compra']->set_entrable_dw(false);
			}
		}
	}
	
	function save_record($db){	
		$cod_orden_compra 	= $this->get_key();		
		$cod_usuario 		= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO');
		$cod_usuario_sol 	= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO_SOLICITA');		
		$cod_moneda			= $this->dws['dw_orden_compra']->get_item(0, 'COD_MONEDA');		
		$cod_est_oc			= $this->dws['dw_orden_compra']->get_item(0, 'COD_ESTADO_ORDEN_COMPRA');
		$cod_nota_venta		= $this->dws['dw_orden_compra']->get_item(0, 'COD_NOTA_VENTA');
		$cod_nota_venta		= ($cod_nota_venta =='') ? "null" : "$cod_nota_venta";
		
		$cod_cta_cte		= $this->dws['dw_orden_compra']->get_item(0, 'COD_CUENTA_CORRIENTE');	 	
		$referencia			= $this->dws['dw_orden_compra']->get_item(0, 'REFERENCIA');
		$referencia 		= str_replace("'", "''", $referencia);
		
		$nro_orden_compra_4d		= $this->dws['dw_orden_compra']->get_item(0, 'NRO_ORDEN_COMPRA_4D');
		$nro_orden_compra_4d		= ($nro_orden_compra_4d =='') ? "null" : "$nro_orden_compra_4d";
		
		$cod_empresa		= $this->dws['dw_orden_compra']->get_item(0, 'COD_EMPRESA');
		$cod_suc_factura	= $this->dws['dw_orden_compra']->get_item(0, 'COD_SUCURSAL_FACTURA');
		$cod_persona		= $this->dws['dw_orden_compra']->get_item(0, 'COD_PERSONA');
		$cod_persona		= ($cod_persona =='') ? "null" : "$cod_persona";			
		$sub_total			= $this->dws['dw_orden_compra']->get_item(0, 'SUM_TOTAL');
		$sub_total      	= ($sub_total =='') ? 0 : "$sub_total";
		
		$porc_descto1		= $this->dws['dw_orden_compra']->get_item(0, 'PORC_DSCTO1');
		$porc_descto1		= ($porc_descto1 =='') ? "null" : "$porc_descto1";
				
		$monto_dscto1		= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_DSCTO1');
		$monto_dscto1		= ($monto_dscto1 =='') ? 0 : "$monto_dscto1";
		
		$porc_descto2		= $this->dws['dw_orden_compra']->get_item(0, 'PORC_DSCTO2');
		$porc_descto2		= ($porc_descto2 =='') ? "null" : "$porc_descto2";
				
		$monto_dscto2		= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_DSCTO2');
		$monto_dscto2		= ($monto_dscto2 =='') ? 0 : "$monto_dscto2";
		
		$total_neto			= $this->dws['dw_orden_compra']->get_item(0, 'TOTAL_NETO');
		$total_neto			= ($total_neto =='') ? 0 : "$total_neto";
		
		$porc_iva			= $this->dws['dw_orden_compra']->get_item(0, 'PORC_IVA');		
		
		$monto_iva			= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_IVA');
		$monto_iva			= ($monto_iva =='') ? 0 : "$monto_iva";
		
		$total_con_iva		= $this->dws['dw_orden_compra']->get_item(0, 'TOTAL_CON_IVA');
		$total_con_iva		= ($total_con_iva =='') ? 0 : "$total_con_iva";
				
		$obs				= $this->dws['dw_orden_compra']->get_item(0, 'OBS');
		$obs		 		= str_replace("'", "''", $obs);
		$obs				= ($obs =='') ? "null" : "'$obs'";
							// NOTA: para el manejo de fecha se debe pasar un string dd/mm/yyyy y en el sp llamar a to_date ber eje en spi_orden_trabajo
		$motivo_anula		= $this->dws['dw_orden_compra']->get_item(0, 'MOTIVO_ANULA');
		$motivo_anula		= str_replace("'", "''", $motivo_anula);
		$motivo_anula		= ($motivo_anula =='') ? "null" : "'$motivo_anula'";
		
		$cod_user_anula		= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO_ANULA');
		$autorizada			= $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZADA');
		$autorizada_20_proc	= $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZADA_20_PROC');
		
		if (($motivo_anula!= '') && ($cod_user_anula == '')) // se anula 
			$cod_user_anula			= $this->cod_usuario;
		else
			$cod_usuario_anula			= "null";
		
		$ingreso_usuario_dscto1 = $this->dws['dw_orden_compra']->get_item(0, 'INGRESO_USUARIO_DSCTO1');;
		$ingreso_usuario_dscto1 = ($ingreso_usuario_dscto1 =='') ? "null" : "'$ingreso_usuario_dscto1'";
		
		
		$ingreso_usuario_dscto2 = $this->dws['dw_orden_compra']->get_item(0, 'INGRESO_USUARIO_DSCTO2');;
		$ingreso_usuario_dscto2 = ($ingreso_usuario_dscto2 =='') ? "null" : "'$ingreso_usuario_dscto2'";

		$tipo_orden_compra = $this->dws['dw_orden_compra']->get_item(0, 'TIPO_ORDEN_COMPRA');;
		$cod_doc = $this->dws['dw_orden_compra']->get_item(0, 'COD_DOC');;
		$cod_doc = ($cod_doc=='') ? "null" : $cod_doc;		

		$cod_orden_compra = ($cod_orden_compra=='') ? "null" : $cod_orden_compra;		
		
		$autoriza_monto_compra = $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZA_MONTO_COMPRA');
		$autoriza_monto_compra = ($autoriza_monto_compra=='') ? "null" : $autoriza_monto_compra;	
    
		$sp = 'spu_orden_compra';
	    if ($this->is_new_record()) {
	    	$operacion = 'INSERT';

			$sql = "select COD_ESTADO_NOTA_VENTA
					from NOTA_VENTA
					where COD_NOTA_VENTA = $cod_nota_venta";
			$result = $db->build_results($sql);		
			$cod_estado_nota_venta = $result[0]['COD_ESTADO_NOTA_VENTA'];
			if ($cod_estado_nota_venta==self::K_NV_CERRADA)
				$tipo_orden_compra = 'BACKCHARGE';
			else 
				$tipo_orden_compra = 'ARRIENDO';
	    }
	    else
	    	$operacion = 'UPDATE';
	    
	
		$param	= "'$operacion'
					,$cod_orden_compra				
					,$cod_usuario 		
					,$cod_usuario_sol 									
					,$cod_moneda		
					,$cod_est_oc
					,$cod_nota_venta			
					,$cod_cta_cte
					,'$referencia'																						
					,$cod_empresa		
					,$cod_suc_factura	
					,$cod_persona			
					,$sub_total		
					,$porc_descto1		
					,$monto_dscto1		
					,$porc_descto2		
					,$monto_dscto2		
					,$total_neto		
					,$porc_iva		
					,$monto_iva		
					,$total_con_iva				
					,$obs
					,$motivo_anula
					,$cod_user_anula
					,$ingreso_usuario_dscto1
					,$ingreso_usuario_dscto2
					,$tipo_orden_compra
					,$cod_doc
					,$autorizada
					,$autorizada_20_proc
					,$nro_orden_compra_4d
                    ,null
                    ,null
                    ,null
                    ,'$autoriza_monto_compra'";
					
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_orden_compra = $db->GET_IDENTITY();
				$this->dws['dw_orden_compra']->set_item(0, 'COD_ORDEN_COMPRA', $cod_orden_compra);
			}
			 for ($i=0; $i<$this->dws['dw_item_orden_compra']->row_count(); $i++)
				$this->dws['dw_item_orden_compra']->set_item($i, 'COD_ORDEN_COMPRA', $this->dws['dw_orden_compra']->get_item(0, 'COD_ORDEN_COMPRA'), 'primary', false);				
			 if (!$this->dws['dw_item_orden_compra']->update($db))			
			 	return false;			
			
			$parametros_sp = "'RECALCULA',$cod_orden_compra";	
			if (!$db->EXECUTE_SP('spu_orden_compra', $parametros_sp))
				return false;

			$parametros_sp = "'item_orden_compra','orden_compra',$cod_orden_compra";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
			
			return true;
		}	
		return false;				
	}	
	function load_wo() {
		if ($this->tiene_wo)
			$this->wo = session::get("wo_orden_compra_arriendo");
	}
	function make_sql_auditoria() {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'orden_compra';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function make_sql_auditoria_relacionada($tabla) {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'orden_compra';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function delete_record($db) {
		return $db->EXECUTE_SP("spu_orden_compra", "'DELETE', ".$this->get_key());
	}
	
	function habilitar(&$temp, $habilita){
		parent::habilitar($temp, $habilita);
		
		$COD_ESTADO_ORDEN_COMPRA = $this->dws['dw_orden_compra']->get_item(0, 'COD_ESTADO_ORDEN_COMPRA');
		
		if($COD_ESTADO_ORDEN_COMPRA == 1){// emitida
			$priv = $this->get_privilegio_opcion_usuario('995515', $this->cod_usuario);
			if($priv == 'E')
				$this->habilita_boton($temp, 'print', true);
			else	
				$this->habilita_boton($temp, 'print', false);
			
		}else if($COD_ESTADO_ORDEN_COMPRA == 4)// autorizada
			$this->habilita_boton($temp, 'print', true);
		else
			$this->habilita_boton($temp, 'print', false);
	}
	
	function print_record(){
		$cod_orden_compra = $this->get_key();
		$sql= "SELECT OC.COD_ORDEN_COMPRA,
					OC.COD_NOTA_VENTA,
					OC.SUBTOTAL,
					OC.PORC_DSCTO1,
					OC.MONTO_DSCTO1,
					OC.PORC_DSCTO2,
					OC.MONTO_DSCTO2,
					OC.TOTAL_NETO,
					OC.PORC_IVA,
					OC.MONTO_IVA,
					OC.TOTAL_CON_IVA,
					OC.REFERENCIA,																
					OC.OBS,
					E.NOM_EMPRESA,
					E.RUT,
					E.DIG_VERIF,
					dbo.f_get_direccion('SUCURSAL', OC.COD_SUCURSAL, '[DIRECCION] [NOM_COMUNA] [NOM_CIUDAD]') DIRECCION,
					dbo.f_format_date(OC.FECHA_ORDEN_COMPRA, 3) FECHA_ORDEN_COMPRA,	
					S.TELEFONO,
					S.FAX,
					P.NOM_PERSONA,
					U.NOM_USUARIO,
					U.MAIL,
					IOC.NOM_PRODUCTO,
					OC.COD_DOC,
					case IOC.COD_PRODUCTO
						when 'T' then ''
						else IOC.COD_PRODUCTO
					end COD_PRODUCTO,
					case IOC.COD_PRODUCTO
						when 'T' then ''
						else IOC.ITEM
					end ITEM,
					case IOC.COD_PRODUCTO
						when 'T' then ''
						else IOC.CANTIDAD
					end CANTIDAD,
					case IOC.COD_PRODUCTO
						when 'T' then ''
						else IOC.PRECIO
					end PRECIO,
					case IOC.COD_PRODUCTO		
						when 'T' then ''
						else IOC.CANTIDAD * IOC.PRECIO
					end TOTAL_IOC,			
					M.SIMBOLO,
					dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
					dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,	
					dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA
			FROM    ORDEN_COMPRA OC LEFT OUTER JOIN PERSONA P ON  OC.COD_PERSONA = P.COD_PERSONA,
					ITEM_ORDEN_COMPRA IOC, EMPRESA E, SUCURSAL S, USUARIO U, MONEDA M
			WHERE   OC.COD_ORDEN_COMPRA = $cod_orden_compra
			AND		E.COD_EMPRESA = OC.COD_EMPRESA 
			AND		S.COD_SUCURSAL = OC.COD_SUCURSAL 
			AND		U.COD_USUARIO = OC.COD_USUARIO_SOLICITA 
			AND		IOC.COD_ORDEN_COMPRA = OC.COD_ORDEN_COMPRA 
			AND		M.COD_MONEDA = OC.COD_MONEDA";

		//reporte
		$labels = array();
		$labels['strCOD_ORDEN_COMPRA'] = $cod_orden_compra;
		$rpt = new print_reporte_arriendo($sql, $this->root_dir.'appl/orden_compra_arriendo/orden_compra_arriendo.xml', $labels, "Orden de Compra Arriendo ".$cod_orden_compra.".pdf", 1);
		$this->redraw();
	}
}

class print_reporte_arriendo extends reporte{	
	function print_reporte_arriendo($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {

		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	function make_reporte() {
		$p = new ReportParser();
		$p->parseRP($this->xml);
		$rdata = new MySQLRD($this->sql);
		$pdf = new OC_PDF_Arr(array($p), array($this->labels), array($rdata), $this->con_logo,$this->orientation,$this->unit,$this->format,$this->sql);
		if ($pdf->barcode_dibujado==false) {
			$pdf->draw_barcode();
		}
		
		$pdf->SetTitle($this->titulo);
		$pdf->Output($this->titulo, 'I');
	}
}

class OC_PDF_Arr extends PDF {
	var $posY_subtotal = 0;
	var $barcode_dibujado = false;
	var $sql_print = "";
	
	function OC_PDF_Arr($t,$d,$rd, $con_logo, $orientation,$unit,$format,$sql){
		$this->sql_print = $sql;
		parent::PDF($t,$d,$rd, $con_logo, $orientation,$unit,$format);
	}
	
	function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='',$rowheight=0) {
		if ($txt=='Direcci�n Factura:') {
			$this->posY_subtotal = $this->GetY();
		}
		parent::Cell($w,$h,$txt,$border,$ln,$align,$fill,$link,$rowheight);
	}
	function draw_barcode(){
		$print_etiqueta = false;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql_print);
		
		$cod_orden_compra_bd	= $result[0]['COD_ORDEN_COMPRA'];
		
		$cod_orden_compra		= '000000';
		$count_cod_oc			= strlen($cod_orden_compra_bd);
		$relleno_cod_oc			= substr($cod_orden_compra, 0, -$count_cod_oc);
		$cod_orden_compra		= $relleno_cod_oc.$cod_orden_compra_bd;
		
		if(K_CLIENTE == 'RENTAL'){
			$cadena = $cod_orden_compra.'-91462001R';
			//RENTAL
			$this->SetFont('Helvetica','',8);
			$factor = 0.5;
			$this->Image(dirname(__FILE__)."/../../images_appl/sello_rental.jpg",520,175, $factor * 120, $factor * 60);
			
			if($cod_orden_compra_bd > 65555)
				$print_etiqueta = true;
		}
		
		if($print_etiqueta == true){
			$style = 40;
			$width = 220;
			$height = 100;
			$xres = 1;
			$font = 1;
			$obj = new C128BObject($width, $height, $style, $cadena);
			if ($obj){
				$obj->SetFont($font);
				$obj->DrawObject($xres);
				$nombre_temp = tempnam(dirname(__FILE__)."/../../../tmp", "tmp");			
				$obj->FlushObjectToFile ($nombre_temp);
				$obj->DestroyObject();
				unset($obj);
				
				$factor = 0.6;
				//imagen codigo de barra ********************************************************************
				$anguloS1 = 220;   //eje x
				$anguloS2 = 114;     //eje y
				$anguloI1 = 310;    //ancho
				$anguloI2 = 40;    //alto
				$this->Image($nombre_temp.".jpg", $anguloS1, $anguloS2, $factor * $anguloI1, $factor * $anguloI2);
			}
		}	
		$this->barcode_dibujado = true;
	}
	function AddPage($orientation=''){
		if ($this->posY_subtotal != 0 && $this->barcode_dibujado==false){
			$this->draw_barcode();
		}
		parent::AddPage($orientation);
	}
}
?>
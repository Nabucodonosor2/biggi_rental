<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

class dw_valor_defecto_compra extends dw_valor_defecto_compra_base {
		function dw_valor_defecto_compra() {
			parent::dw_valor_defecto_compra_base(); 
		}
}

class wi_empresa extends wi_empresa_base {
	function wi_empresa($cod_item_menu) {
		parent::wi_empresa_base($cod_item_menu);
		$sql = "select COD_EMPRESA,
						RUT, 	
						RUT as RUT_NO_ING,	
						DIG_VERIF,
						DIG_VERIF as DIG_VERIF_NO_ING,
						ALIAS,
						ALIAS as ALIAS_NO_ING,
						NOM_EMPRESA,
						NOM_EMPRESA as NOM_EMPRESA_NO_ING,
						GIRO,
						GIRO as GIRO_NO_ING, 
						COD_CLASIF_EMPRESA,
						DIRECCION_INTERNET,
						RUT_REPRESENTANTE,
						DIG_VERIF_REPRESENTANTE,
						NOM_REPRESENTANTE,
						ES_CLIENTE,
						case ES_CLIENTE 
							WHEN 'S' THEN '' 
							ELSE CASE ES_CLIENTE
									WHEN 'S' THEN ''
									ELSE 'none'
								end
						end VISIBLE_TAB2,
						ES_PROVEEDOR_INTERNO,
						ES_PROVEEDOR_EXTERNO,
						ES_PERSONAL,
						CASE ES_PERSONAL
							WHEN 'S' THEN ''
							ELSE 'none'
						end TD_CATEGORIA,
						CASE ES_PERSONAL
							WHEN 'S' THEN 'none'
							ELSE ''
						end TD_ESPACIO,
						TIPO_PARTICIPACION,
						IMPRIMIR_EMP_MAS_SUC,
						SUJETO_A_APROBACION,
						COD_USUARIO,
						dbo.f_get_porc_dscto_corporativo_empresa(cod_empresa, getdate()) PORC_DSCTO_CORPORATIVO,
						0 TOTAL_DISPONIBLE,
						0 TOTAL_POR_FACTURAR,
						0 TOTAL_CREDITO_ASIGNADO,
						0 TOTAL_POR_COBRAR,
						0 TOTAL_ATRASADO,
						year(getdate()) ANO_ACTUAL,
						year(getdate())-1  ANO_ANTERIOR
						,COD_FORMA_PAGO_CLIENTE
						,ALIAS_CONTABLE
						,MONTO_COMPRA_MAX_SIN_AUTORIZAR
						,REFERENCIA_HEM
						,REFERENCIA_HES
					from EMPRESA 
					where COD_EMPRESA = {KEY1}";				
												
		$this->dws['dw_empresa'] = new datawindow($sql);	
		
		$this->dws['dw_empresa']->add_control($control = new static_text('COD_EMPRESA'));	
		$this->dws['dw_empresa']->add_control($control = new edit_rut('RUT'));	
		$control->set_onChange("existe_rut(this);");
		
		$this->dws['dw_empresa']->add_control(new edit_dig_verif('DIG_VERIF'));	
		$this->dws['dw_empresa']->add_control(new edit_text_upper('ALIAS', 27, 30));	
		$this->dws['dw_empresa']->add_control(new edit_text_upper('ALIAS_CONTABLE', 65, 30));
		$this->dws['dw_empresa']->add_control(new edit_text_upper('NOM_EMPRESA', 132, 100));	
		$this->dws['dw_empresa']->add_control(new edit_text_upper('GIRO', 60, 50));
		$this->dws['dw_empresa']->add_control(new edit_num('MONTO_COMPRA_MAX_SIN_AUTORIZAR', 10, 10));	
		
		// se crean alias en el select, para los campos que son ingresables en el primer tab y no ingresables en el segundo
		$this->dws['dw_empresa']->add_control(new edit_rut('RUT_NO_ING'));
		$this->dws['dw_empresa']->set_entrable('RUT_NO_ING', false);
		$this->dws['dw_empresa']->set_entrable('DIG_VERIF_NO_ING', false);
		$this->dws['dw_empresa']->set_entrable('ALIAS_NO_ING', false);
		$this->dws['dw_empresa']->set_entrable('NOM_EMPRESA_NO_ING', false);
		$this->dws['dw_empresa']->set_entrable('GIRO_NO_ING', false);
		
		$this->dws['dw_empresa']->add_control($control = new edit_check_box('REFERENCIA_HEM', 'S', 'N', 'HEM'));
		$control->set_onChange("valida_check(this);");
		$this->dws['dw_empresa']->add_control($control = new edit_check_box('REFERENCIA_HES', 'S', 'N', 'HES'));
		$control->set_onChange("valida_check(this);");
		
		$sql = "select 		COD_CLASIF_EMPRESA, 
							NOM_CLASIF_EMPRESA,
							ORDEN
				from 		CLASIF_EMPRESA
				order by 	ORDEN";
		$this->dws['dw_empresa']->add_control(new drop_down_dw('COD_CLASIF_EMPRESA', $sql, 165));				
		$this->dws['dw_empresa']->add_control(new edit_text_lower('DIRECCION_INTERNET', 30, 30));
		$this->dws['dw_empresa']->add_control(new edit_check_box('SUJETO_A_APROBACION', 'S', 'N'));
		$this->dws['dw_empresa']->add_control(new edit_rut('RUT_REPRESENTANTE', 10, 10, 'DIG_VERIF_REPRESENTANTE'));
		$this->dws['dw_empresa']->add_control(new edit_dig_verif('DIG_VERIF_REPRESENTANTE', 'RUT_REPRESENTANTE'));	
		$this->dws['dw_empresa']->add_control(new edit_text_upper('NOM_REPRESENTANTE', 30, 100));	
		$this->dws['dw_empresa']->add_control($control = new edit_check_box('ES_CLIENTE', 'S', 'N', 'Cliente'));
		$control->set_onChange("muestra_tab_cliente()");	
		
		$sql_fp_cliente		= "	select 			COD_FORMA_PAGO COD_FORMA_PAGO_CLIENTE
													,NOM_FORMA_PAGO	 NOM_FORMA_PAGO_CLIENTE												
						   			from			FORMA_PAGO
						   			order by  		ORDEN";
		$this->dws['dw_empresa']->add_control(new drop_down_dw('COD_FORMA_PAGO_CLIENTE',$sql_fp_cliente,150));	
				
		$this->dws['dw_empresa']->add_control($control = new edit_check_box('ES_PROVEEDOR_INTERNO', 'S', 'N', 'Prov Int'));
		$control->set_onChange("muestra_nuevo_tab(); if (this.checked) document.getElementById('ES_PROVEEDOR_EXTERNO_0').checked=false");
		$this->dws['dw_empresa']->add_control($control = new edit_check_box('ES_PROVEEDOR_EXTERNO', 'S', 'N', 'Prov Ext'));
		$control->set_onChange("muestra_nuevo_tab(); if (this.checked) document.getElementById('ES_PROVEEDOR_INTERNO_0').checked=false");
		
		$this->dws['dw_empresa']->add_control($control = new edit_check_box('ES_PERSONAL', 'S', 'N', 'Personal'));
		$control->set_onChange("valida_personal();");
		
		$this->dws['dw_empresa']->add_control(new drop_down_list('TIPO_PARTICIPACION',array('','FA','BH'),array('','FA','BH'),40));
		
		
		
		$this->dws['dw_empresa']->add_control(new edit_check_box('IMPRIMIR_EMP_MAS_SUC', 'S', 'N'));
		$sql_usuario = "SELECT COD_USUARIO, NOM_USUARIO FROM USUARIO WHERE ES_VENDEDOR = 'S'";
		$this->dws['dw_empresa']->add_control(new drop_down_dw('COD_USUARIO', $sql_usuario, 150));		
			
		// EL CAMPO PORC_DSCTO_CORPORATIVO FUE ELIMINADO DE LA BD							
		$this->dws['dw_empresa']->add_control(new edit_porcentaje('PORC_DSCTO_CORPORATIVO'));
		
		$this->dws['dw_empresa']->add_control(new edit_precio('TOTAL_DISPONIBLE'));
		$this->dws['dw_empresa']->set_entrable('TOTAL_DISPONIBLE', false);
		$this->dws['dw_empresa']->add_control(new edit_precio('TOTAL_POR_FACTURAR'));
		$this->dws['dw_empresa']->set_entrable('TOTAL_POR_FACTURAR', false);
		$this->dws['dw_empresa']->add_control(new edit_precio('TOTAL_CREDITO_ASIGNADO'));
		$this->dws['dw_empresa']->set_entrable('TOTAL_CREDITO_ASIGNADO', false);
		$this->dws['dw_empresa']->add_control(new edit_precio('TOTAL_POR_COBRAR'));
		$this->dws['dw_empresa']->set_entrable('TOTAL_POR_COBRAR', false);
		$this->dws['dw_empresa']->add_control(new edit_precio('TOTAL_ATRASADO'));
		$this->dws['dw_empresa']->set_entrable('TOTAL_ATRASADO', false);	
		
		
		// asigna los mandatorys
		$this->dws['dw_empresa']->set_mandatory('RUT', 'Rut de la Empresa');		
		$this->dws['dw_empresa']->set_mandatory('DIG_VERIF', 'D�gito verificador del RUT de la Empresa');		
		$this->dws['dw_empresa']->set_mandatory('ALIAS', 'Al�as de la Empresa');		
		$this->dws['dw_empresa']->set_mandatory('NOM_EMPRESA', 'Raz�n Social');		
		$this->dws['dw_empresa']->set_mandatory('GIRO', 'Giro de la Empresa');		
		$this->dws['dw_empresa']->set_mandatory('COD_CLASIF_EMPRESA', 'Clasificaci�n de la Empresa');
		$this->dws['dw_empresa']->set_mandatory('COD_USUARIO', 'Un Vendedor');				
		
		$this->dws['dw_sucursal'] = new dw_sucursal();
		$this->dws['dw_persona'] = new dw_persona();
		$this->dws['dw_costo_producto'] = new dw_costo_producto();
		$this->dws['dw_valor_defecto_compra'] = new dw_valor_defecto_compra();
		
		/* tab historial ******* POR DEFINIR
		$this->dws['dw_prorroga'] = new datawindow($sql, "PRORROGA");		//***********
		$this->dws['dw_protesto'] = new datawindow($sql, "PROTESTO");		//***********
		*/
		
		$this->dws['dw_bitacora_empresa'] = new dw_bitacora_empresa();
		
		// estadistica de ventas
		$sql = "execute spdw_estadistica_venta {KEY1}";
		$this->dws['dw_factura'] = new datawindow($sql, "FACTURA");
	
		$this->dws['dw_factura']->add_control(new edit_mes('MES'));
		$this->dws['dw_factura']->add_control(new computed('SUBTOTAL_ANT'));
		$this->dws['dw_factura']->add_control(new edit_precio('MONTO_DSCTO_ANT'));
		$this->dws['dw_factura']->add_control(new edit_precio('MONTO_NETO_ANT'));
		$this->dws['dw_factura']->add_control(new edit_precio('SUBTOTAL_ACT'));
		$this->dws['dw_factura']->add_control(new edit_precio('MONTO_DSCTO_ACT'));
		$this->dws['dw_factura']->add_control(new edit_precio('MONTO_NETO_ACT'));
		$this->dws['dw_factura']->add_control(new edit_porcentaje('CRECIMIENTO'));
		
		$this->dws['dw_factura']->accumulate('SUBTOTAL_ANT', '', false);
		$this->dws['dw_factura']->accumulate('MONTO_DSCTO_ANT', '', false);
		$this->dws['dw_factura']->accumulate('MONTO_NETO_ANT', '', false);
		$this->dws['dw_factura']->accumulate('SUBTOTAL_ACT', '', false);
		$this->dws['dw_factura']->accumulate('MONTO_DSCTO_ACT', '', false);
		$this->dws['dw_factura']->accumulate('MONTO_NETO_ACT', '', false);
		$this->dws['dw_factura']->set_entrable_dw(false);	/// no funcion� dejar no entrable la dw.  IS 25/02/09
		
		//auditoria EMPRESA
		$this->add_auditoria('NOM_EMPRESA');
		$this->add_auditoria('GIRO');
		$this->add_auditoria('ALIAS');
		$this->add_auditoria('COD_CLASIF_EMPRESA');
		$this->add_auditoria('DIRECCION_INTERNET');
		$this->add_auditoria('RUT_REPRESENTANTE');
		$this->add_auditoria('DIG_VERIF_REPRESENTANTE');
		$this->add_auditoria('NOM_REPRESENTANTE');
		$this->add_auditoria('ES_CLIENTE');
		$this->add_auditoria('ES_PROVEEDOR_INTERNO');
		$this->add_auditoria('ES_PROVEEDOR_EXTERNO');
		$this->add_auditoria('ES_PERSONAL');
		$this->add_auditoria('TIPO_PARTICIPACION');
		$this->add_auditoria('IMPRIMIR_EMP_MAS_SUC');
		$this->add_auditoria('SUJETO_A_APROBACION');
		$this->add_auditoria('COD_USUARIO');
		
		//auditoria SUCURSAL
		$this->add_auditoria_relacionada('SUCURSAL', 'NOM_SUCURSAL');
		$this->add_auditoria_relacionada('SUCURSAL', 'DIRECCION');
		$this->add_auditoria_relacionada('SUCURSAL', 'DIRECCION_FACTURA');
		$this->add_auditoria_relacionada('SUCURSAL', 'DIRECCION_DESPACHO');
		$this->add_auditoria_relacionada('SUCURSAL', 'COD_COMUNA');
		$this->add_auditoria_relacionada('SUCURSAL', 'COD_CIUDAD');
		$this->add_auditoria_relacionada('SUCURSAL', 'COD_PAIS');
		$this->add_auditoria_relacionada('SUCURSAL', 'FAX');
		$this->add_auditoria_relacionada('SUCURSAL', 'TELEFONO');

		// dscto corporativo
		$this->add_auditoria_relacionada('DSCTO_CORPORATIVO_EMPRESA', 'PORC_DSCTO_CORPORATIVO');
		$this->add_auditoria_relacionada('DSCTO_CORPORATIVO_EMPRESA', 'FECHA_INICIO_VIGENCIA');
		// se cae falta considerar estas auditorias		
		/*
		//auditoria PERSONA
		$this->add_auditoria_relacionada('PERSONA', 'NOM_PERSONA');
		$this->add_auditoria_relacionada('PERSONA', 'COD_CARGO');
		$this->add_auditoria_relacionada('PERSONA', 'COD_SUCURSAL');
		$this->add_auditoria_relacionada('PERSONA', 'TELEFONO');
		$this->add_auditoria_relacionada('PERSONA', 'FAX');
		$this->add_auditoria_relacionada('PERSONA', 'EMAIL');
	
		//auditoria PRODUCTO_PROVEEDOR
		$this->add_auditoria_relacionada('PRODUCTO_PROVEEDOR', 'COD_PRODUCTO');
		$this->add_auditoria_relacionada('PRODUCTO_PROVEEDOR', 'COD_INTERNO_PRODUCTO');
		$this->add_auditoria_relacionada('PRODUCTO_PROVEEDOR', 'PRECIO');
		*/		
		$this->first_focus = 'RUT';
	}
	function new_record() {
		$row = $this->dws['dw_empresa']->insert_row();
		$this->dws['dw_empresa']->set_item($row, 'ES_CLIENTE', 'S');
		$this->dws['dw_empresa']->controls['TIPO_PARTICIPACION']->enabled = false;
		$this->dws['dw_empresa']->set_item(0, 'TD_CATEGORIA', 'none');
		$this->dws['dw_empresa']->set_item(0, 'TD_ESPACIO', '');
		$this->dws['dw_valor_defecto_compra']->insert_row();
		$this->dws['dw_valor_defecto_compra']->set_item(0, 'VISIBLE_TAB', 'none');
		
	}
	function load_record() {
		$COD_EMPRESA = $this->get_item_wo($this->current_record, 'COD_EMPRESA');
		$this->dws['dw_empresa']->retrieve($COD_EMPRESA);
		$this->dws['dw_empresa']->set_entrable('RUT', false);		// El rut no es modificable
		$this->dws['dw_empresa']->set_entrable('DIG_VERIF', false);		// El rut no es modificable
		$this->dws['dw_sucursal']->retrieve($COD_EMPRESA);
		$this->dws['dw_persona']->retrieve($COD_EMPRESA);
		$this->dws['dw_persona']->cod_empresa = $COD_EMPRESA;
		$this->dws['dw_costo_producto']->retrieve($COD_EMPRESA);
		$this->dws['dw_bitacora_empresa']->retrieve($COD_EMPRESA);
		$this->dws['dw_valor_defecto_compra']->retrieve($COD_EMPRESA);
		if ($this->dws['dw_valor_defecto_compra']->row_count()==0)
			$this->dws['dw_valor_defecto_compra']->insert_row();		

		/**  por definir ************
		$this->dws['dw_prorroga']->retrieve();
		$this->dws['dw_protesto']->retrieve();
		*/
		
		$this->dws['dw_factura']->retrieve($COD_EMPRESA);
		
		$ES_PERSONAL = $this->dws['dw_empresa']->get_item(0, 'ES_PERSONAL');
		
		if($ES_PERSONAL == 'S'){
			$this->dws['dw_empresa']->controls['TIPO_PARTICIPACION']->enabled = true;
		}
	}
	function get_key() {
		return $this->dws['dw_empresa']->get_item(0, 'COD_EMPRESA');
	}
	function save_record($db) {
		$COD_EMPRESA = $this->get_key();
		$RUT = $this->dws['dw_empresa']->get_item(0, 'RUT');
		$DIG_VERIF = $this->dws['dw_empresa']->get_item(0, 'DIG_VERIF');
		$ALIAS = $this->dws['dw_empresa']->get_item(0, 'ALIAS');
		$ALIAS_CONTABLE = $this->dws['dw_empresa']->get_item(0, 'ALIAS_CONTABLE');
		$NOM_EMPRESA = $this->dws['dw_empresa']->get_item(0, 'NOM_EMPRESA');
		$NOM_EMPRESA = str_replace("'", "''", $NOM_EMPRESA);
		$GIRO = $this->dws['dw_empresa']->get_item(0, 'GIRO');
		$GIRO = str_replace("'", "''", $GIRO);
		$COD_CLASIF_EMPRESA = $this->dws['dw_empresa']->get_item(0, 'COD_CLASIF_EMPRESA');
		$DIRECCION_INTERNET = $this->dws['dw_empresa']->get_item(0, 'DIRECCION_INTERNET');
		$RUT_REPRESENTANTE = $this->dws['dw_empresa']->get_item(0, 'RUT_REPRESENTANTE');
		$DIG_VERIF_REPRESENTANTE = $this->dws['dw_empresa']->get_item(0, 'DIG_VERIF_REPRESENTANTE');
		$NOM_REPRESENTANTE = $this->dws['dw_empresa']->get_item(0, 'NOM_REPRESENTANTE');	
		$ES_CLIENTE = $this->dws['dw_empresa']->get_item(0, 'ES_CLIENTE');
		$ES_PROVEEDOR_INTERNO = $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_INTERNO');
		$ES_PROVEEDOR_EXTERNO = $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_EXTERNO');
		$ES_PERSONAL = $this->dws['dw_empresa']->get_item(0, 'ES_PERSONAL');
		$IMPRIMIR_EMP_MAS_SUC = $this->dws['dw_empresa']->get_item(0, 'IMPRIMIR_EMP_MAS_SUC');
		$SUJETO_A_APROBACION = $this->dws['dw_empresa']->get_item(0, 'SUJETO_A_APROBACION');
		$COD_USUARIO = $this->dws['dw_empresa']->get_item(0, 'COD_USUARIO');
		// EL CAMPO PORC_DSCTO_CORPORATIVO FUE ELIMINADO DE LA BD	
		$PORC_DSCTO_CORPORATIVO = $this->dws['dw_empresa']->get_item(0, 'PORC_DSCTO_CORPORATIVO');

		$DIRECCION_INTERNET = ($DIRECCION_INTERNET=='') ? "null" : "'$DIRECCION_INTERNET'";
		$RUT_REPRESENTANTE = ($RUT_REPRESENTANTE=='') ? "null" : $RUT_REPRESENTANTE;
		$DIG_VERIF_REPRESENTANTE = ($DIG_VERIF_REPRESENTANTE=='') ? "null" : "'$DIG_VERIF_REPRESENTANTE'";
		$NOM_REPRESENTANTE = ($NOM_REPRESENTANTE=='') ? "null" : "'$NOM_REPRESENTANTE'";
		
		$REFERENCIA_HEM = $this->dws['dw_empresa']->get_item(0, 'REFERENCIA_HEM');
		$REFERENCIA_HES = $this->dws['dw_empresa']->get_item(0, 'REFERENCIA_HES');
		
		//se valida en la funci�n validate que se ingresen los dem�s campos mandatory
		// EL CAMPO PORC_DSCTO_CORPORATIVO FUE ELIMINADO DE LA BD	
		$PORC_DSCTO_CORPORATIVO = ($PORC_DSCTO_CORPORATIVO=='') ? "0" : $PORC_DSCTO_CORPORATIVO;

		$COD_EMPRESA = ($COD_EMPRESA=='') ? "null" : $COD_EMPRESA;
		if ($ALIAS_CONTABLE=='')
			$ALIAS_CONTABLE = $ALIAS;
			
		$TIPO_PARTICIPACION = $this->dws['dw_empresa']->get_item(0, 'TIPO_PARTICIPACION');
		$TIPO_PARTICIPACION			= ($TIPO_PARTICIPACION =='') ? "null" : "'$TIPO_PARTICIPACION'";
	   
		$COD_FORMA_PAGO_CLIENTE = $this->dws['dw_empresa']->get_item(0, 'COD_FORMA_PAGO_CLIENTE');
		$COD_FORMA_PAGO_CLIENTE	= ($COD_FORMA_PAGO_CLIENTE =='') ? "null" : $COD_FORMA_PAGO_CLIENTE;
		$DSCTO_PROVEEDOR = $this->dws['dw_valor_defecto_compra']->get_item(0, 'DSCTO_PROVEEDOR');
		$MONTO_COMPRA_MAX_SIN_AUTORIZAR = $this->dws['dw_empresa']->get_item(0, 'MONTO_COMPRA_MAX_SIN_AUTORIZAR');
		$MONTO_COMPRA_MAX_SIN_AUTORIZAR	= ($MONTO_COMPRA_MAX_SIN_AUTORIZAR =='') ? 0 : $MONTO_COMPRA_MAX_SIN_AUTORIZAR;
		
		if($DSCTO_PROVEEDOR == '')
			$DSCTO_PROVEEDOR = 0 ;	
		
		$sp = 'spu_empresa';		
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
		
		// EL CAMPO PORC_DSCTO_CORPORATIVO FUE ELIMINADO DE LA BD	
		$param = "'$operacion'
				,$COD_EMPRESA
				,$RUT
				,'$DIG_VERIF'
				,'$ALIAS'
				,'$ALIAS_CONTABLE'
				,'$NOM_EMPRESA'
				,'$GIRO'
				,$COD_CLASIF_EMPRESA
				,$DIRECCION_INTERNET
				,$RUT_REPRESENTANTE
				,$DIG_VERIF_REPRESENTANTE
				,$NOM_REPRESENTANTE
				,'$ES_CLIENTE'
				,'$ES_PROVEEDOR_INTERNO'
				,'$ES_PROVEEDOR_EXTERNO'
				,'$ES_PERSONAL'
				,'$IMPRIMIR_EMP_MAS_SUC'
				,'$SUJETO_A_APROBACION'
				,$PORC_DSCTO_CORPORATIVO
				,$COD_USUARIO
				,$TIPO_PARTICIPACION
				,$DSCTO_PROVEEDOR
				,$COD_FORMA_PAGO_CLIENTE
				,$MONTO_COMPRA_MAX_SIN_AUTORIZAR
				,'$REFERENCIA_HEM'
				,'$REFERENCIA_HES'";
				
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_EMPRESA = $db->GET_IDENTITY();
				$this->dws['dw_empresa']->set_item(0, 'COD_EMPRESA', $COD_EMPRESA);		

				$param = "'DSCTO_CORPORATIVO_EMPRESA',$COD_EMPRESA, $PORC_DSCTO_CORPORATIVO";
				$sp = 'spu_empresa';
				
				if (!$db->EXECUTE_SP($sp, $param))
					return false;
			}
			for ($i=0; $i<$this->dws['dw_sucursal']->row_count(); $i++)
				$this->dws['dw_sucursal']->set_item($i, 'COD_EMPRESA', $COD_EMPRESA);
				
			if (!$this->dws['dw_sucursal']->update($db))
				return false;
			
			for ($i=0; $i<$this->dws['dw_persona']->row_count(); $i++) {
				$COD_SUCURSAL = $this->dws['dw_persona']->get_item($i, 'P_COD_SUCURSAL');
				if ($COD_SUCURSAL < 0) {
						$row = $this->dws['dw_sucursal']->un_redirect(- $COD_SUCURSAL - 100);		// el -100 viene del insert_row
						$COD_SUCURSAL = $this->dws['dw_sucursal']->get_item($row, 'COD_SUCURSAL');
						$this->dws['dw_persona']->set_item($i, 'P_COD_SUCURSAL', $COD_SUCURSAL);		
				}
			}
			if (!$this->dws['dw_persona']->update($db))
				return false;
			 
			for ($i=0; $i<$this->dws['dw_costo_producto']->row_count(); $i++)
				$this->dws['dw_costo_producto']->set_item($i, 'COD_EMPRESA', $COD_EMPRESA);				
			if (!$this->dws['dw_costo_producto']->update($db))
				return false;
								
			for ($i=0; $i<$this->dws['dw_bitacora_empresa']->row_count(); $i++)
				$this->dws['dw_bitacora_empresa']->set_item($i, 'COD_EMPRESA', $COD_EMPRESA);				
			if (!$this->dws['dw_bitacora_empresa']->update($db))
				return false;

			// TAB VALOR DEFECTO COMPRA //	
			$prov_int			 	= $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_INTERNO');
			$prov_ext			 	= $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_EXTERNO');
		
			$cod_persona_defecto 	= $this->dws['dw_valor_defecto_compra']->get_item(0, 'COD_PERSONA_DEFECTO');
			$cod_persona_defecto	= ($cod_persona_defecto=='') ? "null" : $cod_persona_defecto;
			$cod_forma_pago 		= $this->dws['dw_valor_defecto_compra']->get_item(0, 'COD_FORMA_PAGO');
			$cod_forma_pago			= ($cod_forma_pago=='') ? "null" : $cod_forma_pago;
			
			if ($cod_persona_defecto < 0) {
				$row = $this->dws['dw_persona']->un_redirect(- $cod_persona_defecto - 100);
				$cod_persona_defecto = $this->dws['dw_persona']->get_item($row, 'COD_PERSONA');
				$this->dws['dw_valor_defecto_compra']->set_item($i, 'COD_PERSONA_DEFECTO', $cod_persona_defecto);		
			}
			
			if($prov_int == 'S' or $prov_ext == 'S')			
	    		$operacion = 'INSERT';	    		
	    	else	    		
	    		$operacion = 'DELETE';
			$sp = 'spu_valor_defecto_compra';

			$param = "'$operacion',$COD_EMPRESA,$cod_persona_defecto,$cod_forma_pago";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
			 
			return true;
		}
		else
			return false;						
	}
}
?>
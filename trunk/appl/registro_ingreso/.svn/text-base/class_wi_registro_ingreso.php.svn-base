<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/class_dw_help_empresa.php");

class dw_item_registro_ingreso extends dw_item {
	const K_PARAM_VALOR_DOLAR		= 5;
	
	function dw_item_registro_ingreso() {
		$sql = "SELECT		COD_ITEM_REGISTRO_4D,
							NUMERO_REGISTRO_INGRESO,
							ITEM,
							MODELO COD_PRODUCTO,
							NOM_PRODUCTO,
							CANTIDAD,
							PRECIO,
							(SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO = ".self::K_PARAM_VALOR_DOLAR.") DOLAR
				FROM		ITEM_REGISTRO_4D IR4 , PRODUCTO P
				WHERE		P.COD_PRODUCTO = IR4.MODELO
				AND NUMERO_REGISTRO_INGRESO = {KEY1}";

		parent::dw_item($sql, 'ITEM_REGISTRO_INGRESO', true, true, 'COD_PRODUCTO');

		$this->add_control(new edit_text('NUMERO_REGISTRO_INGRESO',10, 10, 'hidden'));
		$this->add_control(new edit_text('COD_ITEM_REGISTRO_4D',10, 10, 'hidden'));
		
		$this->add_control(new edit_text('ITEM',4 , 5));
		$this->add_control($control = new edit_num('PRECIO',10,10,2));
		$control->set_onChange("total_item_exfca(this);");
		
		$this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		$control->set_onChange("total_item_exfca(this);");
		
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]',2);
		$this->accumulate('TOTAL');		// scrip para reclacular los dsctos
		$this->add_controls_producto_help();// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		// precio_dolar
		$js = $this->controls['COD_PRODUCTO']->get_onChange();
		$js =$js."precio_dolar(this);";
		$this->controls['COD_PRODUCTO']->set_onChange($js);
		
		$this->controls['NOM_PRODUCTO']->size = 78;
		$this->set_first_focus('COD_PRODUCTO');
		
		// asigna los mandatorys
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
		$this->set_mandatory('PRECIO', 'Precio');
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ITEM', $this->row_count() * 10);
		return $row;
	}
	function update($db)	{
		$sp = 'spu_item_registro_ingreso';
		
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;


			$COD_ITEM_REGISTRO_4D		= $this->get_item($i, 'COD_ITEM_REGISTRO_4D');
			$NUMERO_REGISTRO_INGRESO 	= $this->get_item($i, 'NUMERO_REGISTRO_INGRESO');
			$ITEM 						= $this->get_item($i, 'ITEM');
			$COD_PRODUCTO 				= $this->get_item($i, 'COD_PRODUCTO');
			$CANTIDAD					= $this->get_item($i, 'CANTIDAD');
			$PRECIO						= $this->get_item($i, 'PRECIO');
			$PRECIO						= str_replace(",", "", $PRECIO);
			$PRECIO = ($PRECIO=='') ? 0: $PRECIO;
			
			$TOTAL						= $this->get_item($i, 'TOTAL');
			$TOTAL						= str_replace(",", "", $TOTAL);
			$TOTAL = ($TOTAL=='') ? 0: $TOTAL;
			
			$COD_ITEM_REGISTRO_4D = ($COD_ITEM_REGISTRO_4D=='') ? "null" : $COD_ITEM_REGISTRO_4D;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',
					$COD_ITEM_REGISTRO_4D,
					$NUMERO_REGISTRO_INGRESO,
					$ITEM,
					'$COD_PRODUCTO',
					$CANTIDAD,
					$PRECIO,
					$TOTAL,
					0,
					0,
					0";
					
		if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');			
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
			
			$COD_ITEM_REGISTRO_4D = $this->get_item($i, 'COD_ITEM_REGISTRO_4D', 'delete');
			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_ITEM_REGISTRO_4D")){							
				return false;				
			}			
		}	
		return true;
	}
}

class wi_registro_ingreso extends w_input {
	const K_PARAM_VALOR_DOLAR		= 5;
	function wi_registro_ingreso($cod_item_menu) {
		parent::w_input('registro_ingreso', $cod_item_menu);
		
		$sql = "SELECT NUMERO_REGISTRO_INGRESO
					   ,CONVERT(VARCHAR(10),FECHA_REGISTRO_INGRESO,103) FECHA_REGISTRO_INGRESO
					   ,NRO_PROFORMA
					   ,CONVERT(VARCHAR(10),FECHA_PROF,103) FECHA_PROF
					   ,NRO_EMBARQUE
					   ,REFERENCIA
					   ,COD_MES
					   ,COD_MES COD_MES1
					   ,COD_MES COD_MES2
					   ,COD_MES COD_MES3
					   ,VALOR_DOLAR
					   ,VALOR_DOLAR VALOR_DOLAR_H
					   ,VALOR_DOLAR_ACUERDO
					   ,ALIAS_PROV
					   ,PE.COD_PROVEEDOR_EXT_4D 
					   ,PE.ALIAS_PROVEEDOR_EXT
					   ,PE.NOM_PROVEEDOR_EXT
					   ,PE.DIRECCION
					   ,PE.COD_PAIS
					   ,PE.COD_CIUDAD
					   ,PE.NOM_PAIS_4D
					   ,PE.NOM_CIUDAD_4D
					   ,PE.TELEFONO
					   ,PE.FAX
					   ,PE.OBS
					   ,NUMERO_OC
					   ,convert(varchar(10),FECHA_OC,103) FECHA_OC
					   ,RID.OBS
					   ,dbo.f_numero_entrada(NUMERO_REGISTRO_INGRESO) NRO_ENTRADA_BODEGA
					   ,dbo.f_fecha_entrada(NUMERO_REGISTRO_INGRESO) FECHA_ENTRDA_BODEGA
					   ,EN_PESOS
					   ,TOTAL_EX_FCA
					   ,TOTAL_EX_FCA TOTAL_EX_FCA_H
					   ,TOTAL_EX_FCA TOTAL_EX_FCA_TEF_H
					   ,EMBALAJE
					   ,FLETE_INTERNO
					   ,OTROS1
					   ,FLETE_SCL
					   ,TOTAL_CIF
					   ,TOTAL_CIF TOTAL_CIF_H
					   ,TOTAL_FOB
					   ,TOTAL_FOB TOTAL_FOB_H
					   ,GRUA
					   ,PERMISO_MUNI
					   ,DESCONSOLIDACION
					   ,GASTO_ORDEN_PAGO
					   ,CARTA_CREDITO
					   ,ALMACENAJE
					   ,OTROS
					   ,TOTAL_CIF_PESOS
					   ,TOTAL_CIF_PESOS TOTAL_CIF_PESOS_H
					   ,AD_VALOREM_PORC
					   ,AD_VALOREM
					   ,AGENTE_ADUANA_POR
					   ,AGENTE_ADUANA
					   ,FLETE_CHILE
					   ,TOTAL_OTROS
					   ,TOTAL_OTROS  TOTAL_OTROS_H
					   ,TOTAL_OTROS  TOTAL_OTROS_DTD
					   ,TOTAL_OTROS  TOTAL_OTROS_DTD_H
					   ,TOTAL_DTD
					   ,TOTAL_DTD TOTAL_DTD_H
					   ,NUM_IMPORT
					   ,CONVERT(VARCHAR(10),FECHA_IMPORT,103) FECHA_IMPORT
					   ,CLAUSULA
					   ,FORMA_PAGO
					   ,FACTOR_IMP
					   ,FACTOR_IMP FACTOR_IMP_H
					   ,COBRANZA
					   ,CD_NUM
					   ,CONVERT(VARCHAR(10),FECHA_VTO_BANCO,103) FECHA_VTO_BANCO
					   ,NAVE
					   ,BL
					   ,CONVERT(VARCHAR(10),FECHA_EMBARQUE,103) FECHA_EMBARQUE
					   ,CONVERT(VARCHAR(10),FECHA_BODEGA,103) FECHA_BODEGA
					   ,TOTAL_GASTOS
					   ,TOTAL_GASTOS TOTAL_GASTOS_H
					   ,TOTAL_GASTOS_US
					   ,TOTAL_GASTOS_US TOTAL_GASTOS_US_H
					   ,PAGO_COBERTURA
					   ,convert(numeric(10),dbo.f_get_parametro(5),2) DOLAR_H
					   ,SEGURO
					   ,FLETE
					   ,FLETE FLETE_STATIC
					   ,SEGURO SEGURO_STATIC 
					   ,CONVERT(VARCHAR(10),FECHA_COBERTURA,103) FECHA_COBERTURA
					   ,CONVERT(VARCHAR(10),FECHA_FLETE,103) FECHA_FLETE
					   ,CONVERT(VARCHAR(10),FECHA_SEGURO,103) FECHA_SEGURO
					   ,IMPORTADO_DESDE_4D
					   ,SUBTOTAL_EX_FCA
					   ,SUBTOTAL_EX_FCA SUBTOTAL_EX_FCA_H
					   ,DESCTO_EX_FCA
				 FROM REGISTRO_INGRESO_4D RID LEFT OUTER JOIN PROVEEDOR_EXT PE ON RID.COD_PROV = PE.COD_PROVEEDOR_EXT_4D
				WHERE NUMERO_REGISTRO_INGRESO = {KEY1}";

		$this->dws['wi_registro_ingreso'] = new dw_help_empresa($sql);
		//FICHA 1
		$this->set_first_focus('NUMERO_REGISTRO_INGRESO');
		$this->dws['wi_registro_ingreso']->add_control(new edit_num('NRO_PROFORMA',12,10));
		$this->dws['wi_registro_ingreso']->add_control(new edit_date('FECHA_PROF',12));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_upper('NRO_EMBARQUE',70,100));
		$this->dws['wi_registro_ingreso']->add_control(new static_num('VALOR_DOLAR',2));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('VALOR_DOLAR_H',10, 10, 'hidden'));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_upper('REFERENCIA',70,100));
		$this->dws['wi_registro_ingreso']->add_control(new edit_num('VALOR_DOLAR_ACUERDO',12,10,2));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_upper('NUMERO_OC',70,100));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('DOLAR_H',10, 10, 'hidden'));
		$this->dws['wi_registro_ingreso']->add_control(new edit_date('FECHA_OC',12));
		$this->dws['wi_registro_ingreso']->add_control(new edit_check_box('EN_PESOS',1,0));		
		$this->dws['wi_registro_ingreso']->add_control(new static_text('FECHA_REGISTRO_INGRESO'));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_multiline('OBS',100,5));
		
		$fecha = getdate();
		$ano_actual = $fecha["year"];
		$sql="SELECT COD_MES,
					 NOM_MES 
			 FROM MES";
		$this->dws['wi_registro_ingreso']->add_control($control = new drop_down_dw('COD_MES', $sql, 120));
        $control->set_onChange("valor_dolar_aduanero(this,$ano_actual);cambia_mes(this);");
        
        $this->dws['wi_registro_ingreso']->add_control($control = new drop_down_dw('COD_MES1', $sql, 120));
        $control->set_onChange("valor_dolar_aduanero(this,$ano_actual);cambia_mes(this);");
        
        $this->dws['wi_registro_ingreso']->add_control($control = new drop_down_dw('COD_MES2', $sql, 120));
        $control->set_onChange("valor_dolar_aduanero(this,$ano_actual);cambia_mes(this);");
        
        $this->dws['wi_registro_ingreso']->add_control($control = new drop_down_dw('COD_MES3', $sql, 120));
        $control->set_onChange("valor_dolar_aduanero(this,$ano_actual);cambia_mes(this);");
        
       //FICHA 2
		$this->dws['wi_registro_ingreso']->add_control(new static_num('SUBTOTAL_EX_FCA',2));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('SUBTOTAL_EX_FCA_H',10, 10, 'hidden'));
		$this->dws['wi_registro_ingreso']->add_control($control = new edit_porcentaje('DESCTO_EX_FCA', 12,20,2));
		$control->set_onChange("item_porc_desc();");
		$this->dws['wi_registro_ingreso']->add_control(new static_num('TOTAL_EX_FCA',2));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('TOTAL_EX_FCA_TEF_H',20, 20, 'hidden'));
		//FICHA 3
		$this->dws['wi_registro_ingreso']->add_control(new static_num('TOTAL_EX_FCA_H',2));
		$this->dws['wi_registro_ingreso']->add_control($control= new edit_num('EMBALAJE',12,10,2));
		$control->set_onChange("calcula_costeo();");
		
		$this->dws['wi_registro_ingreso']->add_control($control=new edit_num('FLETE_INTERNO',12,10,2));
		$control->set_onChange("calcula_costeo();");
		$this->dws['wi_registro_ingreso']->add_control($control=new edit_num('OTROS1',12,10,2));
		$control->set_onChange("calcula_costeo();");
		$this->dws['wi_registro_ingreso']->add_control(new static_num('TOTAL_FOB',2));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('TOTAL_FOB_H',20,20,'hidden'));
		
		$this->dws['wi_registro_ingreso']->add_control($control = new edit_num('FLETE',12,10,2));
		$control->set_onChange("cambio_flete_seguro();calcula_costeo();");
		$this->dws['wi_registro_ingreso']->add_control($control = new edit_num('SEGURO',12,10,2));
		$control->set_onChange("cambio_flete_seguro();calcula_costeo();");
		$this->dws['wi_registro_ingreso']->add_control(new static_text('SEGURO_STATIC'));
		$this->dws['wi_registro_ingreso']->add_control(new static_text('FLETE_STATIC'));
		
		$this->dws['wi_registro_ingreso']->add_control($control=new edit_num('FLETE_SCL',12,10,2));
		$control->set_onChange("calcula_costeo();");
		$this->dws['wi_registro_ingreso']->add_control(new static_num('TOTAL_CIF',2));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('TOTAL_CIF_H',20,20,'hidden'));
		$this->dws['wi_registro_ingreso']->add_control(new static_num('TOTAL_CIF_PESOS'));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('TOTAL_CIF_PESOS_H',20,20,'hidden'));
		
		$this->dws['wi_registro_ingreso']->add_control(new static_num('TOTAL_OTROS'));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('TOTAL_OTROS_H',20,20,'hidden'));
		$this->dws['wi_registro_ingreso']->add_control(new static_num('TOTAL_OTROS_DTD'));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('TOTAL_OTROS_DTD_H',20,20,'hidden'));
		
		$this->dws['wi_registro_ingreso']->add_control(new static_num('TOTAL_DTD'));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('TOTAL_DTD_H',20,20,'hidden'));
		
		$this->dws['wi_registro_ingreso']->add_control($control = new edit_porcentaje('AD_VALOREM_PORC',9,10));
		$control->set_onChange("porc_valorem('porc');calcula_costeo();");
		
		$this->dws['wi_registro_ingreso']->add_control($control = new edit_num('AD_VALOREM',12,10));
		$control->set_onChange("porc_valorem('precio');calcula_costeo();");
		
		$this->dws['wi_registro_ingreso']->add_control($control = new edit_porcentaje('AGENTE_ADUANA_POR',9,10));
		$control->set_onChange("porc_agente_aduana('porc');calcula_costeo();");
		
		$this->dws['wi_registro_ingreso']->add_control($control =new edit_num('AGENTE_ADUANA',12,10));
		$control->set_onChange("porc_agente_aduana('precio'); calcula_costeo();");
		
		$this->dws['wi_registro_ingreso']->add_control($control =new edit_num('FLETE_CHILE',12,10));
		$control->set_onChange("calcula_costeo();");
		
		$this->dws['wi_registro_ingreso']->add_control($control =new edit_num('GRUA',12,10));
		$control->set_onChange("calcula_costeo();");
		$this->dws['wi_registro_ingreso']->add_control(new edit_num('PERMISO_MUNI',12,10));
		$this->dws['wi_registro_ingreso']->add_control($control =new edit_num('DESCONSOLIDACION',12,10));
		$control->set_onChange("calcula_costeo();");
		$this->dws['wi_registro_ingreso']->add_control($control =new edit_num('GASTO_ORDEN_PAGO',12,10));
		$control->set_onChange("calcula_costeo();");
		$this->dws['wi_registro_ingreso']->add_control($control =new edit_num('CARTA_CREDITO',12,10));
		$control->set_onChange("calcula_costeo();");
		$this->dws['wi_registro_ingreso']->add_control($control =new edit_num('ALMACENAJE',12,10));
		$control->set_onChange("calcula_costeo();");
		$this->dws['wi_registro_ingreso']->add_control($control =new edit_num('OTROS',12,10));
		$control->set_onChange("calcula_costeo();");
		
		$this->dws['wi_registro_ingreso']->add_control(new static_num('TOTAL_GASTOS'));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('TOTAL_GASTOS_H',20,20,'hidden'));
		$this->dws['wi_registro_ingreso']->add_control(new static_num('TOTAL_GASTOS_US',2));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('TOTAL_GASTOS_US_H',20,20,'hidden'));
		$this->dws['wi_registro_ingreso']->add_control(new static_num('FACTOR_IMP',2));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text('FACTOR_IMP_H',10,10,'hidden'));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_upper('NUM_IMPORT',70,100));
		$this->dws['wi_registro_ingreso']->add_control(new edit_date('FECHA_IMPORT',12));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_upper('CLAUSULA',70,100));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_upper('FORMA_PAGO',70,100));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_upper('COBRANZA',70,100));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_upper('CD_NUM',70,100));
		$this->dws['wi_registro_ingreso']->add_control(new edit_date('FECHA_VTO_BANCO',12));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_upper('NAVE',70,100));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_upper('BL',70,100));
		$this->dws['wi_registro_ingreso']->add_control(new edit_date('FECHA_EMBARQUE',12));
		$this->dws['wi_registro_ingreso']->add_control(new edit_date('FECHA_BODEGA',12));
		$this->dws['wi_registro_ingreso']->add_control(new edit_date('FECHA_BODEGA',12));
		$this->dws['wi_registro_ingreso']->add_control(new edit_text_upper('PAGO_COBERTURA',70,100));
		$this->dws['wi_registro_ingreso']->add_control(new edit_date('FECHA_BODEGA',12));
		$this->dws['wi_registro_ingreso']->add_control(new edit_date('FECHA_COBERTURA',12));
		$this->dws['wi_registro_ingreso']->add_control(new edit_date('FECHA_FLETE',12));
		$this->dws['wi_registro_ingreso']->add_control(new edit_date('FECHA_SEGURA',12));
	
		$this->dws['dw_item_registro_ingreso'] = new dw_item_registro_ingreso();
		//mandatory
		$this->dws['wi_registro_ingreso']->set_mandatory('NRO_PROFORMA', 'Nro Proforma');
		$this->dws['wi_registro_ingreso']->set_mandatory('FECHA_PROF', 'Fecha Proforma');
		$this->dws['wi_registro_ingreso']->set_mandatory('NRO_EMBARQUE', 'Nro Enbarque');
		$this->dws['wi_registro_ingreso']->set_mandatory('VALOR_DOLAR', 'Valor Dolar');
		$this->dws['wi_registro_ingreso']->set_mandatory('REFERENCIA', 'Referencia');
		$this->dws['wi_registro_ingreso']->set_mandatory('VALOR_DOLAR_ACUERDO', 'Valor Dolar Acuerdo');
		$this->dws['wi_registro_ingreso']->set_mandatory('NUMERO_OC', 'Nro Oc');
		$this->dws['wi_registro_ingreso']->set_mandatory('FECHA_OC', 'Fecha Oc');
		$this->dws['wi_registro_ingreso']->set_mandatory('EN_PESOS', 'En Pesos');
		$this->dws['wi_registro_ingreso']->set_mandatory('OBS', 'Obs');
		$this->dws['wi_registro_ingreso']->set_mandatory('COD_PROVEEDOR_EXT_4D', 'Codigo Prov');
	}
	function get_key() {
		return $this->dws['wi_registro_ingreso']->get_item(0, 'NUMERO_REGISTRO_INGRESO');
	}
	function new_record() {
		$this->dws['wi_registro_ingreso']->insert_row();
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$this->dws['wi_registro_ingreso']->set_item(0, 'DOLAR_H', $this->get_parametro(5));
		$this->dws['wi_registro_ingreso']->set_item(0, 'OBS', '');
		//$this->dws['wi_registro_ingreso']->set_item(0, 'DESCTO_EX_FCA', 0);
		$this->dws['wi_registro_ingreso']->set_item(0, 'AD_VALOREM_PORC', 0);
		$this->dws['wi_registro_ingreso']->set_item(0, 'AGENTE_ADUANA_POR', 0);
		 $ano =$db->current_year();
		 $mes =$db->current_month();
		
		$this->dws['wi_registro_ingreso']->set_item(0, 'COD_MES', $mes);
		$this->dws['wi_registro_ingreso']->set_item(0, 'COD_MES1', $mes);
		$this->dws['wi_registro_ingreso']->set_item(0, 'COD_MES2', $mes);
		$this->dws['wi_registro_ingreso']->set_item(0, 'COD_MES3', $mes);
		 $sql ="select  DOLAR_ADUANERO ,
					   DOLAR_ACUERDO
				from DOLAR_TODOINOX DT , ANO A
				where COD_MES = $mes
						AND DT.COD_ANO = A.COD_ANO
						AND A.ANO = $ano";
		$result = $db->build_results($sql);
		$dolar_aduanero = $result[0]['DOLAR_ADUANERO'];
		if($dolar_aduanero == '')
			$dolar_aduanero = '0.00';
		
		$dolar_acuerdo  = $result[0]['DOLAR_ACUERDO'];
		if($dolar_aduanero == '')
			$dolar_acuerdo = '0.00';
		
		$this->dws['wi_registro_ingreso']->set_item(0, 'VALOR_DOLAR',$dolar_aduanero );
		$this->dws['wi_registro_ingreso']->set_item(0, 'VALOR_DOLAR_H',$dolar_aduanero );
		$this->dws['wi_registro_ingreso']->set_item(0, 'VALOR_DOLAR_ACUERDO', $dolar_acuerdo );
		$this->dws['wi_registro_ingreso']->set_entrable('VALOR_DOLAR', false);
		$this->dws['wi_registro_ingreso']->set_entrable('VALOR_DOLAR_ACUERDO', false);
	}
	function load_record() {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$nro_registro_ingreso = $this->get_item_wo($this->current_record, 'NUMERO_REGISTRO_INGRESO');
		$this->dws['wi_registro_ingreso']->retrieve($nro_registro_ingreso);
		$this->dws['dw_item_registro_ingreso']->retrieve($nro_registro_ingreso);
		
		$sql = "select importado_desde_4d
		from registro_ingreso_4d
		where numero_registro_ingreso = $nro_registro_ingreso";
		
		$result = $db->build_results($sql);
		$importado_desde_4d = $result[0]['importado_desde_4d'];
		if($importado_desde_4d == 'N'){
			
		}			
		if($importado_desde_4d == 'S'){
			$nom_ciudad = $this->dws['wi_registro_ingreso']->get_item(0, 'NOM_CIUDAD_4D');
			$nom_pais = $this->dws['wi_registro_ingreso']->get_item(0, 'NOM_PAIS_4D');
			$this->dws['wi_registro_ingreso']->set_item(0, 'COD_CIUDAD', $nom_ciudad);
			$this->dws['wi_registro_ingreso']->set_item(0, 'COD_PAIS', $nom_pais);
			$this->dws['wi_registro_ingreso']->set_entrable('NRO_PROFORMA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FECHA_PROF', false);
			$this->dws['wi_registro_ingreso']->set_entrable('NRO_EMBARQUE', false);
			$this->dws['wi_registro_ingreso']->set_entrable('VALOR_DOLAR', false);
			$this->dws['wi_registro_ingreso']->set_entrable('REFERENCIA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('VALOR_DOLAR_ACUERDO', false);
			$this->dws['wi_registro_ingreso']->set_entrable('NUMERO_OC', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FECHA_OC', false);
			$this->dws['wi_registro_ingreso']->set_entrable('EN_PESOS', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FECHA_REGISTRO_INGRESO', false);
			$this->dws['wi_registro_ingreso']->set_entrable('OBS', false);
			$this->dws['wi_registro_ingreso']->set_entrable('COD_MES', false);
			$this->dws['wi_registro_ingreso']->set_entrable('COD_MES1', false);
			$this->dws['wi_registro_ingreso']->set_entrable('COD_MES2', false);
			$this->dws['wi_registro_ingreso']->set_entrable('COD_MES3', false);
			$this->dws['wi_registro_ingreso']->set_entrable('NRO_PROFORMA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('DESCTO_EX_FCA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('TOTAL_EX_FCA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('EMBALAJE', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FLETE_INTERNO', false);
			$this->dws['wi_registro_ingreso']->set_entrable('OTROS1', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FLETE', false);
			$this->dws['wi_registro_ingreso']->set_entrable('SEGURO', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FLETE_SCL', false);
			$this->dws['wi_registro_ingreso']->set_entrable('TOTAL_CIF', false);
			$this->dws['wi_registro_ingreso']->set_entrable('TOTAL_CIF_PESOS', false);
			$this->dws['wi_registro_ingreso']->set_entrable('AD_VALOREM_PORC', false);
			$this->dws['wi_registro_ingreso']->set_entrable('AD_VALOREM', false);
			$this->dws['wi_registro_ingreso']->set_entrable('AGENTE_ADUANA_POR', false);
			$this->dws['wi_registro_ingreso']->set_entrable('AGENTE_ADUANA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FLETE_CHILE', false);
			$this->dws['wi_registro_ingreso']->set_entrable('GRUA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('PERMISO_MUNI', false);
			$this->dws['wi_registro_ingreso']->set_entrable('DESCONSOLIDACION', false);
			$this->dws['wi_registro_ingreso']->set_entrable('GASTO_ORDEN_PAGO', false);
			$this->dws['wi_registro_ingreso']->set_entrable('CARTA_CREDITO', false);
			$this->dws['wi_registro_ingreso']->set_entrable('ALMACENAJE', false);
			$this->dws['wi_registro_ingreso']->set_entrable('OTROS', false);
			$this->dws['wi_registro_ingreso']->set_entrable('NUM_IMPORT', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FECHA_IMPORT', false);
			$this->dws['wi_registro_ingreso']->set_entrable('CLAUSULA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FORMA_PAGO', false);
			$this->dws['wi_registro_ingreso']->set_entrable('COBRANZA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('CD_NUM', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FECHA_VTO_BANCO', false);
			$this->dws['wi_registro_ingreso']->set_entrable('NAVE', false);
			$this->dws['wi_registro_ingreso']->set_entrable('BL', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FECHA_EMBARQUE', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FECHA_BODEGA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('TOTAL_GASTO', false);
			$this->dws['wi_registro_ingreso']->set_entrable('PAGO_COBERTURA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FECHA_COBERTURA', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FECHA_FLETE', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FECHA_SEGURO', false);
			$this->dws['wi_registro_ingreso']->set_entrable('TOTAL_GASTO', false);
			$this->dws['wi_registro_ingreso']->set_entrable('TOTAL_GASTOS_US', false);
			$this->dws['wi_registro_ingreso']->set_entrable('FACTOR_IMP', false);
			$this->dws['dw_item_registro_ingreso']->set_entrable_dw(false);
			$this->dws['wi_registro_ingreso']->set_entrable('COD_PROVEEDOR_EXT_4D', false);
			$this->dws['wi_registro_ingreso']->set_entrable('ALIAS_PROVEEDOR_EXT', false);
			$this->dws['wi_registro_ingreso']->set_entrable('NOM_PROVEEDOR_EXT', false);
		}
			$nom_ciudad = $this->dws['wi_registro_ingreso']->get_item(0, 'NOM_CIUDAD_4D');
			$nom_pais = $this->dws['wi_registro_ingreso']->get_item(0, 'NOM_PAIS_4D');
			$this->dws['wi_registro_ingreso']->set_item(0, 'COD_CIUDAD', $nom_ciudad);
			$this->dws['wi_registro_ingreso']->set_item(0, 'COD_PAIS', $nom_pais);
		
	}
	function save_record($db) {
		$NRO_REGISTRO_INGRESO 	= $this->dws['wi_registro_ingreso']->get_item(0, 'NUMERO_REGISTRO_INGRESO');
		$NRO_PROFORMA 			= $this->dws['wi_registro_ingreso']->get_item(0, 'NRO_PROFORMA');
		$FECHA_PROF 			= $this->dws['wi_registro_ingreso']->get_item(0, 'FECHA_PROF');
		$NRO_EMBARQUE 			= $this->dws['wi_registro_ingreso']->get_item(0, 'NRO_EMBARQUE');
		$VALOR_DOLAR 			= $this->dws['wi_registro_ingreso']->get_item(0, 'VALOR_DOLAR_H');
		$COD_PROVEEDOR_EXT_4D 	= $this->dws['wi_registro_ingreso']->get_item(0, 'COD_PROVEEDOR_EXT_4D');
		$NUMERO_OC 				= $this->dws['wi_registro_ingreso']->get_item(0, 'NUMERO_OC');
		$FECHA_OC 				= $this->dws['wi_registro_ingreso']->get_item(0, 'FECHA_OC');
		$OBS					= $this->dws['wi_registro_ingreso']->get_item(0, 'OBS'); 
		$EN_PESOS				= $this->dws['wi_registro_ingreso']->get_item(0, 'EN_PESOS');
		$COD_PROV				= $this->dws['wi_registro_ingreso']->get_item(0, 'COD_PROVEEDOR_EXT_4D');
		$TOTAL_EX_FCA			= $this->dws['wi_registro_ingreso']->get_item(0, 'TOTAL_EX_FCA_TEF_H');
		$EMBALAJE				= $this->dws['wi_registro_ingreso']->get_item(0, 'EMBALAJE');
		$FLETE_INTERNO			= $this->dws['wi_registro_ingreso']->get_item(0, 'FLETE_INTERNO');
		$TOTAL_OTROS			= $this->dws['wi_registro_ingreso']->get_item(0, 'TOTAL_OTROS_H');
		$TOTAL_FOB				= $this->dws['wi_registro_ingreso']->get_item(0, 'TOTAL_FOB_H');
		$FLETE					= $this->dws['wi_registro_ingreso']->get_item(0, 'FLETE');
		$SEGURO					= $this->dws['wi_registro_ingreso']->get_item(0, 'SEGURO');
		$TOTAL_CIF				= $this->dws['wi_registro_ingreso']->get_item(0, 'TOTAL_CIF_H');
		$TOTAL_CIF_PESOS		= $this->dws['wi_registro_ingreso']->get_item(0, 'TOTAL_CIF_PESOS_H');
		$AD_VALOREM_PORC		= $this->dws['wi_registro_ingreso']->get_item(0, 'AD_VALOREM_PORC');
		$AD_VALOREM				= $this->dws['wi_registro_ingreso']->get_item(0, 'AD_VALOREM');	
		$AGENTE_ADUANA_POR		= $this->dws['wi_registro_ingreso']->get_item(0, 'AGENTE_ADUANA_POR');
		$AGENTE_ADUANA			= $this->dws['wi_registro_ingreso']->get_item(0, 'AGENTE_ADUANA');
		$FLETE_CHILE			= $this->dws['wi_registro_ingreso']->get_item(0, 'FLETE_CHILE');
		$OTROS1					= $this->dws['wi_registro_ingreso']->get_item(0, 'OTROS1');
		$TOTAL_DTD				= $this->dws['wi_registro_ingreso']->get_item(0, 'TOTAL_DTD_H');
		$MES_DOLAR				= $this->dws['wi_registro_ingreso']->get_item(0, 'COD_MES');
		$REFERENCIA				= $this->dws['wi_registro_ingreso']->get_item(0, 'REFERENCIA');
		$NUM_IMPORT				= $this->dws['wi_registro_ingreso']->get_item(0, 'NUM_IMPORT');
		$FECHA_IMPORT			= $this->dws['wi_registro_ingreso']->get_item(0, 'FECHA_IMPORT');
		$CLAUSULA				= $this->dws['wi_registro_ingreso']->get_item(0, 'CLAUSULA');	
		$FECHA_REGISTRO_INGRESO	= $this->dws['wi_registro_ingreso']->get_item(0, 'FECHA_REGISTRO_INGRESO');
		$TOTAL_GASTOS			= $this->dws['wi_registro_ingreso']->get_item(0, 'TOTAL_GASTOS_H');
		$TOTAL_GASTOS_US		= $this->dws['wi_registro_ingreso']->get_item(0, 'TOTAL_GASTOS_US_H');
		$FACTOR_IMP	 			= $this->dws['wi_registro_ingreso']->get_item(0, 'FACTOR_IMP_H');
		$COBRANZA				= $this->dws['wi_registro_ingreso']->get_item(0, 'COBRANZA');
		$CD_NUM					= $this->dws['wi_registro_ingreso']->get_item(0, 'CD_NUM');
		$FECHA_VTO_BANCO		= $this->dws['wi_registro_ingreso']->get_item(0, 'FECHA_VTO_BANCO');
		$NAVE					= $this->dws['wi_registro_ingreso']->get_item(0, 'NAVE');
		$BL						= $this->dws['wi_registro_ingreso']->get_item(0, 'BL');
		$FECHA_EMBARQUE			= $this->dws['wi_registro_ingreso']->get_item(0, 'FECHA_EMBARQUE');
		$FECHA_BODEGA			= $this->dws['wi_registro_ingreso']->get_item(0, 'FECHA_BODEGA');
		$TOTAL_GASTO			= 0;	
		$PAGO_COBERTURA			= $this->dws['wi_registro_ingreso']->get_item(0, 'PAGO_COBERTURA');
		$FECHA_COBERTURA		= $this->dws['wi_registro_ingreso']->get_item(0, 'FECHA_COBERTURA');
		$FECHA_FLETE			= $this->dws['wi_registro_ingreso']->get_item(0, 'FECHA_FLETE');
		$FECHA_SEGURO			= $this->dws['wi_registro_ingreso']->get_item(0, 'FECHA_SEGURO');
		$FORMA_PAGO				= $this->dws['wi_registro_ingreso']->get_item(0, 'FORMA_PAGO');
		$ALIAS_PROV				= $this->dws['wi_registro_ingreso']->get_item(0, 'ALIAS_PROVEEDOR_EXT');
		$RUT_PROV				= 0;// VER
		$VALOR_DOLAR_ACUERDO	= $this->dws['wi_registro_ingreso']->get_item(0, 'VALOR_DOLAR_ACUERDO');
		$GRUA					= $this->dws['wi_registro_ingreso']->get_item(0, 'GRUA');
		$PERMISO_MUNI			= $this->dws['wi_registro_ingreso']->get_item(0, 'PERMISO_MUNI');
		$DESCONSOLIDACION		= $this->dws['wi_registro_ingreso']->get_item(0, 'DESCONSOLIDACION');
		$CARTA_CREDITO			= $this->dws['wi_registro_ingreso']->get_item(0, 'CARTA_CREDITO');
		$ALMACENAJE				= $this->dws['wi_registro_ingreso']->get_item(0, 'ALMACENAJE');
		$OTROS					= $this->dws['wi_registro_ingreso']->get_item(0, 'OTROS');
		$ANO_PROF				= 0 ; //VER
		$GASTO_ORDEN_PAGO		= $this->dws['wi_registro_ingreso']->get_item(0, 'GASTO_ORDEN_PAGO');
		$SUBTOTAL_EX_FCA		= $this->dws['wi_registro_ingreso']->get_item(0, 'SUBTOTAL_EX_FCA_H');
		$DESCTO_EX_FCA			= $this->dws['wi_registro_ingreso']->get_item(0, 'DESCTO_EX_FCA');
		$FLETE_SCL				= $this->dws['wi_registro_ingreso']->get_item(0, 'FLETE_SCL');
		
		$SUBTOTAL_EX_FCA = ($SUBTOTAL_EX_FCA=='') ? 0 : $SUBTOTAL_EX_FCA;
		$TOTAL_EX_FCA = ($TOTAL_EX_FCA=='') ? 0 : $TOTAL_EX_FCA;
		$NRO_PROFORMA = ($NRO_PROFORMA=='') ? 0 : $NRO_PROFORMA;
		$EMBALAJE = ($EMBALAJE=='') ? 0 : $EMBALAJE;
		$FLETE_INTERNO = ($FLETE_INTERNO=='') ? 0 : $FLETE_INTERNO;
		$TOTAL_OTROS = ($TOTAL_OTROS=='') ? 0 : $TOTAL_OTROS;
		$TOTAL_FOB = ($TOTAL_FOB=='') ? 0 : $TOTAL_FOB;
		$FLETE = ($FLETE=='') ? 0 : $FLETE;
		$SEGURO = ($SEGURO=='') ? 0 : $SEGURO;
		$TOTAL_CIF	= ($TOTAL_CIF=='') ? 0 : $TOTAL_CIF;									
		$TOTAL_CIF_PESOS = ($TOTAL_CIF_PESOS=='') ? 0 : $TOTAL_CIF_PESOS;								
		$AD_VALOREM_PORC = ($AD_VALOREM_PORC=='') ? 0 : $AD_VALOREM_PORC;
		$AD_VALOREM = ($AD_VALOREM=='') ? 0 : $AD_VALOREM;
		$AD_VALOREM = ($AD_VALOREM=='NaN') ? 0 : $AD_VALOREM;
		$AGENTE_ADUANA_POR = ($AGENTE_ADUANA_POR=='') ? 0 : $AGENTE_ADUANA_POR;
		$AGENTE_ADUANA = ($AGENTE_ADUANA=='') ? 0 : $AGENTE_ADUANA;								
		$FLETE_CHILE = ($FLETE_CHILE=='') ? 0 : $FLETE_CHILE;
		$OTROS1 = ($OTROS1=='') ? 0 : $OTROS1;
		$TOTAL_DTD = ($TOTAL_DTD=='') ? 0 : $TOTAL_DTD;
		$MES_DOLAR = ($MES_DOLAR=='') ? 0 : $MES_DOLAR;
		$TOTAL_GASTOS = ($TOTAL_GASTOS=='') ? 0 : $TOTAL_GASTOS;
		$TOTAL_GASTOS_US = ($TOTAL_GASTOS_US=='') ? 0 : $TOTAL_GASTOS_US;
		$FACTOR_IMP = ($FACTOR_IMP=='') ? 0 : $FACTOR_IMP;
		$CD_NUM = ($CD_NUM=='') ? 0 : $CD_NUM;																				
		$TOTAL_GASTO = ($TOTAL_GASTO=='') ? 0 : $TOTAL_GASTO;
		$PAGO_COBERTURA = ($PAGO_COBERTURA=='') ? 0 : $PAGO_COBERTURA;
		$NUM_IMPORT	= ($NUM_IMPORT=='') ? 0 : $NUM_IMPORT;
		$VALOR_DOLAR = ($VALOR_DOLAR=='') ? 0 : $VALOR_DOLAR;
		$VALOR_DOLAR_ACUERDO = ($VALOR_DOLAR_ACUERDO=='') ? 0 : $VALOR_DOLAR_ACUERDO;
		$GRUA = ($GRUA=='') ? 0 : $GRUA;
		$PERMISO_MUNI  = ($PERMISO_MUNI =='') ? 0 : $PERMISO_MUNI ;
		$DESCONSOLIDACION  = ($DESCONSOLIDACION =='') ? 0 : $DESCONSOLIDACION ;
		$CARTA_CREDITO  = ($CARTA_CREDITO =='') ? 0 : $CARTA_CREDITO ;
		$ALMACENAJE  = ($ALMACENAJE =='') ? 0 : $ALMACENAJE ;
		$OTROS = ($OTROS =='') ? 0 : $OTROS ;
		$ANO_PROF = ($ANO_PROF =='') ? 0 : $ANO_PROF ;
		$GASTO_ORDEN_PAGO = ($GASTO_ORDEN_PAGO =='') ? 0 : $GASTO_ORDEN_PAGO ;
		$DESCTO_EX_FCA = ($DESCTO_EX_FCA =='') ? 0 : $DESCTO_EX_FCA ;
		$FLETE_SCL = ($FLETE_SCL =='') ? 0 : $FLETE_SCL ;
		
		$BL = ($BL =='') ? NULL : $BL ; 
		
		
		
		//fechas
		$FECHA_PROF		= $this->str2date($FECHA_PROF);
		$FECHA_OC		= $this->str2date($FECHA_OC);
		$FECHA_IMPORT	= $this->str2date($FECHA_IMPORT);
		$FECHA_REGISTRO_INGRESO = $this->str2date($FECHA_REGISTRO_INGRESO);
		$FECHA_VTO_BANCO = $this->str2date($FECHA_VTO_BANCO);
		$FECHA_EMBARQUE	= $this->str2date($FECHA_EMBARQUE);	
		$FECHA_BODEGA	= $this->str2date($FECHA_BODEGA);		
		$FECHA_COBERTURA = 	$this->str2date($FECHA_COBERTURA);
		$FECHA_FLETE	=	$this->str2date($FECHA_FLETE);
		$FECHA_SEGURO	=	$this->str2date($FECHA_SEGURO);
		
		$sp = 'spu_registro_ingreso';
	    if ($this->is_new_record()){
	    	$operacion = 'INSERT';

	    $sql = "select (NUMERO_REGISTRO_INGRESO + 1) NUMERO_REGISTRO_INGRESO
				from REGISTRO_INGRESO_4D
				order by NUMERO_REGISTRO_INGRESO desc";
		$result = $db->build_results($sql);
		$NRO_REGISTRO_INGRESO = $result[0]['NUMERO_REGISTRO_INGRESO'];
	    }
	    else{
	    	$operacion = 'UPDATE';
	    }
	     
	    			$param	= "'$operacion'
				    			,$NRO_PROFORMA		    
				    			,$FECHA_PROF
								,'$COD_PROV'	
								,'$NUMERO_OC'	
								,$FECHA_OC	
								,$NRO_REGISTRO_INGRESO	
								,'$OBS'	
								,$TOTAL_EX_FCA	
								,$EMBALAJE	
								,$FLETE_INTERNO	
								,$TOTAL_OTROS	
								,$TOTAL_FOB	 
								,$FLETE 	
								,$SEGURO	
								,$TOTAL_CIF	
								,$TOTAL_CIF_PESOS	
								,$AD_VALOREM_PORC	
								,$AD_VALOREM
								,0
								,0
								,$AGENTE_ADUANA_POR	
								,$AGENTE_ADUANA	
								,$FLETE_CHILE	
								,$OTROS1	
								,$TOTAL_DTD	
								,NULL	
								,'$NRO_EMBARQUE'	
								,'$REFERENCIA'	
								,'$NUM_IMPORT'	
								,$FECHA_IMPORT	
								,'$CLAUSULA'	
								,NULL
								,$EN_PESOS	
								,NULL
								,$TOTAL_GASTOS	
								,$TOTAL_GASTOS_US	
								,$FACTOR_IMP	
								,'$COBRANZA'
								,$CD_NUM	
								,$FECHA_VTO_BANCO	
								,'$NAVE'	
								,'$BL'	
								,$FECHA_EMBARQUE	
								,$FECHA_BODEGA	
								,$TOTAL_GASTO	
								,$PAGO_COBERTURA	
								,$FECHA_COBERTURA	
								,$FECHA_FLETE
								,$FECHA_SEGURO	
								,'$FORMA_PAGO'
								,$MES_DOLAR
								,$VALOR_DOLAR	
								,'$ALIAS_PROV'	
								,'$RUT_PROV'	
								,$VALOR_DOLAR_ACUERDO	
								,$GRUA	
								,$PERMISO_MUNI	
								,$DESCONSOLIDACION	
								,$CARTA_CREDITO	
								,$ALMACENAJE	
								,$OTROS	
								,$ANO_PROF	
								,$GASTO_ORDEN_PAGO	
								,$SUBTOTAL_EX_FCA	
								,$DESCTO_EX_FCA	
								,$FLETE_SCL
								,'N'";	

			if ($db->EXECUTE_SP($sp, $param)){
				if ($this->is_new_record()) {
					$sql ="SELECT MAX(NUMERO_REGISTRO_INGRESO) NUMERO_REGISTRO_INGRESO FROM REGISTRO_INGRESO_4D";
					$result = $db->build_results($sql);
					$NRO_REGISTRO_INGRESO = $result[0]['NUMERO_REGISTRO_INGRESO'];
					$this->dws['wi_registro_ingreso']->set_item(0, 'NUMERO_REGISTRO_INGRESO', $NRO_REGISTRO_INGRESO);
				}
				for ($i=0; $i<$this->dws['dw_item_registro_ingreso']->row_count(); $i++){
					$this->dws['dw_item_registro_ingreso']->set_item($i, 'NUMERO_REGISTRO_INGRESO', $NRO_REGISTRO_INGRESO);
				}
				if (!$this->dws['dw_item_registro_ingreso']->update($db)){
					return false;
				}
			
				return true;
		}
		return false;		
	}
	function print_record() {
		$cod_registro_ingreso  = $this->get_key();
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql= "SELECT NUMERO_REGISTRO_INGRESO,
						   NRO_EMBARQUE,
						   REFERENCIA,
						   VALOR_DOLAR,
						   NRO_PROFORMA,
						   convert(varchar(10),FECHA_PROF ,103) FECHA_PROF,
						   COD_PROV,
						   TOTAL_EX_FCA,
						   EMBALAJE,
						   FLETE,
						   OTROS,
						   TOTAL_FOB,
						   FLETE,
						   SEGURO,
						   FLETE_SCL,
						   TOTAL_CIF,
						   TOTAL_CIF_PESOS,
						   AD_VALOREM,
						   AGENTE_ADUANA,
						   FLETE_CHILE,
						   TOTAL_OTROS,
						   TOTAL_GASTOS,
						   TOTAL_GASTOS_US,
						   FACTOR_IMP,
						   GRUA,
						   PERMISO_MUNI,
						   DESCONSOLIDACION,
						   GASTO_ORDEN_PAGO,
						   CARTA_CREDITO,
						   ALMACENAJE,
						   OTROS,
						   TOTAL_OTROS,
						   OBS,
						   AD_VALOREM_PORC,
						   AGENTE_ADUANA_POR,
						   REFERENCIA
					FROM REGISTRO_INGRESO_4D
					WHERE NUMERO_REGISTRO_INGRESO = $cod_registro_ingreso";
			//// reporte
			$labels = array();
			$labels['strNUMERO_REGISTRO_INGRESO'] = $numero_registro_ingreso;
			$rpt = new print_registro_ingreso($sql, $this->root_dir.'appl/registro_ingreso/registro_ingreso.xml', $labels, "Registro Ingreso".$numero_registro_ingreso, false);
			$this->_load_record();
			return true;
		}
}
class print_registro_ingreso extends reporte {	
	function print_registro_ingreso($sql, $xml, $labels=array(), $titulo, $con_logo , $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	function modifica_pdf(&$pdf) {
		$pdf->SetAutoPageBreak(true);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		
		$nro_registro_ingreso = $result[0]['NUMERO_REGISTRO_INGRESO'];
		$cod_prov = $result[0]['COD_PROV'];
		$dolar_aduanero = $result[0]['VALOR_DOLAR'];
		$nro_factura = $result[0]['NRO_PROFORMA'];
		$fecha_factura = $result[0]['FECHA_PROF'];
		$nro_embarque = $result[0]['NRO_EMBARQUE'];
		$tota_exfca = $result[0]['TOTAL_EX_FCA'];
		$flete_interno = $result[0]['FLETE_INTERNO'];
		$embalaje = $result[0]['EMBALAJE'];
		$otros = $result[0]['OTROS'];
		$total_fob = $result[0]['TOTAL_FOB'];
		$flete = $result[0]['FLETE'];
		$seguro = $result[0]['SEGURO'];
		$flete_scl = $result[0]['FLETE_SCL'];
		$total_cif = $result[0]['TOTAL_CIF'];
		$total_cif_pesos = $result[0]['TOTAL_CIF_PESOS'];
		$ad_valorem = $result[0]['AD_VALOREM'];
		$agente_aduana = $result[0]['AGENTE_ADUANA'];
		$flete_chile = $result[0]['FLETE_CHILE'];
		$total_otros = $result[0]['TOTAL_OTROS'];
		$total_gasto = $result[0]['TOTAL_GASTOS'];
		$obs = $result[0]['OBS'];
		$total_gasto_us = $result[0]['TOTAL_GASTOS_US'];
		$factor_imp = $result[0]['FACTOR_IMP'];
		$grua = $result[0]['GRUA'];
		$permiso_muni = $result[0]['PERMISO_MUNI'];
		$desconsolidacion = $result[0]['DESCONSOLIDACION'];
		$gato_orden_pago = $result[0]['GASTO_ORDEN_PAGO'];
		$gasto_l_c = $result[0]['CARTA_CREDITO'];
		$almacenaje = $result[0]['ALMACENAJE'];
		$otros = $result[0]['OTROS'];
		$ad_valorem_porc = $result[0]['AD_VALOREM_PORC'];
		$agente_aduana_porc = $result[0]['AGENTE_ADUANA_POR'];
		$referencia = $result[0]['REFERENCIA'];

		$sql_prov = "SELECT COD_PROVEEDOR_EXT,
						   COD_PROVEEDOR_EXT_4D,
						   NOM_PROVEEDOR_EXT,
						   DIRECCION,
						   TELEFONO,
						   NOM_PAIS_4D,
						   NOM_CIUDAD_4D,
						   FAX
					FROM PROVEEDOR_EXT
					WHERE COD_PROVEEDOR_EXT_4D =  '$cod_prov'";
					
		$result_prov = $db->build_results($sql_prov);
		$cod_proveedor = $result_prov[0]['COD_PROVEEDOR_EXT_4D'];
		$nom_proveedor = $result_prov[0]['NOM_PROVEEDOR_EXT'];
		$direccion 	   = $result_prov[0]['DIRECCION'];
		$nom_pais 	   = $result_prov[0]['NOM_PAIS_4D'];
		$nom_ciudad    = $result_prov[0]['NOM_CIUDAD_4D'];
		$fono    	   = $result_prov[0]['TELEFONO'];
		$fax    	   = $result_prov[0]['FAX'];

		 $fecha=strftime( "%d/%m/%Y", time() );
		 $hora=strftime( "%H:%M", time() );

		 $pdf->SetFont('Arial','',9);
		$pdf->Text(30, 50,'Fecha   '.$fecha);
		$pdf->Text(30, 62,'Hora    '.$hora);
		$pdf->Text(547, 85,'PAG:  '.$pdf->PageNo());
		
		 $pdf->SetFont('Arial','B',12);
		$pdf->Text(15, 35,'COMERCIAL TODOINOX LTDA');
		
		$pdf->Text(390, 110,'REGISTRO DE INGRESO Nº');
		$pdf->Text(552, 110,$nro_registro_ingreso);
		$pdf->SetFont('Arial','B',10);		
		$pdf->SetXY(15, 115);
		$pdf->MultiCell(570, 120,'',1);
		$pdf->Text(440, 130,'DOLAR ADUANERO : ');
		$pdf->Text(545, 130,$dolar_aduanero);
		//PROVEEDOR
		$pdf->Text(35, 150,'PROVEEDOR:');
		$pdf->Text(125, 150,$cod_prov);	
		$pdf->Text(125, 150,'____________');	
		$pdf->Text(250, 150,'FACT. Nº : ');
		$pdf->Text(300, 150,$nro_factura);	
		$pdf->Text(300, 150,'____________');
		$pdf->Text(460, 150,'FECHA : ');
		$pdf->Text(510, 150,$fecha_factura);	
		$pdf->Text(510, 150,'____________');
		//ENBARQUE
		$pdf->SetFont('Arial','',9);
		$pdf->Text(35, 167,'EMBARQUE Nº:');
		$pdf->Text(125, 167,$nro_embarque);	
		$pdf->Text(125, 167,'________________________________');	
		$pdf->Text(321, 167,'MERCADERIA : ');
		$pdf->Text(399, 167,substr($referencia,0,40));	
		$pdf->Text(399, 167,'________________________________');
		//RAZON SOCIAL
		$pdf->Text(35, 187,'RAZON SOLCIAL:');
		$pdf->Text(125, 187,$nom_proveedor);	
		$pdf->Text(125, 187,'________________________________________________________');	
		//DIRECCION
		$pdf->Text(35, 207,'DIRECCION:');
		$pdf->Text(125, 207,$direccion);	
		$pdf->Text(125, 207,'____________________________________________');	
		$pdf->Text(380, 207,$nom_ciudad);	
		$pdf->Text(380, 207,'____________');
		$pdf->Text(458, 207,$nom_pais);	
		$pdf->Text(458, 207,'____________');
		//FONO
		$pdf->Text(35, 227,'FONO:');
		$pdf->Text(125, 227,$fono);	
		$pdf->Text(125, 227,'_____________');	
		$pdf->Text(220, 227,'FAX:');
		$pdf->Text(260, 227,$fax);	
		$pdf->Text(260, 227,'_____________');
		
		$sql_prod= "SELECT ITEM,
					   MODELO,
					   P.NOM_PRODUCTO,
					   'UND.' UNIDAD,
					   IR4.CANTIDAD,
					   IR4.PRECIO,
					   IR4.TOTAL,
					   IR4.CU_PESOS,
					   IR4.CU_US,
					   P.FACTOR_VENTA_INTERNO,
					   P.PRECIO_VENTA_INTERNO
				FROM ITEM_REGISTRO_4D  IR4 LEFT OUTER JOIN BIGGI.DBO.PRODUCTO P ON IR4.MODELO = P.COD_PRODUCTO
				WHERE NUMERO_REGISTRO_INGRESO = $nro_registro_ingreso";
		$result_prod = $db->build_results($sql_prod);
		$count = count($result_prod);	
		
		if($count != 0){
		$Y = $pdf->gety();
		//////Tabla Encabezado//////
		$pdf->SetFont('Arial','',8);
		$pdf->SetXY(15, $Y+15);
		$pdf->MultiCell(20, 15, 'Ít', 1, 'L');
		$pdf->SetXY(35, $Y+15);
		$pdf->MultiCell(60, 15, 'CODIGO', 1, 'L');
		$pdf->SetXY(95, $Y+15);
		$pdf->MultiCell(161, 15, 'PRODUCTO', 1, 'L');
		$pdf->SetXY(256, $Y+15);
		$pdf->MultiCell(27, 15, 'CT', 1, 'L');
		$pdf->SetXY(283, $Y+15);
		$pdf->MultiCell(55, 15, 'Precio US$', 1, 'L');		
		$pdf->SetXY(338, $Y+15);
		$pdf->MultiCell(56, 15, 'TOT. US$', 1, 'L');
		$pdf->SetXY(394, $Y+15);
		$pdf->MultiCell(57, 15, 'C.U US$', 1, 'L');
		$pdf->SetXY(451, $Y+15);
		$pdf->MultiCell(57, 15, 'C.U $', 1, 'L');
		$pdf->SetXY(508, $Y+15);
		$pdf->MultiCell(24, 15, 'FV', 1, 'L');
		$pdf->SetXY(532, $Y+15);
		$pdf->MultiCell(53, 15, 'PVE $', 1, 'L');
		}
		
		$e= 1;
		for ($i=0; $i< $count; $i++){
		$Y = $pdf->gety();
		if($i < 11){
			$ITEM 		= $result_prod[$i]['ITEM'];
			$PRODUCTO 	= $result_prod[$i]['NOM_PRODUCTO'];
			$MODELO 	= $result_prod[$i]['MODELO'];
			$CANTIDAD 	= $result_prod[$i]['CANTIDAD'];
			$PRECIO 	= $result_prod[$i]['PRECIO'];
			$TOTAL 		= $result_prod[$i]['TOTAL'];
			$CU_PESOS 	= $result_prod[$i]['CU_PESOS'];
			$CU_US 		= $result_prod[$i]['CU_US'];
			$FACTOR_VENTA_INTERNO 		= $result_prod[$i]['FACTOR_VENTA_INTERNO'];
			$PRECIO_VENTA_INTERNO 		= $result_prod[$i]['PRECIO_VENTA_INTERNO'];
			//////Limita caracteres de productos//////
			while ($pdf->GetStringWidth($PRODUCTO) > 43 AND $pdf->GetStringWidth($MODELO) > 9) {
					$PRODUCTO = substr($PRODUCTO, 0, 43);
					$MODELO = substr($MODELO, 0, 9);
          			break;
            }
            
            $pdf->SetFont('Helvetica','',8);
			$pdf->SetXY(15, $Y);
			$pdf->MultiCell(20, 15,$ITEM, '0', 'C');
			$pdf->SetXY(35, $Y);
			$pdf->MultiCell(60, 15,$MODELO, '0', 'L');
			$pdf->SetXY(95, $Y);
			$pdf->MultiCell(161, 15,substr($PRODUCTO,0,30), '0', 'L');
			$pdf->SetXY(256, $Y);
			$pdf->MultiCell(27, 15, $detalle = number_format($CANTIDAD,1,',',' '), '0', 'R');
			$pdf->SetXY(283, $Y);
			$pdf->MultiCell(55, 15, $detalle = number_format($PRECIO, 2, ',', '.'), '0', 'R');
			$pdf->SetXY(338, $Y);
			$pdf->MultiCell(56, 15, $detalle = number_format($TOTAL,2,',','.'), '0', 'R');
			$pdf->SetXY(394, $Y);
			$pdf->MultiCell(57, 15, $detalle = number_format($CU_US,2,',','.'), '0', 'R');
			$pdf->SetXY(451, $Y);
			$pdf->MultiCell(57, 15, $detalle = number_format($CU_PESOS,0,',','.'), '0', 'R');
			$pdf->SetXY(475, $Y);
			$pdf->MultiCell(57, 15, $detalle = number_format($FACTOR_VENTA_INTERNO,2,',','.'), '0', 'R');
			$pdf->SetXY(527, $Y);
			$pdf->MultiCell(57, 15, $detalle = number_format($PRECIO_VENTA_INTERNO,0,',','.'), '0', 'R');
			$e++;
			}
		}
		if($count != 0){
			if($count > 11){	
				$largo = 166;
			}else{
				$largo = $count * 13.5;	
			}
			$pdf->SetXY(15, 265 );
			$pdf->MultiCell(20, $largo, '' ,1); // it
			$pdf->SetXY(35, 265 );
			$pdf->MultiCell(60, $largo, '' ,1); // modelo
			$pdf->SetXY(95, 265 );
			$pdf->MultiCell(161, $largo, '' ,1); // producto
			$pdf->SetXY(256, 265 );
			$pdf->MultiCell(27, $largo, '' ,1); // ct
			$pdf->SetXY(283, 265 );
			$pdf->MultiCell(55, $largo, '' ,1); // Precio US$
			$pdf->SetXY(338, 265 );
			$pdf->MultiCell(56, $largo, '' ,1); // TOT. US$
			$pdf->SetXY(394, 265 );
			$pdf->MultiCell(57, $largo, '' ,1); // C.U US$
			$pdf->SetXY(451, 265 );
			$pdf->MultiCell(57, $largo, '' ,1); // C.U $
			$pdf->SetXY(508, 265 );
			$pdf->MultiCell(24, $largo, '' ,1); // FV
			$pdf->SetXY(532, 265 );
			$pdf->MultiCell(53, $largo, '' ,1); // PVE $
		}else{
			$Y = 250;
		}
		$pdf->SetFont('Arial','',9);
		$pdf->Text(30, 50,'Fecha   '.$fecha);
		$pdf->Text(30, 62,'Hora    '.$hora);
		$pdf->Text(547, 85,'PAG:  '.$pdf->PageNo());
		
		 $pdf->SetFont('Arial','B',12);
		$pdf->Text(15, 35,'COMERCIAL TODOINOX LTDA');
		$pdf->SetFont('Helvetica','',8);
		$pdf->Text(35, $Y + 30,'TOTAL EXFCA                                US$');
		$pdf->Text(35, $Y + 45,'EMBALAJE                                      US$');
		$pdf->Text(35, $Y + 60,'FLETE INTERNO                            US$');
		$pdf->Text(35, $Y + 75,'OTROS                                            US$');
		$pdf->Text(35, $Y + 90,'TOTAL FOB                                     US$');
		$pdf->Text(35, $Y + 107,'FLETE                                             US$');
		$pdf->Text(35, $Y + 122,'SEGURO                                         US$');
		$pdf->Text(35, $Y + 137,'FLETE FRONTERA SCL                 US$');
		$pdf->Text(35, $Y + 152,'TOTAL CIF                                      US$');
		$pdf->Text(35, $Y + 169,'TOTAL CIF PESOS                                       $');
		$pdf->Text(35, $Y + 185,'AD-VALOREM                                               $');
		$pdf->Text(35, $Y + 202,'AGENTE ADUANA                                        $');
		$pdf->Text(35, $Y + 217,'FLETE CHILE                                                $');
		$pdf->Text(35, $Y + 232,'TOTAL OTROS                                             $');
		$pdf->Text(35, $Y + 248,'TOTAL                                                           $');
		
		$pdf->SetFont('Arial','B',9);
		$pdf->Text(35, $Y + 271,'OBSERVACION',1);
		$pdf->SetFont('Helvetica','',8);
		$pdf->SetXY(35, $Y + 274);
		$pdf->MultiCell(280, 10, $obs  ,1,'L'); 
		
		$pdf->SetXY(180, $Y + 20);
		$pdf->MultiCell(57, 12, number_format($tota_exfca, 2, ',', '.')  ,1,'R'); 
		$pdf->SetXY(180, $Y + 35);
		$pdf->MultiCell(57, 12, number_format($embalaje, 2, ',', '.')  ,1,'R');
		$pdf->SetXY(180, $Y + 51);
		$pdf->MultiCell(57, 12, number_format($flete_interno, 2, ',', '.')  ,1,'R');
		$pdf->SetXY(180, $Y + 67);
		$pdf->MultiCell(57, 12, number_format($otros, 2, ',', '.')  ,1,'R');
		$pdf->SetXY(180, $Y + 82);
		$pdf->MultiCell(57, 12, number_format($total_fob, 2, ',', '.')  ,1,'R');
		$pdf->SetXY(180, $Y + 98);
		$pdf->MultiCell(57, 12, number_format($flete, 2, ',', '.')  ,1,'R');
		$pdf->SetXY(180, $Y + 113);
		$pdf->MultiCell(57, 12, number_format($seguro, 2, ',', '.')  ,1,'R');
		$pdf->SetXY(180, $Y + 129);
		$pdf->MultiCell(57, 12, number_format($flete_scl, 2, ',', '.')  ,1,'R');
		$pdf->SetXY(180, $Y + 145);
		$pdf->MultiCell(57, 12, number_format($total_cif, 2, ',', '.')  ,1,'R');
		$pdf->SetXY(200, $Y + 161);
		$pdf->MultiCell(57, 12, number_format($total_cif_pesos, 0, ',', '.')  ,1,'R');
		$pdf->SetXY(200, $Y + 177);
		$pdf->MultiCell(57, 12, number_format($ad_valorem, 0, ',', '.')  ,1,'R');
		$pdf->SetXY(160, $Y + 177);
		$pdf->MultiCell(30, 12, number_format($ad_valorem_porc, 2, ',', '.')  ,1,'R');
		$pdf->SetXY(200, $Y + 193);
		$pdf->MultiCell(57, 12, number_format($agente_aduana, 0, ',', '.')  ,1,'R');
		$pdf->SetXY(160, $Y + 193);
		$pdf->MultiCell(30, 12, number_format($agente_aduana_porc, 2, ',', '.')  ,1,'R');
		$pdf->SetXY(200, $Y + 208);
		$pdf->MultiCell(57, 12, number_format($flete_chile, 0, ',', '.')  ,1,'R');
		$pdf->SetXY(200, $Y + 224);
		$pdf->MultiCell(57, 12, number_format($total_otros, 0, ',', '.')  ,1,'R');
		$pdf->SetXY(200, $Y + 240);
		$pdf->MultiCell(57, 12, number_format($total_gasto, 0, ',', '.')  ,1,'R');
		
		
		$pdf->SetXY(338, $Y + 20);
		$pdf->MultiCell(247,55, ''  ,1,'R');
		$pdf->Text(348, $Y + 35,'TOTAL GASTO IMP.               $');
		$pdf->Text(348, $Y + 50,'TOTAL GASTO IMP.          US$');
		$pdf->SetFont('Helvetica','B',8);
		$pdf->Text(348, $Y + 65,'Factor importacion');
		$pdf->SetFont('Helvetica','',8);
		$pdf->SetXY(478, $Y + 27);
		$pdf->MultiCell(57, 12, number_format($total_gasto, 0, ',', '.')  ,1,'R');
		$pdf->SetXY(478, $Y + 43);
		$pdf->MultiCell(57, 12, number_format($total_gasto_us, 2, ',', '.')  ,1,'R');
		$pdf->SetFont('Helvetica','B',8);
		$pdf->SetXY(478, $Y + 57);
		$pdf->MultiCell(57, 12, number_format($factor_imp, 2, ',', '.')  ,1,'R');
		
		
		$pdf->SetXY(338, $Y + 90);
		$pdf->MultiCell(247,225, ''  ,1,'R');
		$pdf->SetFont('Helvetica','',8);
		$pdf->Text(348, $Y + 110,'GRÚA');
		$pdf->Text(348, $Y + 130,'PERMISO MUNICIPAL');
		$pdf->Text(348, $Y + 150,'DESCONSOLIDACION');
		$pdf->Text(348, $Y + 180,'GASTOS ORDEN PAGO');
		$pdf->Text(348, $Y + 200,'GASTO L/C');
		$pdf->Text(348, $Y + 230,'ALMACENAJE');
		$pdf->Text(348, $Y + 250,'OTROS');
		$pdf->Text(440, $Y + 270,'______________________');
		$pdf->Text(348, $Y + 290,'TOTAL OTROS');
		
		$pdf->SetXY(478, $Y + 100);
		$pdf->MultiCell(57, 12, number_format($grua, 0, ',', '.') ,1,'R');
		$pdf->SetXY(478, $Y + 120);
		$pdf->MultiCell(57, 12, number_format($permiso_muni, 0, ',', '.')  ,1,'R');
		$pdf->SetXY(478, $Y + 140);
		$pdf->MultiCell(57, 12,number_format($desconsolidacion, 0, ',', '.') ,1,'R');
		$pdf->SetXY(342, $Y + 165);
		$pdf->MultiCell(240,42, ''  ,1,'R');
		$pdf->SetXY(478, $Y + 170);
		$pdf->MultiCell(57, 12,number_format($gato_orden_pago, 0, ',', '.') ,1,'R');
		$pdf->SetXY(478, $Y + 190);
		$pdf->MultiCell(57, 12,number_format($gasto_l_c, 0, ',', '.') ,1,'R');
		$pdf->SetXY(478, $Y + 220);
		$pdf->MultiCell(57, 12,number_format($almacenaje, 0, ',', '.') ,1,'R');
		$pdf->SetXY(478, $Y + 240);
		$pdf->MultiCell(57, 12,number_format($otros, 0, ',', '.') ,1,'R');
		$pdf->SetXY(478, $Y + 280);
		$pdf->MultiCell(57, 12,number_format($total_otros, 0, ',', '.') ,1,'R');
		
		if($count > 11){
				$pdf->AddPage();	
					$Y = 91;
				$fecha=strftime( "%d/%m/%Y", time() );
				$hora=strftime( "%H:%M", time() );
				$pdf->SetFont('Arial','',9);
				$pdf->Text(30, 50,'Fecha   '.$fecha);
				$pdf->Text(30, 62,'Hora    '.$hora);
				$pdf->Text(547, 85,'PAG:  '.$pdf->PageNo());
				$pdf->SetFont('Arial','B',12);
				$pdf->Text(15, 35,'COMERCIAL TODOINOX LTDA');	
				$pdf->SetFont('Arial','',8);
				
				$pdf->SetXY(15, $Y+15);
				$pdf->MultiCell(20, 15, 'Ít', 1, 'L');
				
				$pdf->SetXY(35, $Y+15);
				$pdf->MultiCell(60, 15, 'CODIGO', 1, 'L');
				
				$pdf->SetXY(95, $Y+15);
				$pdf->MultiCell(161, 15, 'PRODUCTO', 1, 'L');
				
				$pdf->SetXY(256, $Y+15);
				$pdf->MultiCell(27, 15, 'CT', 1, 'L');
				
				$pdf->SetXY(283, $Y+15);
				$pdf->MultiCell(55, 15, 'Precio US$', 1, 'L');		
				
				$pdf->SetXY(338, $Y+15);
				$pdf->MultiCell(56, 15, 'TOT. US$', 1, 'L');
				
				$pdf->SetXY(394, $Y+15);
				$pdf->MultiCell(57, 15, 'C.U US$', 1, 'L');
				
				$pdf->SetXY(451, $Y+15);
				$pdf->MultiCell(57, 15, 'C.U $', 1, 'L');
		
				$pdf->SetXY(508, $Y+15);
				$pdf->MultiCell(24, 15, 'FV', 1, 'L');
		
				$pdf->SetXY(532, $Y+15);
				$pdf->MultiCell(53, 15, 'PVE $', 1, 'L');
		
			$e= 0;
			for ($i=11; $i< $count; $i++){
			$Y = $pdf->gety();	
			
				$ITEM 		= $result_prod[$i]['ITEM'];
				$PRODUCTO 	= $result_prod[$i]['NOM_PRODUCTO'];
				$MODELO 	= $result_prod[$i]['MODELO'];
				$CANTIDAD 	= $result_prod[$i]['CANTIDAD'];
				$PRECIO 	= $result_prod[$i]['PRECIO'];
				$TOTAL 		= $result_prod[$i]['TOTAL'];
				$CU_PESOS 	= $result_prod[$i]['CU_PESOS'];
				$CU_US 		= $result_prod[$i]['CU_US'];
				//////Limita caracteres de productos//////
				while ($pdf->GetStringWidth($PRODUCTO) > 43 AND $pdf->GetStringWidth($MODELO) > 9) {
						$PRODUCTO = substr($PRODUCTO, 0, 43);
						$MODELO = substr($MODELO, 0, 9);
	          			break;
	            }
	            
	            $pdf->SetFont('Helvetica','',8);
				$pdf->SetXY(15, $Y);
				$pdf->MultiCell(20, 15,$ITEM, '0', 'C');
				$pdf->SetXY(35, $Y );
				$pdf->MultiCell(60, 15,$MODELO, '0', 'L');
				$pdf->SetXY(95, $Y );
				$pdf->MultiCell(161, 15,substr($PRODUCTO,0,30), '0', 'L');
				$pdf->SetXY(256, $Y );
				$pdf->MultiCell(27, 15, $detalle = number_format($CANTIDAD,1,',',' '), '0', 'R');
				$pdf->SetXY(283, $Y );
				$pdf->MultiCell(55, 15, $detalle = number_format($PRECIO, 2, ',', '.'), '0', 'R');
				$pdf->SetXY(338, $Y );
				$pdf->MultiCell(56, 15, $detalle = number_format($TOTAL,2,',','.'), '0', 'R');
				$pdf->SetXY(394, $Y );
				$pdf->MultiCell(57, 15, $detalle = number_format($CU_US,2,',','.'), '0', 'R');
				$pdf->SetXY(451, $Y );
				$pdf->MultiCell(57, 15, $detalle = number_format($CU_PESOS,0,',','.'), '0', 'R');
				$pdf->SetXY(475, $Y);
				$pdf->MultiCell(57, 15, $detalle = number_format($FACTOR_VENTA_INTERNO,2,',','.'), '0', 'R');
				$pdf->SetXY(527, $Y);
				$pdf->MultiCell(57, 15, $detalle = number_format($PRECIO_VENTA_INTERNO,0,',','.'), '0', 'R');
				$e++;
				
			}
			$largo = $e * 14.8;
			$pdf->SetXY(15, 121 );
			$pdf->MultiCell(20, $largo, '' ,1); // it
			$pdf->SetXY(35, 121 );
			$pdf->MultiCell(60, $largo, '' ,1); // modelo
			$pdf->SetXY(95, 121 );
			$pdf->MultiCell(161, $largo, '' ,1); // producto
			$pdf->SetXY(256, 121 );
			$pdf->MultiCell(27, $largo, '' ,1); // ct
			$pdf->SetXY(283, 121 );
			$pdf->MultiCell(55, $largo, '' ,1); // Precio US$
			$pdf->SetXY(338, 121 );
			$pdf->MultiCell(56, $largo, '' ,1); // TOT. US$
			$pdf->SetXY(394, 121 );
			$pdf->MultiCell(57, $largo, '' ,1); // C.U US$
			$pdf->SetXY(451, 121 );
			$pdf->MultiCell(57, $largo, '' ,1); // C.U $
			$pdf->SetXY(508, 121 );
			$pdf->MultiCell(24, $largo, '' ,1); // FV
			$pdf->SetXY(532, 121 );
			$pdf->MultiCell(53, $largo, '' ,1); // PVE $
		}
	}
}
?>
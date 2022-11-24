<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../guia_despacho/class_wi_guia_despacho.php");

class dw_item_guia_despacho_arriendo extends dw_item_guia_despacho  {
	const K_BODEGA_RENTAL = 1;
	
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
	
		// Se guarda el old para los casos en que una validación necesite volver al valor OLD  
		$this->add_control($control = new edit_text_upper('COD_PRODUCTO_OLD', 30, 30, 'hidden'));
			
		// mandatorys
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('NOM_PRODUCTO', 'Descripción del producto');
	}
	
	function dw_item_guia_despacho_arriendo() {
		
		$sql = "SELECT IGD.COD_ITEM_GUIA_DESPACHO,
						IGD.COD_GUIA_DESPACHO,
						IGD.ORDEN,
						IGD.ITEM,
						IGD.COD_PRODUCTO,
						IGD.COD_PRODUCTO COD_PRODUCTO_OLD,
						IGD.NOM_PRODUCTO,
						IGD.CANTIDAD,
						dbo.f_arr_cant_por_despachar(IGD.COD_ITEM_DOC, default) CANTIDAD_POR_DESPACHAR,
						dbo.f_bodega_stock_cero(IGD.COD_PRODUCTO, ".self::K_BODEGA_RENTAL.", getdate()) CANTIDAD_BODEGA,
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
						'none' BOTON_PRECIO, 
						MOTIVO_TE
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
		$this->add_control(new edit_text('CANTIDAD_BODEGA',10, 10, 'hidden'));
		$this->add_control(new computed('PRECIO', 0));
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		$this->set_first_focus('CANTIDAD');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
		//$this->dws['dw_item_guia_despacho']->set_item($i, 'CANTIDAD', '0');
		
	}
	
}
class wi_guia_despacho_arriendo extends wi_guia_despacho {
	function wi_guia_despacho_arriendo($cod_item_menu) {
		parent::w_input('guia_despacho_arriendo', $cod_item_menu);
		$this->add_FK_delete_cascada('ITEM_GUIA_DESPACHO');	
		$this->hide_menu_when_from = false;		// Cuando se crear desde no se debe esconder menu
		
		// tab guia despacho
		// DATAWINDOWS GUIA DESPACHO
		$this->dws['dw_guia_despacho'] = new dw_guia_despacho();
		$this->dws['dw_guia_despacho']->set_entrable('COD_INDICADOR_TIPO_TRASLADO',false);		
		// tab items
		// DATAWINDOWS ITEMS GUIA DESPACHO
		$this->dws['dw_item_guia_despacho'] = new dw_item_guia_despacho_arriendo();
		//$this->dws['dw_item_guia_despacho']->set_item('CANTIDAD', '0');
		$this->set_first_focus('NRO_ORDEN_COMPRA');

		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_ESTADO_DOC_SII');
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('COD_SUCURSAL_DESPACHO');
		$this->add_auditoria('COD_PERSONA');		
	}
	
	function make_sql_auditoria() {
		// cambia de guia_despacho_arriendo a guia_despacho y luego lo devuelve
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'guia_despacho';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function delete_record($db) {
		return $db->EXECUTE_SP("spu_guia_despacho", "'DELETE', ".$this->get_key());
	}
   	function envia_GD_Electronica(){
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
									,GD.COD_TIPO_GUIA_DESPACHO
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
			$COD_TIPO_GUIA_DESPACHO	= $result_dte[0]['COD_TIPO_GUIA_DESPACHO'] ;
			$NRO_GUIA_DESPACHO	= $result_dte[0]['NRO_GUIA_DESPACHO'] ;	// 1 Numero Guia Despacho
			$FECHA_GUIA_DES		= $result_dte[0]['FECHA_GUIA_DES'] ;	// 2 Fecha Guia Despacho
			//Email - VE: =>En el caso de las Guia Despacho y otros documentos, no aplica por lo que se dejan 0;0 
			$TD					= $this->llena_cero;					// 3 Tipo Despacho

			if($result_dte[0]['COD_INDICADOR_TIPO_TRASLADO'] == NULL)
				$TT				= $this->llena_cero;								// 4 Tipo Traslado
			else
				$TT				= $result_dte[0]['COD_INDICADOR_TIPO_TRASLADO'];	// 4 Tipo Traslado
			
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
			$DP					= $result_dte[0]['DIRECCION'];			//15 Dirección Postal
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
			
			//SE SOLICITA QUE EL ARCHIVO DE GD ELECTRONICA SE INDIQUE COMO DOCUMENTO ASOCIADO EL COD_ARRIENDO /RE 13/11/2013
			if ($COD_TIPO_GUIA_DESPACHO = 5){	// en cod_doc esta el cod_mod_arriendo
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	   			$sql= "SELECT COD_ARRIENDO 
						FROM MOD_ARRIENDO
						WHERE COD_MOD_ARRIENDO =".$result_dte[0]['COD_DOC'];
	   			$result = $db->build_results($sql);
				
	   			$result_dte[0]['COD_DOC'] = $result[0]['cod_arriendo'];
			}
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
			$DP				= substr($DP.$space, 0, 60);				//15 Dirección Postal
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
			fwrite($handle, $NRO_GUIA_DESPACHO.$this->separador);			// 1 Numero Guia Despacho
			fwrite($handle, $FECHA_GUIA_DES.$this->separador);		// 2 Fecha Guia Despacho
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
			fwrite($handle, $REFERENCIA.$this->separador);			//Referencia de la Guia Despacho
			fwrite($handle, $NRO_GD.$this->separador);				//Solicitado a VE por SP
			fwrite($handle, $GENERA_SALIDA.$this->separador);		//DESPACHADO Solicitado a VE por SP
			fwrite($handle, $CANCELADA.$this->separador);			//CANCELADO Solicitado a VE por SP
			fwrite($handle, $SUBTOTAL.$this->separador);			//Solicitado a VE por SP "SUBTOTAL"
			fwrite($handle, $PORC_DSCTO1.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO1"
			fwrite($handle, $PORC_DSCTO2.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO2"
			fwrite($handle, $EMISOR_GD.$this->separador);			//Solicitado a VE por SP "EMISOR_GUIA_DESPACHO"
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
			$TOTAL_EN_PALABRAS = strtr($TOTAL_EN_PALABRAS, "áéíóú", "aeiou");
			$TOTAL_EN_PALABRAS = strtoupper($TOTAL_EN_PALABRAS);
			$TOTAL_EN_PALABRAS = substr($TOTAL_EN_PALABRAS.' PESOS.'.$space.$space, 0, 200);	//Total en palabras: de la sumatoria de precios unitarios				
			
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
			fwrite($handle, $OBSERVACIONES.$this->separador);		//si la Guia Despacho tiene notas u observaciones
			fwrite($handle, $TOTAL_EN_PALABRAS.$this->separador);	//Total en palabras: Posterior al campo Notas
			fwrite($handle, "\r\n"); //salto de linea

			//datos de dw_item_guia_despacho linea 5 a 34
			for ($i = 0; $i < 30; $i++){
				if($i < $count){
					fwrite($handle, ' '); //0 space 2
					$ORDEN		= $result_dte[$i]['ORDEN'];	
					$MODELO		= $result_dte[$i]['COD_PRODUCTO'];
					$NOM_PRODUCTO = $result_dte[$i]['NOM_PRODUCTO'];
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
			$NRO_GUIA_DESPACHO = trim($NRO_GUIA_DESPACHO);
			if (!$upload) {
				$this->_load_record();
				$this->alert('No se pudo enviar Guia Despacho Electronica Nº '.$NRO_GUIA_DESPACHO.', Por favor contacte a IntegraSystem.');								
			}else{
				$this->_load_record();
				$this->alert('Gestión Realizada con exíto. Guia Despacho Electronica Nº '.$NRO_GUIA_DESPACHO.'.');								
			}
			unlink($fname);
		}else{
			$db->ROLLBACK_TRANSACTION();
			return false;
		}			
		$this->unlock_record();
   	}
}
?>
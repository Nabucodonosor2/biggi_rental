<?php
////////////////////////////////////////
/////////// TODOINOX ///////////////
////////////////////////////////////////
class wi_nota_credito extends wi_nota_credito_base {
	function wi_nota_credito($cod_item_menu) {
		parent::wi_nota_credito_base($cod_item_menu); 
	//	$this->add_control(new edit_text_upper('COD_NOTA_CREDITO',10, 10, 'hidden'));
		$sql	= "select 	 COD_BODEGA
							,NOM_BODEGA
					from 	 BODEGA
					order by COD_BODEGA";
		$this->dws['dw_nota_credito']->add_control(new drop_down_dw('COD_BODEGA',$sql,150));
		$this->dws['dw_nota_credito']->add_control(new edit_check_box('GENERA_ENTRADA','S','N','GENERA ENTRADA'));
		//$this->dws['dw_nota_credito']->set_item(0, 'GENERA_ENTRADA', 'S');
		$sql	= "SELECT COD_CENTRO_COSTO,
	   					  NOM_CENTRO_COSTO 
					 FROM CENTRO_COSTO
					ORDER BY COD_CENTRO_COSTO";
		$this->dws['dw_nota_credito']->add_control(new drop_down_dw('COD_CENTRO_COSTO',$sql,150));
		
	}
	function new_record() {
		parent::new_record();
		
		$this->dws['dw_nota_credito']->set_item(0, 'GENERA_ENTRADA', 'S');
		$this->dws['dw_nota_credito']->set_item(0, 'COD_BODEGA', 1);	// bodega todoinox
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
		$PORC_DSCTO2				= 0;//$this->dws['dw_nota_credito']->get_item(0, 'PORC_DSCTO2');
		$INGRESO_USUARIO_DSCTO1		= $this->dws['dw_nota_credito']->get_item(0, 'INGRESO_USUARIO_DSCTO1');
		$MONTO_DSCTO1				= $this->dws['dw_nota_credito']->get_item(0, 'MONTO_DSCTO1');
		$INGRESO_USUARIO_DSCTO2		= $this->dws['dw_nota_credito']->get_item(0, 'INGRESO_USUARIO_DSCTO2');
		$MONTO_DSCTO2				= 0;//$this->dws['dw_nota_credito']->get_item(0, 'MONTO_DSCTO2');
		$PORC_IVA					= $this->dws['dw_nota_credito']->get_item(0, 'PORC_IVA');
		$MONTO_IVA					= $this->dws['dw_nota_credito']->get_item(0, 'MONTO_IVA');
		$TOTAL_CON_IVA				= $this->dws['dw_nota_credito']->get_item(0, 'TOTAL_CON_IVA');
		$MOTIVO_ANULA				= $this->dws['dw_nota_credito']->get_item(0, 'MOTIVO_ANULA');
		$COD_USUARIO_ANULA			= $this->dws['dw_nota_credito']->get_item(0, 'COD_USUARIO_ANULA');
		$COD_MOTIVO_NOTA_CREDITO	= $this->dws['dw_nota_credito']->get_item(0, 'COD_MOTIVO_NOTA_CREDITO');
		$GENERA_ENTRADA				= $this->dws['dw_nota_credito']->get_item(0, 'GENERA_ENTRADA');
		$COD_CENTRO_COSTO			= $this->dws['dw_nota_credito']->get_item(0, 'COD_CENTRO_COSTO');	
		
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
		$COD_CENTRO_COSTO			= ($COD_CENTRO_COSTO =='') ? "null" : $COD_CENTRO_COSTO;
		
	
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
				,'$COD_CENTRO_COSTO'";

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
   	function envia_NC_Electronica(){
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
					$PORC_IVA = '';
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
				$db->ROLLBACK_TRANSACTION();
				$this->_load_record();
				$this->alert('No se pudo enviar Nota Credito Electronica Nº '.$NRO_NOTA_CREDITO.', Por favor contacte a IntegraSystem.');								
			}else{
				$this->_load_record();
				$this->alert('Gestión Realizada con exíto. Nota Credito Electronica Nº '.$NRO_NOTA_CREDITO.'.');
				$db->COMMIT_TRANSACTION();								
			}
			unlink($fname);
		}else{
			$db->ROLLBACK_TRANSACTION();
			return false;
		}
		$this->unlock_record();
   	}
}
class print_nota_credito extends print_nota_credito_base {	
		function print_nota_credito($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
			parent::print_nota_credito_base($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
		}			
}

?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");	

	//usuario a quien se le enviar� la bitacora
	$K_COD_USUARIO = 4;

	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "SELECT	C.COD_COTIZACION
          			,CONVERT(VARCHAR,C.FECHA_COTIZACION, 103) FECHA
          			,E.NOM_EMPRESA
          			,C.TOTAL_CON_IVA
          			,BC.GLOSA_COMPROMISO
          			,BC.CONTACTO
          			,' / F: '+BC.TELEFONO FONO
          			,U1.NOM_USUARIO VENDEDOR
					,BC.FECHA_COMPROMISO
			FROM	BITACORA_COTIZACION BC LEFT OUTER JOIN USUARIO U2 ON U2.COD_USUARIO = BC.COD_USUARIO_REALIZADO, USUARIO U1, COTIZACION C, EMPRESA E
			WHERE	BC.COD_COTIZACION = C.COD_COTIZACION
			AND 	E.COD_EMPRESA = C.COD_EMPRESA
			AND 	C.COD_USUARIO_VENDEDOR1 = U1.COD_USUARIO
			AND 	BC.FECHA_COMPROMISO <= DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))
			AND 	BC.FECHA_COMPROMISO >= DATEADD(SECOND,0 , DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))
			AND 	BC.TIENE_COMPROMISO = 'S'
			AND 	(BC.COMPROMISO_REALIZADO = 'N' OR BC.COMPROMISO_REALIZADO IS NULL)
			ORDER BY BC.FECHA_COMPROMISO DESC";
	
	$sql_bitacora = $db->build_results($sql);
	$count = count($sql_bitacora);
	
	//DEPENDIENDO SI ENCONTRO REGISTROS DESPLIEGA HTM
	if($count <> 0)
		$temp = new Template_appl('mail_bitacora_cotizacion.htm');
	else	
		$temp = new Template_appl('mail_bitacora_cotizacion_b.htm');

	//BUSCA AL USUARIO SERGIO PECHOANTE
	$sql_usuario = "SELECT	NOM_USUARIO
							,MAIL
							,CONVERT(VARCHAR,GETDATE(),103) FECHA
					FROM	USUARIO WHERE COD_USUARIO = $K_COD_USUARIO";
	$sql_usuario	= $db->build_results($sql_usuario);
	
	$nom_usuario = $sql_usuario[0]['NOM_USUARIO'];
	$mail_usuario = $sql_usuario[0]['MAIL'];
	$fecha = $sql_usuario[0]['FECHA'];
	
	$temp->setVar("NOM_USUARIO", $nom_usuario);	
	$temp->setVar("MAIL", $mail_usuario);
	$temp->setVar("FECHA", $fecha);
	$temp->setVar("COUNT", $count);
	
	$dw_bitacora_Cotizacion = new datawindow($sql, "BITACORA_COTIZACION");
	$dw_bitacora_Cotizacion->add_control(new static_num('TOTAL_CON_IVA'));
	$dw_bitacora_Cotizacion->retrieve();

	$dw_bitacora_Cotizacion->habilitar($temp, false);
	
	$html = $temp->toString();

	$para = $mail_usuario;
	
    //Envio de mail
	$asunto = ' Sr(a). '.$nom_usuario.' Compromisos para hoy '.$fecha;
	
//////////////////////////////	envio mail SP
	$sql = "select dbo.f_get_parametro(53)         URL_SMTP
	                        ,dbo.f_get_parametro(54)      USER_SMTP
	                        ,dbo.f_get_parametro(55)      PASS_SMTP
	                        ,dbo.f_get_parametro(71)      PORT_SMTP";
	
	$result = $db->build_results($sql);
	$URL_SMTP   = $result[0]['URL_SMTP'];
	$USER_SMTP  = $result[0]['USER_SMTP'];
	$PASS_SMTP  = $result[0]['PASS_SMTP'];
	$PORT_SMTP  = $result[0]['PORT_SMTP'];

	$mail = new phpmailer();
	$mail->PluginDir = dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/";
	$mail->Mailer 	= "smtp";
	$mail->SMTPAuth = true;
	
	$mail->Host = $URL_SMTP;
	$mail->Username = $USER_SMTP;
	$mail->Password = $PASS_SMTP;
	$mail->Port = $PORT_SMTP;
	$mail->SMTPSecure= 'ssl';

	$mail->From 	="soporte@biggi.cl";		
	$mail->FromName = "Comercial Biggi S.A.";
	$mail->Timeout=30;
	$mail->Subject = $asunto;

	$mail->ClearAddresses();
	$mail->AddAddress('sergio.pechoante@biggi.cl', 'SERGIO PECHOANTE');
	//$mail->AddCC('rescudero@biggi.cl', 'RAFAEL ESCUDERO');
	$mail->AddBCC('mherrera@biggi.cl', 'MARCELO HERRERA');
	//$mail->AddBCC('ecastillo@biggi.cl', 'EDUARDO CASTILLO');
	
		
	$mail->Body = $html;
	$mail->AltBody = "";
	$mail->ContentType="text/html";
	$exito = $mail->Send();
	

	// Enviar mails a los vendedores, distinto a SERGIO PECHOANTE
	
	$sql_tiene_bitacora="SELECT COD_USUARIO 
							FROM USUARIO 
							WHERE AUTORIZA_INGRESO = 'S'
							AND COD_USUARIO in (select CO.COD_USUARIO from BITACORA_COTIZACION BI, COTIZACION CO
													where CO.COD_COTIZACION = BI.COD_COTIZACION	
													and BI.FECHA_COMPROMISO <= DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))
							                        AND BI.FECHA_COMPROMISO >= DATEADD(SECOND,0 , DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE()))))"; //SPECHOANTE, ASCIANCA
					
	
	$result = $db->build_results($sql_tiene_bitacora);
	for ($i=0; $i<count($result); $i++) {

		$cod_usuario = $result[$i]['COD_USUARIO'];

		$sql = "SELECT	C.COD_COTIZACION
          			,CONVERT(VARCHAR,C.FECHA_COTIZACION, 103) FECHA
          			,E.NOM_EMPRESA
          			,C.TOTAL_CON_IVA
          			,BC.GLOSA_COMPROMISO
          			,BC.CONTACTO
          			,' / F: '+BC.TELEFONO FONO
          			,U1.NOM_USUARIO VENDEDOR
					,BC.FECHA_COMPROMISO
			FROM	BITACORA_COTIZACION BC LEFT OUTER JOIN USUARIO U2 ON U2.COD_USUARIO = BC.COD_USUARIO_REALIZADO, USUARIO U1, COTIZACION C, EMPRESA E
			WHERE	BC.COD_COTIZACION = C.COD_COTIZACION
			AND 	E.COD_EMPRESA = C.COD_EMPRESA
			AND 	C.COD_USUARIO_VENDEDOR1 = U1.COD_USUARIO
			AND 	BC.FECHA_COMPROMISO <= DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))
			AND 	BC.FECHA_COMPROMISO >= DATEADD(SECOND,0 , DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))
			AND 	BC.TIENE_COMPROMISO = 'S'
			AND 	(BC.COMPROMISO_REALIZADO = 'N' OR BC.COMPROMISO_REALIZADO IS NULL)
			AND     BC.COD_USUARIO = $cod_usuario
			ORDER BY BC.FECHA_COMPROMISO DESC";
	
		$sql_bitacora_vendedor = $db->build_results($sql);
		$count = count($sql_bitacora_vendedor);
	
		$temp = new Template_appl('mail_cotizacion_vendedor.htm');
	
		
	    $sql_usuario = "SELECT	NOM_USUARIO
								,MAIL
								,CONVERT(VARCHAR,GETDATE(),103) FECHA
						FROM	USUARIO WHERE COD_USUARIO = $cod_usuario";
		$sql_usuario	= $db->build_results($sql_usuario);
		
		$nom_usuario = $sql_usuario[0]['NOM_USUARIO'];
		$mail_usuario = $sql_usuario[0]['MAIL'];
		$fecha = $sql_usuario[0]['FECHA'];
		
		$temp->setVar("NOM_USUARIO", $nom_usuario);	
		$temp->setVar("MAIL", $mail_usuario);
		$temp->setVar("FECHA", $fecha);
		$temp->setVar("COUNT", $count);
		
		$dw_bitacora_cotizacion = new datawindow($sql, "BITACORA_COTIZACION");
		$dw_bitacora_cotizacion->add_control(new static_num('TOTAL_CON_IVA'));
		$dw_bitacora_cotizacion->retrieve();
	
		$dw_bitacora_cotizacion->habilitar($temp, false);
		
		$html = $temp->toString();
	
		$para = $mail_usuario;
		
	    //Envio de mail
		$asunto = ' Sr(a). '.$nom_usuario.' Compromisos para hoy '.$fecha;
			////////////////////////////////////////// envio mail vendedores
		$sql = "select dbo.f_get_parametro(53)         URL_SMTP
		                        ,dbo.f_get_parametro(54)      USER_SMTP
		                        ,dbo.f_get_parametro(55)      PASS_SMTP
		                        ,dbo.f_get_parametro(71)      PORT_SMTP";
		
		$result = $db->build_results($sql);
		$URL_SMTP   = $result[0]['URL_SMTP'];
		$USER_SMTP  = $result[0]['USER_SMTP'];
		$PASS_SMTP  = $result[0]['PASS_SMTP'];
		$PORT_SMTP  = $result[0]['PORT_SMTP'];

		$mail = new phpmailer();
		$mail->PluginDir 	= dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/";
		$mail->Mailer 	= "smtp";
		$mail->SMTPAuth 	= true;
		
		$mail->Host = $URL_SMTP;
		$mail->Username = $USER_SMTP;
		$mail->Password = $PASS_SMTP;
		$mail->Port = $PORT_SMTP;
		$mail->SMTPSecure= 'ssl';

		$mail->From 		= "soporte@biggi.cl";		
		$mail->FromName 	= "Comercial Biggi S.A.";
		$mail->Timeout	= 30;
		$mail->Subject 	= $asunto;

		$mail->ClearAddresses();
		
		// copias para HE de los usuarios RB y PV		
		if ($cod_usuario == 11 || $cod_usuario == 17){
    		$mail->AddCC('hescudero@biggi.cl', 'Hector Escudero');
		}
				
		$mail->AddAddress($para, $nom_usuario);
		
		$mail->Body 		= $html;
		$mail->AltBody 	= "";
		$mail->ContentType	= "text/html";
		$exito 		= $mail->Send();
	
	}
	
	$sql_no_tiene_bitacora="SELECT COD_USUARIO 
							FROM USUARIO 
							WHERE AUTORIZA_INGRESO = 'S'
							AND COD_USUARIO in (select COD_USUARIO_VENDEDOR1 from COTIZACION
								                where FECHA_REGISTRO_COTIZACION < DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))
								                AND  FECHA_REGISTRO_COTIZACION  > DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE())- 2,YEAR(GETDATE()))))
						    AND COD_USUARIO not in (select CO.COD_USUARIO_VENDEDOR1 from BITACORA_COTIZACION BI, COTIZACION CO
													where CO.COD_COTIZACION = BI.COD_COTIZACION	
													and BI.FECHA_COMPROMISO <= DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))
							                        AND BI.FECHA_COMPROMISO >= DATEADD(SECOND,0 , DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE()))))"; //SPECHOANTE, ASCIANCA
					
	$result_sin_bitacora = $db->build_results($sql_no_tiene_bitacora);
	for ($e=0; $e<count($result_sin_bitacora); $e++) {

		$cod_usuario = $result_sin_bitacora[$e]['COD_USUARIO'];
        
	    $temp = new Template_appl('mail_bitacora_cotizacion_b.htm');
		
	    $sql_usuario_sin_bitacora = "SELECT	NOM_USUARIO
									,MAIL
									,CONVERT(VARCHAR,GETDATE(),103) FECHA
							FROM	USUARIO WHERE COD_USUARIO = $cod_usuario";
		$sql_usuario_sin_bi	= $db->build_results($sql_usuario_sin_bitacora);
		
		$nom_usuario = $sql_usuario_sin_bi[0]['NOM_USUARIO'];
		$mail_usuario = $sql_usuario_sin_bi[0]['MAIL'];
		$fecha = $sql_usuario_sin_bi[0]['FECHA'];
		
		$temp->setVar("NOM_USUARIO", $nom_usuario);	
		$temp->setVar("MAIL", $mail_usuario);
		$temp->setVar("FECHA", $fecha);
		//$temp->setVar("COUNT", $count);
		//$dw_bitacora_cotizacion = new datawindow($sql_usuario_sin_bitacora, "BITACORA_COTIZACION");
		//$dw_bitacora_cotizacion->add_control(new static_num('TOTAL_CON_IVA'));
		$html = $temp->toString();
	
		$para = $mail_usuario;
		//Envio de mail
		$asunto = ' Sr(a). '.$nom_usuario.' Compromisos para hoy '.$fecha;
			////////////////////////////////////////// envio mail vendedores
		$sql = "select dbo.f_get_parametro(53)         URL_SMTP
		                        ,dbo.f_get_parametro(54)      USER_SMTP
		                        ,dbo.f_get_parametro(55)      PASS_SMTP
		                        ,dbo.f_get_parametro(71)      PORT_SMTP";
		
		$result = $db->build_results($sql);
		$URL_SMTP   = $result[0]['URL_SMTP'];
		$USER_SMTP  = $result[0]['USER_SMTP'];
		$PASS_SMTP  = $result[0]['PASS_SMTP'];
		$PORT_SMTP  = $result[0]['PORT_SMTP'];
		
		$mail = new phpmailer();
		$mail->PluginDir = dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/";
		$mail->Mailer 	= "smtp";
		$mail->SMTPAuth = true;
		
		$mail->Host = $URL_SMTP;
		$mail->Username = $USER_SMTP;
		$mail->Password = $PASS_SMTP;
		$mail->Port = $PORT_SMTP;
		$mail->SMTPSecure= 'ssl';

		$mail->From 	="soporte@biggi.cl";		
		$mail->FromName = "Comercial Biggi S.A.";
		$mail->Timeout=30;
		$mail->Subject = $asunto;
        
		$mail->ClearAddresses();
		
		// copias para HE de los usuarios RB y PV		
		if ($cod_usuario == 11 || $cod_usuario == 17){
    		$mail->AddCC('hescudero@biggi.cl', 'Hector Escudero');
		}
				
		$mail->AddAddress($para, $nom_usuario);
		
		
		$mail->Body = $html;
		$mail->AltBody = "";
		$mail->ContentType="text/html";
		$exito = $mail->Send();
	    
	}

	header('Location:mail_cotizacion.htm');
?>
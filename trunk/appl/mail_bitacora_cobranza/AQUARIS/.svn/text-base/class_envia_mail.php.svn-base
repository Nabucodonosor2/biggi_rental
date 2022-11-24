<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
include("funciones.php");
class class_envio_mail {
 	 static function envio_mail(){
		$K_COD_USUARIO = 3; 
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "SELECT	F.NRO_FACTURA
		          			,CONVERT(VARCHAR,F.FECHA_FACTURA, 103) FECHA
		          			,E.NOM_EMPRESA
		          			,F.TOTAL_CON_IVA
		          			,BF.GLOSA_COMPROMISO
		          			,BF.CONTACTO
		          			,' / F: '+BF.TELEFONO FONO
		          			,U1.NOM_USUARIO VENDEDOR
							,BF.FECHA_COMPROMISO
					FROM	BITACORA_FACTURA BF LEFT OUTER JOIN USUARIO U2 ON U2.COD_USUARIO = BF.COD_USUARIO_REALIZADO, USUARIO U1, FACTURA F, EMPRESA E
					WHERE	BF.COD_FACTURA = F.COD_FACTURA
					AND 	E.COD_EMPRESA = F.COD_EMPRESA
					AND 	BF.COD_USUARIO = U1.COD_USUARIO
					AND 	BF.FECHA_COMPROMISO <= DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))
					AND 	BF.TIENE_COMPROMISO = 'S'
					AND 	(BF.COMPROMISO_REALIZADO = 'N' OR BF.COMPROMISO_REALIZADO IS NULL)
					ORDER BY BF.FECHA_COMPROMISO DESC";
			$sql_bitacora = $db->build_results($sql);
			$count = count($sql_bitacora);
			
			$sql_usuario = "SELECT	NOM_USUARIO
							,MAIL
							,CONVERT(VARCHAR,GETDATE(),103) FECHA
					FROM	USUARIO WHERE COD_USUARIO = $K_COD_USUARIO";
			$sql_usuario	= $db->build_results($sql_usuario);
			
			$nom_from = $sql_usuario[0]['NOM_USUARIO'];
			$mail_from = $sql_usuario[0]['MAIL'];
			$fecha = $sql_usuario[0]['FECHA'];
			
			//$para = $mail_usuario;//.', rescudero@biggi.cl, cscianca@biggi.cl, kverdugo@biggi.cl';
			
			if($count <> 0){
			$body="<html>
						<head>
						<title>Documento sin t&iacute;tulo</title>
						<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
						<style type='text/css'>
						<!--
						.Estilo13 {color: #663300}
						.Estilo15 {color: #999999}
						.Estilo22 {color: #003366}
						-->
						</style>
						</head>
						<body>
							<br>
							<br>
							Sr(a). ".$nom_from.", hay ".$count." Facturas que registran compromisos para hoy ".$fecha." o atrasadas.
							<br>
							<br>
							<table width='80%' rules='none' border='1' align='center' class='claro'>
								<tr>
									<td>
										<table border='1'>
											<tbody>
												<tr class='encabezado_right'>
													<td width='8%'>
														<div align='center'><strong>N° FA</strong></div>
													</td>
													<td width='5%'>
														<div align='center'><strong>Fecha</strong></div>
													</td>
													<td width='20%'>
														<div align='center'><strong>Cliente</strong></div>
													</td>
													<td width='5%'>
														<div align='center'><strong>Monto</strong></div>
													</td>
													<td width='30%'>
														<div align='center'><strong>Compromiso</strong></div>
													</td>
													<td width='10%'>
														<div align='center'><strong>Contacto y Teléfono</strong></div>
													</td>
													<td width='15%'>
														<div align='center'><strong>Vendedor</strong></div>
													</td>
													<td width='15%'>
														<div align='center'><strong>Fecha Compromiso</strong></div>
													</td>
												</tr>";
										for($i=0; $i<count($sql_bitacora); $i++){
											$body.="<tr class='claro' >
													<td>
														<div align='right'>".$sql_bitacora[$i]['NRO_FACTURA']."</div>
													</td>
													<td>
														<div align='center'>".$sql_bitacora[$i]['FECHA']."</div>
													</td>
													<td>
														<div align='left'>".$sql_bitacora[$i]['NOM_EMPRESA']."</div>
													</td>
													<td>
														<div align='right'>".$sql_bitacora[$i]['TOTAL_CON_IVA']."</div>
													</td>
													<td>
														<div align='left'>".$sql_bitacora[$i]['GLOSA_COMPROMISO']."</div>
													</td>
													<td>
														<div align='left'>".$sql_bitacora[$i]['CONTACTO']." ".$sql_bitacora[$i]['FONO']."</div>
													</td>
													<td>
														<div align='left'>".$sql_bitacora[$i]['VENDEDOR']."</div>
													</td>
													<td>
														<div align='left'>".$sql_bitacora[$i]['FECHA_COMPROMISO']."</div>
													</td>
												</tr>";	
										}		
										 	$body.="</tbody>
										</table>
									</td>	
								</tr>
							</table>
							<p />
							<table width='100%'>
								<tr>
									<td>
										<p>Atte.</p>
										<p>Comercial Biggi</p>
										<p>Este mensaje ha sido enviado por nuestro servidor de base de datos.</p>
										<p><br>
									</td>
								</tr>
							</table>
							<p /><p />
						</body>
					</html>";
					$altbody="";
			}else{
				
			}
		    //Envio de mail
		    
			$sql_correo ="SELECT VALOR
						   FROM PARAMETRO 
						  WHERE COD_PARAMETRO = 53
							 OR COD_PARAMETRO = 54
							 OR COD_PARAMETRO = 55";
			$result_correo = $db->build_results($sql_correo);					
			$host = $result_correo[0]['VALOR'];
			$username = $result_correo[1]['VALOR'];
			$password = $result_correo[2]['VALOR'];
			
			$mail = new phpmailer();
			$mail->PluginDir = dirname(__FILE__)."/../../../../../commonlib/trunk/php/clases/";
			$mail->Mailer = "smtp";
			$mail->SMTPAuth = true;
			$mail->Host = "$host";
			$mail->Username = "$username";
			$mail->Password = "$password"; 
			$mail->From = $mail_from;
			$mail->FromName = $nom_from;
			$mail->Timeout=30;
			
			$mail->AddAddress($mail_from);
			//$mail->Body = $html;
			$mail->Body = $body;
			$mail->AltBody = $altbody;
			
			$mail->Send();
			
			
 	 }
}	
?>
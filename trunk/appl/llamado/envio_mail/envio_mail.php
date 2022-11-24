<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
include("funciones.php");

ini_set("display_errors", "off");

$cod_llamado = $_REQUEST["COD_LLAMADO_H"];
$cod_destinatario = $_REQUEST["COD_DESTINATARIO_H"]; //quien envia el mail
$cod_destinatario_resp = $cod_destinatario; //quien envia el mail
$cod_destinatario_envio = $_REQUEST["COD_DESTINATARIO_ENVIO_H"]; //a quien se envia el mail
$mensaje = $_REQUEST["MENSAJE"];
$realizado = $_REQUEST["REALIZADO_RESP"];
if($realizado == 'S' ){
	$realizado = 'S';
}else{
	$realizado = 'N'; 
} 
$ms_realizado = '';
$m_realizado = '';
if($realizado =='S'){
  $ms_realizado = '(COMPROMISO REALIZADO)';
  $m_realizado = 'COMPROMISO REALIZADO'	;	
}
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select dbo.f_get_parametro(53)         URL_SMTP
                        ,dbo.f_get_parametro(54)      USER_SMTP
                        ,dbo.f_get_parametro(55)      PASS_SMTP
                        ,dbo.f_get_parametro(71)      PORT_SMTP";

$result = $db->build_results($sql);
$URL_SMTP   = $result[0]['URL_SMTP'];
$USER_SMTP  = $result[0]['USER_SMTP'];
$PASS_SMTP  = $result[0]['PASS_SMTP'];
$PORT_SMTP  = $result[0]['PORT_SMTP'];


$tipo_doc_realizado = $_REQUEST["TIPO_DOC_REALIZADO"];
$cod_doc_realizado = $_REQUEST["COD_DOC_REALIZADO"];

$cod_destinatario_envio = substr ($cod_destinatario_envio, 0, strlen($cod_destinatario_envio) - 1);

$cod_llamado_enc = encriptar_url($cod_llamado, 'envio_mail_llamado');
//$link = "http://190.196.2.10/sysbiggi/comercial_biggi/biggi/trunk/appl/llamado/envio_mail/formulario.php?";
$link = "http://192.168.2.13/desarrolladores/jmino/biggi/trunk/appl/llamado/envio_mail/formulario.php?";
//$link = "http://201.238.210.133/sysbiggi/envio_mail/biggi/trunk/appl/llamado/envio_mail/formulario.php?";

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql_accion ="SELECT NOM_CONTACTO, NOM_PERSONA, CARGO, NOM_LLAMADO_ACCION, LLAMAR_TELEFONO
				,C.RUT,C.DIG_VERIF,C.DIRECCION,CP.MAIL,E.GIRO		 
			FROM CONTACTO C LEFT OUTER JOIN EMPRESA E ON C.COD_EMPRESA = E.COD_EMPRESA,
				LLAMADO LL, LLAMADO_ACCION LLA, CONTACTO_PERSONA CP 
			WHERE LL.COD_LLAMADO = $cod_llamado
				AND LL.COD_LLAMADO_ACCION = LLA.COD_LLAMADO_ACCION
				AND C.COD_CONTACTO = LL.COD_CONTACTO
				AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA";
$result_accion = $db->build_results($sql_accion);					
$nom_contacto = $result_accion[0]['NOM_CONTACTO'];
$nom_persona = $result_accion[0]['NOM_PERSONA'];
$cargo = $result_accion[0]['CARGO'];
$nom_llamado_accion = $result_accion[0]['NOM_LLAMADO_ACCION'];
$llamar_telefono = $result_accion[0]['LLAMAR_TELEFONO'];
//nuevos datos 
$rut_emp = $result_accion[0]['RUT'];
$direccion = $result_accion[0]['DIRECCION'];
$mail_contac = $result_accion[0]['MAIL'];
$giro = $result_accion[0]['GIRO'];
$dig_verf = $result_accion[0]['DIG_VERIF'];
// si no traen datos
if($cargo == '')
$cargo = '<i>No registrado</i>';
if($rut_emp == '')
$rut_emp = '<i>No registrado</i>';
if($direccion == '')
$direccion = '<i>No registrado</i>';
if($mail_contac == '')
$mail_contac = '<i>No registrado</i>';
if($giro == '')
$giro = '<i>No registrado</i>';

$sql_from ="SELECT NOM_DESTINATARIO, 
					MAIL 
			FROM DESTINATARIO 
			WHERE COD_DESTINATARIO = $cod_destinatario";

$result_from = $db->build_results($sql_from);					
$nom_from = $result_from[0]['NOM_DESTINATARIO'];
$mail_from = $result_from[0]['MAIL'];

//listado de todos a los que se enviara mail
$nom_todos_destinatario = "";
$array_des = explode('|', $cod_destinatario_envio);
$count = count($array_des);

for ($i = 0; $i < $count; $i++) {
	
	$sql_para ="SELECT NOM_DESTINATARIO
			FROM DESTINATARIO 
			WHERE COD_DESTINATARIO = $array_des[$i]";

	$result_para = $db->build_results($sql_para);
	$nom_todos_destinatario = $nom_todos_destinatario.$result_para[0]['NOM_DESTINATARIO'].",";
}

$nom_todos_destinatario = substr ($nom_todos_destinatario, 0, strlen($nom_todos_destinatario) - 1);



$body = "<html>
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
<table width='440' height='171' border='2' bordercolor='#660033'>
   <tr>
     <td bgcolor='#FFF3E8'> <table width='400' border='0'>
         <tr>
           <td width='350'><h4 class='Estilo22'>Registro de llamados Nº: $cod_llamado</h4></td>
          
        </tr>
     </table>     </td>
   </tr>
   <tr>
     <td bgcolor='#FFF1EC'>
     	<table width='400' border='0' align='center'>
        <tr>
          <td width='147' bordercolor='#993399'><h5 class='Estilo22'>Mensaje</h5></td>
          <td width='10'><h5 class='Estilo22'>:</h5></td>
          <td width='362' bordercolor='#9933FF'><h5 class='Estilo22'>$mensaje</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Llamado N&ordm;</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$cod_llamado</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Raz&oacute;n Social</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$nom_contacto</h5></td>
        </tr>
         <tr>
          <td><h5 class='Estilo13'>Rut</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$rut_emp-$dig_verf</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Direccion</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$direccion</h5></td>
        </tr>
        
        <tr>
          <td><h5 class='Estilo13'>Giro</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$giro</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Cont&aacute;cto</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$nom_persona</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Cargo</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$cargo</h5></td>
        </tr>
		 <tr>
          <td><h5 class='Estilo13'>mail</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$mail_contac</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Acci&oacute;n</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$nom_llamado_accion</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Llamar a</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$llamar_telefono</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Registrado por</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$nom_from</h5></td>
        </tr>
        <tr>
          <td colspan='3'><h6 class='Estilo15'>Enviado a los siguientes destinatarios:</h6>
          <p class='claro'><h6>$nom_todos_destinatario<h6></p></td>
        </tr>
        <tr>
          ";

$altbody = "";

//envia mail a cada uno de los destinatarios
$db1 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
for ($i = 0; $i < $count; $i++) {
	$mail = new phpmailer();
	$mail->PluginDir = dirname(__FILE__)."/../../../../../commonlib/trunk/php/clases/";
	$mail->Mailer = "smtp";
	$mail->SMTPAuth = true;
	
	$mail->Host = $URL_SMTP;
	$mail->Username = $USER_SMTP;
	$mail->Password = $PASS_SMTP;
	$mail->Port = $PORT_SMTP;
	$mail->SMTPSecure= 'ssl'; 

	$mail->From = $mail_from;
	$mail->FromName = $nom_from;
	$mail->Timeout=30;


	$sql = "SELECT COUNT(*) CANT
			FROM CONVERSACION
			WHERE COD_LLAMADO =$cod_llamado";
	$result_cant = $db1->build_results($sql);
	$nro = 	$result_cant[0]['CANT']+1;	
	$mail->Subject = "[Nº: $cod_llamado] $nom_contacto : CONVERSACION $nro $ms_realizado";

	$sql_para ="SELECT COD_DESTINATARIO, MAIL 
			FROM DESTINATARIO 
			WHERE COD_DESTINATARIO = $array_des[$i]";

	$result_para = $db1->build_results($sql_para);
	
	$cod_destinatario = $result_para[0]['COD_DESTINATARIO'];
	$mail_para = $result_para[0]['MAIL'];
	
	$cod_destinatario_enc = encriptar_url($cod_destinatario, 'envio_mail_llamado');
	$param_enc = "ll=".$cod_llamado_enc."&d=".$cod_destinatario_enc;
	$link_final = $link.$param_enc;					
	
	$mail->AddAddress($mail_para);
	$final_html= "<td colspan='3' bgcolor='#FFF1EC'><table width='350' border='1'>
				            <tr>
				              <td bgcolor='#EAFDFD' class='Estilo15'><h5>Si desea responder, d&eacute; clic en el <em><a href='$link_final'>link</a></em><h5></td>
				              </tr>
				          </table>          
				          
				          <tr width='100%' align='right'>
				          <td align='right'></td>
				          <td align='right'></td>
				          <td ><h4 class='Estilo13'>$m_realizado</h4></td>
				          </tr>
				          <h6 class='Estilo15'>&nbsp;</h6>
				          
				          </td>
				        </tr>
				          </table>
				          </td>
				           </tr>
				</table>
				</blockquote>
				</body>
					</html>";
	
	$mail->Body = $body.$final_html;
	$mail->AltBody = $altbody.$link_final;

	$exito = $mail->Send();
	if(!$exito)
	{
		echo "Problema al enviar correo electrónico a ".$mail_para;
	}
	
}


$db_c = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db_c->build_results("exec spu_llamado_conversa 'INSERT', NULL, $cod_llamado, $cod_destinatario_resp,'$mensaje','$realizado','S'");

if($realizado == 'S'){													
	$result = $db_c->build_results("exec spu_llamado 'REALIZADO_WEB', 
								$cod_llamado, 
								NULL, 
								NULL,
								NULL, 
								NULL,
								NULL, 
								NULL,
								'S',
								'$mensaje',
								'$tipo_doc_realizado',
								$cod_doc_realizado");		
}							
										

//////////////////////////////////////////////////////////
/////  Se envia mail de confirmacion al destinatario que da respuesta


$db2 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql2= "SELECT GLOSA
FROM LLAMADO_CONVERSA
WHERE COD_LLAMADO = $cod_llamado
order by COD_LLAMADO_CONVERSA desc";
$result = $db2->build_results($sql2);
$respon = $result[0]['GLOSA'];

$sql_dest = "SELECT d.NOM_DESTINATARIO
FROM LLAMADO_CONVERSA LC , DESTINATARIO D
WHERE LC.COD_DESTINATARIO = D.COD_DESTINATARIO
AND COD_LLAMADO = $cod_llamado
order by COD_LLAMADO_CONVERSA desc";
$result_dest = $db2->build_results($sql_dest);
$dest = $result_dest[0]['NOM_DESTINATARIO'];

$body = "<html>
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
<table width='420' height='171' border='2' bordercolor='#660033'>
   <tr>
     <td bgcolor='#F0FFF0'>
	<table width='400' border='0'>
         <tr>
         <td> <h5 class='Estilo22'>Estimado(a): $dest</h5></td>
		</tr>
		<tr>
		</tr>
		
		<tr>
    	 <td> <h5 class='Estilo22'>ESTA ES UNA CONFIRMACIÓN A SU RESPUESTA VÍA WEB DEL LLAMADO Nº: $cod_llamado</h5></td>
         </tr>
     </table>     
     </td>
   </tr>
   <td bgcolor='#F0FFF0'>
	<table width='350' border='0'>
         <tr>
           <td width='350'><h5 class='Estilo22'>Su respuesta fue: $respon</h5></td>
        </tr>
     </table>     
     </td>
     <tr>
       <td bgcolor='#F0FFF0'>
	<table width='350' border='0'>
	<tr>
	</tr>
       <tr>
          <td><h5 class='Estilo13'>Raz&oacute;n Social</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$nom_contacto</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Cont&aacute;cto</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$nom_persona</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Cargo</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$cargo</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Acci&oacute;n</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$nom_llamado_accion</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Llamar a</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$llamar_telefono</h5></td>
        </tr>
        <tr>
          <td><h5 class='Estilo13'>Registrado por</h5></td>
          <td><h5 class='Estilo13'>:</h5></td>
          <td><h5 class='Estilo13'>$nom_from</h5></td>
        </tr>
        <tr>
	</tr>
	        <tr>
	</tr>
	
        <tr>
          <td colspan='3'><h6 class='Estilo15'>SU RESPUESTA FUE ENVIADA A LOS SIGUIENTES DESTINATARIOS:</h6>
          <p class='claro'><h6>$nom_todos_destinatario<h6></p></td>
        </tr>
   		
          ";
     $altbody = "";
        
    $final_html ="
     		</table>
				</blockquote>
				</body>
					</html>";
$mail = new phpmailer();
$mail->PluginDir = dirname(__FILE__)."/../../../../../commonlib/trunk/php/clases/";
$mail->Mailer = "smtp";
$mail->SMTPAuth = true;

$mail->Host = $URL_SMTP;
$mail->Username = $USER_SMTP;
$mail->Password = $PASS_SMTP;
$mail->Port = $PORT_SMTP;
$mail->SMTPSecure= 'ssl'; 
	
$mail->From = $mail_from;
$mail->FromName = $nom_from;
$mail->Timeout=30;

$sql = "SELECT COUNT(*) CANT
		FROM CONVERSACION
		WHERE COD_LLAMADO =$cod_llamado";
$result_cant = $db1->build_results($sql);
$nro = 	$result_cant[0]['CANT']+1;	
$mail->Subject = "[$! CONFIRMACION DE RESPUESTA !$] [Nº: $cod_llamado] $nom_contacto : CONVERSACION $nro $ms_realizado";
 
$sql_para ="SELECT MAIL 
		FROM DESTINATARIO 
		WHERE COD_DESTINATARIO = $cod_destinatario_resp";

$result_para = $db1->build_results($sql_para);

$mail_para = $result_para[0]['MAIL'];

$cod_destinatario_enc = encriptar_url($cod_destinatario_resp, 'envio_mail_llamado');
$param_enc = "ll=".$cod_llamado_enc."&d=".$cod_destinatario_enc;
$link_final = $link.$param_enc;					

$mail->AddAddress($mail_para);


$mail->Body = $body.$final_html;
$mail->AltBody = $altbody.$link_final;

$exito = $mail->Send();
if(!$exito)
{
	print "Problema al enviar correo electrónico a ".$mail_para;
}

print("Mensaje enviado correctamente");	


?>
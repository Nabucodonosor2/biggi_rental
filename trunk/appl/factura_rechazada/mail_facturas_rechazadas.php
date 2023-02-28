<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$db->query("exec spx_resuelve_fa_rechazadas");

$sql_param = "select dbo.f_get_parametro(53) URL_SMTP
                    ,dbo.f_get_parametro(54) USER_SMTP
                    ,dbo.f_get_parametro(55) PASS_SMTP
                    ,dbo.f_get_parametro(71) PORT_SMTP
                    ,CONVERT(VARCHAR,DATEADD(DAY, -1, GETDATE()), 103) FECHA_ANTERIOR";
$result_param = $db->build_results($sql_param);

$host           = $result_param[0]['URL_SMTP'];
$Username       = $result_param[0]['USER_SMTP'];
$Password       = $result_param[0]['PASS_SMTP'];
$Port 	        = $result_param[0]['PORT_SMTP'];
$fecha_anterior = $result_param[0]['FECHA_ANTERIOR'];

$temp = new Template_appl('mail_facturas_rechazadas.htm');

$sql2 = "select F.NRO_FACTURA
                ,CONVERT(VARCHAR, FECHA_FACTURA, 103) + ' - ' + CONVERT(VARCHAR, FR.FECHA_RECHAZO, 103) FECHAS
                ,dbo.number_format(E.RUT, 0,',', '.')+'-'+E.DIG_VERIF+' '+E.NOM_EMPRESA	CLIENTE
                ,dbo.number_format(F.TOTAL_CON_IVA, 0,',', '.') TOTAL_CON_IVA
                ,CASE
                    WHEN dbo.f_get_nc_from_fa(F.COD_FACTURA) IS NULL THEN 'FALTA NC / FALTA REFACTURAR'
                    WHEN dbo.f_get_nc_from_fa(F.COD_FACTURA) IS NOT NULL AND NRO_RE_FACTURA IS NULL THEN 'NC EMITIDA / FALTA REFACTURAR'
                END STATUS
                ,ISNULL(dbo.f_origen_arriendo1(F.COD_FACTURA,'ARRIENDOS_X_FACTURA2'), 'SIN ARRIENDO') ARRIENDO
        FROM FACTURA_RECHAZADA FR LEFT OUTER JOIN USUARIO U ON U.COD_USUARIO = FR.COD_USUARIO_RESUELTA
            ,FACTURA F
            ,EMPRESA E
            ,USUARIO UV1
        WHERE RESUELTA = 'N'
        AND FECHA_RECHAZO <= DATEADD(DAY, -1, GETDATE())
        AND FR.COD_FACTURA = F.COD_FACTURA
        AND E.COD_EMPRESA = F.COD_EMPRESA
        AND UV1.COD_USUARIO = F.COD_USUARIO_VENDEDOR1
        ORDER BY FR.FECHA_RECHAZO ASC";

$result2 = $db->build_results($sql2);

if(count($result2) == 1){
    $temp->setVar("FACTURAS", '1 Factura Rechazada');
    $temp->setVar("FACTURA_LABEL", 'la Factura Rechazada');
    $temp->setVar("FACTURA_LABEL_TABLE", 'Factura Rechazada');
}else{
    $temp->setVar("FACTURAS", count($result2).' Facturas Rechazadas');
    $temp->setVar("FACTURA_LABEL", 'las Facturas Rechazadas');
    $temp->setVar("FACTURA_LABEL_TABLE", 'Facturas Rechazadas');
}

$temp->setVar("FECHA_ANTERIOR", $fecha_anterior);
$tbody = '';

for ($j=0; $j < count($result2); $j++) { 
    $tbody .='<tbody>
                <tr bgcolor="#f2f2f2">
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result2[$j]['NRO_FACTURA'].'</td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result2[$j]['FECHAS'].'</td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="left" valign="top">'.$result2[$j]['CLIENTE'].'</td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result2[$j]['ARRIENDO'].'</td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result2[$j]['TOTAL_CON_IVA'].'</td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result2[$j]['STATUS'].'</td>
                </tr>
            </tbody>';
}

$temp->setVar("TBODY", $tbody);
$html = $temp->toString();
            
$mail               = new phpmailer();
$mail->PluginDir    = dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/";
$mail->Mailer       = "smtp";
$mail->SMTPAuth     = true;
$mail->Host         = "$host";
$mail->Username     = "$Username";
$mail->Password     = "$Password";
$mail->Port         = "$Port";
$mail->SMTPSecure   = 'ssl';
$mail->From         = "modulo_alertas@biggi.cl";		
$mail->FromName     = "M�dulo Alertas Grupo BIGGI";
$mail->Timeout      = 30;
$mail->Subject      = "Informe Facturas Rechazadas al $fecha_anterior";
$mail->ClearAddresses();

/*$mail->AddAddress('mherrera@biggi.cl','Marcelo Herrera');
$mail->AddCC('isra.campos.o@gmail.com', 'Israel Campos');*/

$mail->AddAddress('sergio.pechoante@biggi.cl', 'Sergio Pechoante');
$mail->AddAddress('caravena@biggi.cl', 'Cristian Aravena');
$mail->AddAddress('jcatalan@biggi.cl', 'Jose Catal�n');
$mail->AddAddress('fpuebla@biggi.cl', 'Felipe Puebla');
$mail->AddBCC('mherrera@biggi.cl','Marcelo Herrera');

//$mail->AddEmbeddedImage("../../images_appl/logobiggipo.jpg",'logo_biggi');
$mail->Body         = $html;
$mail->AltBody      = "";
$mail->ContentType  ="text/html";

$exito = $mail->Send();

if(!$exito)
    echo "Problema al enviar correo electr�nico";
else
    echo "Se ha enviado con exito";

?>
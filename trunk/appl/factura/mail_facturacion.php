<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);

$db     = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

//$fecha_hoy = "GETDATE()";//tiene que ser formato DATETIME
$fecha_hoy = "{ts '2022-11-01 00:00:00.000'}";
$sql    = "SELECT   CONVERT(VARCHAR, $fecha_hoy, 103) FECHA
                    ,dbo.f_get_parametro(53) URL_SMTP
                    ,dbo.f_get_parametro(54) USER_SMTP
                    ,dbo.f_get_parametro(55) PASS_SMTP
                    ,dbo.f_get_parametro(71) PORT_SMTP
                    ,CASE MONTH($fecha_hoy)
                        when 1 then 'ENERO ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                        when 2 then 'FEBRERO ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                        when 3 then 'MARZO ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                        when 4 then 'ABRIL ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                        when 5 then 'MAYO ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                        when 6 then 'JUNIO ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                        when 7 then 'JULIO ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                        when 8 then 'AGOSTO ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                        when 9 then 'SEPTIEMBRE ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                        when 10 then 'OCTUBRE ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                        when 11 then 'NOVIEMBRE ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                        when 12 then 'DICIEMBRE ' + CONVERT(VARCHAR, YEAR($fecha_hoy))
                    END FECHA_NAME
                    ,FORMAT($fecha_hoy, 'HH:mm') + ' hrs' FECHA_HORA";

$result = $db->build_results($sql);

$fecha      = $result[0]['FECHA'];
$fecha_name = $result[0]['FECHA_NAME'];
$fecha_hora = $result[0]['FECHA_HORA'];
$host       = $result[0]['URL_SMTP'];
$Username   = $result[0]['USER_SMTP'];
$Password   = $result[0]['PASS_SMTP'];
$Port 	    = $result[0]['PORT_SMTP'];

$temp = new Template_appl('mail_facturacion.htm');
$temp->setVar("FECHA", $fecha);
$temp->setVar("FECHA_NAME", $fecha_name);
$temp->setVar("FECHA_HORA", $fecha_hora);

$sql_dw1 = "exec spdw_informe_facturacion_rental 'FACTURACION', '$fecha'";
$result_dw1 = $db->build_results($sql_dw1);

$tot_monto_neto = 0;
$tot_monto_nc = 0;
$tot_monto_neto_nc = 0;

if(count($result_dw1) > 0){
    for($i=0; $i < count($result_dw1); $i++) {
        $tot_monto_neto     += (int)$result_dw1[$i]['FIELD_CUATRO'];
        $tot_monto_nc       += (int)$result_dw1[$i]['FIELD_CINCO'];
        $tot_monto_neto_nc  += (int)$result_dw1[$i]['FIELD_SEIS'];

        $tbody .=  '<tr bgcolor="#f2f2f2">
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_dw1[$i]['FIELD_UNO'].'</td>
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_dw1[$i]['FIELD_DOS'].'</td>
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_dw1[$i]['FIELD_TRES'].'</td>
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">$ '.number_format($result_dw1[$i]['FIELD_CUATRO'], 0, ',', '.').'</td>
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">$ '.number_format($result_dw1[$i]['FIELD_CINCO'], 0, ',', '.').'</td>
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">$ '.number_format($result_dw1[$i]['FIELD_SEIS'], 0, ',', '.').'</td>
                    </tr>';
    }

    $tbody .=  '<tr>
                    <td colspan="2" style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top"></td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0; background-color: #bcd8ed;" align="right" valign="top"><b>TOTALES NETO</b></td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0; background-color: #bcd8ed;" align="right" valign="top"><b> $ '.number_format($tot_monto_neto, 0, ',', '.').'</b></td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0; background-color: #bcd8ed;" align="right" valign="top"><b> $ '.number_format($tot_monto_nc, 0, ',', '.').'</b></td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0; background-color: #bcd8ed;" align="right" valign="top"><b> $ '.number_format($tot_monto_neto_nc, 0, ',', '.').'</b></td>
                </tr>';

}else{
    $tbody =   '<tr bgcolor="#f2f2f2">
                    <td colspan="6" style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">SIN DOCUMENTOS PARA HOY '.$nombre_day.' '.$fecha_actual.'</td>
                </tr>';
}

$temp->setVar("TBODY_UNO", $tbody);
$temp->setVar("TOT_MONTO_NETO", number_format($tot_monto_neto, 0, ',', '.'));
$temp->setVar("TOT_MONTO_NC", number_format($tot_monto_nc, 0, ',', '.'));
$temp->setVar("TOT_MONTO_NETO_NC", number_format($tot_monto_neto_nc, 0, ',', '.'));

$sql_dw2 = "exec spdw_informe_facturacion_rental 'FACTURACION_VENTA', '$fecha'";
$result_dw2 = $db->build_results($sql_dw2);

//se reinicia y reutiliza variable
$tbody = "";
$tot_monto_neto = 0;
$tot_monto_nc = 0;
$tot_monto_neto_nc = 0;

if(count($result_dw2) > 0){
    for($i=0; $i < count($result_dw2); $i++) {
        $tot_monto_neto     += (int)$result_dw2[$i]['FIELD_CUATRO'];
        $tot_monto_nc       += (int)$result_dw2[$i]['FIELD_CINCO'];
        $tot_monto_neto_nc  += (int)$result_dw2[$i]['FIELD_SEIS'];

        $tbody .=  '<tr bgcolor="#f2f2f2">
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_dw2[$i]['FIELD_UNO'].'</td>
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_dw2[$i]['FIELD_DOS'].'</td>
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_dw2[$i]['FIELD_TRES'].'</td>
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">$ '.number_format($result_dw2[$i]['FIELD_CUATRO'], 0, ',', '.').'</td>
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">$ '.number_format($result_dw2[$i]['FIELD_CINCO'], 0, ',', '.').'</td>
                        <td style="line-height: 24px; font-size: 14px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">$ '.number_format($result_dw2[$i]['FIELD_SEIS'], 0, ',', '.').'</td>
                    </tr>';
    }

    $tbody .=  '<tr>
                    <td colspan="2" style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top"></td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0; background-color: #bcd8ed;" align="right" valign="top"><b>TOTALES NETO</b></td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0; background-color: #bcd8ed;" align="right" valign="top"><b> $ '.number_format($tot_monto_neto, 0, ',', '.').'</b></td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0; background-color: #bcd8ed;" align="right" valign="top"><b> $ '.number_format($tot_monto_nc, 0, ',', '.').'</b></td>
                    <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0; background-color: #bcd8ed;" align="right" valign="top"><b> $ '.number_format($tot_monto_neto_nc, 0, ',', '.').'</b></td>
                </tr>';

}else{
    $tbody =   '<tr bgcolor="#f2f2f2">
                    <td colspan="6" style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">SIN DOCUMENTOS PARA HOY '.$nombre_day.' '.$fecha_actual.'</td>
                </tr>';
}

$temp->setVar("TBODY_DOS", $tbody);
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
$mail->FromName     = "Módulo Alertas Grupo BIGGI";
$mail->Timeout      = 30;
$mail->Subject      = "INFORME FACTURACIÓN SISTEMA RENTAL $fecha_name";
$mail->ClearAddresses();

$mail->AddAddress('mherrera@biggi.cl','Marcelo Herrera');
$mail->AddAddress('isra.campos.o@gmail.com ', 'PRUEBA');

//$mail->AddEmbeddedImage("../../images_appl/logobiggipo.jpg",'logo_biggi');
$mail->Body         = $html;
$mail->AltBody      = "";
$mail->ContentType  ="text/html";

$exito = $mail->Send();

if(!$exito)
    echo "Problema al enviar correo electrónico";
else
    echo "Se ha enviado con exito";
?>
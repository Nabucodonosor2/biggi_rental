<?php
//ini_set('display_errors', 1);
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
include(dirname(__FILE__)."/../../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);

ini_set('max_execution_time', 900); //900 seconds = 15 minutes 

$K_ESTADO_SII_IMPRESA 	= 2;
$K_ESTADO_SII_ENVIADA	= 3;

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$nom_vendedor = $sql_cod_vendedor[$i]['NOM_USUARIO'];
$mail_vendedor = $sql_cod_vendedor[$i]['MAIL'];

//creando el template enviando nombre y cod_usuario
$temp = new Template_appl('facturas_por_cobrar_rental.htm');
$temp->setVar("NOM_VENDEDOR", "RENTAL");		

// Calculando el total de sus facturas con saldos
$sql_total	= 	"SELECT sum(dbo.f_fa_saldo(F.COD_FACTURA)) TOTAL
					   ,convert(varchar, getdate(), 103) FECHA_INF_DOS
						FROM	FACTURA F
						WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
						AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")";
$total_fa	= $db->build_results($sql_total);
$fecha_inf	= $total_fa[0]['FECHA_INF_DOS'];
$total_fa	= $total_fa[0]['TOTAL'];

$temp->setVar("TOTAL_SALDO", number_format($total_fa, 0, ',','.'));

$dws['FECHA'] = new datawindow("select convert(varchar, getdate(), 103) FECHA_INFORME
									  ,dbo.f_get_month_text(MONTH(GETDATE()))				 		H_MENOS_30_TOTAL
									  ,dbo.f_get_month_text(MONTH(DATEADD(MONTH, -1, GETDATE()))) 	H_MAS_30_TOTAL
									  ,dbo.f_get_month_text(MONTH(DATEADD(MONTH, -2, GETDATE())))	H_MAS_60_TOTAL
									  ,dbo.f_get_month_text(MONTH(DATEADD(MONTH, -3, GETDATE()))) 	H_MAS_90_TOTAL
									  ,dbo.f_get_month_text(MONTH(DATEADD(MONTH, -4, GETDATE()))) 	H_MES_RESUMEN_ANT");
$dws['FECHA']->retrieve();

$dws['RESUMEN'] = new datawindow("exec spdw_fa_x_cobrar 'RESUMEN', 'S'", 'RESUMEN');
$dws['RESUMEN']->add_control(new static_num('MAS_90_TOTAL'));
$dws['RESUMEN']->add_control(new static_num('MAS_60_TOTAL'));
$dws['RESUMEN']->add_control(new static_num('MAS_30_TOTAL'));
$dws['RESUMEN']->add_control(new static_num('MENOS_30_TOTAL'));
$dws['RESUMEN']->add_control(new static_num('MAS_ESPECIAL'));
$dws['RESUMEN']->add_control(new static_num('TOTAL'));
$dws['RESUMEN']->retrieve();

$sum_total = 0;
for($i=0; $i < $dws['RESUMEN']->row_count() ; $i++){
	$total = $dws['RESUMEN']->get_item($i, 'TOTAL');
	$sum_total = $sum_total + $total;
}
$temp->setVar("SUM_TOTAL_TABLE_UNO", number_format($sum_total, 0, ',','.'));

$dws['RESUMEN_OTROS'] = new datawindow("exec spdw_fa_x_cobrar 'RESUMEN_VENTA'", 'RESUMEN_VENTA');
$dws['RESUMEN_OTROS']->add_control(new static_num('MAS_90_TOTAL'));
$dws['RESUMEN_OTROS']->add_control(new static_num('MAS_60_TOTAL'));
$dws['RESUMEN_OTROS']->add_control(new static_num('MAS_30_TOTAL'));
$dws['RESUMEN_OTROS']->add_control(new static_num('MENOS_30_TOTAL'));
$dws['RESUMEN_OTROS']->add_control(new static_num('MAS_ESPECIAL'));
$dws['RESUMEN_OTROS']->add_control(new static_num('TOTAL'));
$dws['RESUMEN_OTROS']->retrieve();

$sum_total = 0;
for($k=0; $k < $dws['RESUMEN_OTROS']->row_count() ; $k++){
	$total = $dws['RESUMEN_OTROS']->get_item($k, 'TOTAL');
	$sum_total = $sum_total + $total;
}
$temp->setVar("SUM_TOTAL_TABLE_DOS", number_format($sum_total, 0, ',','.'));

$dws['RESUMEN_ANTERIOR'] = new datawindow("exec spdw_fa_x_cobrar 'RESUMEN_ANTERIOR', 'S'", 'RESUMEN_ANTERIOR');
$dws['RESUMEN_ANTERIOR']->add_control(new static_num('MONTO'));
$dws['RESUMEN_ANTERIOR']->add_control(new static_num('PORC', 2));
$dws['RESUMEN_ANTERIOR']->retrieve();

$sum_monto = 0;
$sum_porc = 0;
for($j=0; $j < $dws['RESUMEN_ANTERIOR']->row_count() ; $j++){
	$monto = $dws['RESUMEN_ANTERIOR']->get_item($j, 'MONTO');
	$porc = $dws['RESUMEN_ANTERIOR']->get_item($j, 'PORC');
	$sum_monto = $sum_monto + $monto;
	$sum_porc = $sum_porc + $porc;
}

$temp->setVar("SUM_MONTO_TABLE_UNO", number_format($sum_monto, 0, ',','.'));
$temp->setVar("SUM_PORC_TABLE_UNO", number_format($sum_porc, 2, ',','.'));

$dws['OTROS_DETALLE'] = new datawindow("exec spdw_fa_x_cobrar 'RESUMEN_ANTERIOR_VENTA'", 'RESUMEN_ANTERIOR_VENTA');
$dws['OTROS_DETALLE']->add_control(new static_text('FECHA_FACTURA'));
$dws['OTROS_DETALLE']->add_control(new static_num('MONTO'));
$dws['OTROS_DETALLE']->add_control(new static_num('PORC', 2));
$dws['OTROS_DETALLE']->retrieve();

$sum_monto = 0;
$sum_porc = 0;
for($l=0; $l < $dws['OTROS_DETALLE']->row_count() ; $l++){
	$monto = $dws['OTROS_DETALLE']->get_item($l, 'MONTO');
	$porc = $dws['OTROS_DETALLE']->get_item($l, 'PORC');
	$sum_monto = $sum_monto + $monto;
	$sum_porc = $sum_porc + $porc;
}

$temp->setVar("SUM_MONTO_TABLE_DOS", number_format($sum_monto, 0, ',','.'));
$temp->setVar("SUM_PORC_TABLE_DOS", number_format($sum_porc, 2, ',','.'));

// habilitando el template
$dws['FECHA']->habilitar($temp, false);
$dws['RESUMEN']->habilitar($temp, false);
$dws['RESUMEN_OTROS']->habilitar($temp, false);
$dws['RESUMEN_ANTERIOR']->habilitar($temp, false);
$dws['OTROS_DETALLE']->habilitar($temp, false);

$html = $temp->toString();

// Envio de mail
$asunto = 'Facturas por Cobrar Sistema Web Rental al '.$fecha_inf.'.-';
	
$sql = "select dbo.f_get_parametro(53) 		URL_SMTP
		,dbo.f_get_parametro(54) 	USER_SMTP
		,dbo.f_get_parametro(55) 	PASS_SMTP
		,dbo.f_get_parametro(71) 	PORT_SMTP";
$result = $db->build_results($sql);

$host     = $result[0]['URL_SMTP'];
$Username = $result[0]['USER_SMTP'];
$Password = $result[0]['PASS_SMTP'];
$Port 	  = $result[0]['PORT_SMTP'];

$mail = new phpmailer();
$mail->PluginDir = dirname(__FILE__)."/../../../../../commonlib/trunk/php/clases/";
$mail->Mailer 	= "smtp";
$mail->SMTPAuth = true;
$mail->Host 	= "$host";
$mail->Username = "$Username";
$mail->Password = "$Password"; 
$mail->Port = "$Port";
$mail->SMTPSecure= 'ssl'; 
$mail->From 	= "sergio.pechoante@biggi.cl";		
$mail->FromName = "Sergio Pechoante";
$mail->Timeout	= 30;
$mail->Subject = $asunto;

$mail->ClearAddresses();

$mail->AddAddress('ascianca@biggi.cl', 'Angel Scianca');
$mail->AddAddress('sergio.pechoante@biggi.cl', 'Sergio Pechoante');
//$mail->AddAddress('rescudero@biggi.cl', 'Rafael Escudero');

$mail->AddAddress('lleiva@biggi.cl', 'Loreto Leiva');

$mail->AddBCC('mherrera@biggi.cl', 'Marcelo Herrera');	
$mail->AddBCC('evergara@integrasystem.cl', 'Erik');
$mail->AddBCC('vmelo@integrasystem.cl', 'Victor Melo');

//$mail->AddAddress('icampos@integrasystem.cl', 'Israel Campos');

$mail->Body = $html;
$mail->AltBody = "";
$mail->ContentType="text/html";

$exito = $mail->Send();

if(!$exito){
	echo "Problema al enviar correo electrónico";
}
else
	echo "Enviado";
?>
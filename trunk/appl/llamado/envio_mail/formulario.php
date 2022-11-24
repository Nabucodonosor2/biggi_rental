<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once ("../../../appl.ini");
require_once("funciones.php");

session::set('K_ROOT_URL', K_ROOT_URL);
session::set('K_ROOT_DIR', K_ROOT_DIR);
session::set('K_CLIENTE', K_CLIENTE);
session::set('K_APPL', K_APPL);

$cod_llamado = $_REQUEST['ll'];
$cod_destinatario = $_REQUEST['d'];

$cod_llamado = dencriptar_url($cod_llamado, 'envio_mail_llamado');
$cod_destinatario = dencriptar_url($cod_destinatario, 'envio_mail_llamado');

$sql = "SELECT COD_LLAMADO
	,NOM_CONTACTO
	,RUT
	,DIG_VERIF
	,NOM_PERSONA
	,CARGO
	,NOM_LLAMADO_ACCION
	,MENSAJE
FROM LLAMADO LL, CONTACTO C, CONTACTO_PERSONA CP, LLAMADO_ACCION LLA
WHERE COD_LLAMADO = $cod_llamado
	AND C.COD_CONTACTO = LL.COD_CONTACTO
	AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA
	AND LLA.COD_LLAMADO_ACCION = LL.COD_LLAMADO_ACCION";
	
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db->build_results($sql);	
	
$NOM_CONTACTO = $result[0]['NOM_CONTACTO'];
$RUT = $result[0]['RUT'];
$DIG_VERIF = $result[0]['DIG_VERIF'];
$NOM_PERSONA = $result[0]['NOM_PERSONA'];
$CARGO = $result[0]['CARGO'];
$NOM_LLAMADO_ACCION = $result[0]['NOM_LLAMADO_ACCION'];
$MENSAJE = $result[0]['MENSAJE'];

$temp = new Template_appl("formulario.htm");
$temp->setVar("COD_LLAMADO", $cod_llamado);
$temp->setVar("COD_LLAMADO_H", '<input name="COD_LLAMADO_H" id="COD_LLAMADO_H" type="hidden" value="'.$cod_llamado.'">');
$temp->setVar("NOM_CONTACTO", $NOM_CONTACTO);
$temp->setVar("RUT", number_format($RUT, 0, ',', '.'));

$temp->setVar("DIG_VERIF", $DIG_VERIF);
$temp->setVar("NOM_PERSONA", $NOM_PERSONA);
$temp->setVar("CARGO", $CARGO);
$temp->setVar("NOM_LLAMADO_ACCION", $NOM_LLAMADO_ACCION);
$temp->setVar("MENSAJE", "");
$temp->setVar("TIPO_DOC_REALIZADO", '<SELECT style="width:120px; font-size:9pt" NAME="TIPO_DOC_REALIZADO">
<OPTION SELECTED VALUE="">
<OPTION VALUE="COTIZACION">COTIZACION
<OPTION VALUE="NOTA VENTA">NOTA VENTA
<OPTION VALUE="GUIA DESPACHO">GUIA DESPACHO
<OPTION VALUE="FACTURA">FACTURA
</SELECT> ');
$temp->setVar("COD_DOC_REALIZADO", '<input name="COD_DOC_REALIZADO" type="text" onKeyPress="return numbersonly(this, event)" size="10" maxLength="10">');

$temp->setVar("MENSAJE_ORIGINAL", $MENSAJE);
$temp->setVar("COD_DESTINATARIO_ENVIO_H", '<input name="COD_DESTINATARIO_ENVIO_H" id="COD_DESTINATARIO_ENVIO_H" type="hidden" value="">');
$temp->setVar("COD_DESTINATARIO_H", '<input name="COD_DESTINATARIO_H" id="COD_DESTINATARIO_H" type="hidden" value="'.$cod_destinatario.'">');

///DESTINATARIO
$db2 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql_des = "SELECT  LL.COD_DESTINATARIO
						,NOM_DESTINATARIO
						,'S' ENVIAR_MAIL
				FROM LLAMADO_DESTINATARIO LL 
						LEFT OUTER JOIN DESTINATARIO D ON D.COD_DESTINATARIO = LL.COD_DESTINATARIO
				WHERE COD_LLAMADO = $cod_llamado
					AND LL.COD_DESTINATARIO <> $cod_destinatario
					AND VIGENTE = 'S'
				UNION
				SELECT COD_DESTINATARIO
					,NOM_DESTINATARIO
					,'N' ENVIAR_MAIL 
				FROM DESTINATARIO
				WHERE COD_DESTINATARIO NOT IN (SELECT LLD.COD_DESTINATARIO FROM LLAMADO_DESTINATARIO LLD WHERE LLD.COD_LLAMADO = $cod_llamado)
				AND VIGENTE = 'S'
				ORDER BY  NOM_DESTINATARIO ASC";
								

$result_des = $db2->build_results($sql_des);

$dw_datos = new datawindow($sql_des, 'DESTINATARIO', true, true);

$dw_datos->add_control(new edit_check_box('ENVIAR_MAIL', 'S', 'N'));        
$dw_datos->add_control(new edit_text_hidden('COD_DESTINATARIO'));
$dw_datos->add_control(new static_text('NOM_DESTINATARIO'));
$entrable = true;

$dw_datos->retrieve();
$dw_datos->habilitar($temp, $entrable);

///CONVERSACION
$db3 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql_conv = "SELECT CONVERT(VARCHAR(10),FECHA_LLAMADO_CONVERSA,103)+'  '+CONVERT(VARCHAR(5),FECHA_LLAMADO_CONVERSA,108)  FECHA_LLAMADO_CONVERSA
					,NOM_DESTINATARIO
					,GLOSA
					,REALIZADO
				FROM LLAMADO_CONVERSA LL, DESTINATARIO D
				WHERE COD_LLAMADO = $cod_llamado
					AND D.COD_DESTINATARIO = LL.COD_DESTINATARIO
				ORDER BY COD_LLAMADO_CONVERSA DESC";				
		
$result_conv = $db3->build_results($sql_conv);
$dw_datos_conv = new datawindow($sql_conv, 'CONVERSACION');

$dw_datos_conv->add_control(new static_text('FECHA_LLAMADO_CONVERSA'));
$dw_datos_conv->add_control(new static_text('GLOSA'));
$dw_datos_conv->add_control(new edit_check_box('REALIZADO', 'S', 'N'));

$entrable = false;

$dw_datos_conv->retrieve();

$dw_datos_conv->habilitar($temp, $entrable);
print $temp->toString();
?>
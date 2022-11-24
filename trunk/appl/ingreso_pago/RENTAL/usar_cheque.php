<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
$cod_empresa = $_REQUEST['cod_empresa'];
$vl_cod_cheque_s = $_REQUEST['cod_cheque_actual'];
$temp = new Template_appl('usar_cheque.htm');											

$sql = "SELECT 'N' SELECCION
			  ,COD_CHEQUE
			  ,(SELECT NOM_TIPO_DOC_PAGO
			    FROM TIPO_DOC_PAGO TDP
			    WHERE TDP.COD_TIPO_DOC_PAGO = C.COD_TIPO_DOC_PAGO) NOM_TIPO_DOC_PAGO
			  ,CONVERT(VARCHAR, FECHA_DOC, 103) FECHA_DOC
			  ,NRO_DOC
			  ,(SELECT NOM_BANCO FROM BANCO B WHERE B.COD_BANCO = C.COD_BANCO) NOM_BANCO
			  ,dbo.f_ch_saldo(COD_CHEQUE) MONTO_DOC
		FROM CHEQUE C, INGRESO_CHEQUE IC
		WHERE COD_EMPRESA = $cod_empresa
		AND dbo.f_ch_saldo(COD_CHEQUE) > 0
		AND C.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE ";

if($vl_cod_cheque_s <> '')
	$sql .= "AND COD_CHEQUE not in ($vl_cod_cheque_s) ";

$sql .= "ORDER BY COD_CHEQUE";

$dw = new datawindow($sql, 'INGRESO_CHEQUE');
$dw->add_control(new edit_check_box('SELECCION','S','N'));
$dw->add_control(new static_text('COD_CHEQUE'));
$dw->add_control(new static_text('NOM_TIPO_DOC_PAGO'));
$dw->add_control(new static_text('FECHA_DOC'));
$dw->add_control(new static_text('NRO_DOC'));
$dw->add_control(new static_text('NOM_BANCO'));
$dw->add_control(new static_num('MONTO_DOC'));
$dw->retrieve();
$dw->habilitar($temp, true);

print $temp->toString();
?>
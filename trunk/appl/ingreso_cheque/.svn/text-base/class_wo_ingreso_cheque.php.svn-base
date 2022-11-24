<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_ingreso_cheque extends w_output_biggi {
   	function wo_ingreso_cheque() {
		$sql = "select	 IC.COD_INGRESO_CHEQUE
						,EIC.COD_ESTADO_INGRESO_CHEQUE
						,EIC.NOM_ESTADO_INGRESO_CHEQUE
						,E.RUT
						,E.NOM_EMPRESA
						,(SELECT COUNT(*) FROM CHEQUE C WHERE C.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE) CANT_CHEQUE
						,(SELECT CONVERT(VARCHAR,MIN(FECHA_DOC),103) FROM CHEQUE C WHERE C.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE) PRIM_FECHA
						,(SELECT CONVERT(VARCHAR,MAX(FECHA_DOC),103) FROM CHEQUE C WHERE C.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE) ULT_FECHA
						,(SELECT SUM(MONTO_DOC) FROM CHEQUE C WHERE C.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE) MONTO_DOC
				from INGRESO_CHEQUE IC
					,ESTADO_INGRESO_CHEQUE EIC
					,EMPRESA E
				WHERE IC.COD_ESTADO_INGRESO_CHEQUE = EIC.COD_ESTADO_INGRESO_CHEQUE
				AND IC.COD_EMPRESA = E.COD_EMPRESA
				ORDER BY IC.COD_INGRESO_CHEQUE DESC";		
			
   		parent::w_output_biggi('ingreso_cheque', $sql, $_REQUEST['cod_item_menu']);
   		
   		//FORMATOS
   		$this->dw->add_control(new static_num('RUT'));
   		$this->dw->add_control(new edit_precio('MONTO_DOC'));

   		// headers 
		$this->add_header(new header_num('COD_INGRESO_CHEQUE', 'COD_INGRESO_CHEQUE', 'Código'));
		$sql_estado =	"SELECT	 EI.COD_ESTADO_INGRESO_CHEQUE
								,EI.NOM_ESTADO_INGRESO_CHEQUE
						FROM ESTADO_INGRESO_CHEQUE EI";
      	$this->add_header($control = new header_drop_down('NOM_ESTADO_INGRESO_CHEQUE', 'EIC.COD_ESTADO_INGRESO_CHEQUE', 'Estado', $sql_estado));
      	$control->field_bd_order = 'EIC.NOM_ESTADO_INGRESO_CHEQUE';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Cliente'));
		$this->add_header(new header_num('CANT_CHEQUE', 'CANT_CHEQUE', 'Cant. Doc.'));
		$this->add_header(new header_date('PRIM_FECHA', 'PRIM_FECHA', 'Fecha Primer Doc'));
		$this->add_header(new header_date('ULT_FECHA', 'ULT_FECHA', 'Fecha Ultimo  Doc'));
		$this->add_header(new header_num('MONTO_DOC', 'MONTO_DOC', 'Monto Total'));
   	}
}
?>
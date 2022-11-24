<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_solicitud_cotizacion extends w_output {
   function wo_solicitud_cotizacion() {
   		parent::w_base('solicitud_cotizacion', $_REQUEST['cod_item_menu']);
		 
		$sql = "select	S.COD_SOLICITUD_COTIZACION	
						,convert(varchar(20), S.FECHA_SOLICITUD_COTIZACION, 103) FECHA_SOLICITUD_COTIZACION
						,CONVERT(VARCHAR(8),S.FECHA_SOLICITUD_COTIZACION, 108) HORA_SOLICITUD_COTIZACION
						,S.FECHA_SOLICITUD_COTIZACION DATE_FECHA_SOLICITUD_COTIZACION
						,C.RUT
						,C.NOM_CONTACTO EMPRESA 
						,CP.NOM_PERSONA NOM_CONTACTO
						,S.TOTAL_NETO
			from		SOLICITUD_COTIZACION S
						,CONTACTO C
						,CONTACTO_PERSONA CP
			where		C.COD_CONTACTO = S.COD_CONTACTO
				  AND   CP.COD_CONTACTO = C.COD_CONTACTO
			order by	S.COD_SOLICITUD_COTIZACION DESC";
			
     	parent::w_output('solicitud_cotizacion', $sql, $_REQUEST['cod_item_menu']);
				
		
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
      	
			
	    // headers
      	$this->add_header($control = new header_date('FECHA_SOLICITUD_COTIZACION', 'S.FECHA_SOLICITUD_COTIZACION', 'Fecha'));
	    $control->field_bd_order = 'DATE_FECHA_SOLICITUD_COTIZACION';
	    $this->add_header(new header_num('COD_SOLICITUD_COTIZACION', 'S.COD_SOLICITUD_COTIZACION', 'Código'));
	    $this->add_header(new header_text('HORA_SOLICITUD_COTIZACION', 'HORA_SOLICITUD_COTIZACION', 'Hora'));
	  	$this->add_header(new header_text('RUT', 'C.RUT', 'Rut'));
	 	$this->add_header(new header_text('NOM_CONTACTO', 'NOM_CONTACTO', 'Nombre Contacto'));
	    $this->add_header(new header_text('EMPRESA', 'C.EMPRESA', 'Empresa'));
	    $this->add_header(new header_num('TOTAL_NETO', 'S.TOTAL_NETO', 'Total Neto'));

  	}
}
?>
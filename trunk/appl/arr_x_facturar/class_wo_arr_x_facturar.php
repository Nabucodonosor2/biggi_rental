<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_arr_x_facturar extends w_output_biggi {
   function __construct() {
       $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
       $cod_usuario = $this->cod_usuario;
       $sql = "exec sp_arr_x_facturar $cod_usuario";
      $db->query($sql);
       
   		$sql = "select  
        		A.COD_ARRIENDO
        		,A.NOM_ARRIENDO
        		,A.REFERENCIA
        		,TOTAL
        		,A.NOM_EMPRESA 
        		,A.RUT 
        		,A.DIG_VERIF 
        		FROM ARR_X_FACTURAR A 
                WHERE COD_USUARIO = $cod_usuario
				ORDER BY COD_ARRIENDO ";

   		parent::w_output_biggi('arr_x_facturar', $sql, $_REQUEST['cod_item_menu']);
				
		$this->dw->add_control(new static_num('RUT'));
			
	    // headers
      	$this->add_header(new header_num('COD_ARRIENDO', 'COD_ARRIENDO', 'N° Contrato'));
	    $this->add_header(new header_rut('RUT', 'A', 'Rut'));
	    $this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
	    $this->add_header(new header_num('TOTAL', 'TOTAL', 'Total Neto'));
	    $this->add_header(new header_text('NOM_ARRIENDO', 'NOM_ARRIENDO', 'Nombre'));
	    $this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
		
  	}
}
?>
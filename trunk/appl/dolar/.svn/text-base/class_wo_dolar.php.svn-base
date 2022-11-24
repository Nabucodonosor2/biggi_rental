<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_dolar extends w_parametrica
{
   function wo_dolar()
   {   	
      $sql = "select COD_ANO,	
      				ANO 
      			FROM ANO 
      			ORDER BY COD_ANO";
			
      parent::w_parametrica('dolar', $sql, $_REQUEST['cod_item_menu'], '3075');
      
      // headers
      $this->add_header(new header_num('COD_ANO', 'COD_ANO', 'Código'));
      $this->add_header(new header_text('ANO', 'ANO', 'Ano'));      
   }	
}
?>
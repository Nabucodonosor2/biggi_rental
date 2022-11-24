<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_proveedor_ext extends w_parametrica
{
   function wo_proveedor_ext()
   {   	
      $sql = "select   COD_PROVEEDOR_EXT,
					   NOM_PROVEEDOR_EXT,
					   ALIAS_PROVEEDOR_EXT
					   from PROVEEDOR_EXT
	        order by 	COD_PROVEEDOR_EXT desc";
			
      parent::w_parametrica('proveedor_ext', $sql, $_REQUEST['cod_item_menu'], '3070');
      
      // headers
      $this->add_header(new header_num('COD_PROVEEDOR_EXT', 'COD_PROVEEDOR_EXT', 'Código'));
      $this->add_header(new header_text('NOM_PROVEEDOR_EXT', 'NOM_PROVEEDOR_EXT', 'Proveedor'));
      
   }
}
?>
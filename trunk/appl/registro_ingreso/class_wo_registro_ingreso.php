<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_registro_ingreso extends w_output
{
   function wo_registro_ingreso()
   {
      $sql = "SELECT NUMERO_REGISTRO_INGRESO
					  ,REFERENCIA
					  ,PE.ALIAS_PROVEEDOR_EXT
					  ,PE.NOM_PROVEEDOR_EXT
					  ,NUMERO_OC
					  ,dbo.f_numero_entrada(NUMERO_REGISTRO_INGRESO) NRO_ENTRADA_BODEGA
					  ,TOTAL_EX_FCA
			    FROM REGISTRO_INGRESO_4D RI4 , PROVEEDOR_EXT PE
			    WHERE RI4.COD_PROV = PE.COD_PROVEEDOR_EXT_4D
				ORDER BY NUMERO_REGISTRO_INGRESO DESC";
			
      parent::w_output('registro_ingreso', $sql, $_REQUEST['cod_item_menu']);
      // headers
      $this->add_header(new header_num('NUMERO_REGISTRO_INGRESO', 'NUMERO_REGISTRO_INGRESO', 'Código'));
      $this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
      $this->add_header(new header_text('ALIAS_PROVEEDOR_EXT', 'ALIAS_PROVEEDOR_EXT', 'Alias'));
      $this->add_header(new header_text('NOM_PROVEEDOR_EXT', 'NOM_PROVEEDOR_EXT', 'Proveedor'));
      $this->add_header(new header_num('NUMERO_OC', 'NUMERO_OC', 'N° OC'));
      $this->add_header(new header_num('NRO_ENTRADA_BODEGA', 'NRO_ENTRADA_BODEGA', 'N° Entrada'));
      $this->add_header(new header_num('TOTAL_EX_FCA', 'TOTAL_EX_FCA', 'Total ExFCA'));
   
		$this->dw->add_control(new static_num('TOTAL_EX_FCA', 2));
   }
}
?>
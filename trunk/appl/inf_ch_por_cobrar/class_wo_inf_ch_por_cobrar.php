<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_inf_ch_por_cobrar extends w_informe_pantalla{
   function wo_inf_ch_por_cobrar(){
   		$cod_usuario =  session::get("COD_USUARIO");
   
   		$db	= new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_ch_por_cobrar", "$cod_usuario");
   		
		$sql = "SELECT ITEM
				      ,CLIENTE
				      ,RUT
				      ,CT_CH_REGISTRADO
				      ,MONTO_CH_REGISTRADOS
				      ,CT_CH_COBRADOS
				      ,MONTO_CH_COBRADOS
				      ,CT_CH_CARTERA
				      ,MONTO_CH_CARTERA
				      ,MONTO_FACT_X_COBRAR
				FROM INF_CH_POR_COBRAR
				ORDER BY ITEM";

		parent::w_informe_pantalla('inf_ch_por_cobrar', $sql, $_REQUEST['cod_item_menu']);
		$this->dw->add_control(new static_num('CT_CH_REGISTRADO'));
		$this->dw->add_control(new static_num('MONTO_CH_REGISTRADOS'));		
		$this->dw->add_control(new static_num('CT_CH_COBRADOS'));
		$this->dw->add_control(new static_num('MONTO_CH_COBRADOS'));
		$this->dw->add_control(new static_num('CT_CH_CARTERA'));
		$this->dw->add_control(new static_num('MONTO_CH_CARTERA'));
		$this->dw->add_control(new static_num('MONTO_FACT_X_COBRAR'));
		
		$this->add_header(new header_num('ITEM', 'ITEM', 'Item'));
		$this->add_header(new header_text('CLIENTE', "CLIENTE", 'Cliente'));
		$this->add_header(new header_text('RUT', "RUT", 'Rut'));
		$this->add_header(new header_num('CT_CH_REGISTRADO', 'CT_CH_REGISTRADO', 'CT CH Registrados'));
		$this->add_header(new header_num('MONTO_CH_REGISTRADOS', 'MONTO_CH_REGISTRADOS', 'Monto CH Registrados'));
		$this->add_header(new header_num('CT_CH_COBRADOS', 'CT_CH_COBRADOS', 'CT CH Cobrados'));
		$this->add_header(new header_num('MONTO_CH_COBRADOS', 'MONTO_CH_COBRADOS', 'Monto Cobrado CH'));
		$this->add_header(new header_num('CT_CH_CARTERA', 'CT_CH_CARTERA', 'CT CH Cartera'));
		$this->add_header(new header_num('MONTO_CH_CARTERA', 'MONTO_CH_CARTERA', 'Monto CH Cartera'));
		$this->add_header(new header_num('MONTO_FACT_X_COBRAR', 'MONTO_FACT_X_COBRAR', 'Monto Facturas por Cobrar'));
		
   }
}
?>
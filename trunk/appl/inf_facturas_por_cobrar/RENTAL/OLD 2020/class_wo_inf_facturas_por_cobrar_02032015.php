<?php
class wo_inf_facturas_por_cobrar extends wo_inf_facturas_por_cobrar_base {
	var $checkbox_ventas;
	var $checkbox_arriendo;
	
	function make_sql() {
   		/* El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		   hasta despues de llamar al ancestro
   		 */  
		$cod_usuario =  session::get("COD_USUARIO");
		$sql = "select	F.COD_FACTURA
						,F.NRO_FACTURA
						,F.FECHA_FACTURA
						,convert(varchar, F.FECHA_FACTURA, 103) FECHA_FACTURA_STR
						,F.FECHA_FACTURA DATE_FACTURA
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,U1.INI_USUARIO INI_USUARIO_VENDEDOR_A
						,U2.INI_USUARIO INI_USUARIO_VENDEDOR_B
						,F.TOTAL_CON_IVA
						,dbo.f_fa_saldo(F.COD_FACTURA) SALDO
						,dbo.f_fa_total_ingreso_pago(F.COD_FACTURA) PAGO
						,1 CANTIDAD_FA 
						,F.COD_USUARIO_VENDEDOR1
				FROM	FACTURA F	left outer join USUARIO U1 on U1.COD_USUARIO = F.COD_USUARIO_VENDEDOR1 
									left outer join USUARIO U2 on U2.COD_USUARIO = F.COD_USUARIO_VENDEDOR2
						,EMPRESA E
				WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
				  AND	E.COD_EMPRESA = F.COD_EMPRESA";

		if ($this->checkbox_ventas == false)
   			$sql .= " and F.COD_TIPO_FACTURA <> 1";
   		if ($this->checkbox_arriendo == false)
   			$sql .= " and F.COD_TIPO_FACTURA <> 2";
   			
   		$sql .= " ORDER BY F.FECHA_FACTURA";
   		
		return $sql;		
	}
  	function wo_inf_facturas_por_cobrar() {
   		$this->checkbox_ventas = true;
		$this->checkbox_arriendo = true;
   	
		$sql = $this->make_sql();
		
		parent::w_informe_pantalla('inf_facturas_por_cobrar', $sql, $_REQUEST['cod_item_menu']);
		
		$this->dw->add_control(new edit_text_hidden('COD_NOTA_VENTA_H'));
		
		// headers
		$this->add_header(new header_num('NRO_FACTURA', 'F.NRO_FACTURA', 'Número'));
		$this->add_header($control = new header_date('FECHA_FACTURA_STR', 'F.FECHA_FACTURA', 'Fecha'));//*****
		$control->field_bd_order = 'DATE_FACTURA';
		$this->add_header(new header_text('NOM_EMPRESA', "E.NOM_EMPRESA", 'Cliente'));
		$sql = "select	distinct F.COD_USUARIO_VENDEDOR1 COD_USUARIO ,U.NOM_USUARIO 
				FROM FACTURA F left outer join USUARIO U on U.COD_USUARIO = F.COD_USUARIO_VENDEDOR1 
				WHERE dbo.f_fa_saldo(F.COD_FACTURA) > 0
				order by U.NOM_USUARIO";

		$this->add_header(new header_drop_down('INI_USUARIO_VENDEDOR_A', 'F.COD_USUARIO_VENDEDOR1', 'V1', $sql));
		$this->add_header(new header_rut('RUT', 'F', 'Rut'));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'F.TOTAL_CON_IVA', 'Total', 0, true, 'SUM'));
		$this->add_header($control = new header_num('SALDO', 'dbo.f_fa_saldo(F.COD_FACTURA)', 'Saldo', 0, true, 'SUM'));
		$control->field_bd_order = 'SALDO';
		$this->add_header($control = new header_num('PAGO', 'dbo.f_fa_total_ingreso_pago(F.COD_FACTURA)', 'Pagos', 0, true, 'SUM'));
		$control->field_bd_order = 'PAGO';
		$this->add_header(new header_num('CANTIDAD_FA', '1', '', 0, true, 'SUM'));
		
		// controls
		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new static_num('TOTAL_CON_IVA'));
		$this->dw->add_control(new static_num('SALDO'));
		$this->dw->add_control(new static_num('PAGO'));

		$sql = "select 'S' CHECK_VENTAS,
					   'S' CHECK_ARRIENDO,
					   'N' HIZO_CLICK";
		$this->dw_check_box = new datawindow($sql);
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_VENTAS','S','N'));
		$control->set_onClick("agrega_factura(); document.getElementById('loader').style.display='';");
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_ARRIENDO','S','N'));
		$control->set_onClick("agrega_factura(); document.getElementById('loader').style.display='';");
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();
	}
	function redraw(&$temp) {
		parent::redraw(&$temp);
		$this->dw_check_box->habilitar($temp, true);
	}	
	function procesa_event() {
		if ($_POST['HIZO_CLICK_0'] == 'S') {
			$this->checkbox_ventas = isset($_POST['CHECK_VENTAS_0']);
			$this->checkbox_arriendo = isset($_POST['CHECK_ARRIENDO_0']);
			
			if ($this->checkbox_ventas)
				$this->dw_check_box->set_item(0, 'CHECK_VENTAS', 'S');
			else{
				$this->dw_check_box->set_item(0, 'CHECK_VENTAS', 'N');
			}
			
			if ($this->checkbox_arriendo)
				$this->dw_check_box->set_item(0, 'CHECK_ARRIENDO', 'S');
			else
				$this->dw_check_box->set_item(0, 'CHECK_ARRIENDO', 'N');

			$sql = $this->make_sql();
			$this->dw->set_sql($sql);
			$this->sql_original = $sql;
			$this->save_SESSION();	
			$this->make_filtros();
			$this->retrieve();	
		}else{ 
			$this->checkbox_ventas = 0;
			$this->checkbox_arriendo = 0;
			parent::procesa_event();
			
		}
	}
}
?>
/*function select_1_producto(valores, record) {
	// para BODEGA se debe usar el precio INTERNO
	var ajax = nuevoAjax();
	var vl_cod_producto_value = URLEncode(valores[1]);
	ajax.open("GET", "BODEGA/ajax_producto_precio.php?cod_producto="+vl_cod_producto_value, false);
	ajax.send(null);		

	var resp = URLDecode(ajax.responseText);
	valores[3] = resp; 
	set_values_producto(valores, record);
}*/
function valida_ct_x_facturar(ve_campo) {
	// SE REEMPLZA LA ORIGINAL PARA ADICIONAR QUE VALIDE EL STOCK
	 
	// valida solo si la GD es creada desde
	var cod_doc = to_num(document.getElementById('COD_DOC_0').innerHTML);
	
	if (cod_doc != 0){
		var vl_error = false;
		var record = get_num_rec_field(ve_campo.id);
		var cant_ingresada = to_num(ve_campo.value);
		var cant_por_facturar = to_num(document.getElementById('CANTIDAD_POR_FACTURAR_' + record).innerHTML);
		if (parseFloat(cant_por_facturar) < parseFloat(cant_ingresada)) {
			alert('El valor ingresado no puede ser mayor que la cantidad "por Facturar": '+ number_format(cant_por_facturar, 1, ',', '.'));
			cant_ingresada = cant_por_facturar;
			vl_error = true;
		}

		var ajax = nuevoAjax();
		var vl_cod_producto_value = document.getElementById('COD_PRODUCTO_' + record).value;
		ajax.open("GET", "BODEGA/ajax_producto_stock.php?cod_producto="+vl_cod_producto_value, false);
		ajax.send(null);		

		var vl_stock = ajax.responseText;
		if (parseFloat(vl_stock) < parseFloat(cant_ingresada)) {
			alert('El valor ingresado no puede ser mayor que el stock actual: '+ number_format(vl_stock, 1, ',', '.'));
			cant_ingresada = vl_stock;
			vl_error = true;
		}


		if (vl_error)
			return cant_ingresada;
		else
			return ve_campo.value;
	}
	else
		return ve_campo.value;
}
function select_1_empresa(valores, record) {
	if(valores[1] != '29'){
		set_values_empresa(valores, record);
	}else{
		alert('Usted no puede generar una factura para: COMERCIAL BIGGI CHILE S.A.\n\nFavor aseg�rese de indicar el cliente correcto de esta factura');
		set_value('COD_EMPRESA_' + record, '', '');
		set_value('RUT_' + record, '', '');
		set_value('ALIAS_' + record, '', '');
		set_value('NOM_EMPRESA_' + record, '', '');
		set_value('DIG_VERIF_' + record, '', '');
		set_value('DIRECCION_FACTURA_' + record, '', '');
		set_value('DIRECCION_DESPACHO_' + record, '', '');
		set_value('GIRO_' + record, '', '');
		set_value('SUJETO_A_APROBACION_' + record, '', '');
		set_drop_down_vacio('COD_SUCURSAL_FACTURA_' + record);
		set_drop_down_vacio('COD_SUCURSAL_DESPACHO_' + record);
		set_drop_down_vacio('COD_PERSONA_' + record);
		set_value('MAIL_CARGO_PERSONA_' + record, '', '');
		set_value('COD_CUENTA_CORRIENTE_' + record, '', '');
		set_value('NOM_CUENTA_CORRIENTE_' + record, '', '');
		set_value('NRO_CUENTA_CORRIENTE_' + record, '', '');
	}
}
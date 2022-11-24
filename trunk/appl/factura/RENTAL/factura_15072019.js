function add_line_ref(ve_tabla_id, ve_nom_tabla){
	var vl_row = add_line(ve_tabla_id, ve_nom_tabla);
	return vl_row;
}

function valida_referencias(ve_control){
	var vl_record = get_num_rec_field(ve_control.id);
	var vl_hem = document.getElementById('REFERENCIA_HEM_0').value;
	var vl_hes = document.getElementById('REFERENCIA_HES_0').value;
	var count1 = 0;
	var count2 = 0;
	var count_cto = 0;
	var count_m_cto = 0;

	var aTR = get_TR('REFERENCIAS');
	for(i = 0; i < aTR.length ; i++){
	 	var vl_rec = get_num_rec_field(aTR[i].id);
	 	
	 	if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 1)//HEM
	 		count1++;
	 	else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 2)//HES
	 		count2++;
	 	else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 3){//CONTACTO
	 		count_cto++;
	 	}else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 4){//CORREO CONTACTO
	 		count_m_cto++;
	 		
	 		if(count_m_cto == 1){
		 		var theElement = document.getElementById('DOC_REFERENCIA_'+vl_rec);
		 		validate_mail(theElement);
			}
		}	
	}
	
	if(count1 > 1 || count2 > 1){
		alert('No debe ingresar mas de un tipo sea HEM o HES');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	
	if(vl_hem == 'S' && count2 > 0){
		alert('Esta empresa tiene como referencia HEM, no puede agregar referencias de tipo HES');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	
	if(vl_hes == 'S' && count1 > 0){
		alert('Esta empresa tiene como referencia HES, no puede agregar referencias de tipo HEM');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	
	if(count_cto > 1){
		alert('No debe ingresar mas de un tipo sea Contacto');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_record).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	if(count_m_cto > 1){
		alert('No debe ingresar mas de un correo de contacto');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_record).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
}

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
		alert('Usted no puede generar una factura para: COMERCIAL BIGGI CHILE S.A.\n\nFavor asegúrese de indicar el cliente correcto de esta factura');
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

$(document).ready(function () {
	$('#NRO_ORDEN_COMPRA_0').live('input', function (e) {
	    if (!/^[ a-z0-9áéíóúüñ-]*$/i.test(this.value)) {
	        this.value = this.value.replace(/[^ a-z0-9áéíóúüñ-]+/ig,"");
	    }
	});
});
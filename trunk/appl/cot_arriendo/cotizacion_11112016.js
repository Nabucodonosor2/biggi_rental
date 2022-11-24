function dlg_print() {
	var tipo_dispositivo = document.getElementById('TIPO_DISPOSITIVO_0').innerHTML;
	if(tipo_dispositivo == 'IPAD'){
    	alert('Resumen Cotizacion');
    }else{
	var args = "location:no;dialogLeft:400px;dialogTop:300px;dialogWidth:460px;dialogHeight:235px;dialogLocation:0;Toolbar:no;";
	var returnVal = null;
	
	returnVal = window.showModalDialog("dlg_print_cotizacion.php?cod_cot_arriendo="+document.getElementById('COD_COT_ARRIENDO_H_0').value, "_blank", args);
	
 	if (returnVal == null)
 		return false;
	else {
		document.getElementById('wi_hidden').value = returnVal;
		document.input.submit();
   		return true;
		}
	}
}

function validate() {
		
	return validate_cot_nv('ITEM_COT_ARRIENDO');
}

function add_line_item(tabla_id, nom_tabla) {
	var row = add_line(tabla_id, nom_tabla);
	var aTR = get_TR('ITEM_COT_ARRIENDO');
	var item = 1;
	var letra = '';
	for (var i=aTR.length - 2; i >=0; i--, item++) {
		var cod_producto_value = document.getElementById('COD_PRODUCTO_' + get_num_rec_field(aTR[i].id)).value
		if (cod_producto_value=='T') {
			letra = document.getElementById('ITEM_' + get_num_rec_field(aTR[i].id)).value; 
			break;
		}
	}	
	document.getElementById('ITEM_' + row).value = letra + item;
}

function get_precio_compra(ve_cod_proveedor) {
	var sc_precio_compra_value = ve_cod_proveedor.options[ve_cod_proveedor.selectedIndex].label; 
	var record = get_num_rec_field(ve_cod_proveedor.id);
	set_value('PRECIO_COMPRA_' + record, sc_precio_compra_value, number_format(sc_precio_compra_value, 0, ',', '.'));
}

function mostrarOcultar(ve_cod_forma_pago) {
	var cod_forma_pago = ve_cod_forma_pago.options[ve_cod_forma_pago.selectedIndex].value; 	
	if (parseFloat(cod_forma_pago) == 1){
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').type='text';
		document.getElementById('NOM_FORMA_PAGO_OTRO_0').setAttribute('onblur', "this.style.border=''");				
		document.getElementById('NOM_FORMA_PAGO_OTRO_0').setAttribute('onfocus', "this.style.border='1px solid #FF0000'");				
    }
    else{
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').type='hidden';
    }
}
function select_1_producto(valores, record) {
	/* Se reimplementa esta fucnion, para completa el precio de arrindo 
	 */
	set_value('COD_PRODUCTO_' + record, valores[1], valores[1]);
	set_value('NOM_PRODUCTO_' + record, valores[2], valores[2]);
	set_value('PRECIO_' + record, valores[3], valores[3]);
	set_value('PRECIO_H_' + record, to_num(valores[3]), to_num(valores[3]));

	var vl_porc_arr = document.getElementById('PORC_ARRIENDO_0').value;
	var vl_precio = roundNumber(to_num(valores[3]) * to_num(vl_porc_arr) /100, 0);	 
	set_value('PRECIO_ARRIENDO_' + record, vl_precio, number_format(vl_precio, 0, ',', '.'));
	set_value('PRECIO_ARRIENDO_H_' + record, vl_precio, vl_precio);
}
function valida_porc_arriendo(ve_porc_arriendo) {
	var vl_porc_min = document.getElementById('MIN_PORC_ARRIENDO_0').value;
	var vl_porc_max = document.getElementById('MAX_PORC_ARRIENDO_0').value;
	
	if (parseFloat(ve_porc_arriendo.value) < parseFloat(vl_porc_min)) {
		alert("El porcentaje mínimo es "+vl_porc_min);
		ve_porc_arriendo.value = vl_porc_min;
	}
	else if (parseFloat(ve_porc_arriendo.value) > parseFloat(vl_porc_max)) {
		alert("El porcentaje máximo es "+vl_porc_max);
		ve_porc_arriendo.value = vl_porc_max;
	}

	// vuelve a calcular todas las lineas	
	var aTR = get_TR('ITEM_COT_ARRIENDO');
	var vl_suma = 0;
	for (i=0; i < aTR.length; i++) {
		var vl_rec = get_num_rec_field(aTR[i].id);
		var vl_precio_venta = document.getElementById('PRECIO_' + vl_rec).innerHTML;
		var vl_precio = roundNumber(to_num(vl_precio_venta) * to_num(ve_porc_arriendo.value) /100, 0);	 
		set_value('PRECIO_ARRIENDO_' + vl_rec, vl_precio, number_format(vl_precio, 0, ',', '.'));
		set_value('PRECIO_ARRIENDO_H_' + vl_rec, vl_precio, number_format(vl_precio, 0, ',', '.'));
		recalc_computed_relacionados(vl_rec, 'PRECIO_ARRIENDO');
	}
}
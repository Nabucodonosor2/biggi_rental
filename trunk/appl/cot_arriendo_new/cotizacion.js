function dlg_print() {
	var tipo_dispositivo = document.getElementById('TIPO_DISPOSITIVO_0').innerHTML;
	if(tipo_dispositivo == 'IPAD'){
    	alert('Resumen Cotizacion');
    }else{
		var url = "dlg_print_cotizacion.php?cod_cot_arriendo="+document.getElementById('COD_COT_ARRIENDO_H_0').value;
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 290,
			 width: 470,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	if (returnVal == null){		
					return false;
				}			
				else {
					var input = document.createElement("input");
					input.setAttribute("type", "hidden");
					input.setAttribute("name", "b_print_x");
					input.setAttribute("id", "b_print_x");
					document.getElementById("input").appendChild(input);
					
					document.getElementById('wi_hidden').value = returnVal;
					document.input.submit();
			   		return true;
				}
			}
		});	
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
	var sc_precio_compra_value = ve_cod_proveedor.options[ve_cod_proveedor.selectedIndex].dataset.dropdown; 
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
	 
	set_value('COD_PRODUCTO_' + record, valores[1], valores[1]);
	set_value('NOM_PRODUCTO_' + record, valores[2], valores[2]);
	set_value('PRECIO_' + record, valores[3], valores[3]);
	set_value('PRECIO_H_' + record, to_num(valores[3]), to_num(valores[3]));

	var vl_porc_arr = document.getElementById('PORC_ARRIENDO_0').value;
	var vl_precio = roundNumber(to_num(valores[3]) * to_num(vl_porc_arr) /100, 0);	 
	set_value('PRECIO_ARRIENDO_' + record, vl_precio, number_format(vl_precio, 0, ',', '.'));
	set_value('PRECIO_ARRIENDO_H_' + record, vl_precio, vl_precio);*/
	
/*	 Se reimplementa esta fucnion, para completa el precio de arrindo */
	 
	var ajax = nuevoAjax();
	ajax.open("GET", "../common_appl/ajax_precio_arriendo.php?cod_producto="+valores[1], false);
	ajax.send(null);

	var vl_resp = ajax.responseText;
	var resp = vl_resp.split('|');
	var precio_arriendo = resp[0];
	
	set_value('COD_PRODUCTO_' + record, valores[1], valores[1]);
	set_value('NOM_PRODUCTO_' + record, valores[2], valores[2]);
	set_value('PRECIO_' + record, precio_arriendo, precio_arriendo);
	set_value('PRECIO_H_' + record, to_num(precio_arriendo), to_num(precio_arriendo));

	var vl_porc_arr = document.getElementById('PORC_ARRIENDO_0').value;
	
	var vl_precio = roundNumber(to_num(precio_arriendo) * to_num(vl_porc_arr) /100, 0);	
	
	set_value('PRECIO_ARRIENDO_' + record, vl_precio, number_format(vl_precio, 0, ',', '.'));
	set_value('PRECIO_ARRIENDO_H_' + record, 100, vl_precio);
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
function request_crear_desde(ve_prompt,ve_valor) {
	var url = "../../../../commonlib/trunk/php/request.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor);
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 200,
		 width: 380,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	
		 	if (returnVal == null){		
				return false;
			}			
			else {
				var ajax = nuevoAjax();
				ajax.open("GET", "ajax_que_precio_usa.php?regreso="+returnVal, false);
				ajax.send(null);
	
				var vl_resp = ajax.responseText;
				var resp = vl_resp.split('|');
				var cod_cotizacion = resp[0];
				var cambio_precio = resp[1]; 
				
				if(cambio_precio == 'SI'){
					
					var url = "../cot_arriendo/que_precio_usa.php?cod_cot_arriendo="+cod_cotizacion;
					$.showModalDialog({
						 url: url,
						 dialogArguments: '',
						 height: 330,
						 width: 710,
						 scrollable: false,
						 onClose: function(){ 
						 	var returnVal2 = this.returnValue;
						 	if (returnVal2=='1'){
						 		var ajax = nuevoAjax();
								ajax.open("GET", "../common_appl/setear_sesion.php?", false);
								ajax.send(null);
						 	}
						 	var input = document.createElement("input");
							input.setAttribute("type", "hidden");
							input.setAttribute("name", "b_create_x");
							input.setAttribute("id", "b_create_x");
							document.getElementById("output").appendChild(input);
							
							document.getElementById('wo_hidden').value = returnVal;
							document.output.submit();
					   		return true;
						}
					});	
					
					
				}else{
					var input = document.createElement("input");
					input.setAttribute("type", "hidden");
					input.setAttribute("name", "b_create_x");
					input.setAttribute("id", "b_create_x");
					document.getElementById("output").appendChild(input);
					
					document.getElementById('wo_hidden').value = returnVal;
					document.output.submit();
			   		return true;
			   	}	
			}
		}
	});	
}	

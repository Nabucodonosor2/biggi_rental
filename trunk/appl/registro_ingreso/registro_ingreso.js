function calcula_costeo() {
	//TOTAL FOB    'TOTAL_FOB', "[TOTAL_EX_FCA_H] + [EMBALAJE] + [FLETE_INTERNO] + [OTROS1]"
	var vl_total_ex_fca = document.getElementById('TOTAL_EX_FCA_TEF_H_0').value;
	if (vl_total_ex_fca=='') 
		vl_total_ex_fca = 0;
	else
		vl_total_ex_fca = vl_total_ex_fca.replace(",",".");
	
	var vl_embalaje = document.getElementById('EMBALAJE_0').value;
	if (vl_embalaje=='') 
		vl_embalaje = 0;
	else
		vl_embalaje = vl_embalaje.replace(",",".");

	var vl_flete_interno = document.getElementById('FLETE_INTERNO_0').value;
	if (vl_flete_interno=='') 
		vl_flete_interno = 0;
	else
		vl_flete_interno = vl_flete_interno.replace(",",".");
	
	var vl_otros_fob = document.getElementById('OTROS1_0').value;
	if (vl_otros_fob=='') 
		vl_otros_fob = 0;
	else
		vl_otros_fob = vl_otros_fob.replace(",",".");
	
	total_fob = parseFloat(vl_total_ex_fca) + parseFloat(vl_embalaje) + parseFloat(vl_flete_interno) + parseFloat(vl_otros_fob);

	document.getElementById('TOTAL_FOB_0').innerHTML = number_format(total_fob, 2, ',', '.');
	document.getElementById('TOTAL_FOB_H_0').value = roundNumber(total_fob, 2);  
    
    
    //TOTAL CIF  'TOTAL_CIF_H', "[TOTAL_FOB] + [FLETE] + [SEGURO] + [FLETE_SCL]"
	var vl_total_fob = document.getElementById('TOTAL_FOB_H_0').value;
	vl_total_fob = vl_total_fob.replace(",",".");
	
	var vl_flete = document.getElementById('FLETE_0').value;
	if (vl_flete=='') 
		vl_flete = 0;
	else
		vl_flete = vl_flete.replace(",",".");
	
	var vl_seguro = document.getElementById('SEGURO_0').value;
	if (vl_seguro=='')
		vl_seguro = 0;
	else
		vl_seguro = vl_seguro.replace(",",".");
	
	var vl_flete_scl = document.getElementById('FLETE_SCL_0').value;
	if (vl_flete_scl=='')
		vl_flete_scl = 0;
	else
		vl_flete_scl = vl_flete_scl.replace(",",".");
	
	total_cif = parseFloat(vl_total_fob) + parseFloat(vl_flete) + parseFloat(vl_seguro) + parseFloat(vl_flete_scl);
	
	document.getElementById('TOTAL_CIF_0').innerHTML = number_format(total_cif, 2, ',', '.');
	document.getElementById('TOTAL_CIF_H_0').value = total_cif;
    
    //TOTAL CIF PESOS   'TOTAL_CIF_PESOS_H', "([TOTAL_FOB] + [FLETE] + [SEGURO] + [FLETE_SCL]) * [VALOR_DOLAR_H]"
	var vl_total_cif = document.getElementById('TOTAL_CIF_H_0').value;
	vl_total_cif = vl_total_cif.replace(",",".");
	var vl_dolar = document.getElementById('VALOR_DOLAR_H_0').value;
	
	
	total_cif_pesos = parseFloat(vl_total_cif) * parseFloat(vl_dolar);
	total_cif_pesos = roundNumber(total_cif_pesos, 0);
	
	document.getElementById('TOTAL_CIF_PESOS_0').innerHTML = number_format(total_cif_pesos, 0, ',', '.');
	document.getElementById('TOTAL_CIF_PESOS_H_0').value = total_cif_pesos;
	
	//('TOTAL_OTROS', "[GRUA] + [PERMISO_MUNI] + [DESCONSOLIDACION] + [GASTO_ORDEN_PAGO] + [CARTA_CREDITO] + [ALMACENAJE] + [OTROS]");
	var vl_grua = document.getElementById('GRUA_0').value;
	if (vl_grua=='')
		vl_grua = 0;
	
	var vl_permiso_muni = 0;
	
	var vl_desconsolid = document.getElementById('DESCONSOLIDACION_0').value;
	if (vl_desconsolid=='')
		vl_desconsolid = 0;
	
	var vl_gasto_orden = document.getElementById('GASTO_ORDEN_PAGO_0').value;
	if (vl_gasto_orden=='')
		vl_gasto_orden = 0;
	
	var vl_carta_credito = document.getElementById('CARTA_CREDITO_0').value;
	if (vl_carta_credito=='')
		vl_carta_credito = 0;
	
	var vl_almacenaje = document.getElementById('ALMACENAJE_0').value;
	if (vl_almacenaje=='')
		vl_almacenaje = 0;
	
	var vl_otros = document.getElementById('OTROS_0').value;
	if (vl_otros=='')
		vl_otros = 0;
	
	total_otros = parseFloat(vl_grua) + parseFloat(vl_desconsolid) + parseFloat(vl_gasto_orden) + parseFloat(vl_carta_credito)+ parseFloat(vl_almacenaje)+ parseFloat(vl_otros);
	
	document.getElementById('TOTAL_OTROS_0').innerHTML = number_format(total_otros, 0, ',', '.');
	document.getElementById('TOTAL_OTROS_H_0').value = total_otros;
	//TOTAL OTROS DTD
	document.getElementById('TOTAL_OTROS_DTD_0').innerHTML = number_format(total_otros, 0, ',', '.');
	document.getElementById('TOTAL_OTROS_DTD_H_0').value = total_otros;  
	
	//TOTAL_DTD', "[TOTAL_CIF_PESOS_H] +[TOTAL_OTROS_DTD] + [AD_VALOREM] + [AGENTE_ADUANA] + [FLETE_CHILE] 
	var vl_total_cif_h = document.getElementById('TOTAL_CIF_PESOS_H_0').value;
	vl_total_cif_h = vl_total_cif_h.replace(",",".");
	
	var vl_total_otros = document.getElementById('TOTAL_OTROS_DTD_H_0').value;
	vl_total_otros = vl_total_otros.replace(",",".");
	
	var vl_flete_chile = document.getElementById('FLETE_CHILE_0').value;
	if (vl_flete_chile=='')
		vl_flete_chile = 0;
	else
		vl_flete_chile = vl_flete_chile.replace(",",".");
	
	var vl_agente_aduana = document.getElementById('AGENTE_ADUANA_0').value;
	if (vl_agente_aduana=='')
		vl_agente_aduana = 0;
	
	var vl_ad_valorem = document.getElementById('AD_VALOREM_0').value;
	if (vl_ad_valorem=='')
		vl_ad_valorem = 0;
	
	total_dtd = parseFloat(vl_total_cif_h) + parseFloat(vl_total_otros)+ parseFloat(vl_flete_chile) + parseFloat(vl_agente_aduana)+ parseFloat(vl_ad_valorem);
	
	document.getElementById('TOTAL_DTD_0').innerHTML = number_format(total_dtd, 0, ',', '.');
	document.getElementById('TOTAL_DTD_H_0').value = total_dtd;
	//TOTAL GASTOS IMP
	document.getElementById('TOTAL_GASTOS_0').innerHTML = number_format(total_dtd, 0, ',', '.');
	document.getElementById('TOTAL_GASTOS_H_0').value = total_dtd;  

	//'TOTAL_GASTO_US', "([TOTAL_CIF_PESOS_H] + [AD_VALOREM] + [AGENTE_ADUANA] + [FLETE_CHILE] +[TOTAL_OTROS_DTD]) / [DOLAR_H]",2);
	var vl_total_gastos = document.getElementById('TOTAL_GASTOS_H_0').value;
	vl_total_gastos = vl_total_gastos.replace(",",".");
	var vl_dolar = document.getElementById('VALOR_DOLAR_H_0').value;
	
	total_gastos_us = parseFloat(vl_total_gastos) / parseFloat(vl_dolar);
	document.getElementById('TOTAL_GASTOS_US_0').innerHTML = number_format(total_gastos_us, 2, ',', '.');
	document.getElementById('TOTAL_GASTOS_US_H_0').value = roundNumber(total_gastos_us, 2);   

	//FACTOR_IMP = TOTAL_GASTO_US / TOTAL_EXFCA
	var vl_factor_imp = parseFloat(total_gastos_us) / parseFloat(vl_total_ex_fca);
	document.getElementById('FACTOR_IMP_0').innerHTML = number_format(vl_factor_imp, 2, ',', '.');
	document.getElementById('FACTOR_IMP_H_0').value = roundNumber(vl_factor_imp, 2);   
}

function total_item_exfca(item) {
 var aTR = get_TR('ITEM_REGISTRO_INGRESO');
 		// vuelve a calcular todas las lineas	
	vl_total_total = 0;
	for (i=0; i < aTR.length; i++) {
		
		var precio = document.getElementById('PRECIO_' + get_num_rec_field(aTR[i].id)).value;
		
		precio = precio.replace(",",".");
		if(precio == '0,00'){
			return false;
		}
		var cantidad = document.getElementById('CANTIDAD_' + get_num_rec_field(aTR[i].id)).value;
		cantidad = cantidad.replace(",",".");
		
		vl_cant_precio = parseFloat(precio) * parseFloat(cantidad);
		vl_total_total = parseFloat(vl_cant_precio) + parseFloat(vl_total_total);
		vl_total_total_h = vl_total_total;
	}
	document.getElementById('SUBTOTAL_EX_FCA_H_0').value = vl_total_total;
	vl_total_total = number_format(vl_total_total, 2, ',', '.');
	var vl_descto_ex_fca = document.getElementById('DESCTO_EX_FCA_0').value;
	
	document.getElementById('SUBTOTAL_EX_FCA_0').innerHTML = vl_total_total; //vl_total_total_h;
   	item_porc_desc();
}
function porc_valorem(tipo){
	
	var total_cif_pesos = document.getElementById('TOTAL_CIF_PESOS_H_0').value;
	total_cif_pesos = total_cif_pesos.replace(".","");
 	total_cif_pesos = to_num(total_cif_pesos);
	
	var valorem_porc = document.getElementById('AD_VALOREM_PORC_0').value;
	if (valorem_porc=='')
		valorem_porc = 0;
	else
		valorem_porc = valorem_porc.replace(",",".");//valorem_porc
	
	if(tipo ==  'porc'){
		var total_valorem = parseFloat(total_cif_pesos) * parseFloat(valorem_porc);
		total_valorem = total_valorem / 100;
	
		document.getElementById('AD_VALOREM_0').value = total_valorem;
	}else{
		var ad_valorem = document.getElementById('AD_VALOREM_0').value;
		if (ad_valorem=='')
			ad_valorem = 0;
		else
			ad_valorem = ad_valorem.replace(",",".");
		total_valorem_porc = ad_valorem * 100;
		total_valorem_porc = total_valorem_porc / total_cif_pesos;
		total_valorem_porc= number_format(total_valorem_porc, 2, ',', '.');
		document.getElementById('AD_VALOREM_PORC_0').value = total_valorem_porc;
	}
}

function porc_agente_aduana(tipo){
	
	var total_cif_pesos = document.getElementById('TOTAL_CIF_PESOS_H_0').value;
	total_cif_pesos = total_cif_pesos.replace(".","");
 	total_cif_pesos = to_num(total_cif_pesos);
	
	var vl_porc = document.getElementById('AGENTE_ADUANA_POR_0').value;
	if (vl_porc=='')
		vl_porc = 0;
	else
		vl_porc = vl_porc.replace(",",".");
	
	if(tipo ==  'porc'){
		var vl_total = parseFloat(total_cif_pesos) * parseFloat(vl_porc);
		vl_total = vl_total / 100;
	
		document.getElementById('AGENTE_ADUANA_0').value = vl_total;
	}else{
		var vl_total = document.getElementById('AGENTE_ADUANA_0').value;
		if (vl_total=='')
			vl_total = 0;
		else
			vl_total = vl_total.replace(",",".");
		vl__porc = vl_total * 100;
		vl__porc = vl_porc / total_cif_pesos;
		vl_porc= number_format(vl_porc, 2, ',', '.');
		document.getElementById('AGENTE_ADUANA_POR_0').value = vl_porc;
	}
}

function item_porc_desc() {
	var vl_subtotal_ex_fca = document.getElementById('SUBTOTAL_EX_FCA_H_0').value;
	vl_subtotal_ex_fca = vl_subtotal_ex_fca.replace(",",".");//vl_subtotal_ex_fca
	
	var vl_subtotal_ex_fca_0 = document.getElementById('SUBTOTAL_EX_FCA_0').innerHTML;
	
	var vl_descto_ex_fca = document.getElementById('DESCTO_EX_FCA_0').value;
	if (vl_descto_ex_fca=='') 
		vl_descto_ex_fca = 0;
	
	vl_subtotal_ex_fca_0 = vl_subtotal_ex_fca_0.replace(".","");//vl_subtotal_ex_fca
	vl_subtotal_ex_fca_0 = vl_subtotal_ex_fca_0.replace(",",".");//vl_subtotal_ex_fca
	vl_subtotal_ex_fca_0 = number_format(vl_subtotal_ex_fca_0, 2, ',', '.');
	
	vl_sub_x_desc = parseFloat(vl_subtotal_ex_fca) - parseFloat(vl_descto_ex_fca);
	vl_total_ex_fca = vl_sub_x_desc;
	vl_total_ex_fca_h = vl_total_ex_fca;

	vl_total_ex_fca= number_format(vl_total_ex_fca, 2, ',', '.');  
		
	document.getElementById('SUBTOTAL_EX_FCA_H_0').value = vl_subtotal_ex_fca;
	document.getElementById('TOTAL_EX_FCA_0').innerHTML = vl_total_ex_fca;
	document.getElementById('TOTAL_EX_FCA_H_0').innerHTML = vl_total_ex_fca;
	document.getElementById('TOTAL_EX_FCA_TEF_H_0').value = vl_total_ex_fca_h;
	
	calcula_costeo();
}
function add_line_item(tabla_id, nom_tabla) {
	var row = add_line(tabla_id, nom_tabla);
	var aTR = get_TR('ITEM_REGISTRO_INGRESO');
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
function precio_dolar(cod_producto) {
	
	var vl_record = get_num_rec_field(cod_producto.id);
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_precio_item_registro_ingreso.php?cod_producto="+URLEncode(cod_producto.value), false);
	ajax.send(null);
	var resp = ajax.responseText;
	document.getElementById('PRECIO_' + vl_record).innerHTML = resp;
	
}
function help_producto(campo, num_dec) {
	var campo_id = campo.id;
	var field = get_nom_field(campo_id);
	var record = get_num_rec_field(campo_id);

	var cod_producto = document.getElementById('COD_PRODUCTO_' + record); 
	var nom_producto = document.getElementById('NOM_PRODUCTO_' + record); 
	var precio = document.getElementById('PRECIO_' + record);
	var precio_h = document.getElementById('PRECIO_H_' + record);

	cod_producto.value = cod_producto.value.toUpperCase();
	var cod_producto_value = nom_producto_value = '';
	switch (field) {
	case 'COD_PRODUCTO': if (cod_producto.value=='TE') {
   							ingreso_TE(cod_producto);
   							return;
   						}
   						var boton_precio = document.getElementById('BOTON_PRECIO_' + record);
   						if (boton_precio)
   							boton_precio.value =  'Precio';
   						cod_producto_value = campo.value;	
   						break;
	case 'NOM_PRODUCTO': if (cod_producto.value=='T' || cod_producto.value=='TE') return;   											
   						nom_producto_value = campo.value;	
   						break;
	}
	var ajax = nuevoAjax();
	cod_producto_value = URLEncode(cod_producto_value);
	nom_producto_value = URLEncode(nom_producto_value);
	ajax.open("GET", "help_producto.php?cod_producto="+cod_producto_value+"&nom_producto="+nom_producto_value, false);
	ajax.send(null);		

	var resp = URLDecode(ajax.responseText);
	var lista = resp.split('|');
	switch (lista[0]) {
  	case '0':	
				alert('El producto no existe, favor ingrese nuevamente');
			cod_producto.value = nom_producto.value = precio.innerHTML = ''; 
			campo.focus();
	   	break;
  	case '1': 				
  		select_1_producto(lista, record);
	   	break;
  	default:
		var args = "dialogLeft:100px;dialogTop:300px;dialogWidth:650px;dialogHeight:200px;dialogLocation:0;Toolbar:'yes';";
 			var returnVal = window.showModalDialog("../../../../commonlib/trunk/php/help_lista_producto.php?sql="+URLEncode(lista[1]), "_blank", args);
	   	if (returnVal == null) {
 				alert('El producto no existe, favor ingrese nuevamente');
				cod_producto.value = nom_producto.value = precio.innerHTML = ''; 
				campo.focus();
			}
			else {
				returnVal = URLDecode(returnVal);
			   	var valores = returnVal.split('|');
		  		select_1_producto(valores, record);
			}
			break;
	}
	// reclacula los computed que usan precio
	if (precio_h) {
		
		precio_h.value = findAndReplace(precio.innerHTML, '.', '');	// borra los puntos en los miles
		precio_h.value = findAndReplace(precio_h.value, ',', '.');	// cambia coma decimal por punto
	}
	
	recalc_computed_relacionados(record, 'PRECIO');
	
	var cantidad = document.getElementById('CANTIDAD_' + record);
	if (cantidad)
		cantidad.setAttribute('type', "text");				
	var item = document.getElementById('ITEM_' + record);
	if (item)
		item.setAttribute('type', "text");				
	var boton_precio = document.getElementById('BOTON_PRECIO_' + record);
	if (boton_precio)	
		boton_precio.removeAttribute('disabled');
	nom_producto.removeAttribute('disabled');
	if (cod_producto.value=='T') {
		document.getElementById('NOM_PRODUCTO_' + record).select();
		if (cantidad) {
			cantidad.setAttribute('type', "hidden");
			cantidad.value = 1;
		}		
		if (item) {
			var aTR = get_TR('ITEM_COTIZACION');
			for (var i=0; i<aTR.length; i++) {
				if (get_num_rec_field(aTR[i].id)==record)
					break;
			}
			var letra = 'A'.charCodeAt(0);
			for (i=i-1; i >=0; i--) {
				var cod_producto_value = document.getElementById('COD_PRODUCTO_' + get_num_rec_field(aTR[i].id)).value;
				if (cod_producto_value=='T') {
					letra = document.getElementById('ITEM_' + get_num_rec_field(aTR[i].id)).value.charCodeAt(0);
					if (letra >= 'A'.charCodeAt(0) && letra<='Z'.charCodeAt(0))
						letra++;
					else
						letra = 'A'.charCodeAt(0);
					break;
				}
			}	
			item.value = String.fromCharCode(letra);
		}
		if (boton_precio)	
			boton_precio.setAttribute('disabled', "");				
	}
	else if (cod_producto.value!='')
		if (cantidad)
			cantidad.focus();
		
	var cod_producto_old = document.getElementById('COD_PRODUCTO_OLD_' + record);
	if (cod_producto_old)
		cod_producto_old.value = cod_producto.value;  
	
}
function del_line_standard(tr_id, nom_tabla) {
	var tr = document.getElementById(tr_id); 
	var label_record = get_nom_field(tr_id);
	var record = get_num_rec_field(tr_id);

	var ajax = nuevoAjax();
	ajax.open("GET", "../../../../commonlib/trunk/php/del_line.php?nom_tabla="+nom_tabla+"&label_record="+label_record+"&record="+record, false);
	ajax.send(null);	
	var resp = ajax.responseText;	// no se espera respuesta
	recalc_sum(tr);
	tr.parentNode.removeChild(tr);
	total_item_exfca();
}
function cambio_flete_seguro() {

	var flete = document.getElementById('FLETE_0').value;
	var seguro = document.getElementById('SEGURO_0').value;
	
	document.getElementById('FLETE_STATIC_0').innerHTML = flete;
	document.getElementById('SEGURO_STATIC_0').innerHTML = seguro;
}
function valor_dolar_aduanero(cod_mes,ano_actual) {
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_valor_dolar_aduanero.php?cod_mes="+URLEncode(cod_mes.value)+"&ano="+ano_actual, false);
	ajax.send(null);
	var resp = ajax.responseText;
	document.getElementById('VALOR_DOLAR_0').innerHTML = number_format(resp, 2, ',', '.');
	document.getElementById('VALOR_DOLAR_H_0').value = resp;
	
    calcula_costeo();
}
function cambia_mes(cod_mes) {
	var option = cod_mes.value;
	document.getElementById('COD_MES_0').value = option;
	document.getElementById('COD_MES1_0').value = option;
	document.getElementById('COD_MES2_0').value = option;
	document.getElementById('COD_MES3_0').value = option;
}



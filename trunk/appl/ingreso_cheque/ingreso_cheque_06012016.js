function validate(){
	var aTR = get_TR('CHEQUE');
	var vl_cod_est_ing_cheque = get_value('COD_ESTADO_INGRESO_CHEQUE_0');
	var vl_chq_depositado = false;
	
	if (aTR.length==0) {
		alert('Debe tener al menos un item de documento de pago antes de grabar.');
		return false;
	}
	
	if(vl_cod_est_ing_cheque == 3){
		for(i=0 ; i < aTR.length ; i++){
			var vl_record = get_num_rec_field(aTR[i].id);
			var vl_depositado = get_value('DEPOSITADO_'+vl_record);
			
			if(vl_depositado == 'S')
				vl_chq_depositado = true;
		}
		
		if(vl_chq_depositado){
			alert('Hay uno o mas cheque que han sido depositados, no se puede anular.');
			return false;
		}
	}
}
/////////////////
////////////// HELP DE EMPRESA
/////////////////
function set_empresa_vacio(campo) {
	var campo_id = campo.id;
	var record = get_num_rec_field(campo_id);

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
	borra_registro_cheque();
	campo.focus();
	
}
function set_values_empresa(valores, record) {
	set_value('COD_EMPRESA_' + record, valores[1], valores[1]);
	set_value('RUT_' + record, valores[2], valores[2]);
	set_value('ALIAS_' + record, valores[3], valores[3]);
	set_value('NOM_EMPRESA_' + record, valores[4], valores[4]);
	set_value('DIG_VERIF_' + record, valores[5], valores[5]);
	set_value('GIRO_' + record, valores[6], valores[6]);
	set_value('SUJETO_A_APROBACION_' + record, valores[7], valores[7]);
	set_drop_down('COD_SUCURSAL_FACTURA_' + record, valores[8]);
	set_value('DIRECCION_FACTURA_' + record, valores[9], valores[9]);
	set_drop_down('COD_SUCURSAL_DESPACHO_' + record, valores[10]);
	set_value('DIRECCION_DESPACHO_' + record, valores[11], valores[11]);
	set_drop_down('COD_PERSONA_' + record, valores[12]);
	set_value('MAIL_CARGO_PERSONA_' + record, '', '');
	set_value('COD_CUENTA_CORRIENTE_' + record, valores[13], valores[13]);
	set_value('NOM_CUENTA_CORRIENTE_' + record, valores[14], valores[14]);
	set_value('NRO_CUENTA_CORRIENTE_' + record, valores[15], valores[15]);
	
	item_registro_cheque()
}

function help_empresa(campo, tipo_empresa) {
	var campo_id = campo.id;
	var field = get_nom_field(campo_id);
	var record = get_num_rec_field(campo_id);
	var cod_empresa_value = rut_value = alias_value = nom_empresa_value = '';
	switch (field) {
	   case 'COD_EMPRESA':	cod_empresa_value = campo.value;    break;
	   case 'RUT': 					rut_value = campo.value;	break;
	   case 'ALIAS': 				alias_value = campo.value;	break;
	   case 'NOM_EMPRESA': 	nom_empresa_value = campo.value;	break;
	}
	var ajax = nuevoAjax();
	alias_value = URLEncode(alias_value);
	nom_empresa_value = URLEncode(nom_empresa_value);
	var php = "../empresa/help_empresa.php?cod_empresa="+cod_empresa_value+"&rut="+rut_value+"&alias="+alias_value+"&nom_empresa="+nom_empresa_value+"&tipo_empresa="+tipo_empresa;
	ajax.open("GET", php, true);
	ajax.onreadystatechange=function() { 
		if (ajax.readyState==4) {
			var resp = URLDecode(ajax.responseText);
			var lista = resp.split('|');
			switch (lista[0]) {
		  	case '0':	
	 				alert('La empresa no existe, favor ingrese nuevamente');
	 				set_empresa_vacio(campo);
			   	break;
		  	case '1':
		  		select_1_empresa(lista, record);
			   	break;
		  	default:
					var args = "dialogLeft:100px;dialogTop:300px;dialogWidth:650px;dialogHeight:450px;dialogLocation:0;Toolbar:'yes';";
	  			var returnVal = window.showModalDialog("../empresa/help_lista_empresa.php?sql="+URLEncode(lista[1]), "_blank", args);
			   	if (returnVal == null)
		 				set_empresa_vacio(campo);
					else {
						returnVal = URLDecode(returnVal);
				   	var valores = returnVal.split('|');
			  		select_1_empresa(valores, record);
					}
					break;
			}
		} 
	}
	ajax.send(null);	
}
function direccion_sucursal(sucursal) {
	var sucursal_id = sucursal.id;
	var field = get_nom_field(sucursal_id);
	var record = get_num_rec_field(sucursal_id);
	
	var pos = field.lastIndexOf('_');
	var tipo_sucursal = field.substr(pos, field.length - pos);		// en tipo_sucursal queda "_FACTURA" o "_DESPACHO"
	var direccion = document.getElementById('DIRECCION' + tipo_sucursal + '_' + record); 
	if (sucursal.options[sucursal.selectedIndex].value=='')
		direccion.innerHTML = '';
	else {
		var ajax = nuevoAjax();
		ajax.open("GET", "../empresa/direccion_sucursal.php?cod_sucursal="+sucursal.options[sucursal.selectedIndex].value, true);
		ajax.onreadystatechange=function() { 
			if (ajax.readyState==4) {
				var resp = ajax.responseText;
				direccion.innerHTML = URLDecode(resp);
			} 
		}
		ajax.send(null);	
	}
}
function mail_cargo_persona(ve_persona) {
	var persona_id = ve_persona.id;
	var field = get_nom_field(persona_id);
	var record = get_num_rec_field(persona_id);
	
	var mail_cargo = document.getElementById('MAIL_CARGO_PERSONA_' + record); 
	if (ve_persona.options[ve_persona.selectedIndex].value=='')
		mail_cargo.innerHTML = '';
	else {
		var ajax = nuevoAjax();
		ajax.open("GET", "../empresa/mail_cargo_persona.php?cod_persona="+ve_persona.options[ve_persona.selectedIndex].value, true);
		ajax.onreadystatechange=function() { 
			if (ajax.readyState==4) {
				var resp = ajax.responseText;
				mail_cargo.innerHTML = URLDecode(resp);
			} 
		}
		ajax.send(null);	
	}
}
function crear_cliente(ve_cod_item_menu) {
	var returnVal = add_documento('empresa', ve_cod_item_menu);
 	if (returnVal == null)
 		return false;
	else {
		var cod_empresa = document.getElementById('COD_EMPRESA_0'); 
		cod_empresa.value = returnVal; 
		help_empresa(cod_empresa, 'C');
   		return true;
	}
}
function modificar_cliente(ve_cod_item_menu) {
	var cod_empresa_value = document.getElementById('COD_EMPRESA_0').value;
	if (cod_empresa_value=='') {
		alert('Debe seleccionar un cliente');
		return false;
	}
	var returnVal = mod_documento('empresa', cod_empresa_value, ve_cod_item_menu, 'S');
 	if (returnVal == null)
 		return false;
	else {
		var cod_empresa = document.getElementById('COD_EMPRESA_0'); 
		cod_empresa.value = returnVal; 
		help_empresa(cod_empresa, 'C');
   		return true;
	}
}
function select_1_empresa(valores, record) {
/* Esta funcion se llama cuando el usuario selecciono una empresa de la lista o el dato
ingresado dio como resultado 1 empresa 

En los modulos donde es usado help_empresa, si se desea agregar un código adiconal se debe 
reimplementar esta funcion
ver ejmplo en nota_venta.js
*/
	 set_values_empresa(valores, record);
}
//realiza el llenado de los arriendos.
function item_registro_cheque(){
//elimina todos los registros.
	delete_registro_cheque();

	var vl_rut = document.getElementById('RUT_0').value;
	
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_load_registro_cheque.php?ve_rut="+vl_rut, false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);
	var result = eval("(" + resp + ")");
	if(result != ''){	
		for (i=0; i < result.length; i++) {
			var vl_row = add_line('ITEM_INGRESO_CHEQUE', 'ingreso_cheque');
			document.getElementById('CHECK_ARRIENDO_' + vl_row).Value 				= 'N';
			document.getElementById('COD_ARRIENDO_' + vl_row).innerHTML 			= result[i]['COD_ARRIENDO'];
			document.getElementById('NOM_ARRIENDO_' + vl_row).innerHTML 			= result[i]['NOM_ARRIENDO'];
			document.getElementById('NRO_ORDEN_COMPRA_' + vl_row).innerHTML 		= result[i]['NRO_ORDEN_COMPRA'];
			document.getElementById('CENTRO_COSTO_CLIENTE_' + vl_row).innerHTML 	= result[i]['CENTRO_COSTO_CLIENTE'];
			document.getElementById('NRO_MESES_' + vl_row).innerHTML 				= result[i]['NRO_MESES'];
			document.getElementById('TOTAL_CON_IVA_' + vl_row).innerHTML 			= number_format(result[i]['TOTAL_CON_IVA'], 0, '.', '.');
			
			document.getElementById('COD_ARRIENDO_H_'+vl_row).value					= result[i]['COD_ARRIENDO_H'];
			document.getElementById('NRO_MESES_H_'+vl_row).value					= result[i]['NRO_MESES_H'];
		}
	}
	//valida que tenga una por lo menos un item de contrato arriendo 
	var aTR = get_TR('ITEM_INGRESO_CHEQUE');

	if (aTR.length==0) {
		alert('La empresa ingresada no cuenta con contratos de arriendo pendientes.');
		return false;
	}
}
function delete_registro_cheque(){
	// borra todo lo anterior    
	var aTR = get_TR('ITEM_INGRESO_CHEQUE');
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		del_line('ITEM_INGRESO_CHEQUE_'+rec_tr, 'ingreso_cheque');
	}
}
//determinina el count de los meses y valida que sea la misma cifa si se vuleve a realizar el click.
function determina_meses(ve_control){
//hacer visble imagen de cargando
document.getElementById('loader').style.display='';

	/*if (ve_control.checked == true) {
		var vl_cant_cheque = document.getElementById('CANT_CHEQUE_0').innerHTML;
		if (vl_cant_cheque=='') 
			vl_cant_cheque = 0;
		var vl_rec_tr = get_num_rec_field(ve_control.id);
		var vl_nro_meses = document.getElementById('NRO_MESES_H_' + vl_rec_tr).value;
	
		if(vl_cant_cheque != 0 && vl_cant_cheque != vl_nro_meses){
			alert('La Cantidad de meses en los documentos debe ser la misma');
			ve_control.checked = false;
			ve_control.value='N';
			return;
		}else{
			ve_control.value='S';
		}	
	}*/
	suma_arriendo_total();
//hacer NO visble imagen de cargando
document.getElementById('loader').style.display='none';
}
//realiza la suma de los totales de los items.
function suma_arriendo_total(){
	var vl_cant_cheque = document.getElementById('CANT_CHEQUE_0').innerHTML;
	var aTR = get_TR('ITEM_INGRESO_CHEQUE');
	var sum_total_con_iva = 0;
	var vl_nro_meses = 0;
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		var check = document.getElementById('CHECK_ARRIENDO_' + rec_tr).checked;
		if (check == true){
			vl_nro_meses = document.getElementById('NRO_MESES_H_' + rec_tr).value;
			var total_con_iva = to_num(get_value('TOTAL_CON_IVA_' + rec_tr)); 
			sum_total_con_iva = parseInt(sum_total_con_iva)+ parseInt(total_con_iva);
		}
	}
	document.getElementById('SUM_ARRIENDO_TOTAL_0').innerHTML = number_format(sum_total_con_iva, 0, '.', '.');
	document.getElementById('CANT_CHEQUE_0').innerHTML = vl_nro_meses;
	document.getElementById('CANT_CHEQUE_H_0').value = vl_nro_meses;
	document.getElementById('CANT_CHEQUE_DOC_0').innerHTML = vl_nro_meses;
	add_documento_cheque(vl_cant_cheque != vl_nro_meses);
}
//solo si la cantidad cheque es diferente del mes ingresa, realiza delete y agrega los cheques. 
function add_documento_cheque(ve_cambio_cant_ch){
	if (ve_cambio_cant_ch) {
		delete_documento_cheque();
		
		var vl_cant_cheque = document.getElementById('CANT_CHEQUE_0').innerHTML;
		for (i=0; i < vl_cant_cheque; i++) {
			var vl_row = add_line('CHEQUE', 'ingreso_cheque');
			document.getElementById('NOM_TIPO_DOC_PAGO_'+ vl_row).innerHTML	= 'CHEQUE A FECHA';//result[i]['FECHA_DOCUMENTO_PAGO'];
			document.getElementById('COD_TIPO_DOC_PAGO_H_'+ vl_row).value	=12;// result[i]['FECHA_DOCUMENTO_PAGO'];
		}
		actualiza_fecha();
	}

	// actualiza monto	
	var vl_total_con_iva = to_num(get_value('SUM_ARRIENDO_TOTAL_0')); 
	var sum_monto_doc = 0;
	var aTR = get_TR('CHEQUE');
	for (i=0; i<aTR.length; i++){
		var vl_row = get_num_rec_field(aTR[i].id);
		document.getElementById('MONTO_DOC_'+ vl_row).value = number_format(vl_total_con_iva, 0, '.', '.');
		document.getElementById('MONTO_DOC_H_'+ vl_row).value = number_format(vl_total_con_iva, 0, '.', '.');
		sum_monto_doc = parseInt(sum_monto_doc) + parseInt(vl_total_con_iva);
	}
	document.getElementById('SUM_TOTAL_MONTO_DOC_0').innerHTML = number_format(sum_monto_doc, 0, '.', '.');
	
}
function delete_documento_cheque(){
	// borra todo lo anterior    
	var aTR = get_TR('CHEQUE');
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		del_line('CHEQUE_'+rec_tr, 'ingreso_cheque');
	}
}
function actualiza_fecha() {
	var ajax = nuevoAjax();
	var vl_cant_cheque = document.getElementById('CANT_CHEQUE_0').innerHTML;
	var vl_fecha_1er_cheque = document.getElementById('FECHA_PRIMER_CHEQUE_0').value;

	ajax.open("GET", "ajax_load_documento_cheque.php?fecha_1er_cheque="+vl_fecha_1er_cheque+"&meses="+vl_cant_cheque, false);
	ajax.send(null);
	
	var resp = URLDecode(ajax.responseText);
	var result = eval("(" + resp + ")");

	var aTR = get_TR('CHEQUE');
	for (i=0; i<aTR.length; i++){
		var vl_row = get_num_rec_field(aTR[i].id);
		document.getElementById('FECHA_DOC_'+ vl_row).value = result[i]['FECHA_DOCUMENTO_PAGO'];
		document.getElementById('FECHA_DOC_H_'+ vl_row).value = result[i]['FECHA_DOCUMENTO_PAGO'];
	}
	if (vl_fecha_1er_cheque == '')
		document.getElementById('FECHA_PRIMER_CHEQUE_0').value = result[0]['FECHA_DOCUMENTO_PAGO'];
}
//realiza el copiado del anterior banco de i hacia adelante
function cambia_banco(ve_campo){
	var record = get_num_rec_field(ve_campo.id);
	var aTR = get_TR('CHEQUE');
	for (i=0; i<aTR.length; i++){
		var record_i = get_num_rec_field(aTR[i].id);
		if (parseInt(record) < parseInt(record_i)){
			document.getElementById('COD_BANCO_'+record_i).value = document.getElementById('COD_BANCO_'+record).value;
		}
	}
}
//realiza el copiado del anterior Plaza de i hacia adelante
function cambia_plaza(ve_campo){
	var record = get_num_rec_field(ve_campo.id);
	var aTR = get_TR('CHEQUE');
	for (i=0; i<aTR.length; i++){
		var record_i = get_num_rec_field(aTR[i].id);
		if (parseInt(record) < parseInt(record_i)){
			document.getElementById('COD_PLAZA_'+record_i).value = document.getElementById('COD_PLAZA_'+record).value;
		}
	}
}
//realiza el copiado del anterior N° docuemnto de i hacia adelante sumandole 1
function cambia_nro_documento(ve_campo){
	var record = get_num_rec_field(ve_campo.id);
	var aTR = get_TR('CHEQUE');
	var nro_documento = document.getElementById('NRO_DOC_'+record).value;
	
	//si se borra el nro documento, copia este cambio a los demás registros
	if (nro_documento == ''){
		for (i=0; i<aTR.length; i++){
			var record_i = get_num_rec_field(aTR[i].id);
			if (parseInt(record) < parseInt(record_i))
				document.getElementById('NRO_DOC_'+record_i).value = '';
		}
		return;
	}
	
	var nro_documento = parseInt(nro_documento);
	for (i=0; i<aTR.length; i++){
		var record_i = get_num_rec_field(aTR[i].id);
		if (parseInt(record) < parseInt(record_i)){
			var nro_documento = nro_documento + 1;
			document.getElementById('NRO_DOC_'+record_i).value =  nro_documento;
		}
	}
}
//cuando se cambia el valor del monto, se cambia el valor del control oculto
function actualiza_monto(ve_campo){
	//alert('SUM_TOTAL_MONTO_DOC_0'+document.getElementById('SUM_TOTAL_MONTO_DOC_0').value);
	var record = get_num_rec_field(ve_campo.id);
	var monto_doc=0;
	var suma=0;
	var aTR = get_TR('CHEQUE');
	for (i=0; i<aTR.length; i++){
		var vl_row = get_num_rec_field(aTR[i].id);
		monto_doc = document.getElementById('MONTO_DOC_'+vl_row).value;
		suma=parseInt(suma)+parseInt(monto_doc);
	}
	if(suma>document.getElementById('SUM_TOTAL_MONTO_DOC_H_0').value)
		alert('La suma de los cheques es mayor al total');
	else if(suma<document.getElementById('SUM_TOTAL_MONTO_DOC_H_0').value)	
		alert('La suma de los cheques es menor al total');
		
	document.getElementById('MONTO_DOC_H_'+record).value = document.getElementById('MONTO_DOC_'+record).value;
}
function actuliza(ve_campo){
	var ajax = nuevoAjax();
	var vl_cant_cheque = document.getElementById('CANT_CHEQUE_DOC_0').innerHTML;
	var record = get_num_rec_field(ve_campo.id);
	var cheques_restantes = vl_cant_cheque - record;
	var vl_fecha_cambio = document.getElementById('FECHA_DOC_'+record).value;
	//return false;
	ajax.open("GET", "ajax_load_documento_cheque.php?fecha_1er_cheque="+vl_fecha_cambio+"&meses="+cheques_restantes, false);
	ajax.send(null);
	
	var resp = URLDecode(ajax.responseText);
	var result = eval("(" + resp + ")");
	/*Avisa que la fecha sea mayor al dia de hoy*/
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_valida_fecha.php?fecha="+vl_fecha_cambio, false);
	ajax.send(null);
	var resp_fecha = URLDecode(ajax.responseText);
	var result_fecha = eval("(" + resp_fecha + ")");
	if(result_fecha=='menor')
		alert('La fecha ingresada es menor al dia de hoy');
		
	var aTR = get_TR('CHEQUE');
	var j=0;
	var k=record;
	for (i=record; i<aTR.length; i++){
		var vl_row = get_num_rec_field(aTR[k].id);
		document.getElementById('FECHA_DOC_'+ vl_row).value = result[j]['FECHA_DOCUMENTO_PAGO'];
		document.getElementById('FECHA_DOC_H_'+ vl_row).value = result[j]['FECHA_DOCUMENTO_PAGO'];
		j++;
		k++;
	}
}
function actualiza_numero_doc(){
	var vl_cant_cheque = document.getElementById('CANT_CHEQUE_DOC_0').innerHTML;
	document.getElementById('CANT_CHEQUE_DOC_0').innerHTML=parseInt(vl_cant_cheque)+parseInt(1);
}
function saca_numero_doc(){
	var vl_cant_cheque = document.getElementById('CANT_CHEQUE_DOC_0').innerHTML;
	document.getElementById('CANT_CHEQUE_DOC_0').innerHTML=parseInt(vl_cant_cheque)-parseInt(1);
}
/////////////////
////////////// HELP DE EMPRESA
/////////////////
function set_empresa_vacio(campo) {

	var campo_id = campo.id;
	var record = get_num_rec_field(campo_id);

	set_value('COD_PROVEEDOR_EXT_4D_' + record, '', '');
	set_value('ALIAS_PROVEEDOR_EXT_' + record, '', '');
	set_value('NOM_PROVEEDOR_EXT_' + record, '', '');
	set_value('COD_CIUDAD_' + record, '', '');
	set_value('COD_PAIS_' + record, '', '');
	set_value('TELEFONO_' + record, '', '');
	set_value('FAX_' + record, '', '');
//	set_value('OBS_' + record, '', '');
	campo.focus();
}
function set_values_empresa(valores, record) {
	
	set_value('COD_PROVEEDOR_EXT_4D_' + record, valores[1], valores[1]);
	set_value('ALIAS_PROVEEDOR_EXT_' + record, valores[2], valores[2]);
	set_value('NOM_PROVEEDOR_EXT_' + record, valores[3], valores[3]);
	set_value('COD_CIUDAD_' + record, valores[4], valores[4]);
	set_value('COD_PAIS_' + record, valores[5], valores[5]);
	set_value('TELEFONO_' + record, valores[6], valores[6]);
	set_value('FAX_' + record, valores[7], valores[7]);
//	set_value('OBS_' + record, valores[8], valores[8]);
	
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
function help_empresa(campo, tipo_empresa) {

	var campo_id = campo.id;
	
	var field = get_nom_field(campo_id);
	
	var record = get_num_rec_field(campo_id);
	var cod_proveedor_value = alias_proveedor_value = nom_proveedor_value = '';
	switch (field) {
	   case 'COD_PROVEEDOR_EXT_4D':	cod_proveedor_value = campo.value;    break;
	   case 'ALIAS_PROVEEDOR_EXT': 	alias_proveedor_value = campo.value;	break;
	   case 'NOM_PROVEEDOR_EXT': 	nom_proveedor_value = campo.value;	break;
	}
	var ajax = nuevoAjax();
	alias_proveedor_value = URLEncode(alias_proveedor_value);
	nom_proveedor_value = URLEncode(nom_proveedor_value);
	var php = "help_empresa.php?cod_proveedor_ext_4d="+cod_proveedor_value+"&alias_proveedor_ext="+alias_proveedor_value+"&nom_proveedor_ext="+nom_proveedor_value;
	
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
	  			var returnVal = window.showModalDialog("help_lista_empresa.php?sql="+URLEncode(lista[1]), "_blank", args);
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
function personalizar_add_documento(returnVal)
{	
	if(returnVal == null || returnVal == '')
		return null;
   	else{
   		var cod_empresa = document.getElementById('COD_EMPRESA_0'); 
		cod_empresa.value = returnVal; 
		help_empresa(cod_empresa, 'C');
   		return true;
   	}
}
function crear_cliente(ve_cod_item_menu) {
	var returnVal = add_documento('empresa', ve_cod_item_menu);
}
function personalizar_mod_documento(returnVal){

	if(returnVal == null)
		return null;
   	else{
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
}
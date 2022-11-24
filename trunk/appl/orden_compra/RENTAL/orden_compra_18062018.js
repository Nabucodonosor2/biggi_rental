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
function validate() {
	var aTR = get_TR('ITEM_ORDEN_COMPRA');
	if (aTR.length==0) {
		alert('Debe ingresar al menos 1 item antes de grabar.');
		return false;
	}
	
	var cod_estado_doc_sii_value = get_value('COD_ESTADO_ORDEN_COMPRA_H_0'); 
	if (to_num(cod_estado_doc_sii_value) == 2){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	
	var vl_maximo_precio_oc = get_value('MAXIMO_PRECIO_OC_H_0');
	var vl_autorizada_20_proc = document.getElementById('AUTORIZADA_20_PROC_0').checked;  
	var vl_total_neto_oc = get_value('TOTAL_NETO_0'); 
	//vl_maximo_precio_oc = vl_maximo_precio_oc.replace('.',',');
	vl_maximo_precio_oc = parseInt(vl_maximo_precio_oc);
	vl_total_neto_oc = parseInt(to_num(vl_total_neto_oc));
	
	/* VMC, 29-03-2011 se deja comentado hasta que se retome esta restriccion
		esta restriccion la implemento MU antes de irnos de vacaciones y se hecho para atras porque pedia autorizar de todo
	
	if (vl_total_neto_oc > vl_maximo_precio_oc){
		if(vl_autorizada_20_proc){
			alert('La OC excede Monto Neto permitido, se debe Autorizar la OC para ser impresa.');
		}
	}
	*/
	
	return true;
}

function change_item_orden_compra(ve_valor, ve_campo) {
	var record_item_oc = get_num_rec_field(ve_valor.id);
	var item_value = document.getElementById('ITEM_' + record_item_oc).value;
	var cod_producto_old = document.getElementById('COD_PRODUCTO_OLD_' + record_item_oc);
	var cod_producto = document.getElementById('COD_PRODUCTO_' + record_item_oc);
	
	if(ve_campo == 'COD_PRODUCTO' | ve_campo == 'NOM_PRODUCTO'){
		help_producto(ve_valor, 0);
		if(cod_producto.value == 'T'){
			alert('No se pueden agregar Títulos a una Orden de Compra.');
			if(cod_producto_old.value=='T'){ //es la primera vez que se ingresa el código
				document.getElementById('COD_PRODUCTO_' + record_item_oc).value = '';
				document.getElementById('NOM_PRODUCTO_' + record_item_oc).value = '';
			}
			else{
				cod_producto.value = cod_producto_old; 
				help_producto(cod_producto, 0); 
			}	
		}
		document.getElementById('PRECIO_H_'+record_item_oc).value = document.getElementById('PRECIO_'+record_item_oc).value;
	}	
}

// funcion que despliega un tipo texto si es que el cod_doc_sii='anulada' 
function mostrarOcultar_Anula(ve_campo) {
	var vl_cod_estado_orden_compra_h = document.getElementById('COD_ESTADO_ORDEN_COMPRA_H_0');
	vl_cod_estado_orden_compra_h.value = ve_campo.value;
	
	var tr_anula = document.getElementById('tr_anula');
	
	if (to_num(ve_campo.value)==2) {
		tr_anula.style.display = ''; 
		
		document.getElementById('MOTIVO_ANULA_0').type='text';
		document.getElementById('MOTIVO_ANULA_0').setAttribute('onblur', "this.style.border=''");				
		document.getElementById('MOTIVO_ANULA_0').setAttribute('onfocus', "this.style.border='1px solid #FF0000'");	
		document.getElementById('MOTIVO_ANULA_0').focus();
	}
	else{
		document.getElementById('MOTIVO_ANULA_0').value = '';
		tr_anula.style.display = 'none'; 
	}
}

function existe_nv(cod_nv) {
	var cod_nv = document.getElementById('COD_NOTA_VENTA_0');
   	ajax = nuevoAjax();
	ajax.open("GET", "existe_nv.php?cod_nv="+cod_nv.value,false);
    ajax.send(null);	        
	var resp = ajax.responseText;
    if(resp == 'NO'){    	 
    	alert('La Nota de Venta NO Existe!!');
       	cod_nv.value = '';       	       	        	
    }else if(resp == 'EMITIDA'){    	 
    	alert('La Nota de Venta está Emitida.');
       	cod_nv.value = '';
    }else if(resp == 'CERRADA'){    	 
    	alert('La Nota de Venta está Cerrada.');
       	cod_nv.value = '';
    }else if(resp == 'CERRADA_PUEDE'){    	 
    	alert('La Nota de Venta está Cerrada.  La compra será considerada como backcharge.');
       	//cod_nv.value = '';	=> permite continuar
    }else if(resp == 'ANULADA'){    	 
    	alert('La Nota de Venta está Anulada!!');
       	cod_nv.value = ''; 
    } 
}

function select_1_producto(valores, record) {
	set_values_producto(valores, record);
	 
	var cod_producto_value = document.getElementById('COD_PRODUCTO_' + record).value;
	var cod_empresa = document.getElementById('COD_EMPRESA_0').value;
		 
	var ajax = nuevoAjax();	
    ajax.open("GET", "get_precio_proveedor.php?cod_producto="+cod_producto_value+"&cod_empresa="+cod_empresa, false);    
    ajax.send(null);    
	var resp = ajax.responseText.split('|');	
	var precio_pub = resp[0];	
	
	document.getElementById('PRECIO_'+record).value = precio_pub;
}

function change_precio(ve_precio) {
	var por_modifica_precio = parseFloat(document.getElementById('PORC_MODIFICA_PRECIO_OC_H_0').value);
	var record = get_num_rec_field(ve_precio.id);
	var cod_producto = document.getElementById('COD_PRODUCTO_' + record).value;
	if (cod_producto.toUpperCase() == 'F' | cod_producto.toUpperCase() == 'E'| cod_producto.toUpperCase() == 'I'){ //para el caso de los flete o embalaje el precio es libre
		return;
	}
		
	if(por_modifica_precio == 0.0){//se mantiene el precio que tenía
		var precio = document.getElementById('PRECIO_H_'+record).value;
		var precio_min = precio;
		var precio_max = precio;
		
		alert('Sr. usuario, su porcentaje de variación definido en los precios de compra es de un 0%.\n \n ¡Se mantendrá el precio anterior!');
		ve_precio.value = document.getElementById('PRECIO_H_'+record).value;
	}
	else{
		//obtiene el precio del proveedor
		var cod_empresa = document.getElementById('COD_EMPRESA_0').value;
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_change_precio.php?cod_producto="+URLEncode(cod_producto)+"&cod_empresa="+cod_empresa, false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var aDato = eval("(" + resp + ")");
		var precio = parseInt(aDato[0]['PRECIO']);

		var precio_min = roundNumber(precio - (precio * por_modifica_precio/100), 0);
		var precio_max = roundNumber(precio + (precio * por_modifica_precio/100), 0);
		
		if (ve_precio.value>precio_max && por_modifica_precio < 100){
			alert('Sr. usuario, su porcentaje de variación definido en los precios de compra es de un '+por_modifica_precio+'%. El monto ingresado supera el permitido, ya que el precio de compra vigente es de $'+number_format(precio, 0, ',', '.')+'.\n \n - Máximo permitido: '+number_format(precio_max, 0, ',', '.'));
			ve_precio.value = document.getElementById('PRECIO_H_'+record).value;		
		}
		else if (ve_precio.value<precio_min && por_modifica_precio < 100){
			alert('Sr. usuario, su porcentaje de variación definido en los precios de compra es de un '+por_modifica_precio+'%. El monto ingresado supera el permitido, ya que el precio de compra vigente es de $'+number_format(precio, 0, ',', '.')+'.\n \n - Mínimo permitido: '+number_format(precio_min, 0, ',', '.'));
			ve_precio.value = document.getElementById('PRECIO_H_'+record).value;		
		}
	}
}
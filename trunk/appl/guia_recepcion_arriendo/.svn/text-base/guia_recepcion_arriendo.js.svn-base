function gd_pendiente(){
	/*if(request_arriendo_validacion() == false){
		alert('false');
		return false;
	}*/
	var cod_arriendo = document.getElementById('VALOR_0').value;
	var ajax = nuevoAjax();
	var php = "../guia_recepcion_arriendo/ajax_gr_pendientes.php?cod_arriendo="+cod_arriendo;
	ajax.open("GET", php, false);
	ajax.send(null);
    var vl_resp = URLDecode(ajax.responseText);
    var vl_result = eval("(" + vl_resp + ")");
    
    
	var vl_table = document.getElementById("MOD_ARRIENDO");
	var vl_tr = document.getElementById("DW_TR_ID");
	var vl_tabla_x = vl_table.rows.length;
	 for(var i= vl_tabla_x -1; i>=0; i --){
	   vl_table.deleteRow(vl_tr);
  	}
   
   var vl_tabla = document.getElementById('MOD_ARRIENDO');
	for (var i=0; i < vl_result.length; i++) {
		var vl_tr = document.createElement("tr");
		vl_tr.className="claro";
		vl_tr.setAttribute("id","TR_"+i);
		
		var check_box = document.createElement('INPUT');
		check_box.setAttribute("type","radio");
		check_box.setAttribute("value","valor_checkbox");
		check_box.setAttribute("id","SELECCION_"+i);
		check_box.setAttribute("name","SELECCIONA");
		
		
		var vl_td_check = document.createElement("td");
		vl_td_check.width = "15%";
		vl_td_check.align = "center";
		vl_td_check.innerHTML = ''; 
		vl_tr.appendChild(vl_td_check); 
		vl_td_check.appendChild(check_box);

		var vl_td = document.createElement("td");
		vl_td.width = "20%";
		vl_td.align = "center";
		vl_td.setAttribute("id","COD_MOD_ARRIENDO_"+i);
		vl_td.innerHTML = vl_result[i]['COD_MOD_ARRIENDO'];
		vl_tr.appendChild(vl_td); 
		
		var vl_td = document.createElement("td");
		vl_td.width = "75%";
		vl_td.align = "center";
		vl_td.innerHTML = vl_result[i]['REFERENCIA'];
		vl_tr.appendChild(vl_td); 
		vl_tabla.appendChild(vl_tr); 
		
	}
	document.getElementById('MOD_ARRIENDO_DISPLAY').style.display = '';
}
function selecciona(){
vl_tabla= document.getElementById("MOD_ARRIENDO").rows.length;

	for (var i=0; i < vl_tabla; i++) {
		
		if(document.getElementById("SELECCION_"+i).checked)
		{
			cod_mod_arriendo =document.getElementById("COD_MOD_ARRIENDO_"+i).innerHTML;
		}
	}
	return cod_mod_arriendo;
	
} 

function request_arriendo(){
      	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:520px;dialogHeight:320px;dialogLocation:0;Toolbar:no;";
		var returnVal = window.showModalDialog("../guia_recepcion_arriendo/request.php", "_blank", args);
		
		var val = returnVal.split('|');
		var respuesta = val[0];
		var cod_arriendo = val[1];
		
		if(respuesta == 'NO_EXISTE'){
	    alert('El contrato de arriendo Nº '+cod_arriendo+' no existe.')
	    return false;   
	    }else if(respuesta == 'NO_CONFIRMADO'){
		alert('El contrato de arriendo Nº '+cod_arriendo+' no esta confirmado.');
		return false;		    
	    }else if(respuesta == 'PENDIENTES'){
	    alert('El contrato de arriendo Nº '+cod_arriendo+' tiene Guía(s) pendientes(s) en estado emitido. Para poder generar más guías deberá imprimir los documentos emitidos.');
		return false;
	    }else if(respuesta == 'RECEPCIONADO'){
	     alert('No existen Modificaciones de Arriendo de tipo "Eliminar" pendientes de recepcion')
	     return false;
	     }
	    if (respuesta == null || respuesta == ''){
	    	return false;		
		}else 
			document.getElementById('wo_hidden').value = returnVal;
}

function request_arriendo_validacion(){
		var cod_arriendo = document.getElementById('VALOR_0').value;
		var ajax = nuevoAjax();
		var php = "ajax_guia_recepcion_arriendo.php?cod_arriendo="+cod_arriendo;
		ajax.open("GET", php, false);
		ajax.send(null);
	    var resp = URLDecode(ajax.responseText);
	   	return resp;
}

function validate() {
	var cod_estado_doc_value = get_value('COD_ESTADO_GUIA_RECEPCION_0');
	// cod_estado_doc_sii_value = 1 = emitida
	if (to_num(cod_estado_doc_value) == 1){
		var aTR = get_TR('ITEM_GUIA_RECEPCION');
		var cant_total = 0;
		if (aTR.length==0) {
			alert('Debe ingresar al menos 1 item antes de grabar.');
			return false;
		}
		for (var i = 0; i < aTR.length; i++){
			cant_total = cant_total + parseFloat(document.getElementById('CANTIDAD_' + i).value);
		}	
		if(cant_total == '0'){
			alert('La Cantidad a Recepcionar debe ser superior a "0"');
			document.getElementById('CANTIDAD_0').focus();
			return false;
		}	
	}
	// cod_estado_doc_sii_value = 4 = anulada
	if (to_num(cod_estado_doc_sii_value) == 4){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	return true;
}
function add_line_gd(ve_tabla_item, nomTabla) {


	var aTR = get_TR(ve_tabla_item);
	var VALOR_GD_H = document.getElementById('VALOR_GD_H_0').value;
	if (aTR.length >= VALOR_GD_H){
		alert('¡No se pueden agregar más ítems, se ha llegado al máximo permitido!');
		return false;
		}
	else
		add_line(ve_tabla_item,nomTabla);
}
function change_item_guia_despacho(ve_valor, ve_campo) {
	var record_item_nc = get_num_rec_field(ve_valor.id);
	var item_value = document.getElementById('ITEM_' + record_item_nc).value;
	var cod_producto_old = document.getElementById('COD_PRODUCTO_OLD_' + record_item_nc);
	var cod_producto = document.getElementById('COD_PRODUCTO_' + record_item_nc);
	//var cod_item_nv = document.getElementById('COD_ITEM_NOTA_CREDITO_' + record_item_nc).value;
	
	if(ve_campo == 'COD_PRODUCTO' | ve_campo == 'NOM_PRODUCTO'){
	
		help_producto(ve_valor, 0);
		if(cod_producto.value == 'T'){
			alert('No se pueden agregar Títulos a una Guia de Recepción.');
			if(cod_producto_old.value=='T'){ //es la primera vez que se ingresa el código
				document.getElementById('COD_PRODUCTO_' + record_item_nc).value = '';
				document.getElementById('NOM_PRODUCTO_' + record_item_nc).value = '';
			}
			else{
				cod_producto.value = cod_producto_old; 
				help_producto(cod_producto, 0); 
			}	
		}
	}	
}
function valida_ct_x_despachar(ve_campo) {
	// valida solo si la GD es creada desde
	var cod_doc = to_num(document.getElementById('COD_DOC_0').innerHTML);
	
	if (cod_doc != 0){
		var record = get_num_rec_field(ve_campo.id);
		var cant_por_despachar = to_num(document.getElementById('CANTIDAD_POR_DESPACHAR_' + record).innerHTML);
		var vl_cantidad_bodega = document.getElementById('CANTIDAD_BODEGA_' + record).value;
		var cant_ingresada = to_num(ve_campo.value);
		if (parseFloat(cant_por_despachar) < parseFloat(cant_ingresada)) {
			alert('El valor ingresado no puede ser mayor que la cantidad "por Recepcionar": '+ number_format(cant_por_despachar, 1, ',', '.'));
			return number_format(cant_por_despachar, 1, ',', '.');
		}
		/* VMC+MH, 20-06-2013 se decide no validar stock en la etapa inicial
		else if (parseFloat(vl_cantidad_bodega) < parseFloat(cant_ingresada)) {
			alert('El valor ingresado no puede ser mayor que la cantidad disponible en Bodega, stock actual: '+ number_format(vl_cantidad_bodega, 1, ',', '.'));
			return number_format(vl_cantidad_bodega, 1, ',', '.');
		}
		*/
		else
			return ve_campo.value;
	}
	else
		return ve_campo.value;
}
function mostrarOcultar_Anula(ve_campo) {
	var tr_anula = document.getElementById('tr_anula');
	
	if (to_num(ve_campo.value)==4) {
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
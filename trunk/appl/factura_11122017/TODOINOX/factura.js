function request_factura(ve_prompt, ve_valor) 
{
	var args = "location:no;dialogLeft:100px;dialogTop:350px;dialogWidth:300px;dialogHeight:330px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("../../../trunk/appl/factura/TODOINOX/request_factura.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor), "_blank", args);
	if (returnVal == null)		
		return false;		
	else 
	{
		
	  var dato = returnVal.split("|");
	  var cod_nota_venta_value= dato[1];
	  var opcion= dato[0];
	    
		if (opcion == 'desde_nv' || opcion == 'desde_cot' || opcion == 'desde_comercial' || opcion == 'desde_bodega' || opcion == 'desde_servindus') //
		{  
				//debe crear la FA para todos los itemsNV que tengan pendiente por facturar usar f_nv_por_facturar
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
		   		return true;
		}else	
 			{  
 			
 				//si selecciona desde GD debe presentar una 2da ventana 
 				args2 = "location:no;dialogLeft:400px;dialogTop:300px;dialogWidth:900px;dialogHeight:400px;dialogLocation:0;Toolbar:no;";
				var returnVal = window.showModalDialog("../../../trunk/appl/factura/request_factura_desdeGd.php?cod_nota_venta="+ cod_nota_venta_value, "_blank", args2);
	 			if (returnVal == null)
	 				return false;
	 			else
	 			{	
	 				
	 				document.getElementById('wo_hidden').value = returnVal;
					document.output.submit();
		   			return true;
	 				
	 			}
 			}
	}
}
function select_1_producto(valores, record) {
	// para BODEGA se debe usar el precio INTERNO
	cod_empresa = document.getElementById('COD_EMPRESA_0').value;
	var ajax = nuevoAjax();
	var vl_cod_producto_value = URLEncode(valores[1]);
	ajax.open("GET", "../factura/TODOINOX/ajax_producto_precio.php?cod_producto="+vl_cod_producto_value+"&cod_empresa="+cod_empresa, false);
	ajax.send(null);		

	var resp = URLDecode(ajax.responseText);
	valores[3] = resp; 
	
	set_values_producto(valores, record);

}

function select_1_empresa(valores, record) {
	 set_values_empresa(valores, record);
	 precio_prod_empresa();
	 centro_costo();
}

function precio_prod_empresa(){

	var aTR = get_TR('ITEM_FACTURA');
	for (var i = 0; i < aTR.length; i++){
		var cod_prod = document.getElementById('COD_PRODUCTO_' + i).value;
		var cod_empresa = document.getElementById('COD_EMPRESA_0').value;
		var cantidad = document.getElementById('CANTIDAD_'+ i).value;
		
		var ajax = nuevoAjax();
		ajax.open("GET", "../factura/TODOINOX/ajax_producto_precio.php?cod_producto="+cod_prod+"&cod_empresa="+cod_empresa, false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		
		precio = resp.replace(".","");
		set_value('PRECIO_'+i, resp, resp);
		set_value('PRECIO_H_'+i, precio, precio);
		document.getElementById('PRECIO_' + i).innerHTML = resp;
		computed(i, 'TOTAL');
		
		
	} 
}
function centro_costo() {
	var vl_cod_centro_costo = document.getElementById('COD_CENTRO_COSTO_0');
	vl_cod_centro_costo.length = 0;
	
	var vl_rut = document.getElementById('RUT_0').value;
	if (vl_rut==91462001) {	// COMERCIAL
		var vl_option = new Option('', ''); 
		vl_cod_centro_costo.appendChild(vl_option);
		 
		var vl_option = new Option('COMERCIAL BIGGI RENTAL', '010'); 
		vl_cod_centro_costo.appendChild(vl_option);

		var vl_option = new Option('COMERCIAL BIGGI SODEXO', '011'); 
		vl_cod_centro_costo.appendChild(vl_option);

		var vl_option = new Option('COMERCIAL BIGGI COMPASS', '012'); 
		vl_cod_centro_costo.appendChild(vl_option);

		var vl_option = new Option('COMERCIAL BIGGI CDR', '013'); 
		vl_cod_centro_costo.appendChild(vl_option);

		var vl_option = new Option('COMERCIAL BIGGI', '014'); 
		vl_cod_centro_costo.appendChild(vl_option);
	}
	else if (vl_rut==80112900) {	// BODEGA
		var vl_option = new Option('BODEGA BIGGI', '015'); 
		vl_cod_centro_costo.appendChild(vl_option);
	}
	else if (vl_rut==77773650) {	// SERVINDUS
		var vl_option = new Option('SERVINDUS', '016'); 
		vl_cod_centro_costo.appendChild(vl_option);
	}
	else {
		var vl_option = new Option('VENTAS TODOINOX', '017'); 
		vl_cod_centro_costo.appendChild(vl_option);
	}
} 

function validate() {
	var vl_cod_tipo_factura = document.getElementById('COD_TIPO_FACTURA_H_0').value;
	var K_TIPO_ARRIENDO = 2;
	
	if (vl_cod_tipo_factura != K_TIPO_ARRIENDO) {
		var aTR = get_TR('ITEM_FACTURA');
		if (aTR.length==0) {
			alert('Debe ingresar al menos 1 item antes de grabar.');
			return false;
		}
	}
	var cod_estado_doc_sii_value = get_value('COD_ESTADO_DOC_SII_0'); 
	if (to_num(cod_estado_doc_sii_value) == 4){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	if (document.getElementById('COD_FORMA_PAGO_0')){
		var cod_forma_pago = document.getElementById('COD_FORMA_PAGO_0').options[document.getElementById('COD_FORMA_PAGO_0').selectedIndex].value;
		var nom_forma_pago_otro = document.getElementById('NOM_FORMA_PAGO_OTRO_0').value;
		
		if (parseFloat(cod_forma_pago) == 1 && nom_forma_pago_otro == ''){
			alert ('Debe ingresar la Descripción de la forma de pago seleccionada.');
			document.getElementById('NOM_FORMA_PAGO_OTRO_0').focus();
			return false;
		}
	}	
	var porc_dscto1 = get_value('PORC_DSCTO1_0');
	var monto_dscto1 = get_value('MONTO_DSCTO1_0');
	//var monto_dscto2 = get_value('MONTO_DSCTO2_0');
	var sum_total = document.getElementById('SUM_TOTAL_H_0');		
	var porc_dscto_max = document.getElementById('PORC_DSCTO_MAX_0');
	if (sum_total.value=='') sum_total.value = 0;
	//if (monto_dscto1=='') monto_dscto2 = 0;
	
	
	
		cod_empresa = document.getElementById('COD_EMPRESA_0').value;
		var ajax = nuevoAjax();
		ajax.open("GET", "../factura/TODOINOX/ajax_valida_dscto.php?cod_empresa="+cod_empresa, false);
		ajax.send(null);
		var resp = ajax.responseText;
		
	  	var resp = resp.split('|');
	  	var porc_dscto = resp[0];
	  	var tabla = resp[1];
	  	 
	  	 var porc_dscto1 = parseFloat(porc_dscto1.replace(',', '.', 'g'));
	  	 var porc_dscto =  parseFloat(porc_dscto.replace(',', '.', 'g'));
	  	 
	  	 if(porc_dscto1>porc_dscto){
	  	 	alert('El descuento es mayor al permitido (máximo '+porc_dscto+'%) para '+tabla);
	  	 	return false;
	  	 }
	
	var aTR = get_TR('BITACORA_FACTURA');
	for (var i = 0; i < aTR.length; i++){
		var tiene_compromiso = document.getElementById('TIENE_COMPROMISO_' + i).checked;
		if (tiene_compromiso == true){
			var fecha_compromiso = document.getElementById('FECHA_COMPROMISO_E_' + i).value;
			var hora_compromiso = document.getElementById('HORA_COMPROMISO_E_' + i).value;
			var glosa_compromiso = document.getElementById('GLOSA_COMPROMISO_E_' + i).value;
			if(fecha_compromiso == ''){
				alert('Debe ingresar la fecha del compromiso');
				return false;
			}
			else if (hora_compromiso == ''){
				alert('Debe ingresar la hora del compromiso');
				return false;
			}
			else if (glosa_compromiso == ''){
				alert('Debe ingresar la descripción del compromiso');
				return false;
			}
		}
	}
	
	var vl_no_tiene_OC = document.getElementById('NO_TIENE_OC_0');
	var vl_nro_OC = document.getElementById('NRO_ORDEN_COMPRA_0').value;
	if (!vl_no_tiene_OC.checked && vl_nro_OC=='') {
		alert('Debe ingresar el nro OC');
		document.getElementById('NRO_ORDEN_COMPRA_0').focus();
		return false;
	}
	
	return true;
}

function valida_dsct(){

	porc_dscto 	= document.getElementById('PORC_DSCTO1_0').value;
	monto_dscto = document.getElementById('MONTO_DSCTO1_0').value;
	cod_empresa = document.getElementById('COD_EMPRESA_0').value;

	var ajax = nuevoAjax();
		ajax.open("GET", "../factura/TODOINOX/valida_dscto.php?porc_dscto="+porc_dscto+"&monto_dscto="+monto_dscto+"&cod_empresa"+cod_empresa, false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);

}
function otros_sistemas(){

	var comercial = document.getElementById('comercial_biggi');
	var bodega = document.getElementById('bodega_biggi');
	var servindus = document.getElementById('servindus');
	
	if (document.getElementById('otros_sistem').checked){
		comercial.style.display = '';
		bodega.style.display = '';
		servindus.style.display = '';
	}else{
		comercial.style.display = 'none';
		bodega.style.display = 'none';
		servindus.style.display = 'none';
	}
			
}


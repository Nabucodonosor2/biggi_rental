function usar_cheques(){
	var cod_empresa = get_value('COD_EMPRESA_0');
	var vl_cod_cheque_s = '';
	if(cod_empresa == ''){
		alert('Sr. Usuario: Primero debe indicar una empresa, así podrá ver los cheques asociados a dicha empresa');
		return false;
	}
	var vl_aTR = get_TR('DOC_INGRESO_PAGO'); 
	for(i=0 ; i < vl_aTR.length ; i++){
		var rec_tr = get_num_rec_field(vl_aTR[i].id);
		var vl_cod_cheque = get_value('COD_CHEQUE_'+rec_tr);
		if(vl_cod_cheque != '')
			vl_cod_cheque_s = vl_cod_cheque_s + vl_cod_cheque + ',';
	}
	if(vl_cod_cheque_s != '')
		vl_cod_cheque_s = vl_cod_cheque_s.substring(0, vl_cod_cheque_s.length-1);
		
	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:800px;dialogHeight:300px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("RENTAL/usar_cheque.php?cod_empresa="+cod_empresa+"&cod_cheque_actual="+vl_cod_cheque_s, "_blank", args);
	if (returnVal != null){
		var ajax = nuevoAjax();
		ajax.open("GET", "RENTAL/ajax_datos_cheque.php?cod_cheque="+returnVal, false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var vl_result = eval("(" + resp + ")");
		var vl_sum = 0;
		
		for(var ind=0 ; ind < vl_result.length ; ind++){
			var vl_row = add_line('DOC_INGRESO_PAGO', 'ingreso_pago');
			
			set_value('COD_TIPO_DOC_PAGO_'+vl_row, vl_result[ind]['COD_TIPO_DOC_PAGO'], vl_result[ind]['COD_TIPO_DOC_PAGO']);
			set_value('FECHA_DOC_'+vl_row, vl_result[ind]['FECHA_DOC'], vl_result[ind]['FECHA_DOC']);
			set_value('NRO_DOC_'+vl_row, vl_result[ind]['NRO_DOC'], vl_result[ind]['NRO_DOC']);
			set_value('COD_BANCO_'+vl_row, vl_result[ind]['COD_BANCO'], vl_result[ind]['COD_BANCO']);
			set_value('MONTO_DOC_'+vl_row, vl_result[ind]['MONTO_DOC'], vl_result[ind]['MONTO_DOC']);
			set_value('COD_CHEQUE_'+vl_row, vl_result[ind]['COD_CHEQUE'], vl_result[ind]['COD_CHEQUE']);
			
			set_value('COD_TIPO_DOC_PAGO_H_'+vl_row, vl_result[ind]['COD_TIPO_DOC_PAGO'], vl_result[ind]['COD_TIPO_DOC_PAGO']);
			set_value('COD_BANCO_H_'+vl_row, vl_result[ind]['COD_BANCO'], vl_result[ind]['COD_BANCO']);
			
			document.getElementById('FECHA_DOC_'+vl_row).readOnly = true;
			document.getElementById('NRO_DOC_'+vl_row).readOnly = true;
			document.getElementById('COD_TIPO_DOC_PAGO_'+vl_row).disabled = true;
			document.getElementById('COD_BANCO_'+vl_row).disabled = true;
			document.getElementById('DOC_INGRESO_PAGO_'+vl_row).style.backgroundColor = '#A9F5E1';
			
			computed(get_num_rec_field('MONTO_DOC_'+vl_row), 'MONTO_DOC_C');
		}
		set_value('OTRO_ANTICIPO_0', 0, 0);
	}
}

function ingreso_gasto_abono(ve_campo){
	var nom_campo = get_nom_field(ve_campo.id);
	if (nom_campo == "OTRO_INGRESO"){
		document.getElementById('OTRO_GASTO_0').value = 0;
		document.getElementById('OTRO_ANTICIPO_0').value = 0;
	}	
	else if (nom_campo == "OTRO_GASTO"){
		document.getElementById('OTRO_INGRESO_0').value = 0;
		document.getElementById('OTRO_ANTICIPO_0').value = 0;
	}
	else if(nom_campo == "OTRO_ANTICIPO"){	
		document.getElementById('OTRO_INGRESO_0').value = 0;
		document.getElementById('OTRO_GASTO_0').value = 0;
	}
	
	if(nom_campo == "OTRO_ANTICIPO"){
		var vl_aTR = get_TR('DOC_INGRESO_PAGO');
		var count_tr = 0;
		var count_cheque = 0;
		var vl_monto_doc = 0;
		var vl_sum_monto_doc = 0;
		for(i=0 ; i < vl_aTR.length ; i++){
			var rec_tr = get_num_rec_field(vl_aTR[i].id);
			var vl_cod_cheque = get_value('COD_CHEQUE_'+rec_tr);
			
			if(vl_cod_cheque != '')
				count_cheque++;
			else	
				vl_monto_doc = get_value('MONTO_DOC_'+rec_tr);
			
			count_tr++;
			vl_sum_monto_doc = parseInt(vl_monto_doc) + parseInt(vl_sum_monto_doc);
		}

		if(count_cheque != 0){
			if(count_tr > count_cheque){
				var vl_anticipo = get_value('OTRO_ANTICIPO_0');
				if(vl_anticipo > vl_sum_monto_doc){
					alert("El monto anticipo no puede ser mayor a $ "+vl_sum_monto_doc+" (Cheque que no provienen desde un Registro Cheque)");
					document.getElementById('OTRO_ANTICIPO_0').value = 0;
				}	
			}else{
				alert("No puede asignar anticipos, modifique el monto del documento en la pestaña Ingreso Pago");
				document.getElementById('OTRO_ANTICIPO_0').value = 0;
			}
		}
	}
}

function change_monto_doc(){
	var aTR = get_TR('INGRESO_PAGO_FACTURA');
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		var	monto_asignado = parseFloat(document.getElementById('MONTO_ASIGNADO_C_H_' + rec_tr).value);
		var	monto_fa = parseFloat(document.getElementById('MONTO_ASIGNADO_' + rec_tr).value);
		
		if(monto_asignado > 0){
			set_monto_asignado(rec_tr, monto_fa, 'INGRESO_PAGO_FACTURA');
			document.getElementById('SELECCION_' + rec_tr).checked = true;
		}	
	}//fin for

	var aTR = get_TR('INGRESO_PAGO_NOTA_VENTA');
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		var	monto_asignado = parseFloat(document.getElementById('MONTO_ASIGNADO_C_NV_H_' + rec_tr).value);
		var	monto_nv = parseFloat(document.getElementById('MONTO_ASIGNADO_NV_' + rec_tr).value);

		if(monto_asignado > 0){
			set_monto_asignado(rec_tr, monto_nv, 'INGRESO_PAGO_NOTA_VENTA');
			document.getElementById('SELECCION_NV_' + rec_tr).checked = true;
		}	
	}//fin for
	
	var aTR = get_TR('DOC_INGRESO_PAGO');
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		var vl_cod_cheque = get_value('COD_CHEQUE_'+rec_tr);
		var vl_monto_doc = get_value('MONTO_DOC_'+rec_tr);
		
		if(vl_cod_cheque != ''){
			var ajax = nuevoAjax();
			ajax.open("GET", "RENTAL/ajax_datos_cheque.php?cod_cheque="+vl_cod_cheque, false);
			ajax.send(null);
			var resp = URLDecode(ajax.responseText);
			var vl_result = eval("(" + resp + ")");
			
			if(vl_monto_doc > vl_result[0]['MONTO_DOC']){
				alert("Monto modificado excede al monto y/o saldo actual del cheque");
				document.getElementById('MONTO_DOC_'+rec_tr).value = vl_result[0]['MONTO_DOC'];
			}
		}
	}
	
	document.getElementById('OTRO_ANTICIPO_0').value = 0;
}
function select_1_empresa(valores, record) {
	set_values_empresa(valores, record);

	// borra todos los tr
	var vl_tabla = document.getElementById('ARRIENDO');
	while (vl_tabla.firstChild) {
	  vl_tabla.removeChild(vl_tabla.firstChild);
	}

	// Mientras carga elimino la opcion "Selecciona Opcion..." y pongo una que dice "Cargando..."
	var vl_tr = document.createElement("tr");
	vl_tr.setAttribute("class", "claro");
	vl_tabla.appendChild(vl_tr);
		var vl_td = document.createElement("td");
		vl_td.align = "center";
		vl_tr.appendChild(vl_td);
			var vl_label = document.createElement("label");
			vl_label.innerHTML = 'Cargando...';
			vl_td.appendChild(vl_label);

	// obtiene los contratos de la empresa
	var vl_cod_empresa = valores[1]; 
	var vl_fecha_stock = document.getElementById('FECHA_STOCK_0').value;
	ajax = nuevoAjax();
	ajax.open("GET", "ajax_load_arriendo.php?cod_empresa=" + vl_cod_empresa+"&fecha_stock="+vl_fecha_stock, false);
	ajax.send(null);  

	// elimina el mensaje de cargando
	vl_tabla.removeChild(vl_tabla.firstChild);

	var vl_resp = URLDecode(ajax.responseText);
	var vl_result = eval("(" + vl_resp + ")");	
	var vl_suma = 0;
	for(var i = 0; i < vl_result.length; i++) {
		vl_suma = parseFloat(vl_suma) + parseFloat(vl_result[i]['TOTAL']);
		
		vl_tr = document.createElement("tr");
		if (i%2==0)
			vl_tr.setAttribute("class", "claro");
		else
			vl_tr.setAttribute("class", "oscuro");
		vl_tr.id = "ARRIENDO_" + parseInt(i);
		vl_tabla.appendChild(vl_tr);

		vl_td = document.createElement("td");
		vl_td.width = "10%";
		vl_td.align = "center";
		vl_tr.appendChild(vl_td);
			var vl_input = document.createElement("input");
			vl_input.type = 'checkbox';
			vl_input.id = 'SELECCION_' + i;
			
			if(vl_result[i]['EXIGE_CHEQUE'] == 'S'){
				if(vl_result[i]['MONTO_POR_USAR'] == 0)
					vl_input.checked = false;
				else
					vl_input.checked = true;
			}else
				vl_input.checked = true;

			vl_input.onchange = function(){if(valida_check(this)){selecciona_contrato(this); selec_count(this);}}
			vl_td.appendChild(vl_input);

		vl_td = document.createElement("td");
		vl_td.width = "10%";
		vl_td.align = "center";
		vl_tr.appendChild(vl_td);
			vl_label = document.createElement("label");
			vl_label.id = 'COD_ARRIENDO_' + i;
			vl_label.innerHTML = vl_result[i]['COD_ARRIENDO'];
			vl_td.appendChild(vl_label);

		vl_td = document.createElement("td");
		vl_td.width = "20%";
		vl_td.align = "left";
		vl_tr.appendChild(vl_td);
			vl_label = document.createElement("label");
			vl_label.id = 'NOM_ARRIENDO_' + i;
			vl_label.innerHTML = URLDecode(vl_result[i]['NOM_ARRIENDO']);
			vl_td.appendChild(vl_label);

		vl_td = document.createElement("td");
		vl_td.width = "20%";
		vl_td.align = "left";
		vl_tr.appendChild(vl_td);
			vl_label = document.createElement("label");
			vl_label.id = 'REFERENCIA_' + i;
			vl_label.innerHTML = URLDecode(vl_result[i]['REFERENCIA']);
			vl_td.appendChild(vl_label);

		vl_td = document.createElement("td");
		vl_td.width = "10%";
		vl_td.align = "right";
		vl_tr.appendChild(vl_td);
				vl_label = document.createElement("label");
				vl_label.id = 'TOTAL_' + i;
				vl_label.innerHTML = number_format(vl_result[i]['TOTAL'], 0, ',', '.');
				vl_td.appendChild(vl_label);
				
		vl_td = document.createElement("td");
		vl_td.width = "10%";
		vl_td.align = "center";
		vl_tr.appendChild(vl_td);
			var vl_input = document.createElement("input");
			vl_input.type = 'checkbox';
			vl_input.id = 'EXIGE_CHEQUE_' + i;
			if(vl_result[i]['EXIGE_CHEQUE'] == 'S')
				vl_input.checked = true;
			else
				vl_input.checked = false;

			vl_input.disabled = true;
			vl_td.appendChild(vl_input);
		
		vl_td = document.createElement("td");
		vl_td.width = "10%";
		vl_td.align = "center";
		vl_tr.appendChild(vl_td);
			vl_label = document.createElement("label");
			vl_label.id = 'FECHA_LIBERADA_' + i;
			vl_label.innerHTML = vl_result[i]['FECHA_LIBERADO'];
			vl_td.appendChild(vl_label);
			
		vl_td = document.createElement("td");
		vl_td.width = "10%";
		vl_td.align = "right";
		vl_tr.appendChild(vl_td);
			var vl_div = document.createElement("div");
			vl_div.setAttribute("class", "margenDerecho");
			vl_td.appendChild(vl_div);
				vl_label = document.createElement("label");
				vl_label.id = 'SALDO_LIBERADO_' + i;
				vl_label.innerHTML = number_format(vl_result[i]['MONTO_POR_USAR'], 0, ',', '.');
				vl_div.appendChild(vl_label);
	}
	document.getElementById('sel_count').innerHTML = vl_result.length;
	document.getElementById('SUMA_TOTAL_0').innerHTML = number_format(vl_suma, 0, ',', '.');
	
	//Turn hourglass off
	document.body.style.cursor = "default";
}
function get_return_value() {
	
	if(document.getElementById('1fac_Ncont').checked){
		var vl_res ='';
	}else{
		var vl_res ='1fac_1cont|';
	}
	
	var val_fecha_stock = document.getElementById('FECHA_STOCK_0').value;
		
	ajax = nuevoAjax();
	ajax.open("GET", "ajax_valida_cambio_fecha.php?fecha=" + val_fecha_stock, false);
	ajax.send(null);
	var vl_resp = URLDecode(ajax.responseText);
	
	if(vl_resp == 'NO_ES_IGUAL'){
		vl_res = vl_res + val_fecha_stock+'|';
	}else if (vl_resp == 'ES_IGUAL'){
		vl_res = vl_res + '|';
	}
	
	var vl_tabla = document.getElementById('ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	for (var i=0; i<aTR.length; i++) {
		var vl_record = get_num_rec_field(aTR[i].id);
		var vl_seleccion = document.getElementById('SELECCION_' + vl_record);
		var vl_cod_arriendo = document.getElementById('COD_ARRIENDO_' + vl_record);
		if (vl_seleccion.checked)
			vl_res = vl_res + vl_cod_arriendo.innerHTML + '|'; 
	}
	return vl_res;
}
function marcar_todo() {
	var vl_tabla = document.getElementById('ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	var vl_suma = 0;
	for (var i=0; i < aTR.length; i++)	{
		var vl_record = get_num_rec_field(aTR[i].id);
		document.getElementById('SELECCION_' + vl_record).checked = true;
		vl_suma = parseFloat(vl_suma) + parseFloat(document.getElementById('TOTAL_'+ vl_record).innerHTML.replace('.',''));
	}
	document.getElementById('sel_count').innerHTML = aTR.length;
	document.getElementById('SUMA_TOTAL_0').innerHTML = number_format(vl_suma, 0, ',', '.');
}
function desmarcar_todo() {
	var vl_tabla = document.getElementById('ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	for (var i=0; i < aTR.length; i++)	{
		var vl_record = get_num_rec_field(aTR[i].id);
		document.getElementById('SELECCION_' + vl_record).checked = false;
	}
	document.getElementById('SUMA_TOTAL_0').innerHTML = 0;
	document.getElementById('sel_count').innerHTML = 0;
}
function dejar_seleccion() {
	var vl_tabla = document.getElementById('ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	
	// aTR esta VIVO !, por eso no se porne i++ en el for
	for (var i=0; i < aTR.length; )	{
		var vl_record = get_num_rec_field(aTR[i].id);
		if (document.getElementById('SELECCION_' + vl_record).checked == false) {
			var vl_tr = document.getElementById('ARRIENDO_' + vl_record);
		 	vl_tabla.removeChild(vl_tr);
		 }
		 else 
		 	i++;
	}
}
function selecciona_contrato(ve_seleccion) {
	var vl_tabla = document.getElementById('ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	var vl_suma = 0;
	for (var i=0; i < aTR.length; i++)	{
		var vl_record = get_num_rec_field(aTR[i].id);
		if (document.getElementById('SELECCION_' + vl_record).checked == true) {
			vl_suma = vl_suma + parseInt(to_num(document.getElementById('TOTAL_' + vl_record).innerHTML));
		 }
	}
	document.getElementById('SUMA_TOTAL_0').innerHTML = number_format(vl_suma, 0, ',', '.');
}

function selec_count(ve_seleccion){
	var vl_tabla = document.getElementById('ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	var vl_suma = 0;
	for (var i=0; i < aTR.length; i++)	{
		var vl_record = get_num_rec_field(aTR[i].id);
		if (document.getElementById('SELECCION_' + vl_record).checked == true) {
			vl_suma = vl_suma + 1;
		 }
	}
	document.getElementById('sel_count').innerHTML = vl_suma;
}

function valida_check(ve_control){
	var vl_record			= get_num_rec_field(ve_control.id);
	var vl_saldo_liberado	= document.getElementById('SALDO_LIBERADO_'+vl_record).innerHTML.replace(',','.','g');
	var vl_exige_cheque		= document.getElementById('EXIGE_CHEQUE_'+vl_record).checked;

	if(vl_exige_cheque == true)
		if(parseInt(vl_saldo_liberado) > 0)
			return true;
		else{
			document.getElementById('SELECCION_'+vl_record).checked = false;
			alert('Tiene que tener al menos un cheque liberado');
		}
	else
		return true;	
}
function valida_fecha(){
	var val_fecha_stock = document.getElementById('FECHA_STOCK_0').value;
	
	ajax = nuevoAjax();
	ajax.open("GET", "ajax_valida_fecha_stock.php?fecha=" + val_fecha_stock, false);
	ajax.send(null);
	var vl_resp = URLDecode(ajax.responseText);
	
	if(vl_resp == 'NO_FECHA'){
		alert('Debe ingresar una fecha valida dd/mm/aaaa');
		document.getElementById('FECHA_STOCK_0').value = '';
		return false;
		
	}else if (vl_resp == 'MAYOR'){
		alert('Debe ingresar una fecha igual o Menor a la actual ');
		document.getElementById('FECHA_STOCK_0').value = '';
		return false;
	}else{
		var cod_empresa = document.getElementById('COD_EMPRESA_0');
		help_empresa(cod_empresa, 'C');
	}
}
function valida_cambio_fecha(){
	var val_fecha_stock = document.getElementById('FECHA_STOCK_0').value;
	
	ajax = nuevoAjax();
	ajax.open("GET", "ajax_valida_cambio_fecha.php?fecha=" + val_fecha_stock, false);
	ajax.send(null);
	var vl_resp = URLDecode(ajax.responseText);
	
	if(vl_resp == 'NO_ES_IGUAL'){
		var r=confirm("¿esta seguro que dese facturar los contratos con los stock al : " +  val_fecha_stock);
		
		if (r==true){
			return true;
		}else{
			return false;
		  
		}
	}else if(vl_resp == 'ES_IGUAL'){
		return true;
	}
	
}
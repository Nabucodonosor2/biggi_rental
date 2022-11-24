function valida_liberado(ve_control){
	var vl_record = get_num_rec_field(ve_control.id);
	var vl_depositado = document.getElementById('DEPOSITADO_' + vl_record).checked;

	if(!vl_depositado){
		document.getElementById('LIBERADO_' + vl_record).checked = false;
		document.getElementById('FECHA_LIBERADO_' + vl_record).value = '';
		alert('Debe marcar el registro como depositado.');
	}
}

function marcado_deposito(ve_control){
	var vl_record = get_num_rec_field(ve_control.id);
	var vl_deposito = document.getElementById('DEPOSITADO_'+vl_record).checked;
	if(vl_deposito){
		ajax = nuevoAjax();
		ajax.open("GET", "ajax_get_date.php",false);
	    ajax.send(null);        
		var resp = ajax.responseText;
		
		document.getElementById('FECHA_DEPOSITADO_' + vl_record).value = resp;
	}else{
		document.getElementById('FECHA_DEPOSITADO_' + vl_record).value = '';
		document.getElementById('LIBERADO_' + vl_record).checked = false;
		document.getElementById('FECHA_LIBERADO_' + vl_record).value = '';
	}
}

function marcado_liberado(ve_control){
	var vl_record = get_num_rec_field(ve_control.id);
	var vl_liberado = document.getElementById('LIBERADO_'+vl_record).checked;
	if(vl_liberado){
		ajax = nuevoAjax();
		ajax.open("GET", "ajax_get_date.php",false);
	    ajax.send(null);        
		var resp = ajax.responseText;
		
		document.getElementById('FECHA_LIBERADO_' + vl_record).value = resp;
	}else
		document.getElementById('FECHA_LIBERADO_' + vl_record).value = '';
	
}
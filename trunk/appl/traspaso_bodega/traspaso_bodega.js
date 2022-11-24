function add_line(ve_tabla_id, ve_nom_tabla) {
	var vl_cod_bodega = get_value('COD_BODEGA_ORIGEN_0');
	if(vl_cod_bodega != '')
		return add_line_standard(ve_tabla_id, ve_nom_tabla);
	else
		my_alert('Falta ingresar la bodega origen');	
}

function stock_bodega_origen(ve_control){
	var record = get_num_rec_field(ve_control.id);
	var vl_cod_producto	= document.getElementById('COD_PRODUCTO_' + record).value;
	if(vl_cod_producto != ''){
		
		var vl_cantidad 	= findAndReplace(document.getElementById('CANTIDAD_' + record).value, ',', '.');
		var vl_cod_bodega 	= document.getElementById('COD_BODEGA_ORIGEN_0').value;
		ajax = nuevoAjax();
		ajax.open("GET", "ajax_valida_stock.php?cod_producto="+vl_cod_producto+"&cod_bodega="+vl_cod_bodega,false);
	    ajax.send(null);
	    var resp = URLDecode(ajax.responseText);
		var result = eval("(" + resp + ")");
	    if(parseFloat(vl_cantidad) > parseFloat(result[0]['CANTIDAD_STOCK'])){
	    	alert('El máximo disponible es '+ result[0]['CANTIDAD_STOCK']);
			set_value('CANTIDAD_' + record , result[0]['CANTIDAD_STOCK'], result[0]['CANTIDAD_STOCK']);
			document.getElementById('CANTIDAD_' + record).focus;
		}	
    }
}

function change_bodega(){
	var vl_aTR = get_TR('ITEM_TRASPASO_BODEGA');
	for(i=0 ; i < vl_aTR.length ; i++){
		var vl_rec = get_num_rec_field(vl_aTR[i].id);
		del_line('ITEM_TRASPASO_BODEGA_'+vl_rec, 'traspaso_bodega');
	}
}

function validate(){
	var vl_cod_bodega_ori = get_value('COD_BODEGA_ORIGEN_0');
	var vl_cod_bodega_des = get_value('COD_BODEGA_DESTINO_0');
	var vl_count = 0;
	
	if(vl_cod_bodega_ori == vl_cod_bodega_des){
		my_alert('No se puede hacer un traspaso a la misma bodega');
		return false;
	}
	
	var vl_aTR = get_TR('ITEM_TRASPASO_BODEGA');
	for(i=0 ; i < vl_aTR.length ; i++){
		var vl_rec = get_num_rec_field(vl_aTR[i].id);
		var vl_cantidad = get_value('CANTIDAD_'+vl_rec);
		
		if(vl_cantidad != 0)
			vl_count++;
	}
	
	if(vl_count == 0){
		my_alert('Debe ingresar al menos un producto para generar un traspaso en bodega');
		return false;
	}
}
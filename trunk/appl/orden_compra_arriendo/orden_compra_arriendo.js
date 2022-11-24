function select_1_producto(valores, record) {
	set_values_producto(valores, record);
	 
	var cod_producto_value = document.getElementById('COD_PRODUCTO_' + record).value;
	var cod_empresa = document.getElementById('COD_EMPRESA_0').value;  
	var ajax = nuevoAjax();	
    ajax.open("GET", "../orden_compra/get_precio_proveedor.php?cod_producto="+cod_producto_value+"&cod_empresa="+cod_empresa, false);    
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
		ajax.open("GET", "../orden_compra/ajax_change_precio.php?cod_producto="+URLEncode(cod_producto)+"&cod_empresa="+cod_empresa, false);
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
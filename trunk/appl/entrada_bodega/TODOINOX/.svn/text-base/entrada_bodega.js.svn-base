function request_entrada_bodega(ve_prompt, ve_valor) {
	var args = "location:no;dialogLeft:100px;dialogTop:200px;dialogWidth:300px;dialogHeight:250px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("../entrada_bodega/TODOINOX/request_entrada_bodega.php?prompt="+ve_prompt+"&valor="+ve_valor, "_blank", args);
 	if (returnVal == null)		
		return false;
	else{		
	 var dato = returnVal.split("|"); 
	 var nro_docto= dato[1];
	 var opcion= dato[0];
	 
	if (opcion == 'desde_ri' || opcion == 'desde_oc'){
		document.getElementById('wo_hidden').value = returnVal;
		document.output.submit();
		return true;
		
	  }
	}
}

function valida_stock(cantidad){
   
    var cant = ""+cantidad.id;
    var i = cant.substring(9, 13);
    var num = cantidad.value.replace(",",".");
    var num = parseFloat(cantidad.value);
    
	var vl_cod_producto = document.getElementById('COD_PRODUCTO_'+i).value;

	var ajax = nuevoAjax();
		ajax.open("GET", "TODOINOX/ajax_entrada_producto.php?cod_producto="+vl_cod_producto, false);
		ajax.send(null);
		var resp_desde_nv = URLDecode(ajax.responseText);
		
		var resp = ajax.responseText.split('|');
		
		var maneja_inventario = resp[0];
		var stock = parseFloat(resp[1]);
		
		if(resp[0] == 'N'){
		  alert('Este producto no maneja inventario, debe habilitarse previamente si desea modificar su stock');
		}else{
		  //alert('PRODUCTO EN "S"');
		}
		
		if((stock+num) < 0){
		 alert('Una entrada por '+num+' unidades, dejara el stock de este producto en negativo');
		}else{
		 
		}
		


}



function dlg_print_anexo() {
	var args = "location:no;dialogLeft:400px;dialogTop:200px;dialogWidth:450px;dialogHeight:150;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("dlg_print_anexo.php", "_blank", args);
 	if (returnVal == null)
 		return false;
	else {
		document.getElementById('wi_hidden').value = returnVal;
		document.input.submit();
   		return true;
	}
}

function dlg_seleccion_factura() {
	var args = "location:no;dialogLeft:400px;dialogTop:200px;dialogWidth:470px;dialogHeight:150;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("dlg_seleccion_factura.php", "_blank", args);
 	if (returnVal == null)
 		return false;
	else {
		var vl_cod_factura = get_value('COD_FACTURA_0');
		document.getElementById('wi_hidden').value = vl_cod_factura+'|'+returnVal;
		document.input.submit();
   		return true;
	}
}

function dlg_print_anexo_masivo(){
	var args = "location:no;dialogLeft:400px;dialogTop:200px;dialogWidth:450px;dialogHeight:150;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("dlg_print_anexo_masivo.php", "_blank", args);
 	if(returnVal == null)
 		return false;
	else{
		document.getElementById('wo_hidden').value = returnVal;
		document.output.submit();
   		return true;
	}
}
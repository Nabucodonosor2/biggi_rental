function dlg_find_vendedor(ve_nom_header, ve_valor_filtro, ve_sql) {
	var args = "location:no;dialogLeft:400px;dialogTop:300px;dialogWidth:470px;dialogHeight:170px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("../common_appl/dlg_find_vendedor.php?nom_header="+URLEncode(ve_nom_header)+"&valor_filtro="+URLEncode(ve_valor_filtro)+"&sql="+URLEncode(ve_sql), "_blank", args);
 	if (returnVal == null)
		document.getElementById('wo_hidden').value = '__BORRAR_FILTRO__';
	else
		document.getElementById('wo_hidden').value = returnVal;
	document.output.submit();
   	return true;
}
function dlg_find_modelo(ve_nom_header, ve_valor_filtro) {
	var args = "location:no;dialogLeft:400px;dialogTop:300px;dialogWidth:470px;dialogHeight:170px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("../common_appl/dlg_find_modelo.php?nom_header="+URLEncode(ve_nom_header)+"&valor_filtro="+URLEncode(ve_valor_filtro), "_blank", args);
 	if (returnVal == null)
		document.getElementById('wo_hidden').value = '__BORRAR_FILTRO__';
	else
		document.getElementById('wo_hidden').value = returnVal.trim();
	document.output.submit();
   	return true;
}

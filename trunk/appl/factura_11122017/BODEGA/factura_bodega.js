function request_factura(ve_prompt, ve_valor)  {
	var returnVal = prompt('Ingrese N� de OC Comercial', '');//ve_valor);
	if (returnVal == null)		
		return false;
	else if (returnVal=='')		
		return false;
	else {
		document.getElementById('wo_hidden').value = returnVal;
		document.output.submit();
   		return true;
	}
}
function dlg_factura_contrato(){
	var url = "dlg_factura_contrato.php";
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 200,
		 width: 470,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null){		
				return false;
			}			
			else {
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_add_x");
				input.setAttribute("id", "b_add_x");
				document.getElementById("output").appendChild(input);
				
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
		   		return true;
			}
		}
	});	
}

function dlg_print_anexo() {
	var url = "dlg_print_anexo.php";
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 200,
		 width: 470,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null){		
				return false;
			}			
			else {
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_print_anexo_x");
				input.setAttribute("id", "b_print_anexo_x");
				document.getElementById("input").appendChild(input);
				
				document.getElementById('wi_hidden').value = returnVal;
				document.input.submit();
		   		return true;
			}
		}
	});	
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
	var url = "dlg_print_anexo_masivo.php";
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 200,
		 width: 470,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null){		
				return false;
			}			
			else {
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_print_anexo_x");
				input.setAttribute("id", "b_print_anexo_x");
				document.getElementById("output").appendChild(input);
				
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
		   		return true;
			}
		}
	});	
}
function dlg_print() {
	var url = "dlg_print_por_despachar.php";
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 190,
		 width: 410,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null)		
				return false;		
			else 
			{
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_print_x");
				input.setAttribute("id", "b_print_x");
				document.getElementById("output").appendChild(input);
				
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
			   	return true;	
			}
		}
	});			
}
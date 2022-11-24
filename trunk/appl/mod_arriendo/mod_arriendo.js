function validate(){
	var vl_tabla = document.getElementById('ITEM_MOD_ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	var vl_count = 0;
	
	var tipo_mod_arriendo = document.getElementById('TIPO_MOD_ARRIENDO_H_0').value;	
	if (tipo_mod_arriendo == 'ELIMINAR'){
		for (i=0; i<aTR.length; i++){
			var rec_tr =get_num_rec_field(aTR[i].id);
			var cantidad_h = document.getElementById('CANTIDAD_H_' + rec_tr).value;
			var cantidad = document.getElementById('CANTIDAD_' + rec_tr).value;
			
			if(parseFloat(findAndReplace(cantidad_h, ',', '.')) < parseFloat(findAndReplace(cantidad, ',', '.'))){
				alert('La cantidad debe ser menor o igual a '+cantidad_h);
				document.getElementById('CANTIDAD_' + rec_tr).value = cantidad_h;
				document.getElementById('CANTIDAD_' + rec_tr).focus();
				return false;
			}
			if(cantidad == 0)
				vl_count++;
		}	
	}else{
		for (i=0; i<aTR.length; i++){
			var rec_tr =get_num_rec_field(aTR[i].id);
			var cantidad = document.getElementById('CANTIDAD_' + rec_tr).value;
			
			if(cantidad == 0)
				vl_count++;
		}
	}

	if (aTR.length==0 || vl_count == aTR.length) {
		alert('Debe ingresar al menos 1 item antes de grabar.');
		return false;
	}
	
	
	return true;
}

function request_mod_arriendo(){
	var url = "dlg_input_contrato.php";
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
				vl_split = returnVal.split('|');
				vl_codigo = vl_split[0];
				var ajax = nuevoAjax();
	            ajax.open("GET", "ajax_valida_mod_arriendo.php?cod_arriendo="+vl_codigo, false);
	            ajax.send(null);
	            var resp = URLDecode(ajax.responseText);
	           if(resp != ''){
	                  alert('El Contrato de Arriendo Nº'+vl_codigo+' tiene una Modificación de Arriendo Nº '+resp +' en estado emitido. Debe aprobar o anular esta Modificación de Arriendo antes de crear una nueva Modificación de Arriendo');
	                  return false;
	            }
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_create_x");
				input.setAttribute("id", "b_create_x");
				document.getElementById("output").appendChild(input);
				
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
			   	return true;	
			}
		}
	});	
}
function add_line_ref(ve_tabla_id, ve_nom_tabla){
	var vl_row = add_line(ve_tabla_id, ve_nom_tabla);
	return vl_row;
}

function valida_referencias(ve_control){
	var vl_record = get_num_rec_field(ve_control.id);
	var vl_hem = document.getElementById('REFERENCIA_HEM_0').value;
	var vl_hes = document.getElementById('REFERENCIA_HES_0').value;
	var count1 = 0;
	var count2 = 0;
	var count_cto = 0;
	var count_m_cto = 0;

	var aTR = get_TR('REFERENCIAS');
	for(i = 0; i < aTR.length ; i++){
	 	var vl_rec = get_num_rec_field(aTR[i].id);
	 	
	 	if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 1)//HEM
	 		count1++;
	 	else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 2)//HES
	 		count2++;
	 	else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 3){//CONTACTO
	 		count_cto++;
	 	}else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 4){//CORREO CONTACTO
	 		count_m_cto++;
	 		
	 		if(count_m_cto == 1){
		 		var theElement = document.getElementById('DOC_REFERENCIA_'+vl_rec);
		 		validate_mail(theElement);
			}
		}	
	}
	
	if(count1 > 1 || count2 > 1){
		alert('No debe ingresar mas de un tipo sea HEM o HES');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	
	if(vl_hem == 'S' && count2 > 0){
		alert('Esta empresa tiene como referencia HEM, no puede agregar referencias de tipo HES');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	
	if(vl_hes == 'S' && count1 > 0){
		alert('Esta empresa tiene como referencia HES, no puede agregar referencias de tipo HEM');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	
	if(count_cto > 1){
		alert('No debe ingresar mas de un tipo sea Contacto');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_record).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	if(count_m_cto > 1){
		alert('No debe ingresar mas de un correo de contacto');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_record).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
}

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
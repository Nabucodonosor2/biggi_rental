function validate(){
	var aTR = get_TR('ITEM_ARRIENDO');
	var cant_total = 0;
	if (aTR.length==0) {
		alert('Debe ingresar al menos 1 item antes de grabar.');
		return false;
	}
	var vl_cod_estado_arriendo =  document.getElementById('COD_ESTADO_ARRIENDO_0').selectedIndex;
	var vl_cod_arriendo =  document.getElementById('COD_ARRIENDO_H_0').value;
	//var vl_usuario_autoriza_sincheque =  document.getElementById('APROBAR_SIN_CHEQUE_H_0').value;
	var vl_exige_cheque =  document.getElementById('EXIGE_CHEQUE_0').checked;
	
	if(vl_exige_cheque){
		///para confirmar el contrato debe confirmar los cheques.
		if (vl_cod_estado_arriendo == 2){
		//Ajax trae el count  de los cheques confirmados.
			//if (vl_usuario_autoriza_sincheque != 'S'){
			var ajax = nuevoAjax();
			ajax.open("GET", "ajax_valida_registro_cheque.php?cod_arriendo="+vl_cod_arriendo, false);
			ajax.send(null);
			var resp = URLDecode(ajax.responseText);
	
			if (resp <= 0){
				alert('Debe registrar y confirmar los cheques antes de confirmar el contrato de arriendo');
				return false;
			}
			//}
		}/*else if (vl_cod_estado_arriendo == 3){
			var ajax = nuevoAjax();
			ajax.open("GET", "ajax_valida_registro_cheque.php?cod_arriendo="+vl_cod_arriendo, false);
			ajax.send(null);
			var resp = URLDecode(ajax.responseText);
	
			if (resp > 1){
				alert('El contrato de arriendo cuenta con cheques asignados.');
				return false;
			}
		}*/
	}	
}
function select_1_empresa(valores, record) {
/* Se reimplementa para agregar el llenado de COD_SUCURSAL
*/
	set_value('COD_EMPRESA_' + record, valores[1], valores[1]);
	set_value('RUT_' + record, valores[2], valores[2]);
	set_value('ALIAS_' + record, valores[3], valores[3]);
	set_value('NOM_EMPRESA_' + record, valores[4], valores[4]);
	set_value('DIG_VERIF_' + record, valores[5], valores[5]);
	set_value('GIRO_' + record, valores[6], valores[6]);
	
	var vl_drop_down_sucursal = valores[8];
	
	vl_drop_down_sucursal = findAndReplace(vl_drop_down_sucursal, 'COD_SUCURSAL_FACTURA_0', 'COD_SUCURSAL_0');
	set_drop_down('COD_SUCURSAL_' + record, vl_drop_down_sucursal);

	set_value('DIRECCION_SUCURSAL_' + record, valores[9], valores[9]);
	set_drop_down('COD_PERSONA_' + record, valores[12]);
	set_value('MAIL_CARGO_PERSONA_' + record, '', '');
}
function add_line_item(ve_tabla_id, ve_nom_tabla) {
	var vl_row = add_line(ve_tabla_id, ve_nom_tabla);
	return vl_row
}
function select_1_producto(valores, record) {
	/* Se reimplementa esta fucnion, para completa el precio de arrindo 
	 */
	set_value('COD_PRODUCTO_' + record, valores[1], valores[1]);
	set_value('NOM_PRODUCTO_' + record, valores[2], valores[2]);
	set_value('PRECIO_VENTA_' + record, valores[3], valores[3]);
	set_value('PRECIO_VENTA_H_' + record, to_num(valores[3]), to_num(valores[3]));

	var vl_porc_arr = document.getElementById('PORC_ARRIENDO_0').value;
	var vl_precio = roundNumber(to_num(valores[3]) * to_num(vl_porc_arr) /100, 0);	 
	set_value('PRECIO_' + record, vl_precio, number_format(vl_precio, 0, ',', '.'));
}
function calc_adicional() {
	computed(0, 'MONTO_ADICIONAL_RECUPERACION');
}
function valida_porc_arriendo(ve_porc_arriendo) {
	var vl_porc_min = document.getElementById('MIN_PORC_ARRIENDO_0').value;
	var vl_porc_max = document.getElementById('MAX_PORC_ARRIENDO_0').value;
	
	if (parseFloat(ve_porc_arriendo.value) < parseFloat(vl_porc_min)) {
		alert("El porcentaje m�nimo es "+vl_porc_min);
		ve_porc_arriendo.value = vl_porc_min;
	}
	else if (parseFloat(ve_porc_arriendo.value) > parseFloat(vl_porc_max)) {
		alert("El porcentaje m�ximo es "+vl_porc_max);
		ve_porc_arriendo.value = vl_porc_max;
	}

	// vuelve a calcular todas las lineas	
	var aTR = get_TR('ITEM_ARRIENDO');
	var vl_suma = 0;
	for (i=0; i < aTR.length; i++) {
		var vl_rec = get_num_rec_field(aTR[i].id);
		var vl_precio_venta = document.getElementById('PRECIO_VENTA_' + vl_rec).innerHTML;
		var vl_precio = roundNumber(to_num(vl_precio_venta) * to_num(ve_porc_arriendo.value) /100, 0);	 
		set_value('PRECIO_' + vl_rec, vl_precio, number_format(vl_precio, 0, ',', '.'));
		set_value('PRECIO_H_' + vl_rec, vl_precio, number_format(vl_precio, 0, ',', '.'));
		recalc_computed_relacionados(vl_rec, 'PRECIO');
	}
}
function ingreso_TE(cod_producto) {	
	/* VMC 25-03-2011 
		se duplica el codigo desde common_appl
		para manejar el precio venta y el calculo del valor del arriendo
		el codigo NUEVO esta entre *******
	*/

	var record = get_num_rec_field(cod_producto.id);
	
	// datos del TE		
	var nom_te_value = URLEncode(get_value('NOM_PRODUCTO_' + record));
	var previo_value = URLEncode(document.getElementById('PRECIO_H_' + record).value);
	var cod_tipo_te_value = URLEncode(document.getElementById('COD_TIPO_TE_' + record).value);
	var motivo_te_value = URLEncode(document.getElementById('MOTIVO_TE_' + record).value);

	// solo para NV, existe nom_usuario_autoriza_te 
	var nom_usuario_autoriza_te = document.getElementById('NOM_USUARIO_AUTORIZA_TE_' + record);
	if (nom_usuario_autoriza_te) {
		var nom_usuario_autoriza_te_value = URLEncode(document.getElementById('NOM_USUARIO_AUTORIZA_TE_' + record).value);
		var fecha_autoriza_te_value = URLEncode(document.getElementById('FECHA_AUTORIZA_TE_' + record).value);
		var motivo_autoriza_te_value = URLEncode(document.getElementById('MOTIVO_AUTORIZA_TE_' + record).value);
		var url = "../common_appl/ingreso_TE.php?nom_te="+nom_te_value+"&precio="+previo_value+"&cod_tipo_te="+cod_tipo_te_value+"&motivo_te="+motivo_te_value+"&nom_usuario_autoriza_te="+nom_usuario_autoriza_te_value+"&fecha_autoriza_te="+fecha_autoriza_te_value+"&motivo_autoriza_te="+motivo_autoriza_te_value;		
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 440,
			 width: 550,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	
				if (returnVal == null){
					if (cod_tipo_te_value!='')	// mantiene los valores anteriores.
			 			return;
			 				
			 		cod_producto.value = '';
					
					var precio 			= document.getElementById('PRECIO_'+ record);
					var precio_h 		= document.getElementById('PRECIO_H_' + record);
					var motivo  		= document.getElementById('MOTIVO_TE_' + record);
					var tipo_TE  		= document.getElementById('COD_TIPO_TE_' + record);		
					var cantidad  		= document.getElementById('CANTIDAD_' + record);
					
					
					set_value('NOM_PRODUCTO_' + record, '', '');
					precio.innerHTML 	= '';
					precio_h.value 		= '';
					motivo.value		= '';
					tipo_TE.value		= '';
					cantidad.value		= '';		
					
					recalc_computed_relacionados(record, 'PRECIO');
				}		
				else {
					var vl_boton_precio = document.getElementById('BOTON_PRECIO_' + record)
					if (vl_boton_precio)
						vl_boton_precio.value = 'TE';
					var res = returnVal.split('|');	
					var precio 			= document.getElementById('PRECIO_'+ record);
					var precio_h 		= document.getElementById('PRECIO_H_' + record);
					var motivo  		= document.getElementById('MOTIVO_TE_' + record);
					var tipo_TE  		= document.getElementById('COD_TIPO_TE_' + record);		
					var cantidad  		= document.getElementById('CANTIDAD_' + record);
					var motivo_autoriza_te	= document.getElementById('MOTIVO_AUTORIZA_TE_' + record);	
					
					set_value('NOM_PRODUCTO_' + record, res[0], res[0]);
					/*************************
					precio.innerHTML 	= number_format(res[1], 0, ',', '.'); 
					precio.value 		= res[1]; //por ejemplo en la OC el precio es un edit_num
					precio_h.value 		= res[1];
					*/
					set_value('PRECIO_VENTA_' + record, to_num(res[1]), number_format(res[1], 0, ',', '.'));
					set_value('PRECIO_VENTA_H_' + record, to_num(res[1]), number_format(res[1], 0, ',', '.'));
					var vl_porc_arr = document.getElementById('PORC_ARRIENDO_0').value;
					var vl_precio = roundNumber(to_num(res[1]) * to_num(vl_porc_arr) /100, 0);	 
					set_value('PRECIO_' + record, vl_precio, number_format(vl_precio, 0, ',', '.'));		
					set_value('PRECIO_H_' + record, vl_precio, number_format(vl_precio, 0, ',', '.'));		
					//************************
					
					motivo.value		= res[2];
					tipo_TE.value		= res[3];
					if (nom_usuario_autoriza_te)
						motivo_autoriza_te.value  = res[4];
			
					cantidad.focus();		
					
					recalc_computed_relacionados(record, 'PRECIO');
				}
			}
		});
	}
	else {
		var url = "../common_appl/ingreso_TE.php?nom_te="+nom_te_value+"&precio="+previo_value+"&cod_tipo_te="+cod_tipo_te_value+"&motivo_te="+motivo_te_value;		
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 330,
			 width: 550,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	
				if (returnVal == null){
					if (cod_tipo_te_value!='')	// mantiene los valores anteriores.
			 			return;
			 				
			 		cod_producto.value = '';
					
					var precio 			= document.getElementById('PRECIO_'+ record);
					var precio_h 		= document.getElementById('PRECIO_H_' + record);
					var motivo  		= document.getElementById('MOTIVO_TE_' + record);
					var tipo_TE  		= document.getElementById('COD_TIPO_TE_' + record);		
					var cantidad  		= document.getElementById('CANTIDAD_' + record);
					
					
					set_value('NOM_PRODUCTO_' + record, '', '');
					precio.innerHTML 	= '';
					precio_h.value 		= '';
					motivo.value		= '';
					tipo_TE.value		= '';
					cantidad.value		= '';		
					
					recalc_computed_relacionados(record, 'PRECIO');
				}		
				else {
					var vl_boton_precio = document.getElementById('BOTON_PRECIO_' + record)
					if (vl_boton_precio)
						vl_boton_precio.value = 'TE';
					var res = returnVal.split('|');	
					var precio 			= document.getElementById('PRECIO_'+ record);
					var precio_h 		= document.getElementById('PRECIO_H_' + record);
					var motivo  		= document.getElementById('MOTIVO_TE_' + record);
					var tipo_TE  		= document.getElementById('COD_TIPO_TE_' + record);		
					var cantidad  		= document.getElementById('CANTIDAD_' + record);
					var motivo_autoriza_te	= document.getElementById('MOTIVO_AUTORIZA_TE_' + record);	
					
					set_value('NOM_PRODUCTO_' + record, res[0], res[0]);
					/*************************
					precio.innerHTML 	= number_format(res[1], 0, ',', '.'); 
					precio.value 		= res[1]; //por ejemplo en la OC el precio es un edit_num
					precio_h.value 		= res[1];
					*/
					set_value('PRECIO_VENTA_' + record, to_num(res[1]), number_format(res[1], 0, ',', '.'));
					set_value('PRECIO_VENTA_H_' + record, to_num(res[1]), number_format(res[1], 0, ',', '.'));
					var vl_porc_arr = document.getElementById('PORC_ARRIENDO_0').value;
					var vl_precio = roundNumber(to_num(res[1]) * to_num(vl_porc_arr) /100, 0);	 
					set_value('PRECIO_' + record, vl_precio, number_format(vl_precio, 0, ',', '.'));		
					set_value('PRECIO_H_' + record, vl_precio, number_format(vl_precio, 0, ',', '.'));		
					//************************
					
					motivo.value		= res[2];
					tipo_TE.value		= res[3];
					if (nom_usuario_autoriza_te)
						motivo_autoriza_te.value  = res[4];
			
					cantidad.focus();		
					
					recalc_computed_relacionados(record, 'PRECIO');
				}
			}
		});
	}
}

function valida_fecha_entrega (ve_campo){
	var fecha = ve_campo.value.split('/');
	var ano = fecha[2];
	if ((ano.length )== 2){ //se ingreso el a�o en dos digitos, ejemplo = 17/01/11
		var ano_final = parseInt(ano) + 2000; //deja el a�o en cuatro digitos, ejemplo = 17/01/2011
	}	
	else{
		var ano_final = fecha[2];
	}
	
	var fecha_entrega = new Date(ano_final,fecha[1]-1,fecha[0]);  	

	var fecha_actual = new Date();
	var month = fecha_actual.getMonth();
	var day = fecha_actual.getDate();
	var year = fecha_actual.getFullYear();
	var fecha_actual = new Date(year,month,day); 
	
	var fecha_max = new Date();
	var month_max = fecha_actual.getMonth() + 6;
	var fecha_max = new Date(year,month_max,day);
	
	if (fecha_entrega < fecha_actual){
		ve_campo.value = '';
		document.getElementById('FECHA_ENTREGA_0').focus();
		alert('�La fecha de entrega no puede ser anterior al d�a de hoy!');
	}else if(fecha_entrega > fecha_max){
		ve_campo.value = '';
		document.getElementById('FECHA_ENTREGA_0').focus();
		alert('�La fecha ingresada no puede ser superior a 6 meses desde la fecha actual!');
	}
}
function dlg_print() {
	var cod_arriendo_value = document.getElementById('COD_ARRIENDO_H_0').value;
	var url = "dlg_print_arriendo.php?cod_arriendo="+cod_arriendo_value;
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 320,
			 width: 630,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	if (returnVal == null){		
					return false;
				}			
				else {
					var dato = returnVal.split('|');	  
					if (dato[1] == 'marca') {				
						var url = "dlg_print_marca.php?cod_arriendo="+cod_arriendo_value+"&cod_mod_arriendo="+dato[0];
						$.showModalDialog({
							 url: url,
							 dialogArguments: '',
							 height: 260,
							 width: 640,
							 scrollable: false,
							 onClose: function(){ 
							 	var returnVal = this.returnValue;
							 	if (returnVal == null){		
									return false;
								}			
								else {
									var input = document.createElement("input");
									input.setAttribute("type", "hidden");
									input.setAttribute("name", "b_print_x");
									input.setAttribute("id", "b_print_x");
									document.getElementById("input").appendChild(input);
									
									document.getElementById('wi_hidden').value = 'marca|'+returnVal;
									document.input.submit();
							   		return true;
								}
							}
						});	
					}else{			
						var input = document.createElement("input");
						input.setAttribute("type", "hidden");
						input.setAttribute("name", "b_print_x");
						input.setAttribute("id", "b_print_x");
						document.getElementById("input").appendChild(input);
						
						document.getElementById('wi_hidden').value = returnVal;
						document.input.submit();
				   		return true;
			   		}
				}
			}
		});	
}

function dlg_display_cot_arriendo(){
	var url = "dlg_display_cot_arr.php";
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 190,
		 width: 410,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null){		
				return false;
			}			
			else {
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
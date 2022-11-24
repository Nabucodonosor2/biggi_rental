function get_return_value() {
	var vl_res = '';
	var vl_tabla = document.getElementById('FACTURAS_DTE');
	var aTR = vl_tabla.getElementsByTagName("tr");
	for (var i=0; i<aTR.length; i++) {
		var vl_record = get_num_rec_field(aTR[i].id);
		var vl_seleccion = document.getElementById('SELECCIONAR_' + vl_record);
		var vl_cod_empresa = document.getElementById('COD_EMPRESA_' + vl_record);
		if (vl_seleccion.checked)
			vl_res = vl_res + vl_cod_empresa.value + '|'; 
	}
	return vl_res;
}
function validate(){
	var vl_res = '';
	var vl_tabla = document.getElementById('FACTURAS_DTE');
	var aTR = vl_tabla.getElementsByTagName("tr");
	var count_sel = 0
	var count_i = 0
	var count_cant_fa = 0
	for (var i=0; i<aTR.length; i++) {
		var vl_seleccion = document.getElementById('SELECCIONAR_' + i);
		var vl_cant_fa = document.getElementById('CANT_FA_H_' + i).value;
		
		if(!vl_seleccion.checked){	
			count_sel=count_sel+1;
			
		}
		count_i =  count_i+1;
		if (vl_seleccion.checked){
			count_cant_fa = parseInt(count_cant_fa) + parseInt(vl_cant_fa);
		}	
	}
	if(count_sel == count_i){
		alert('Seleccione al menos un registro');
		return false;
	}else{
		var r=confirm("Esta seguro que desea generar: " + count_cant_fa + " facturas electronicas");
		if (r==true){
		  return true;
		}else{
		  return false;
		}	
	}
	
	return true;
}
function validate()
{
	vl_tab_cheque					= document.getElementById('CHEQUE');
	vl_cod_estado_ingreso_cheque	= document.getElementById('COD_ESTADO_INGRESO_CHEQUE_0').value;
	
	if(vl_tab_cheque.rows.length == 0 && vl_cod_estado_ingreso_cheque != 3){
		alert('Debe ingresar almenos un documento');
		return false;
	}	
	
	//se obtiene el numero del ultimo tr 
	for (i=0; i<vl_tab_cheque.rows.length; i++){
		vl_num_tr	= get_num_rec_field(vl_tab_cheque.rows[i].id);
		
		vl_cod_tipo_doc_pago	= document.getElementById('COD_TIPO_DOC_PAGO_'+vl_num_tr).value;
		vl_fecha_doc			= document.getElementById('FECHA_DOC_'+vl_num_tr).value;
		vl_nro_doc				= document.getElementById('NRO_DOC_'+vl_num_tr).value;
		vl_cod_banco			= document.getElementById('COD_BANCO_'+vl_num_tr).value;
		vl_cod_plaza			= document.getElementById('COD_PLAZA_'+vl_num_tr).value;
		vl_monto_doc			= document.getElementById('MONTO_DOC_'+vl_num_tr).value;
		
		if(vl_cod_tipo_doc_pago == ''){
			alert('Ingresar Tipo Documento');
			return false;
		}
		if(vl_fecha_doc == ''){
			alert('Ingresar Fecha Documento');
			return false;
		}
		if(vl_nro_doc == '' || vl_nro_doc <= 0){
			alert('Ingresar Nro Documento');
			return false;
		}
		if(vl_cod_banco == ''){
			alert('Ingresar Banco');
			return false;
		}
		if(vl_cod_plaza == ''){
			alert('Ingresar Plaza');
			return false;
		}
		if(vl_monto_doc == '' || vl_monto_doc <=0){
			alert('no se permiten documentos con monto $0');
			return false;
		}
	}
}
function add_cheque(ve_row)
{
	vl_cant_cuota	= document.getElementById('COD_TIPO_DOC_PAGO_'+ve_row).value = 12;
	vl_tab_cheque	= document.getElementById('CHEQUE');
	vl_num_tr		= 0;
	
	document.getElementById('COUNT_CHEQUE_0').innerHTML = vl_tab_cheque.rows.length;
	document.getElementById('COUNT_CHEQUE_DOS_0').innerHTML = vl_tab_cheque.rows.length;
	
	//se obtiene el numero del ultimo tr 
	for (i=0; i<vl_tab_cheque.rows.length-1; i++){
		vl_num_tr = get_num_rec_field(vl_tab_cheque.rows[i].id);
	}
	
	//se obtiene el valor del ultimo tr
	vl_cod_banco	= document.getElementById('COD_BANCO_'+vl_num_tr).value;
	vl_cod_plaza	= document.getElementById('COD_PLAZA_'+vl_num_tr).value;
	
	if(vl_cod_banco != null){
		document.getElementById('COD_BANCO_'+ve_row).value = vl_cod_banco;
		document.getElementById('COD_PLAZA_'+ve_row).value = vl_cod_plaza;
	}
}
function add_line_item(ve_tabla_id, ve_nom_tabla)
{
	var vl_row = add_line(ve_tabla_id, ve_nom_tabla);
	add_cheque(vl_row)
	return vl_row
}
function del_line(ve_tr_id, ve_nom_mantenedor)
{
	del_line_standard(ve_tr_id, ve_nom_mantenedor);
	vl_tab_cheque	= document.getElementById('CHEQUE');
	document.getElementById('COUNT_CHEQUE_0').innerHTML = vl_tab_cheque.rows.length;
	document.getElementById('COUNT_CHEQUE_DOS_0').innerHTML = vl_tab_cheque.rows.length;
}
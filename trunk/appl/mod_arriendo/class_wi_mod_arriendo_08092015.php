<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/class_print_mod_arriendo.php");

class dw_item_mod_arriendo extends dw_item {
	var $tipo_mod_arriendo;
	
	function dw_item_mod_arriendo() {
		$sql_i = "select IMA.COD_ITEM_MOD_ARRIENDO
						,MA.COD_MOD_ARRIENDO
						,MA.TIPO_MOD_ARRIENDO
						,IMA.ORDEN
						,IMA.ITEM
						,IMA.COD_PRODUCTO
						,IMA.NOM_PRODUCTO
						,dbo.f_bodega_stock(IMA.cod_producto, a.cod_bodega, getdate()) CANTIDAD_ACTUAL
						,IMA.CANTIDAD CANTIDAD
						,dbo.f_bodega_stock(IMA.cod_producto, a.cod_bodega, getdate())CANTIDAD_H
						,IMA.PRECIO
						,IMA.PRECIO_VENTA
						,IMA.COD_TIPO_TE
						,IMA.MOTIVO_TE
						,'' DISPLAY_BTN
						,'' DISPLAY_CANT_QUITAR
						,'' DISPLAY_CANT_QUITAR2
				from MOD_ARRIENDO MA, ITEM_MOD_ARRIENDO IMA, ARRIENDO A
				where MA.COD_MOD_ARRIENDO = {KEY1}
				AND MA.COD_MOD_ARRIENDO = IMA.COD_MOD_ARRIENDO
				and A.cod_arriendo = MA.cod_arriendo
				--and dbo.f_bodega_stock(IMA.cod_producto, a.cod_bodega, getdate()) > 0
				order by ORDEN";
		parent::dw_item($sql_i, 'ITEM_MOD_ARRIENDO', true, true, 'COD_PRODUCTO');
		
		$this->add_control(new edit_text('COD_ITEM_MOD_ARRIENDO',10, 10, 'hidden'));
		$this->add_control(new edit_cantidad('CANTIDAD_H'));
		
		
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text('ITEM',4 , 5));
		$this->add_control(new edit_cantidad('CANTIDAD_ACTUAL',12,10));
		$this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		
		$this->add_control(new computed('PRECIO_VENTA'), 0);
		$this->add_control(new computed('PRECIO', 0));
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL');
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		$this->add_control(new edit_text('COD_TIPO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('MOTIVO_TE',10, 100, 'hidden'));		
		
		$this->set_first_focus('COD_PRODUCTO');

		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
		
	}
	function retrieve($cod_mod_arriendo) {
		parent::retrieve($cod_mod_arriendo);
		if ($this->row_count() > 0)
			$this->tipo_mod_arriendo = $this->get_item(0, 'TIPO_MOD_ARRIENDO');
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		$this->set_item($row, 'ITEM', $this->row_count());
		if ($this->tipo_mod_arriendo=='AGREGAR')
			$this->set_item($row, 'DISPLAY_CANT_QUITAR', 'none');

		return $row;
	}
	
	function update($db, $cod_mod_arriendo)	{
		$sp = 'spu_item_mod_arriendo';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
				
			$cod_item_mod_arriendo 	= $this->get_item($i, 'COD_ITEM_MOD_ARRIENDO');
			$orden 					= $this->get_item($i, 'ORDEN');
			$item 					= $this->get_item($i, 'ITEM');
			$cod_producto 			= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 			= $this->get_item($i, 'NOM_PRODUCTO');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');
			$precio 				= $this->get_item($i, 'PRECIO');
			$precio_venta 			= $this->get_item($i, 'PRECIO_VENTA');
			$cod_tipo_te 			= $this->get_item($i, 'COD_TIPO_TE');
			$motivo_te				= $this->get_item($i, 'MOTIVO_TE');
			
			$cod_item_mod_arriendo = ($cod_item_mod_arriendo=='') ? "null" : $cod_item_mod_arriendo;
			$cod_tipo_te = ($cod_tipo_te=='') ? "null" : $cod_tipo_te;
			$motivo_te = ($motivo_te=='') ? "null" : "'$motivo_te'";
			
			if ($cantidad==0)
				continue;
				
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion'
						,$cod_item_mod_arriendo
						,$cod_mod_arriendo
						,$orden
						,'$item'
						,'$cod_producto'
						,'$nom_producto'
						,$cantidad
						,$precio
						,$precio_venta
						,$cod_tipo_te
						,$motivo_te";
				if (!$db->EXECUTE_SP($sp, $param))
					return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;

			$cod_item_mod_arriendo = $this->get_item($i, 'COD_ITEM_MOD_ARRIENDO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_item_mod_arriendo")){
				return false;
			}
		}
		//Ordernar
		if ($this->row_count() > 0) {
			$parametros_sp = "'ITEM_MOD_ARRIENDO','MOD_ARRIENDO', $cod_mod_arriendo";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
		}
		return true;
	}
}
class dw_mod_arriendo extends datawindow {
	const K_EMITIDA = 1;
	
	function dw_mod_arriendo() {
		$sql = "select	MA.COD_MOD_ARRIENDO
						,convert(varchar(20), MA.FECHA_MOD_ARRIENDO, 103) FECHA_MOD_ARRIENDO
						,U.NOM_USUARIO
						,MA.REFERENCIA
						,MA.COD_ESTADO_MOD_ARRIENDO
						,A.COD_ARRIENDO
						,convert(varchar(20), A.FECHA_ARRIENDO, 103) FECHA_ARRIENDO
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,A.REFERENCIA REFERENCIA_ARRIENDO
						,A.NRO_MESES
						,A.PORC_ARRIENDO
						,A.PORC_ARRIENDO PORC_ARRIENDO_STATIC
						,MA.SUBTOTAL SUM_TOTAL
						,MA.TOTAL_NETO
						,MA.PORC_IVA
						,MA.MONTO_IVA
						,MA.TOTAL_CON_IVA
						,MA.TIPO_MOD_ARRIENDO
						,MA.TIPO_MOD_ARRIENDO TIPO_MOD_ARRIENDO_H
						,MA.OBS
						,'' AGREGA_ELIMINA
						,'' AGREGAR
						,'' DISPLAY_BTN
						,'' DISPLAY_CANT_QUITAR
				from 	MOD_ARRIENDO MA, ARRIENDO A, USUARIO U, EMPRESA E
				where	MA.COD_MOD_ARRIENDO = {KEY1}
				  and 	A.COD_ARRIENDO = MA.COD_ARRIENDO 
				  and   E.COD_EMPRESA = A.COD_EMPRESA
			 	  and	U.COD_USUARIO = MA.COD_USUARIO";
		parent::datawindow($sql);
		
		$sql = "select COD_ESTADO_MOD_ARRIENDO
						,NOM_ESTADO_MOD_ARRIENDO
				from ESTADO_MOD_ARRIENDO
				order by COD_ESTADO_MOD_ARRIENDO";
		$this->add_control(new drop_down_dw('COD_ESTADO_MOD_ARRIENDO',$sql));
		$this->add_control(new edit_text_upper('REFERENCIA', 100, 100));
		$this->add_control(new static_text('COD_ARRIENDO'));
		$this->add_control(new static_text('TIPO_MOD_ARRIENDO'));
		$this->add_control(new static_num('RUT'));
		$this->add_control(new edit_text('TIPO_MOD_ARRIENDO_H',10, 10, 'hidden'));
		$this->add_control(new edit_text_multiline('OBS',101,2));
		
		// Totales
		$this->add_control($control = new edit_porcentaje('PORC_ARRIENDO'));
		$control->type = 'hidden';
		$this->set_computed('TOTAL_NETO', '[SUM_TOTAL]');
		$this->add_control(new drop_down_iva());
		$this->set_computed('MONTO_IVA', '[TOTAL_NETO] * [PORC_IVA] / 100');
		$this->set_computed('TOTAL_CON_IVA', '[TOTAL_NETO] + [MONTO_IVA]');
		
		$this->set_mandatory('REFERENCIA', 'Referencia');
		$this->set_mandatory('COD_ESTADO_MOD_ARRIENDO', 'Estado');
	}
	function new_mod_arrriendo() {
		
	}
}
class wi_mod_arriendo extends w_cot_nv {
	const K_MOD_ARRIENDO_EMITIDO = 1;
	const K_MOD_ARRIENDO_APROBADO = 2;
	const K_MOD_ARRIENDO_ANULADO = 3;
	const K_EMITIDA = 1;
	
	function wi_mod_arriendo($cod_item_menu) {
		parent::w_cot_nv('mod_arriendo', $cod_item_menu);
		$this->dws['dw_mod_arriendo'] = new dw_mod_arriendo();
		$this->dws['dw_item_mod_arriendo'] = new dw_item_mod_arriendo();
		$this->set_first_focus('REFERENCIA');
	}
	function new_record() {
		//$this->dws['dw_mod_arriendo']->new_mod_arrriendo();
		$this->dws['dw_mod_arriendo']->insert_row();
		$cod_arriendo = session::get('MOD_ARRIENDO.CREAR_DESDE_ARRIENDO');
		session::un_set('MOD_ARRIENDO.CREAR_DESDE_ARRIENDO');
		$opcion = session::get('MOD_ARRIENDO.CREAR_DESDE_ARRIENDO_OP');
		session::un_set('MOD_ARRIENDO.CREAR_DESDE_ARRIENDO_OP');

		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select	convert(varchar(20), A.FECHA_ARRIENDO, 103) FECHA_ARRIENDO
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,A.NRO_MESES
						,A.PORC_ARRIENDO
						,A.REFERENCIA REFERENCIA_ARRIENDO
				from 	ARRIENDO A, EMPRESA E
				where	A.COD_ARRIENDO = $cod_arriendo
				  and 	E.COD_EMPRESA = A.COD_EMPRESA";
		$result = $db->build_results($sql);

		$this->dws['dw_mod_arriendo']->set_item(0, 'FECHA_MOD_ARRIENDO', $this->current_date());
		$this->dws['dw_mod_arriendo']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_mod_arriendo']->set_item(0, 'COD_ESTADO_MOD_ARRIENDO', self::K_EMITIDA);
		$this->dws['dw_mod_arriendo']->set_entrable('COD_ESTADO_MOD_ARRIENDO', false);
		$this->dws['dw_mod_arriendo']->set_item(0, 'COD_ARRIENDO', $cod_arriendo);
		$this->dws['dw_mod_arriendo']->set_item(0, 'FECHA_ARRIENDO', $result[0]['FECHA_ARRIENDO']);
		$this->dws['dw_mod_arriendo']->set_item(0, 'RUT', $result[0]['RUT']);
		$this->dws['dw_mod_arriendo']->set_item(0, 'DIG_VERIF', $result[0]['DIG_VERIF']);
		$this->dws['dw_mod_arriendo']->set_item(0, 'NOM_EMPRESA', $result[0]['NOM_EMPRESA']);
		$this->dws['dw_mod_arriendo']->set_item(0, 'REFERENCIA_ARRIENDO', $result[0]['REFERENCIA_ARRIENDO']);
		$this->dws['dw_mod_arriendo']->set_item(0, 'REFERENCIA', $result[0]['REFERENCIA_ARRIENDO']);
		$this->dws['dw_mod_arriendo']->set_item(0, 'NRO_MESES', $result[0]['NRO_MESES']);
		$this->dws['dw_mod_arriendo']->set_item(0, 'PORC_ARRIENDO', $result[0]['PORC_ARRIENDO']);
		$this->dws['dw_mod_arriendo']->set_item(0, 'PORC_ARRIENDO_STATIC', number_format($result[0]['PORC_ARRIENDO'], 1, ',', ''));
		
		
		if($opcion == 'agrega'){
			$this->dws['dw_mod_arriendo']->set_item(0, 'AGREGAR', '');
			$this->dws['dw_mod_arriendo']->set_item(0, 'AGREGA_ELIMINA', 'Agregar');
			$this->dws['dw_mod_arriendo']->set_item(0, 'TIPO_MOD_ARRIENDO', 'AGREGAR');
			$this->dws['dw_mod_arriendo']->set_item(0, 'TIPO_MOD_ARRIENDO_H', 'AGREGAR');
			$this->dws['dw_mod_arriendo']->set_item(0, 'DISPLAY_CANT_QUITAR', 'none');	//OCULTA LA COLUMNA "CANT_QUITAR"
			$this->dws['dw_item_mod_arriendo']->tipo_mod_arriendo = 'AGREGAR';
			
		}else if($opcion == 'elimina'){
			$this->dws['dw_mod_arriendo']->set_item(0, 'AGREGAR', '');
			$this->dws['dw_mod_arriendo']->set_item(0, 'AGREGA_ELIMINA', 'Eliminar');
			$this->dws['dw_mod_arriendo']->set_item(0, 'DISPLAY_BTN', 'none');	//OCULTA EL BOTON "AGREGAR_ITEM"
			$this->dws['dw_item_mod_arriendo']->tipo_mod_arriendo = 'ELIMINAR';
			$this->dws['dw_item_mod_arriendo']->set_entrable('AGREGAR_ITEM_MOD_ARRIENDO', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('ORDEN', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('ITEM', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('COD_PRODUCTO', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('NOM_PRODUCTO', false);
			$this->dws['dw_mod_arriendo']->set_entrable('REFERENCIA', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('CANTIDAD_ACTUAL', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('CANTIDAD', true);
			$this->dws['dw_mod_arriendo']->set_item(0, 'TIPO_MOD_ARRIENDO', 'ELIMINAR');
			$this->dws['dw_mod_arriendo']->set_item(0, 'TIPO_MOD_ARRIENDO_H', 'ELIMINAR');
		
			$sql_item = "SELECT	 DISTINCT I.COD_PRODUCTO
							,I.NOM_PRODUCTO
							,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA,getdate()) CANTIDAD_ACTUAL
							,0 CANTIDAD
							,I.PRECIO
							,I.PRECIO
							,I.PRECIO_VENTA
							,I.COD_TIPO_TE
							,I.MOTIVO_TE
					FROM  ITEM_MOD_ARRIENDO I, MOD_ARRIENDO M, ARRIENDO A
					WHERE M.COD_ARRIENDO = $cod_arriendo
			          AND I.COD_MOD_ARRIENDO = M.COD_MOD_ARRIENDO
			          AND A.COD_ARRIENDO = M.COD_ARRIENDO
			          AND dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA,getdate()) > 0";
			$result_item = $db->build_results($sql_item);
			
			$orden= 10;
			$item= 1;
			for($i=0; $i<count($result_item); $i++) {
	
				//traemos de stock los productos. 
				$this->dws['dw_item_mod_arriendo']->insert_row();
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'ORDEN', $orden);
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'ITEM', $item);	
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'COD_PRODUCTO', $result_item[$i]['COD_PRODUCTO']);
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'NOM_PRODUCTO', $result_item[$i]['NOM_PRODUCTO']);
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'CANTIDAD_ACTUAL', $result_item[$i]['CANTIDAD_ACTUAL']);
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'CANTIDAD_H', $result_item[$i]['CANTIDAD_ACTUAL']);
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'CANTIDAD', $result_item[$i]['CANTIDAD']);
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'PRECIO_VENTA', $result_item[$i]['PRECIO_VENTA']);
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'PRECIO', $result_item[$i]['PRECIO']);
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'DISPLAY_CANT_QUITAR2', '');	//MUESTRA EL CAMPO "	 2"
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'DISPLAY_BTN', 'none');	//OCULTA EL BOTON "ELIMINAR"
				$orden = $orden + 10;
				$item++;
			}
			
		}
		
		$sql_total ="SELECT TOTAL_NETO
					 		,PORC_IVA
							,MONTO_IVA
							,TOTAL_CON_IVA
							,SUBTOTAL	 
					FROM	ARRIENDO
					WHERE	COD_ARRIENDO = $cod_arriendo";
		$result_total = $db->build_results($sql_total);
		
		$this->dws['dw_mod_arriendo']->set_item(0, 'TOTAL_NETO', $result_total[0]['TOTAL_NETO']);
		//TOTAL_NETO
		$this->dws['dw_mod_arriendo']->set_item(0, 'SUM_TOTAL', $result_total[0]['SUBTOTAL']);
		$this->dws['dw_mod_arriendo']->set_item(0, 'PORC_IVA', $result_total[0]['PORC_IVA']);
		$this->dws['dw_mod_arriendo']->set_item(0, 'MONTO_IVA', $result_total[0]['MONTO_IVA']);
		$this->dws['dw_mod_arriendo']->set_item(0, 'TOTAL_CON_IVA', $result_total[0]['TOTAL_CON_IVA']);
		
		$this->dws['dw_item_mod_arriendo']->calc_computed();
		$this->dws['dw_mod_arriendo']->calc_computed();
		
	}
	function print_record(){
		$cod_mod_arriendo = $this->get_key();
		$labels = array();
		$labels['strCOD_MOD_ARRIENDO'] = $cod_mod_arriendo;			
		$file_name = $this->find_file('mod_arriendo', 'mod_arriendo.xml');					
		$rpt = new print_mod_arriendo($cod_mod_arriendo, $file_name, $labels, "Mod. Arriendo ".$cod_mod_arriendo.".pdf", 1);
		$this->_load_record();
		return true;	
	}
	function load_record(){
		$cod_mod_arriendo = $this->get_item_wo($this->current_record, 'COD_MOD_ARRIENDO');
		$this->dws['dw_mod_arriendo']->retrieve($cod_mod_arriendo);
		$this->dws['dw_item_mod_arriendo']->retrieve($cod_mod_arriendo);
		$this->dws['dw_mod_arriendo']->set_entrable('COD_ESTADO_MOD_ARRIENDO', true);
		$cod_estado_mod_arriendo = $this->dws['dw_mod_arriendo']->get_item(0, 'COD_ESTADO_MOD_ARRIENDO');
		if ($cod_estado_mod_arriendo==self::K_MOD_ARRIENDO_APROBADO) {
			$this->b_delete_visible = false;
			$this->b_save_visible = false;
			$this->b_no_save_visible = false;
			$this->b_modify_visible = false;
		}
		else if ($cod_estado_mod_arriendo==self::K_MOD_ARRIENDO_ANULADO) {
			$this->b_delete_visible = false;
			$this->b_save_visible = false;
			$this->b_no_save_visible = false;
			$this->b_modify_visible = false;
			$this->b_print_visible = false;
		}
		 $tipo_mod_arriendo = $this->dws['dw_mod_arriendo']->get_item(0, 'TIPO_MOD_ARRIENDO');
		 
		 if($tipo_mod_arriendo == 'AGREGAR'){
		 	$this->dws['dw_mod_arriendo']->set_item(0, 'AGREGAR', '');
			$this->dws['dw_mod_arriendo']->set_item(0, 'AGREGA_ELIMINA', 'Agregar');
			//$this->dws['dw_mod_arriendo']->set_item(0, 'DISPLAY_BTN', 'none');
			$this->dws['dw_mod_arriendo']->set_item(0, 'DISPLAY_CANT_QUITAR', 'none');
			
			//for ($i = 0; $i < $this->row_count(); $i++){
			for ($i=0; $i<$this->dws['dw_item_mod_arriendo']->row_count(); $i++){	
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'DISPLAY_CANT_QUITAR', 'none');
			}
			
			$this->dws['dw_item_mod_arriendo']->set_entrable('AGREGAR_ITEM_MOD_ARRIENDO', true);
			$this->dws['dw_item_mod_arriendo']->set_entrable('ORDEN', true);
			$this->dws['dw_item_mod_arriendo']->set_entrable('ITEM', true);
			$this->dws['dw_mod_arriendo']->set_entrable('REFERENCIA', true);
			$this->dws['dw_item_mod_arriendo']->set_entrable('COD_PRODUCTO', true);
			$this->dws['dw_item_mod_arriendo']->set_entrable('NOM_PRODUCTO', true);
			$this->dws['dw_item_mod_arriendo']->set_entrable('CANTIDAD', true);	
		}
		 else if($tipo_mod_arriendo == 'ELIMINAR'){
		 	$this->dws['dw_mod_arriendo']->set_item(0, 'AGREGAR', '');
			$this->dws['dw_mod_arriendo']->set_item(0, 'AGREGA_ELIMINA', 'Eliminar');
			$this->dws['dw_item_mod_arriendo']->tipo_mod_arriendo = 'ELIMINAR';
			$this->dws['dw_item_mod_arriendo']->set_entrable('AGREGAR_ITEM_MOD_ARRIENDO', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('ORDEN', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('ITEM', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('COD_PRODUCTO', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('NOM_PRODUCTO', false);
			$this->dws['dw_mod_arriendo']->set_entrable('REFERENCIA', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('CANTIDAD_ACTUAL', false);
			$this->dws['dw_item_mod_arriendo']->set_entrable('CANTIDAD', true);
			
			$this->dws['dw_mod_arriendo']->set_item(0, 'DISPLAY_BTN', 'none');
			for ($i=0; $i<$this->dws['dw_item_mod_arriendo']->row_count(); $i++){	
				$this->dws['dw_item_mod_arriendo']->set_item($i, 'DISPLAY_BTN', 'none');
			}
			
		}
	}
	function get_key(){
		return $this->dws['dw_mod_arriendo']->get_item(0, 'COD_MOD_ARRIENDO');
	}
	function save_record($db){
		$cod_mod_arriendo = $this->get_key();
		$cod_arriendo = $this->dws['dw_mod_arriendo']->get_item(0, 'COD_ARRIENDO');
		$cod_estado_mod_arriendo = $this->dws['dw_mod_arriendo']->get_item(0, 'COD_ESTADO_MOD_ARRIENDO');
		
		$referencia = $this->dws['dw_mod_arriendo']->get_item(0, 'REFERENCIA');
		$subtotal = $this->dws['dw_mod_arriendo']->get_item(0, 'SUM_TOTAL');
		$total_neto = $this->dws['dw_mod_arriendo']->get_item(0, 'TOTAL_NETO');
		$porc_iva = $this->dws['dw_mod_arriendo']->get_item(0, 'PORC_IVA');
		$monto_iva = $this->dws['dw_mod_arriendo']->get_item(0, 'MONTO_IVA');
		$total_con_iva = $this->dws['dw_mod_arriendo']->get_item(0, 'TOTAL_CON_IVA');		
		$tipo_mod_arriendo = $this->dws['dw_mod_arriendo']->get_item(0, 'TIPO_MOD_ARRIENDO');
		$obs = $this->dws['dw_mod_arriendo']->get_item(0, 'OBS');
    	
		$cod_mod_arriendo = ($cod_mod_arriendo=='') ? 'null' : $cod_mod_arriendo;
		$obs = ($obs=='') ? 'null' : "'$obs'";
		$subtotal = ($subtotal=='') ? 0 : $subtotal;
		$total_neto = ($total_neto=='') ? 0 : $total_neto;
		$porc_iva = ($porc_iva=='') ? 0 : $porc_iva;
		$monto_iva = ($monto_iva=='') ? 0 : $monto_iva;
		$total_con_iva = ($total_con_iva=='') ? 0 : $total_con_iva;    	
		
    	$sp = 'spu_mod_arriendo';
	    if ($this->is_new_record()){
	    	$operacion = 'INSERT';
	    	$cod_estado_mod_arriendo_old = '';	// no tenia estado
	    }
	    else {
	    	$operacion = 'UPDATE';
			// obtiene el valor anterior del estado (antes de grabar)
			$sql = "select COD_ESTADO_MOD_ARRIENDO
					from MOD_ARRIENDO
					where COD_MOD_ARRIENDO = $cod_mod_arriendo"; 
			$result = $db->build_results($sql);
	    	$cod_estado_mod_arriendo_old = $result[0]['COD_ESTADO_MOD_ARRIENDO'];
	    }
	    $param	= "'$operacion'
					,$cod_mod_arriendo
					,$this->cod_usuario
					,$cod_arriendo
					,$cod_estado_mod_arriendo
					,'$referencia'
					,$subtotal
					,$total_neto
					,$porc_iva
					,$monto_iva
					,$total_con_iva
					,'$tipo_mod_arriendo'
					,$obs";
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_mod_arriendo = $db->GET_IDENTITY();
				$this->dws['dw_mod_arriendo']->set_item(0, 'COD_MOD_ARRIENDO', $cod_mod_arriendo);
			}
			if (!$this->dws['dw_item_mod_arriendo']->update($db, $cod_mod_arriendo))
				return false;
			
			$parametros_sp = "'RECALCULA', $cod_mod_arriendo";
			if (!$db->EXECUTE_SP('spu_mod_arriendo', $parametros_sp))
			return false;
				
			if ($cod_estado_mod_arriendo_old == self::K_MOD_ARRIENDO_EMITIDO && $cod_estado_mod_arriendo == self::K_MOD_ARRIENDO_APROBADO) // se esta aprobando un arriendo
				if (!$db->EXECUTE_SP($sp, "'APROBAR', $cod_mod_arriendo"))
					return false;
				
			return true;
		}
		return false;
	}
}
?>
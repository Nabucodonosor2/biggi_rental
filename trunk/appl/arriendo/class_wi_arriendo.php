<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
require_once(dirname(__FILE__)."/class_print_arriendo.php");
require_once(dirname(__FILE__)."/class_print_anexo_arriendo.php");
require_once(dirname(__FILE__)."/class_print_anexo_arriendo_d.php");
require_once(dirname(__FILE__)."/class_informe_saldo_arriendo.php");

class input_file extends edit_control {
	function input_file($field) {
		parent::edit_control($field);
	}
	function draw_entrable($dato, $record) {
		$field = $this->field.'_'.$record;
		return '<input type="file" name="'.$field.'" id="'.$field.'" class="Button"/>';
	}
	function draw_no_entrable($dato, $record) {
		return '';
	}
}

class dw_arriendo_docs extends datawindow {
	function dw_arriendo_docs() {
		$sql = "SELECT AD.COD_ARRIENDO_DOCS							D_COD_ARRIENDO_DOCS  
    					,NULL										D_COD_ENCRIPT
    					,AD.COD_ARRIENDO							D_COD_ARRIENDO
    					,AD.COD_USUARIO								D_COD_USUARIO
    					,U.NOM_USUARIO								D_NOM_USUARIO
    					,AD.RUTA_ARCHIVO							D_RUTA_ARCHIVO
    					,AD.NOM_ARCHIVO								D_NOM_ARCHIVO
    					,''											ELIMINA_DOC
    					,AD.NOM_ARCHIVO								D_NOM_ARCHIVO_REF
    					,CONVERT(VARCHAR, AD.FECHA_REGISTRO, 103)	D_FECHA_REGISTRO
    					,AD.OBS										D_OBS
    					,NULL										D_FILE
    					,''											D_DIV_LINK
    					,'none'										D_DIV_FILE
				FROM ARRIENDO_DOCS AD, USUARIO U
				WHERE COD_ARRIENDO = {KEY1}
				AND U.COD_USUARIO = AD.COD_USUARIO";
		
		parent::datawindow($sql, 'ARRIENDO_DOCS', true, true);
		$this->add_control(new edit_text_upper('D_OBS',88, 50));
		$this->add_control(new static_text('D_NOM_ARCHIVO'));
		$this->add_control(new input_file('D_FILE'));

		$this->set_mandatory('D_FILE', 'Archivo');
	}
	function draw_field($field, $record) {
		if ($field=='D_FILE') {
			$status = $this->get_status_row($record);
			if ($status==K_ROW_NEW || $status==K_ROW_NEW_MODIFIED) {
				$row = $this->redirect($record);
				$dato = $this->get_item($record, $field);
				return $this->controls[$field]->draw_entrable($dato, $row);
			}
			else 
				return $this->controls[$field]->draw_no_entrable($dato, $row);
		}
		else
			return parent::draw_field($field, $record);
	}
	function retrieve($cod_arriendo) {
		parent::retrieve($cod_arriendo);
		for($i=0; $i<$this->row_count(); $i++) {
			$cod_arriendo = $this->get_item($i, 'D_COD_ARRIENDO_DOCS');
			$this->set_item($i, 'D_COD_ENCRIPT', base64_encode($cod_arriendo));
		}
	}
	function insert_row($row = -1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'D_COD_USUARIO', $this->cod_usuario);
		$this->set_item($row, 'D_NOM_USUARIO', $this->nom_usuario);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$this->set_item($row, 'D_FECHA_REGISTRO', $db->current_date());
		$this->set_item($row, 'D_FILE', 'ARR_ARCHIVO_'.$this->redirect($row));
		$this->set_item($row, 'D_DIV_LINK', 'none');
		$this->set_item($row, 'D_DIV_FILE', '');
		return $row;
	}
	
	function get_ruta($cod_arriendo) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT YEAR(A.FECHA_ARRIENDO) ANO
						,UPPER(M.NOM_MES) NOM_MES
						,REPLACE(CONVERT(VARCHAR, A.FECHA_ARRIENDO, 103), '/', '-') FECHA
						,P.VALOR RUTA
				FROM ARRIENDO A, MES M, PARAMETRO P
				WHERE A.COD_ARRIENDO = $cod_arriendo
				AND M.COD_MES = month(A.FECHA_ARRIENDO)
  				AND P.COD_PARAMETRO = 66";	// RUTA DOCS ARRIENDO (definir)
      	$result = $db->build_results($sql);
      	$folder = $result[0]['RUTA'].$result[0]['ANO']."/".$result[0]['NOM_MES']."/".$result[0]['FECHA']."/".$cod_arriendo."/";
		if (!file_exists($folder))	
			$res = mkdir($folder, 0777 , true);	// recursive = true		
			
		return $folder;
	}
	function update($db, $cod_arriendo)	{
		$sp = 'spu_arriendo_docs';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;			

			if ($statuts == K_ROW_NEW_MODIFIED) {
				$operacion = 'INSERT';
				$cod_arriendo_docs = 'null';
				$cod_usuario = $this->cod_usuario;

				// subir archivo
				$ruta_archivo = $this->get_ruta($cod_arriendo);	// obtiene la ruta donde debe quesdar 

				// direccion absoluta
				$row = $this->redirect($i);
				$file = 'D_FILE_'.$row;
				$nom_archivo = $_FILES[$file]['name'];
				$char = '';
				$pos  = 0;
				$nom_archivo_s='';
				/*
				 * Si el nombre del archivo tiene mas de 94 caracteres
				 * busca el ultimo punto para extraer los caracteres antes de la extension para
				 * acortar el nombre del archivo
				 */
	
				if(strlen($nom_archivo) > 94){
					for($j=0 ; $j < strlen($nom_archivo) ; $j++){
						$char = substr($nom_archivo, $j, 1);
						if($char == '.')
							$pos = $j;
					}
					$nom_archivo_s = substr($nom_archivo, 0, 90); //nombre archivo sin extension truncado
					$nom_archivo   = substr($nom_archivo, $pos, strlen($nom_archivo)); //la extension
					
					$nom_archivo = $nom_archivo_s.$nom_archivo;
				}
				$e		= array(archivo::getTipoArchivo($nom_archivo));
				$t		= $_FILES[$file]['size'];
				$tmp	= $_FILES[$file]['tmp_name'];

				$archivo = new archivo($nom_archivo, $ruta_archivo, $e,$t,$tmp);
			 	$u = $archivo->upLoadFile();	// sube el archivo al directorio definitivo
			}
			elseif ($statuts == K_ROW_MODIFIED) {
				$operacion = 'UPDATE';
				$cod_arriendo_docs = $this->get_item($i, 'D_COD_ARRIENDO_DOCS');
				$cod_usuario = $this->cod_usuario;
				$nom_archivo = $this->get_item($i, 'D_NOM_ARCHIVO');
				$ruta_archivo = $this->get_item($i, 'D_RUTA_ARCHIVO');
			}			
			$obs = $this->get_item($i, 'D_OBS');
			
			$obs = $obs =='' ? 'null' : "'$obs'";

			$param = "'$operacion'
					,$cod_arriendo_docs 
					,$cod_arriendo 
					,$cod_usuario
					,'$ruta_archivo'
					,'$nom_archivo'
					,$obs";
				
			if (!$db->EXECUTE_SP($sp, $param))
				return false;	
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');			
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
			
			$cod_arriendo_docs = $this->get_item($i, 'D_COD_ARRIENDO_DOCS', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_arriendo_docs"))
				return false;
				
			$ruta_archivo = $this->get_item($i, 'D_RUTA_ARCHIVO', 'delete');
			$nom_archivo = $this->get_item($i, 'D_NOM_ARCHIVO', 'delete');
			
			if (file_exists($ruta_archivo.$nom_archivo))
				unlink($ruta_archivo.$nom_archivo);		
		}			
		return true;
	}
}

class dw_oca_orden_compra extends datawindow {
	function dw_oca_orden_compra() {
		$sql = "select convert(varchar, OC.COD_ORDEN_COMPRA)+'|'+convert(varchar, OC.COD_ORDEN_COMPRA) COD_ORDEN_COMPRA,
						dbo.f_format_date(OC.FECHA_ORDEN_COMPRA, 1) FECHA_ORDEN_COMPRA,
						E.NOM_EMPRESA OC_NOM_EMPRESA,
						OC.COD_ESTADO_ORDEN_COMPRA,
						OC.TOTAL_NETO OC_TOTAL_NETO,
						OC.MONTO_IVA OC_MONTO_IVA,
						OC.TOTAL_CON_IVA OC_TOTAL_CON_IVA,
						CASE OC.COD_ESTADO_ORDEN_COMPRA
							WHEN 2 
							THEN 'NULA'							 
						END ANULADA,
						case OC.COD_ESTADO_ORDEN_COMPRA
							when 2 then 0
							else OC.TOTAL_NETO
						end OC_NETO_SUMA, 
						case OC.COD_ESTADO_ORDEN_COMPRA
							when 2 then 0
							else OC.MONTO_IVA
						end OC_IVA_SUMA, 
						case OC.COD_ESTADO_ORDEN_COMPRA
							when 2 then 0
							else OC.TOTAL_CON_IVA
						end OC_TOTAL_SUMA
				from ORDEN_COMPRA OC, EMPRESA E, ESTADO_ORDEN_COMPRA EOC
				where E.COD_EMPRESA = OC.COD_EMPRESA 
				and OC.COD_DOC = {KEY1}
				and OC.TIPO_ORDEN_COMPRA = 'ARRIENDO'
				and OC.COD_ESTADO_ORDEN_COMPRA <> 1 
				and E.COD_EMPRESA = OC.COD_EMPRESA
				and OC.COD_ESTADO_ORDEN_COMPRA = EOC.COD_ESTADO_ORDEN_COMPRA";
		
		parent::datawindow($sql, "ORDEN_COMPRA");	
		
		$this->add_control(new static_link('COD_ORDEN_COMPRA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=arriendo&modulo_destino=orden_compra&cod_modulo_destino=[COD_ORDEN_COMPRA]&cod_item_menu=2040&current_tab_page=5'));
		$this->add_control(new static_text('OC_NOM_EMPRESA'));
		$this->add_control(new static_num('OC_TOTAL_NETO', 0));
		$this->add_control(new static_num('OC_MONTO_IVA', 0));
		$this->add_control(new static_num('OC_TOTAL_CON_IVA', 0));
		
		$this->add_control(new edit_text('COD_ESTADO_ORDEN_COMPRA',10,10, 'hidden'));

		$this->accumulate('OC_NETO_SUMA', '', false);
		$this->accumulate('OC_IVA_SUMA', '', false);
		$this->accumulate('OC_TOTAL_SUMA', '', false);
	}
}


class dw_lista_guia_despacho extends datawindow {
	const K_TIPO_GB = 5;
	const K_ESTADO_SII_IMPRESA = 2;
	const K_ESTADO_SII_ENVIADA = 3;
	
	function dw_lista_guia_despacho() {
		$sql = "SELECT CONVERT(VARCHAR, G.NRO_GUIA_DESPACHO)+'|'+CONVERT(VARCHAR, G.COD_GUIA_DESPACHO) NRO_GUIA_DESPACHO
						,CONVERT(VARCHAR(20),G.FECHA_GUIA_DESPACHO, 103)FECHA_GUIA_DESPACHO
						,G.REFERENCIA
				FROM GUIA_DESPACHO G, MOD_ARRIENDO M
				WHERE G.COD_TIPO_GUIA_DESPACHO =  ".self::K_TIPO_GB."
				AND G.COD_ESTADO_DOC_SII IN (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")
				AND M.COD_MOD_ARRIENDO = G.COD_DOC
				AND M.COD_ARRIENDO = {KEY1}
				GROUP BY G.NRO_GUIA_DESPACHO, G.COD_GUIA_DESPACHO, FECHA_GUIA_DESPACHO, G.REFERENCIA
				ORDER BY G.FECHA_GUIA_DESPACHO DESC";/*7746*/
				
		parent::datawindow($sql, 'GD_RELACIONADA');

		$this->add_control(new static_link('NRO_GUIA_DESPACHO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=arriendo&modulo_destino=guia_despacho&cod_modulo_destino=[NRO_GUIA_DESPACHO]&cod_item_menu=1525&current_tab_page=7'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.GD_SEPARADOR', '-');
	}
}
class dw_lista_guia_recepcion extends datawindow {
	const K_TIPO_GR	= 4;
	
	function dw_lista_guia_recepcion() {
		$sql = "SELECT CONVERT(VARCHAR, G.COD_GUIA_RECEPCION) NRO_GUIA_RECEPCION
						,CONVERT(VARCHAR(20),G.FECHA_GUIA_RECEPCION, 103)FECHA_GUIA_RECEPCION
						,G.OBS REFERENCIA
				FROM GUIA_RECEPCION G, MOD_ARRIENDO M
				WHERE G.COD_TIPO_GUIA_RECEPCION = ".self::K_TIPO_GR."       
				AND G.TIPO_DOC = 'MOD_ARRIENDO'
				AND M.COD_MOD_ARRIENDO = G.COD_DOC
				AND M.COD_ARRIENDO = {KEY1}";/*7652*/
				
		parent::datawindow($sql, 'GR_RELACIONADA');

		$this->add_control(new static_link('NRO_GUIA_RECEPCION', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=arriendo&modulo_destino=guia_recepcion&cod_modulo_destino=[NRO_GUIA_RECEPCION]&cod_item_menu=1530&current_tab_page=7'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.GR_SEPARADOR', '-');
	}
}
class dw_lista_factura extends datawindow {
	const K_TIPO_FA= 2;
	const K_ESTADO_SII_IMPRESA = 2;
	const K_ESTADO_SII_ENVIADA = 3;
		
	function dw_lista_factura() {
		$sql = "select convert(varchar, f.NRO_FACTURA)+'|'+convert(varchar, f.COD_FACTURA) NRO_FACTURA
						,CONVERT(VARCHAR(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
						,F.REFERENCIA
						,F.TOTAL_NETO TOTAL_NETO_LF
						,F.MONTO_IVA MONTO_IVA_LF
						,F.TOTAL_CON_IVA TOTAL_CON_IVA_LF
						,dbo.f_fa_saldo(f.COD_FACTURA) SALDO_LF  
				 from ITEM_FACTURA i
				 		, FACTURA F 
		 		where i.COD_ITEM_DOC = {KEY1} 
		 		and i.TIPO_DOC = 'ARRIENDO'
				and f.COD_FACTURA = i.COD_FACTURA
				and f.COD_TIPO_FACTURA = ".self::K_TIPO_FA."
				and f.COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")
				group by F.COD_FACTURA, NRO_FACTURA, FECHA_FACTURA, REFERENCIA, TOTAL_NETO, MONTO_IVA, TOTAL_CON_IVA
				order by F.FECHA_FACTURA DESC"; /*6006*/
		

		$this->add_control(new static_num('TOTAL_NETO_LF', 0));
		$this->add_control(new static_num('MONTO_IVA_LF', 0));
		$this->add_control(new static_num('TOTAL_CON_IVA_LF', 0));
		$this->add_control(new static_num('SALDO_LF', 0));

		parent::datawindow($sql, 'FA_RELACIONADA');
		$this->add_control(new static_link('NRO_FACTURA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=arriendo&modulo_destino=factura&cod_modulo_destino=[NRO_FACTURA]&cod_item_menu=1535&current_tab_page=7'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.FA_SEPARADOR', '-');
	}
}
class dw_lista_nota_credito extends datawindow {
	const K_TIPO_NC			   = 2;
	const K_ESTADO_SII_IMPRESA = 2;
	const K_ESTADO_SII_ENVIADA = 3;

	function dw_lista_nota_credito() {
		$sql ="SELECT CONVERT(VARCHAR, N.NRO_NOTA_CREDITO)+'|'+CONVERT(VARCHAR, N.COD_NOTA_CREDITO) NRO_NOTA_CREDITO
					,CONVERT(VARCHAR(20),N.FECHA_NOTA_CREDITO, 103) FECHA_NOTA_CREDITO
					,N.REFERENCIA
					,N.TOTAL_NETO
			FROM NOTA_CREDITO N, ITEM_FACTURA I, FACTURA F 
			WHERE N.COD_ESTADO_DOC_SII IN (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.") 
			AND F.COD_FACTURA = N.COD_DOC 
			AND I.COD_ITEM_DOC = {KEY1}
			AND I.TIPO_DOC = 'ARRIENDO'
			AND F.COD_FACTURA = I.COD_FACTURA
			AND F.COD_TIPO_FACTURA = ".self::K_TIPO_NC."
			AND F.COD_ESTADO_DOC_SII IN (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")
			GROUP BY N.COD_NOTA_CREDITO, NRO_NOTA_CREDITO, FECHA_NOTA_CREDITO, N.REFERENCIA, N.TOTAL_NETO
			ORDER BY N.FECHA_NOTA_CREDITO DESC";/*7509*/
		parent::datawindow($sql, 'NC_RELACIONADA');

		$this->add_control(new static_link('NRO_NOTA_CREDITO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=arriendo&modulo_destino=nota_credito&cod_modulo_destino=[NRO_NOTA_CREDITO]&cod_item_menu=1540&current_tab_page=7'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.NC_SEPARADOR', '-');
	}
}
class dw_lista_cheques extends datawindow {
	function dw_lista_cheques() {
		
		$sql ="SELECT CONVERT (VARCHAR(10),CH.FECHA_DOC,103) FECHA_DOC
					,CH.NRO_DOC
					,CH.COD_BANCO
					,B.NOM_BANCO
					,(SELECT NOM_PLAZA FROM PLAZA WHERE COD_PLAZA = CH.COD_PLAZA) NOM_PLAZA
					,CH.MONTO_DOC
					,CONVERT(VARCHAR(10),GETDATE(),103) FECHA_DEPOSITO
					,'0' NRO_INGRESO
			FROM  ARRIENDO A,INGRESO_ARRIENDO IA, INGRESO_CHEQUE IC, CHEQUE CH LEFT OUTER JOIN BANCO B ON CH.COD_BANCO = B.COD_BANCO
			WHERE A.COD_ARRIENDO = {KEY1}
			AND A.COD_ARRIENDO = IA.COD_ARRIENDO
			AND IA.COD_INGRESO_CHEQUE = IC.COD_INGRESO_CHEQUE
			AND IC.COD_INGRESO_CHEQUE = CH.COD_INGRESO_CHEQUE
			AND IC.COD_ESTADO_INGRESO_CHEQUE = 2";
		parent::datawindow($sql, 'CHEQUE');
		
		$this->add_control(new static_num('MONTO_DOC'));
		//$this->add_control(new static_link('NRO_INGRESO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=arriendo&modulo_destino=nota_credito&cod_modulo_destino=[NRO_NOTA_CREDITO]&cod_item_menu=1540&current_tab_page=7'));
	}
}
class dw_item_arriendo extends dw_item {

	function dw_item_arriendo() {
		$sql = "select COD_ITEM_ARRIENDO
						,COD_ARRIENDO
						,ORDEN
						,ITEM
						,COD_PRODUCTO
						,NOM_PRODUCTO
						,CANTIDAD
						,PRECIO
						,PRECIO_VENTA
						,COD_TIPO_TE
						,MOTIVO_TE
				from ITEM_ARRIENDO
				where COD_ARRIENDO = {KEY1}
				order by ORDEN";
		parent::dw_item($sql, 'ITEM_ARRIENDO', true, true, 'COD_PRODUCTO');

		$this->add_control(new edit_text('COD_ITEM_ARRIENDO',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text('ITEM',4 , 5));
		$this->add_control(new edit_cantidad('CANTIDAD',12,10));
		$this->add_control(new computed('PRECIO_VENTA'), 0);
		$this->add_control(new computed('PRECIO', 0));
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL');
		$this->set_computed('TOTAL_VENTA', '[CANTIDAD] * [PRECIO_VENTA]');
		$this->accumulate('TOTAL_VENTA', "calc_adicional();");
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		$this->add_control(new edit_text('COD_TIPO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('MOTIVO_TE',10, 100, 'hidden'));		
		
		$this->set_first_focus('COD_PRODUCTO');

		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Cï¿½digo del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		$this->set_item($row, 'ITEM', $this->row_count());
		return $row;
	}
	function update($db, $cod_arriendo)	{
		$sp = 'spu_item_arriendo';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_item_arriendo 		= $this->get_item($i, 'COD_ITEM_ARRIENDO');
			$orden 					= $this->get_item($i, 'ORDEN');
			$item 					= $this->get_item($i, 'ITEM');
			$cod_producto 			= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 			= $this->get_item($i, 'NOM_PRODUCTO');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');
			$precio 				= $this->get_item($i, 'PRECIO');
			$precio_venta 			= $this->get_item($i, 'PRECIO_VENTA');
			$cod_tipo_te 			= $this->get_item($i, 'COD_TIPO_TE');
			$motivo_te				= $this->get_item($i, 'MOTIVO_TE');
			
			$cod_item_arriendo = ($cod_item_arriendo=='') ? "null" : $cod_item_arriendo;
			$cod_tipo_te = ($cod_tipo_te=='') ? "null" : $cod_tipo_te;
			$motivo_te = ($motivo_te=='') ? "null" : "'$motivo_te'";
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion'
						,$cod_item_arriendo
						,$cod_arriendo
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
				
			$cod_item_arriendo = $this->get_item($i, 'COD_ITEM_ARRIENDO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_item_arriendo")){
				return false;
			}
		}
		//Ordernar
		if ($this->row_count() > 0) {
			$parametros_sp = "'ITEM_ARRIENDO','ARRIENDO', $cod_arriendo";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
		}
		return true;
	}
}
class dw_mod_arriendo2 extends datawindow {
	// Se renombra para evitar que tenga el mimso nombre la clase que en MOD_ARRIENDO
	function dw_mod_arriendo2() {
		$sql = "SELECT MA.TIPO_MOD_ARRIENDO 	MA_TIPO_MOD_ARRIENDO
					,MA.COD_MOD_ARRIENDO 	MA_COD_MOD_ARRIENDO
					,convert(varchar, MA.FECHA_MOD_ARRIENDO, 103) MA_FECHA_MOD_ARRIENDO
					,MA.REFERENCIA			MA_REFERENCIA
					,CASE MA.TIPO_MOD_ARRIENDO
					WHEN 'AGREGAR'  THEN dbo.f_mod_porc_despachado(MA.COD_MOD_ARRIENDO)
					WHEN 'ELIMINAR' THEN dbo.f_mod_porc_recepcionado(MA.COD_MOD_ARRIENDO)
					ELSE -1
					END PORC_ARRIENDO		
			from MOD_ARRIENDO MA 
			where MA.COD_ARRIENDO = {KEY1}
			order by MA.COD_MOD_ARRIENDO";
		parent::datawindow($sql, 'MOD_ARRIENDO');
		
		$this->add_control(new static_link('MA_COD_MOD_ARRIENDO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=arriendo&modulo_destino=mod_arriendo&cod_modulo_destino=[MA_COD_MOD_ARRIENDO]&cod_item_menu=2015&current_tab_page=3'));
	}
}
class dw_arriendo extends dw_help_empresa {
	const K_EMITIDA = 1;
	const K_PARAM_NRO_MESES = 46;
	const K_PARAM_PORC_RECUPERACION = 47;
	const K_PARAM_PORC_ARRIENDO = 48;
	const K_PARAM_MIN_PORC_ARRIENDO = 49;
	const K_PARAM_MAX_PORC_ARRIENDO = 50;

	function dw_arriendo() {
		$sql = "select	A.COD_ARRIENDO
						,A.COD_ARRIENDO COD_ARRIENDO_H
						,convert(varchar(20), A.FECHA_ARRIENDO, 103) FECHA_ARRIENDO
						,A.COD_USUARIO
						,U.NOM_USUARIO
						,A.COD_USUARIO_VENDEDOR1
						,A.NRO_ORDEN_COMPRA
						,A.REFERENCIA
						,A.CENTRO_COSTO_CLIENTE
						,A.COD_ESTADO_ARRIENDO
						,A.COD_COT_ARRIENDO
						,convert(varchar(20), A.FECHA_ENTREGA, 103) FECHA_ENTREGA
						,A.COD_EMPRESA
						,E.ALIAS
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,E.GIRO
						,A.COD_SUCURSAL
						,dbo.f_get_direccion('SUCURSAL', A.COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_SUCURSAL
						,A.COD_PERSONA
						,dbo.f_emp_get_mail_cargo_persona(A.COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA
						,A.NOM_ARRIENDO
						,A.UBICACION_DIRECCION      
						,A.UBICACION_COMUNA     
						,A.UBICACION_CIUDAD     
						,A.EJECUTIVO_CONTACTO       
						,A.EJECUTIVO_TELEFONO       
						,A.EJECUTIVO_MAIL           
						,A.NRO_MESES
						,A.PORC_ARRIENDO
						,dbo.f_get_parametro(".self::K_PARAM_MIN_PORC_ARRIENDO.") MIN_PORC_ARRIENDO
						,dbo.f_get_parametro(".self::K_PARAM_MAX_PORC_ARRIENDO.") MAX_PORC_ARRIENDO
						,A.PORC_ADICIONAL_RECUPERACION
						,A.MONTO_ADICIONAL_RECUPERACION
						,A.SUBTOTAL SUM_TOTAL
						,A.TOTAL_NETO
						,A.PORC_IVA
						,A.MONTO_IVA
						,A.TOTAL_CON_IVA
						,A.COD_BODEGA
						,B.NOM_BODEGA
						,A.OBS
						,dbo.f_arr_total_actual(A.COD_ARRIENDO,getdate()) TOTAL_ACTUAL
						,VIGENCIA_ARRIENDO
						,'' APROBAR_SIN_CHEQUE_H
						,EXIGE_CHEQUE
						,0 F_VA_TOTAL
						,0 F_VA_TOTAL_VALOR_VENTA_INICIAL
						,0 F_VA_TOTAL_ARRIENDO_ACTUAL
						,0 F_VA_TOTAL_VALOR_VENTA_ACTUAL
				from 	ARRIENDO A left outer join BODEGA B ON B.COD_BODEGA = A.COD_BODEGA, USUARIO U, EMPRESA E
				where	A.COD_ARRIENDO = {KEY1} 
				  and	U.COD_USUARIO = A.COD_USUARIO
				  and	E.COD_EMPRESA = A.COD_EMPRESA";

		parent::dw_help_empresa($sql);
		
		//DATOS GENERALES
		$sql_usuario_vendedor =  "select 	COD_USUARIO
											,NOM_USUARIO
											,PORC_PARTICIPACION
									from USUARIO
									order by NOM_USUARIO asc";
		$this->add_control(new drop_down_dw('COD_USUARIO_VENDEDOR1',$sql_usuario_vendedor,110));
		$this->add_control(new edit_text_upper('NRO_ORDEN_COMPRA', 30, 100));
		$this->add_control(new edit_text_upper('REFERENCIA', 100, 100));
		$this->add_control(new edit_text_upper('CENTRO_COSTO_CLIENTE', 30, 100));
		$sql = "select COD_ESTADO_ARRIENDO
						,NOM_ESTADO_ARRIENDO
				from ESTADO_ARRIENDO
				order by COD_ESTADO_ARRIENDO";
		$this->add_control(new drop_down_dw('COD_ESTADO_ARRIENDO',$sql));
		$this->add_control($control = new edit_date('FECHA_ENTREGA'));
		$control->set_onChange("valida_fecha_entrega(this);");
		
		$this->add_control(new edit_text_hidden('COD_ARRIENDO_H'));
		$this->add_control(new edit_text_hidden('APROBAR_SIN_CHEQUE_H'));
		$this->add_control(new edit_check_box('EXIGE_CHEQUE', 'S', 'N'));
		//OBSERVACIONES 
		
		$this->add_control(new edit_text_multiline('OBS',54,4));
		

		// DIRECCION
		$this->add_control(new drop_down_sucursal('COD_SUCURSAL'));
		$this->add_control(new static_text('DIRECCION_SUCURSAL'));
		$this->add_control(new drop_down_persona('COD_PERSONA'));
		
		// DOCUMENTOS *****************
		
		// DATOS CONTRATO
		$this->add_control(new edit_text_upper('NOM_ARRIENDO', 80, 100));
		$this->add_control(new edit_text_upper('UBICACION_DIRECCION', 80, 100));
		$this->add_control(new edit_text_upper('UBICACION_CIUDAD', 80, 100));	
		$this->add_control(new edit_text_upper('UBICACION_COMUNA', 80, 100));			
		$this->add_control(new edit_text_upper('EJECUTIVO_CONTACTO', 80, 100));
		$this->add_control(new edit_text_upper('EJECUTIVO_TELEFONO', 20, 100));
		$this->add_control(new edit_mail('EJECUTIVO_MAIL', 30, 100));


		// Totales
		$this->add_control(new edit_num('NRO_MESES', 3,3));
		$this->add_control(new edit_porcentaje('PORC_ADICIONAL_RECUPERACION'));
		$this->add_control($control = new edit_porcentaje('PORC_ARRIENDO'));
		$control->set_onChange("valida_porc_arriendo(this);");
		$this->add_control(new edit_text('MIN_PORC_ARRIENDO',10, 10, 'hidden'));
		$this->add_control(new edit_text('MAX_PORC_ARRIENDO',10, 10, 'hidden'));
		
		// total contrato actual tab de stock
		$this->add_control(new static_num('TOTAL_ACTUAL'));	
				
		//$this->set_computed('MONTO_ADICIONAL_RECUPERACION', '[SUM_TOTAL] * [PORC_ADICIONAL_RECUPERACION] / 100');
		$this->set_computed('MONTO_ADICIONAL_RECUPERACION', '(([SUM_TOTAL] * 100)/[PORC_ARRIENDO]) * [PORC_ADICIONAL_RECUPERACION] / 100');
		$this->set_computed('TOTAL_NETO', '[SUM_TOTAL]');
		$this->add_control(new drop_down_iva());
		// Elimina la opciï¿½n de IVA= 0
		unset($this->controls['PORC_IVA']->aValues[1]);
		unset($this->controls['PORC_IVA']->aLabels[1]);

		$this->set_computed('MONTO_IVA', '[TOTAL_NETO] * [PORC_IVA] / 100');
		$this->set_computed('TOTAL_CON_IVA', '[TOTAL_NETO] + [MONTO_IVA]');
		
		$this->set_mandatory('NOM_ARRIENDO', 'Nombre contrato');
		$this->set_mandatory('COD_USUARIO_VENDEDOR1', 'Vendedor');
		$this->set_mandatory('REFERENCIA', 'Referencia');
		//$this->set_mandatory('FECHA_ENTREGA', 'Fecha de entrega');
		$this->set_mandatory('COD_EMPRESA', 'Cliente');
		$this->set_mandatory('COD_SUCURSAL', 'Direcciï¿½n');
		$this->set_mandatory('COD_PERSONA', 'Atenciï¿½n a');
		$this->set_mandatory('REFERENCIA', 'Referencia');
		$this->set_mandatory('NRO_MESES', 'Nï¿½mero de meses');
		$this->set_mandatory('PORC_ARRIENDO', 'Porcentaje arriendo');
		$this->set_mandatory('UBICACION_DIRECCION', 'Direcciï¿½n arriendo');
	}
	function new_arriendo($cod_cot_arriendo = NULL) {
		 $this->insert_row();

		$this->set_item(0, 'FECHA_ARRIENDO', $this->current_date());
		$this->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->set_item(0, 'COD_USUARIO_VENDEDOR1',$this->cod_usuario);
		$this->set_item(0, 'VIGENCIA_ARRIENDO', 'NO VIGENTE');
		
		$this->set_item(0, 'COD_ESTADO_ARRIENDO', self::K_EMITIDA);
		$this->set_entrable('COD_ESTADO_ARRIENDO', false);
		
		if($cod_cot_arriendo == ''){
			$this->set_item(0, 'NRO_MESES', $this->get_parametro(self::K_PARAM_NRO_MESES));
			$this->set_item(0, 'PORC_ADICIONAL_RECUPERACION', $this->get_parametro(self::K_PARAM_PORC_RECUPERACION));
			$this->set_item(0, 'PORC_ARRIENDO', $this->get_parametro(self::K_PARAM_PORC_ARRIENDO));
		}else{
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			
			$sql = "SELECT NRO_MESES
						  ,PORC_ADICIONAL_RECUPERACION
						  ,PORC_ARRIENDO
					FROM COT_ARRIENDO
					WHERE COD_COT_ARRIENDO = $cod_cot_arriendo";
			$result = $db->build_results($sql);
			
			$this->set_item(0, 'NRO_MESES', $result[0]['NRO_MESES']);
			$this->set_item(0, 'PORC_ADICIONAL_RECUPERACION', $result[0]['PORC_ADICIONAL_RECUPERACION']);
			$this->set_item(0, 'PORC_ARRIENDO', $result[0]['PORC_ARRIENDO']);
		}
		
		$this->set_item(0, 'MIN_PORC_ARRIENDO', $this->get_parametro(self::K_PARAM_MIN_PORC_ARRIENDO));
		$this->set_item(0, 'MAX_PORC_ARRIENDO', $this->get_parametro(self::K_PARAM_MAX_PORC_ARRIENDO));
		
		$this->set_item(0, 'EXIGE_CHEQUE', 'N');
	}
}
class dw_arriendo_stock extends datawindow {
	function dw_arriendo_stock() {
		$sql = "select distinct I.COD_PRODUCTO ST_COD_PRODUCTO
						,P.NOM_PRODUCTO ST_NOM_PRODUCTO
						,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) ST_CANTIDAD
						,I.PRECIO ST_PRECIO
						,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) * I.PRECIO ST_TOTAL
				from ITEM_MOD_ARRIENDO I, MOD_ARRIENDO M, ARRIENDO A, PRODUCTO P
				WHERE M.COD_ARRIENDO = {KEY1}
				  AND I.COD_MOD_ARRIENDO = M.COD_MOD_ARRIENDO
				  AND A.COD_ARRIENDO = M.COD_ARRIENDO
				  AND I.COD_PRODUCTO = P.COD_PRODUCTO
				  AND DBO.F_BODEGA_STOCK(I.COD_PRODUCTO, A.COD_BODEGA, GETDATE()) > 0
				  order by ST_COD_PRODUCTO";
		parent::datawindow($sql, 'ARRIENDO_STOCK');
		
		$this->add_control(new static_num('ST_CANTIDAD', 1));
		$this->add_control(new static_num('ST_PRECIO'));
		$this->add_control(new static_num('ST_TOTAL'));
	}
}

class dw_arriendo_valor_actual extends datawindow {
	function dw_arriendo_valor_actual() {
		$sql = "select distinct I.COD_PRODUCTO VA_COD_PRODUCTO
						,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) VA_CANTIDAD
						,I.PRECIO VA_PRECIO
						,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) * I.PRECIO VA_TOTAL
						,(I.PRECIO*100)/A.PORC_ARRIENDO VA_VALOR_VENTA_INICIAL
						,((I.PRECIO*100)/A.PORC_ARRIENDO) * dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) VA_TOTAL_VALOR_VENTA_INICIAL
						,(P.PRECIO_VENTA_PUBLICO*A.PORC_ARRIENDO)/100	VA_VALOR_ARRIENDO_ACTUAL
						,((P.PRECIO_VENTA_PUBLICO*A.PORC_ARRIENDO)/100)*dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) VA_TOTAL_ARRIENDO_ACTUAL
						,P.PRECIO_VENTA_PUBLICO VA_PRECIO_VENTA_PUBLICO
						,P.PRECIO_VENTA_PUBLICO * dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) VA_TOTAL_VALOR_VENTA_ACTUAL
				from ITEM_MOD_ARRIENDO I
					,MOD_ARRIENDO M
					,ARRIENDO A
					,PRODUCTO P
				WHERE M.COD_ARRIENDO = {KEY1}
				AND I.COD_MOD_ARRIENDO = M.COD_MOD_ARRIENDO
				AND A.COD_ARRIENDO = M.COD_ARRIENDO
				AND I.COD_PRODUCTO = P.COD_PRODUCTO
				AND DBO.F_BODEGA_STOCK(I.COD_PRODUCTO, A.COD_BODEGA, GETDATE()) > 0
				order by VA_COD_PRODUCTO";
		parent::datawindow($sql, 'ARRIENDO_VALOR_ACTUAL');
		
		$this->add_control(new static_num('VA_CANTIDAD', 1));
		$this->add_control(new static_num('VA_PRECIO'));
		$this->add_control(new static_num('VA_TOTAL'));
		$this->add_control(new static_num('VA_VALOR_VENTA_INICIAL'));
		$this->add_control(new static_num('VA_TOTAL_VALOR_VENTA_INICIAL'));
		$this->add_control(new static_num('VA_VALOR_ARRIENDO_ACTUAL'));
		$this->add_control(new static_num('VA_TOTAL_ARRIENDO_ACTUAL'));
		$this->add_control(new static_num('VA_PRECIO_VENTA_PUBLICO'));
		$this->add_control(new static_num('VA_TOTAL_VALOR_VENTA_ACTUAL'));
	}

	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);

		if($record % 2 == 0){
			$temp->setVar($this->label_record.'.CSS_STYLE1_UNO', '#b0c6e5');
			$temp->setVar($this->label_record.'.CSS_STYLE1_DOS', '#f7cbae');
			$temp->setVar($this->label_record.'.CSS_STYLE1_TRES', '#c5e0b5');

			$temp->setVar($this->label_record.'.CSS_STYLE2_UNO', '#b0c6e5');
			$temp->setVar($this->label_record.'.CSS_STYLE2_DOS', '#f7cbae');
			$temp->setVar($this->label_record.'.CSS_STYLE2_TRES', '#c5e0b5');
		}else{
			$temp->setVar($this->label_record.'.CSS_STYLE1_UNO', '#dae1f3');
			$temp->setVar($this->label_record.'.CSS_STYLE1_DOS', '#fce4d7');
			$temp->setVar($this->label_record.'.CSS_STYLE1_TRES', '#e2efdb');

			$temp->setVar($this->label_record.'.CSS_STYLE2_UNO', '#dae1f3');
			$temp->setVar($this->label_record.'.CSS_STYLE2_DOS', '#fce4d7');
			$temp->setVar($this->label_record.'.CSS_STYLE2_TRES', '#e2efdb');
		}
	}
}

class wi_arriendo extends w_cot_nv {
	const K_ARRIENDO_EMITIDO = 1;
	const K_ARRIENDO_APROBADO = 2;
	const K_ARRIENDO_ANULADO = 3;
	const K_APROBAR_SIN_CHEQUES = '997015';
	
	function wi_arriendo($cod_item_menu) {
		parent::w_cot_nv('arriendo', $cod_item_menu);

		$this->dws['dw_arriendo'] = new dw_arriendo();
		$this->dws['dw_item_arriendo'] = new dw_item_arriendo();
		$this->dws['dw_mod_arriendo'] = new dw_mod_arriendo2();
		$this->dws['dw_arriendo_stock'] = new dw_arriendo_stock();
		$this->dws['dw_arriendo_docs'] = new dw_arriendo_docs();
		$this->dws['dw_arriendo_valor_actual'] = new dw_arriendo_valor_actual();
		
		////////*****LISTAS
		$this->dws['dw_oca_orden_compra'] = new dw_oca_orden_compra();
		$this->dws['dw_lista_guia_despacho'] = new dw_lista_guia_despacho();
		$this->dws['dw_lista_guia_recepcion'] = new dw_lista_guia_recepcion();
		$this->dws['dw_lista_factura'] = new dw_lista_factura();
		$this->dws['dw_lista_nota_credito'] = new dw_lista_nota_credito();
		$this->dws['dw_lista_cheques'] = new dw_lista_cheques();
		$this->set_first_focus('REFERENCIA');
		
		$priv = $this->get_privilegio_opcion_usuario('998505', $this->cod_usuario);
		if ($priv == 'E')
			$this->dws['dw_arriendo']->set_entrable('EXIGE_CHEQUE', true);	
		else
			$this->dws['dw_arriendo']->set_entrable('EXIGE_CHEQUE', false);
		
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('NOM_ARRIENDO');
		$this->add_auditoria('UBICACION_DIRECCION');
		$this->add_auditoria('UBICACION_CIUDAD');
		$this->add_auditoria('UBICACION_COMUNA');
		$this->add_auditoria('EJECUTIVO_CONTACTO');
		$this->add_auditoria('EJECUTIVO_TELEFONO');
		$this->add_auditoria('EJECUTIVO_MAIL');
		$this->add_auditoria('EXIGE_CHEQUE');
	}
	function new_record(){
		if (session::is_set('CREADA_DESDE_COT_ARR')) {
			$cod_cot_arriendo = session::get('CREADA_DESDE_COT_ARR');			
			$this->creada_desde_cot_arr($cod_cot_arriendo);
			session::un_set('CREADA_DESDE_COT_ARR');	
			return;	
		}
		$this->dws['dw_arriendo']->new_arriendo();
	}
	function habilita_boton_print(&$temp, $boton, $habilita) {
		if ($habilita){
			$ruta_over = "'../../../../commonlib/trunk/images/b_print_over.jpg'";
			$ruta_out = "'../../../../commonlib/trunk/images/b_print.jpg'";
			$ruta_click = "'../../../../commonlib/trunk/images/b_print_click.jpg'";
			$temp->setVar("WI_PRINT", '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
												 'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../../../commonlib/trunk/images/b_print.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
												 'onClick="var vl_tab = document.getElementById(\'wi_current_tab_page\'); if (TabbedPanels1 && vl_tab) vl_tab.value =TabbedPanels1.getCurrentTabIndex();dlg_print();" />');
		}
		else
			$temp->setVar("WI_PRINT", '<img src="../../../../commonlib/trunk/images/b_print_d.jpg"/>');	
		
	}
	function habilitar(&$temp, $habilita) { 
		parent::habilitar($temp, $habilita);
		$cod_estado_arriendo = $this->dws['dw_arriendo']->get_item(0, 'COD_ESTADO_ARRIENDO');
		if($this->is_new_record() || $cod_estado_arriendo==self::K_ARRIENDO_ANULADO)
			$this->habilita_boton_print($temp, 'print', false);
		else	
			$this->habilita_boton_print($temp, 'print', true);		
		
	}
	function load_record() {
		$cod_arriendo = $this->get_item_wo($this->current_record, 'COD_ARRIENDO');
		$this->dws['dw_arriendo']->retrieve($cod_arriendo);
		$cod_empresa = $this->dws['dw_arriendo']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_arriendo']->controls['COD_SUCURSAL']->retrieve($cod_empresa);
		$this->dws['dw_arriendo']->controls['COD_PERSONA']->retrieve($cod_empresa);		
		$this->dws['dw_item_arriendo']->retrieve($cod_arriendo);
		$this->dws['dw_mod_arriendo']->retrieve($cod_arriendo);
		$this->dws['dw_arriendo_stock']->retrieve($cod_arriendo);
		$this->dws['dw_arriendo_docs']->retrieve($cod_arriendo);
		$this->dws['dw_arriendo_valor_actual']->retrieve($cod_arriendo);
		
		///////***********listas
		$this->dws['dw_oca_orden_compra']->retrieve($cod_arriendo);
		$this->dws['dw_lista_guia_despacho']->retrieve($cod_arriendo);
		$this->dws['dw_lista_guia_recepcion']->retrieve($cod_arriendo);	
		$this->dws['dw_lista_factura']->retrieve($cod_arriendo);
		$this->dws['dw_lista_nota_credito']->retrieve($cod_arriendo);
		$this->dws['dw_lista_cheques']->retrieve($cod_arriendo);
		$this->b_print_visible = true;
		
		$priv = $this->get_privilegio_opcion_usuario('998510', $this->cod_usuario);
		if ($priv == 'E')
			$this->dws['dw_arriendo_docs']->b_del_line_visible = true;	
		else
			$this->dws['dw_arriendo_docs']->b_del_line_visible = false;
		
		$cod_estado_arriendo = $this->dws['dw_arriendo']->get_item(0, 'COD_ESTADO_ARRIENDO');
		if ($cod_estado_arriendo==self::K_ARRIENDO_APROBADO) {
			$this->b_delete_visible = false;
			
			$ES_VIGENTE = $this->dws['dw_arriendo']->get_item(0, 'VIGENCIA_ARRIENDO');
			if($ES_VIGENTE == 'VIGENTE'){
				$this->dws['dw_arriendo']->set_entrable_dw(false);		
				$this->dws['dw_item_arriendo']->set_entrable_dw(false);
				$this->dws['dw_mod_arriendo']->set_entrable_dw(false);
				$this->dws['dw_arriendo_stock']->set_entrable_dw(false);
			}else{
				$this->b_save_visible = false;
				$this->b_no_save_visible = false;
				$this->b_modify_visible = false;
			}
			
			
			
		}
		else if ($cod_estado_arriendo==self::K_ARRIENDO_ANULADO) {
			$this->b_delete_visible = false;
			$this->b_save_visible = false;
			$this->b_no_save_visible = false;
			$this->b_modify_visible = false;
			$this->b_print_visible = false;
		}
		else if ($cod_estado_arriendo==self::K_ARRIENDO_EMITIDO) {
			$this->b_print_visible = true;
		}
		
		if($this->tiene_privilegio_opcion(self::K_APROBAR_SIN_CHEQUES)){
			$this->dws['dw_arriendo']->set_item(0, 'APROBAR_SIN_CHEQUE_H', 'S');
		}else{
			$this->dws['dw_arriendo']->set_item(0, 'APROBAR_SIN_CHEQUE_H', 'N');
		}

		//rellenar totales dw_arriendo_valor_actual
		$count = $this->dws['dw_arriendo_valor_actual']->row_count();
		$F_VA_TOTAL = 0;
		$F_VA_TOTAL_VALOR_VENTA_INICIAL = 0;
		$F_VA_TOTAL_ARRIENDO_ACTUAL = 0;
		$F_VA_TOTAL_VALOR_VENTA_ACTUAL = 0;

		for($i=0; $i < $count; $i++){ 
			$F_VA_TOTAL += $this->dws['dw_arriendo_valor_actual']->get_item($i, 'VA_TOTAL');
			$F_VA_TOTAL_VALOR_VENTA_INICIAL += $this->dws['dw_arriendo_valor_actual']->get_item($i, 'VA_TOTAL_VALOR_VENTA_INICIAL');
			$F_VA_TOTAL_ARRIENDO_ACTUAL += $this->dws['dw_arriendo_valor_actual']->get_item($i, 'VA_TOTAL_ARRIENDO_ACTUAL');
			$F_VA_TOTAL_VALOR_VENTA_ACTUAL += $this->dws['dw_arriendo_valor_actual']->get_item($i, 'VA_TOTAL_VALOR_VENTA_ACTUAL');
		}

		$this->dws['dw_arriendo']->set_item(0, 'F_VA_TOTAL', number_format($F_VA_TOTAL, 0, ',', '.'));
		$this->dws['dw_arriendo']->set_item(0, 'F_VA_TOTAL_VALOR_VENTA_INICIAL', number_format($F_VA_TOTAL_VALOR_VENTA_INICIAL, 0, ',', '.'));
		$this->dws['dw_arriendo']->set_item(0, 'F_VA_TOTAL_ARRIENDO_ACTUAL', number_format($F_VA_TOTAL_ARRIENDO_ACTUAL, 0, ',', '.'));
		$this->dws['dw_arriendo']->set_item(0, 'F_VA_TOTAL_VALOR_VENTA_ACTUAL', number_format($F_VA_TOTAL_VALOR_VENTA_ACTUAL, 0, ',', '.'));
	}
	function get_key() {
		return $this->dws['dw_arriendo']->get_item(0, 'COD_ARRIENDO');
	}
	function print_record() {
		$cod_arriendo = $this->get_key();
		$adicional = $this->dws['dw_arriendo']->get_item(0, 'MONTO_ADICIONAL_RECUPERACION');
		
		$sel_print_ca = $_POST['wi_hidden'];
		$print_ca = explode("|", $sel_print_ca);
		$cod_mod_arriendo = $print_ca[1]; 
		if($print_ca[0] == 'marca'){
			$sel_print_ca2 = trim($sel_print_ca,'|');
			$print_ca2 = explode("|", $sel_print_ca2);		
			for($i=2 ; $i < count($print_ca2) ; $i++){
				$aux .= $print_ca2[$i].'|';
			}
			$this->printca_marca_pdf($cod_mod_arriendo, $aux);
		}else if($print_ca[0] == 'anexo'){
			$fecha = $this->current_date();
			$labels = array();
			$labels['strCOD_ARRIENDO'] = $cod_arriendo;
			$labels['strFECHA_ACTUAL'] = $fecha;			
			$file_name = $this->find_file('arriendo', 'anexo_arriendo.xml');					
			$rpt = new print_anexo_arriendo($cod_arriendo, $file_name, $labels, "Anexo Arriendo ".$cod_arriendo.".pdf", 1);
			$this->_load_record();
			return true;
		}else if($print_ca[0] == 'anexo_d'){
			$fecha = $this->current_date();
			$labels = array();
			$labels['strCOD_ARRIENDO'] = $cod_arriendo;
			$labels['strFECHA_ACTUAL'] = $fecha;			
			$file_name = $this->find_file('arriendo', 'anexo_arriendo_d.xml');					
			$rpt = new print_anexo_arriendo_d($cod_arriendo, $file_name, $labels, "Anexo Arriendo ".$cod_arriendo.".pdf", 1);
			$this->_load_record();
			return true;	
		}else if($print_ca[0] == 'despachar'){
			$file_name = $this->find_file('arriendo', 'print_por_despachar.xml');
			$labels = array();
			$labels['strCOD_ARRIENDO'] = $cod_arriendo;
			$labels['strFECHA'] = $this->current_date();
			$labels['strTIME'] = $this->current_time();				
			$rpt = new informe_saldo_arriendo('AGREGAR',$cod_arriendo, $file_name, $labels, "Por Despachar ".$cod_arriendo.".pdf", 1);
			$this->_load_record();
		}else if($print_ca[0] == 'recibir'){
			$labels = array();
			$labels['strCOD_ARRIENDO'] = $cod_arriendo;
			$labels['strFECHA'] = $this->current_date();
			$labels['strTIME'] = $this->current_time();	
			$file_name = $this->find_file('arriendo', 'print_por_recibir.xml');				
			$rpt = new informe_saldo_arriendo('ELIMINAR',$cod_arriendo, $file_name, $labels, "Por Recibir ".$cod_arriendo.".pdf", 1);
			$this->_load_record();
		}else{	
			if($adicional > 0){
				$labels = array();
				$labels['strCOD_ARRIENDO'] = $cod_arriendo;			
				$file_name = $this->find_file('arriendo', 'arriendo_adicional.xml');					
				$rpt = new print_arriendo($cod_arriendo, $file_name, $labels, "Arriendo adicional ".$cod_arriendo.".pdf", 1);
				$this->_load_record();
				return true;
			}
			else{
				// reporte
				$labels = array();
				$labels['strCOD_ARRIENDO'] = $cod_arriendo;			
				$file_name = $this->find_file('arriendo', 'arriendo.xml');					
				$rpt = new print_arriendo($cod_arriendo, $file_name, $labels, "Arriendo ".$cod_arriendo.".pdf", 1);
				$this->_load_record();
				return true;
			}
		}	
			
	}
	
	function save_record($db) {
		$cod_arriendo = $this->get_key();
		$nom_arriendo = $this->dws['dw_arriendo']->get_item(0, 'NOM_ARRIENDO');
		$cod_usuario_vendedor1 = $this->dws['dw_arriendo']->get_item(0, 'COD_USUARIO_VENDEDOR1');
		$nro_orden_compra = $this->dws['dw_arriendo']->get_item(0, 'NRO_ORDEN_COMPRA');
		$cod_empresa = $this->dws['dw_arriendo']->get_item(0, 'COD_EMPRESA');
		$cod_sucursal = $this->dws['dw_arriendo']->get_item(0, 'COD_SUCURSAL');
		$cod_persona = $this->dws['dw_arriendo']->get_item(0, 'COD_PERSONA');		
		$ejecutivo_contacto = $this->dws['dw_arriendo']->get_item(0, 'EJECUTIVO_CONTACTO');
		$ejecutivo_telefono = $this->dws['dw_arriendo']->get_item(0, 'EJECUTIVO_TELEFONO');
		$ejecutivo_mail = $this->dws['dw_arriendo']->get_item(0, 'EJECUTIVO_MAIL');
		$cod_cotizacion_arriendo = $this->dws['dw_arriendo']->get_item(0, 'COD_COT_ARRIENDO');
		$referencia = $this->dws['dw_arriendo']->get_item(0, 'REFERENCIA');
		$centro_costo_cliente = $this->dws['dw_arriendo']->get_item(0, 'CENTRO_COSTO_CLIENTE');		
		$porc_adicional_recuperacion = $this->dws['dw_arriendo']->get_item(0, 'PORC_ADICIONAL_RECUPERACION');
		$monto_adicional_recuperacion = $this->dws['dw_arriendo']->get_item(0, 'MONTO_ADICIONAL_RECUPERACION');
		$nro_meses = $this->dws['dw_arriendo']->get_item(0, 'NRO_MESES');
		$porc_arriendo = $this->dws['dw_arriendo']->get_item(0, 'PORC_ARRIENDO');
		$subtotal = $this->dws['dw_arriendo']->get_item(0, 'SUM_TOTAL');
		$total_neto = $this->dws['dw_arriendo']->get_item(0, 'TOTAL_NETO');
		$porc_iva = $this->dws['dw_arriendo']->get_item(0, 'PORC_IVA');
		$monto_iva = $this->dws['dw_arriendo']->get_item(0, 'MONTO_IVA');
		$total_con_iva = $this->dws['dw_arriendo']->get_item(0, 'TOTAL_CON_IVA');		
		$fecha_entrega = $this->dws['dw_arriendo']->get_item(0, 'FECHA_ENTREGA');
		$ubicacion_direccion = $this->dws['dw_arriendo']->get_item(0, 'UBICACION_DIRECCION');
		$ubicacion_comuna = $this->dws['dw_arriendo']->get_item(0, 'UBICACION_COMUNA');
		$ubicacion_ciudad = $this->dws['dw_arriendo']->get_item(0, 'UBICACION_CIUDAD');
		$cod_estado_arriendo = $this->dws['dw_arriendo']->get_item(0, 'COD_ESTADO_ARRIENDO');
		$obs = $this->dws['dw_arriendo']->get_item(0, 'OBS');
		$exige_cheque = $this->dws['dw_arriendo']->get_item(0, 'EXIGE_CHEQUE');
		
		
    	$cod_arriendo = ($cod_arriendo=='') ? 'null' : $cod_arriendo;
		$cod_usuario_vendedor1 = ($cod_usuario_vendedor1=='') ? 'null' : $cod_usuario_vendedor1;
		$nro_orden_compra = ($nro_orden_compra=='') ? 'null' : "'$nro_orden_compra'";
		$ejecutivo_contacto = ($ejecutivo_contacto=='') ? 'null' : "'$ejecutivo_contacto'";
		$ejecutivo_telefono = ($ejecutivo_telefono=='') ? 'null' : "'$ejecutivo_telefono'";
		$ejecutivo_mail = ($ejecutivo_mail=='') ? 'null' : "'$ejecutivo_mail'";
		$cod_cotizacion_arriendo = ($cod_cotizacion_arriendo=='') ? 'null' : $cod_cotizacion_arriendo;
		$centro_costo_cliente = ($centro_costo_cliente=='') ? 'null' : "'$centro_costo_cliente'";		
		$porc_adicional_recuperacion = ($porc_adicional_recuperacion=='') ? 0 : $porc_adicional_recuperacion;
		$monto_adicional_recuperacion = ($monto_adicional_recuperacion=='') ? 0 : $monto_adicional_recuperacion;
		$nro_meses = ($nro_meses=='') ? 0 : $nro_meses;
		$porc_arriendo = ($porc_arriendo=='') ? 0 : $porc_arriendo;
		$subtotal = ($subtotal=='') ? 0 : $subtotal;
		$total_neto = ($total_neto=='') ? 0 : $total_neto;
		$porc_iva = ($porc_iva=='') ? 0 : $porc_iva;
		$monto_iva = ($monto_iva=='') ? 0 : $monto_iva;
		$total_con_iva = ($total_con_iva=='') ? 0 : $total_con_iva;
		$fecha_entrega = $this->str2date($fecha_entrega);
		$obs = ($obs=='') ? 'null' : "'$obs'";
		
		$sp = 'spu_arriendo';
	    if ($this->is_new_record()) {
	    	$operacion = 'INSERT';
	    	$cod_estado_arriendo_old = '';	// no tenia estado
	    }
	    else {
	    	$operacion = 'UPDATE';
			// obtiene el valor anterior del estado (antes de grabar)
			$sql = "select COD_ESTADO_ARRIENDO
					from ARRIENDO
					where COD_ARRIENDO = $cod_arriendo"; 
			$result = $db->build_results($sql);
	    	$cod_estado_arriendo_old = $result[0]['COD_ESTADO_ARRIENDO'];
	    }
	    
		$param	= "'$operacion'
					,$cod_arriendo
					,'$nom_arriendo'
					,$this->cod_usuario
					,$cod_usuario_vendedor1
					,$nro_orden_compra
					,$cod_empresa
					,$cod_sucursal
					,$cod_persona
					,$ejecutivo_contacto
					,$ejecutivo_telefono
					,$ejecutivo_mail
					,$cod_cotizacion_arriendo
					,'$referencia'
					,$centro_costo_cliente
					,$porc_adicional_recuperacion
					,$monto_adicional_recuperacion
					,$nro_meses
					,$porc_arriendo
					,$subtotal
					,$total_neto
					,$porc_iva
					,$monto_iva
					,$total_con_iva
					,$fecha_entrega
					,'$ubicacion_direccion'
					,'$ubicacion_comuna'
					,'$ubicacion_ciudad'
					,$cod_estado_arriendo
					,$obs
					,'$exige_cheque'";
			
			
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_arriendo = $db->GET_IDENTITY();
				$this->dws['dw_arriendo']->set_item(0, 'COD_ARRIENDO', $cod_arriendo);
			}

			if (!$this->dws['dw_item_arriendo']->update($db, $cod_arriendo))
				return false;
				
			if ($cod_estado_arriendo_old == self::K_ARRIENDO_EMITIDO && $cod_estado_arriendo == self::K_ARRIENDO_APROBADO) // se esta aprobando un arriendo
				if (!$db->EXECUTE_SP($sp, "'APROBAR', $cod_arriendo"))
					return false;
			
			if (!$this->dws['dw_arriendo_docs']->update($db, $cod_arriendo))
				return false;
					
			$parametros_sp = "'RECALCULA',$cod_arriendo";	
			if (!$db->EXECUTE_SP($sp, $parametros_sp))
				return false;

			return true;
		}
		return false;
	}
	function printca_marca_pdf($cod_mod_arriendo, $aux) {	
		$sql = "exec spr_ca_marca $cod_mod_arriendo, '$aux'";
		$labels = array();
		$file_name = $this->find_file('arriendo', 'ca_marca.xml');
		$rpt = new print_ca_marca($sql, $file_name, $labels, "Arriendo ".$this->get_key().".pdf", 0);												
		$this->_load_record();
		// fin reporte de marca despacho	
	}
	
	function creada_desde_cot_arr($cod_cot_arriendo){
		$this->dws['dw_arriendo']->new_arriendo($cod_cot_arriendo);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT	 C.COD_USUARIO_VENDEDOR1
						,C.REFERENCIA
						,E.RUT
						,E.DIG_VERIF
						,E.ALIAS
						,C.COD_EMPRESA
						,E.NOM_EMPRESA
						,E.GIRO
						,C.COD_SUCURSAL_FACTURA
						,C.COD_PERSONA
						,dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_SUCURSAL
						,dbo.f_emp_get_mail_cargo_persona(C.COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA
						,C.SUBTOTAL
						,PORC_ADICIONAL_RECUPERACION
						,C.TOTAL_NETO
						,C.PORC_IVA
						,C.MONTO_IVA
						,C.TOTAL_CON_IVA
						,C.PORC_ARRIENDO
						,DATEDIFF(DAY, C.FECHA_REGISTRO_COTIZACION, GETDATE()) DAY_REGISTRO_COT
				from 	COT_ARRIENDO C, USUARIO U, EMPRESA E, ESTADO_COTIZACION EC
				where	COD_COT_ARRIENDO = $cod_cot_arriendo and
						U.COD_USUARIO = C.COD_USUARIO AND
						E.COD_EMPRESA = C.COD_EMPRESA AND
						EC.COD_ESTADO_COTIZACION = C.COD_ESTADO_COTIZACION";
		
		$result = $db->build_results($sql);
		$this->dws['dw_arriendo']->set_item(0, 'COD_USUARIO_VENDEDOR1',	$result[0]['COD_USUARIO_VENDEDOR1']);
		$this->dws['dw_arriendo']->set_item(0, 'COD_COT_ARRIENDO',		$cod_cot_arriendo);
		$this->dws['dw_arriendo']->set_item(0, 'REFERENCIA',			$result[0]['REFERENCIA']);
		$this->dws['dw_arriendo']->set_item(0, 'RUT',					$result[0]['RUT']);
		$this->dws['dw_arriendo']->set_item(0, 'DIG_VERIF',				$result[0]['DIG_VERIF']);
		$this->dws['dw_arriendo']->set_item(0, 'ALIAS',					$result[0]['ALIAS']);
		$this->dws['dw_arriendo']->set_item(0, 'COD_EMPRESA',			$result[0]['COD_EMPRESA']);
		$this->dws['dw_arriendo']->set_item(0, 'NOM_EMPRESA',			$result[0]['NOM_EMPRESA']);
		$this->dws['dw_arriendo']->set_item(0, 'GIRO',					$result[0]['GIRO']);
		
		$this->dws['dw_arriendo']->controls['COD_SUCURSAL']->retrieve($result[0]['COD_EMPRESA']);
		$this->dws['dw_arriendo']->controls['COD_PERSONA']->retrieve($result[0]['COD_EMPRESA']);
		
		$this->dws['dw_arriendo']->set_item(0, 'COD_SUCURSAL',					$result[0]['COD_SUCURSAL_FACTURA']);
		$this->dws['dw_arriendo']->set_item(0, 'COD_PERSONA',					$result[0]['COD_PERSONA']);
		$this->dws['dw_arriendo']->set_item(0, 'DIRECCION_SUCURSAL',			$result[0]['DIRECCION_SUCURSAL']);
		$this->dws['dw_arriendo']->set_item(0, 'MAIL_CARGO_PERSONA',			$result[0]['MAIL_CARGO_PERSONA']);
		$this->dws['dw_arriendo']->set_item(0, 'SUM_TOTAL',						$result[0]['SUBTOTAL']);		
		
		$sql_item="SELECT		COD_ITEM_COT_ARRIENDO,
								COD_COT_ARRIENDO,
								ORDEN,
								ITEM,
								COD_PRODUCTO,
								NOM_PRODUCTO,
								CANTIDAD,
								PRECIO,
								PRECIO_ARRIENDO,
								'' MOTIVO,
								COD_TIPO_TE,
								MOTIVO_TE
					FROM		ITEM_COT_ARRIENDO
					WHERE		COD_COT_ARRIENDO = $cod_cot_arriendo
					AND			COD_PRODUCTO <> 'T'
					ORDER BY	ORDEN";
					
		$result_item = $db->build_results($sql_item);
		
		$day_registro_cot = $result[0]['DAY_REGISTRO_COT'];
		$validez_oferta_cot = $this->get_parametro(7);
		$no_valido = false;
		$monto_adicional = 0;
		
		if($day_registro_cot > $validez_oferta_cot){
			$this->alert("La Cotización de arriendo N° ".$cod_cot_arriendo.", se encuentra fuera del periodo de validez de oferta (".$validez_oferta_cot." días.) Se usarán precios actualizados.");
			$no_valido = true;
			$porc_arr = $result[0]['PORC_ARRIENDO'];
		}
		
		for($i=0 ; $i < count($result_item); $i++){
			$this->dws['dw_item_arriendo']->insert_row();
			$this->dws['dw_item_arriendo']->set_item($i, 'ORDEN',			$result_item[$i]['ORDEN']);
			$this->dws['dw_item_arriendo']->set_item($i, 'ITEM',			$result_item[$i]['ITEM']);
			$this->dws['dw_item_arriendo']->set_item($i, 'COD_PRODUCTO',	$result_item[$i]['COD_PRODUCTO']);
			$this->dws['dw_item_arriendo']->set_item($i, 'NOM_PRODUCTO',	$result_item[$i]['NOM_PRODUCTO']);
			$this->dws['dw_item_arriendo']->set_item($i, 'CANTIDAD',		$result_item[$i]['CANTIDAD']);
			
			if($no_valido){
				$sql_precio="SELECT ROUND((PRECIO_VENTA_PUBLICO * $porc_arr)/ 100, 0) PRECIO_VENTA_PUBLICO_CAL
								   ,PRECIO_VENTA_PUBLICO
						     FROM PRODUCTO
						     WHERE COD_PRODUCTO = '".$result_item[$i]['COD_PRODUCTO']."'";
				
				$result_precio = $db->build_results($sql_precio);
				
				$precio_tot = $result_precio[0]['PRECIO_VENTA_PUBLICO'] * $result_item[$i]['CANTIDAD'];
				
				$this->dws['dw_item_arriendo']->set_item($i, 'PRECIO_VENTA',	$result_precio[0]['PRECIO_VENTA_PUBLICO']);
				$this->dws['dw_item_arriendo']->set_item($i, 'PRECIO',		$result_precio[0]['PRECIO_VENTA_PUBLICO_CAL']);
				$total = $result_precio[0]['PRECIO_VENTA_PUBLICO_CAL'] * $result_item[$i]['CANTIDAD'];
			}else{
				$precio_tot = $result_item[$i]['PRECIO'] * $result_item[$i]['CANTIDAD'];
			
				$this->dws['dw_item_arriendo']->set_item($i, 'PRECIO_VENTA',	$result_item[$i]['PRECIO']);
				$this->dws['dw_item_arriendo']->set_item($i, 'PRECIO',		$result_item[$i]['PRECIO_ARRIENDO']);
				$total = $result_item[$i]['PRECIO_ARRIENDO'] * $result_item[$i]['CANTIDAD'];
			}
			
			$this->dws['dw_item_arriendo']->set_item($i, 'TOTAL',			$total);
			$this->dws['dw_item_arriendo']->set_item($i, 'COD_TIPO_TE',		$result_item[$i]['COD_TIPO_TE']);
			$this->dws['dw_item_arriendo']->set_item($i, 'MOTIVO_TE',		$result_item[$i]['MOTIVO_TE']);
			$sum_total += $total;
			$monto_adicional += $precio_tot;
		}
		
		$monto_adicional = $monto_adicional * ($result[0]['PORC_ADICIONAL_RECUPERACION']/100);
		
		$this->dws['dw_item_arriendo']->calc_computed();
		
		$this->dws['dw_arriendo']->set_item(0, 'MONTO_ADICIONAL_RECUPERACION',	$monto_adicional);
		$this->dws['dw_arriendo']->set_item(0, 'TOTAL_NETO',		number_format($sum_total, 0, ',', ''));
		$this->dws['dw_arriendo']->set_item(0, 'PORC_IVA', 			$this->get_parametro(1));
		$this->dws['dw_arriendo']->set_item(0, 'MONTO_IVA',			$sum_total * ($this->get_parametro(1) / 100));
		$this->dws['dw_arriendo']->set_item(0, 'TOTAL_CON_IVA',		$sum_total+ $sum_total * ($this->get_parametro(1) / 100));
		
	}
}
class print_ca_marca extends reporte {	
	function print_ca_marca($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
	}
	function dibuja_uno(&$pdf, $result) {
		$pdf->SetFont('Helvetica','B',20);
		$pdf->Text(50, 150,'Nï¿½ Contrato');
		$pdf->SetFont('Helvetica','',14);
		$pdf->Text(50,190,'Cliente');
		$pdf->Text(50,250,'Nombre Contrato');
		$pdf->Text(50,310,'Atenciï¿½n Sr(a)');		
		$pdf->Text(50,340,'Despacho');		
		$pdf->Text(50,400,'Nï¿½ OC');
		if(!$result['CENTRO_COSTO_CLIENTE'] == '')
			$pdf->Text(380,400,'CECO :');		
		$pdf->Text(50,430,'ï¿½tem');	
		$pdf->Text(50,460,'Mï¿½delo');		
		$pdf->Text(50,492,'Producto');		
		$pdf->Text(50,550,'Remitente');		
		$pdf->Text(50,580,'Direcciï¿½n');
		
		
		
		$pdf->SetFont('Helvetica','B',20);
		$pdf->Text(180, 150, $result['COD_ARRIENDO']);
		$pdf->SetFont('Helvetica','',14);
		
		if($result['EJECUTIVO_CONTACTO'] == '')
			$pdf->Text(180, 310, $result['NOM_PERSONA']);
		else
			$pdf->Text(180, 310, $result['EJECUTIVO_CONTACTO']);	
		
		$pdf->Text(180, 340, $result['UBICACION_DIRECCION']);
		$pdf->Text(180, 355, $result['UBICACION_COMUNA']);
		$pdf->Text(180, 370, $result['UBICACION_CIUDAD']);
		
		
		$pdf->Text(180, 400, $result['NRO_ORDEN_COMPRA']);
		if(!$result['CENTRO_COSTO_CLIENTE'] == '')
			$pdf->Text(440,400, $result['CENTRO_COSTO_CLIENTE']);	
		$pdf->Text(180, 430, $result['ITEM']);
		$pdf->Text(180, 460, $result['COD_PRODUCTO']);
		$pdf->Text(180, 550, $result['NOM_EMPRESA_EMISOR']);
		$pdf->Text(215, 578, $result['DIR_EMPRESA']);
		$pdf->Text(350, 578, $result['CIUDAD_EMPRESA']);
		$pdf->Text(440, 578, $result['PAIS_EMPRESA']);
		$pdf->Text(245, 598, $result['TEL_EMPRESA']);
		$pdf->Text(400, 598, $result['FAX_EMPRESA']);
		$pdf->Text(305, 613, $result['MAIL_EMPRESA']);
		$pdf->Text(340, 576,'-');
		$pdf->Text(430, 576,'-');
		$pdf->Text(190, 598,'FONO:');
		$pdf->Text(358, 598,'FAX:');
		
		
		$pdf->Rect(40,120, 130, 50, 'f');
		$pdf->Rect(40,120, 530, 50, 'f');
		$pdf->Rect(40,170, 130, 60, 'f');
		$pdf->Rect(40,170, 530, 60, 'f');
		$pdf->Rect(40,230, 130, 60, 'f');
		$pdf->Rect(40,230, 530, 60, 'f');
		$pdf->Rect(40,290, 130, 30, 'f');
		$pdf->Rect(40,290, 530, 30, 'f');
		$pdf->Rect(40,320, 130, 60, 'f');
		$pdf->Rect(40,320, 530, 60, 'f');
		$pdf->Rect(40,380, 130, 30, 'f');
		
		if(!$result['CENTRO_COSTO_CLIENTE'] == '')
			$pdf->Rect(40,380, 330, 30, 'f');
		
		$pdf->Rect(40,380, 530, 30, 'f');
		$pdf->Rect(40,410, 130, 30, 'f');
		$pdf->Rect(40,410, 530, 30, 'f');
		$pdf->Rect(40,440, 130, 30, 'f');
		$pdf->Rect(40,440, 530, 30, 'f');
		$pdf->Rect(40,470, 130, 60, 'f');
		$pdf->Rect(40,470, 530, 60, 'f');
		$pdf->Rect(40,530, 130, 30, 'f');
		$pdf->Rect(40,530, 530, 30, 'f');
		$pdf->Rect(40,560, 130, 60, 'f');
		$pdf->Rect(40,560, 530, 60, 'f');
		
		$pdf->SetXY(175 , 480);
		$pdf->MultiCell(400, 15,$result['NOM_PRODUCTO'], 0, 'T');
		$pdf->SetXY(175 , 180);
		$pdf->MultiCell(400, 15,$result['NOM_EMPRESA'], 0, 'T');
		$pdf->SetXY(175 , 240);
		$pdf->MultiCell(400, 15,$result['NOM_ARRIENDO'], 0, 'T');
		
		$pdf->SetFont('Helvetica','',8);
		$factor = 0.5;
		$pdf->Image(dirname(__FILE__)."/../../images_appl/RENTAL/logo_rental.jpg",40,20, $factor * 450, $factor * 170);

	}
	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		for($i=0; $i<count($result); $i++) {
			$this->dibuja_uno($pdf, $result[$i]);
			if ($i < count($result) - 1)
				$pdf->AddPage();
		}
	}	
}
?>
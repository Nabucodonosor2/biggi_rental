<?php
class static_cod_doc extends static_text {
    function static_gr($field) {
        parent::static_text($field);
    }
    function draw_no_entrable($dato, $record) {
        $a = explode("|", $dato);
        if (count($a) > 1)
            return 'varios';
            else
                return $a[0];
    }
    function draw_entrable($dato, $record) {
        return $this->draw_no_entrable($dato, $record);
    }
}

class wo_inf_facturas_por_cobrar extends wo_inf_facturas_por_cobrar_base {
	var $checkbox_ventas;
	var $checkbox_arriendo;
	
	var $checkbox_arriendo_biggi;
	var $checkbox_arriendo_catering;
	var $todos_seleccionados = true;
	var $ninguno_seleccionado = false;
	var $todos_arriendos_seleccionados = false;
	var $sql_filtrado = '';
	
	function make_sql() {
   		/* El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		   hasta despues de llamar al ancestro
   		 */  
		$cod_usuario =  session::get("COD_USUARIO");
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_facturas_por_cobrar", "$cod_usuario"); 
   		$sql = "select	I.COD_FACTURA
						,I.NRO_FACTURA
						,I.FECHA_FACTURA
						,I.FECHA_FACTURA_STR
						,I.DATE_FACTURA
						,I.RUT
						,I.DIG_VERIF
						,I.NOM_EMPRESA
						,I.INI_USUARIO_VENDEDOR_A
						,I.INI_USUARIO_VENDEDOR_B
						,I.TOTAL_CON_IVA
						,I.SALDO
						,I.PAGO
						,I.CANTIDAD_FA 
						,I.COD_USUARIO_VENDEDOR1
                        ,dbo.f_origen_arriendo1(I.COD_FACTURA,'FACTURA')ORIGEN_ARRIENDO
						,dbo.f_origen_arriendo1(I.COD_FACTURA,'ARRIENDOS_X_FACTURA')COD_DOCS
                        
				FROM INF_FACTURAS_POR_COBRAR I
				where I.COD_USUARIO = $cod_usuario ";
		
   		if($this->ninguno_seleccionado){
   		    $sql .= " and dbo.f_origen_arriendo1(I.COD_FACTURA,'FACTURA') = 'NO_MOSTRAR'";
   		}else{
   		    if($this->todos_seleccionados == false){
   		        if ($this->checkbox_ventas == false){
   		            $sql .= " and I.COD_TIPO_FACTURA <> 1";
   		        }else if ($this->checkbox_ventas == true && $this->checkbox_arriendo_biggi == false && $this->checkbox_arriendo_catering == false){
   		            $sql .= " and I.COD_TIPO_FACTURA = 1";
   		        }
   		        if($this->todos_arriendos_seleccionados == false){
   		            if ($this->checkbox_arriendo_biggi == true){
   		                $sql .= " and dbo.f_origen_arriendo1(I.COD_FACTURA,'FACTURA') = 'BIGGI'";
   		            }
   		            if ($this->checkbox_arriendo_catering == true){
   		                $sql .= " and dbo.f_origen_arriendo1(I.COD_FACTURA,'FACTURA') = 'CATERING'";
   		            }
   		        }
   		        if ($this->checkbox_ventas == true && $this->checkbox_arriendo_biggi == false && $this->checkbox_arriendo_catering == true){
   		            $sql .= "UNION
                            select	I.COD_FACTURA
        						,I.NRO_FACTURA
        						,I.FECHA_FACTURA
        						,I.FECHA_FACTURA_STR
        						,I.DATE_FACTURA
        						,I.RUT
        						,I.DIG_VERIF
        						,I.NOM_EMPRESA
        						,I.INI_USUARIO_VENDEDOR_A
        						,I.INI_USUARIO_VENDEDOR_B
        						,I.TOTAL_CON_IVA
        						,I.SALDO
        						,I.PAGO
        						,I.CANTIDAD_FA 
        						,I.COD_USUARIO_VENDEDOR1
                                ,dbo.f_origen_arriendo1(I.COD_FACTURA,'FACTURA')ORIGEN_ARRIENDO
        						,dbo.f_origen_arriendo1(I.COD_FACTURA,'ARRIENDOS_X_FACTURA')COD_DOCS
                                
        				FROM INF_FACTURAS_POR_COBRAR I
        				where I.COD_USUARIO = $cod_usuario 
                         and I.COD_TIPO_FACTURA = 1";
   		        }
   		    }if ($this->checkbox_ventas == true && $this->checkbox_arriendo_biggi == true && $this->checkbox_arriendo_catering == false){
   		        $sql .= "UNION
                            select	I.COD_FACTURA
        						,I.NRO_FACTURA
        						,I.FECHA_FACTURA
        						,I.FECHA_FACTURA_STR
        						,I.DATE_FACTURA
        						,I.RUT
        						,I.DIG_VERIF
        						,I.NOM_EMPRESA
        						,I.INI_USUARIO_VENDEDOR_A
        						,I.INI_USUARIO_VENDEDOR_B
        						,I.TOTAL_CON_IVA
        						,I.SALDO
        						,I.PAGO
        						,I.CANTIDAD_FA
        						,I.COD_USUARIO_VENDEDOR1
                                ,dbo.f_origen_arriendo1(I.COD_FACTURA,'FACTURA')ORIGEN_ARRIENDO
        						,dbo.f_origen_arriendo1(I.COD_FACTURA,'ARRIENDOS_X_FACTURA')COD_DOCS
                                
        				FROM INF_FACTURAS_POR_COBRAR I
        				where I.COD_USUARIO = $cod_usuario
                         and I.COD_TIPO_FACTURA = 1";
   		    }
   		}
   		
		$sql .= " ORDER BY I.FECHA_FACTURA";
		//echo $sql;
		$this->sql_filtrado = $sql;
		return $sql;		
	}
  	function wo_inf_facturas_por_cobrar() {
   		$this->checkbox_ventas = true;
		$this->checkbox_arriendo = true;
   	
		$this->checkbox_arriendo_catering = true;
		$this->checkbox_arriendo_biggi = true;
		
		$sql = $this->make_sql();
		
		parent::w_informe_pantalla('inf_facturas_por_cobrar', $sql, $_REQUEST['cod_item_menu']);
		
		$this->dw->add_control(new edit_text_hidden('COD_NOTA_VENTA_H'));
		
		// headers
		$this->add_header(new header_num('NRO_FACTURA', 'I.NRO_FACTURA', 'N° Fa'));
		$this->add_header($control = new header_date('FECHA_FACTURA_STR', 'I.FECHA_FACTURA', 'Fecha'));//*****
		$control->field_bd_order = 'I.DATE_FACTURA';
		$this->add_header(new header_text('NOM_EMPRESA', "I.NOM_EMPRESA", 'Cliente'));
		$sql = "select	distinct I.COD_USUARIO_VENDEDOR1 COD_USUARIO ,U.NOM_USUARIO 
				FROM INF_FACTURAS_POR_COBRAR I left outer join USUARIO U on U.COD_USUARIO = I.COD_USUARIO_VENDEDOR1 
				order by U.NOM_USUARIO";

		$this->add_header(new header_drop_down('INI_USUARIO_VENDEDOR_A', 'I.COD_USUARIO_VENDEDOR1', 'V1', $sql));
		$this->add_header(new header_rut('RUT', 'I', 'Rut'));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'I.TOTAL_CON_IVA', 'Total', 0, true, 'SUM'));
		$this->add_header($control = new header_num('SALDO', 'SALDO', 'Saldo', 0, true, 'SUM'));
		$this->add_header($control = new header_num('PAGO', 'PAGO', 'Pagos', 0, true, 'SUM'));
		$this->add_header(new header_num('CANTIDAD_FA', '1', '', 0, true, 'SUM'));
		$this->add_header(new header_text('COD_DOCS', "dbo.f_origen_arriendo1(I.COD_FACTURA,'ARRIENDOS_X_FACTURA')", 'N° Arriendo'));
		
		$sql = "SELECT 'Biggi' COD,
				'Biggi' NOM
				UNION
				SELECT 'Catering' COD,
				'Catering' NOM";
		$this->add_header(new header_drop_down_string('ORIGEN_ARRIENDO',"(dbo.f_origen_arriendo1(I.COD_FACTURA,'FACTURA'))", 'Origen', $sql));
		
		// controls
		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new static_num('TOTAL_CON_IVA'));
		
		$this->dw->add_control(new static_num('SALDO'));
		
		$this->dw->add_control(new static_num('PAGO'));
		
		
		$this->dw->add_control(new static_cod_doc('COD_DOCS'));

		$sql = "select 'S' CHECK_VENTAS,
					   'S' CHECK_ARRIENDO,
					   'N' HIZO_CLICK,
                       'S' CHECK_ARRIENDO_BIGGI,
					   'S' CHECK_ARRIENDO_CATERING";
		$this->dw_check_box = new datawindow($sql);
		
		$sql = "select 0 SUMAS_CANTIDADES_FA
                                ,0 SUMAS_TOTAL_CON_IVA
                                ,0 SUMAS_SALDO
                                ,0 SUMAS_PAGO";
		$this->dw_totales = new datawindow($sql);
		$this->dw_totales->add_control(new static_num('SUMAS_TOTAL_CON_IVA'));
		$this->dw_totales->add_control(new static_num('SUMAS_SALDO'));
		$this->dw_totales->add_control(new static_num('SUMAS_PAGO'));
		$this->dw_totales->add_control(new static_text('SUMAS_CANTIDADES_FA'));
		$this->dw_totales->retrieve();
		
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_VENTAS','S','N'));
		$control->set_onClick("agrega_factura(); document.getElementById('loader').style.display='';");
		
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_ARRIENDO','S','N'));
		$control->set_onClick("agrega_factura(); document.getElementById('loader').style.display='';");
		
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_ARRIENDO_BIGGI','S','N'));
		$control->set_onClick("agrega_factura(); document.getElementById('loader').style.display='';");
		
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_ARRIENDO_CATERING','S','N'));
		$control->set_onClick("agrega_factura(); document.getElementById('loader').style.display='';");
		
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->calcular_totales();
		$this->dw_check_box->retrieve();
	}
	
	function calcular_totales(){
	   $sql = $this->dw->get_sql();
	   
	    $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	    $result = $db->build_results($sql);
	    
	    $total_cantidad_fa = 0;
	    $total_fa = 0;
	    $saldos = 0;
	    $pagos = 0;
	    for ($i = 0; $i < count($result); $i++) {
	        $TOTAL_CON_IVA = $result[$i]['TOTAL_CON_IVA'];
	        $SALDO = $result[$i]['SALDO'];
	        $PAGO = $result[$i]['PAGO'];
	        $CANTIDAD_FA = $result[$i]['CANTIDAD_FA'];
	        
	        $total_cantidad_fa = $total_cantidad_fa +$CANTIDAD_FA;
	        $total_fa = $total_fa +$TOTAL_CON_IVA;
	        $saldos = $saldos + $SALDO;
	        $pagos = $pagos +$PAGO;
	    }
	    $this->dw_totales->set_item(0, 'SUMAS_CANTIDADES_FA', $total_cantidad_fa);
	    $this->dw_totales->set_item(0, 'SUMAS_SALDO', $saldos);
	    $this->dw_totales->set_item(0, 'SUMAS_TOTAL_CON_IVA', $total_fa);
	    $this->dw_totales->set_item(0, 'SUMAS_PAGO', $pagos);
	}
	function make_menu(&$temp) {
	    $menu = session::get('menu_appl');
	    $menu_original = $menu->ancho_completa_menu;
	    $menu->ancho_completa_menu = 398;
	    $menu->draw($temp);
	    $menu->ancho_completa_menu = $menu_original;    // volver a setear el tamaño original
	}
	function redraw(&$temp) {
		parent::redraw(&$temp);
		$this->dw_check_box->habilitar($temp, true);
		$this->dw_totales->habilitar($temp, true);
	}	
	function make_filtros() {
	    $this->nom_filtro = '';
	    $filtro_total = '';
	    $indices = array_keys($this->headers);
	    for ($i=0; $i<count($this->headers); $i++) {
	        $filtro = $this->headers[$indices[$i]]->make_filtro();
	        if ($filtro != '') {
	            $filtro_total .= $filtro;
	            $this->nom_filtro .= $this->headers[$indices[$i]]->make_nom_filtro()."; ";
	        }
	    }
	    // Elimina ; final
	    if ($this->nom_filtro != '')
	        $this->nom_filtro = substr($this->nom_filtro, 0, strlen($this->nom_filtro)-2);
	        
	        $sql = $this->sql_original;
	        if ($filtro_total != '') {
	            $pos = strrpos(strtoupper($sql), 'WHERE');
	            if ($pos === false) {
	                $pos = strrpos(strtoupper($sql), 'GROUP');
	                if ($pos === false) {
	                    $pos = strrpos(strtoupper($sql), 'ORDER');
	                    if ($pos===false)
	                        $sql = $sql.' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4);	// borra 'and '
	                        else
	                            $sql = substr($sql, 0, $pos).' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4).' '.substr($sql, $pos);	// borra 'and ' y agrega el resto
	                }
	                else
	                    $sql = substr($sql, 0, $pos).' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4).' '.substr($sql, $pos);	// borra 'and ' y agrega el resto
	            }
	            else
	                $sql = substr($sql, 0, $pos).' WHERE '.$filtro_total.' '.substr($sql, $pos + 5);
	        }
	        
	        // Aplica un order by si ha sido seleccionado por el usuario
	        if ($this->field_sort != '') {
	            $pos_order = strrpos(strtoupper($sql), 'ORDER');	// posible error si es que existe un nombre de campo que contenga la palabra ORDER !!
	            if ($pos_order===false)
	                $pos_order = strlen($sql);
	                $sql = substr($sql, 0, $pos_order - 1);
	                
	                $sql .= ' ORDER BY ';
	                $lista = explode(",", $this->headers[$this->field_sort]->field_bd_order);
	                for ($i=0; $i<count($lista); $i++)
	                    $sql .= $lista[$i].' '.$this->sort_asc_desc.",";
	                    $sql = substr($sql, 0, strlen($sql)-1);
	        }
	        $this->dw->set_sql($sql);
	        $this->calcular_totales();
	}
	function procesa_event() {
		if ($_POST['HIZO_CLICK_0'] == 'S') {
			$this->checkbox_ventas = isset($_POST['CHECK_VENTAS_0']);
			
			
			$this->checkbox_arriendo_biggi = isset($_POST['CHECK_ARRIENDO_BIGGI_0']);
			$this->checkbox_arriendo_catering = isset($_POST['CHECK_ARRIENDO_CATERING_0']);
			
			
			if($this->checkbox_ventas && $this->checkbox_arriendo_biggi && $this->checkbox_arriendo_catering)
			    $this->todos_seleccionados = true;
			else 
			    $this->todos_seleccionados = false;
			
		    if(!$this->checkbox_ventas && !$this->checkbox_arriendo_biggi && !$this->checkbox_arriendo_catering)
		        $this->ninguno_seleccionado = true;
		    else 
		        $this->ninguno_seleccionado = false;
			
		    if($this->checkbox_arriendo_biggi && $this->checkbox_arriendo_catering ){
		        $this->todos_arriendos_seleccionados = true;
		    }else if (!$this->checkbox_arriendo_biggi && $this->checkbox_arriendo_catering ) {
		        $this->todos_arriendos_seleccionados = false;
		    }else if ($this->checkbox_arriendo_biggi && !$this->checkbox_arriendo_catering ) {
		        $this->todos_arriendos_seleccionados = false;
		    }
			
			if ($this->checkbox_ventas)
				$this->dw_check_box->set_item(0, 'CHECK_VENTAS', 'S');
			else{
				$this->dw_check_box->set_item(0, 'CHECK_VENTAS', 'N');
			}
			
			if ($this->checkbox_arriendo_biggi)
			    $this->dw_check_box->set_item(0, 'CHECK_ARRIENDO_BIGGI', 'S');
		    else
		        $this->dw_check_box->set_item(0, 'CHECK_ARRIENDO_BIGGI', 'N');
		    
	        if ($this->checkbox_arriendo_catering)
	            $this->dw_check_box->set_item(0, 'CHECK_ARRIENDO_CATERING', 'S');
            else
                $this->dw_check_box->set_item(0, 'CHECK_ARRIENDO_CATERING', 'N');

			$sql = $this->make_sql();
			
			$this->calcular_totales();
			$this->dw->set_sql($sql);
			$this->sql_original = $sql;
			$this->save_SESSION();	
			$this->make_filtros();
			
			$this->retrieve();	
		}else{ 
			$this->checkbox_ventas = 0;
			$this->checkbox_arriendo = 0;
			parent::procesa_event();
			
		}
	}
}
?>
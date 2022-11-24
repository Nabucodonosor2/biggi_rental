<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

// Esta clase es auxiliar y la idea es que todo el codigo que sea comun entre help_empresa.php y helpo_lista_empresa.php este en esta clase
class help_empresa {	
		
	const MAX_LISTA = 100;		// Cantidad máxima de empresas que se deben cargar en la lista
	const K_PARAM_CTA_CTE_COMERCIAL = 33;
	
	static function una_row($fields, $row) {
		/* Arma un string con todo el contenido cuando se selecciona una empresa desde la lista o cuando la busqueda dio por
			 resultado 1 registro
		*/
		$resp = '';
		for ($j=0; $j<count($fields); $j++)
			$resp .= $row[$fields[$j]->name]."|";

		$cod_empresa = $row['COD_EMPRESA'];
	
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		
		$resp = substr($resp, 0, strlen($resp) - 1);	// borra el ultimo caracter
		return $resp;	
	}
	static function find_empresa($cod_proveedor_ext, $alias_proveedor_ext, $nom_proveedor_ext) {
		/* Esta funcion es llamada desde el ajax y busca las empresas que cumplan con los datos ingresados
			 Si el resultado de la busqueda es mayor a 1 retorna el sql para que se despliegue la ventana de selección de empresas
		*/
		//if (!is_numeric($cod_empresa))	$cod_empresa = '';
		//if (!is_numeric($rut))	$rut = '';
		
		if ($cod_proveedor_ext=='' && $alias_proveedor_ext=='' && $nom_proveedor_ext=='') {
			$resp = "0|";
			print urlencode($resp);	
			return;
		}

		$sql_base = "SELECT COD_PROVEEDOR_EXT_4D 
						   ,ALIAS_PROVEEDOR_EXT
						   ,NOM_PROVEEDOR_EXT
						   ,NOM_CIUDAD_4D 
						   ,NOM_PAIS_4D 
						   ,TELEFONO
						   ,FAX
						   ,OBS
					FROM PROVEEDOR_EXT 
				WHERE";

		if ($cod_proveedor_ext!='')
			$sql = $sql_base."(COD_PROVEEDOR_EXT_4D = '%".$cod_proveedor_ext."%')";
		elseif ($alias_proveedor_ext!='')
			$sql = $sql_base."(ALIAS_PROVEEDOR_EXT like '%".$alias_proveedor_ext."%')";
		elseif ($nom_proveedor_ext!='')
			$sql = $sql_base."(NOM_PROVEEDOR_EXT like '%".$nom_proveedor_ext."%')";
					
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->query($sql);
		$count_rows = $db->count_rows();
		if ($count_rows==0) {
			// Busqueda con % (contiente) %
			if ($alias_proveedor_ext!='')
				$sql = $sql_base."(ALIAS_PROVEEDOR_EXT like '%".$alias_proveedor_ext."%')";
			elseif ($nom_proveedor_ext!='')
				$sql = $sql_base."(NOM_PROVEEDOR_EXT like '%".$nom_proveedor_ext."%')";
			$db->query($sql);
			$count_rows = $db->count_rows();
		}
		if ($count_rows==0)
			$resp = "0|";
		elseif ($count_rows==1) {
			$row = $db->get_row();
			$resp = "1|";
			$fields = $db->get_fields();
			$resp .= help_empresa::una_row($fields, $row);
		}
		else
			$resp = $count_rows."|".$sql;
		
		print urlencode($resp);	
	}
	static function draw_htm_lista_empresa($sql) {
		/* Arma el html con la lista de empresas
			 $sql, es el sql con qle que se buscan las empresas
			 
			 Solo carga un máximo de registro definido en $MAX_LSTA
		*/
		
		$temp = new Template_appl('help_lista_empresa.htm');	

		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($sql, self::MAX_LISTA);
				
		if ($db->count_rows() > self::MAX_LISTA) {
			$temp->setVar("TIENE_MAS_REGISTROS", 'Se cargaron los primeros '.self::MAX_LISTA.' registos de un total de '.$db->count_rows().' registros.<br>Sea má especifico en los datos de búsqueda.');			
			$count = self::MAX_LISTA;
		}
		else {
			$temp->setVar("TIENE_MAS_REGISTROS", '');			
			$count = $db->count_rows();
		}
		
		$fields = $db->get_fields();
		for ($i=0 ; $i <$count; $i++) {
			$returnValue = '1|'.urlencode(help_empresa::una_row($fields, $result[$i]));

			$temp->gotoNext("EMPRESA");		

			if ($i % 2 == 0)
				$temp->setVar("EMPRESA.DW_TR_CSS", datawindow::css_claro);
			else
				$temp->setVar("EMPRESA.DW_TR_CSS", datawindow::css_oscuro);

			for ($j=0; $j<count($fields); $j++) {
				if ($j==0)
					$temp->setVar("EMPRESA.".$fields[$j]->name, '<a href="#" onClick="window.close(); returnValue=\''.$returnValue.'\';">'.$result[$i][$fields[$j]->name].'</a>');			
				else
					$temp->setVar("EMPRESA.".$fields[$j]->name, $result[$i][$fields[$j]->name]);			
			}
		}
		print $temp->toString();
	}
}
?>
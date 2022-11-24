<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
class print_arriendo extends reporte {	
	var $cod_arriendo;
	function print_arriendo($cod_arriendo, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		$this->cod_arriendo = $cod_arriendo;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "SELECT COD_ESTADO_ARRIENDO
				FROM ARRIENDO
				WHERE COD_ARRIENDO = $cod_arriendo";
		$result = $db->build_results($sql);
		$COD_ESTADO_ARRIENDO = $result[0]['COD_ESTADO_ARRIENDO'];	
		
		$sql = "SELECT top 1 convert(varchar(20), G.FECHA_GUIA_DESPACHO, 103) FECHA_GUIA_DESPACHO
				FROM GUIA_DESPACHO G, MOD_ARRIENDO M
				WHERE G.COD_TIPO_GUIA_DESPACHO =  5
				AND G.COD_ESTADO_DOC_SII IN (2, 3)
				AND M.COD_MOD_ARRIENDO = G.COD_DOC
				AND M.COD_ARRIENDO = $cod_arriendo
				ORDER BY G.FECHA_GUIA_DESPACHO ASC";
		$result = $db->build_results($sql);
		
		if($result[0]['FECHA_GUIA_DESPACHO'] <> '' && $COD_ESTADO_ARRIENDO == 2)
			$visible_fecha = "'S'";
		else
			$visible_fecha = "'N'";
		
		$FECHA_GUIA_DESPACHO = $result[0]['FECHA_GUIA_DESPACHO'];
		
		$sql = "SELECT    A.COD_ARRIENDO
                         ,convert(varchar(20), GETDATE(), 103) FECHA_ACTUAL
                         ,E.NOM_EMPRESA
                         ,E.COD_EMPRESA
                         ,E.RUT
                         ,E.DIG_VERIF
                         ,SU.DIRECCION
                         ,CO.NOM_COMUNA
                         ,CI.NOM_CIUDAD
                         ,PA.NOM_PAIS
                         ,SU.TELEFONO
                         ,SU.FAX
                         ,P.NOM_PERSONA
                         ,A.REFERENCIA
                         ,A.NRO_MESES
                         ,A.MONTO_ADICIONAL_RECUPERACION
                         ,(A.MONTO_ADICIONAL_RECUPERACION + A.SUBTOTAL) TOTAL_ADICIONAL
                         ,IT.ITEM
                         ,IT.NOM_PRODUCTO
                         ,IT.COD_PRODUCTO
                         ,IT.CANTIDAD
                         ,IT.PRECIO
                         ,IT.PRECIO * IT.CANTIDAD TOTAL
                         ,U.NOM_USUARIO USUARIO
                         ,A.NOM_ARRIENDO
                         ,(A.UBICACION_DIRECCION +' - '+ A.UBICACION_COMUNA +' - '+ A.UBICACION_CIUDAD) DIRECCION
                         ,(ISNULL(A.EJECUTIVO_CONTACTO,' ') +' - Fono: '+ ISNULL(A.EJECUTIVO_TELEFONO,'	') +' - E-Mail: '+ ISNULL(A.EJECUTIVO_MAIL,' ')) EJECUTIVO
                         ,A.PORC_ARRIENDO
                         ,NRO_ORDEN_COMPRA
                         ,A.OBS
                         ,'$FECHA_GUIA_DESPACHO' FECHA_GUIA_DESPACHO
                         ,$visible_fecha VISIBLE_FECHA
                 FROM     ARRIENDO A
                         ,EMPRESA E
                         ,USUARIO U
                         ,ESTADO_ARRIENDO EA
                         ,PERSONA P
                         ,ITEM_ARRIENDO IT
                         ,SUCURSAL SU LEFT OUTER JOIN COMUNA CO ON SU.COD_COMUNA = CO.COD_COMUNA
                                     LEFT OUTER JOIN CIUDAD CI ON SU.COD_CIUDAD = CI.COD_CIUDAD
                                     LEFT OUTER JOIN PAIS PA ON SU.COD_PAIS = PA.COD_PAIS
                 WHERE A.COD_ARRIENDO = $cod_arriendo
                     AND E.COD_EMPRESA = A.COD_EMPRESA
                     AND U.COD_USUARIO = A.COD_USUARIO
                     AND EA.COD_ESTADO_ARRIENDO = A.COD_ESTADO_ARRIENDO
                     AND P.COD_PERSONA = A.COD_PERSONA
                     AND IT.COD_ARRIENDO = A.COD_ARRIENDO
                     AND SU.COD_SUCURSAL = A.COD_SUCURSAL
                     AND P.COD_PERSONA = A.COD_PERSONA";
		
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
}
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

//////////////////////
$archivo = array( new item_menu('Cambio password', '0505', "../../../commonlib/trunk/php/change_password.php"),
				new item_menu('-'),
				new item_menu('Salir', '0510', "../../../commonlib/trunk/php/cerrar_sesion.php"));
				
$maestro = array( new item_menu('Empresas', '1005', "../../../commonlib/trunk/php/mantenedor.php?modulo=empresa&cod_item_menu=1005"),
				new item_menu('-'),
				new item_menu('Productos', '1010', "../../../commonlib/trunk/php/mantenedor.php?modulo=producto&cod_item_menu=1010"),
				new item_menu('-'),
				new item_menu('Usuarios', '1015', "../../../commonlib/trunk/php/mantenedor.php?modulo=usuario&cod_item_menu=1015"),
				new item_menu('Perfiles', '1020', "../../../commonlib/trunk/php/mantenedor.php?modulo=perfil&cod_item_menu=1020"),
				new item_menu('-'),
				new item_menu('Parámetros', '1025', "../appl/parametro/wi_parametro.php?cod_item_menu=1025"));

$ventas = array( new item_menu('Cotización', '1505', "../../../commonlib/trunk/php/mantenedor.php?modulo=cotizacion&cod_item_menu=1505"),
				new item_menu('Nota Venta', '1510', "../../../commonlib/trunk/php/mantenedor.php?modulo=nota_venta&cod_item_menu=1510"),
				new item_menu('Orden Compra', '1520', "../../../commonlib/trunk/php/mantenedor.php?modulo=orden_compra&cod_item_menu=1520"),
				new item_menu('-'),
				new item_menu('Guía Despacho', '1525', "../../../commonlib/trunk/php/mantenedor.php?modulo=guia_despacho&cod_item_menu=1525"),
				new item_menu('Guía Recepción', '1530', "../../../commonlib/trunk/php/mantenedor.php?modulo=guia_recepcion&cod_item_menu=1530"),
				new item_menu('Factura Ventas', '1535', "../../../commonlib/trunk/php/mantenedor.php?modulo=factura&cod_item_menu=1535"),
				new item_menu('Nota Crédito', '1540', "../../../commonlib/trunk/php/mantenedor.php?modulo=nota_credito&cod_item_menu=1540"));
			
$arriendo = array(new item_menu('Cotizacion Arriendo', '2005', "../../../commonlib/trunk/php/mantenedor.php?modulo=cotizacion_arriendo&cod_item_menu=2005")
				,new item_menu('-')
				,new item_menu('Contrato', '2010', "../../../commonlib/trunk/php/mantenedor.php?modulo=arriendo&cod_item_menu=2010")
				,new item_menu('Modificación Contrato', '2015', "../../../commonlib/trunk/php/mantenedor.php?modulo=mod_arriendo&cod_item_menu=2015")
				//new item_menu('Orden Compra Arriendo', '2020', "../../../commonlib/trunk/php/mantenedor.php?modulo=orden_compra_arriendo&cod_item_menu=2020"),
				,new item_menu('Guía Despacho Arriendo', '2025', "../../../commonlib/trunk/php/mantenedor.php?modulo=guia_despacho_arriendo&cod_item_menu=2025")
				,new item_menu('Guía Recepción Arriendo', '2030', "../../../commonlib/trunk/php/mantenedor.php?modulo=guia_recepcion_arriendo&cod_item_menu=2030")
				,new item_menu('-')
				,new item_menu('Factura Arriendos', '2035', "../../../commonlib/trunk/php/mantenedor.php?modulo=factura_arriendo&cod_item_menu=2035")
				,new item_menu('-')
				,new item_menu('Orden Compra Arriendo', '2040', "../../../commonlib/trunk/php/mantenedor.php?modulo=orden_compra_arriendo&cod_item_menu=2040")
				//,new item_menu('Nota Crédito Arriendo', '2040', "../../../commonlib/trunk/php/mantenedor.php?modulo=nota_credito_arriendo&cod_item_menu=2040")
				//,new item_menu('-')
				//,new item_menu('Bodega', '2045', "../../../commonlib/trunk/php/mantenedor.php?modulo=bodega&cod_item_menu=2045")
				//,new item_menu('Entrada bodega', '2050', "../../../commonlib/trunk/php/mantenedor.php?modulo=entrada_bodega&cod_item_menu=2050")
				//,new item_menu('Salida bodega', '2055', "../../../commonlib/trunk/php/mantenedor.php?modulo=salida_bodega&cod_item_menu=2055")
				);
				
$administracion = array( new item_menu('Ingreso Pago', '2505', "../../../commonlib/trunk/php/mantenedor.php?modulo=ingreso_pago&cod_item_menu=2505"),
				new item_menu('-'),
				new item_menu('Asignación Documentos', '2520', "../../../commonlib/trunk/php/mantenedor.php?modulo=asig_nro_doc_sii&cod_item_menu=2520"),
				new item_menu('-'),
				new item_menu('FA Proveedor', '2525', "../../../commonlib/trunk/php/mantenedor.php?modulo=faprov&cod_item_menu=2525"),
				new item_menu('NC Proveedor', '2526',"../../../commonlib/trunk/php/mantenedor.php?modulo=ncprov&cod_item_menu=2526"),
				new item_menu('Pago Proveedor', '2530', "../../../commonlib/trunk/php/mantenedor.php?modulo=pago_faprov&cod_item_menu=2530"),
				new item_menu('-'),
				new item_menu('Traspaso Softland', '2545', "../../../commonlib/trunk/php/mantenedor.php?modulo=envio_softland&cod_item_menu=2545"),
				new item_menu('-'),
				new item_menu('Gasto Fijo', '2550', "../../../commonlib/trunk/php/mantenedor.php?modulo=gasto_fijo&cod_item_menu=2550"),
				);
								
$informes = array(new item_menu('Facturas por Equipo', '4005', "../../../commonlib/trunk/php/mantenedor.php?modulo=inf_ventas_por_equipo&cod_item_menu=4015"),
				new item_menu('Facturas por Cobrar', '4035', "../../../commonlib/trunk/php/mantenedor.php?modulo=inf_facturas_por_cobrar&cod_item_menu=4035"),
				new item_menu('Equipos por despachar', '4017', "../../../commonlib/trunk/php/mantenedor.php?modulo=inf_por_despachar&cod_item_menu=4017"),
			//new item_menu('Facturas por Cliente', '4060', "../appl/inf_facturas_por_cliente/inf_facturas_por_cliente.php")
				);
				
$menu = new menu(array(new item_menu('Archivo', '15', '', $archivo), 
						new item_menu('Maestros', '15', '', $maestro),
						new item_menu('Ventas', '15', '', $ventas),
						new item_menu('Rental', '10', '', $arriendo),
						new item_menu('Administración', '15', '', $administracion),
						new item_menu('Informes', '15', '', $informes)),288);
?>
<?php
function auto_load($class_name) {
	if ($class_name=='print_dw_resultado_mes') {
		require_once(dirname(__FILE__)."/appl/inf_resultado/class_print_dw_resultado_mes.php");
   		return;
	}
	if ($class_name=='print_dw_resultado_resumen') {
		require_once(dirname(__FILE__)."/appl/inf_resultado/class_print_dw_resultado_resumen.php");
   		return;
	}
}
?>
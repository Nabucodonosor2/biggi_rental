<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('cot_arriendo')){
  $wo_cot_arriendo = new wo_cot_arriendo();
  $wo_cot_arriendo->retrieve();
}else{
  $wo = session::get('wo_cot_arriendo');
  $wo->procesa_event();
}
?>
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('arr_x_facturar'))
{
    $wo_arr_x_facturar = new wo_arr_x_facturar();
    $wo_arr_x_facturar->retrieve();
} else
{
  $wo = session::get('wo_arr_x_facturar');
  $wo->procesa_event();
}
?>
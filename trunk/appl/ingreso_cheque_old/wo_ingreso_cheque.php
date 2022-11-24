<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('ingreso_cheque'))
{
  $wo_ingreso_cheque = new wo_ingreso_cheque();
  $wo_ingreso_cheque->retrieve();
} else
{
  $wo = session::get('wo_ingreso_cheque');
  $wo->procesa_event();
}
?>
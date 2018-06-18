<?php
require_once('database.php');

function wup_activator(){
  wup_run_migration();
}

function wup_deactivator(){
  die('got run');
  delete_site_option('wup_db_version');
}
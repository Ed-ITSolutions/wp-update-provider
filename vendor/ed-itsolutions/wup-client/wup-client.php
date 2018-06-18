<?php
function wup_client($type, $slug, $url){
  if($type != 'theme' && $type != 'plugin'){
    throw "WUPClient must be theme or plugin";
  }

  require_once('lib/wup-client.php');

  $client = new WUPClient($type, $slug, $url);
}
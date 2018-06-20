<?php
require(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

wup_build(
  'wp-update-provider',
  dirname(dirname(__FILE__)),
  '6af435c747', //getenv('WUP_DEPLOY_KEY'),
  'http://local.ed-it.solutions/wp-admin/admin-post.php'
);
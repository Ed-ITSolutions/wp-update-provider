<?php
require(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

wup_build(
  'wp-update-provider',
  dirname(dirname(__FILE__)),
  getenv('WUP_DEPLOY_KEY'),
  'https://wp.ed-it.solutions/wp-admin/admin-post.php'
);
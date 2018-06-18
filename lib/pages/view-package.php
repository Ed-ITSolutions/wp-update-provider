<?php
  require(plugin_dir_path(__FILE__) . '../tables/domains.php');

  $table = new WUPDomainsTable($package['id']);
  $table->prepare_items();
?>

<div class="wrap">
  <h2><?php echo($package['name']); ?></h2>

  <h3>Current Version</h3>
  <p>
    Version Number: <?php echo($version['version']); ?>
  </p>
  <?php $table->display(); ?>
</div>
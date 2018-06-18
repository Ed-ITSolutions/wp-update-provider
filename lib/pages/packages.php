<?php
  require(plugin_dir_path(__FILE__) . '../tables/packages.php');

  $table = new WUPPackagesTable();
  $table->prepare_items();
?>

<div class="wrap">
  <h2>WP Update Provider <a href="<?php echo(admin_url('admin.php?page=wup_add_package')); ?>" class="page-title-action">Add New</a></h2>

  <?php $table->display(); ?>
</div>
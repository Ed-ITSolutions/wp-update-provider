<?php
  require(plugin_dir_path(__FILE__) . '../tables/domains.php');

  if(isset($_FILES['file'])){
    if(!file_exists(wp_upload_dir()['basedir'] . '/wup-releases')){
      $dir = wp_upload_dir()['basedir'] . '/wup-releases';
      mkdir($dir);
    }

    if(!file_exists(wp_upload_dir()['basedir'] . '/wup-releases/' . $package['slug'])){
      $dir = wp_upload_dir()['basedir'] . '/wup-releases/' . $package['slug'];
      mkdir($dir);
    }

    require_once(dirname(__FILE__) . '/../../vendor/wp-metadata/extension-meta.php');

    $meta = WshWordPressPackageParser::parsePackage($_FILES['file']['tmp_name'], true);

    move_uploaded_file(
      $_FILES['file']['tmp_name'],
      wp_upload_dir()['basedir'] . '/wup-releases/' . $package['slug'] . '/' . $meta['header']['Version'] . '.zip'
    );

    $pluginData = serialize($meta);

    $sql = "INSERT INTO {$wpdb->prefix}wup_versions (`packageId`, `version`, `releaseDate`, `pluginData`) VALUES ('{$package['id']}', '{$meta['header']['Version']}', NOW(), '{$pluginData}')";

    $wpdb->query($sql);
  }

  $table = new WUPDomainsTable($package['id']);
  $table->prepare_items();
?>

<div class="wrap">
  <h2><?php echo($package['name']); ?></h2>

  <h3>Current Version</h3>
  <p>
    Version Number: <?php echo($version['version']); ?><br ?>
    <a href="<?php echo(content_url('uploads/wup-releases/' . $package['slug'] . '/' . $version['version'] . '.zip')); ?>">Download latest ZIP</a>
  </p>
  <h3>Deploy Key</h3>
  <p><?php echo($package['deployKey']); ?></p>
  <p><a href="?page=wup_package&package=<?php echo($package['slug']); ?>&action=newKey">Regenerate Key</a></p>
  <form action="" method="POST" enctype="multipart/form-data">
    Release a new Version:
    <input type="file" name="file" id="file">
    <input type="submit" value="Release new version" name="submit">
  </form>
  <?php $table->display(); ?>
</div>
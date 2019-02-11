<?php
require_once('database.php');

class WPUpdateProvider{
  public function run(){
    wup_run_migration();

    add_action('admin_menu', array($this, 'menus'));

    add_action('admin_post_nopriv_wup_release', array($this, 'newRelease'));
    add_action('admin_post_wup_release', array($this, 'newRelease'));

    add_action('init', array($this, 'rewrites'));
    add_action('template_redirect', array($this, 'returnJson'), 1);

    wup_client('plugin', 'wp-update-provider', 'https://wp.ed-it.solutions/wup/wp-update-provider');
  }

  public function log($message){
    $log = get_option('wp_update_provider_log', array());
    $log[] = $message;
    update_option('wp_update_provider_log', array_slice($log, -50, 50));
  }

  public function returnJson(){
    global $wp_query, $wp, $wpdb;

    if(substr($wp->request, 0, 3) != 'wup'){
      return;
    }

    $headers = getallheaders();
    $slug = $wp_query->get('wup_package');

    if(!isset($headers['WP_DOMAIN']) || !isset($headers['WP_VERSION'])){
      $this->log('Request for ' . $slug . ' made without WP_DOMAIN or WP_VERSION header');

      wp_send_json(array(
        'error' => 'WP_DOMAIN or WP_VERSION header missing.'
      ));
      return;
    }

    if(isset($headers['WUP_CLIENT_VERSION'])){
      $wup_client_version = $headers['WUP_CLIENT_VERSION'];
    }else{
      $wup_client_version = "< 0.1.2";
    }

    $package = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wup_packages WHERE `slug` = '{$slug}'", 'ARRAY_A');

    if($package == null){
      $this->log('Request for ' . $slug . ' from ' . $headers['WP_DOMAIN'] . ' but the package does not exist.');

      wp_send_json(array(
        'slug' => $slug,
        'error' => 'Slug does not match a package.'
      ));
      return;
    }

    $version = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wup_versions WHERE `packageId` = '{$package['id']}' ORDER BY `id` DESC LIMIT 1", 'ARRAY_A');

    if($version == null){
      $this->log('Request for ' . $slug . ' from ' . $headers['WP_DOMAIN'] . ' but the package has no versions.');

      wp_send_json(array(
        'slug' => $slug,
        'error' => 'No versions exist for this package'
      ));
      return;
    }

    $data = unserialize($version['pluginData']);

    $domain = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wup_domains WHERE `domain` = '{$headers['WP_DOMAIN']}' AND `packageId` = '{$package['id']}' ORDER BY `id` DESC LIMIT 1", 'ARRAY_A');

    if($domain == null){
      $sql = "INSERT INTO {$wpdb->prefix}wup_domains (`packageId`, `domain`, `version`) VALUES ('{$package['id']}', '{$headers['WP_DOMAIN']}', '{$headers['WP_VERSION']}')";
    }else{
      $sql = "UPDATE {$wpdb->prefix}wup_domains SET `version` = '{$headers['WP_VERSION']}', `lastCheckIn` = CURRENT_TIMESTAMP WHERE `id` = '{$domain['id']}' ";
    }

    $this->log('Request for ' . $slug . ' from ' . $headers['WP_DOMAIN'] . ' @ ' . $headers['WP_VERSION'] . '.');

    $wpdb->query($sql);

    if($headers['WP_VERSION'] === $version['version']){
      // This site is already running the latest.
      $sql = "UPDATE {$wpdb->prefix}wup_packages SET `wup_client_version` = '{$wup_client_version}' WHERE `id` = '{$package['id']}'";
      $wpdb->query($sql);
    }

    wp_send_json(array(
      'slug' => $slug,
      'name' => $data['header']['Name'],
      'version' => $version['version'],
      'last_updated' => $version['releaseDate'],
      'detailsUrl' => $data['header']['DetailsURI'],
      'downloadUrl' => content_url('uploads/wup-releases/' . $package['slug'] . '/' . $version['version'] . '.zip'),
      'image_svg' => $data['header']['ImageSVG'],
      'image_2x' => $data['header']['Image2X'],
      'image_1x' => $data['header']['Image1X'],
      'image_default' => $data['header']['Image']
    ));
  }

  public function rewrites(){
    add_rewrite_tag('%wup_package%', '([^&]+)');
    add_rewrite_rule('wup/(.*)?', 'index.php?wup_package=$matches[1]', 'top');
  }

  public function menus(){
    add_menu_page(
      'WP Update Provider',
      'WP Update Provider',
      'manage_options',
      'wup',
      array($this, 'mainPage'),
      'dashicons-update',
      100
    );

    add_submenu_page(
      'wup',
      'Add Package',
      'Add Package',
      'manage_options',
      'wup_add_package',
      array($this, 'addPackagePage')
    );

    add_submenu_page(
      'wup',
      'Log',
      'Log',
      'manage_options',
      'wup_view_log',
      array($this, 'viewLog')
    );

    add_submenu_page(
      '',
      'View Package',
      'View Package',
      'manage_options',
      'wup_package',
      array($this, 'viewPackagePage')
    );
  }

  public function viewLog(){
    require('pages/log.php');
  }

  public function mainPage(){
    if(isset($_POST['package'])){
      $this->addPackage($_POST['package']);
    }

    require('pages/packages.php');
  }

  public function addPackagePage(){
    require('pages/add-package.php');
  }

  private function addPackage($package){
    global $wpdb;

    $deployKey = wp_create_nonce('deploy-' . $package['slug']);

    $sql = "INSERT INTO {$wpdb->prefix}wup_packages (`name`, `slug`, `deployKey`) VALUES ('{$package['name']}', '{$package['slug']}', '{$deployKey}')";

    $wpdb->query($sql);

    return;
  }

  public function viewPackagePage(){
    global $wpdb;

    if(array_key_exists('action', $_GET) && $_GET['action'] == 'newKey'){
      $deployKey = wp_create_nonce('deploy-' . $_GET['package'] . time());

      $sql = "UPDATE {$wpdb->prefix}wup_packages SET `deployKey` = '{$deployKey}' WHERE `slug` = '{$_GET['package']}'";

      $wpdb->query($sql);
    }

    if(array_key_exists('action', $_GET) && $_GET['action'] == 'delete'){
      $nonce = esc_attr($_REQUEST['_wpnonce']);

      if(!wp_verify_nonce($nonce, 'wup_delete_domain')){
        $wpdb->delete(
          "{$wpdb->prefix}wup_packages",
          ['id' => $_GET['domain']],
          ['%d']
        );
      }
    }

    $package = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wup_packages WHERE `slug` = '{$_GET['package']}'", 'ARRAY_A');
    $version = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wup_versions WHERE `packageId` = '{$package['id']}' ORDER BY `id` DESC LIMIT 1", 'ARRAY_A');

    require('pages/view-package.php');
  }

  public function newRelease(){
    global $wpdb;

    $this->log('New release attempted.');

    $package = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wup_packages WHERE `deployKey` = '{$_POST['deployKey']}'", 'ARRAY_A');

    if(!isset($package)){
      $this->log('Deploy attempted with invalid deploy key.');

      echo(json_encode(array(
        'error' => 'Invalid Deploy Key.'
      )));
      return;
    }

    if(!isset($_FILES['release'])){
      $this->log('No release provided for package.');

      echo(json_encode(array(
        'error' => 'No release provided.'
      )));

      return;
    }

    if($_FILES['release']['error'] === 1){
      $this->log('New release was over filesize');

      echo(json_encode(array(
        'error' => 'File is over upload_max_filesize'
      )));

      return;
    }

    $fileType = wp_check_filetype(basename($_FILES['release']['name']));

    if($fileType['type'] != 'application/zip'){
      $this->log('New release was not a zip');

      echo(json_encode(array(
        'error' => 'File is not a zip'
      )));

      return;
    }

    // Create upload directory
    if(!file_exists(wp_upload_dir()['basedir'] . '/wup-releases')){
      $dir = wp_upload_dir()['basedir'] . '/wup-releases';
      mkdir($dir);
    }

    if(!file_exists(wp_upload_dir()['basedir'] . '/wup-releases/' . $package['slug'])){
      $dir = wp_upload_dir()['basedir'] . '/wup-releases/' . $package['slug'];
      mkdir($dir);
    }

    require_once(dirname(__FILE__) . '/../vendor/wp-metadata/extension-meta.php');

    $meta = WshWordPressPackageParser::parsePackage($_FILES['release']['tmp_name'], true);

    $version = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wup_versions WHERE `packageId` = '{$package['id']}' AND `version` = '{$meta['header']['Version']}' ORDER BY `id` DESC LIMIT 1", 'ARRAY_A');
    if(isset($version)){
      $this->log('Version ' . $meta['header']['Version'] . ' already exists');

      echo(json_encode(array(
        'error' => 'Version ' . $meta['header']['Version'] . ' already exists'
      )));

      return;
    }

    move_uploaded_file(
      $_FILES['release']['tmp_name'],
      wp_upload_dir()['basedir'] . '/wup-releases/' . $package['slug'] . '/' . $meta['header']['Version'] . '.zip'
    );

    copy(wp_upload_dir()['basedir'] . '/wup-releases/' . $package['slug'] . '/' . $meta['header']['Version'] . '.zip', wp_upload_dir()['basedir'] . '/wup-releases/' . $package['slug'] . '/latest.zip');

    $pluginData = serialize($meta);

    $sql = "INSERT INTO {$wpdb->prefix}wup_versions (`packageId`, `version`, `releaseDate`, `pluginData`) VALUES ('{$package['id']}', '{$meta['header']['Version']}', NOW(), '{$pluginData}')";

    $wpdb->query($sql);

    $this->log('New release!');

    echo(json_encode(array(
      'success' => 'Update received.'
    )));
  }
}
<?php
// if you change this make sure you change the class name below (0.0.1 becomes WUPClient0_0_1).
$classVersion = "0.1.0";

if(!defined('WUP_CLIENT_CLASS_VERSION')){
  define('WUP_CLIENT_CLASS_VERSION', $classVersion);
}else{
  if(Composer\Semver\Comparator::greaterThan($classVersion, WUP_CLIENT_CLASS_VERSION)){
    define('WUP_CLIENT_CLASS_VERSION', $classVersion);
  }
}

class WUPClient{
  public function __construct($type, $slug, $url){
    $className = 'WUPClient' . str_replace('.', '_', WUP_CLIENT_CLASS_VERSION);

    new $className($type, $slug, $url);
  }
}


class WUPClient0_1_0{
  public $url;
  public $type;
  public $slug;
  public $settingName;

  public function __construct($type, $slug, $url){
    $this->type = $type;
    $this->url = $url;
    $this->slug = $slug;

    $this->settingName = 'wup_client_' . $type . '_' . $slug;

    $this->hooks();
  }

  public function hooks(){    
    // Add actions when WordPress would normally check for updates.
    add_action('load-' . $this->type . '.php', array($this, 'maybeUpdate'));
    add_action('load-update.php', array($this, 'maybeUpdate'));
    add_action('load-update-core.php', array($this, 'maybeUpdate'));
    add_action('wp_update_' . $this->type . 's', array($this, 'maybeUpdate'));

    // Inject the update 
    add_filter('site_transient_update_' . $this->type . 's', array($this,'injectUpdate'));

    // Clear data when WordPress clears its cache
    add_action('delete_site_transient_update_' . $this->type . 's', array($this, 'deleteStoredData'));
  }

  public function maybeUpdate(){
    $state = get_option($this->settingName, new StdClass);

    // If this is a force check OR it has been 12 hours since the last check OR the state is empty.
    if(
      isset($_GET['force-check'])
      ||
      (
        !empty($state)
        &&
        $state->lastCheck < (time() - (12 * HOUR_IN_SECONDS))
      )
      ||
      empty($state)
    ){
      $this->checkForUpdates();
    }
  }

  public function deleteStoredData(){
    delete_option($this->settingName);
  }

  public function injectUpdate($updates){
    $state = get_option($this->settingName);

    if(
      !empty($state)
      &&
      isset($state->wupVersion)
      &&
      !empty($state->wupVersion)
      &&
      Composer\Semver\Comparator::greaterThan($state->wupVersion, $state->localVersion)
    ){
			$updates->response[$this->updateResponseKey()] = $this->updateResponse($state);
    }

    return $updates;
  }

  public function updateResponseKey(){
    if($this->type == 'theme'){
      return $this->slug;
    }else{
      return $this->slug . '/' . $this->slug . '.php';
    }
  }

  public function updateResponse($state){
    if($this->type == 'theme'){
      return array(
        'new_version' => $state->wupVersion,
        'url' => $state->detailsUrl,
        'package' => $state->downloadUrl,
        'theme' => $this->slug
      );
    }else{
      $update = new StdClass;
      $update->slug = $this->slug;
      $update->plugin = $this->updateResponseKey();
      $update->new_version = $state->wupVersion;
      $update->package = $state->downloadUrl;
      $update->url = $state->detailsUrl;

      return $update;
    }
  }

  public function checkForUpdates(){
    $state = get_option($this->settingName, new StdClass);

    if(!empty($state)){
      $state = new StdClass;
      $state->lastCheck = 0;
      $state->localVersion = '';
      $state->wupVersion = null;
      $state->detailsUrl = '';
      $state->downloadUrl = '';
    }

    $state->lastCheck = time();
    
    if($this->type == 'plugin'){
      $state->localVersion = $this->getLocalPluginVersion();
    }elseif($this->type == 'theme'){
      $state->localVersion = $this->getLocalThemeVersion();
    }

    // Save the state before update just in case things go wrong.
    update_option($this->settingName, $state);

    $data = $this->getWUPData($state->localVersion);
    $state->wupVersion = $data->version;
    $state->detailsUrl = $data->detailsUrl;
    $state->downloadUrl = $data->downloadUrl;
    update_option($this->settingName, $state);
  }

  public function getLocalPluginVersion(){
    if(!function_exists('get_plugin_data')){
			require_once(ABSPATH . '/wp-admin/includes/plugin.php');
    }
    
    $filePath = WP_PLUGIN_DIR . '/' . $this->slug . '/' . $this->slug . '.php';

    $data = get_plugin_data($filePath);
    return $data['Version'];
  }

  public function getLocalThemeVersion(){
    $theme = wp_get_theme($this->theme);
	  return $theme->get('Version');
  }

  public function getWUPData($localVersion){
    global $wp_version;

    $args = array(
      'timeout'     => 5,
      'redirection' => 5,
      'httpversion' => '1.0',
      'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
      'blocking'    => true,
      'headers'     => array(
        'WP_DOMAIN' => home_url(),
        'WP_VERSION' => $localVersion
      ),
      'cookies'     => array(),
      'body'        => null,
      'compress'    => false,
      'decompress'  => true,
      'sslverify'   => true,
      'stream'      => false,
      'filename'    => null
    );

    $response = wp_remote_get($this->url, $args);

    return json_decode($response['body']);
  }
}
<?php

if(!class_exists('WP_List_Table')){
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class WUPPackagesTable extends WP_List_Table{
	public function __construct() {
		parent::__construct([
			'singular' => __('Package', 'wp-update-provider'),
			'plural' => __('Packages', 'wp-update-provider'),
			'ajax' => false 
		]);
  }

  public static function get_packages($per_page = 5, $page_number = 1){
    global $wpdb;
    $sql = "SELECT * FROM {$wpdb->prefix}wup_packages";
  
    if(!empty($_REQUEST['orderby'])){
      $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
      $sql .= ! empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
    }else{
      $sql .= ' ORDER BY name';
      $sql .= ! empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
    }
  
    $sql .= " LIMIT $per_page";
    $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
    
    $result = $wpdb->get_results($sql, 'ARRAY_A');
  
    return $result;
  }

  public static function record_count(){
    global $wpdb;
  
    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wup_packages";
  
    return $wpdb->get_var($sql);
  }

  public function no_items(){
    _e('You have no packages! Create one and it will appear here', 'wp-update-provider');
  }

  function column_name($item){
    $url = admin_url('admin.php?page=wup_package&package=' . $item['slug']);

    return "<a href=\"{$url}\">{$item['name']}</a>";
  }

  function column_installs($item){
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wup_domains WHERE `packageId` = '{$item['id']}'";

    return $wpdb->get_var($sql);
  }

  function column_versions($item){
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wup_versions WHERE `packageId` = '{$item['id']}'";

    return $wpdb->get_var($sql);
  }

  function column_default($item, $column_name){
    switch($column_name){
      case 'slug':
      case 'deployKey':
        return $item[$column_name];
      default:
        return print_r($item, true);
    }
  }

  function get_columns(){
    $columns = [
      'name' => __('Name', 'wp-update-provider'),
      'slug' => __('Slug', 'wp-update-provider'),
      'versions' => __('Versions', 'wp-update-provider'),
      'deployKey' => __('Deploy Key', 'wp-update-provider'),
      'installs' => __('Installs', 'wp-update-provider')
    ];
  
    return $columns;
  }

  public function get_sortable_columns(){
    $sortableColumns = array(
      'name' => array('name', true),
      'slug' => array('slug', false),
      'installs' => array('installs', false)
    );
  
    return $sortableColumns;
  }

  public function prepare_items() {
    $this->_column_headers = array(self::get_columns(), array(), self::get_sortable_columns());
  
    //$this->process_bulk_action();
  
    $per_page = $this->get_items_per_page('packages_per_page', 30);
    $current_page = $this->get_pagenum();
    $total_items = self::record_count();
  
    $this->set_pagination_args([
      'total_items' => $total_items,
      'per_page' => $per_page
    ]);
  
    $this->items = self::get_packages($per_page, $current_page);
  }
}

?>

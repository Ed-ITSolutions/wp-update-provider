<?php

if(!class_exists('WP_List_Table')){
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class WUPDomainsTable extends WP_List_Table{
  public $packageId;

	public function __construct($packageId){
    $this->packageId = $packageId;
		parent::__construct([
			'singular' => __('Domain', 'wp-update-provider'),
			'plural' => __('Domains', 'wp-update-provider'),
			'ajax' => false 
		]);
  }

  public static function get_domains($per_page = 5, $page_number = 1, $packageId){
    global $wpdb;
    $sql = "SELECT * FROM {$wpdb->prefix}wup_domains WHERE `packageId` = '{$packageId}'";
  
    if(!empty($_REQUEST['orderby'])){
      $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
      $sql .= ! empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
    }
  
    $sql .= " LIMIT $per_page";
    $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
    
    $result = $wpdb->get_results($sql, 'ARRAY_A');
  
    return $result;
  }

  public static function record_count($packageId){
    global $wpdb;
  
    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wup_domains WHERE `packageId` = '{$packageId}'";
  
    return $wpdb->get_var($sql);
  }

  public function no_items(){
    _e('No sites are using this package.', 'wp-update-provider');
  }

  function column_domain($item){
    $domain = '<strong>' . $item['domain'] . '</strong>';

    $deleteNonce = wp_create_nonce('wup_delete_domain');

    $actions = [
      'delete' => sprintf(
        '<a href="?page=%s&action=%s&domain=%s&_wpnonce=%s">Delete</a>',
        esc_attr( $_REQUEST['page'] ),
        'delete',
        absint($item['id']),
        $deleteNonce
      )
    ];

    return $domain . $this->row_actions($actions);
  }

  function column_default($item, $column_name){
    switch($column_name){
      case 'domain':
      case 'version':
      case 'lastCheckIn':
        return $item[$column_name];
      default:
        return print_r($item, true);
    }
  }

  function get_columns(){
    $columns = [
      'domain' => __('Domain', 'wp-update-provider'),
      'version' => __('Version', 'wp-update-provider'),
      'lastCheckIn' => __('Last Check In', 'wp-update-provider')
    ];
  
    return $columns;
  }

  public function get_sortable_columns(){
    $sortableColumns = array(
      'domain' => array('domain', true),
      'version' => array('version', false)
    );
  
    return $sortableColumns;
  }

  public function prepare_items(){
    $this->_column_headers = array(self::get_columns(), array(), self::get_sortable_columns());
  
    //$this->process_bulk_action();
  
    $per_page = $this->get_items_per_page('packages_per_page', 20);
    $current_page = $this->get_pagenum();
    $total_items = self::record_count($this->packageId);
  
    $this->set_pagination_args([
      'total_items' => $total_items,
      'per_page' => $per_page
    ]);
  
    $this->items = self::get_domains($per_page, $current_page, $this->packageId);
  }
}

?>

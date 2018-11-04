<?php
/*
  Plugin Name: IGDB API
  Plugin URI: 
  Description: This is IGDB API plugin, custom options with admin setting page.
  Author: Jordan R
  Version: 1.0
  Author URI: 
 */

class IGDBPage {
  public function __construct() {
    add_action('wp_loaded', array($this,'igdbAddStyles'));
    add_action('admin_menu', array($this,'igdb_create_admin_pages'));
    register_activation_hook(__FILE__, array($this, 'create_igdb_database_table'));
  }

  public function igdbAddStyles() {
    wp_register_style( 'igdbapirbbstyle', plugins_url('css/style.css',__FILE__ ));
  }

  public function create_igdb_database_table() {
    global $wpdb;
    $igdbSearched = $wpdb->prefix.'igdbsearched';
    $igdbgames = $wpdb->prefix.'igdbgames';
    $this->create_igdb_searched_db_table($igdbSearched);
    $this->create_igdb_games_db_table($igdbgames);
  }

  public function create_igdb_games_db_table($table_name) {
    global $wpdb;
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = 'CREATE TABLE ' . $table_name . ' ( `id` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, `igdb_id` VARCHAR(255), `addedtocommerce` tinyint(1), `post_id` INTEGER(10) UNSIGNED,`game_ids_processed` tinyint(1), `itemObject` BLOB)';
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
  }

  public function create_igdb_searched_db_table($table_name) {
    global $wpdb;
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = 'CREATE TABLE ' . $table_name . ' ( `id` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, `searchKey` VARCHAR(255), `searchobj` LONGTEXT, `timestamp` VARCHAR(255))';
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
  }

  public function igdb_create_admin_pages() {
    
    $page_title = 'IGDB API';
    $menu_title = 'IGDB API';
    $capability = 'manage_options';
    $menu_slug = 'igdb_api_rbb';
    $parent_slug = $menu_slug;
    $function = array($this,'igdb_api_page_display');
    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function);

    $s_page_title = 'IGDB Products';
    $s_menu_title = 'IGDB Products';
    $s_menu_slug = 'igdb_products_rbb';
    $s_function = array($this,'igdb_products_page_display');
    add_submenu_page( $parent_slug, $s_page_title, $s_menu_title, $capability, $s_menu_slug, $s_function);
    
    $st_page_title = 'IGDB Settings';
    $st_menu_title = 'IGDB Settings';
    $st_menu_slug = 'igdb_settings_rbb';
    $st_function = array($this,'igdb_settings_page_display');
    add_submenu_page( $parent_slug, $st_page_title, $st_menu_title, $capability, $st_menu_slug, $st_function);

  }

  public function igdb_api_page_display(){

    wp_enqueue_style('igdbapirbbstyle');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user');
    }
    
    if (!empty($_POST))
    {
        if (! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'EbayNonce' ) )
        {
            print 'Sorry, your nonce did not verify.';
            exit;
        }
    }

    if (isset($_POST['igdbsearchkey'])) {
      
      $igdbSearchKey = $_POST['igdbsearchkey'];
      $searchKey = sanitize_text_field($igdbSearchKey);
      update_option('igdbsearchkey', $searchKey);
      $res = $this->checkForDbValue('igdbsearched','searchkey',$searchKey);
      if($res){
        $results =  "Search Key is Already in Database";
      }else{

        $options = array(
          'fields'=> '*',
          'searchkey' => $searchKey,
          'order' => 'rating',
          'limit' => 50,
          'scroll' => 1 
        );
  
        $results = $this->call_IGDB_API('games',$options);

      }
    }

    $searchKey = get_option('igdbsearchkey');

    if(count($_POST['delete_list'])){
      $this->deleteSearchKeys('igdbSearched','searchkey', $_POST['delete_list']);
      $results = "Item(s) Deleted from Database";
    }
    
    $searchKeyResults = $this->getAllResultsById('igdbSearched');
    include 'form-file.php';

  }

  public function igdb_products_page_display() {
    wp_enqueue_style('igdbapirbbstyle');
    //$productResults = $this->getProductResults(0,10);
    include 'products-file.php';
  }

  public function igdb_settings_page_display() {
  
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user');
    }
  
    if (!empty($_POST))
    {
        if (! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'EbayNonce' ) )
        {
            print 'Sorry, your nonce did not verify.';
            exit;
        }
    }
  
    if (isset($_POST['igdbapikey']) && strlen($_POST['igdbapikey']) ) {
        $value = sanitize_text_field($_POST['igdbapikey']);
        update_option('igdbapikey', $value);
    }
  
    $value = get_option('igdbapikey');
    include 'settings-file.php';
  }

  public function call_IGDB_API($type,$options){

    $url = 'https://api-endpoint.igdb.com/'.$type.'/?';

    $fields = array();
    foreach($options as $key => $value){
      $fields[] = $key.'='.$value;
    }

    $field_url = implode('&',$fields);
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url.$field_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'user-key: ' . get_option('igdbapikey'),
        'Accept: application/json',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    // EXECUTE:
    $result = curl_exec($curl);
    if(!$result){
      $results  = "<h3>Oops! The request was not successful. Make sure you are using a valid ";
      $results .= "AppID for the Production environment.</h3>";
    }else{
      $result = json_decode($result);
      $this->addDataToIGDBSearchTable($result);
      $results =  "Search Key is Successfully saved";
    }
    curl_close($curl);
    return $results;
  }

  public function addDataToIGDBSearchTable($data){
    global $wpdb;
    $IGDBSearched = $wpdb->prefix.'IGDBSearched';
    $IGDBSearchedA['id'] = "";
    $IGDBSearchedA['searchKey'] = get_option('igdbsearchkey');
    $IGDBSearchedA['searchobj'] = json_encode($data);
    $IGDBSearchedA['timestamp'] = time();
    $this->sendToTable($IGDBSearched,$IGDBSearchedA);
  }

  public function sendToTable($table,$values){
    global $wpdb;
    $wpdb->insert($table,$values);
  }

  public function checkForDbValue($table,$field,$value){
    global $wpdb;
    $table = $wpdb->prefix.$table;
    $results = $wpdb->get_row( "SELECT count(*) as count FROM $table WHERE $field = '$value'", OBJECT );
    return $results->count;
  }

  public function getAllResultsById($table){
    global $wpdb;
    $table = $wpdb->prefix.$table;
    $results = $wpdb->get_results( "SELECT * FROM $table WHERE id > 0 ORDER BY id DESC", OBJECT );
    return $results;
  }

  public function deleteSearchKeys($table,$field,$valueA){
		global $wpdb;
		$table = $wpdb->prefix.$table;
		$qStr = "DELETE FROM $table WHERE ";
		$qStr .= "$field IN ('";
		$qStr .= implode("','",$valueA);
		$qStr .= "')";
		$wpdb->query( $qStr );
  }

}

global $IGDBPage;
$IGDBPage = new IGDBPage();
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
    add_action('wp_ajax_process_search_key', array( $this, 'process_search_key' ));
    add_action('admin_menu', array($this,'igdb_create_admin_pages'));
    register_activation_hook(__FILE__, array($this, 'create_igdb_database_table'));
  }

  public function igdbAddStyles() {
    wp_register_style( 'igdbapirbbstyle', plugins_url('css/style.css',__FILE__ ));
    wp_register_script( 'igdbapirbbscript', plugins_url('js/igdb.js',__FILE__ ),array('jquery'),TRUE);
    wp_localize_script( 'igdbapirbbscript','myAjax', array('url' => admin_url( 'admin-ajax.php' ),'nonce' => wp_create_nonce( "process_reservation_nonce" ),));
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
        $sql = 'CREATE TABLE ' . $table_name . ' ( `id` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, `igdb_id` VARCHAR(255), `name` VARCHAR(255), `url` VARCHAR(255), `cover_url_id` VARCHAR(255), `addedtocommerce` tinyint(1), `post_id` INTEGER(10) UNSIGNED,`is_processed` tinyint(1), `itemObject` BLOB,`timestamp` VARCHAR(255))';
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
  }

  public function create_igdb_searched_db_table($table_name) {
    global $wpdb;
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = 'CREATE TABLE ' . $table_name . ' ( `id` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, `searchKey` VARCHAR(255),`is_processed` tinyint(1), `searchobj` LONGTEXT, `timestamp` VARCHAR(255))';
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

  public function processForTesting(){
    echo "<pre>";
		echo "Testing Enabled";
		$this->igdbProcessSearchTable();
		die;
  }

  public function process_search_key(){
    check_ajax_referer( 'process_reservation_nonce', 'nonce' );
    if( true ){
      $this->igdbProcessSearchTable($_POST['id']);
      wp_send_json_error('Success');
    } else {
      wp_send_json_error( array( 'error' => $custom_error ) );
    }
  }

  public function igdb_api_page_display(){

    //$this->processForTesting();

    wp_enqueue_style('igdbapirbbstyle');
    wp_enqueue_script('igdbapirbbscript');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user');
    }
    
    if (!empty($_POST))
    {
        if (! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'IgdbNonce' ) )
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
          'search' => urlencode($searchKey),
          'limit' => 50,
        );
  
        $results = $this->call_IGDB_API('games',$options);

      }
    }

    $searchKey = get_option('igdbsearchkey');

    if(count($_POST['delete_list'])){
      $this->deleteSearchKeys('igdbsearched','searchkey', $_POST['delete_list']);
      $results = "Item(s) Deleted from Database";
    }
    
    $searchKeyResults = $this->getAllResultsById('igdbsearched');
    include 'form-file.php';

  }

  public function igdb_products_page_display() {

    wp_enqueue_style('igdbapirbbstyle');
    

    $perpage = 25;
    
    if(isset($_GET['paged']) & !empty($_GET['paged'])){
      $curpage = $_GET['paged'];
    }else{
      $curpage = 1;
    }

    $start = ($curpage * $perpage) - $perpage;
    $totalRows = $this->getRowCount('igdbgames');
    $totalItems = $totalRows->count;
    $startpage = 1;
    $endpage = ceil($totalItems/$perpage);
    $nextpage = $curpage + 1;
    $previouspage = $curpage - 1;
    
    $productResults = $this->getGamesResults($start,$perpage);
    include 'products-file.php';
  }

  public function igdb_settings_page_display() {
  
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user');
    }
  
    if (!empty($_POST))
    {
        if (! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'IgdbNonce' ) )
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
    $IGDBSearchedA['id'] = "";
    $IGDBSearchedA['searchKey'] = get_option('igdbsearchkey');
    $IGDBSearchedA['is_processed'] = 0;
    $IGDBSearchedA['searchobj'] = json_encode($data);
    $IGDBSearchedA['timestamp'] = time();
    $this->sendToTable('igdbsearched',$IGDBSearchedA);
  }

  public function sendToTable($table,$values){
    global $wpdb;
    $table = $wpdb->prefix.$table;
    $wpdb->insert($table,$values);
  }

  public function checkForDbValue($table,$field,$value){
    global $wpdb;
    $table = $wpdb->prefix.$table;
    $sqlQuery = "SELECT count(*) as count FROM $table WHERE $field = '$value'";
    $results = $wpdb->get_row( $sqlQuery, OBJECT );
    return $results->count;
  }

  public function getAllResultsById($table){
    global $wpdb;
    $table = $wpdb->prefix.$table;
    $results = $wpdb->get_results( "SELECT * FROM $table WHERE id > 0 ORDER BY searchkey ASC", OBJECT );
    return $results;
  }

  public function getDbRow($table,$field,$value){
    global $wpdb;
    $table = $wpdb->prefix.$table;
    $sqlQuery = "SELECT * FROM $table WHERE $field = $value ORDER BY id DESC limit 1";
    $results = $wpdb->get_row( $sqlQuery, OBJECT );
    return $results;
  }

  public function updateTable($table,$field,$value,$where,$where_value){
    global $wpdb;
    $table = $wpdb->prefix.$table;
    $fieldA[$field] = $value;
    $whereA[$where] = $where_value;
    $wpdb->update($table, $fieldA, $whereA);
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

  public function igdbProcessSearchTable($id = 0){
    $table = 'igdbsearched';
    if($id){
      $field = 'id';  
    }else{
      $field = 'is_processed';
    }
    $data = $this->getDbRow($table,$field,$id);
    if($data){
      $result = json_decode($data->searchobj);
      foreach($result as $key => $value){
        $this->igdbUpdateGamesTable($value);
      }
      $field = 'is_processed';
      $this->updateTable($table,$field,1,'id',$data->id);
    }
  }

  public function igdbUpdateGamesTable($value){
    $data = (array) $value;
    $dataA['id'] = "";
    $dataA['igdb_id'] = $data['id'];
    $dataA['name'] = $data['name'];
    $dataA['url'] = $data['url'];
    $dataA['cover_url_id'] = $data['cover']->cloudinary_id;    
    $dataA['addedtocommerce'] = 0;
    $dataA['post_id'] = 0;
    $dataA['is_processed'] = 0;
    $dataA['itemObject'] = $data;
    $dataA['timestamp'] = time();
    $table = 'igdbgames';
    if(strlen($dataA['cover_url_id']) > 0){
      $check = $this->checkForDbValue($table,'igdb_id',$data['id']);
      if(!$check) $this->sendToTable($table,$dataA);
    }
  }

  public function getGamesResults($start,$limit){
    global $wpdb;
    $table = $wpdb->prefix.'igdbgames';
    $query = "SELECT * FROM $table WHERE id > 0 ORDER BY id DESC LIMIT $start,$limit";
    $results = $wpdb->get_results( $query, OBJECT );
    return $results;
  }

  public function getRowCount($table){
    global $wpdb;
    $table = $wpdb->prefix.$table;
    $query = "SELECT count(*) as count FROM $table";
    $results = $wpdb->get_row( $query, OBJECT );
    return $results;
  }

}

global $IGDBPage;
$IGDBPage = new IGDBPage();

class IGDBCronPage {
  public function __construct() {
      //echo '<pre>'; print_r( _get_cron_array() ); echo '</pre>';
      add_filter('cron_schedules', array($this,'igdb_add_cron_interval'));
      register_activation_hook(__FILE__, array($this,'igdb_cron_activation'));
      add_action('igdb_cron_run', array($this,'igdbCallMyFunction'));
      register_deactivation_hook(__FILE__, array($this,'igdb_cron_deactivation'));
  }
  public function igdb_cron_deactivation() {
      wp_clear_scheduled_hook('igdb_cron_run');
  }
  public function igdb_cron_activation(){
      if (! wp_next_scheduled ( 'igdb_cron_run' )) {
          wp_schedule_event(time(), 'SeventeenMinutes', 'igdb_cron_run');
      }
  }
  public function igdb_add_cron_interval( $schedules ) {
      $schedules['SeventeenMinutes'] = array(
          'interval' => 1020,
          'display' => __( 'Every 17 Minutes' ),
      );
      return $schedules;
  }
  public function igdbCallMyFunction(){
    global $IGDBPage;
    $IGDBPage::igdbProcessSearchTable();
  }
}

global $IGDBCronPage;
$IGDBCronPage = new IGDBCronPage();
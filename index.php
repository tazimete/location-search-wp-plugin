
<?php 
/*
Plugin Name: Location Search Autocomplete Box
Plugin URI: http://wordpress.org/plugins/location-search/
Description: This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong: Hello, Dolly. When activated you will randomly see a lyric from <cite>Hello, Dolly</cite> in the upper right of your admin screen on every page.
Author: AGM Tazim
Version: 1.0
Author URI: http://agm-tazim.com/
*/
?>

<?php error_reporting(E_ERROR | E_PARSE); ?>

<?php 
	
	//Adding CSS and Javascript
	function plugin_css_scripts() {
		wp_enqueue_style( 'ls-style-name', plugins_url('css/style.css', __FILE__) );
		//wp_enqueue_script( 'jquery',  plugins_url('js/jquery.js', __FILE__), array(), '2.1.4' );
		//wp_enqueue_script( 'ls-custom-script',  plugins_url('js/script.js', __FILE__), array( 'jquery' ), '2.1.4' );
	}
	
	add_action( 'wp_enqueue_scripts', 'plugin_css_scripts' );



//Creating  Database Table
    function create_table_location_search(){
		
		global $wpdb;
		//$wpdb->prefix.
		$table_name = 'population';
		
		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `location` varchar(150) NOT NULL,
		  `slug` varchar(150) NOT NULL,
		  `population` int(10) unsigned NOT NULL,
		   PRIMARY KEY (`id`)
		 )  ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;";
		  $rs = $wpdb->query($sql);
    }
	
	add_action( 'init', 'create_table_location_search' );

	
	function t22_location_search_plugin_activate() {
		
		//$csv_submit_url = plugins_url('upload_csv.php', __FILE__);
		global $user_ID;
		$ls_page_uc_content = '<div class="form-container"> 
			<h1>
			Select and submit CSV file to import data : 
			</h1>
				
			<form action="#" method="post" enctype="multipart/form-data" name="form1" id="form1"> 
				<input name="csv" type="file" id="csv" /> 
				<input type="submit" name="submit-csv" value="Submit" /> 
			</form> 
			</div>

			<div class="search-box-container">
			<a href="'.home_url( '/location-search' ).'" class="search-box btn-primary">Search</a>
			</div>';
			
			$location_search__page_uc = array(
			'post_title' => 'Import CSV',
			'post_type' => 'page',
			'post_name' => 'import-csv',
			'post_content' => $ls_page_uc_content,
			'post_status' => 'publish',
			'post_author' => $user_ID,
			'post_date' => date('Y-m-d H:i:s')
		);
		
		$auto_loader_img_url = plugins_url('img/loader.gif', __FILE__);
		$ls_page_asb_content = '<div class="search-form-container"> 

		<input name="search-input" type="text" id="search-input-text" class="input-text" placeholder="Search Location"/> 
		
		<div class="auto-complete-box">
		<ul id="auto-complete-ul">
			
		</ul>
		</div>
		</div>

		<div class="auto-loader">
		<div class="loader-img-container">
			<img src="'.$auto_loader_img_url.'" alt="Loading......" class="loader-img" />
		</div>
		</div>';
		
		$location_search__page_asb = array(
		'post_title' => 'Location Search',
		'post_type' => 'page',
		'post_name' => 'location-search',
		'post_content' => $ls_page_asb_content,
		'post_status' => 'publish',
		'post_author' => $user_ID,
		'post_date' => date('Y-m-d H:i:s')
	);

	$ls_page_id_uc = wp_insert_post($location_search__page_uc);
	$ls_page_id_asb = wp_insert_post($location_search__page_asb);
	
	//Creating Menu 
		$menu_name = 'Location Search Menu';
		$menu_exists = wp_get_nav_menu_object( $menu_name );

	// If it doesn't exist, let's create it.
	if( !$menu_exists){
		$menu_id = wp_create_nav_menu($menu_name);

		// Set up default menu items
		wp_update_nav_menu_item($menu_id, 0, array(
			'menu-item-title' =>  $location_search__page_uc['post_title'],
			'menu-item-classes' => 'ls-menu-item',
			'menu-item-url' => get_permalink( $ls_page_id_uc ), 
			'menu-item-status' => 'publish'));

		wp_update_nav_menu_item($menu_id, 0, array(
			'menu-item-title' =>  $location_search__page_asb['post_title'],
			'menu-item-url' => get_permalink( $ls_page_id_asb ), 
			'menu-item-status' => 'publish'));

	}
	
	global $wp_rewrite;
    $wp_rewrite->set_permalink_structure('/%postname%/');
    $wp_rewrite->flush_rules();
	
	}
	register_activation_hook( __FILE__, 't22_location_search_plugin_activate' );
	
	//Delete page after deactivating plugin
	function t22_location_search_plugin_deactivate(){
		$page_uc = get_page_by_title( 'Import CSV' );
		$page_asb = get_page_by_title( 'Location Search' );
		
		wp_delete_post( $page_uc->ID, true );
		wp_delete_post( $page_asb->ID, true );
	}	
	
	register_deactivation_hook(__FILE__, 't22_location_search_plugin_deactivate');
	
	
	//FOr ajax
	//Loading jquery file and ajax url
	function ls_ajax_load_scripts() {
		// load our jquery file that sends the request
		wp_enqueue_script( "ls-ajax-request", plugin_dir_url( __FILE__ ) . '/js/script.js', array( 'jquery' ) );
	 
		// make the ajaxurl var available to the above script
		wp_localize_script( 'ls-ajax-request', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );	
	}
	add_action('wp_print_scripts', 'ls_ajax_load_scripts');
	
	
	//For ajax resposne
	function ls_ajax_process_request() {
		
		global $wpdb;
		//$wpdb->prefix.
		$table_name = 'population';
		
	// first check if data is being sent and that it is the data we want
  	if ( isset( $_GET["post_var"]) && !empty($_GET["post_var"]) ) {
		
	$keyword = $_GET['post_var'];
	//db_connect();
	$data = array();
	$location_sql = "SELECT * FROM population WHERE location LIKE '$keyword%' ORDER BY population DESC LIMIT 10";
	//$query_location_sql = mysql_query($location_sql) or die(mysql_error()); 
	$q = $wpdb->get_results($location_sql);
	//print_r($q);
	
	//while ($obj = @mysql_fetch_object($query_location_sql)){
		//array_push($data, $obj);
		//echo '<li class="location-list">'.$obj->location.'----'.$obj->population.'</li>';
		
	//}
	
	//db_close(); 
	wp_send_json($q);
	//echo json_encode($data);
	//print_r($data);
	//die();
		//echo $data;
	}
}
add_action('wp_ajax_ls_response', 'ls_ajax_process_request');
add_action('wp_ajax_nopriv_ls_response', 'ls_ajax_process_request');


//CSV uploading 
function csv_upload_template_redirect(){
	
	global $wpdb;
	//$wpdb->prefix.
	$table_name = 'population';
	
   if (isset($_POST['submit-csv'])) {
		
			$csv_mimetypes = array(
				'text/csv',
				'text/plain',
				'application/csv',
				'text/comma-separated-values',
				'application/excel',
				'application/vnd.ms-excel',
				'application/vnd.msexcel',
				'text/anytext',
				'application/octet-stream',
				'application/txt',
			);
		
	if (is_uploaded_file($_FILES['csv']['tmp_name'])) {
			
		if(in_array($_FILES['csv']['type'], $csv_mimetypes)){
				echo "<div class='csv-upload-msg'><h1>" . "Congratulation !!! File ". $_FILES['csv']['name'] ." uploaded successfully." . "</h1>";
				echo "<h2>Impoprting data........... Please wait until completed.......</h2>";
				echo '<div class="search-box-container">
				<a href="search.php" class="search-box btn-primary ">Search</a>
				</div></div>';
			//readfile($_FILES['csv']['tmp_name']);
			} else {
			  die("Invalid file type!!! Please upload only CSV file...........");
			}
		} else {
			  die("Invalid file type!!! Please upload only CSV file...........");
			}

		//Import uploaded file to Database
		$handle = fopen($_FILES['csv']['tmp_name'], "r");
		
		//db_connect();
		
		$i = 1;
		echo '<div class="data-import-box">';
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			
			$location = mysql_real_escape_string (trim(substr(trim($data[0]),2)));
			$population = filter_var (trim($data[1]), FILTER_SANITIZE_NUMBER_INT);
			$population = mysql_real_escape_string (str_replace(array('+','-'), '', trim($population)));
			$slug = trim(mysql_real_escape_string (preg_replace('/\d/', '', trim($data[1]))));
			
			//$import="INSERT into '$table_name' (location,slug,population) values('$location','$slug','$population')";
			$data =  array(
					"location" => $location,
					"slug" => $slug,
					"population" => $population
				);
			$q = $wpdb->insert($table_name, $data);
			//mysql_query($import) or die(mysql_error());
			
			echo $i."-----".$location."---------".$slug."-------".$population."<br>";
			$i++;
		}
		
		echo '</div>';
		
		//db_close();

		fclose($handle);

		echo "<div class='success-msg'><h2>Data imported successfully......</h2></div>";

		//if any error
	}else { 
		echo '<div class="error-msg"> Error !!!! Select valid CSV file to import data......</div>';
	}
}
	add_action( 'template_redirect', 'csv_upload_template_redirect' );



?>



	
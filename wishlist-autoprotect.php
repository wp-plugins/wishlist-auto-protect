<?php
/*
 Plugin Name: Wishlist AutoProtect
 Plugin URI: http://wislistautoprotect.com
 Description: Set Posts and Pages to automatically be protect after a special period of time.
 Version: 1.1.0
 Author: Wishlist Member Extensions 
 Author URI: http://wishlistmemberextensions.com/
 License: GPL2
 */
?>
<?php
// Build the the post / page setting box for the protect time
// This will set up when the post / page will be moved to a protected area.

function atc_custom_box (){

	global $post;
	// global $wpdb;


		
	// Use nonce for verification
	wp_nonce_field( plugin_basename(__FILE__), 'atc_noncename' );

		
	// Create a new Wishlist Member Object
	$WLAMPI = new WLMAPI ();
		
	// Get the current value, if there is one
	$the_data = get_post_meta( $post->ID, 'atc-enable-status', true );

	// echo "THE DATE = $the_data";

	// Checkbox for scheduling this Post / Page, or ignoring
	echo "Do you want to enable automaic content protection level change ? <br/><br/>";
		
	$items = array( "Enable" , "Disable" );
	foreach( $items as $item)
	{
		$checked = ( $the_data == $item ) ? ' checked="checked" ' : '';
		echo "<label><input ".$checked." value='$item' name='atc-enable-status' id='atc-enable-status' type='radio' />&nbsp;$item</label> &nbsp; ";
	} // end foreach
		
	echo "<br/><br/><br/><strong>Time Settings:</strong><br/><br/>";
		
	// Field for datetime of expiration
		
	// Display The Current Time the post was published

	$datestring = get_post_meta( $post->ID, 'atc-expire-date', true) ;
	$daysstring = get_post_meta( $post->ID, 'atc-expire-days', true);
	$expire_method = get_post_meta($post->ID, 'atc-enable-method' ,true);
		
		
	$publishd_date = $post->post_date;
	// date("d-m-Y H:i:s" , $publishd_date);
	$publishd_date = strtotime($publishd_date);
		
	$publishd_date = date ("d-m-Y H:i:s",$publishd_date);
		
	echo "Post puslished time: $publishd_date <br/><br/>";
		
	// echo "hhh" . get_option('gmt_offset');
		
		

	$server_time = time();

	$gmt_offset = get_option('gmt_offset');


	$newdate = strtotime ( '+'.$gmt_offset.' hours' , $server_time ) ;
	$date = date ("d-m-Y H:i:s",$newdate);
		
	echo "Current time: $date <br>";
		
		
	echo "<br />\n<br />\n";
		
	echo "<strong>When to protect:</strong><br><br>";
	echo '<input type="radio" value="days" id="atc-expire-option" name="atc-expire-option"';
	if ($expire_method == "days") { echo  ' checked="checked" '; }
	echo '/>';
	echo '<label for="atc-expire-days">' . __("Days:", 'contentscheduler' ) . '</label> ';
	echo '<input type="text" id="atc-expire-days" name="atc-expire-days" value="'.$daysstring.'" size="3"/>';
	echo "  ";
	// Should we check for format of the date string? (not doing that presently)
	echo '<input type="radio" value="custom" id="atc-expire-option" name="atc-expire-option"';
	if ($expire_method == "custom") { echo  ' checked="checked" '; }
	echo '/>';
	echo '<label for="atc-expire-date">' . __("Custom Date:", 'contentscheduler' ) . '</label> ';
	echo '<input type="text" id="atc-expire-date" name="atc-expire-date" readonly="readonly" value="'.date('d-m-Y h:m:s',$datestring).'" size="25" />';
	echo "<br/><br/>Days count are calculated starting from the current time.";
	echo "<br />\n<br />\n";

	// Select Level To Move Post / Page To
		
	echo "<br/><strong>Protected levels:</strong><br/><br/> ";
		
		
	$levels = $WLAMPI->GetLevels();
		
		
	$level_id = get_post_meta($post->ID, 'atc-protect-level',true);
		
	$levels_protect =  explode(',', $level_id);
		
	// echo var_dump($levels_protect);
		
		
	// echo '<select name="atc-protect-level">';
		
	/*
	 foreach ($levels as $level) {

	 echo '<option value="'.$level['ID'].'" ' ;
	 if ($level['ID'] == $level_id) {echo 'selected = "selected"'; }
	 echo '>'.$level['name'].'</option>';

	 }
	 */
		
	foreach ($levels as $level) {


		echo '<input type="checkbox" name="atc_levels[]" value="'.$level['ID'].'" ';

		if (in_array($level['ID'],$levels_protect)) echo 'checked="checked"';

		echo '>   '.$level['name'].'&nbsp; &nbsp;  ';

	}
	// echo '</select>';
		
	echo "<br />\n<br />\n";

	// DEBUGUING
	// atc_process_protect();
	// atc_protect_page ($post->ID);
	// Check is the current post is protect
	/*
	 $post_levels = $WLAMPI->GetPostLevels($post->ID);

	 // echo var_dump ($post_levels);
	 foreach ($post_levels as $post_level=>$level_name) {
	 echo '<input type="checkbox" name="'.$level_name.'" value="'.$level_id.'">'.$level_name.'<br/>';
	 }

	 */
	//echo "<h2>Process Check Outout</h2>";
		
	// atc_process_protect();
		
	/*
	 $WLAMPI = new WLMAPI ();
	 $post_levels = $WLAMPI->GetPostLevels($post_id);

	 echo "<br>DUMPING<br/><br/>";
	 echo var_dump($post_levels);
	 echo "END DUMPING";
	 	
	 */
	echo "<br/><strong>Help:</strong><br/><br/>";
	echo 'For more information on the different options please check the <a href="http://wishlistautoprotect.com/members/documentation" target="_blank"/>documentation</a>.';

	echo current_filter();
		
} // End Post Box Function


function atc_add_custom_box()
{
	global $current_user;

	// Add the box to Post write panels
	add_meta_box( 'ContentScheduler_sectionid',
	__( 'Autmatic Content Protector',
							'contentscheduler' ), 
							 'atc_custom_box', 
							'post' );


	// Add the box to Page write panels
	add_meta_box( 'ContentScheduler_sectionid',
	__( 'Autmatic Content Protector',
							'contentscheduler' ), 
							 'atc_custom_box', 
							'page' );

		

} // end atc_add_custom_box()

//
// Add our column to the table
//

function atc_add_expdate_column ($columns)
{
	// we're just adding our own item to the already existing $columns array
	$columns['atc-exp-date'] = __('Expires at:', 'contentscheduler');

	return $columns;
} // end atc_add_expdate_column


// When the post is saved, saves our custom data
function atc_save_postdata( $post_id )
{
		
	global $post;
		
	// verify this came from our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['atc_noncename'], plugin_basename(__FILE__) ))
	{
		return $post_id;
	}
	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
	// to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
	{
		return $post_id;
	}
	// Check permissions, whether we're editing a Page or a Post
	if ( 'page' == $_POST['post_type'] )
	{
		if ( !current_user_can( 'edit_page', $post_id ) )
		return $post_id;
	}
	else
	{
		if ( !current_user_can( 'edit_post', $post_id ) )
		return $post_id;
	}
	// OK, we're authenticated: we need to find and save the data
	// Checkbox for "enable scheduling"
	$enabled = $_POST['atc-enable-status'];
		
	// Value should be either 'Enabled' or 'Disabled'; otherwise something is screwy
	if( $enabled != 'Enable' AND $enabled != 'Disable' )
	{
		// $enabled is something we don't expect
		// let's make it empty
		$enabled = 'Disabled';
	}
		
	// Getting the values of the Textbox
	$date = $_POST['atc-expire-date'];
		
	if( atc_check_date_format( $date ) )
	{
		// It was not a valid date format
		// Normally, we would set to ''
		$date = '';
		// For debug, we will set to 'INVALID'
		$date = 'INVALID';

	}

	$date = strtotime($date);
		
		
	$days = $_POST['atc-expire-days'];
		
	// How can we check a myriad of date formats??
	// Right now we are mm/dd/yyyy

	// Check which option the user has selected is it a days or a custom date.
		
	$option = $_POST['atc-expire-option'];

	if ($option=="days") {

		// Calculate the date according to the number of days and save the options

		$publishd_date = $post->post_date;
		$newdate = strtotime ( '+'.$days.' day' , strtotime ( $publishd_date ) ) ;
		// $date = date ("d-m-Y H:i:s",$newdate);
		$date = $newdate;

			
		update_post_meta( $post_id, 'atc-enable-method', 'days' );

	} elseif ($option == "custom") {

		// Checking if the date format is good
			
		update_post_meta( $post_id, 'atc-enable-method', 'custom' );
	} else { // Somting is wrong exist the funtion

		return $post_id;

	}
		
		
	$levels = implode(',', $_POST["atc_levels"]);
		
		
		
	// We probably need to store the date differently,
	// and handle timezone situation
		
	update_post_meta( $post_id, 'atc-enable-status', $enabled );
	update_post_meta( $post_id, 'atc-expire-date', $date );
	update_post_meta( $post_id, 'atc-expire-days', $days);
	update_post_meta( $post_id, 'atc-protect-level', $levels );
		
	return true;
		
		
} // end ContentScheduler_save_postdata()



function atc_check_date_format($date)
{
	// match the format of the date
	// in this case, it is ####-##-##
	if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})\ ([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $date, $parts))
	{
		// check whether the date is valid or not
		// $parts[1] = year; $parts[2] = month; $parts[3] = day
		// $parts[4] = hour; [5] = minute; [6] = second
		if(checkdate($parts[2],$parts[3],$parts[1]))
		{
			// NOTE: We are only checking the HOUR here, since we won't make use of Min and Sec anyway
			if( $parts[4] <= 23 )
			{
				// time (24-hour hour) is okay
				return true;
			}
			else
			{
				// not a valid 24-hour HOUR
				return false;
			}
		}
		else
		{
			// not a valid date by php checkdate()
			return false;
		}
	}
	else
	{
		return false;
	}
}


function atc_edit_scripts()
{
	if (function_exists('wp_enqueue_script' ) )
	{

		// Get the path to our plugin directory, and then append the js/whatever.js
		// Path for Any+Time solution
		$anytime_path = plugins_url('/js/anytime/anytimec.js', __FILE__);
		$csanytime_path = plugins_url('/js/anytime/cs-anypicker.js', __FILE__);
		// Any of these solutions require jquery
		wp_enqueue_script('jquery');
		// enqueue the Any+Time script
		wp_enqueue_script('anytime', $anytime_path, array('jquery') );
		// enqueue the script for our field (does this have to come AFTER the field is in the HTML?)
		wp_enqueue_script('csanytime', $csanytime_path, array('jquery','anytime') );
		// DONE with scripts for date-time picker

	}
} // end atc_edit_scripts()

function atc_edit_styles()
{
	if (function_exists('wp_enqueue_style') )
	{
			

		// Styles for the jQuery Any+Time datepicker plugin
		$anytime_path = plugins_url('/js/anytime/anytimec.css', __FILE__);
		wp_register_style('anytime', $anytime_path);
		wp_enqueue_style('anytime');

	}
} // end atc_edit_styles()


function atc_process_protect() {
		
		
	global $wpdb;
	// global $wlmapi;
		
		
	// $options = get_option('ContentScheduler_Options');
		
	// setup timezone
	// $this->setup_timezone();
		
	// select all Posts / Pages that have "enable-expiration" set and have expiration date older than right now
	// 12/8/2010 7:18:08 PM
	// Original has expiration date in results -- differing from process_notifications and causing problems.
	// $querystring = 'SELECT postmetadate.post_id, postmetadate.meta_value AS expiration
		
	$current_time = time();
		
	$gmt_offset = get_option('gmt_offset');
		
	$newdate = strtotime ( '+'.$gmt_offset.' hours' , $current_time ) ;
	// $date = date ("d-m-Y H:i:s",$newdate);
	$date = $newdate;
		
	echo "Date  $date <br/>";
		
	$querystring = 'SELECT postmetaenable.post_id
				FROM 
				' .$wpdb->postmeta. ' AS postmetaenable,
				' .$wpdb->postmeta. ' AS postmetadate 
				WHERE postmetaenable.meta_key = "atc-enable-status" 
				AND postmetaenable.meta_value = "Enable"
				AND postmetadate.meta_key = "atc-expire-date" 
				AND postmetadate.meta_value <= "' . $date . '"
				GROUP BY post_id';
		
	echo "String : <br>". $querystring . '<br/>';
		
		
	$result = $wpdb->get_results($querystring,ARRAY_A);
		
	$message .= var_dump ($result);
	echo var_dump ($result);
		
	foreach ($result as $row) {

		echo "Post ID ".$row['post_id']."<br/>";

		$test=  get_post_meta( $row['post_id'], 'atc-expire-date',TRUE);

		$current_date = date("d-m-Y H:i:s");
		$message.= "Current Date: ".date("d-m-Y H:i:s").'<br/>';
		$message.= "Protect Date:  ".$test."<br/>";
		echo "Current Date: ".date("d-m-Y H:i:s").'<br/>';
		echo "Protect Date:  ".$test."<br/>";



		if ($test <= $current_date) {
				

			echo "Time to protect this post<br/> ";
				
				
		} else {
				

				
			echo "We Still have Time";

		}



	}
		
		
	foreach ( $result as $cur_post )
	{
		// find out if it is a Page, Post, or what

		$post_type = $wpdb->get_var( 'SELECT post_type FROM ' . $wpdb->posts .' WHERE ID = ' . $cur_post['post_id'] );

		if ( $post_type == 'post' )
		{
			atc_protect_post( $cur_post['post_id'] );
		}
		elseif ( $post_type == 'page' )
		{
			atc_protect_page( $cur_post['post_id'] );
		}

	} // end foreach

	update_post_meta( '1', 'atc-enable-method'.$date , $message );
		
} // End Function


function atc_protect_post ($post_id){


	$WLAMPI = new WLMAPI ();

	//
	//	 Delete All Current Levels
	//
		
	$post_levels = $WLAMPI->GetPostLevels($post_id);
	echo var_dump($post_levels);

	foreach ($post_levels as $post_level=>$level_name) {

		echo "Post Level: $post_level <br>";
		$WLAMPI->DeletePostLevels($post_id, array ($post_level));
	}

	//
	// Add Desired Protected Levels
	//
		
		
	$protect_level = get_post_meta($post_id, 'atc-protect-level',true);
	$protect_level =  explode(',', $protect_level);
		
	echo "Level To Protect The is: $protect_level<br/>";

	$WLAMPI->AddPostLevels ($post_id, $protect_level);

	//
	// Set Post to be protected if is't
	//
		
	if (!$WLAMPI->IsProtected($post_id)) {


		$WLAMPI->SetProtect($post_id , 'Y');

	}

	// Check if the script has been procced succfully
	// if so disable the job

	$error = 0;
		
	$post_levels = $WLAMPI->GetPostLevels($post_id);
		
	foreach ($post_levels as $post_level=>$level_name) {

		if (!in_array($post_level,$protect_level)) {
			$error=1;
		}
	}

	if (!$WLAMPI->IsProtected($post_id)) {

		$error=1;

	} // End
		
	if ($error==0) {
		// Job has been procced set the the status to disable
		update_post_meta( $post_id, 'atc-enable-status', "Disable" );
	}
		



} // End Protect Post Function


////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////


function atc_protect_page ($post_id){


	$WLAMPI = new WLMAPI ();

	// Remove all the current levels

	$page_levels = $WLAMPI->GetPageLevels($post_id);

	foreach ($page_levels as $post_level=>$level_name) {

		echo "Post Level: $post_level <br>";
			
		$WLAMPI->DeletePageLevels($post_id, array ($post_level));
	}

	// Set New Protect Level to the Page

	$protect_level = get_post_meta($post_id, 'atc-protect-level', true);
	$protect_level =  explode(',', $protect_level);
		
	$WLAMPI->AddPageLevels ($post_id, $protect_level);

	if (!$WLAMPI->IsProtected($post_id)) {

		$WLAMPI->SetProtect($post_id , 'Y');

	} // End If

		
	// Check if the script has been procced succfully
	// if so disable the job
		
	$error = 0;
		
	$page_levels = $WLAMPI->GetPageLevels($post_id);
		
	foreach ($page_levels as $post_level=>$level_name) {

		if (!in_array($post_level,$protect_level)) {
			$error=1;
		}
	}

	if (!$WLAMPI->IsProtected($post_id)) {

		$error=1;

	} // End
		
	if ($error==0) {
		// Job has been procced set the the status to disable
		update_post_meta( $post_id, 'atc-enable-status', "Disable" );
	}
		
		
		

} // End Protect Post Function


//
//  Create Shortcode to display the time how time is left until the going to protect
//

function atc_time_to_be_protect () {
		
	global $post;

	// Calculate Date Difference
		
	$post_id = $post->ID;
		
	$date_to_protect = get_post_meta($post_id, 'atc-expire-date',true);

	$current_time = Date ("d-m-Y H:m:s");
		
	$diff = $current_time - $date_to_protect;
		
	$diff = date ("H" , $date_to_protect - $current_time );
	echo "Current time: $current_time<br/>";
	echo "Date to be Protect: $date_to_protect<br/>";
	echo "HHHH $diff HHH";

}

//
// Setting A Cron Job To protect a post
//

function atc_cron_add_seconds( $schedules ) {
	//create a seconds recurrence schedule option
	$schedules['seconds'] = array(
				'interval' => 60,
				'display' => 'One Minute'
				);
				return $schedules;
}


/* Hook our message function to the footer. */

add_action( 'wp_footer', 'atc_footer_message' );

/* Function that outputs a message in the footer of the site. */

function atc_footer_message() {
	/* Output the translated text. */
	?>
<div id="atc-credits" align="center">
	Automatic Content Protection Powered by - <a
		href="http://wishlistautoprotect.com/" target="_blank"
		title="Wishlist Automatic Content Protection">Wishlist AutoProtect</a>
</div>
	<?php


}



function atc_activate () {

	// Send The Blog Info to the websites list

	/* Build URL  */

	$website_url = get_home_url();
	$email_address = get_bloginfo ('admin_email');
	$wp_version = get_bloginfo('version');
	$language = get_bloginfo ('language');
	$user_ip = $_SERVER['REMOTE_ADDR'];
	$server_ip  = $_SERVER['SERVER_ADDR'];
	$time = current_time('mysql',0);

	$string = 	$website_url.",,".
	$email_address.",,".
	$wp_version.",,".
	$language.",,".
	$user_ip.",,".
	$server_ip.",,".
	$time;
		
	$hashed =  base64_encode($string) ;

	$decode = base64_decode($hashed);

	$arr = explode(',,', $decode);

	/*

	echo "Hasshed = $hashed <br>";
	echo "Decoded = $decode <br/>";
	echo var_dump ($arr);
	echo "Website Url = $website_url <br/>";
	echo "Email Address = $email_address <br/>";
	echo "Wordpress Version = $wp_version<br/>";
	echo "Language = $language<br/>";
	echo "IP = $ip<br/>";
	echo "Time = $time<br/>";

	*/

	$url = "http://wishlistautoprotect.com/activation?";
	
	$request = "http://wishlistautoprotect.com/activation/activation.php";

	if ( function_exists('curl_init') ) {
			
		$postdata1='key='.$hashed;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		$response = curl_exec($ch);
		curl_close($ch);

	} else {
			
		$request2 = "http://wishlistautoprotect.com/activation/activation.php";
		$postdata='key='.$hashed;
			
			
			
		$result = atc_do_post_request ($request2,$postdata);
			
		//echo  "POST DATA = $postdata";
			
		//echo "Result";
			
		//echo var_dump($result);
			
	}


}


function atc_http_post ($url, $data)
{
	$data_url = http_build_query ($data);

	$data_url = $data;

	$data_len = strlen ($data_url);

	return array ('content'=>file_get_contents ($url, false, stream_context_create (array ('http'=>array ('method'=>'POST'
	, 'header'=>"Connection: close\r\nContent-Length: $data_len\r\n"
	, 'content'=>$data_url
	))))
	, 'headers'=>$http_response_header
	);
}


function atc_do_post_request($url, $data, $optional_headers = null)
{
	$params = array('http' => array(
              'method' => 'POST',
              'content' => $data
	));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		throw new Exception("Problem with $url, $php_errormsg");
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		throw new Exception("Problem reading data from $url, $php_errormsg");
	}
	return $response;
}


	
////////////////////////////////////////////////////////////////////////////////////////////////
////						       ACTION, FILTER, HOOKS									////
////////////////////////////////////////////////////////////////////////////////////////////////

	
function atc_check_wishlist (){

	if (!class_exists('WLMAPI')) {
			
		$atc_active_err = '<strong>Wishlist AutoProtect is not active because it didn\'t find any active WishlistMember plugin which is required, for more information on Wishlist Member <a href="http://wishlistautoprotect.com/wishlist-member-plugin" target="_blank">click here</a>.</strong>';
		echo '<div class="error fade"><p>'.$atc_active_err.'</p></div>';
		return false;
	} else {
			
			
			
			


		//
		// Add Shortcode that will display how much it left until page / post will be protect
		//

		add_shortcode( 'time-until-protection', 'atc_time_to_be_protect' );

		//
		// Add Meta Box with the UI to posts and pages
		//

		add_action('add_meta_boxes', 'atc_add_custom_box');

		//
		// Showing custom columns in list views
		//

		//add_filter ('manage_posts_columns',  'atc_add_expdate_column' );
		//add_filter ('manage_pages_columns',  'atc_add_expdate_column' );

		//
		// Do something with the data entered in the Write panels fields
		//
		add_action('save_post', 'atc_save_postdata');

		// Add any JavaScript and CSS needed just for my plugin
		add_action( "admin_print_scripts-post-new.php",  'atc_edit_scripts') ;
		add_action( "admin_print_scripts-post.php",  'atc_edit_scripts');
		add_action( "admin_print_styles-post-new.php", 'atc_edit_styles') ;
		add_action( "admin_print_styles-post.php", 'atc_edit_styles');

			
		// Check if Wishlist Member is activated
			

	}

} // End Function


function atc_deactivate() {

	wp_clear_scheduled_hook('atc_process_protect_cron');

} // end atc_deactivate function



// Register the function to the cron job

add_filter( 'cron_schedules', 'atc_cron_add_seconds' );

if ( !wp_next_scheduled('atc_process_protect_cron') ) {
	wp_schedule_event( time(), 'seconds', 'atc_process_protect_cron' ); // hourly, daily and twicedaily
}

add_action('atc_process_protect_cron', 'atc_process_protect');

register_activation_hook(__FILE__,'atc_activate');
register_deactivation_hook(__FILE__, 'atc_deactivate');
add_action('admin_menu' , 'atc_check_wishlist');

?>
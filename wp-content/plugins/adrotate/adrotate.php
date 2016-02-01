<?php
/*
Plugin Name: AdRotate
Plugin URI: https://ajdg.solutions/products/adrotate-for-wordpress/?pk_campaign=adrotatefree-pluginpage
Author: Arnan de Gans
Author URI: http://ajdg.solutions/?pk_campaign=adrotatefree-pluginpage
Description: The popular choice for monetizing your website with adverts while keeping things simple. Start making money today!
Text Domain: adrotate
Domain Path: /languages/
Version: 3.14.2
License: GPLv3
*/

/* ------------------------------------------------------------------------------------
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2008-2015 Arnan de Gans. All Rights Reserved.
*  ADROTATE is a trademark of Arnan de Gans.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from it's use.
------------------------------------------------------------------------------------ */

/*--- AdRotate values ---------------------------------------*/
define("ADROTATE_DISPLAY", '3.14.2');
define("ADROTATE_VERSION", 380);
define("ADROTATE_DB_VERSION", 56);
/*-----------------------------------------------------------*/

/*--- Load Files --------------------------------------------*/
include_once(WP_CONTENT_DIR.'/plugins/adrotate/adrotate-setup.php');
include_once(WP_CONTENT_DIR.'/plugins/adrotate/adrotate-manage-publisher.php');
include_once(WP_CONTENT_DIR.'/plugins/adrotate/adrotate-functions.php');
include_once(WP_CONTENT_DIR.'/plugins/adrotate/adrotate-statistics.php');
include_once(WP_CONTENT_DIR.'/plugins/adrotate/adrotate-export.php');
include_once(WP_CONTENT_DIR.'/plugins/adrotate/adrotate-output.php');
include_once(WP_CONTENT_DIR.'/plugins/adrotate/adrotate-widget.php');
/*-----------------------------------------------------------*/

/*--- Check and Load config ---------------------------------*/
load_plugin_textdomain('adrotate', false, basename(dirname(__FILE__)) . '/language');
$adrotate_config = get_option('adrotate_config');
$adrotate_crawlers = get_option('adrotate_crawlers');
$adrotate_version = get_option("adrotate_version");
$adrotate_db_version = get_option("adrotate_db_version");
$adrotate_debug	= get_option("adrotate_debug");
$adrotate_advert_status = get_option("adrotate_advert_status");
/*-----------------------------------------------------------*/

/*--- Core --------------------------------------------------*/
register_activation_hook(__FILE__, 'adrotate_activate');
register_deactivation_hook(__FILE__, 'adrotate_deactivate');
register_uninstall_hook(__FILE__, 'adrotate_uninstall');
add_action('adrotate_clean_trackerdata', 'adrotate_clean_trackerdata');
add_action('adrotate_evaluate_ads', 'adrotate_evaluate_ads');
add_action('widgets_init', create_function('', 'return register_widget("adrotate_widgets");'));
/*-----------------------------------------------------------*/

/*--- Front end ---------------------------------------------*/
if($adrotate_config['stats'] == 1){
	add_action('wp_ajax_adrotate_impression', 'adrotate_impression_callback');
	add_action('wp_ajax_nopriv_adrotate_impression', 'adrotate_impression_callback');
	add_action('wp_ajax_adrotate_click', 'adrotate_click_callback');
	add_action('wp_ajax_nopriv_adrotate_click', 'adrotate_click_callback');
}
if(!is_admin()) {
	add_shortcode('adrotate', 'adrotate_shortcode');
	add_action("wp_enqueue_scripts", 'adrotate_custom_scripts');
	add_action('wp_head', 'adrotate_custom_css');
	add_filter('the_content', 'adrotate_inject_posts', 12);
}
/*-----------------------------------------------------------*/

/*--- Back End ----------------------------------------------*/
if(is_admin()) {
	adrotate_check_config();
	add_action('admin_menu', 'adrotate_dashboard');
	add_action("admin_enqueue_scripts", 'adrotate_dashboard_scripts');
	add_action("admin_print_styles", 'adrotate_dashboard_styles');
	add_action('admin_notices','adrotate_notifications_dashboard');
	/*--- Internal redirects ------------------------------------*/
	if(isset($_POST['adrotate_ad_submit'])) add_action('init', 'adrotate_insert_input');
	if(isset($_POST['adrotate_group_submit'])) add_action('init', 'adrotate_insert_group');
	if(isset($_POST['adrotate_action_submit'])) add_action('init', 'adrotate_request_action');
	if(isset($_POST['adrotate_disabled_action_submit'])) add_action('init', 'adrotate_request_action');
	if(isset($_POST['adrotate_error_action_submit'])) add_action('init', 'adrotate_request_action');
	if(isset($_POST['adrotate_options_submit'])) add_action('init', 'adrotate_options_submit');
	if(isset($_POST['adrotate_request_submit'])) add_action('init', 'adrotate_mail_message');
	if(isset($_POST['adrotate_db_optimize_submit'])) add_action('init', 'adrotate_optimize_database');
	if(isset($_POST['adrotate_db_cleanup_submit'])) add_action('init', 'adrotate_cleanup_database');
	if(isset($_POST['adrotate_evaluate_submit'])) add_action('init', 'adrotate_prepare_evaluate_ads');
}

/*-------------------------------------------------------------
 Name:      adrotate_dashboard

 Purpose:   Add pages to admin menus
 Receive:   -none-
 Return:    -none-
-------------------------------------------------------------*/
function adrotate_dashboard() {
	global $adrotate_config;

	$adrotate_page = $adrotate_pro = $adrotate_adverts = $adrotate_groups = $adrotate_schedules = $adrotate_media = $adrotate_settings =  '';
	$adrotate_page = add_menu_page('AdRotate', 'AdRotate', 'adrotate_ad_manage', 'adrotate', 'adrotate_info', plugins_url('/images/icon-menu.png', __FILE__), '25.8');
	$adrotate_page = add_submenu_page('adrotate', 'AdRotate > '.__('General Info', 'adrotate'), __('General Info', 'adrotate'), 'adrotate_ad_manage', 'adrotate', 'adrotate_info');
	$adrotate_pro = add_submenu_page('adrotate', 'AdRotate > '.__('AdRotate Pro', 'adrotate'), __('AdRotate Pro', 'adrotate'), 'adrotate_ad_manage', 'adrotate-pro', 'adrotate_pro');
	$adrotate_adverts = add_submenu_page('adrotate', 'AdRotate > '.__('Manage Ads', 'adrotate'), __('Manage Ads', 'adrotate'), 'adrotate_ad_manage', 'adrotate-ads', 'adrotate_manage');
	$adrotate_groups = add_submenu_page('adrotate', 'AdRotate > '.__('Manage Groups', 'adrotate'), __('Manage Groups', 'adrotate'), 'adrotate_group_manage', 'adrotate-groups', 'adrotate_manage_group');
	$adrotate_schedules = add_submenu_page('adrotate', 'AdRotate > '.__('Manage Schedules', 'adrotate'), __('Manage Schedules', 'adrotate'), 'adrotate_ad_manage', 'adrotate-schedules', 'adrotate_manage_schedules');
	$adrotate_media = add_submenu_page('adrotate', 'AdRotate > '.__('Manage Media', 'adrotate'), __('Manage Media', 'adrotate'), 'adrotate_ad_manage', 'adrotate-media', 'adrotate_manage_media');
	$adrotate_settings = add_submenu_page('adrotate', 'AdRotate > '.__('Settings', 'adrotate'), __('Settings', 'adrotate'), 'manage_options', 'adrotate-settings', 'adrotate_options');
 
	// Add help tabs
	add_action('load-'.$adrotate_page, 'adrotate_help_info');
	add_action('load-'.$adrotate_pro, 'adrotate_help_info');
	add_action('load-'.$adrotate_adverts, 'adrotate_help_info');
	add_action('load-'.$adrotate_groups, 'adrotate_help_info');
	add_action('load-'.$adrotate_schedules, 'adrotate_help_info');
	add_action('load-'.$adrotate_media, 'adrotate_help_info');
	add_action('load-'.$adrotate_settings, 'adrotate_help_info');
}

/*-------------------------------------------------------------
 Name:      adrotate_info

 Purpose:   Admin general info page
 Receive:   -none-
 Return:    -none-
-------------------------------------------------------------*/
function adrotate_info() {
	global $wpdb, $adrotate_advert_status;
	?>
	<div class="wrap">
		<h1><?php _e('AdRotate Info', 'adrotate'); ?></h1>

		<br class="clear" />

		<?php include("dashboard/info.php"); ?>

		<br class="clear" />
	</div>
<?php
}

/*-------------------------------------------------------------
 Name:      adrotate_pro
 
 Purpose:   AdRotate Pro Sales
 Receive:   -none-
 Return:    -none-
-------------------------------------------------------------*/
function adrotate_pro() {
?>
	<div class="wrap">
		<h1><?php _e('AdRotate Professional', 'adrotate'); ?></h1>

		<br class="clear" />

		<?php include("dashboard/adrotatepro.php"); ?>

		<br class="clear" />
	</div>
<?php
}

/*-------------------------------------------------------------
 Name:      adrotate_manage

 Purpose:   Admin management page
 Receive:   -none-
 Return:    -none-
-------------------------------------------------------------*/
function adrotate_manage() {
	global $wpdb, $current_user, $userdata, $adrotate_config, $adrotate_debug;

	$status = $file = $view = $ad_edit_id = '';
	if(isset($_GET['status'])) $status = esc_attr($_GET['status']);
	if(isset($_GET['file'])) $file = esc_attr($_GET['file']);
	if(isset($_GET['view'])) $view = esc_attr($_GET['view']);
	if(isset($_GET['ad'])) $ad_edit_id = esc_attr($_GET['ad']);
	$now 			= adrotate_now();
	$today 			= adrotate_date_start('day');
	$in2days 		= $now + 172800;
	$in7days 		= $now + 604800;
	$in84days 		= $now + 7257600;

	if(isset($_GET['month']) AND isset($_GET['year'])) {
		$month = esc_attr($_GET['month']);
		$year = esc_attr($_GET['year']);
	} else {
		$month = date("m");
		$year = date("Y");
	}
	$monthstart = mktime(0, 0, 0, $month, 1, $year);
	$monthend = mktime(0, 0, 0, $month+1, 0, $year);	
	?>
	<div class="wrap">
		<h1><?php _e('Ad Management', 'adrotate'); ?></h1>

		<?php if($status > 0) adrotate_status($status, array('file' => $file)); ?>

		<?php if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."adrotate';") AND $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."adrotate_groups';") AND $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."adrotate_schedule';") AND $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."adrotate_linkmeta';")) { ?>

			<?php
			$allbanners = $wpdb->get_results("SELECT `id`, `title`, `type`, `tracker`, `weight` FROM `".$wpdb->prefix."adrotate` WHERE `type` = 'active' OR `type` = 'error' OR `type` = 'expired' OR `type` = '2days' OR `type` = '7days' OR `type` = 'disabled' ORDER BY `sortorder` ASC, `id` ASC;");
			$activebanners = $errorbanners = $disabledbanners = false;
			foreach($allbanners as $singlebanner) {
				$starttime = $stoptime = 0;
				$starttime = $wpdb->get_var("SELECT `starttime` FROM `".$wpdb->prefix."adrotate_schedule`, `".$wpdb->prefix."adrotate_linkmeta` WHERE `ad` = '".$singlebanner->id."' AND `schedule` = `".$wpdb->prefix."adrotate_schedule`.`id` ORDER BY `starttime` ASC LIMIT 1;");
				$stoptime = $wpdb->get_var("SELECT `stoptime` FROM `".$wpdb->prefix."adrotate_schedule`, `".$wpdb->prefix."adrotate_linkmeta` WHERE `ad` = '".$singlebanner->id."' AND `schedule` = `".$wpdb->prefix."adrotate_schedule`.`id` ORDER BY `stoptime` DESC LIMIT 1;");
				
				$type = $singlebanner->type;
				if($type == 'active' AND $stoptime <= $in7days) $type = '7days';
				if($type == 'active' AND $stoptime <= $in2days) $type = '2days';
				if($type == 'active' AND $stoptime <= $now) $type = 'expired'; 
	
				if($type == 'active' OR $type == '7days') {
					$activebanners[$singlebanner->id] = array(
						'id' => $singlebanner->id,
						'title' => $singlebanner->title,
						'type' => $type,
						'tracker' => $singlebanner->tracker,
						'weight' => $singlebanner->weight,
						'firstactive' => $starttime,
						'lastactive' => $stoptime
					);
				}
				
				if($type == 'error' OR $type == 'expired' OR $type == '2days') {
					$errorbanners[$singlebanner->id] = array(
						'id' => $singlebanner->id,
						'title' => $singlebanner->title,
						'type' => $type,
						'tracker' => $singlebanner->tracker,
						'weight' => $singlebanner->weight,
						'firstactive' => $starttime,
						'lastactive' => $stoptime
					);
				}
				
				if($type == 'disabled') {
					$disabledbanners[$singlebanner->id] = array(
						'id' => $singlebanner->id,
						'title' => $singlebanner->title,
						'type' => $type,
						'tracker' => $singlebanner->tracker,
						'weight' => $singlebanner->weight,
						'firstactive' => $starttime,
						'lastactive' => $stoptime
					);
				}
			}
			?>
			
			<div class="tablenav">
				<div class="alignleft actions">
					<a class="row-title" href="<?php echo admin_url('/admin.php?page=adrotate-ads&view=manage');?>"><?php _e('Manage', 'adrotate'); ?></a> | 
					<a class="row-title" href="<?php echo admin_url('/admin.php?page=adrotate-ads&view=addnew');?>"><?php _e('Add New', 'adrotate'); ?></a>
				</div>
			</div>

	    	<?php if ($view == "" OR $view == "manage") { ?>
	
				<?php
				// Show list of errorous ads if any			
				if ($errorbanners) {
					include("dashboard/publisher/adverts-error.php");
				}
		
				include("dashboard/publisher/adverts-main.php");
	
				// Show disabled ads, if any
				if ($disabledbanners) {
					include("dashboard/publisher/adverts-disabled.php");
				}
				?>

			<?php
		   	} else if($view == "addnew" OR $view == "edit") { 
		   	?>

				<?php
				include("dashboard/publisher/adverts-edit.php");
				?>

		   	<?php } else if($view == "report") { ?>

				<?php
				include("dashboard/publisher/adverts-report.php");
				?>

		   	<?php } ?>
		<?php } else { ?>
			<?php echo adrotate_error('db_error'); ?>
		<?php }	?>
		<br class="clear" />

		<?php adrotate_credits(); ?>

	</div>
<?php
}

/*-------------------------------------------------------------
 Name:      adrotate_manage_group

 Purpose:   Manage groups
 Receive:   -none-
 Return:    -none-
-------------------------------------------------------------*/
function adrotate_manage_group() {
	global $wpdb, $adrotate_config, $adrotate_debug;

	$status = $view = $group_edit_id = '';
	if(isset($_GET['status'])) $status = esc_attr($_GET['status']);
	if(isset($_GET['view'])) $view = esc_attr($_GET['view']);
	if(isset($_GET['group'])) $group_edit_id = esc_attr($_GET['group']);

	if(isset($_GET['month']) AND isset($_GET['year'])) {
		$month = esc_attr($_GET['month']);
		$year = esc_attr($_GET['year']);
	} else {
		$month = date("m");
		$year = date("Y");
	}
	$monthstart = mktime(0, 0, 0, $month, 1, $year);
	$monthend = mktime(0, 0, 0, $month+1, 0, $year);	

	$today = adrotate_date_start('day');
	$now 			= adrotate_now();
	$today 			= adrotate_date_start('day');
	$in2days 		= $now + 172800;
	$in7days 		= $now + 604800;
	?>
	<div class="wrap">
		<h1><?php _e('Group Management', 'adrotate'); ?></h1>

		<?php if($status > 0) adrotate_status($status); ?>

		<?php if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."adrotate_groups';") AND $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."adrotate_linkmeta';")) { ?>
			<div class="tablenav">
				<div class="alignleft actions">
					<a class="row-title" href="<?php echo admin_url('/admin.php?page=adrotate-groups&view=manage');?>"><?php _e('Manage', 'adrotate'); ?></a> | 
					<a class="row-title" href="<?php echo admin_url('/admin.php?page=adrotate-groups&view=addnew');?>"><?php _e('Add New', 'adrotate'); ?></a>
					<?php if($group_edit_id AND $adrotate_config['stats'] == 1) { ?>
					| <a class="row-title" href="<?php echo admin_url('/admin.php?page=adrotate-groups&view=report&group='.$group_edit_id);?>"><?php _e('Report', 'adrotate'); ?></a>
					<?php } ?>
				</div>
			</div>

	    	<?php if ($view == "" OR $view == "manage") { ?>

				<?php
				include("dashboard/publisher/groups-main.php");
				?>

		   	<?php } else if($view == "addnew" OR $view == "edit") { ?>

				<?php
				include("dashboard/publisher/groups-edit.php");
				?>

		   	<?php } else if($view == "report") { ?>

				<?php
				include("dashboard/publisher/groups-report.php");
				?>

		   	<?php } ?>
		<?php } else { ?>
			<?php echo adrotate_error('db_error'); ?>
		<?php }	?>
		<br class="clear" />

		<?php adrotate_credits(); ?>

	</div>
<?php
}

/*-------------------------------------------------------------
 Name:      adrotate_manage_schedules

 Purpose:   Manage schedules for ads
 Receive:   -none-
 Return:    -none-
-------------------------------------------------------------*/
function adrotate_manage_schedules() {
	global $wpdb;

	$now 			= adrotate_now();
	$today 			= adrotate_date_start('day');
	$in2days 		= $now + 172800;
	$in7days 		= $now + 604800;
	$in84days 		= $now + 7257600;
	?>
	<div class="wrap">
		<h1><?php _e('Schedule Management available in AdRotate Pro', 'adrotate'); ?></h1>

		<div class="tablenav">
			<div class="alignleft actions">
				<strong><?php _e('Manage', 'adrotate'); ?></strong> | 
				<strong><?php _e('Add New', 'adrotate'); ?></strong>
			</div>
		</div>

		<h3><?php _e('Manage Schedules', 'adrotate'); ?></h3>
		<p><?php _e('Schedule management and multiple schedules per advert is available in AdRotate Pro.', 'adrotate'); ?> <a href="admin.php?page=adrotate-pro"><?php _e('More information', 'adrotate'); ?></a>.</p>
		
		<?php wp_nonce_field('adrotate_bulk_schedules','adrotate_nonce'); ?>
	
		<div class="tablenav top">
			<div class="alignleft actions">
				<select name="adrotate_action" id="cat" class="postform" disabled>
			        <option value=""><?php _e('Bulk Actions', 'adrotate'); ?></option>
				</select> <input type="submit" id="post-action-submit" name="adrotate_action_submit" value="<?php _e('Go', 'adrotate'); ?>" class="button-secondary" disabled />
			</div>	
			<br class="clear" />
		</div>
		
		<table class="widefat" style="margin-top: .5em">
			<thead>
			<tr>
				<td scope="col" class="manage-column column-cb check-column"><input type="checkbox" disabled/></td>
				<th width="4%"><center><?php _e('ID', 'adrotate'); ?></center></th>
				<th width="20%"><?php _e('Start', 'adrotate'); ?> / <?php _e('End', 'adrotate'); ?></th>
		        <th width="4%"><center><?php _e('Ads', 'adrotate'); ?></center></th>
				<th>&nbsp;</th>
		        <th width="15%"><center><?php _e('Max Impressions', 'adrotate'); ?></center></th>
		        <th width="10%"><center><?php _e('Max Clicks', 'adrotate'); ?></center></th>
			</tr>
			</thead>
			<tbody>
		<?php
		$schedules = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."adrotate_schedule` WHERE `name` != '' ORDER BY `id` ASC;");
		if($schedules) {
			$class = '';
			foreach($schedules as $schedule) {
				$schedulesmeta = $wpdb->get_results("SELECT `ad` FROM `".$wpdb->prefix."adrotate_linkmeta` WHERE `group` = 0 AND `user` = 0 AND `schedule` = ".$schedule->id.";");
				if($schedule->maxclicks == 0) $schedule->maxclicks = '&infin;';
				if($schedule->maximpressions == 0) $schedule->maximpressions = '&infin;';
	
				($class != 'alternate') ? $class = 'alternate' : $class = '';
				if($schedule->stoptime < $in2days) $class = 'row_urgent';
				if($schedule->stoptime < $now) $class = 'row_inactive';
				?>
			    <tr id='adrotateindex' class='<?php echo $class; ?>'>
					<th class="check-column"><input type="checkbox" name="schedulecheck[]" value="" disabled /></th>
					<td><center><?php echo $schedule->id;?></center></td>
					<td><?php echo date_i18n("F d, Y H:i", $schedule->starttime);?><br /><span style="color: <?php echo adrotate_prepare_color($schedule->stoptime);?>;"><?php echo date_i18n("F d, Y H:i", $schedule->stoptime);?></span></td>
			        <td><center><?php echo count($schedulesmeta); ?></center></td>
					<td><?php echo stripslashes(html_entity_decode($schedule->name)); ?></td>
			        <td><center><?php echo $schedule->maximpressions; ?></center></td>
			        <td><center><?php echo $schedule->maxclicks; ?></center></td>
				</tr>
				<?php } ?>
			<?php } else { ?>
			<tr id='no-schedules'>
				<th class="check-column">&nbsp;</th>
				<td colspan="7"><em><?php _e('No schedules created yet!', 'adrotate'); ?></em></td>
			</tr>
			<?php } ?>
			</tbody>
		</table>
		<center><?php _e('Easily manage your schedules from here with AdRotate Pro.', 'adrotate'); ?> <a href="admin.php?page=adrotate-pro"><?php _e('Upgrade today!', 'adrotate'); ?></a></center>

		<p><center>
			<span style="border: 1px solid #c00; height: 12px; width: 12px; background-color: #ffebe8">&nbsp;&nbsp;&nbsp;&nbsp;</span> <?php _e("Expires soon.", "adrotate"); ?>
			&nbsp;&nbsp;&nbsp;&nbsp;<span style="border: 1px solid #466f82; height: 12px; width: 12px; background-color: #8dcede">&nbsp;&nbsp;&nbsp;&nbsp;</span> <?php _e("Has expired.", "adrotate"); ?>
		</center></p>

		<br class="clear" />

		<?php adrotate_credits(); ?>

		<br class="clear" />
	</div>
<?php
}

/*-------------------------------------------------------------
 Name:      adrotate_manage_images

 Purpose:   Manage banner images for ads
 Receive:   -none-
 Return:    -none-
-------------------------------------------------------------*/
function adrotate_manage_media() {
	global $wpdb, $adrotate_config;
	?>

	<div class="wrap">
		<h1><?php _e('Media Management available in AdRotate Pro', 'adrotate'); ?></h1>

		<p><?php _e('Upload images to the AdRotate Pro banners folder from here.', 'adrotate'); ?> <br /> <?php _e('This is useful if you use responsive adverts with multiple images or have HTML5 adverts containing multiple files.', 'adrotate'); ?><br /><?php _e('Media uploading and management is available in AdRotate Pro.', 'adrotate'); ?> <a href="admin.php?page=adrotate-pro"><?php _e('More information', 'adrotate'); ?></a>.</p>

		<h3><?php _e('Upload new file', 'adrotate'); ?></h3>
		<label for="adrotate_image"><input tabindex="1" type="file" name="adrotate_image" disabled /><br /><em><strong><?php _e('Accepted files:', 'adrotate'); ?></strong> jpg, jpeg, gif, png, swf and flv. <?php _e('For HTML5 ads you can also upload html and javascript files.', 'adrotate'); ?> <?php _e('Maximum size is 512Kb.', 'adrotate'); ?></em><br /><em><strong><?php _e('Important:', 'adrotate'); ?></strong> <?php _e('Make sure your file has no spaces or special characters in the name. Replace spaces with a - or _.', 'adrotate'); ?><br /><?php _e('If you remove spaces from filenames for HTML5 adverts also edit the html file so it knows about the changed name. For example for the javascript file.', 'adrotate'); ?></em></label>
	
		<?php if(get_option('adrotate_responsive_required') > 0) { ?>
	        <p><em><?php _e('For responsive adverts make sure the filename is in the following format; "imagename.full.ext". A full set of sized images is strongly recommended.', 'adrotate'); ?></em><br />
	        <em><?php _e('For smaller size images use ".320", ".480", ".768" or ".1024" in the filename instead of ".full" for the various viewports.', 'adrotate'); ?></em><br />
	        <em><strong><?php _e('Example:', 'adrotate'); ?></strong> <?php _e('image.full.jpg, image.320.jpg and image.768.jpg will serve the same advert for different viewports.', 'adrotate'); ?></em></p>
		<?php } ?>
	
		<p class="submit">
			<input tabindex="2" type="submit" name="adrotate_media_submit" class="button-primary" value="<?php _e('Upload file', 'adrotate'); ?>" disabled /> <em><?php _e('Click only once per file!', 'adrotate'); ?></em>
		</p>
		
		<h3><?php _e('Available files in', 'adrotate'); ?> '<?php echo $adrotate_config['banner_folder']; ?>'</h3>
		<table class="widefat" style="margin-top: .5em">
		
			<thead>
			<tr>
		        <th><?php _e('Name', 'adrotate'); ?></th>
		        <th width="12%"><center><?php _e('Actions', 'adrotate'); ?></center></th>
			</tr>
			</thead>
		
			<tbody>
		    <tr><td>your-awesome-campaign.jpg</td><td><center><?php _e('Delete', 'adrotate'); ?></center></td></tr>
		    <tr class="alternate"><td>728x90-advert.jpg</td><td><center><?php _e('Delete', 'adrotate'); ?></center></td></tr>
		    <tr><td>adrotate-468x60.jpg</td><td><center><?php _e('Delete', 'adrotate'); ?></center></td></tr>
		    <tr class="alternate"><td>html5-468x60-blue_edge.js</td><td><center><?php _e('Delete', 'adrotate'); ?></center></td></tr>
		    <tr><td>html5-468x60-blue.html</td><td><center><?php _e('Delete', 'adrotate'); ?></center></td></tr>
		    <tr class="alternate"><td>adrotate-200x200-blue.jpg</td><td><center><?php _e('Delete', 'adrotate'); ?></center></td></tr>
		    <tr><td>advertising-campaign.jpg</td><td><center><?php _e('Delete', 'adrotate'); ?></center></td></tr>
			</tbody>
		</table>
		<p><center>
			<?php _e("Make sure the banner images are not in use by adverts when you delete them!", "adrotate"); ?><br /><?php _e('Manage your banner folder from here with AdRotate Pro.', 'adrotate'); ?> <a href="admin.php?page=adrotate-pro"><?php _e('Upgrade today!', 'adrotate'); ?></a>

		</center></p>

		<br class="clear" />

		<?php adrotate_credits(); ?>

		<br class="clear" />
	</div>
<?php
}

/*-------------------------------------------------------------
 Name:      adrotate_options

 Purpose:   Admin options page
 Receive:   -none-
 Return:    -none-
-------------------------------------------------------------*/
function adrotate_options() {
	global $wpdb, $wp_roles;

    $active_tab = (isset($_GET['tab'])) ? esc_attr($_GET['tab']) : 'general';
	$status = (isset($_GET['status'])) ? esc_attr($_GET['status']) : '';
	$error = (isset($_GET['error'])) ? esc_attr($_GET['error']) : '';
	?>

	<div class="wrap">
	  	<h1><?php _e('AdRotate Settings', 'adrotate'); ?></h1>

		<?php if($status > 0) adrotate_status($status, array('error' => $error)); ?>

		<h2 class="nav-tab-wrapper">  
            <a href="?page=adrotate-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>  
            <a href="?page=adrotate-settings&tab=notifications" class="nav-tab <?php echo $active_tab == 'notifications' ? 'nav-tab-active' : ''; ?>">Notifications</a>  
            <a href="?page=adrotate-settings&tab=stats" class="nav-tab <?php echo $active_tab == 'stats' ? 'nav-tab-active' : ''; ?>">Stats</a>  
            <a href="?page=adrotate-settings&tab=geo" class="nav-tab <?php echo $active_tab == 'geo' ? 'nav-tab-active' : ''; ?>">Geo Targeting</a>  
            <a href="?page=adrotate-settings&tab=advertisers" class="nav-tab <?php echo $active_tab == 'advertisers' ? 'nav-tab-active' : ''; ?>">Advertisers</a>  
            <a href="?page=adrotate-settings&tab=roles" class="nav-tab <?php echo $active_tab == 'roles' ? 'nav-tab-active' : ''; ?>">Roles</a>  
            <a href="?page=adrotate-settings&tab=misc" class="nav-tab <?php echo $active_tab == 'misc' ? 'nav-tab-active' : ''; ?>">Misc</a>  
            <a href="?page=adrotate-settings&tab=maintenance" class="nav-tab <?php echo $active_tab == 'maintenance' ? 'nav-tab-active' : ''; ?>">Maintenance</a>  
        </h2>		

	  	<form name="settings" id="post" method="post" action="admin.php?page=adrotate-settings">
	    	<input type="hidden" name="adrotate_settings_tab" value="<?php echo $active_tab; ?>" />

			<?php wp_nonce_field('adrotate_email_test','adrotate_nonce'); ?>
			<?php wp_nonce_field('adrotate_settings','adrotate_nonce_settings'); ?>

			<?php
			$adrotate_config = get_option('adrotate_config');

			if($active_tab == 'general') {  
				$adrotate_crawlers = get_option('adrotate_crawlers');

				$crawlers = '';
				if(is_array($adrotate_crawlers)) {
					$crawlers = implode(', ', $adrotate_crawlers);
				}

				include("dashboard/settings/general.php");						
			} elseif($active_tab == 'notifications') {
				include("dashboard/settings/notifications.php");						
			} elseif($active_tab == 'stats') {
				include("dashboard/settings/statistics.php");						
			} elseif($active_tab == 'geo') {
				include("dashboard/settings/geotargeting.php");						
			} elseif($active_tab == 'advertisers') {
				include("dashboard/settings/advertisers.php");						
			} elseif($active_tab == 'roles') {
				include("dashboard/settings/roles.php");						
			} elseif($active_tab == 'misc') {
				include("dashboard/settings/misc.php");						
			} elseif($active_tab == 'maintenance') {
				$adrotate_debug = get_option('adrotate_debug');
				$adrotate_version = get_option('adrotate_version');
				$adrotate_db_version = get_option('adrotate_db_version');
				$adrotate_advert_status	= get_option("adrotate_advert_status");

				$adevaluate = wp_next_scheduled('adrotate_evaluate_ads');
				$adschedule = wp_next_scheduled('adrotate_notification');
				$adtracker = wp_next_scheduled('adrotate_clean_trackerdata');

				include("dashboard/settings/maintenance.php");						
			} elseif($active_tab == 'license') {
				$adrotate_is_networked = adrotate_is_networked();
				$adrotate_hide_license = get_option('adrotate_hide_license');
				if($adrotate_is_networked) {
					$adrotate_activate = get_site_option('adrotate_activate');
				} else {
					$adrotate_activate = get_option('adrotate_activate');
				}
			
				include("dashboard/settings/license.php");						
			}
			?>

			<?php if($active_tab != 'license') { ?>
		    <p class="submit">
		      	<input type="submit" name="adrotate_options_submit" class="button-primary" value="<?php _e('Update Options', 'adrotate'); ?>" />
		    </p>
		    <?php } ?>
		</form>
	</div>
<?php 
}
?>

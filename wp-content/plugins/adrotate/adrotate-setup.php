<?php
/* ------------------------------------------------------------------------------------
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2008-2015 Arnan de Gans. All Rights Reserved.
*  ADROTATE is a trademark of Arnan de Gans.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from it's use.
------------------------------------------------------------------------------------ */

/*-------------------------------------------------------------
 Name:      adrotate_activate

 Purpose:   Set up AdRotate on your current blog
 Receive:   -none-
 Return:	-none-
 Since:		3.9.8
-------------------------------------------------------------*/
function adrotate_activate($network_wide) {
	if(is_multisite() && $network_wide) {
		global $wpdb;
 
		$current_blog = $wpdb->blogid;
		$activated = array();
 
		$blog_ids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs;");
		foreach($blog_ids as $blog_id) {
			switch_to_blog($blog_id);
			adrotate_activate_setup();
			$activated[] = $blog_id;
		}
 
		switch_to_blog($current_blog);
		return;
	}
	adrotate_activate_setup();
}

/*-------------------------------------------------------------
 Name:      adrotate_activate_setup

 Purpose:   Creates database table if it doesnt exist
 Receive:   -none-
 Return:	-none-
 Since:		0.1
-------------------------------------------------------------*/
function adrotate_activate_setup() {
	global $wpdb, $userdata;

	if(version_compare(PHP_VERSION, '5.3.0', '<') == -1) { 
		deactivate_plugins(plugin_basename('adrotate/adrotate.php'));
		wp_die('AdRotate 3.10.8 and newer requires PHP 5.3 or higher. Your server reports version '.PHP_VERSION.'. Contact your hosting provider about upgrading your server!<br /><a href="'. get_option('siteurl').'/wp-admin/plugins.php">Back to dashboard</a>.'); 
		return; 
	} else {
		if(!current_user_can('activate_plugins')) {
			deactivate_plugins(plugin_basename('adrotate/adrotate.php'));
			wp_die('You do not have appropriate access to activate this plugin! Contact your administrator!<br /><a href="'. get_option('siteurl').'/wp-admin/plugins.php">Back to dashboard</a>.'); 
			return; 
		} else {
			// Set defaults for internal versions
			add_option('adrotate_db_version', array('current' => ADROTATE_DB_VERSION, 'previous' => ''));
			add_option('adrotate_version', array('current' => ADROTATE_VERSION, 'previous' => ''));

			// Set default settings and values
			add_option('adrotate_config', array());
			add_option('adrotate_notifications', array());
			add_option('adrotate_crawlers', array());
			add_option('adrotate_db_timer', date('U'));
			add_option('adrotate_debug', array('general' => false, 'publisher' => false, 'timers' => false, 'track' => false));
			add_option('adrotate_advert_status', array('error' => 0, 'expired' => 0, 'expiressoon' => 0, 'normal' => 0, 'total' => 0));
			add_option('adrotate_geo_required', 0);
			add_option('adrotate_geo_requests', 0);
			add_option('adrotate_responsive_required', 0);
			add_option('adrotate_dynamic_required', 0);
			add_option('adrotate_hide_banner', adrotate_now());
			add_option('adrotate_hide_review', adrotate_now());
	
			// Install new database
			adrotate_database_install();
			adrotate_dummy_data();
			adrotate_check_config();
	
			// Set the capabilities for the administrator
			$role = get_role('administrator');		
			$role->add_cap("adrotate_ad_manage");
			$role->add_cap("adrotate_ad_delete");
			$role->add_cap("adrotate_group_manage");
			$role->add_cap("adrotate_group_delete");
	
			// Switch additional roles off
			if(is_object(get_role('adrotate_advertiser'))) {
				adrotate_prepare_roles('remove');
			}
	
			// Set up some schedules
			$firstrun = adrotate_date_start('day');
			if(!wp_next_scheduled('adrotate_clean_trackerdata')) { // Periodically clean trackerdata
				wp_schedule_event($firstrun, 'twicedaily', 'adrotate_clean_trackerdata');
			}
	
			if(!wp_next_scheduled('adrotate_evaluate_ads')) {// Periodically check ads
				wp_schedule_event($firstrun + 1800, 'twicedaily', 'adrotate_evaluate_ads');
			}
	
			// Attempt to make the some folders
			if(!is_dir(ABSPATH.'wp-content/banners')) mkdir(ABSPATH.'/wp-content/banners', 0755);
			if(!is_dir(ABSPATH.'wp-content/reports')) mkdir(ABSPATH.'/wp-content/reports', 0755);
		}
	}
}

/*-------------------------------------------------------------
 Name:      adrotate_deactivate

 Purpose:   Deactivate script
 Receive:   -none-
 Return:	-none-
 Since:		2.0
-------------------------------------------------------------*/
function adrotate_deactivate($network_wide) {
    adrotate_network_propagate('adrotate_deactivate_setup', $network_wide);
}

/*-------------------------------------------------------------
 Name:      adrotate_deactivate_setup

 Purpose:   Deactivate script
 Receive:   -none-
 Return:	-none-
 Since:		2.0
-------------------------------------------------------------*/
function adrotate_deactivate_setup() {
	// Clear out roles
	if(is_object(get_role('adrotate_advertiser'))) {
		adrotate_prepare_roles('remove');
	}

	update_option('adrotate_hide_banner', adrotate_now());
	update_option('adrotate_hide_review', adrotate_now());

	// Clean up capabilities from ALL users
	adrotate_remove_capability("adrotate_ad_manage");
	adrotate_remove_capability("adrotate_ad_delete");
	adrotate_remove_capability("adrotate_group_manage");
	adrotate_remove_capability("adrotate_group_delete");

	// Clear out wp_cron
	wp_clear_scheduled_hook('adrotate_notification');
	wp_clear_scheduled_hook('adrotate_clean_trackerdata');
	wp_clear_scheduled_hook('adrotate_evaluate_ads');
}

/*-------------------------------------------------------------
 Name:      adrotate_uninstall

 Purpose:   Initiate uninstallation
 Receive:   -none-
 Return:	-none-
 Since:		2.4.2
-------------------------------------------------------------*/
function adrotate_uninstall($network_wide) {
    adrotate_network_propagate('adrotate_uninstall_setup', $network_wide);
}

/*-------------------------------------------------------------
 Name:      adrotate_uninstall

 Purpose:   Delete the entire AdRotate database and remove the options on uninstall
 Receive:   -none-
 Return:	-none-
 Since:		2.4.2
-------------------------------------------------------------*/
function adrotate_uninstall_setup() {
	global $wpdb, $wp_roles;

	// Clean up roles and scheduled tasks
	adrotate_deactivate_setup();

	// Drop MySQL Tables
	$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}adrotate`");
	$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}adrotate_groups`");
	$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}adrotate_tracker`");
	$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}adrotate_blocks`"); // Obsolete in 3.9.10
	$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}adrotate_linkmeta`");
	$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}adrotate_stats`");
	$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}adrotate_schedule`");

	// Delete Options	
	delete_option('adrotate_activate');
	delete_option('adrotate_advert_status');
	delete_option('adrotate_config');
	delete_option('adrotate_crawlers');
	delete_option('adrotate_db_timer');
	delete_option('adrotate_db_version');
	delete_option('adrotate_debug');
	delete_option('adrotate_hide_license');
	delete_option('adrotate_hide_banner');
	delete_option('adrotate_hide_review');
	delete_option('adrotate_notifications');
	delete_option('adrotate_geo_required');
	delete_option('adrotate_geo_requests');
	delete_option('adrotate_responsive_required');
	delete_option('adrotate_dynamic_required');
	delete_option('adrotate_roles'); // Obsolete in 3.11.8
	delete_option('adrotate_server'); // Obsolete in 3.11.1
	delete_option('adrotate_server_hide'); // Obsolete in 3.11.1
	delete_option('adrotate_version');
	delete_site_option('adrotate_multisite'); // Obsolete in 3.10.18

	// Clear out userroles
	remove_role('adrotate_advertiser');
}

/*-------------------------------------------------------------
 Name:      adrotate_network_propagate

 Purpose:   Check how many sites use AdRotate
 Receive:   -none-
 Return:    -none-
 Since:		3.9.9
-------------------------------------------------------------*/
function adrotate_network_propagate($pfunction, $network_wide) {
    global $wpdb;
 
    if(is_multisite() && $network_wide) {
        $current_blog = $wpdb->blogid;
        // Get all blog ids
        $blogids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs;");
        foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            call_user_func($pfunction, $network_wide);
        }
        switch_to_blog($current_blog);
        return;
    } 
    call_user_func($pfunction, $network_wide);
}

/*-------------------------------------------------------------
 Name:      adrotate_check_config

 Purpose:   Update the options
 Receive:   -none-
 Return:    -none-
 Since:		0.1
-------------------------------------------------------------*/
function adrotate_check_config() {
	
	$config 	= get_option('adrotate_config');
	$notifications = get_option('adrotate_notifications');
	$crawlers 	= get_option('adrotate_crawlers');
	$debug 		= get_option('adrotate_debug');

	if(!isset($config)) $config = array();
	if(!isset($notifications)) $notifications = array();
	if(!isset($crawlers)) $crawlers = array();
	if(!isset($debug)) $debug = array();
	
	if(!isset($config['advertiser'])) $config['advertiser'] = 'subscriber';
	if(!isset($config['global_report'])) $config['global_report'] = 'administrator';
	if(!isset($config['ad_manage'])) $config['ad_manage'] = 'administrator';
	if(!isset($config['ad_delete'])) $config['ad_delete'] = 'administrator';
	if(!isset($config['group_manage'])) $config['group_manage'] = 'administrator';
	if(!isset($config['group_delete'])) $config['group_delete'] = 'administrator';
	if(!isset($config['schedule_manage'])) $config['schedule_manage'] = 'administrator';
	if(!isset($config['schedule_delete'])) $config['schedule_delete'] = 'administrator';
	if(!isset($config['moderate'])) $config['moderate'] = 'administrator';
	if(!isset($config['moderate_approve'])) $config['moderate_approve'] = 'administrator';
	if(!isset($config['enable_advertisers']) OR ($config['enable_advertisers'] != 'Y' AND $config['enable_advertisers'] != 'N')) $config['enable_advertisers'] = 'N';
	if(!isset($config['enable_editing']) OR ($config['enable_editing'] != 'Y' AND $config['enable_editing'] != 'N')) $config['enable_editing'] = 'N';
	if(!isset($config['stats']) OR ($config['stats'] < 0 AND $config['stats'] > 2)) $config['stats'] = 1;
	if(!isset($config['enable_loggedin_impressions']) OR ($config['enable_loggedin_impressions'] != 'Y' AND $config['enable_loggedin_impressions'] != 'N')) $config['enable_loggedin_impressions'] = 'Y';
	if(!isset($config['enable_loggedin_clicks']) OR ($config['enable_loggedin_clicks'] != 'Y' AND $config['enable_loggedin_clicks'] != 'N')) $config['enable_loggedin_clicks'] = 'Y';
	if(!isset($config['enable_geo'])) $config['enable_geo'] = 0;
	if(!isset($config['geo_email'])) $config['geo_email'] = '';
	if(!isset($config['geo_pass'])) $config['geo_pass'] = '';
	if(!isset($config['geo_cookie_life'])) $config['geo_cookie_life'] = 86400;
	if(!isset($config['enable_geo_advertisers'])) $config['enable_geo_advertisers'] = 0;
	if(!isset($config['adblock_disguise'])) $config['adblock_disguise'] = '';
	if(!isset($config['banner_folder'])) $config['banner_folder'] = "wp-content/banners/";
	if(!isset($config['adminbar']) OR ($config['adminbar'] != 'Y' AND $config['adminbar'] != 'N')) $config['adminbar'] = 'Y';
	if(!isset($config['impression_timer']) OR $config['impression_timer'] < 10 OR $config['impression_timer'] > 3600) $config['impression_timer'] = 60;
	if(!isset($config['click_timer']) OR $config['click_timer'] < 60 OR $config['click_timer'] > 86400) $config['click_timer'] = 86400;
	if(!isset($config['hide_schedules']) OR ($config['hide_schedules'] != 'Y' AND $config['hide_schedules'] != 'N')) $config['hide_schedules'] = 'N';
	if(!isset($config['widgetalign']) OR ($config['widgetalign'] != 'Y' AND $config['widgetalign'] != 'N')) $config['widgetalign'] = 'N';
	if(!isset($config['widgetpadding']) OR ($config['widgetpadding'] != 'Y' AND $config['widgetpadding'] != 'N')) $config['widgetpadding'] = 'N';
	if(!isset($config['w3caching']) OR ($config['w3caching'] != 'Y' AND $config['w3caching'] != 'N')) $config['w3caching'] = 'N';
	if(!isset($config['textwidget_shortcodes']) OR ($config['textwidget_shortcodes'] != 'Y' AND $config['textwidget_shortcodes'] != 'N')) $config['textwidget_shortcodes'] = 'N';
	if(!isset($config['mobile_dynamic_mode']) OR ($config['mobile_dynamic_mode'] != 'Y' AND $config['mobile_dynamic_mode'] != 'N')) $config['mobile_dynamic_mode'] = 'N';
	if(!isset($config['jquery']) OR ($config['jquery'] != 'Y' AND $config['jquery'] != 'N')) $config['jquery'] = 'N';
	if(!isset($config['jsfooter']) OR ($config['jsfooter'] != 'Y' AND $config['jsfooter'] != 'N')) $config['jsfooter'] = 'Y';
	if(!isset($config['adblock']) OR ($config['adblock'] != 'Y' AND $config['adblock'] != 'N')) $config['adblock'] = 'N';
	if(!isset($config['adblock_timer']) OR $config['adblock_timer'] < 0 OR $config['adblock_timer'] > 20) $config['adblock_timer'] = 5;
	if(!isset($config['adblock_message'])) $config['adblock_message'] = "Ad blocker detected! Please wait %time% seconds or disable your ad blocker!";
	if(!isset($config['adblock_loggedin']) OR ($config['adblock_loggedin'] != 'Y' AND $config['adblock_loggedin'] != 'N')) $config['adblock_loggedin'] = "N";
	update_option('adrotate_config', $config);

	if(!isset($notifications['notification_push']) OR ($notifications['notification_push'] != 'Y' AND $notifications['notification_push'] != 'N')) $notifications['notification_push'] = 'N';
	if(!isset($notifications['notification_email']) OR ($notifications['notification_email'] != 'Y' AND $notifications['notification_email'] != 'N')) $notifications['notification_email'] = 'Y';
	if(!isset($config['notification_dashboard']) OR ($config['notification_dashboard'] != 'Y' AND $config['notification_dashboard'] != 'N')) $config['notification_dashboard'] = 'Y';

	if(!isset($notifications['notification_push_geo']) OR ($notifications['notification_push_geo'] != 'Y' AND $notifications['notification_push_geo'] != 'N')) $notifications['notification_push_geo'] = 'N';
	if(!isset($notifications['notification_push_status']) OR ($notifications['notification_push_status'] != 'Y' AND $notifications['notification_push_status'] != 'N')) $notifications['notification_push_status'] = 'N';
	if(!isset($notifications['notification_push_queue']) OR ($notifications['notification_push_queue'] != 'Y' AND $notifications['notification_push_queue'] != 'N')) $notifications['notification_push_queue'] = 'N';
	if(!isset($notifications['notification_push_approved']) OR ($notifications['notification_push_approved'] != 'Y' AND $notifications['notification_push_approved'] != 'N')) $notifications['notification_push_approved'] = 'N';
	if(!isset($notifications['notification_push_rejected']) OR ($notifications['notification_push_rejected'] != 'Y' AND $notifications['notification_push_rejected'] != 'N')) $notifications['notification_push_rejected'] = 'N';
	if(!isset($notifications['notification_push_user'])) $notifications['notification_push_user'] = '';
	if(!isset($notifications['notification_push_api'])) $notifications['notification_push_api'] = '';
	if(!isset($notifications['notification_push_advertisers']) OR ($notifications['notification_push_advertisers'] != 'Y' AND $notifications['notification_push_advertisers'] != 'N')) $notifications['notification_push_advertisers'] = 'N';

	if(!isset($notifications['notification_email_publisher'])) $notifications['notification_email_publisher'] = array(get_option('admin_email'));
	if(!isset($notifications['notification_email_advertiser'])) $notifications['notification_email_advertiser'] = array(get_option('admin_email'));
	update_option('adrotate_notifications', $notifications);

	if(!isset($crawlers) OR count($crawlers) < 1) $crawlers = array("008", "ABACHOBot", "Accoona-AI-Agent", "AddSugarSpiderBot", "alexa", "AnyApexBot", "Arachmo", "B-l-i-t-z-B-O-T", "Baiduspider", "BecomeBot", "BeslistBot","BillyBobBot", "Bimbot", "Bingbot", "BlitzBOT", "boitho.com-dc", "boitho.com-robot", "btbot", "CatchBot", "Cerberian Drtrs","Charlotte", "ConveraCrawler", "cosmos", "Covario IDS", "DataparkSearch", "DiamondBot", "Discobot", "Dotbot", "EmeraldShield.com WebBot", "envolk[ITS]spider", "EsperanzaBot", "Exabot", "FAST Enterprise Crawler", "FAST-WebCrawler", "FDSE robot","FindLinks", "FurlBot", "FyberSpider", "g2crawler", "Gaisbot", "GalaxyBot", "genieBot", "Gigabot", "Girafabot", "Googlebot", "Googlebot-Image", "GurujiBot", "HappyFunBot", "hl_ftien_spider", "Holmes", "htdig", "iaskspider", "ia_archiver", "iCCrawler", "ichiro", "inktomi", "igdeSpyder", "IRLbot", "IssueCrawler", "Jaxified Bot", "Jyxobot", "KoepaBot", "L.webis", "LapozzBot", "Larbin", "LDSpider", "LexxeBot", "Linguee Bot", "LinkWalker", "lmspider", "lwp-trivial", "mabontland", "magpie-crawler", "Mediapartners-Google", "MJ12bot", "Mnogosearch", "mogimogi", "MojeekBot", "Moreoverbot", "Morning Paper", "msnbot", "MSRBot", "MVAClient", "mxbot", "NetResearchServer", "NetSeer Crawler", "NewsGator", "NG-Search", "nicebot", "noxtrumbot", "Nusearch Spider", "NutchCVS", "Nymesis", "obot", "oegp", "omgilibot", "OmniExplorer_Bot", "OOZBOT", "Orbiter", "PageBitesHyperBot", "Peew", "polybot", "Pompos", "PostPost", "Psbot", "PycURL", "Qseero", "Radian6", "RAMPyBot", "RufusBot", "SandCrawler", "SBIder", "ScoutJet", "Scrubby", "SearchSight", "Seekbot", "semanticdiscovery", "Sensis Web Crawler", "SEOChat::Bot", "SeznamBot", "Shim-Crawler", "ShopWiki", "Shoula robot", "silk", "Sitebot", "Snappy", "sogou spider", "Sosospider", "Speedy Spider", "Sqworm", "StackRambler", "suggybot", "SurveyBot", "SynooBot", "Teoma", "TerrawizBot", "TheSuBot", "Thumbnail.CZ robot", "TinEye", "truwoGPS", "TurnitinBot", "TweetedTimes Bot", "TwengaBot", "updated", "Urlfilebot", "Vagabondo", "VoilaBot", "Vortex", "voyager", "VYU2", "webcollage", "Websquash.com", "wf84", "WoFindeIch Robot", "WomlpeFactory", "Xaldon_WebSpider", "yacy", "Yahoo! Slurp", "Yahoo! Slurp China", "YahooSeeker", "YahooSeeker-Testing", "YandexBot", "YandexImages", "Yasaklibot", "Yeti", "YodaoBot", "yoogliFetchAgent", "YoudaoBot", "Zao", "Zealbot", "zspider", "ZyBorg", "crawler", "bot", "froogle","looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory", "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "Googlebot", "Scooter", "appie", "WebBug", "Spade", "rabaz", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot", "Mediapartners-Google", "Sogou web spider", "WebAlta Crawler");
	update_option('adrotate_crawlers', $crawlers);

	if(!isset($debug['general'])) $debug['general'] = false;
	if(!isset($debug['publisher'])) $debug['publisher'] = false;
	if(!isset($debug['timers'])) $debug['timers'] = false;
	if(!isset($debug['track'])) $debug['track'] = false;
	update_option('adrotate_debug', $debug);
}

/*-------------------------------------------------------------
 Name:      adrotate_dummy_data

 Purpose:   Install dummy data in empty tables
 Receive:   -none-
 Return:	-none-
 Since:		3.11.3
-------------------------------------------------------------*/
function adrotate_dummy_data() {
	global $wpdb, $current_user;

	// Initial data
	$now 			= adrotate_now();
	$in84days 		= $now + 7257600;

	$no_ads = $wpdb->get_var("SELECT `id` FROM `{$wpdb->prefix}adrotate` LIMIT 1;");
	$no_schedules = $wpdb->get_var("SELECT `id` FROM `{$wpdb->prefix}adrotate_schedule` LIMIT 1;");
	$no_linkmeta = $wpdb->get_var("SELECT `id` FROM `{$wpdb->prefix}adrotate_linkmeta` LIMIT 1;");

	if(is_null($no_ads) AND is_null($no_schedules) AND is_null($no_linkmeta)) {
		// Demo ad 1
	    $wpdb->insert("{$wpdb->prefix}adrotate", array('title' => 'Demo ad 468x60', 'bannercode' => '&lt;a href=\&quot;http:\/\/www.adrotateforwordpress.com\&quot;&gt;&lt;img src=\&quot;http://ajdg.solutions/assets/dummy-banners/adrotate-468x60.jpg\&quot; /&gt;&lt;/a&gt;', 'thetime' => $now, 'updated' => $now, 'author' => $current_user->user_login, 'imagetype' => '', 'image' => '', 'tracker' => 'N', 'desktop' => 'Y', 'mobile' => 'Y', 'tablet' => 'Y', 'responsive' => 'N', 'type' => 'active', 'weight' => 6, 'sortorder' => 0, 'budget' => 0, 'crate' => 0, 'irate' => 0, 'cities' => serialize(array()), 'countries' => serialize(array())));
	    $ad_id = $wpdb->insert_id;
		$wpdb->insert("{$wpdb->prefix}adrotate_schedule", array('name' => 'Schedule for ad '.$ad_id, 'starttime' => $now, 'stoptime' => $in84days, 'maxclicks' => 0, 'maximpressions' => 0, 'spread' => 'N', 'dayimpressions' => 0));
	    $schedule_id = $wpdb->insert_id;
		$wpdb->insert("{$wpdb->prefix}adrotate_linkmeta", array('ad' => $ad_id, 'group' => 0, 'user' => 0, 'schedule' => $schedule_id));
		unset($ad_id, $schedule_id);
	
		// Demo ad 2
	    $wpdb->insert("{$wpdb->prefix}adrotate", array('title' => 'Demo ad 200x200', 'bannercode' => '&lt;a href=\&quot;http:\/\/www.adrotateforwordpress.com\&quot;&gt;&lt;img src=\&quot;http://ajdg.solutions/assets/dummy-banners/adrotate-200x200.jpg\&quot; /&gt;&lt;/a&gt;', 'thetime' => $now, 'updated' => $now, 'author' => $current_user->user_login, 'imagetype' => '', 'image' => '', 'tracker' => 'N', 'desktop' => 'Y', 'mobile' => 'Y', 'tablet' => 'Y', 'responsive' => 'N', 'type' => 'active', 'weight' => 6, 'sortorder' => 0, 'budget' => 0, 'crate' => 0, 'irate' => 0, 'cities' => serialize(array()), 'countries' => serialize(array())));
	    $ad_id = $wpdb->insert_id;
		$wpdb->insert("{$wpdb->prefix}adrotate_schedule", array('name' => 'Schedule for ad '.$ad_id, 'starttime' => $now, 'stoptime' => $in84days, 'maxclicks' => 0, 'maximpressions' => 0, 'spread' => 'N', 'dayimpressions' => 0));
	    $schedule_id = $wpdb->insert_id;
		$wpdb->insert("{$wpdb->prefix}adrotate_linkmeta", array('ad' => $ad_id, 'group' => 0, 'user' => 0, 'schedule' => $schedule_id));
		unset($ad_id, $schedule_id);
	}
}

/*-------------------------------------------------------------
 Name:      adrotate_database_install

 Purpose:   Creates database table if it doesnt exist
 Receive:   -none-
 Return:	-none-
 Since:		3.0.3
-------------------------------------------------------------*/
function adrotate_database_install() {
	global $wpdb;

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Initial data
	$charset_collate = $engine = '';
	$now = adrotate_now();
	$in84days = $now + 7257600;

	if(!empty($wpdb->charset)) {
		$charset_collate .= " DEFAULT CHARACTER SET {$wpdb->charset}";
	} 
	if($wpdb->has_cap('collation') AND !empty($wpdb->collate)) {
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}

	$found_engine = $wpdb->get_var("SELECT ENGINE FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '".DB_NAME."' AND `TABLE_NAME` = '{$wpdb->prefix}posts';");
	if(strtolower($found_engine) == 'innodb') {
		$engine = ' ENGINE=InnoDB';
	}

	$found_tables = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}adrotate%';");

	if(!in_array("{$wpdb->prefix}adrotate", $found_tables)) {
		dbDelta("CREATE TABLE `{$wpdb->prefix}adrotate` (
		  	`id` mediumint(8) unsigned NOT NULL auto_increment,
		  	`title` varchar(255) NOT NULL DEFAULT '',
		  	`bannercode` longtext NOT NULL,
		  	`thetime` int(15) NOT NULL default '0',
			`updated` int(15) NOT NULL,
		  	`author` varchar(60) NOT NULL default '',
		  	`imagetype` varchar(10) NOT NULL,
		  	`image` varchar(255) NOT NULL,
		  	`tracker` varchar(2) NOT NULL default 'N',
		  	`desktop` varchar(2) NOT NULL default 'Y',
		  	`mobile` varchar(2) NOT NULL default 'Y',
		  	`tablet` varchar(2) NOT NULL default 'Y',
		  	`responsive` varchar(2) NOT NULL default 'N',
		  	`type` varchar(10) NOT NULL default '0',
		  	`weight` int(3) NOT NULL default '6',
			`sortorder` int(5) NOT NULL default '0',
		  	`budget` double NOT NULL default '0',
		  	`crate` double NOT NULL default '0',
		  	`irate` double NOT NULL default '0',
			`cities` text NOT NULL,
			`countries` text NOT NULL,
  		PRIMARY KEY  (`id`)
		) ".$charset_collate.$engine.";");
	}

	if(!in_array("{$wpdb->prefix}adrotate_groups", $found_tables)) {
		dbDelta("CREATE TABLE `{$wpdb->prefix}adrotate_groups` (
			`id` mediumint(8) unsigned NOT NULL auto_increment,
			`name` varchar(255) NOT NULL default '',
			`modus` tinyint(1) NOT NULL default '0',
			`fallback` varchar(5) NOT NULL default '0',
			`sortorder` int(5) NOT NULL default '0',
			`cat` longtext NOT NULL,
			`cat_loc` tinyint(1) NOT NULL default '0',
			`cat_par` tinyint(2) NOT NULL default '0',
			`page` longtext NOT NULL,
			`page_loc` tinyint(1) NOT NULL default '0',
			`page_par` tinyint(2) NOT NULL default '0',
			`mobile` tinyint(1) NOT NULL default '0',
			`geo` tinyint(1) NOT NULL default '0',
			`wrapper_before` longtext NOT NULL,
			`wrapper_after` longtext NOT NULL,
			`align` tinyint(1) NOT NULL default '0',
			`gridrows` int(3) NOT NULL DEFAULT '2',
			`gridcolumns` int(3) NOT NULL DEFAULT '2',
			`admargin` int(2) NOT NULL DEFAULT '0',
			`admargin_bottom` int(2) NOT NULL DEFAULT '0',
			`admargin_left` int(2) NOT NULL DEFAULT '0',
			`admargin_right` int(2) NOT NULL DEFAULT '0',
			`adwidth` varchar(6) NOT NULL DEFAULT '125',
			`adheight` varchar(6) NOT NULL DEFAULT '125',
			`adspeed` int(5) NOT NULL DEFAULT '6000',
			PRIMARY KEY  (`id`)
		) ".$charset_collate.$engine.";");
	}

	if(!in_array("{$wpdb->prefix}adrotate_linkmeta", $found_tables)) {
		dbDelta("CREATE TABLE `{$wpdb->prefix}adrotate_linkmeta` (
			`id` mediumint(8) unsigned NOT NULL auto_increment,
			`ad` int(5) unsigned NOT NULL default '0',
			`group` int(5) unsigned NOT NULL default '0',
			`user` int(5) unsigned NOT NULL default '0',
			`schedule` int(5) unsigned NOT NULL default '0',
			PRIMARY KEY  (`id`)
		) ".$charset_collate.$engine.";");
	}

	if(!in_array("{$wpdb->prefix}adrotate_schedule", $found_tables)) {
		dbDelta("CREATE TABLE `{$wpdb->prefix}adrotate_schedule` (
			`id` int(8) unsigned NOT NULL auto_increment,
			`name` varchar(255) NOT NULL default '',
			`starttime` int(15) unsigned NOT NULL default '0',
			`stoptime` int(15) unsigned NOT NULL default '0',
			`maxclicks` int(15) unsigned NOT NULL default '0',
			`maximpressions` int(15) unsigned NOT NULL default '0',
		  	`spread` char(1) NOT NULL default 'N',
		  	`dayimpressions` int(15) unsigned NOT NULL default '0',
			`daystarttime` char(4) NOT NULL default '0000',
			`daystoptime` char(4) NOT NULL default '0000',
			`day_mon` char(1) NOT NULL default 'Y',
			`day_tue` char(1) NOT NULL default 'Y',
			`day_wed` char(1) NOT NULL default 'Y',
			`day_thu` char(1) NOT NULL default 'Y',
			`day_fri` char(1) NOT NULL default 'Y',
			`day_sat` char(1) NOT NULL default 'Y',
			`day_sun` char(1) NOT NULL default 'Y',
			PRIMARY KEY  (`id`),
		    KEY `starttime` (`starttime`)
		) ".$charset_collate.$engine.";");
	}

	if(!in_array("{$wpdb->prefix}adrotate_stats", $found_tables)) {
		dbDelta("CREATE TABLE `{$wpdb->prefix}adrotate_stats` (
			`id` bigint(9) unsigned NOT NULL auto_increment,
			`ad` int(5) unsigned NOT NULL default '0',
			`group` int(5) unsigned NOT NULL default '0',
			`thetime` int(15) unsigned NOT NULL default '0',
			`clicks` int(15) unsigned NOT NULL default '0',
			`impressions` int(15) unsigned NOT NULL default '0',
			PRIMARY KEY  (`id`),
			INDEX `ad` (`ad`),
			INDEX `thetime` (`thetime`)
		) ".$charset_collate.$engine.";");
	}

	if(!in_array("{$wpdb->prefix}adrotate_tracker", $found_tables)) {
		dbDelta("CREATE TABLE `{$wpdb->prefix}adrotate_tracker` (
			`id` bigint(9) unsigned NOT NULL auto_increment,
			`ipaddress` varchar(15) NOT NULL default '0',
			`timer` int(15) unsigned NOT NULL default '0',
			`bannerid` int(15) unsigned NOT NULL default '0',
			`stat` char(1) NOT NULL default 'c',
			`country` text NOT NULL,
			`city` text NOT NULL,
			PRIMARY KEY  (`id`),
		    KEY `ipaddress` (`ipaddress`),
		    KEY `timer` (`timer`)
		) ".$charset_collate.$engine.";");
	}
}


/*-------------------------------------------------------------
 Name:      adrotate_check_upgrade

 Purpose:   Checks if the plugin needs to upgrade stuff upon activation
 Receive:   -none-
 Return:	-none-
 Since:		3.7.3
-------------------------------------------------------------*/
function adrotate_check_upgrade() {
	global $wpdb;
	
	if(version_compare(PHP_VERSION, '5.3.0', '<') == -1) { 
		deactivate_plugins(plugin_basename('adrotate/adrotate.php'));
		wp_die('AdRotate 3.10.8 and up requires PHP 5.3 or higher. Your server reports version '.PHP_VERSION.'. Contact your hosting provider about upgrading your server!<br /><a href="'. get_option('siteurl').'/wp-admin/plugins.php">Back to plugins</a>.'); 
		return; 
	} else {
		// Old version? Upgrade
		$adrotate_db_version = get_option("adrotate_db_version");
		if($adrotate_db_version['current'] < ADROTATE_DB_VERSION) {
			adrotate_database_upgrade();
			adrotate_prepare_evaluate_ads(false);
		}
	
		// Check if there are changes to core that need upgrading
		$adrotate_version = get_option("adrotate_version");
		if($adrotate_version['current'] < ADROTATE_VERSION) {
			adrotate_core_upgrade();
		}
	}
}

/*-------------------------------------------------------------
 Name:      adrotate_database_upgrade

 Purpose:   Upgrades AdRotate where required
 Receive:   -none-
 Return:	-none-
 Since:		3.0.3
-------------------------------------------------------------*/
function adrotate_database_upgrade() {
	global $wpdb;

	$adrotate_db_version = get_option("adrotate_db_version");

	// Database: 	24
	// AdRotate:	3.8b412
	if($adrotate_db_version['current'] < 24) {
		if($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}adrotate_stats_tracker'")) {
			$wpdb->query("RENAME TABLE `{$wpdb->prefix}adrotate_stats_tracker` TO `{$wpdb->prefix}adrotate_stats`;");
		}
	}

	// Database: 	25
	// AdRotate:	3.8b413
	if($adrotate_db_version['current'] < 25) {
		$wpdb->query("CREATE INDEX `timer` ON `{$wpdb->prefix}adrotate_tracker` (timer);");
		$wpdb->query("CREATE INDEX `ipaddress` ON `{$wpdb->prefix}adrotate_tracker` (ipaddress);");
		$wpdb->query("CREATE INDEX `ad` ON `{$wpdb->prefix}adrotate_stats` (ad);");
		$wpdb->query("CREATE INDEX `thetime` ON `{$wpdb->prefix}adrotate_stats` (thetime);");
	}

	// Database: 	26
	// AdRotate:	3.8.1
	if($adrotate_db_version['current'] < 26) {
		adrotate_add_column("{$wpdb->prefix}adrotate", 'budget', 'double NOT NULL default \'0\' AFTER `sortorder`');
		adrotate_add_column("{$wpdb->prefix}adrotate", 'crate', 'double NOT NULL default \'0\' AFTER `budget`');
		adrotate_add_column("{$wpdb->prefix}adrotate", 'irate', 'double NOT NULL default \'0\' AFTER `crate`');
	}

	// Database: 	30
	// AdRotate:	3.8.3.4
	if($adrotate_db_version['current'] < 30) {
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'wrapper_before', 'longtext NOT NULL AFTER `page_loc`');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'wrapper_after', 'longtext NOT NULL AFTER `wrapper_before`');
	}

	// Database: 	32
	// AdRotate:	3.8.4.4
	if($adrotate_db_version['current'] < 32) {
		adrotate_add_column("{$wpdb->prefix}adrotate", 'cities', 'text NOT NULL AFTER `irate`');
		adrotate_add_column("{$wpdb->prefix}adrotate", 'countries', 'text NOT NULL AFTER `cities`');
		$geo_array = serialize(array());
		$wpdb->query("UPDATE `{$wpdb->prefix}adrotate` SET `cities` = '$geo_array' WHERE `cities` = '';");
		$wpdb->query("UPDATE `{$wpdb->prefix}adrotate` SET `countries` = '$geo_array' WHERE `countries` = '';");
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'geo', 'tinyint(1) NOT NULL default \'0\' AFTER `page_loc`');
	}

	// Database: 	33
	// AdRotate:	3.8.6
	if($adrotate_db_version['current'] < 33) {
		adrotate_del_column("{$wpdb->prefix}adrotate_groups", 'token');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'modus', 'tinyint(1) NOT NULL default \'0\' AFTER `name`');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'gridrows', 'int(3) NOT NULL default \'2\' AFTER `wrapper_after`');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'gridcolumns', 'int(3) NOT NULL default \'2\' AFTER `gridrows`');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'admargin', 'int(3) NOT NULL default \'1\' AFTER `gridcolumns`');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'adwidth', 'varchar(4) NOT NULL default \'125\' AFTER `admargin`');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'adheight', 'varchar(4) NOT NULL default \'125\' AFTER `adwidth`');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'adspeed', 'int(5) NOT NULL default \'6000\' AFTER `adheight`');
	}


	// Database: 	36
	// AdRotate:	3.8.10
	if($adrotate_db_version['current'] < 36) {
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'admargin_bottom', 'int(3) NOT NULL default \'1\' AFTER `admargin`');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'admargin_left', 'int(3) NOT NULL default \'1\' AFTER `admargin_bottom`');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'admargin_right', 'int(3) NOT NULL default \'1\' AFTER `admargin_left`');
	}

	// Database: 	38
	// AdRotate:	3.9
	if($adrotate_db_version['current'] < 38) {
		adrotate_add_column("{$wpdb->prefix}adrotate_linkmeta", 'schedule', 'int(5) NOT NULL default \'0\' AFTER `user`');
		$schedules = $wpdb->get_results("SELECT `id`, `ad` FROM {$wpdb->prefix}adrotate_schedule ORDER BY `id` ASC;");
		foreach($schedules as $schedule) {
			$wpdb->insert("{$wpdb->prefix}adrotate_linkmeta", array('ad' => $schedule->ad, 'group' => 0, 'user' => 0, 'schedule' => $schedule->id), array('%d', '%d', '%d', '%d', '%d'));
			unset($schedule);
		}
		unset($schedules);
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'name', 'varchar(255) NOT NULL default \'\' AFTER `id`');
		adrotate_del_column("{$wpdb->prefix}adrotate_schedule", 'ad');

		$schedules = $wpdb->get_results("SELECT `id` FROM {$wpdb->prefix}adrotate_schedule WHERE `name` = '' ORDER BY `id` ASC;");
		foreach($schedules as $schedule) {
			$wpdb->update("{$wpdb->prefix}adrotate_schedule", array('name' => 'Schedule '.$schedule->id), array('id' => $schedule->id));
			unset($schedule);
		}
		unset($schedules);
	}

	// Database: 	39
	// AdRotate:	3.9.1
	if($adrotate_db_version['current'] < 39) {
		adrotate_add_column("{$wpdb->prefix}adrotate_tracker", 'country', 'text NOT NULL AFTER `useragent`');
		adrotate_add_column("{$wpdb->prefix}adrotate_tracker", 'city', 'text NOT NULL AFTER `country`');
	}

	// Database: 	40
	// AdRotate:	3.9.9
	if($adrotate_db_version['current'] < 40) {
		adrotate_add_column("{$wpdb->prefix}adrotate", 'responsive', 'varchar(5) NOT NULL default \'N\' AFTER `tracker`');
	}

	// Database: 	41
	// AdRotate:	3.9.12
	if($adrotate_db_version['current'] < 41) {
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'page_par', 'tinyint(1) NOT NULL default \'0\' AFTER `page_loc`');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'cat_par', 'tinyint(1) NOT NULL default \'0\' AFTER `cat_loc`');
	}

	// Database: 	42
	// AdRotate:	3.10
	if($adrotate_db_version['current'] < 42) {
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'spread', 'varchar(5) NOT NULL default \'N\' AFTER `maximpressions`');
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'hourimpressions', 'int(15) NOT NULL default \'0\' AFTER `spread`');
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_groups` CHANGE `page_par` `page_par` tinyint(2) NOT NULL default '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_groups` CHANGE `cat_par` `cat_par` tinyint(2) NOT NULL default '0';");
	}

	// Database: 	43
	// AdRotate:	3.10.7
	if($adrotate_db_version['current'] < 43) {
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_groups` CHANGE `admargin` `admargin` int(2) NOT NULL default '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_groups` CHANGE `admargin_bottom` `admargin_bottom` int(2) NOT NULL default '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_groups` CHANGE `admargin_left` `admargin_left` int(2) NOT NULL default '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_groups` CHANGE `admargin_right` `admargin_right` int(2) NOT NULL default '0';");
	}

	// Database: 	44
	// AdRotate:	3.10.8
	if($adrotate_db_version['current'] < 44) {
		adrotate_del_column("{$wpdb->prefix}adrotate", 'timeframe');
		adrotate_del_column("{$wpdb->prefix}adrotate", 'timeframelength');
		adrotate_del_column("{$wpdb->prefix}adrotate", 'timeframeclicks');
		adrotate_del_column("{$wpdb->prefix}adrotate", 'timeframeimpressions');
	}

	// Database: 	46
	// AdRotate:	3.10.13
	if($adrotate_db_version['current'] < 46) {
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_schedule` CHANGE `hourimpressions` `dayimpressions` int(15) NOT NULL default '0';");
	}

	// Database: 	47
	// AdRotate:	3.10.18
	if($adrotate_db_version['current'] < 47) {
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'align', 'tinyint(1) NOT NULL default \'0\' AFTER `wrapper_after`');
	}

	// Database: 	48
	// AdRotate:	3.11.2b2
	if($adrotate_db_version['current'] < 48) {
		if($wpdb->get_var("SHOW INDEX FROM `{$wpdb->prefix}adrotate_tracker` WHERE Key_name = 'bannerid';") !== null) {
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_tracker` DROP KEY `bannerid`;");
		}
		if($wpdb->get_var("SHOW INDEX FROM `{$wpdb->prefix}adrotate_schedule` WHERE Key_name = 'stoptime';") !== null) {
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_schedule` DROP KEY `stoptime`;");
		}
		if($wpdb->get_var("SHOW INDEX FROM `{$wpdb->prefix}adrotate_schedule` WHERE Key_name = 'ad';") !== null) {
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_schedule` DROP KEY `ad`;");
		}
		adrotate_del_column("{$wpdb->prefix}adrotate_stats", 'block');
		adrotate_del_column("{$wpdb->prefix}adrotate_linkmeta", 'block');
	}

	// Database: 	49
	// AdRotate:	3.11.2b3
	if($adrotate_db_version['current'] < 49) {
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_stats` CHANGE `ad` `ad` INT(5) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_stats` CHANGE `group` `group` INT(5) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_stats` CHANGE `thetime` `thetime` INT(15) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_stats` CHANGE `clicks` `clicks` INT(15) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_stats` CHANGE `impressions` `impressions` INT(15) UNSIGNED NOT NULL DEFAULT '0';");

		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_tracker` CHANGE `timer` `timer` INT(15) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_tracker` CHANGE `bannerid` `bannerid` INT(15) UNSIGNED NOT NULL DEFAULT '0';");

		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_schedule` CHANGE `starttime` `starttime` INT(15) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_schedule` CHANGE `stoptime` `stoptime` INT(15) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_schedule` CHANGE `maxclicks` `maxclicks` INT(15) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_schedule` CHANGE `maximpressions` `maximpressions` INT(15) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_schedule` CHANGE `dayimpressions` `dayimpressions` INT(15) UNSIGNED NOT NULL DEFAULT '0';");

		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_linkmeta` CHANGE `ad` `ad` INT(5) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_linkmeta` CHANGE `group` `group` INT(5) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_linkmeta` CHANGE `user` `user` INT(5) UNSIGNED NOT NULL DEFAULT '0';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_linkmeta` CHANGE `schedule` `schedule` INT(5) UNSIGNED NOT NULL DEFAULT '0';");
	}

	// Database: 	50
	// AdRotate:	3.11.3b1
	if($adrotate_db_version['current'] < 50) {
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate_tracker` CHANGE `ipaddress` `ipaddress` varchar(15) NOT NULL DEFAULT '';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate` CHANGE `cbudget` `budget` double NOT NULL default '0';");
		adrotate_del_column("{$wpdb->prefix}adrotate", 'ibudget');
	}

	// Database: 	51
	// AdRotate:	3.12.5b2
	if($adrotate_db_version['current'] < 51) {
		adrotate_add_column("{$wpdb->prefix}adrotate", 'mobile', 'varchar(5) NOT NULL default \'N\' AFTER `tracker`');
		adrotate_add_column("{$wpdb->prefix}adrotate_groups", 'mobile', 'tinyint(1) NOT NULL default \'0\' AFTER `page_par`');
	}
	
	// Database: 	52
	// AdRotate:	3.13
	if($adrotate_db_version['current'] < 52) {
		adrotate_add_column("{$wpdb->prefix}adrotate", 'tablet', 'varchar(2) NOT NULL default \'N\' AFTER `mobile`');
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate` CHANGE `tracker` `tracker` varchar(2) NOT NULL default 'N';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate` CHANGE `mobile` `mobile` varchar(2) NOT NULL default 'N';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate` CHANGE `responsive` `responsive` varchar(2) NOT NULL default 'N';");
	}

	// Database: 	53
	// AdRotate:	3.13.1
	if($adrotate_db_version['current'] < 53) {
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'daystarttime', 'char(4) NOT NULL default \'0000\' AFTER `dayimpressions`');
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'daystoptime', 'char(4) NOT NULL default \'0000\' AFTER `daystarttime`');
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'day_mon', 'char(1) NOT NULL default \'Y\' AFTER `daystoptime`');
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'day_tue', 'char(1) NOT NULL default \'Y\' AFTER `day_mon`');
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'day_wed', 'char(1) NOT NULL default \'Y\' AFTER `day_tue`');
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'day_thu', 'char(1) NOT NULL default \'Y\' AFTER `day_wed`');
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'day_fri', 'char(1) NOT NULL default \'Y\' AFTER `day_thu`');
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'day_sat', 'char(1) NOT NULL default \'Y\' AFTER `day_fri`');
		adrotate_add_column("{$wpdb->prefix}adrotate_schedule", 'day_sun', 'char(1) NOT NULL default \'Y\' AFTER `day_sat`');
	}

	// Database: 	54
	// AdRotate:	3.14
	if($adrotate_db_version['current'] < 54) {
		adrotate_add_column("{$wpdb->prefix}adrotate", 'desktop', 'varchar(2) NOT NULL default \'Y\' AFTER `tracker`');
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate` CHANGE `mobile` `mobile` varchar(2) NOT NULL default 'Y';");
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}adrotate` CHANGE `tablet` `tablet` varchar(2) NOT NULL default 'Y';");
	}

	// Database: 	55
	// AdRotate:	3.14.1
	if($adrotate_db_version['current'] < 55) {
		$adverts = $wpdb->get_results("SELECT `id`, `bannercode`, `link` FROM {$wpdb->prefix}adrotate WHERE `link` != '' ORDER BY `id` ASC;");
		foreach($adverts as $advert) {
			$bannercode = $advert->bannercode;
			$bannercode = str_replace('%link%', $advert->link, $bannercode);
			$wpdb->update("{$wpdb->prefix}adrotate", array('bannercode' => $bannercode), array('id' => $advert->id));
			unset($advert, $bannercode);
		}
		adrotate_del_column("{$wpdb->prefix}adrotate", 'link');
	}

	// Database: 	56
	// AdRotate:	3.14.2
	if($adrotate_db_version['current'] < 56) {
		adrotate_del_column("{$wpdb->prefix}adrotate_tracker", 'useragent');
	}

	update_option("adrotate_db_version", array('current' => ADROTATE_DB_VERSION, 'previous' => $adrotate_db_version['current']));
}

/*-------------------------------------------------------------
 Name:      adrotate_core_upgrade

 Purpose:   Upgrades AdRotate where required
 Receive:   -none-
 Return:	-none-
 Since:		3.5
-------------------------------------------------------------*/
function adrotate_core_upgrade() {
	global $wp_roles;

	$firstrun = date('U') + 3600;
	$adrotate_version = get_option("adrotate_version");

	if($adrotate_version['current'] < 323) {
		delete_option('adrotate_notification_timer');
	}
	
	if($adrotate_version['current'] < 340) {
		add_option('adrotate_db_timer', date('U'));
	}

	if($adrotate_version['current'] < 350) {
		update_option('adrotate_debug', array('general' => false, 'stats' => false));
	}

	if($adrotate_version['current'] < 351) {
		wp_clear_scheduled_hook('adrotate_prepare_cache_statistics');
		delete_option('adrotate_stats');
	}

	if($adrotate_version['current'] < 352) {
		adrotate_remove_capability("adrotate_userstatistics"); // OBSOLETE IN 3.5
		adrotate_remove_capability("adrotate_globalstatistics"); // OBSOLETE IN 3.5
		$role = get_role('administrator');		
		$role->add_cap("adrotate_advertiser_report"); // NEW IN 3.5
		$role->add_cap("adrotate_global_report"); // NEW IN 3.5
	}

	if($adrotate_version['current'] < 353) {
		if(!is_dir(ABSPATH.'/wp-content/plugins/adrotate/language')) {
			mkdir(ABSPATH.'/wp-content/plugins/adrotate/language', 0755);
		}
	}

	if($adrotate_version['current'] < 354) {
		$crawlers = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi","looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory","Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot","www.galaxy.com", "Googlebot", "Scooter", "Slurp","msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz","Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot","Mediapartners-Google", "Sogou web spider", "WebAlta Crawler","bot", "crawler", "yahoo", "msn", "ask", "ia_archiver");
		update_option('adrotate_crawlers', $crawlers);
	}

	if($adrotate_version['current'] < 355) {
		if(!is_dir(ABSPATH.'/wp-content/reports')) {
			mkdir(ABSPATH.'/wp-content/reports', 0755);
		}
	}

	if($adrotate_version['current'] < 356) {
		adrotate_remove_capability("adrotate_advertiser_report");
		$role = get_role('administrator');		
		$role->add_cap("adrotate_advertiser");
	}
	
	if($adrotate_version['current'] < 357) {
		$role = get_role('administrator');		
		$role->add_cap("adrotate_moderate");
		$role->add_cap("adrotate_moderate_approve");
	}
	
	// 3.8.3.3
	if($adrotate_version['current'] < 363) {
		// Set defaults for internal versions
		$adrotate_db_version = get_option("adrotate_db_version");
		if(empty($adrotate_db_version)) update_option('adrotate_db_version', array('current' => ADROTATE_DB_VERSION, 'previous' => $adrotate_db_version['current']));
	}

	// 3.8.4
	if($adrotate_version['current'] < 364) {
		// Reset wp-cron tasks
		wp_clear_scheduled_hook('adrotate_ad_notification');
		wp_clear_scheduled_hook('adrotate_prepare_cache_statistics'); // OBSOLETE IN 3.6 - REMOVE IN 4.0
		wp_clear_scheduled_hook('adrotate_clean_trackerdata');
		wp_clear_scheduled_hook('adrotate_evaluate_ads');

		if(!wp_next_scheduled('adrotate_clean_trackerdata')) wp_schedule_event($firstrun, 'twicedaily', 'adrotate_clean_trackerdata');
	}

	// 3.8.5.1
	if($adrotate_version['current'] < 367) {
		if(!wp_next_scheduled('adrotate_evaluate_ads')) wp_schedule_event($firstrun, 'twicedaily', 'adrotate_evaluate_ads');
	}

	// 3.8.9
	if($adrotate_version['current'] < 368) {
		if(!is_dir(ABSPATH.'/wp-content/banners')) mkdir(ABSPATH.'/wp-content/banners', 0755);
		if(!is_dir(ABSPATH.'/wp-content/reports')) mkdir(ABSPATH.'/wp-content/reports', 0755);
	}

	// 3.9.9
	if($adrotate_version['current'] < 371) {
		// Reset wp-cron tasks
		if(!wp_next_scheduled('adrotate_clean_trackerdata')) wp_schedule_event($firstrun + 1800, 'twicedaily', 'adrotate_clean_trackerdata');
		if(!wp_next_scheduled('adrotate_evaluate_ads')) wp_schedule_event($firstrun + 3600, 'twicedaily', 'adrotate_evaluate_ads');
	}

	// 3.10
	if($adrotate_version['current'] < 373) {
		add_option('adrotate_responsive_required', 0);
	}

	// 3.10.10 (Pro 3.11)
	if($adrotate_version['current'] < 374) {
		add_option('adrotate_dynamic_required', 1);
	}

	// 3.10.13
	if($adrotate_version['current'] < 375) {
		wp_clear_scheduled_hook('adrotate_clean_trackerdata');
	}

	// 3.10.14
	if($adrotate_version['current'] < 376) {
		adrotate_check_config();
	}

	// 3.11.1
	if($adrotate_version['current'] < 377) {
		delete_option('adrotate_server');
		delete_option('adrotate_server_hide');
	}

	// 3.11.2
	if($adrotate_version['current'] < 378) {
		// Reset wp-cron tasks
		if(!wp_next_scheduled('adrotate_notification')) wp_schedule_event($firstrun, 'daily', 'adrotate_notification');
		if(!wp_next_scheduled('adrotate_clean_trackerdata')) wp_schedule_event($firstrun + 1800, 'twicedaily', 'adrotate_clean_trackerdata');
		if(!wp_next_scheduled('adrotate_evaluate_ads')) wp_schedule_event($firstrun + 3600, 'twicedaily', 'adrotate_evaluate_ads');
	}

	// 3.11.4
	if($adrotate_version['current'] < 379) {
		$config379 = get_option('adrotate_config');
		if($config379['enable_stats'] == 'Y') {
			$config379['stats'] = 1;
		} else {
			$config379['stats'] = 0;
		}
		unset($config379['enable_stats']);
		update_option('adrotate_config', $config379);
	}

	// 3.12
	if($adrotate_version['current'] < 380) {
		delete_option('adrotate_roles');
		update_option('adrotate_debug', array('general' => false, 'publisher' => false, 'timers' => false, 'track' => false));
		if(get_option('adrotate_hide_banner') == 1) update_option('adrotate_hide_banner', adrotate_now());
	}

	update_option("adrotate_version", array('current' => ADROTATE_VERSION, 'previous' => $adrotate_version['current']));
}

/*-------------------------------------------------------------
 Name:      adrotate_optimize_database

 Purpose:   Optimizes all AdRotate tables
 Receive:   -none-
 Return:    -none-
 Since:		3.4
-------------------------------------------------------------*/
function adrotate_optimize_database() {
	global $wpdb;
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$adrotate_db_timer 	= get_option('adrotate_db_timer');
	$now = adrotate_now();

	if($adrotate_db_timer < ($now - 86400)) {
		dbDelta("OPTIMIZE TABLE `{$wpdb->prefix}adrotate`, `{$wpdb->prefix}adrotate_groups`, `{$wpdb->prefix}adrotate_linkmeta`, `{$wpdb->prefix}adrotate_stats`, `{$wpdb->prefix}adrotate_tracker`, `{$wpdb->prefix}adrotate_schedule`, ;");
		dbDelta("REPAIR TABLE `{$wpdb->prefix}adrotate`, `{$wpdb->prefix}adrotate_groups`, `{$wpdb->prefix}adrotate_linkmeta`, `{$wpdb->prefix}adrotate_stats`, `{$wpdb->prefix}adrotate_tracker`, `{$wpdb->prefix}adrotate_schedule`;");
		update_option('adrotate_db_timer', $now);
		adrotate_return('adrotate-settings', 403);
	} else {
		adrotate_return('adrotate-settings', 504);
	}
}

/*-------------------------------------------------------------
 Name:      adrotate_cleanup_database

 Purpose:   Clean AdRotate tables
 Receive:   -none-
 Return:    -none-
 Since:		3.5
-------------------------------------------------------------*/
function adrotate_cleanup_database() {
	global $wpdb;

	$now = adrotate_now();
	
	// Delete expired schedules
	if(isset($_POST['adrotate_db_cleanup_schedules'])) {
		$wpdb->query("DELETE FROM `{$wpdb->prefix}adrotate_schedule` WHERE `stoptime` < $now;");
	}

	// Delete old stats
	if(isset($_POST['adrotate_db_cleanup_statistics'])) {
		$lastyear = $now - 30758400;
		$wpdb->query("DELETE FROM `{$wpdb->prefix}adrotate_stats` WHERE `thetime` < $lastyear;");
	}

	// Clean up Tracker data
	$yesterday = $now - 2073600;
	$wpdb->query("DELETE FROM `{$wpdb->prefix}adrotate_tracker` WHERE `timer` < $yesterday;");

	// Delete empty ads, groups and schedules
	$wpdb->query("DELETE FROM `{$wpdb->prefix}adrotate` WHERE `type` = 'empty' OR `type` = 'a_empty';");
	$wpdb->query("DELETE FROM `{$wpdb->prefix}adrotate_groups` WHERE `name` = '';");
	
	// Clean up meta data
	$ads = $wpdb->get_results("SELECT `id` FROM `{$wpdb->prefix}adrotate` ORDER BY `id`;");
	$metas = $wpdb->get_results("SELECT `id`, `ad` FROM `{$wpdb->prefix}adrotate_linkmeta` WHERE `ad` != '0' ORDER BY `id`;");
	
	$adverts = $linkmeta = array();
	foreach($ads as $ad) {
		$adverts[$ad->id] = $ad->id;
	}
	foreach($metas as $meta) {
		$linkmeta[$meta->id] = $meta->ad;
	}

	$result = array_diff($linkmeta, $adverts);
	foreach($result as $key => $value) {
		$wpdb->query("DELETE FROM `{$wpdb->prefix}adrotate_linkmeta` WHERE `id` = $key;");
		unset($result[$key]);
	}
	unset($ads, $metas, $adverts, $linkmeta, $result);

	// Clean up stray linkmeta
	$wpdb->query("DELETE FROM `{$wpdb->prefix}adrotate_linkmeta` WHERE `ad` = 0 OR `ad` = '';");

	adrotate_return('adrotate-settings', 406);
}

/*-------------------------------------------------------------
 Name:      adrotate_clean_trackerdata

 Purpose:   Removes old statistics
 Receive:   -none-
 Return:    -none-
 Since:		2.0
-------------------------------------------------------------*/
function adrotate_clean_trackerdata() {
	global $wpdb;

	$now = adrotate_now();
	$clicks = $now - 86400;
	$impressions = $now - 3600;

	$wpdb->query("DELETE FROM `{$wpdb->prefix}adrotate_tracker` WHERE (`timer` < ".$clicks." AND `stat` = 'c') OR (`timer` < ".$impressions." AND `stat` = 'i') OR `ipaddress`  = 'unknown' OR `ipaddress`  = '';");
}

/*-------------------------------------------------------------
 Name:      adrotate_add_column

 Purpose:   Check if the column exists in the table
 Receive:   $table_name, $column_name, $attributes
 Return:	Boolean
 Since:		3.0.3
-------------------------------------------------------------*/
function adrotate_add_column($table_name, $column_name, $attributes) {
	global $wpdb;
	
	foreach($wpdb->get_col("SHOW COLUMNS FROM $table_name;") as $column) {
		if($column == $column_name) return true;
	}
	
	$wpdb->query("ALTER TABLE $table_name ADD $column_name " . $attributes.";");
	
	foreach($wpdb->get_col("SHOW COLUMNS FROM $table_name;") as $column) {
		if($column == $column_name) return true;
	}
	
	return false;
}

/*-------------------------------------------------------------
 Name:      adrotate_del_column

 Purpose:   Check if the column exists in the table remove if it does
 Receive:   $table_name, $column_name
 Return:	Boolean
 Since:		3.8.3.3
-------------------------------------------------------------*/
function adrotate_del_column($table_name, $column_name) {
	global $wpdb;
	
	foreach($wpdb->get_col("SHOW COLUMNS FROM $table_name;") as $column) {
		if($column == $column_name) {
			$wpdb->query("ALTER TABLE $table_name DROP $column;");
			return true;
		}
	}
	
	return false;
}
?>
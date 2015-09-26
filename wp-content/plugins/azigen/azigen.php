<?php
/*
Plugin Name: Azigen
Version: 0.1
Plugin URI: http://azigen.hu
Description: Ez a plugin generálja a magyar WordPress Planet widgetet, meg az idézetest
Author: DjZoNe
Author URI: http://djz.hu/
*/

function azigen_install()
{
  $widget_options['dashboard_primary'] = array(
  	'link' => apply_filters( 'dashboard_primary_link',  __( 'http://napsugar.net/' ) ),
  	'url' => apply_filters( 'dashboard_primary_feed',  __( 'http://feeds.feedburner.com/idezetek' ) ),
  	'title' => apply_filters( 'dashboard_primary_title', __( 'A napi lélekmelegítőd' ) ),
  	'items' => 1,
  	'show_summary' => 1,
  	'show_author' => 1,
  	'show_date' => 1
  );
  
  $widget_options['dashboard_secondary'] = array(
  	'link' => apply_filters( 'dashboard_secondary_link',  __( 'http://azigen.hu/category/tech/wordpress/' ) ),
  	'url' => apply_filters( 'dashboard_secondary_feed',  __( 'http://feeds.feedburner.com/WordpressPlanet' ) ),
  	'title' => apply_filters( 'dashboard_primary_title', __( 'Magyar WordPress Planet' ) ),
  	'items' => 3,
  	'show_summary' => 1,
  	'show_author' => 1,
  	'show_date' => 1
  );
  
  update_option('dashboard_widget_options', $widget_options);
}

add_action('activate_azigen/azigen.php', 'azigen_install');
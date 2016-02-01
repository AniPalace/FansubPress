<?php
/*
Plugin Name: FansubPressPlugin
Version: 1.0
Plugin URI: https://github.com/AniPalace/FansubPress
Description: A FansubPressPlugin egy WordPress plugin, ami kifejezetten az anime fansubbereknek készült
Author: AniPalace
Author URI: http://anipalace.hu/
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once(dirname( __FILE__ ).'/class/FansubPressPlugin.php');

// Nyelvi textdomain beolvasása
load_plugin_textdomain('fansubpress-plugin', false, basename( dirname( __FILE__ ) ) . '/languages' );

// Pluginok be lettek töltve, mehet a plugin inicializálás
FansubPressPlugin::getInstanceClass()->init();
<?php

require_once(dirname(__FILE__) . '/FansubPressPluginBase.php');

class FansubPressPluginTemplate extends FansubPressPluginBase {

    function init() {
	add_action('customize_register', array($this, 'costumizeRegister'));
    }

    /**
     * Egyedi template beállítások
     * 
     * @param WP_Customize_Manager $wp_customize
     */
    function costumizeRegister($wp_customize) {
	$wp_customize->add_section('fansubpress_social_icon_section', array(
	    'title' => __('Közösségi oldalak', 'fansubpress-plugin'),
	    'priority' => 100,
	    'capability' => 'edit_theme_options',
	    'description' => __('Közösségi oldalak linkek megadása', 'fansubpress-plugin'),
	));

	$wp_customize->add_setting('fansubpress_indavideo', array(
	    'default' => ''
	));
	
	$wp_customize->add_setting('fansubpress_facebook', array(
	    'default' => ''
	));

	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'fansubpress_social_icons_indavideo', array(
	    'label' => __('Indavideo', 'fansubpress-plugin'),
	    'section' => 'fansubpress_social_icon_section',
	    'settings' => 'fansubpress_indavideo',
	)));
	
	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'fansubpress_social_icons_facebook', array(
	    'label' => __('Facebook', 'fansubpress-plugin'),
	    'section' => 'fansubpress_social_icon_section',
	    'settings' => 'fansubpress_facebook',
	)));
    }

}

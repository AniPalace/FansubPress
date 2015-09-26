<?php
/**
 * Fifteen Theme Customizer
 *
 * @package Fifteen
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function fifteen_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
	$wp_customize->remove_control( 'header_textcolor');
	
	$wp_customize->add_setting( 'fifteen_title_color', array (
			'default'	=> '#22233A',
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
	
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'fifteen_title_color', array(
			'label'    => __( 'Site Title Color', 'fifteen' ),
			'section'  => 'colors',
			'settings' => 'fifteen_title_color',
			'priority'    => 102,
	) ) );
	
	$wp_customize->add_setting( 'fifteen_desc_color', array (
			'default'	=> '#ffffff',
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
			
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'fifteen_desc_color', array(
			'label'    => __( 'Site Description Color', 'fifteen' ),
			'section'  => 'colors',
			'settings' => 'fifteen_desc_color',
			'priority'    => 102,
	) ) );
	
	$wp_customize->add_setting( 'fifteen_menu_color', array (
			'default'	=> '#ffffff',
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
			
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'fifteen_menu_color', array(
			'label'    => __( 'Primary Menu Color', 'fifteen' ),
			'section'  => 'colors',
			'settings' => 'fifteen_menu_color',
			'priority'    => 106,
	) ) );
	
}
add_action( 'customize_register', 'fifteen_customize_register' );

if ( ! function_exists( 'fifteen_apply_color' ) ) :
  function fifteen_apply_color() { ?>
  		<style id="fifteen-custom-style">
  		<?php if (get_theme_mod('fifteen_desc_color') ) : ?>
  			.site-description { color: <?php echo get_theme_mod('fifteen_desc_color') ?> }
  		<?php endif; ?>
  		<?php if (get_theme_mod('fifteen_title_color') ) : ?>
  			.site-title a { color: <?php echo get_theme_mod('fifteen_title_color') ?> }
  		<?php endif; ?>
  		<?php if (get_theme_mod('fifteen_menu_color') ) : ?>
  			#site-navigation ul > li a, .menu-toggle, .primary-navigation.toggled .nav-menu { color: <?php echo get_theme_mod('fifteen_menu_color') ?> }
  		<?php endif; ?>
  		
  		</style>	
  <?php 	
  }
  endif;

add_action( 'wp_head', 'fifteen_apply_color' );


/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function fifteen_customize_preview_js() {
	wp_enqueue_script( 'fifteen_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'fifteen_customize_preview_js' );

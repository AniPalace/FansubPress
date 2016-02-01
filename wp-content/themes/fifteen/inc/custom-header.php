<?php
/**
 * Sample implementation of the Custom Header feature
 * http://codex.wordpress.org/Custom_Headers

 * @package Fifteen
 */

function fifteen_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'fifteen_custom_header_args', array(
		'default-image'          => get_template_directory_uri().'/images/header.jpg',
		'default-text-color'     => 'ffffff',
		'width'                  => 1920,
		'height'                 => 600,
		'flex-height'            => true,
		'wp-head-callback'       => 'fifteen_header_style',
		'admin-head-callback'    => 'fifteen_admin_header_style',
		'admin-preview-callback' => 'fifteen_admin_header_image',
	) ) );
}
add_action( 'after_setup_theme', 'fifteen_custom_header_setup' );

if ( ! function_exists( 'fifteen_header_style' ) ) :
/**
 * Styles the header image and text displayed on the blog
 *
 * @see fifteen_custom_header_setup().
 */
function fifteen_header_style() {
	$header_text_color = get_header_textcolor();
	?>
	<style type="text/css">
	<?php
		// Has the text been hidden?
		if ( 'blank' == $header_text_color ) :
	?>
		.site-title,
		.site-description {
			position: absolute;
			clip: rect(1px, 1px, 1px, 1px);
		}
		#social-icons {
			margin-top: 105px;
		}
	<?php endif; 
		//Check if user has defined any header image.
		if ( get_header_image() ) :
	?>
		#parallax-bg {
			background: url(<?php echo get_header_image(); ?>) no-repeat;
			background-position: center top;
			background-size: cover;
		}
	<?php endif; ?>	
	</style>
	<?php
}
endif; // fifteen_header_style

if ( ! function_exists( 'fifteen_admin_header_style' ) ) :
/**
 * Styles the header image displayed on the Appearance > Header admin panel.
 *
 * @see fifteen_custom_header_setup().
 */
function fifteen_admin_header_style() {
?>
	<style type="text/css">
		.appearance_page_custom-header #headimg {
			border: none;
		}
	</style>
<?php
}
endif; // fifteen_admin_header_style

if ( ! function_exists( 'fifteen_admin_header_image' ) ) :
/**
 * Custom header image markup displayed on the Appearance > Header admin panel.
 *
 * @see fifteen_custom_header_setup().
 */
function fifteen_admin_header_image() {
	$style = sprintf( ' style="color:#%s;"', get_header_textcolor() );
?>
	<div id="headimg">
		<?php if ( get_header_image() ) : ?>
		<img src="<?php header_image(); ?>" alt="">
		<?php endif; ?>
	</div>
<?php
}
endif; // fifteen_admin_header_image

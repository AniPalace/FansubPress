<?php

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}



require dirname( __FILE__ ).'/inc/widgets.php';
require dirname( __FILE__ ).'/inc/image-size.php';

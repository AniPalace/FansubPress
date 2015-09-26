<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 */

function optionsframework_option_name() {

	// This gets the theme name from the stylesheet
	$themename = wp_get_theme();
	$themename = preg_replace("/\W/", "_", strtolower($themename) );

	$optionsframework_settings = get_option( 'optionsframework' );
	$optionsframework_settings['id'] = $themename;
	update_option( 'optionsframework', $optionsframework_settings );
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *
 * If you are making your theme translatable, you should replace 'fifteen'
 * with the actual text domain for your theme.  Read more:
 * http://codex.wordpress.org/Function_Reference/load_theme_textdomain
 */

function optionsframework_options() {

	$options = array();
	$imagepath =  get_template_directory_uri() . '/images/';	
	$true_false = array(
		'true' => __('true', 'fifteen'),
		'false' => __('false', 'fifteen')
	);	
	
	//Basic Settings
	
	$options[] = array(
		'name' => __('Basic Settings', 'fifteen'),
		'type' => 'heading');
			
	$options[] = array(
		'name' => __('Site Logo', 'fifteen'),
		'desc' => __('Leave Blank to use text Heading.', 'fifteen'),
		'id' => 'logo',
		'class' => '',
		'type' => 'upload');		
		
	$options[] = array(
		'name' => __('Copyright Text', 'fifteen'),
		'desc' => __('Some Text regarding copyright of your site, you would like to display in the footer.', 'fifteen'),
		'id' => 'footertext2',
		'std' => '',
		'type' => 'textarea');
		
	$options[] = array(
		'name' => __('More Settings', 'fifteen'),
		'desc' => __('More Settings Like Analytics, Footer Codes, Header Codes, Responsive Navigation, etc are available in Fifteen plus. <a href="http://inkhive.com/product/fifteen-plus/" target="_blank">Upgrade to Pro at $24.90</a>.', 'fifteen'),
		'type' => 'info');	
	
	//Design Settings
		
	$options[] = array(
		'name' => __('Layout Settings', 'fifteen'),
		'type' => 'heading');	
	
	$options[] = array(
		'name' => "Sidebar Layout",
		'desc' => "Select Layout for Posts & Pages.",
		'id' => "sidebar-layout",
		'std' => "right",
		'type' => "images",
		'options' => array(
			'left' => $imagepath . '2cl.png',
			'right' => $imagepath . '2cr.png')
	);
	
	$options[] = array(
		'name' => __('Custom CSS', 'fifteen'),
		'desc' => __('Some Custom Styling for your site. Place any css codes here instead of the style.css file.', 'fifteen'),
		'id' => 'style2',
		'std' => '',
		'type' => 'textarea');
	
	$options[] = array(
		'name' => __('More Layout Options', 'fifteen'),
		'desc' => __('Fifteen Plus Supports a Featured Slider with over 16 Animation Effects and plenty of other configuration options. <a href="http://inkhive.com/product/fifteen-plus/" target="_blank">Upgrade to Pro at $24.90</a>.', 'fifteen'),
		'type' => 'info');
				
	//Social Settings
	
	$options[] = array(
	'name' => __('Social Settings', 'fifteen'),
	'type' => 'heading');

	$options[] = array(
		'name' => __('Facebook', 'fifteen'),
		'desc' => __('Facebook Profile or Page URL i.e. http://facebook.com/username/ ', 'fifteen'),
		'id' => 'facebook',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');
	
	$options[] = array(
		'name' => __('Twitter', 'fifteen'),
		'desc' => __('Twitter Username', 'fifteen'),
		'id' => 'twitter',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');
	
	$options[] = array(
		'name' => __('Google Plus', 'fifteen'),
		'desc' => __('Google Plus profile url, including "http://"', 'fifteen'),
		'id' => 'google',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');
		
	$options[] = array(
		'name' => __('Feeburner', 'fifteen'),
		'desc' => __('URL for your RSS Feeds', 'fifteen'),
		'id' => 'feedburner',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');	
		
	$options[] = array(
		'name' => __('Pinterest', 'fifteen'),
		'desc' => __('Your Pinterest Profile URL', 'fifteen'),
		'id' => 'pinterest',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');
		
	$options[] = array(
		'name' => __('Instagram', 'fifteen'),
		'desc' => __('Your Instagram Profile URL', 'fifteen'),
		'id' => 'instagram',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');	
		
	$options[] = array(
		'name' => __('Linked In', 'fifteen'),
		'desc' => __('Your Linked In Profile URL', 'fifteen'),
		'id' => 'linkedin',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');	
		
	$options[] = array(
		'name' => __('Youtube', 'fifteen'),
		'desc' => __('Your Youtube Channel URL', 'fifteen'),
		'id' => 'youtube',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');
		
	$options[] = array(
		'name' => __('Flickr', 'fifteen'),
		'desc' => __('Your Flickr Profile URL', 'fifteen'),
		'id' => 'flickr',
		'std' => '',
		'class' => 'mini',
		'type' => 'text');	
		
	$options[] = array(
		'name' => __('More Icons & Images', 'fifteen'),
		'desc' => __('For more Icons, Custom Icons and Choice of images - <a href="http://inkhive.com/product/fifteen-plus/" target="_blank">Upgrade to Pro at $24.90</a>.', 'fifteen'),
		'type' => 'info');				
		
	$options[] = array(
		'name' => __('Support', 'fifteen'),
		'type' => 'heading');
	
	$options[] = array(
		'desc' => __('Fifteen WordPress theme has been Designed and Created by <a href="http://InkHive.com" target="_blank">InkHive</a>. For any Queries or help regarding this theme, <a href="http://wordpress.org/support/theme/fifteen/" target="_blank">use the WordPress support forums</a>.', 'fifteen'),
		'type' => 'info');		
		
	 $options[] = array(
		'desc' => __('<a href="http://twitter.com/rohitinked" target="_blank">Follow Me on Twitter</a> to know about my upcoming themes.', 'fifteen'),
		'type' => 'info');		
	
	$options[] = array(
		'name' => __('Dedicated Support', 'fifteen'),
		'desc' => __('We offer Dedicated and Fast e-mail Support only for Pro Version Customers. <a href="http://inkhive.com/product/fifteen-plus/" target="_blank">Upgrade to Pro at $24.90</a>.', 'fifteen'),
		'type' => 'info');	
	
	$options[] = array(
		'name' => __('Live Demo Blog', 'fifteen'),
		'desc' => __('For your convenience, we have created a <a href="http://demo.inkhive.com/fifteen/" target="_blank">Live Demo Blog of Fifteen</a>. You can take a look at and find out how your site would look once complete.', 'fifteen'),
		'type' => 'info');	
	
	$options[] = array(
		'name' => __('Regenerating Post Thumbnails', 'fifteen'),
		'desc' => __('If you are using Fifteen Theme on a New Wordpress Installation, then you can skip this section.<br />But if you have just switched to this theme from some other theme, then you are requested regenerate all the post thumbnails. It will fix all the issues you are facing with distorted & ugly homepage thumbnail Images. ', 'fifteen'),
		'type' => 'info');	
		
	$options[] = array(
		'desc' => __('To Regenerate all Thumbnail images, Install and Activate the <a href="http://wordpress.org/plugins/regenerate-thumbnails/" target="_blank">Regenerate Thumbnails</a> WP Plugin. Then from <strong>Tools &gt; Regen. Thumbnails</strong>, re-create thumbnails for all your existing images. And your blog will look even more stylish with Fifteen theme.<br /> ', 'fifteen'),
		'type' => 'info');	
		
	$options[] = array(
		'desc' => __('<strong>Note:</strong> Regenerating the thumbnails, will not affect your original images. It will just generate a separate image file for those images.', 'fifteen'),
		'type' => 'info');	
			
	$options[] = array(
		'name' => __('Theme Credits', 'fifteen'),
		'desc' => __('Check this if you do not want to give us credit in your site footer.', 'fifteen'),
		'id' => 'credit1',
		'std' => '0',
		'type' => 'checkbox');
	
	return $options;
}
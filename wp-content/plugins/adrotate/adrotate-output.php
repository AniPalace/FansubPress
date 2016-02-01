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
 Name:      adrotate_ad

 Purpose:   Show requested ad
 Receive:   $banner_id, $individual, $group, $site
 Return:    $output
 Since:		3.0
-------------------------------------------------------------*/
function adrotate_ad($banner_id, $individual = true, $group = null, $site = 0) {
	global $wpdb, $adrotate_config, $adrotate_debug;

	$output = '';

	if($banner_id) {			
		$banner = $wpdb->get_row($wpdb->prepare("SELECT `id`, `title`, `bannercode`, `tracker`, `image`, `responsive` FROM `{$wpdb->prefix}adrotate` WHERE `id` = %d AND (`type` = 'active' OR `type` = '2days' OR `type` = '7days');", $banner_id));

		if($banner) {
			if($adrotate_debug['general'] == true) {
				echo "<p><strong>[DEBUG][adrotate_ad()] Selected Ad ID</strong><pre>";
				print_r($banner->id); 
				echo "</pre></p>"; 
			}
			
			$selected = array($banner->id => 0);			
			$selected = adrotate_filter_schedule($selected, $banner);
		} else {
			$selected = false;
		}
		
		if($selected) {
			$image = str_replace('%folder%', $adrotate_config['banner_folder'], $banner->image);

			if($individual == true) $output .= '<div class="a-single a-'.$banner->id.'">';
			$output .= adrotate_ad_output($banner->id, 0, $banner->title, $banner->bannercode, $banner->tracker, $image, $banner->responsive);
			if($individual == true) $output .= '</div>';

			if($adrotate_config['stats'] == 1 AND $banner->tracker == "Y") {
				adrotate_count_impression($banner->id, 0, 0, $adrotate_config['impression_timer']);
			}
		} else {
			$output .= adrotate_error('ad_expired', array($banner_id));
		}
		unset($banner);
	} else {
		$output .= adrotate_error('ad_no_id');
	}

	return $output;
}

/*-------------------------------------------------------------
 Name:      adrotate_group

 Purpose:   Fetch ads in specified group(s) and show a random ad
 Receive:   $group_ids, $fallback, $weight, $site
 Return:    $output
 Since:		3.0
-------------------------------------------------------------*/
function adrotate_group($group_ids, $fallback = 0, $weight = 0, $site = 0) { 
	global $wpdb, $adrotate_config, $adrotate_debug;

	$output = $group_select = '';
	if($group_ids) {
		$now = adrotate_now();
		$group_array = (!is_array($group_ids)) ? explode(",", $group_ids) : $group_ids;

		foreach($group_array as $key => $value) {
			$group_select .= " `{$wpdb->prefix}adrotate_linkmeta`.`group` = {$value} OR";
		}
		$group_select = rtrim($group_select, " OR");

		$group = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}adrotate_groups` WHERE `name` != '' AND `id` = %d;", $group_array[0]));

		if($adrotate_debug['general'] == true) {
			echo "<p><strong>[DEBUG][adrotate_group] Selected group</strong><pre>"; 
			print_r($group);
			echo "</pre></p>";
		}

		if($group) {
			// Get all ads in all selected groups
			$ads = $wpdb->get_results(
				"SELECT 
					`{$wpdb->prefix}adrotate`.`id`, 
					`{$wpdb->prefix}adrotate`.`title`, 
					`{$wpdb->prefix}adrotate`.`bannercode`, 
					`{$wpdb->prefix}adrotate`.`image`, 
					`{$wpdb->prefix}adrotate`.`responsive`, 
					`{$wpdb->prefix}adrotate`.`tracker`, 
					`{$wpdb->prefix}adrotate_linkmeta`.`group`
				FROM 
					`{$wpdb->prefix}adrotate`, 
					`{$wpdb->prefix}adrotate_linkmeta` 
				WHERE 
					({$group_select}) 
					AND `{$wpdb->prefix}adrotate_linkmeta`.`user` = 0 
					AND `{$wpdb->prefix}adrotate`.`id` = `{$wpdb->prefix}adrotate_linkmeta`.`ad` 
					AND (`{$wpdb->prefix}adrotate`.`type` = 'active' 
						OR `{$wpdb->prefix}adrotate`.`type` = '2days'
						OR `{$wpdb->prefix}adrotate`.`type` = '7days')
				GROUP BY `{$wpdb->prefix}adrotate`.`id`
				ORDER BY `{$wpdb->prefix}adrotate`.`id`;");
		
			if($ads) {
				if($adrotate_debug['general'] == true) {
					echo "<p><strong>[DEBUG][adrotate_group()] All ads in group</strong><pre>"; 
					print_r($ads); 
					echo "</pre></p>"; 
				}			

				foreach($ads as $ad) {
					$selected[$ad->id] = $ad;
					$selected = adrotate_filter_schedule($selected, $ad);
				}
				unset($ads);
				
				if($adrotate_debug['general'] == true) {
					echo "<p><strong>[DEBUG][adrotate_group] Reduced array based on schedule restrictions</strong><pre>"; 
					print_r($selected); 
					echo "</pre></p>"; 
				}			

				$array_count = count($selected);
				if($array_count > 0) {
					$before = $after = '';
					$before = str_replace('%id%', $group_array[0], stripslashes(html_entity_decode($group->wrapper_before, ENT_QUOTES)));
					$after = str_replace('%id%', $group_array[0], stripslashes(html_entity_decode($group->wrapper_after, ENT_QUOTES)));

					$output .= '<div class="g g-'.$group->id.'">';

					// Kill dynamic mode for mobile users
					if($adrotate_config['mobile_dynamic_mode'] == 'Y' AND $group->modus == 1 AND (adrotate_is_mobile() OR adrotate_is_tablet())) {
						$group->modus = 0;
					}

					if($group->modus == 1) { // Dynamic ads
						$i = 1;

						// Limit group to save resources
						$amount = ($group->adspeed >= 10000) ? 10 : 20;
						
						// Randomize and trim output
						$selected = adrotate_shuffle($selected);
						foreach($selected as $key => $banner) {
							if($i <= $amount) {
								$image = str_replace('%folder%', $adrotate_config['banner_folder'], $banner->image);
	
								$output .= '<div class="g-dyn a-'.$banner->id.' c-'.$i.'">';
								$output .= $before.adrotate_ad_output($banner->id, $group->id, $banner->title, $banner->bannercode, $banner->tracker, $image, $banner->responsive).$after;
								$output .= '</div>';
								$i++;
							}
						}
					} else if($group->modus == 2) { // Block of ads
						$block_count = $group->gridcolumns * $group->gridrows;
						if($array_count < $block_count) $block_count = $array_count;
						$columns = 1;

						for($i=1;$i<=$block_count;$i++) {
							$banner_id = array_rand($selected, 1);

							$image = str_replace('%folder%', $adrotate_config['banner_folder'], $selected[$banner_id]->image);

							$output .= '<div class="g-col b-'.$group->id.' a-'.$selected[$banner_id]->id.'">';
							$output .= $before.adrotate_ad_output($selected[$banner_id]->id, $group->id, $selected[$banner_id]->title, $selected[$banner_id]->bannercode, $selected[$banner_id]->tracker, $image, $selected[$banner_id]->responsive).$after;
							$output .= '</div>';

							if($columns == $group->gridcolumns AND $i != $block_count) {
								$output .= '</div><div class="g g-'.$group->id.'">';
								$columns = 1;
							} else {
								$columns++;
							}

							if($adrotate_config['stats'] == 1){
								adrotate_count_impression($selected[$banner_id]->id, $group->id, 0, $adrotate_config['impression_timer']);
							}

							unset($selected[$banner_id]);
						}
					} else { // Default (single ad)
						$banner_id = array_rand($selected, 1);

						$image = str_replace('%folder%', $adrotate_config['banner_folder'], $selected[$banner_id]->image);

						$output .= '<div class="g-single a-'.$selected[$banner_id]->id.'">';
						$output .= $before.adrotate_ad_output($selected[$banner_id]->id, $group->id, $selected[$banner_id]->title, $selected[$banner_id]->bannercode, $selected[$banner_id]->tracker, $image, $selected[$banner_id]->responsive).$after;
						$output .= '</div>';

						if($adrotate_config['stats'] == 1){
							adrotate_count_impression($selected[$banner_id]->id, $group->id, 0, $adrotate_config['impression_timer']);
						}
					}

					$output .= '</div>';

					unset($selected);
				} else {
					$output .= adrotate_error('ad_expired');
				}
			} else { 
				$output .= adrotate_error('ad_unqualified');
			}
		} else {
			$output .= adrotate_error('group_not_found', array($group_array[0]));
		}
	} else {
		$output .= adrotate_error('group_no_id');
	}

	return $output;
}

/*-------------------------------------------------------------
 Name:      adrotate_inject_posts

 Purpose:   Add an advert to a single post
 Receive:   $post_content
 Return:    $post_content
 Added:		3.7
-------------------------------------------------------------*/
function adrotate_inject_posts($post_content) { 
	global $wpdb, $post, $adrotate_debug;
	
	$group_array = array();
	if(is_page()) {
		// Inject ads into page
		$ids = $wpdb->get_results("SELECT `id`, `page`, `page_loc`, `page_par` FROM `{$wpdb->prefix}adrotate_groups` WHERE `page_loc` > 0 AND  `page_loc` < 5;");
		
		foreach($ids as $id) {
			$pages = explode(",", $id->page);
			if(!is_array($pages)) $pages = array();

			if(in_array($post->ID, $pages)) {
				$group_array[$id->id] = array('location' => $id->page_loc, 'paragraph' => $id->page_par, 'ids' => $pages);
			}
		}
		unset($ids, $pages);
	}
	
	if(is_single()) {
		// Inject ads into posts in specified category
		$ids = $wpdb->get_results("SELECT `id`, `cat`, `cat_loc`, `cat_par` FROM `{$wpdb->prefix}adrotate_groups` WHERE `cat_loc` > 0 AND `cat_loc` < 5;");
		$wp_categories = get_terms('category', array('fields' => 'ids'));

		foreach($ids as $id) {
			$categories = explode(",", $id->cat);
			if(!is_array($categories)) $categories = array();

			foreach($wp_categories as &$value) {
				if(in_array($value, $categories)) {
					$group_array[$id->id] = array('location' => $id->cat_loc, 'paragraph' => $id->cat_par, 'ids' => $categories);
				}
			}
		}
		unset($ids, $wp_categories, $categories);
	}

	$group_array = adrotate_shuffle($group_array);	
	$group_count = count($group_array);

	if($adrotate_debug['general'] == true) {
		echo "<p><strong>[DEBUG][adrotate_inject_posts()] group_array</strong><pre>"; 
		echo "Group count: ".$group_count."</br>";
		print_r($group_array); 
		echo "</pre></p>"; 
	}

	if($group_count > 0) {
		$before = $after = $inside = 0;
		foreach($group_array as $group_id => $group) {
			if(is_page($group['ids']) OR is_category($group['ids']) OR in_category($group['ids'])) {
				// Advert in front of content
				if(($group['location'] == 1 OR $group['location'] == 3) AND $before == 0) {
					$post_content = adrotate_group($group_id).$post_content;
					unset($group_array[$group_id]);
					$before = 1;
				}
	
				// Advert behind the content
				if(($group['location'] == 2 OR $group['location'] == 3) AND $after == 0) {
					$post_content = $post_content.adrotate_group($group_id);
					unset($group_array[$group_id]);
					$after = 1;
				}

				// Adverts inside the content
				if($group['location'] == 4) {
				    $paragraphs = explode('</p>', $post_content);
					$paragraph_count = count($paragraphs);
					$count_p = ($group['paragraph'] == 99) ? ceil($paragraph_count / 2) : $group['paragraph'];

				    foreach($paragraphs as $index => $paragraph) {
				        if(trim($paragraph)) {
				            $paragraphs[$index] .= '</p>';
				        }

				        if($count_p == $index + 1 AND $inside == 0) {
				            $paragraphs[$index] .= adrotate_group($group_id);
							unset($group_array[$group_id]);
				            $inside = 1;
				        }
				    }
				    $inside = 0; // Reset for the next paragraph
				    $post_content = implode('', $paragraphs);
					unset($paragraphs, $paragraph_count);
				}
			}
		}
		unset($group_array, $before, $after, $inside);
	}
	return $post_content;
}

/*-------------------------------------------------------------
 Name:      adrotate_preview

 Purpose:   Show preview of selected ad (Dashboard)
 Receive:   $banner_id
 Return:    $output
 Since:		3.0
-------------------------------------------------------------*/
function adrotate_preview($banner_id) {
	global $wpdb, $adrotate_debug;

	if($banner_id) {
		$now = adrotate_now();
		
		$banner = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}adrotate` WHERE `id` = %d;", $banner_id));

		if($adrotate_debug['general'] == true) {
			echo "<p><strong>[DEBUG][adrotate_preview()] Ad information</strong><pre>"; 
			print_r($banner); 
			echo "</pre></p>"; 
		}			

		if($banner) {
			$image = str_replace('%folder%', '/wp-content/banners/', $banner->image);		
			$output = adrotate_ad_output($banner->id, 0, $banner->title, $banner->bannercode, $banner->tracker, $image, 'N');
		} else {
			$output = adrotate_error('ad_expired');
		}
	} else {
		$output = adrotate_error('ad_no_id');
	}

	return $output;
}

/*-------------------------------------------------------------
 Name:      adrotate_ad_output

 Purpose:   Prepare the output for viewing
 Receive:   $id, $group, $bannercode, $tracker, $image, $responsive
 Return:    $banner_output
 Since:		3.0
-------------------------------------------------------------*/
function adrotate_ad_output($id, $group = 0, $name, $bannercode, $tracker, $image, $responsive) {
	global $blog_id, $adrotate_debug, $adrotate_config;

	$banner_output = $bannercode;
	$banner_output = stripslashes(htmlspecialchars_decode($banner_output, ENT_QUOTES));

	if($adrotate_config['stats'] > 0) {
		if(empty($blog_id) or $blog_id == '') {
			$blog_id = 0;
		}
		
		if($adrotate_config['stats'] == 1 AND $tracker == "Y") { // Internal tracker
			preg_match_all('/<a[^>](?:.*?)>/i', $banner_output, $matches, PREG_SET_ORDER);
			if(isset($matches[0])) {
				$banner_output = str_replace('<a ', '<a data-track="'.adrotate_hash($id, $group, $blog_id).'" ', $banner_output);
				foreach($matches[0] as $value) {
					if(preg_match('/<a[^>]+class=\"(.+?)\"[^>]*>/i', $value, $regs)) {
					    $result = $regs[1]." gofollow";
						$banner_output = str_replace('class="'.$regs[1].'"', 'class="'.$result.'"', $banner_output);	    
					} else {
						$banner_output = str_replace('<a ', '<a class="gofollow" ', $banner_output);
					}
					unset($value, $regs, $result);
				}
			}
			if($adrotate_debug['timers'] == true) {
				$banner_output = str_replace('<a ', '<a data-debug="1" ', $banner_output);
			}
		}
	}

	// Add Responsive classes
	preg_match_all('/<img[^>](?:.*?)>/i', $banner_output, $matches, PREG_SET_ORDER);
	if(isset($matches[0])) {
		foreach($matches[0] as $value) {
			if(preg_match('/<img[^>]+class=\"(.+?)\"[^>]*>/i', $value, $regs)) {
				$result = $regs[1];
				if($responsive == 'Y') $result .= " responsive";
				$result = trim($result);
				$banner_output = str_replace('class="'.$regs[1].'"', 'class="'.$result.'"', $banner_output);	    
			} else {
				$result = '';
				if($responsive == 'Y') $result .= " responsive";
				$result = trim($result);
				if(strlen($result) > 0) {
					$banner_output = str_replace('<img ', '<img class="'.$result.'" ', $banner_output);
				}
			}
			unset($value, $regs, $result);
		}
	}
	unset($matches);

	$banner_output = str_replace('%title%', $name, $banner_output);		
	$banner_output = str_replace('%random%', rand(100000,999999), $banner_output);
	$banner_output = str_replace('%image%', $image, $banner_output);
	$banner_output = str_replace('%id%', $id, $banner_output);
	$banner_output = do_shortcode($banner_output);

	return $banner_output;
}

/*-------------------------------------------------------------
 Name:      adrotate_custom_scripts

 Purpose:   Add required scripts to site head
 Receive:   -None-
 Return:	-None-
 Since:		3.6
-------------------------------------------------------------*/
function adrotate_custom_scripts() {
	global $adrotate_config;
	
	$in_footer = false;
	if($adrotate_config['jsfooter'] == "Y") {
		$in_footer = true;
	}
	
	if($adrotate_config['jquery'] == 'Y') wp_enqueue_script('jquery', false, false, false, $in_footer);
	if(get_option('adrotate_dynamic_required') > 0) wp_enqueue_script('jshowoff-adrotate', plugins_url('/library/jquery.adrotate.dyngroup.js', __FILE__), false, null, $in_footer);
	if(get_option('adrotate_responsive_required') > 0) wp_enqueue_script('responsive-adrotate', plugins_url('/library/jquery.adrotate.responsive.js', __FILE__), false, null, $in_footer);

	// Make clicktracking and impression tracking a possibility
	if($adrotate_config['stats'] == 1){
		wp_enqueue_script('clicktrack-adrotate', plugins_url('/library/jquery.adrotate.clicktracker.js', __FILE__), false, null, $in_footer);
		wp_localize_script('jshowoff-adrotate', 'impression_object', array('ajax_url' => admin_url( 'admin-ajax.php')));
		wp_localize_script('clicktrack-adrotate', 'click_object', array('ajax_url' => admin_url('admin-ajax.php')));
	}

	if(!$in_footer) {
		add_action('wp_head', 'adrotate_custom_javascript');
	} else {
		add_action('wp_footer', 'adrotate_custom_javascript', 100);
	}
}

/*-------------------------------------------------------------
 Name:      adrotate_custom_javascript

 Purpose:   Add required JavaScript to site
 Receive:   -None-
 Return:	-None-
 Since:		3.10.5
-------------------------------------------------------------*/
function adrotate_custom_javascript() {
	global $wpdb, $adrotate_config;

	$groups = $wpdb->get_results("SELECT `id`, `adspeed` FROM `{$wpdb->prefix}adrotate_groups` WHERE `name` != '' AND `modus` = 1 ORDER BY `id` ASC;");
	if($groups) {
		$output_js = "jQuery(document).ready(function(){\n";
		$output_js .= "if(jQuery.fn.gslider) {\n";
		foreach($groups as $group) {
			$output_js .= "\tjQuery('.g-".$group->id."').gslider({ groupid: ".$group->id.", speed: ".$group->adspeed." });\n";
		}
		$output_js .= "}\n";
		$output_js .= "});\n";
		unset($groups);
	}

	$output = "<!-- AdRotate JS -->\n";
	$output .= "<script type=\"text/javascript\">\n";

	if(($adrotate_config['adblock'] == 'Y' AND !is_user_logged_in()) OR ($adrotate_config['adblock'] == 'Y' AND $adrotate_config['adblock_loggedin'] == "Y" AND is_user_logged_in())) {
		$output .= "jQuery(document).ready(function() {\n";
		$output .= "\tjQuery('body').adblockdetect({time: ".$adrotate_config['adblock_timer'].", message: \"".$adrotate_config['adblock_message']."\"});\n";
		$output .= "});\n";
	}

	if(isset($output_js)) {
		$output .= $output_js;
		unset($output_js);
	}
	$output .= "</script>\n";
	$output .= "<!-- /AdRotate JS -->\n\n";

	echo $output;
}

/*-------------------------------------------------------------
 Name:      adrotate_custom_css

 Purpose:   Add required CSS to site head
 Receive:   -None-
 Return:	-None-
 Since:		3.8
-------------------------------------------------------------*/
function adrotate_custom_css() {
	global $wpdb, $adrotate_config;
	
	$output = "\n<!-- This site is using AdRotate v".ADROTATE_DISPLAY." to display their advertisements - https://ajdg.solutions/products/adrotate-for-wordpress/ -->\n";

	$groups = $wpdb->get_results("SELECT `id`, `modus`, `gridrows`, `gridcolumns`, `adwidth`, `adheight`, `admargin`, `align` FROM `{$wpdb->prefix}adrotate_groups` WHERE `name` != '' ORDER BY `id` ASC;");
	if($groups) {
		$output_css = "\t.g { margin:0px; padding:0px; overflow:hidden; line-height:1; zoom:1; }\n";
		$output_css .= "\t.g img { height:auto; }\n";
		$output_css .= "\t.g-col { position:relative; float:left; }\n";
		$output_css .= "\t.g-col:first-child { margin-left: 0; }\n";
		$output_css .= "\t.g-col:last-child { margin-right: 0; }\n";

		foreach($groups as $group) {
			if($group->align == 0) { // None
				$group_align = '';
			} else if($group->align == 1) { // Left
				$group_align = ' float:left; clear:left;';
			} else if($group->align == 2) { // Right
				$group_align = ' float:right; clear:right;';
			} else if($group->align == 3) { // Center
				$group_align = ' margin: 0 auto;';
			}

			if($group->modus == 0 AND ($group->admargin > 0 OR $group->align > 0)) { // Single ad group
				if($group->align < 3) {
					$output_css .= "\t.g-".$group->id." { margin:".$group->admargin."px;".$group_align." }\n";
				} else {
					$output_css .= "\t.g-".$group->id." { ".$group_align." }\n";	
				}
			}
	
			if($group->modus == 1) { // Dynamic group
				if($group->adwidth != 'auto') {
					$width = "width:100%; max-width:".$group->adwidth."px;";
				} else {
					$width = "width:auto;";
				}
				
				if($group->adheight != 'auto') {
					$height = "height:100%; max-height:".$group->adheight."px;";
				} else {
					$height = "height:auto;";
				}

				if($group->align < 3) {
					$output_css .= "\t.g-".$group->id." { margin:".$group->admargin."px;".$width." ".$height.$group_align." }\n";
				} else {
					$output_css .= "\t.g-".$group->id." { ".$width." ".$height.$group_align." }\n";	
				}

				unset($width_sum, $width, $height_sum, $height);
			}
	
			if($group->modus == 2) { // Block group
				if($group->adwidth != 'auto') {
					$width_sum = $group->gridcolumns * ($group->admargin + $group->adwidth + $group->admargin);
					$grid_width = "min-width:".$group->admargin."px; max-width:".$width_sum."px;";
				} else {
					$grid_width = "width:auto;";
				}
				
				$output_css .= "\t.g-".$group->id." { ".$grid_width.$group_align." }\n";
				$output_css .= "\t.b-".$group->id." { margin:".$group->admargin."px; }\n";
				unset($width_sum, $grid_width, $height_sum, $grid_height);
			}
		}
		$output_css .= "\t@media only screen and (max-width: 480px) {\n";
		$output_css .= "\t\t.g-col, .g-dyn, .g-single { width:100%; margin-left:0; margin-right:0; }\n";
		$output_css .= "\t}\n";
		unset($groups);
	}

	if(isset($output_css) OR $adrotate_config['widgetpadding'] == "Y") {
		$output .= "<!-- AdRotate CSS -->\n";
		$output .= "<style type=\"text/css\" media=\"screen\">\n";
		if(isset($output_css)) {
			$output .= $output_css;
			unset($output_css);
		}
		if($adrotate_config['widgetpadding'] == "Y") { 
			$output .= ".widget_adrotate_widgets { overflow:hidden; padding:0; }\n"; 
		}
		$output .= "</style>\n";
		$output .= "<!-- /AdRotate CSS -->\n\n";
	}

	echo $output;
}

/*-------------------------------------------------------------
 Name:      adrotate_nonce_error

 Purpose:   Display a formatted error if Nonce fails
 Receive:   -none-
 Return:    -none-
 Since:		3.7.4.2
-------------------------------------------------------------*/
function adrotate_nonce_error() {
	echo '	<h2 style="text-align: center;">'.__('Oh no! Something went wrong!', 'adrotate').'</h2>';
	echo '	<p style="text-align: center;">'.__('WordPress was unable to verify the authenticity of the url you have clicked. Verify if the url used is valid or log in via your browser.', 'adrotate').'</p>';
	echo '	<p style="text-align: center;">'.__('If you have received the url you want to visit via email, you are being tricked!', 'adrotate').'</p>';
	echo '	<p style="text-align: center;">'.__('Contact support if the issue persists:', 'adrotate').' <a href="https://ajdg.solutions/forums/pk_campaign=adrotatefree-nonceerror&pk_kwd=forum" title="AdRotate Support" target="_blank">AJdG Solutions Support</a>.</p>';
}

/*-------------------------------------------------------------
 Name:      adrotate_error

 Purpose:   Show errors for problems in using AdRotate, should they occur
 Receive:   $action, $arg
 Return:    -none-
 Since:		3.0
-------------------------------------------------------------*/
function adrotate_error($action, $arg = null) {
	global $adrotate_debug;

	switch($action) {
		// Ads
		case "ad_expired" :
			if($adrotate_debug['general'] == true) {
				$result = '<span style="font-weight: bold; color: #f00;">'.__('Error, Ad is not available at this time due to schedule/geolocation restrictions or does not exist!', 'adrotate').'</span>';
			} else {
				$result = '<!-- '.__('Error, Ad is not available at this time due to schedule/geolocation restrictions!', 'adrotate').' -->';
			}
			return $result;
		break;
		
		case "ad_unqualified" :
			if($adrotate_debug['general'] == true) {
				$result = '<span style="font-weight: bold; color: #f00;">'.__('Either there are no banners, they are disabled or none qualified for this location!', 'adrotate').'</span>';
			} else {
				$result = '<!-- '.__('Either there are no banners, they are disabled or none qualified for this location!', 'adrotate').' -->';
			}
			return $result;
		break;
		
		case "ad_no_id" :
			$result = '<span style="font-weight: bold; color: #f00;">'.__('Error, no Ad ID set! Check your syntax!', 'adrotate').'</span>';
			return $result;
		break;

		// Groups
		case "group_no_id" :
			$result = '<span style="font-weight: bold; color: #f00;">'.__('Error, no group ID set! Check your syntax!', 'adrotate').'</span>';
			return $result;
		break;

		case "group_not_found" :
			$result = '<span style="font-weight: bold; color: #f00;">'.__('Error, group does not exist! Check your syntax!', 'adrotate').' (ID: '.$arg[0].')</span>';
			return $result;
		break;

		// Database
		case "db_error" :
			$result = '<span style="font-weight: bold; color: #f00;">'.__('There was an error locating the database tables for AdRotate. Please deactivate and re-activate AdRotate from the plugin page!!', 'adrotate').'<br />'.__('If this does not solve the issue please seek support at', 'adrotate').' <a href="https://ajdg.solutions/forums/forum/adrotate-for-wordpress/?pk_campaign=adrotatefree-databaseerror&pk_kwd=forum">ajdg.solutions/forums/forum/adrotate-for-wordpress/</a></span>';
			return $result;
		break;

		// Misc
		default:
			$result = '<span style="font-weight: bold; color: #f00;">'.__('An unknown error occured.', 'adrotate').' (ID: '.$arg[0].')</span>';
			return $result;
		break;
	}
}

/*-------------------------------------------------------------
 Name:      adrotate_notifications_dashboard

 Purpose:   Notify user of expired banners in the dashboard
 Receive:   -none-
 Return:    -none-
 Since:		3.0
-------------------------------------------------------------*/
function adrotate_notifications_dashboard() {
	global $adrotate_advert_status;
	if(current_user_can('adrotate_ad_manage')) {
		if(!is_array($adrotate_advert_status)) {
			$data = unserialize($adrotate_advert_status);
		} else {
			$data = $adrotate_advert_status;
		}

		if($data['total'] > 0) {
			if($data['expired'] > 0 AND $data['expiressoon'] == 0 AND $data['error'] == 0) {
				echo '<div class="error"><p>'.$data['expired'].' '.__('active ad(s) expired.', 'adrotate').' <a href="admin.php?page=adrotate-ads">'.__('Take action now', 'adrotate').'</a>!</p></div>';
			} else if($data['expired'] == 0 AND $data['expiressoon'] > 0 AND $data['error'] == 0) {
				echo '<div class="error"><p>'.$data['expiressoon'].' '.__('active ad(s) are about to expire.', 'adrotate').' <a href="admin.php?page=adrotate-ads">'.__('Check it out', 'adrotate').'</a>!</p></div>';
			} else if($data['expired'] == 0 AND $data['expiressoon'] == 0 AND $data['error'] > 0) {
				echo '<div class="error"><p>There are '.$data['error'].' '.__('active ad(s) with configuration errors.', 'adrotate').' <a href="admin.php?page=adrotate-ads">'.__('Solve this', 'adrotate').'</a>!</p></div>';
			} else {
				echo '<div class="error"><p>'.$data['expired'].' '.__('ad(s) expired.', 'adrotate').' '.$data['expiressoon'].' '.__('ad(s) are about to expire.', 'adrotate').' There are '.$data['error'].' '.__('ad(s) with configuration errors.', 'adrotate').' <a href="admin.php?page=adrotate-ads">'.__('Fix this as soon as possible', 'adrotate').'</a>!</p></div>';
			}
		}

		if(isset($_GET['hide']) AND $_GET['hide'] == 1) update_option('adrotate_hide_banner', 1);
		if(isset($_GET['hide']) AND $_GET['hide'] == 2) update_option('adrotate_hide_review', 1);
		if(isset($_GET['page'])) { $page = $_GET['page']; } else { $page = ''; }

		$pro_banner = get_option('adrotate_hide_banner');
		if($pro_banner != 1 AND $pro_banner < (adrotate_now() - 604800) AND strpos($page, 'adrotate') !== false) {
			echo '<div class="updated" style="padding: 0; margin: 0; border-left: none;">';
			echo '	<div class="adrotate_banner">';
			echo '		<div class="button_div"><a class="button" target="_blank" href="https://ajdg.solutions/products/adrotate-for-wordpress/?add-to-cart=1126?pk_campaign=adrotatefree-upgradebanner">'.__('Buy now', 'adrotate').'</a></div>';
			echo '		<div class="text">'.__("You've been using <strong>AdRotate</strong> for a while now. Why not upgrade to the <strong>PRO</strong> version", 'adrotate').'?<br /><span>'.__('Use discount code <b>getadrotatepro</b> for 10% off on any AdRotate license!', 'adrotate' ).' '.__('Thank you for your purchase!', 'adrotate' ).'</span></div>';
			echo '		<a class="close_banner" href="admin.php?page=adrotate-pro&hide=1"><img title="Close" src="'.plugins_url('images/icon-close.png', __FILE__).'" alt=""/></a>';
			echo '		<div class="icon"><img title="" src="'.plugins_url('images/logo-60x60.png', __FILE__).'" alt=""/></div>';
			echo '	</div>';
			echo '</div>';
		}

		$review_banner = get_option('adrotate_hide_review');
		if($review_banner != 1 AND $review_banner < (adrotate_now() - 2419200) AND strpos($page, 'adrotate') !== false) {
			echo '<div class="updated" style="padding: 0; margin: 0; border-left: none;">';
			echo '	<div class="adrotate_banner">';
			echo '		<div class="button_div"><a class="button" target="_blank" href="https://wordpress.org/support/view/plugin-reviews/adrotate?rate=5#postform">Rate AdRotate</a></div>';
			echo '		<div class="text">If you like <strong>AdRotate</strong> please let the world know that you do. Thanks for your support!<br /><span>If you have questions, suggestions or something else that doesn\'t belong in a review, please <a href="https://ajdg.solutions/forums/forum/adrotate-for-wordpress/?pk_campaign=adrotatefree-reviewbanner" target="_blank">get in touch</a>!</span></div>';
			echo '		<a class="close_banner" href="admin.php?page=adrotate&hide=2"><img title="Close" src="'.plugins_url('images/icon-close.png', __FILE__).'" alt=""/></a>';
			echo '		<div class="icon"><img title="" src="'.plugins_url('images/logo-60x60.png', __FILE__).'" alt=""/></div>';
			echo '	</div>';
			echo '</div>';
		}
	}

	if(isset($_GET['upgrade']) AND $_GET['upgrade'] == 1) adrotate_check_upgrade();
	$adrotate_db_version = get_option("adrotate_db_version");
	$adrotate_version = get_option("adrotate_version");
	if($adrotate_db_version['current'] < ADROTATE_DB_VERSION OR $adrotate_version['current'] < ADROTATE_VERSION) {
		echo '<div class="updated" style="padding: 0; margin: 0; border-left: none;">';
		echo '	<div class="adrotate_banner">';
		echo '		<div class="button_div"><a class="button" href="admin.php?page=adrotate&upgrade=1">Update Now</a></div>';
		echo '		<div class="text">You have almost finished upgrading <strong>AdRotate</strong> to version <strong>'.ADROTATE_DISPLAY.'</strong>!<br /><span>To complete the update click the button on the left. This may take a few seconds to complete!</span></div>';
		echo '		<div class="icon"><img title="" src="'.plugins_url('images/logo-60x60.png', __FILE__).'" alt=""/></div>';
		echo '	</div>';
		echo '</div>';
	}
}

/*-------------------------------------------------------------
 Name:      adrotate_welcome_pointer

 Purpose:   Show dashboard pointers
 Receive:   -none-
 Return:    -none-
 Since:		3.9.14
-------------------------------------------------------------*/
function adrotate_welcome_pointer() {
    $pointer_content = '<h3>AdRotate '.ADROTATE_DISPLAY.'</h3>';
    $pointer_content .= '<p>'.__('Welcome, and thanks for using AdRotate. Everything related to AdRotate is in this menu. Check out the', 'adrotate').' <a href="http:\/\/ajdg.solutions\/manuals\/adrotate-manuals\/" target="_blank">'.__('manuals', 'adrotate').'</a> '.__('and', 'adrotate').' <a href="https:\/\/ajdg.solutions\/forums\/forum\/adrotate-for-wordpress\/" target="_blank">'.__('forums', 'adrotate').'</a>.</p>';
    $pointer_content .= '<p><strong>AdRotate Professional</strong><br />Did you know there is also a premium version of AdRotate? benefit from many <a href="admin.php?page=adrotate-pro">extra features</a>.</p>';
?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#toplevel_page_adrotate').pointer({
				'content':'<?php echo $pointer_content; ?>',
				'position':{ 'edge':'left', 'align':'middle' },
				close: function() {
	                $.post(ajaxurl, {
		                pointer:'adrotatefree_'+<?php echo ADROTATE_VERSION.ADROTATE_DB_VERSION; ?>, 
		                action:'dismiss-wp-pointer'
					});
				}
			}).pointer("open");
		});
	</script>
<?php
}

/*-------------------------------------------------------------
 Name:      adrotate_help_info

 Purpose:   Help tab on all pages
 Receive:   -none-
 Return:    -none-
 Since:		3.10.17
-------------------------------------------------------------*/
function adrotate_help_info() {
    $screen = get_current_screen();

    $screen->add_help_tab(array(
        'id' => 'adrotate_useful_links',
        'title' => __('Useful Links'),
        'content' => '<h4>'.__('Useful links to learn more about AdRotate', 'adrotate').'</h4>'.
			'<ul>'.
			'<li><a href="https://ajdg.solutions/products/adrotate-for-wordpress/?pk_campaign=adrotatefree-helptab" target="_blank">'.__('AdRotate website', 'adrotate').'</a>.</li>'.
			'<li><a href="https://ajdg.solutions/manuals/adrotate-manuals/getting-started-with-adrotate/?pk_campaign=adrotatefree-helptab" target="_blank">'.__('Getting Started With AdRotate', 'adrotate').'</a>.</li>'.
			'<li><a href="https://ajdg.solutions/manuals/adrotate-manuals/?pk_campaign=adrotatefree-helptab" target="_blank">'.__('AdRotate manuals', 'adrotate').'</a>.</li>'.
			'<li><a href="https://ajdg.solutions/forums/forum/adrotate-for-wordpress/?pk_campaign=adrotatefree-helptab" target="_blank">'.__('AdRotate Support Forum', 'adrotate').'</a>.</li>'.
			'</ul>'
		) 
    );
    $screen->add_help_tab(array(
        'id' => 'adrotate_thanks',
        'title' => 'Thank You',
        'content' => '<h4>Thank you for using AdRotate</h4><p>AdRotate is growing to be one of the most popular WordPress plugins for Advertising and is a household name for many companies around the world. AdRotate wouldn\'t be possible without your support and my life wouldn\'t be what it is today without your help.</p><p><em>- Arnan</em></p>'.
        '<p><strong>Add me:</strong> <a href="http://twitter.com/arnandegans/" target="_blank">Twitter</a>, <a href="https://www.facebook.com/Arnandegans/" target="_blank">Facebook</a>. <strong>Business:</strong> <a href="https://ajdg.solutions/?pk_campaign=adrotatefree-helptab" target="_blank">ajdg.solutions</a> <strong>Blog:</strong> <a href="http://meandmymac.net/?pk_campaign=adrotatefree-helptab" target="_blank">meandmymac.net</a>.</p>'
		)
    );
}

/*-------------------------------------------------------------
 Name:      adrotate_credits

 Purpose:   Promotional stuff shown throughout the plugin
 Receive:   -none-
 Return:    -none-
 Since:		3.7
-------------------------------------------------------------*/
function adrotate_credits() {
	echo '<table class="widefat" style="margin-top: .5em">';

	echo '<thead>';
	echo '<tr valign="top">';
	echo '	<th colspan="2">'.__('Help AdRotate Grow', 'adrotate').'</th>';
	echo '	<th style="text-align:center;">'.__('Follow Arnan on Facebook', 'wpevents').'</th>';
	echo '</tr>';
	echo '</thead>';

	echo '<tbody>';
	echo '<tr>';
	echo '<td><center><a href="https://ajdg.solutions/products/adrotate-for-wordpress/?pk_campaign=adrotatefree-credits&pk_kwd=adrotate_logo" title="AdRotate plugin for WordPress"><img src="'.plugins_url('/images/logo-60x60.png', __FILE__).'" alt="AdRotate logo" width="60" height="60" /></a></center></td>';
	echo '<td>'.__("A lot of users only think to review AdRotate when something goes wrong while thousands of people use AdRotate satisfactory. Don't let this go unnoticed.", 'adrotate').' <strong>'. __("If you find AdRotate useful please leave your honest", 'adrotate').' <a href="https://wordpress.org/support/view/plugin-reviews/adrotate?rate=5#postform" target="_blank">'.__('rating','adrotate').'</a> '.__('and','adrotate').' <a href="https://wordpress.org/support/view/plugin-reviews/adrotate" target="_blank">'.__('review','adrotate').'</a> '.__('on WordPress.org to help AdRotate grow in a positive way', 'adrotate').'!</strong></td>';

	echo '<td width="25%"><script>(function(d, s, id) {';
	echo 'var js, fjs = d.getElementsByTagName(s)[0];';
	echo 'if (d.getElementById(id)) return;';
	echo 'js = d.createElement(s); js.id = id;';
	echo 'js.src = "https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5";';
	echo 'fjs.parentNode.insertBefore(js, fjs);';
	echo '}(document, \'script\', \'facebook-jssdk\'));</script>';
	echo '<center><div class="fb-page" data-href="https://www.facebook.com/Arnandegans" data-width="315" data-adapt-container-width="true" data-small-header="true" data-hide-cover="false" data-show-facepile="false"></div></center></td>';
	echo '</tr>';
	echo '</tbody>';

	echo '</table>';
	echo adrotate_trademark();
}

/*-------------------------------------------------------------
 Name:      adrotate_trademark
 
 Purpose:   Trademark notice
 Receive:   -none-
 Return:    -none-
 Since:		3.9.14
-------------------------------------------------------------*/
function adrotate_trademark() {
	return '<center><small>AdRotate<sup>&reg;</sup> is a registered trademark.</small></center>';
}

/*-------------------------------------------------------------
 Name:      adrotate_pro_notice
 
 Purpose:   Credits shown on user statistics
 Receive:   $d
 Return:    -none-
 Since:		3.8
-------------------------------------------------------------*/
function adrotate_pro_notice($d = '') {

	if($d == "t") echo __('Available in AdRotate Pro', 'adrotate').'. <a href="admin.php?page=adrotate-pro">'.__('More information...', 'adrotate').'</a>';
	else echo __('This feature is available in AdRotate Pro', 'adrotate').'. <a href="admin.php?page=adrotate-pro">'.__('Learn more', 'adrotate').'</a>!';
}
?>
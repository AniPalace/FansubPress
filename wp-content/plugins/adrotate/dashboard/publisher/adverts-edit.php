<?php
/* ------------------------------------------------------------------------------------
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2008-2015 Arnan de Gans. All Rights Reserved.
*  ADROTATE is a trademark of Arnan de Gans.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from it's use.
------------------------------------------------------------------------------------ */

if(!$ad_edit_id) {
	$edit_id = $wpdb->get_var("SELECT `id` FROM `{$wpdb->prefix}adrotate` WHERE `type` = 'empty' ORDER BY `id` DESC LIMIT 1;");
	if($edit_id == 0) {
	    $wpdb->insert($wpdb->prefix."adrotate", array('title' => '', 'bannercode' => '', 'thetime' => $now, 'updated' => $now, 'author' => $current_user->user_login, 'imagetype' => 'dropdown', 'image' => '', 'tracker' => 'N', 'desktop' => 'Y', 'mobile' => 'Y', 'tablet' => 'Y', 'responsive' => 'N', 'type' => 'empty', 'weight' => 6, 'sortorder' => 0, 'budget' => 0, 'crate' => 0, 'irate' => 0, 'cities' => serialize(array()), 'countries' => serialize(array())));
	    $edit_id = $wpdb->insert_id;

		$wpdb->insert($wpdb->prefix.'adrotate_schedule', array('name' => 'Schedule for ad '.$edit_id, 'starttime' => $now, 'stoptime' => $in84days, 'maxclicks' => 0, 'maximpressions' => 0, 'spread' => 'N', 'dayimpressions' => 0, 'daystarttime' => '0000', 'daystoptime' => '0000', 'day_mon' => 'Y', 'day_tue' => 'Y', 'day_wed' => 'Y', 'day_thu' => 'Y', 'day_fri' => 'Y', 'day_sat' => 'Y', 'day_sun' => 'Y'));

	    $schedule_id = $wpdb->insert_id;
		$wpdb->insert($wpdb->prefix.'adrotate_linkmeta', array('ad' => $edit_id, 'group' => 0, 'user' => 0, 'schedule' => $schedule_id));
	}
	$ad_edit_id = $edit_id;
}

$edit_banner = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}adrotate` WHERE `id` = '$ad_edit_id';");
$groups	= $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}adrotate_groups` WHERE `name` != '' ORDER BY `sortorder` ASC, `id` ASC;"); 
$schedules = $wpdb->get_row("SELECT `{$wpdb->prefix}adrotate_schedule`.`id`, `starttime`, `stoptime`, `maxclicks`, `maximpressions` FROM `{$wpdb->prefix}adrotate_schedule`, `{$wpdb->prefix}adrotate_linkmeta` WHERE `ad` = $edit_banner->id AND `group` = 0 AND `user` = 0 AND `schedule` = `{$wpdb->prefix}adrotate_schedule`.`id` ORDER BY `{$wpdb->prefix}adrotate_schedule`.`id` ASC LIMIT 1;");
$linkmeta = $wpdb->get_results("SELECT `group` FROM `{$wpdb->prefix}adrotate_linkmeta` WHERE `ad` = '$edit_banner->id' AND `user` = 0 AND `schedule` = 0;");

wp_enqueue_media();
wp_enqueue_script('uploader-hook', plugins_url().'/adrotate/library/uploader-hook.js', array('jquery'));

list($sday, $smonth, $syear, $shour, $sminute) = explode(" ", date("d m Y H i", $schedules->starttime));
list($eday, $emonth, $eyear, $ehour, $eminute) = explode(" ", date("d m Y H i", $schedules->stoptime));

$meta_array = '';
foreach($linkmeta as $meta) {
	$meta_array[] = $meta->group;
}

if(!is_array($meta_array)) $meta_array = array();

if($ad_edit_id) {
	if($edit_banner->type != 'empty') {
		// Errors
		if(strlen($edit_banner->bannercode) < 1 AND $edit_banner->type != 'empty') 
			echo '<div class="error"><p>'. __('The AdCode cannot be empty!', 'adrotate').'</p></div>';

		if(!preg_match("/%image%/i", $edit_banner->bannercode) AND $edit_banner->image != '') 
			echo '<div class="error"><p>'. __('You did not use %image% in your AdCode but did select a file to use!', 'adrotate').'</p></div>';

		if(preg_match("/%image%/i", $edit_banner->bannercode) AND $edit_banner->image == '') 
			echo '<div class="error"><p>'. __('You did use %image% in your AdCode but did not select a file to use!', 'adrotate').'</p></div>';
		
		if(!preg_match("/%image%/i", $edit_banner->bannercode) AND $edit_banner->responsive == 'Y') 
			echo '<div class="error"><p>'. __('You did not use %image% in your AdCode. The responsive checkbox will be ineffective.', 'adrotate').'</p></div>';
		
		if((($edit_banner->imagetype != '' AND $edit_banner->image == '') OR ($edit_banner->imagetype == '' AND $edit_banner->image != ''))) 
			echo '<div class="error"><p>'. __('There is a problem saving the image. Please reset your image and re-save the ad!', 'adrotate').'</p></div>';

		if(strlen($edit_banner->image) > 0 AND !preg_match("/full/", $edit_banner->image) AND $edit_banner->responsive == 'Y') 
			echo '<div class="error"><p>'. __('Responsive is enabled but your banner image has the wrong name.', 'adrotate').'</p></div>';

		if(!preg_match_all('/<(a|script|embed|iframe)[^>](.*?)>/i', stripslashes(htmlspecialchars_decode($edit_banner->bannercode, ENT_QUOTES)), $things) AND $edit_banner->tracker == 'Y')
			echo '<div class="error"><p>'. __("Tracking is enabled but no valid link/tag was found in the adcode!", 'adrotate').'</p></div>';

		// Ad Notices
		$adstate = adrotate_evaluate_ad($edit_banner->id);
		if($edit_banner->type == 'error' AND $adstate == 'active')
			echo '<div class="error"><p>'. __('AdRotate cannot find an error but the ad is marked erroneous, try re-saving the ad!', 'adrotate').'</p></div>';

		if($adstate == 'expired')
			echo '<div class="error"><p>'. __('This ad is expired and currently not shown on your website!', 'adrotate').'</p></div>';

		if($adstate == '2days')
			echo '<div class="updated"><p>'. __('The ad will expire in less than 2 days!', 'adrotate').'</p></div>';

		if($adstate == '7days')
			echo '<div class="updated"><p>'. __('This ad will expire in less than 7 days!', 'adrotate').'</p></div>';

		if($edit_banner->type == 'disabled') 
			echo '<div class="updated"><p>'. __('This ad has been disabled and does not rotate on your site!', 'adrotate').'</p></div>';
	}
}

// Determine image field
if($edit_banner->imagetype == "field") {
	$image_field = $edit_banner->image;
	$image_dropdown = '';
} else if($edit_banner->imagetype == "dropdown") {
	$image_field = '';
	$image_dropdown = $edit_banner->image;
} else {
	$image_field = '';
	$image_dropdown = '';
}		
?>

	<form method="post" action="admin.php?page=adrotate-ads">
	<?php wp_nonce_field('adrotate_save_ad','adrotate_nonce'); ?>
	<input type="hidden" name="adrotate_username" value="<?php echo $userdata->user_login;?>" />
	<input type="hidden" name="adrotate_id" value="<?php echo $edit_banner->id;?>" />
	<input type="hidden" name="adrotate_type" value="<?php echo $edit_banner->type;?>" />
	<input type="hidden" name="adrotate_schedule" value="<?php echo $schedules->id;?>" />

	<?php if($edit_banner->type == 'empty') { ?>
		<h3><?php _e('New Advert', 'adrotate'); ?></h3>
	<?php } else { ?> 
		<h3><?php _e('Edit Advert', 'adrotate'); ?></h3>
	<?php } ?>

	<table class="widefat" style="margin-top: .5em">
		<tbody>
      	<tr>
	        <th width="15%"><?php _e('Title', 'adrotate'); ?></th>
	        <td colspan="2">
	        	<label for="adrotate_title"><input tabindex="1" name="adrotate_title" type="text" size="50" class="search-input" value="<?php echo stripslashes($edit_banner->title);?>" autocomplete="off" /></label>
	        </td>
      	</tr>
      	<tr>
	        <th valign="top"><?php _e('AdCode', 'adrotate'); ?></th>
	        <td>
	        	<label for="adrotate_bannercode"><textarea tabindex="2" id="adrotate_bannercode" name="adrotate_bannercode" cols="65" rows="10"><?php echo stripslashes($edit_banner->bannercode); ?></textarea></label>
	        </td>
	        <td width="30%">
		        <p><strong><?php _e('Basic Examples:', 'adrotate'); ?></strong></p>
				<p><em><a href="#" onclick="textatcursor('adrotate_bannercode','&lt;a href=&quot;http://www.adrotateforwordpress.com&quot;&gt;&lt;img src=&quot;%image%&quot; /&gt;&lt;/a&gt;');return false;">&lt;a href="http://www.adrotateforwordpress.com"&gt;&lt;img src="%image%" /&gt;&lt;/a&gt;</a></em></p>
		        <p><em><a href="#" onclick="textatcursor('adrotate_bannercode','&lt;span class=&quot;ad-%id%&quot;&gt;&lt;a href=&quot;http://www.adrotateforwordpress.com&quot;&gt;Text Link Ad!&lt;/a&gt;&lt;/span&gt;');return false;">&lt;span class="ad-%id%"&gt;&lt;a href="http://www.adrotateforwordpress.com"&gt;Text Link Ad!&lt;/a&gt;&lt;/span&gt;</a></em></p>
		        <p><em><a href="#" onclick="textatcursor('adrotate_bannercode','&lt;iframe src=&quot;%image%&quot; height=&quot;250&quot; frameborder=&quot;0&quot; style=&quot;border:none;&quot;&gt;&lt;/iframe&gt;');return false;">&lt;iframe src=&quot;%image%&quot; height=&quot;250&quot; frameborder=&quot;0&quot; style=&quot;border:none;&quot;&gt;&lt;/iframe&gt;</a></em></p>
	        </td>
      	</tr>
      	<tr>
	        <th valign="top"><?php _e('Useful tags', 'adrotate'); ?></th>
	        <td colspan="2">
		        <p><em><a href="#" title="<?php _e('Insert the advert ID Number.', 'adrotate'); ?>" onclick="textatcursor('adrotate_bannercode','%id%');return false;">%id%</a>, <a href="#" title="<?php _e('Required when selecting a image below.', 'adrotate'); ?>" onclick="textatcursor('adrotate_bannercode','%image%');return false;">%image%</a>, <a href="#" title="<?php _e('Insert the advert name.', 'adrotate'); ?>" onclick="textatcursor('adrotate_bannercode','%title%');return false;">%title%</a>, <a href="#" title="<?php _e('Insert a random seed. Useful for DFP/DoubleClick type adverts.', 'adrotate'); ?>" onclick="textatcursor('adrotate_bannercode','%random%');return false;">%random%</a>, <a href="#" title="<?php _e('Add inside the <a> tag to open advert in a new window.', 'adrotate'); ?>" onclick="textatcursor('adrotate_bannercode','target=&quot;_blank&quot;');return false;">target="_blank"</a>, <a href="#" title="<?php _e('Add inside the <a> tag to tell crawlers to ignore this link', 'adrotate'); ?>" onclick="textatcursor('adrotate_bannercode','rel=&quot;nofollow&quot;');return false;">rel="nofollow"</a></em><br /><?php _e('Place the cursor in your AdCode where you want to add any of these tags and click to add it.', 'adrotate'); ?></p>
	        </td>
      	</tr>
	  	<?php if($edit_banner->type != 'empty') { ?>
     	<tr>
	        <th valign="top"><?php _e('Preview', 'adrotate'); ?></th>
	        <td colspan="2">
	        	<div><?php echo adrotate_preview($edit_banner->id); ?></div>
		        <br /><em><?php _e('Note: While this preview is an accurate one, it might look different then it does on the website.', 'adrotate'); ?>
				<br /><?php _e('This is because of CSS differences. Your themes CSS file is not active here!', 'adrotate'); ?></em>
			</td>
      	</tr>
		<?php } ?>
		<tr>
	        <th valign="top"><?php _e('Banner asset', 'adrotate'); ?></th>
			<td colspan="2">
				<label for="adrotate_image">
					<?php _e('WordPress media:', 'adrotate'); ?> <input tabindex="3" id="adrotate_image" type="text" size="50" name="adrotate_image" value="<?php echo $image_field; ?>" /> <input tabindex="4" id="adrotate_image_button" class="button" type="button" value="<?php _e('Select Banner', 'adrotate'); ?>" />
				</label><br />
				<?php _e('- OR -', 'adrotate'); ?><br />
				<label for="adrotate_image_dropdown">
					<?php _e('Banner folder:', 'adrotate'); ?> <select tabindex="5" name="adrotate_image_dropdown" style="min-width: 200px;">
   						<option value=""><?php _e('No image selected', 'adrotate'); ?></option>
						<?php echo adrotate_folder_contents($image_dropdown); ?>
					</select><br />
				</label>
				<em><?php _e('Use %image% in the adcode instead of the file path.', 'adrotate'); ?> <?php _e('Use either the text field or the dropdown. If the textfield has content that field has priority.', 'adrotate'); ?></em>
			</td>
		</tr>
		<?php if($adrotate_config['stats'] > 0) { ?>
      	<tr>
	        <th width="15%" valign="top"><?php _e('Statistics', 'adrotate'); ?></th>
	        <td colspan="2">
	        	<label for="adrotate_tracker"><input tabindex="6" type="checkbox" name="adrotate_tracker" <?php if($edit_banner->tracker == 'Y') { ?>checked="checked" <?php } ?> /> <?php _e('Enable click and impression tracking for this advert.', 'adrotate'); ?> <br />
	        	<em><?php _e('Note: Clicktracking does not work for Javascript adverts such as those provided by Google AdSense/DFP/DoubleClick. HTML5/Flash adverts are not always supported.', 'adrotate'); ?></em>
		        </label>
	        </td>
      	</tr>
		<?php } ?>
      	<tr>
	        <th><?php _e('Activate', 'adrotate'); ?></th>
	        <td colspan="2">
		        <label for="adrotate_active">
			        <select tabindex="7" name="adrotate_active">
						<option value="active" <?php if($edit_banner->type == "active") { echo 'selected'; } ?>><?php _e('Yes, this ad will be used', 'adrotate'); ?></option>
						<option value="disabled" <?php if($edit_banner->type == "disabled") { echo 'selected'; } ?>><?php _e('No, do not show this ad anywhere', 'adrotate'); ?></option>
					</select>
				</label>
			</td>
      	</tr>
		</tbody>
	</table>
	<center><?php _e('Get more features with AdRotate Pro.', 'adrotate'); ?> <a href="admin.php?page=adrotate-pro"><?php _e('More information', 'adrotate'); ?></a>.</center>

	<p class="submit">
		<input tabindex="8" type="submit" name="adrotate_ad_submit" class="button-primary" value="<?php _e('Save Advert', 'adrotate'); ?>" />
		<a href="admin.php?page=adrotate-ads&view=manage" class="button"><?php _e('Cancel', 'adrotate'); ?></a>
	</p>

	<h3><?php _e('Usage', 'adrotate'); ?></h3>
	<table class="widefat" style="margin-top: .5em">
		<tbody>
      	<tr>
	        <th width="15%"><?php _e('Widget', 'adrotate'); ?></th>
	        <td colspan="3"><?php _e('Drag the AdRotate widget to the sidebar you want it in, select "Single Ad" and enter ID', 'adrotate'); ?> "<?php echo $edit_banner->id; ?>".</td>
      	</tr>
      	<tr>
	        <th width="15%"><?php _e('In a post or page', 'adrotate'); ?></th>
	        <td>[adrotate banner="<?php echo $edit_banner->id; ?>"]</td>
	        <th width="15%"><?php _e('Directly in a theme', 'adrotate'); ?></th>
	        <td>&lt;?php echo adrotate_ad(<?php echo $edit_banner->id; ?>); ?&gt;</td>
      	</tr>
      	</tbody>
	</table>

	<h3><?php _e('Schedule your advert', 'adrotate'); ?></h3>
	<table class="widefat" style="margin-top: .5em">
		<tbody>
      	<tr>
	        <th width="15%"><?php _e('Start date (day/month/year)', 'adrotate'); ?></th>
	        <td>
	        	<label for="adrotate_sday">
	        	<input tabindex="9" name="adrotate_sday" class="search-input" type="text" size="4" maxlength="2" value="<?php echo $sday;?>" /> /
				<select tabindex="10" name="adrotate_smonth">
					<option value="01" <?php if($smonth == "01") { echo 'selected'; } ?>><?php _e('January', 'adrotate'); ?></option>
					<option value="02" <?php if($smonth == "02") { echo 'selected'; } ?>><?php _e('February', 'adrotate'); ?></option>
					<option value="03" <?php if($smonth == "03") { echo 'selected'; } ?>><?php _e('March', 'adrotate'); ?></option>
					<option value="04" <?php if($smonth == "04") { echo 'selected'; } ?>><?php _e('April', 'adrotate'); ?></option>
					<option value="05" <?php if($smonth == "05") { echo 'selected'; } ?>><?php _e('May', 'adrotate'); ?></option>
					<option value="06" <?php if($smonth == "06") { echo 'selected'; } ?>><?php _e('June', 'adrotate'); ?></option>
					<option value="07" <?php if($smonth == "07") { echo 'selected'; } ?>><?php _e('July', 'adrotate'); ?></option>
					<option value="08" <?php if($smonth == "08") { echo 'selected'; } ?>><?php _e('August', 'adrotate'); ?></option>
					<option value="09" <?php if($smonth == "09") { echo 'selected'; } ?>><?php _e('September', 'adrotate'); ?></option>
					<option value="10" <?php if($smonth == "10") { echo 'selected'; } ?>><?php _e('October', 'adrotate'); ?></option>
					<option value="11" <?php if($smonth == "11") { echo 'selected'; } ?>><?php _e('November', 'adrotate'); ?></option>
					<option value="12" <?php if($smonth == "12") { echo 'selected'; } ?>><?php _e('December', 'adrotate'); ?></option>
				</select> /
				<input tabindex="11" name="adrotate_syear" class="search-input" type="text" size="4" maxlength="4" value="<?php echo $syear;?>" />&nbsp;&nbsp;&nbsp; 
				</label>
	        </td>
	        <th width="15%"><?php _e('End date (day/month/year)', 'adrotate'); ?></th>
	        <td>
	        	<label for="adrotate_eday">
	        	<input tabindex="12" name="adrotate_eday" class="search-input" type="text" size="4" maxlength="2" value="<?php echo $eday;?>"  /> /
				<select tabindex="13" name="adrotate_emonth">
					<option value="01" <?php if($emonth == "01") { echo 'selected'; } ?>><?php _e('January', 'adrotate'); ?></option>
					<option value="02" <?php if($emonth == "02") { echo 'selected'; } ?>><?php _e('February', 'adrotate'); ?></option>
					<option value="03" <?php if($emonth == "03") { echo 'selected'; } ?>><?php _e('March', 'adrotate'); ?></option>
					<option value="04" <?php if($emonth == "04") { echo 'selected'; } ?>><?php _e('April', 'adrotate'); ?></option>
					<option value="05" <?php if($emonth == "05") { echo 'selected'; } ?>><?php _e('May', 'adrotate'); ?></option>
					<option value="06" <?php if($emonth == "06") { echo 'selected'; } ?>><?php _e('June', 'adrotate'); ?></option>
					<option value="07" <?php if($emonth == "07") { echo 'selected'; } ?>><?php _e('July', 'adrotate'); ?></option>
					<option value="08" <?php if($emonth == "08") { echo 'selected'; } ?>><?php _e('August', 'adrotate'); ?></option>
					<option value="09" <?php if($emonth == "09") { echo 'selected'; } ?>><?php _e('September', 'adrotate'); ?></option>
					<option value="10" <?php if($emonth == "10") { echo 'selected'; } ?>><?php _e('October', 'adrotate'); ?></option>
					<option value="11" <?php if($emonth == "11") { echo 'selected'; } ?>><?php _e('November', 'adrotate'); ?></option>
					<option value="12" <?php if($emonth == "12") { echo 'selected'; } ?>><?php _e('December', 'adrotate'); ?></option>
				</select> /
				<input tabindex="14" name="adrotate_eyear" class="search-input" type="text" size="4" maxlength="4" value="<?php echo $eyear;?>" />&nbsp;&nbsp;&nbsp; 
				</label>
			</td>
      	</tr>	
      	<tr>
	        <th><?php _e('Start time (hh:mm)', 'adrotate'); ?></th>
	        <td>
	        	<label for="adrotate_sday">
				<input tabindex="15" name="adrotate_shour" class="search-input" type="text" size="2" maxlength="4" value="<?php echo $shour;?>" /> :
				<input tabindex="16" name="adrotate_sminute" class="search-input" type="text" size="2" maxlength="4" value="<?php echo $sminute;?>" />
				</label>
	        </td>
	        <th><?php _e('End time (hh:mm)', 'adrotate'); ?></th>
	        <td>
	        	<label for="adrotate_eday">
				<input tabindex="17" name="adrotate_ehour" class="search-input" type="text" size="2" maxlength="4" value="<?php echo $ehour;?>" /> :
				<input tabindex="18" name="adrotate_eminute" class="search-input" type="text" size="2" maxlength="4" value="<?php echo $eminute;?>" />
				</label>
			</td>
      	</tr>	
		<?php if($adrotate_config['stats'] == 1) { ?>
      	<tr>
      		<th><?php _e('Maximum Clicks', 'adrotate'); ?></th>
	        <td><input tabindex="19" name="adrotate_maxclicks" type="text" size="5" class="search-input" autocomplete="off" value="<?php echo $schedules->maxclicks;?>" /> <em><?php _e('Leave empty or 0 to skip this.', 'adrotate'); ?></em></td>
		    <th><?php _e('Maximum Impressions', 'adrotate'); ?></th>
	        <td><input tabindex="20" name="adrotate_maxshown" type="text" size="5" class="search-input" autocomplete="off" value="<?php echo $schedules->maximpressions;?>" /> <em><?php _e('Leave empty or 0 to skip this.', 'adrotate'); ?></em></td>
		</tr>
		<?php } ?>
      	<tr>
      		<th valign="top"><?php _e('Important', 'adrotate'); ?></th>
	        <td colspan="3"><em><?php _e('Note: Time uses a 24 hour clock. When you are used to the AM/PM system keep this in mind: If the start or end time is after lunch, add 12 hours. 2PM is 14:00 hours. 6AM is 6:00 hours.', 'adrotate'); ?></em></td>
		</tr>
		</tbody>					
	</table>
	<center><?php _e('Create multiple and more advanced schedules for each advert with AdRotate Pro.', 'adrotate'); ?> <a href="admin.php?page=adrotate-pro"><?php _e('Upgrade today', 'adrotate'); ?></a>.</center>

	<p class="submit">
		<input tabindex="21" type="submit" name="adrotate_ad_submit" class="button-primary" value="<?php _e('Save Advert', 'adrotate'); ?>" />
		<a href="admin.php?page=adrotate-ads&view=manage" class="button"><?php _e('Cancel', 'adrotate'); ?></a>
	</p>

	<h3><?php _e('Advanced', 'adrotate'); ?></h3>
	<p><em><?php _e('Everything below is optional.', 'adrotate'); ?></em></p>
	<table class="widefat" style="margin-top: .5em">
		<tbody>
      	<tr>
	        <th width="15%" valign="top"><?php _e('Mobile', 'adrotate'); ?></th>
	        <td colspan="3">
	        	<?php _e('Show on;', 'adrotate'); ?>&nbsp;&nbsp;<label for="adrotate_desktop"><input disabled type="checkbox" name="adrotate_desktop" checked="checked" /><?php _e('Computers', 'adrotate'); ?></label>&nbsp;&nbsp;<label for="adrotate_mobile"><input disabled type="checkbox" name="adrotate_mobile" checked="checked" /><?php _e('Smartphones', 'adrotate'); ?></label>&nbsp;&nbsp;<label for="adrotate_tablet"><input disabled type="checkbox" name="adrotate_tablet" checked="checked" /><?php _e('Tablets.', 'adrotate'); ?></label><br />
	        	<em><?php _e('Also enable mobile support in the group this advert goes in.', 'adrotate'); ?></em>
	        </td>
      	</tr>
      	<tr>
	        <th width="15%" valign="top"><?php _e('Responsive', 'adrotate'); ?></th>
	        <td colspan="3">
	        	<label for="adrotate_responsive"><input tabindex="22" type="checkbox" name="adrotate_responsive" <?php if($edit_banner->responsive == 'Y') { ?>checked="checked" <?php } ?> /> <?php _e('Enable responsive support for this advert.', 'adrotate'); ?></label><br />
		        <em><?php _e('Upload your images to the banner folder and make sure the filename is in the following format; "imagename.full.ext".', 'adrotate'); ?></em><br />
		        <em><?php _e('For smaller size images use ".320", ".480", ".768" or ".1024" in the filename instead of ".full" for the various viewports.', 'adrotate'); ?></em><br />
		        <em><strong><?php _e('Note:', 'adrotate'); ?></strong> <?php _e('This feature will not be developed any further. Consider switching to the Mobile feature in AdRotate Pro as seen above!', 'adrotate'); ?></em></label>
	        </td>
      	</tr>
       	<tr>
	        <th><?php _e('Sortorder', 'adrotate'); ?></th>
	        <td colspan="3">
		        <label for="adrotate_sortorder"><input tabindex="23" name="adrotate_sortorder" type="text" size="5" class="search-input" autocomplete="off" value="<?php echo $edit_banner->sortorder;?>" /> <em><?php _e('For administrative purposes set a sortorder.', 'adrotate'); ?> <?php _e('Leave empty or 0 to skip this. Will default to ad id.', 'adrotate'); ?></em></label>
			</td>
      	</tr>
		</tbody>
	</table>
	<center><?php _e('With AdRotate Pro you can easily select which devices the advert should show on!', 'adrotate'); ?>  <a href="admin.php?page=adrotate-pro"><?php _e('Upgrade today', 'adrotate'); ?></a>!</center>

	<h3><?php _e('Geo Targeting in AdRotate Pro', 'adrotate'); ?></h3>
	<p><em><?php _e('Assign the advert to a group and enable that group to use Geo Targeting.', 'adrotate'); ?></em></p>
	<table class="widefat" style="margin-top: .5em">			
		<tbody>
	    <tr>
			<th width="15%" valign="top"><?php _e('Cities/States', 'adrotate'); ?></strong></th>
			<td colspan="2">
				<textarea name="adrotate_geo_cities" cols="85" rows="3" disabled>Amsterdam, Noord Holland, New York, California, Tokyo, London</textarea><br />
		        <p><em><?php _e('A comma separated list of cities (or the Metro ID) and/or states (Also the states ISO codes are supported)', 'adrotate'); ?> (Alkmaar, Philadelphia, Melbourne, ...)<br /><?php _e('AdRotate does not check the validity of names so make sure you spell them correctly!', 'adrotate'); ?></em></p>
			</td>
		</tr>
	    <tr>
			<th valign="top"><?php _e('Countries', 'adrotate'); ?></strong></th>
	        <td colspan="2">
		        <label for="adrotate_geo_countries">
			        <div class="adrotate-select">
						<table width="100%">
							<tbody>
							<tr>
								<td class="check-column" style="padding: 0px;"><input type="checkbox" name="adrotate_geo_countries[]" value="" disabled></td><td style="padding: 0px;">United States</td>
							</tr>
							<tr>
								<td class="check-column" style="padding: 0px;"><input type="checkbox" name="adrotate_geo_countries[]" value="" disabled></td><td style="padding: 0px;">Australia</td>
							</tr>
							<tr>
								<td class="check-column" style="padding: 0px;"><input type="checkbox" name="adrotate_geo_countries[]" value="" disabled></td><td style="padding: 0px;">Germany</td>
							</tr>
							<tr>
								<td class="check-column" style="padding: 0px;"><input type="checkbox" name="adrotate_geo_countries[]" value="" disabled></td><td style="padding: 0px;">Brazil</td>
							</tr>
							<tr>
								<td class="check-column" style="padding: 0px;"><input type="checkbox" name="adrotate_geo_countries[]" value="" disabled></td><td style="padding: 0px;">Japan</td>
							</tr>
							</tbody>
						</table>
					</div>
		        </label>
		        <p><em><?php _e('Select the countries you want the adverts to show in.', 'adrotate'); ?> <?php _e('Cities take priority and will be filtered first.', 'adrotate'); ?></em></p>
			</td>
		</tr>
		</tbody>
	</table>
	<center><?php _e('Target your audience with Geo Targeting in AdRotate Pro', 'adrotate'); ?>, <a href="admin.php?page=adrotate-pro"><?php _e('Upgrade today', 'adrotate'); ?></a>.</center>

	<h3><?php _e('Usage', 'adrotate'); ?></h3>
	<table class="widefat" style="margin-top: .5em">
		<tbody>
       	<tr>
	        <th width="15%"><?php _e('Widget', 'adrotate'); ?></th>
	        <td colspan="3"><?php _e('Drag the AdRotate widget to the sidebar you want it in, select "Single Ad" and enter ID', 'adrotate'); ?> "<?php echo $edit_banner->id; ?>".</td>
      	</tr>
     	<tr>
	        <th width="15%"><?php _e('In a post or page', 'adrotate'); ?></th>
	        <td>[adrotate banner="<?php echo $edit_banner->id; ?>"]</td>
	        <th width="15%"><?php _e('Directly in a theme', 'adrotate'); ?></th>
	        <td>&lt;?php echo adrotate_ad(<?php echo $edit_banner->id; ?>); ?&gt;</td>
      	</tr>
      	</tbody>
	</table>

	<p class="submit">
		<input tabindex="24" type="submit" name="adrotate_ad_submit" class="button-primary" value="<?php _e('Save Advert', 'adrotate'); ?>" />
		<a href="admin.php?page=adrotate-ads&view=manage" class="button"><?php _e('Cancel', 'adrotate'); ?></a>
	</p>

	<?php if($groups) { ?>
	<h3><?php _e('Select Groups', 'adrotate'); ?></h3>
	<table class="widefat" style="margin-top: .5em">
		<thead>
		<tr>
			<td scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></td>
			<th><?php _e('ID - Name', 'adrotate'); ?></th>
			<th width="5%"><center><?php _e('Ads', 'adrotate'); ?></center></th>
		</tr>
		</thead>

		<tbody>
		<?php 
		$class = '';
		foreach($groups as $group) {
			if($group->adspeed > 0) $adspeed = $group->adspeed / 1000;
	        if($group->modus == 0) $modus[] = __('Default', 'adrotate');
	        if($group->modus == 1) $modus[] = __('Dynamic', 'adrotate').' ('.$adspeed.' '. __('second rotation', 'adrotate').')';
	        if($group->modus == 2) $modus[] = __('Block', 'adrotate').' ('.$group->gridrows.' x '.$group->gridcolumns.' '. __('grid', 'adrotate').')';
	        if($group->cat_loc > 0 OR $group->page_loc > 0) $modus[] = __('Post Injection', 'adrotate');
	        if($group->geo == 1 AND $adrotate_config['enable_geo'] > 0) $modus[] = __('Geolocation', 'adrotate');

			$ads_in_group = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}adrotate_linkmeta` WHERE `group` = ".$group->id." AND `user` = 0 AND `schedule` = 0;");
			$class = ('alternate' != $class) ? 'alternate' : ''; ?>
		    <tr id='group-<?php echo $group->id; ?>' class=' <?php echo $class; ?>'>
				<th class="check-column" width="2%"><input type="checkbox" name="groupselect[]" value="<?php echo $group->id; ?>" <?php if(in_array($group->id, $meta_array)) echo "checked"; ?> /></th>
				<td><?php echo $group->id; ?> - <strong><?php echo $group->name; ?></strong><span style="color:#999;"><?php echo '<br /><span style="font-weight:bold;">'.__('Mode', 'adrotate').':</span> '.implode(', ', $modus); ?></span></td>
				<td><center><?php echo $ads_in_group; ?></center></td>
			</tr>
			<?php 
			unset($modus);
		} 
		?>
		</tbody>					
	</table>

	<p class="submit">
		<input tabindex="25" type="submit" name="adrotate_ad_submit" class="button-primary" value="<?php _e('Save Advert', 'adrotate'); ?>" />
		<a href="admin.php?page=adrotate-ads&view=manage" class="button"><?php _e('Cancel', 'adrotate'); ?></a>
	</p>
	<?php } ?>
</form>
<?php
/* ------------------------------------------------------------------------------------
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2008-2015 Arnan de Gans. All Rights Reserved.
*  ADROTATE is a trademark of Arnan de Gans.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from it's use.
------------------------------------------------------------------------------------ */
?>
<h3><?php _e('Statistics', 'adrotate'); ?></h3></td>
<span class="description"><?php _e('Track statistics for your adverts.', 'adrotate'); ?> <?php _e('Some options are only available in AdRotate Pro!', 'adrotate'); ?></span>
<table class="form-table">
	<tr>
		<th valign="top"><?php _e('How to track stats', 'adrotate'); ?></th>
		<td>
			<select name="adrotate_stats">
				<option value="0" <?php if($adrotate_config['stats'] == 0) { echo 'selected'; } ?>><?php _e('Disabled', 'adrotate'); ?></option>
				<option value="1" <?php if($adrotate_config['stats'] == 1) { echo 'selected'; } ?>>Internal Tracker (Default)</option>
				<option value="0" disabled>Piwik Analytics (Advanced, Faster)</option>
				<option value="0" disabled>Google Analytics (Faster)</option>
			</select><br />
			<span class="description">
				<strong>Interal Tracker</strong> - <?php _e('Tracks impressions and clicks internally', 'adrotate'); ?>, <a href="https://ajdg.solutions/manuals/adrotate-manuals/adrotate-statistics/?pk_campaign=adrotatepro_settings" target="_blank"><?php _e('manual', 'adrotate'); ?></a>.<br />
				<strong>Supports:</strong> <em><?php _e('Click and Impression recording, Click and impression limits, impression spread for schedules, local stats display. Javascript/HTML5/Flash adverts will only track impressions.', 'adrotate'); ?></em><br /><br />
				<strong>Piwik Analytics (<?php _e('In AdRotate Pro!', 'adrotate'); ?>)</strong> - <?php _e('Requires Piwik Analytics tracker installed in your sites footer! Uses data attributes', 'adrotate'); ?>, <a href="https://ajdg.solutions/manuals/adrotate-manuals/piwik-analytics/?pk_campaign=adrotatepro_settings" target="_blank"><?php _e('manual', 'adrotate'); ?></a>.<br />
				<strong>Supports:</strong> <em><?php _e('Click and Impression recording via Cookie, stats are displayed in Actions > Contents.', 'adrotate'); ?></em><br /><br />
				<strong>Google Analytics (<?php _e('In AdRotate Pro!', 'adrotate'); ?>)</strong> - <?php _e('Requires Google Universal Analytics tracker installed in your sites footer! uses onClick() and onload() in adverts', 'adrotate'); ?>, <a href="https://ajdg.solutions/manuals/adrotate-manuals/google-analytics/?pk_campaign=adrotatepro_settings" target="_blank"><?php _e('manual', 'adrotate'); ?></a>.<br />
				<strong>Supports:</strong> <em><?php _e('Click and Impression recording via Cookie, stats are displayed in Events > Banner.', 'adrotate'); ?></em>
			</span>
		</td>
	</tr>
</table>

<h3><?php _e('Internal Tracker', 'adrotate'); ?></h3></td>
<span class="description"><?php _e('The settings below are for the internal tracker and have no effect when using Piwik/Google Analytics.', 'adrotate'); ?></span>
<table class="form-table">
	<tr>
		<th valign="top"><?php _e('Logged in impressions', 'adrotate'); ?></th>
		<td>
			<input type="checkbox" name="adrotate_enable_loggedin_impressions_disabled" checked="checked" disabled /> <?php _e('Track impressions from logged in users.', 'adrotate'); ?>
		</td>
	</tr>
	<tr>
		<th valign="top"><?php _e('Logged in clicks', 'adrotate'); ?></th>
		<td>
			<input type="checkbox" name="adrotate_enable_loggedin_clicks_disabled" checked="checked" disabled /> <?php _e('Track clicks from logged in users.', 'adrotate'); ?>
		</td>
	</tr>
	<tr>
		<th valign="top"><?php _e('Impression timer', 'adrotate'); ?></th>
		<td>
			<input name="adrotate_impression_timer" type="text" class="search-input" size="5" value="<?php echo $adrotate_config['impression_timer']; ?>" autocomplete="off" /> <?php _e('Seconds.', 'adrotate'); ?><br />
			<span class="description"><?php _e('Default: 60.', 'adrotate'); ?> <?php _e('This number may not be empty, be lower than 10 or exceed 3600 (1 hour).', 'adrotate'); ?></span>
		</td>
	</tr>
	<tr>
		<th valign="top"><?php _e('Click timer', 'adrotate'); ?></th>
		<td>
			<input name="adrotate_click_timer" type="text" class="search-input" size="5" value="<?php echo $adrotate_config['click_timer']; ?>" autocomplete="off" /> <?php _e('Seconds.', 'adrotate'); ?><br />
			<span class="description"><?php _e('Default: 86400.', 'adrotate'); ?> <?php _e('This number may not be empty, be lower than 60 or exceed 86400 (24 hours).', 'adrotate'); ?></span>
		</td>
	</tr>
</table>

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
<h3><?php _e('Notifications - Available in AdRotate Pro', 'adrotate'); ?></h3>
<span class="description"><?php _e('Set up who gets notifications if ads need your attention.', 'adrotate'); ?></span>
<table class="form-table">
	<tr>
		<th valign="top"><?php _e('Delivery method', 'adrotate'); ?></th>
		<td>
			<input type="checkbox" name="adrotate_notification_email_disabled" disabled /> <?php _e('Email message.', 'adrotate'); ?><br />
			<input type="checkbox" name="adrotate_notification_push_disabled" disabled /> <?php _e('Push notifications to your smartphone.', 'adrotate'); ?><br />
			<span class="description"><?php _e('Push notifications are delivered through Pushover, a notification service for Android and iOS', 'adrotate'); ?><br /><?php _e('The Pushover App is a one time purchase for either Android and/or iOS. More information can be found on the pushover website;', 'adrotate'); ?> <a href="http://www.pushover.net" target="_blank">pushover.net</a>.</span>
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top"><?php _e('Test notification', 'adrotate'); ?></th>
		<td>
			<input type="submit" name="adrotate_notification_test_submit_disabled" class="button-secondary" value="Test" disabled /> <?php _e('This sends a test notification. Before you test, save the options first!', 'adrotate'); ?>
		</td>
	</tr>
</table>

<h3><?php _e('Dashboard Notifications', 'adrotate'); ?></h3>
<span class="description"><?php _e('These show to every administrator who can edit adverts.', 'adrotate'); ?></span>
<table class="form-table">
	<tr>
		<th valign="top"><?php _e('Notification banners', 'adrotate'); ?></th>
		<td><label for="adrotate_notification_dashboard_disabled"><input type="checkbox" name="adrotate_notification_dashboard_disabled" disabled /> <?php _e('Disable dashboard notifications.', 'adrotate'); ?></label></td>
	</tr>
</table>

<h3><?php _e('Email Notifications', 'adrotate'); ?></h3>
<span class="description"><?php _e('Set up who gets notification emails.', 'adrotate'); ?></span>
<table class="form-table">
	<tr>
		<th valign="top"><?php _e('Publishers', 'adrotate'); ?></th>
		<td>
			<textarea name="adrotate_notification_email_publisher" cols="50" rows="2" disabled><?php echo get_option('admin_email'); ?></textarea><br />
			<span class="description"><?php _e('A comma separated list of email addresses. Maximum of 5 addresses. Keep this list to a minimum!', 'adrotate'); ?><br />
			<?php _e('Messages are sent once every 24 hours when needed. If this field is empty no email notifications will be send.', 'adrotate'); ?></span>
		</td>
	</tr>
	<tr>
		<th valign="top"><?php _e('Advertisers', 'adrotate'); ?></th>
		<td>
			<textarea name="adrotate_notification_email_advertiser" cols="50" rows="2" disabled><?php echo get_option('admin_email'); ?></textarea><br />
			<span class="description"><?php _e('Who gets email from advertisers. Maximum of 2 addresses. Comma seperated. This field may not be empty!', 'adrotate'); ?></span>
		</td>
	</tr>
</table>

<h3><?php _e('Push Notifications', 'adrotate'); ?></h3>
<span class="description"><?php _e('Receive information about what is happening with your AdRotate setup on your smartphone via Pushover.', 'adrotate'); ?></span>
<table class="form-table">
	<tr>
		<th valign="top"><?php _e('Publishers', 'adrotate'); ?></th>
		<td>
			<input type="checkbox" name="adrotate_notification_push_geo_disabled" disabled /> <?php _e('When you are running out of Geo Targeting Lookups.', 'adrotate'); ?><br /><br />
			<input type="checkbox" name="adrotate_notification_push_status_disabled" disabled /> <?php _e('Daily digest of any advert status other than normal.', 'adrotate'); ?><br />
			<input type="checkbox" name="adrotate_notification_push_queue_disabled" disabled /> <?php _e('Any advertiser saving an advert in your moderation queue.', 'adrotate'); ?><br />
			<input type="checkbox" name="adrotate_notification_push_approved_disabled" disabled /> <?php _e('A moderator approved an advert from the moderation queue.', 'adrotate'); ?><br />
			<input type="checkbox" name="adrotate_notification_push_rejected_disabled" disabled /> <?php _e('A moderator rejected an advert from the moderation queue.', 'adrotate'); ?><br /><span class="description"><?php _e('If you have a lot of activity with many advertisers adding/changing adverts you may get a lot of messages!', 'adrotate'); ?></span>

		</td>
	</tr>
	<tr>
		<th valign="top"><?php _e('User Key', 'adrotate'); ?></th>
		<td>
			<label for="adrotate_notification_push_user_disabled"><input name="adrotate_notification_push_user_disabled" type="text" class="search-input" size="50" value="" disabled /> <?php _e('Get your user token', 'adrotate'); ?> <a href="https://pushover.net" target="_blank"><?php _e('here', 'adrotate'); ?></a>.</label>
		</td>
	</tr>
	<tr>
		<th valign="top"><?php _e('Api Token', 'adrotate'); ?></th>
		<td>
			<label for="adrotate_notification_push_api_disabled"><input name="adrotate_notification_push_api_disabled" type="text" class="search-input" size="50" value="" disabled /> <?php _e('Create your', 'adrotate'); ?> <a href="https://pushover.net/apps/build" target="_blank"><?php _e('App', 'adrotate'); ?></a> <?php _e('and get your API token', 'adrotate'); ?> <a href="https://pushover.net/apps" target="_blank"><?php _e('here', 'adrotate'); ?></a>.</label>
		</td>
	</tr>
</table>
<?php
/* ------------------------------------------------------------------------------------
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2008-2015 Arnan de Gans. All Rights Reserved.
*  ADROTATE is a trademark of Arnan de Gans.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from it's use.
------------------------------------------------------------------------------------ */

$banners = $groups = 0;
$banners = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}adrotate` WHERE `type` != 'empty' AND `type` != 'a_empty';");
$groups = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}adrotate_groups` WHERE `name` != '';");
$data = get_option("adrotate_advert_status");
?>

<div id="dashboard-widgets-wrap">
	<div id="dashboard-widgets" class="metabox-holder">

		<div id="postbox-container-1" class="postbox-container" style="width:50%;">
			<div id="normal-sortables" class="meta-box-sortables ui-sortable">
				
				<h3><?php _e('Currently', 'adrotate'); ?></h3>
				<div class="postbox-adrotate">
					<div class="inside">
						<table width="100%">
							<thead>
							<tr class="first">
								<td width="50%"><strong><?php _e('Your setup', 'adrotate'); ?></strong></td>
								<td width="50%"><strong><?php _e('Adverts that need you', 'adrotate'); ?></strong></td>
							</tr>
							</thead>
							
							<tbody>
							<tr class="first">
								<td class="first b"><a href="admin.php?page=adrotate-ads"><?php echo $banners; ?> <?php _e('Adverts', 'adrotate'); ?></a></td>
								<td class="b"><a href="admin.php?page=adrotate-ads"><?php echo $data['expiressoon']; ?> <?php _e('(Almost) Expired', 'adrotate'); ?></a></td>
							</tr>
							<tr>
								<td class="first b"><a href="admin.php?page=adrotate-groups"><?php echo $groups; ?> <?php _e('Groups', 'adrotate'); ?></a></td>
								<td class="b"><a href="admin.php?page=adrotate-ads"><?php echo $data['error']; ?> <?php _e('Have errors', 'adrotate'); ?></a></td>
							</tr>
							</tbody>

							<thead>
							<tr class="first">
								<td colspan="2"><strong><?php _e('Support AdRotate', 'adrotate'); ?></strong></td>
							</tr>
							</thead>

							<tbody>
							<tr class="first">
								<td colspan="2">
									<center><?php _e('Consider writing a review if you like AdRotate. Also follow my Facebook page for updates about me and my plugins. Thank you!', 'adrotate-pro'); ?><br /><br />
									<center><a class="button-primary" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40ajdg%2enet&item_name=AdRotate%20Donation&no_shipping=0&no_note=0&tax=0&currency_code=EUR&bn=PP%2dDonationsBF&charset=UTF%2d8&amount=10" target="_blank">Donate &euro; 10 via Paypal</a> <a class="button" target="_blank" href="https://wordpress.org/support/view/plugin-reviews/adrotate?rate=5#postform">Write review on WordPress.org</a></center><br />
									<script>(function(d, s, id) {
									  var js, fjs = d.getElementsByTagName(s)[0];
									  if (d.getElementById(id)) return;
									  js = d.createElement(s); js.id = id;
									  js.src = "https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5";
									  fjs.parentNode.insertBefore(js, fjs);
									}(document, 'script', 'facebook-jssdk'));</script>
									<p><center><div class="fb-page" 
										data-href="https://www.facebook.com/Arnandegans" 
										data-width="490" 
										data-adapt-container-width="true" 
										data-hide-cover="false" 
										data-show-facepile="false">
									</div></center></p>
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>

				<h3><?php _e('Buy AdRotate Professional', 'adrotate'); ?></h3>
				<div class="postbox-adrotate">
					<div class="inside">
						<a href="https://ajdg.solutions/products/adrotate-for-wordpress/?pk_campaign=adrotatefree-infopage&pk_kwd=compare_license"><img src="<?php echo WP_CONTENT_URL; ?>/plugins/adrotate/images/adrotate-product.png" alt="adrotate-product" width="150" height="150" align="right" style="padding: 0 0 10px 10px;" /></a>
						<p><h4><?php _e('Single License', 'adrotate'); ?> (&euro; 29.00)</h4><?php _e('For one WordPress installation.', 'adrotate'); ?> <a href="https://ajdg.solutions/cart/?add-to-cart=1124&pk_campaign=adrotatefree-infopage&pk_kwd=buy_single" target="_blank"><?php _e('Buy now', 'adrotate'); ?> &raquo;</a></p>
						<p><h4><?php _e('Duo License', 'adrotate'); ?> (&euro; 39.00)</h4><?php _e('For two WordPress installations.', 'adrotate'); ?> <a href="https://ajdg.solutions/cart/?add-to-cart=1126&pk_campaign=adrotatefree-infopage&pk_kwd=buy_duo" target="_blank"><?php _e('Buy now', 'adrotate'); ?> &raquo;</a></p>
						<p><h4><?php _e('Multi License', 'adrotate'); ?> (&euro; 99.00)</h4><?php _e(' For up to five WordPress installations.', 'adrotate'); ?> <a href="https://ajdg.solutions/cart/?add-to-cart=1128&pk_campaign=adrotatefree-infopage&pk_kwd=buy_multi" target="_blank"><?php _e('Buy now', 'adrotate'); ?> &raquo;</a></p>
						<p><h4><?php _e('Developer License', 'adrotate'); ?> (&euro; 299.00)</h4><?php _e('Unlimited WordPress installations and/or networks.', 'adrotate'); ?> <a href="https://ajdg.solutions/cart/?add-to-cart=1130&pk_campaign=adrotatefree-infopage&pk_kwd=buy_developer" target="_blank"><?php _e('Buy now', 'adrotate'); ?> &raquo;</a></p>
						<p><h4><?php _e('Compare licenses', 'adrotate'); ?></h4> <?php _e("Not sure which license is for you? Compare them...", 'adrotate'); ?> <a href="https://ajdg.solutions/products/adrotate-for-wordpress/?pk_campaign=adrotatefree-infopage&pk_kwd=compare_license" target="_blank"><?php _e('All Licenses', 'adrotate'); ?> &raquo;</a></p>
<!--
						<img src="<?php echo WP_CONTENT_URL; ?>/plugins/adrotate/images/adrotate-product.png" alt="adrotate-product" width="150" height="150" align="right" style="padding: 0 0 10px 10px;" />
						<p><h4><?php _e('Lifetime License', 'adrotate'); ?> (&euro; 59.00)</h4><?php _e('Single installation.', 'adrotate'); ?> <a href="https://ajdg.solutions/cart/?add-to-cart=1124&pk_campaign=adrotatefree-infopage&pk_kwd=buy_single" target="_blank"><?php _e('Buy now', 'adrotate'); ?> &raquo;</a></p>
						<p><h4><?php _e('Duo License', 'adrotate'); ?> (&euro; 29.00 p/year)</h4><?php _e('Up to 2 installations.', 'adrotate'); ?> <a href="https://ajdg.solutions/cart/?add-to-cart=1126&pk_campaign=adrotatefree-infopage&pk_kwd=buy_duo" target="_blank"><?php _e('Buy now', 'adrotate'); ?> &raquo;</a></p>
						<p><h4><?php _e('Multi License', 'adrotate'); ?> (&euro; 99.00 p/year)</h4><?php _e('Up to 10 installations.', 'adrotate'); ?> <a href="https://ajdg.solutions/cart/?add-to-cart=1128&pk_campaign=adrotatefree-infopage&pk_kwd=buy_multi" target="_blank"><?php _e('Buy now', 'adrotate'); ?> &raquo;</a></p>
						<p><h4><?php _e('Developer License', 'adrotate'); ?> (&euro; 199.00 p/year)</h4><?php _e('Up to 25 installations or multisite networks.', 'adrotate'); ?> <a href="https://ajdg.solutions/cart/?add-to-cart=1130&pk_campaign=adrotatefree-infopage&pk_kwd=buy_developer" target="_blank"><?php _e('Buy now', 'adrotate'); ?> &raquo;</a></p>
						<p><em><?php _e('Subscriptions get 1 year access to updates, email support & AdRotate Geo.', 'adrotate'); ?></em></p>
						<p><h4><?php _e('Compare licenses', 'adrotate'); ?></h4> <?php _e("Not sure which license is for you?", 'adrotate'); ?> <a href="https://ajdg.solutions/products/adrotate-for-wordpress/?pk_campaign=adrotatefree-infopage&pk_kwd=compare_license" target="_blank"><?php _e('Compare Licenses', 'adrotate'); ?> &raquo;</a></p>
-->
					</div>
				</div>

				<h3><?php _e('AdRotate is brought to you by', 'adrotate'); ?></h3>
				<div class="postbox-adrotate">
					<div class="inside">
						<p><img src="<?php echo WP_CONTENT_URL; ?>/plugins/adrotate/images/arnan-jungle.jpg" alt="Arnan de Gans" width="100" height="100" align="left" class="adrotate-photo" style="margin: 0 10px 0 0;" />
						 <a href="http://meandmymac.net/?pk_campaign=adrotatefree-infopage" title="Arnan de Gans">Arnan de Gans</a> (<a href="https://ajdg.solutions/?pk_campaign=adrotatefree-infopage" title="Arnan de Gans">AJdG Solutions</a>) - <?php _e('Premium plugins, support and services for WordPress and WooCommerce! I am a digital nomad in the Philippines. Click on my name to find out more about me and what I am doing. Thanks for your support and for using my plugins!', 'adrotate'); ?></p>
					</div>
				</div>

			</div>
		</div>

		<div id="postbox-container-3" class="postbox-container" style="width:50%;">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
						
				<h3><?php _e('Get more features with AdRotate Pro', 'adrotate'); ?></h3>
				<div class="postbox-adrotate">
					<div class="inside">
						<p><img src="<?php echo WP_CONTENT_URL; ?>/plugins/adrotate/images/logo-60x60.png" alt="AdRotate logo" width="60" height="60" align="left" style="padding: 0 10px 10px 0;" /><?php _e('Benefit from extra features to reinforce your income with advertising campaigns. Make the most of your website with the powerful tools AdRotate Pro offers on top of the trusted features included in the free version.', 'adrotate'); ?> <?php _e('Want to know more about', 'adrotate'); ?> <a href="admin.php?page=adrotate-pro">AdRotate Pro</a>? <?php _e('Visit the', 'adrotate'); ?> <a href="https://ajdg.solutions/products/adrotate-for-wordpress/?pk_campaign=adrotatefree-infopage" target="_blank">AdRotate <?php _e('website', 'adrotate'); ?></a>.</p>
					</div>
				</div>

				<h3><?php _e('AdRotate News and Developer Blog', 'adrotate'); ?></h3>
				<div class="postbox-adrotate">
					<div class="inside">
						<?php 
							wp_widget_rss_output(array(
							'url' => array('http://meandmymac.net/feed/', 'http://ajdg.solutions/feed/'), 
							'title' => 'News', 
							'items' => 6, 
							'show_summary' => 1, 
							'show_author' => 0, 
							'show_date' => 1)
							);
						?>
					</div>
				</div>

			</div>	
		</div>

	</div>

	<div class="clear"></div>
	<p><?php echo adrotate_trademark(); ?></p>
</div>
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
<h3><?php _e('Geo Targeting - Available in AdRotate Pro', 'adrotate'); ?></h3>
<span class="description"><?php _e('Target certain areas in the world for better advertising oppurtunities.', 'adrotate'); ?></span>
<table class="form-table">
	<tr>
		<th valign="top"><?php _e('Which Geo Service', 'adrotate'); ?></th>
		<td>
			<select name="adrotate_enable_geo_disabled">
				<option value="0" disabled><?php _e('Disabled', 'adrotate'); ?></option>
				<option value="0" disabled>AdRotate Geo</option>
				<option value="0" disabled>MaxMind City (Recommended)</option>
				<option value="0" disabled>MaxMind Country</option>
				<option value="0" disabled>Telize</option>
			</select><br />
			<span class="description">
				<strong>MaxMind</strong> - <a href="https://www.maxmind.com/en/geoip2-precision-services?rId=ajdgnet" target="_blank">GeoIP2 Precision</a> - <?php _e('The most complete and accurate geo targeting you can get for only $20 USD per 50000 lookups.', 'adrotate'); ?> <a href="https://www.maxmind.com/en/geoip2-precision-city?rId=ajdgnet" target="_blank"><?php _e('Buy now', 'adrotate'); ?>.</a><br />
				<em><strong>Supports:</strong> Countries, States, State ISO codes, Cities and DMA codes.</em><br /><br />					
				<strong>AdRotate Geo</strong> - <?php _e('50000 free lookups every day, uses GeoLite2 databases from MaxMind!', 'adrotate'); ?><br />
				<em><strong>Supports:</strong> Countries, Cities, DMA codes, States and State ISO codes.</em><br /><br />
				<strong>Telize</strong> - <?php _e('Free service, uses GeoLite2 databases from MaxMind!', 'adrotate'); ?><br />
				<em><strong>Supports:</strong> Countries, Cities and DMA codes.</em>
			</span>
		</td>
	</tr>
	<tr>
		<th valign="top"><?php _e('Geo Cookie Lifespan', 'adrotate'); ?></th>
		<td>
			<label for="adrotate_geo_cookie_life"><select name="adrotate_geo_cookie_life_disabled">
				<option value="0" disabled>24 (<?php _e('Default', 'adrotate'); ?>)</option>
				<option value="0" disabled>36</option>
				<option value="0" disabled>48</option>
				<option value="0" disabled>72</option>
				<option value="0" disabled>120</option>
				<option value="0" disabled>168</option>
			</select> <?php _e('Hours.', 'adrotate'); ?></label><br />
			<span class="description"><?php _e('Geo Data is stored in a cookie to reduce lookups. How long should this cookie last? A longer period is less accurate for mobile users but may reduce the usage of your lookups drastically.', 'adrotate'); ?></span>

		</td>
	</tr>
</table>

<h3><?php _e('MaxMind City/Country', 'adrotate'); ?></h3>
<table class="form-table">
	<tr>
		<th valign="top"><?php _e('Username/Email', 'adrotate'); ?></th>
		<td><label for="adrotate_geo_email"><input name="adrotate_geo_email_disabled" type="text" class="search-input" size="50" value="" disabled /></label></td>
	</tr>
	<tr>
		<th valign="top"><?php _e('Password/License Key', 'adrotate'); ?></th>
		<td><label for="adrotate_geo_pass"><input name="adrotate_geo_pass_disabled" type="text" class="search-input" size="50" value="" disabled /></label></td>
	</tr>
</table>
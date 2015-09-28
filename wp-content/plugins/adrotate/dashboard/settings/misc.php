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
<h3><?php _e('Miscellaneous', 'adrotate'); ?></h3>
<table class="form-table">			
	<tr>
		<th valign="top"><?php _e('Widget alignment', 'adrotate'); ?></th>
		<td><label for="adrotate_widgetalign"><input type="checkbox" name="adrotate_widgetalign" <?php if($adrotate_config['widgetalign'] == 'Y') { ?>checked="checked" <?php } ?> /> <?php _e('Check this box if your widgets do not align in your themes sidebar. (Does not always help!)', 'adrotate'); ?></label></td>
	</tr>
	<tr>
		<th valign="top"><?php _e('Widget padding', 'adrotate'); ?></th>
		<td><label for="adrotate_widgetpadding"><input type="checkbox" name="adrotate_widgetpadding" <?php if($adrotate_config['widgetpadding'] == 'Y') { ?>checked="checked" <?php } ?> /> <?php _e('Enable this to remove the padding (blank space) around ads in widgets. (Does not always work!)', 'adrotate'); ?></label></td>
	</tr>
	<?php if($adrotate_config['w3caching'] == "Y" AND !defined('W3TC_DYNAMIC_SECURITY')) { ?>
	<tr>
		<th valign="top"><?php _e('NOTICE:', 'adrotate'); ?></th>
		<td><span style="color:#f00;"><?php _e('You have enabled W3 Total Caching support but not defined the security hash. You need to add the following line to your wp-config.php near the bottom or below line 52 (which defines another hash.) Using the "late init" function needs to be enabled in W3 Total Cache as well too.', 'adrotate'); ?></span><br /><pre>define('W3TC_DYNAMIC_SECURITY', '<?php echo md5(rand(0,999)); ?>');</pre></td>
	</tr>
	<?php } ?>
	<tr>
		<th valign="top"><?php _e('W3 Total Caching', 'adrotate'); ?></th>
		<td><label for="adrotate_w3caching"><input type="checkbox" name="adrotate_w3caching" <?php if($adrotate_config['w3caching'] == 'Y') { ?>checked="checked" <?php } ?> /> <?php _e('Check this box if you use W3 Total Caching on your site.', 'adrotate'); ?></label></td>
	</tr>
	<tr>
		<th valign="top">&nbsp;</th>
		<td><span class="description"><?php _e('It may take a while for the ad to start rotating. The caching plugin needs to refresh the cache. This can take up to a week if not done manually.', 'adrotate'); ?> <?php _e('Caching support only works for [shortcodes] and the AdRotate Widget. If you use a PHP Snippet you need to wrap your PHP in the exclusion code yourself.', 'adrotate'); ?></span></td>
	</tr>
</table>

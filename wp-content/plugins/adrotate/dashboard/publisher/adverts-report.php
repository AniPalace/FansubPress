<?php
/* ------------------------------------------------------------------------------------
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2008-2015 Arnan de Gans. All Rights Reserved.
*  ADROTATE is a trademark of Arnan de Gans.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from it's use.
------------------------------------------------------------------------------------ */

$banner 		= $wpdb->get_row("SELECT `title`, `tracker` FROM `".$wpdb->prefix."adrotate` WHERE `id` = '$ad_edit_id';");
$stats 			= adrotate_stats($ad_edit_id);
$stats_today 	= adrotate_stats($ad_edit_id, $today);

// Get Click Through Rate
$ctr = adrotate_ctr($stats['clicks'], $stats['impressions']);

if($adrotate_debug['publisher'] == true) {
	echo "<p><strong>[DEBUG] Ad Stats (all time)</strong><pre>";
	print_r($stats); 
	echo "</pre></p>"; 
	echo "<p><strong>[DEBUG] Ad Stats (today)</strong><pre>";
	print_r($stats_today); 
	echo "</pre></p>"; 
}	
?>

<h3><?php _e('Statistics for advert', 'adrotate'); ?> '<?php echo $banner->title; ?>'</h3>
<table class="widefat" style="margin-top: .5em">

	<tbody>
  	<tr>
        <td width="20%"><div class="stats_large"><?php _e('Impressions', 'adrotate'); ?><br /><div class="number_large"><?php echo $stats['impressions']; ?></div></div></td>
        <td width="20%"><div class="stats_large"><?php _e('Clicks', 'adrotate'); ?><br /><div class="number_large"><?php if($banner->tracker == "Y") { echo $stats['clicks']; } else { echo '--'; } ?></div></div></td>
        <td width="20%"><div class="stats_large"><?php _e('Impressions today', 'adrotate'); ?><br /><div class="number_large"><?php echo $stats_today['impressions']; ?></div></div></td>
        <td width="20%"><div class="stats_large"><?php _e('Clicks today', 'adrotate'); ?><br /><div class="number_large"><?php if($banner->tracker == "Y") { echo $stats_today['clicks']; } else { echo '--'; } ?></div></div></td>
        <td width="20%"><div class="stats_large"><?php _e('CTR', 'adrotate'); ?><br /><div class="number_large"><?php if($banner->tracker == "Y") { echo $ctr.' %'; } else { echo '--'; } ?></div></div></td>
  	</tr>
	<tbody>

</table>

<h3><?php _e('Monthly overview of clicks and impressions', 'adrotate'); ?></h3>
<table class="widefat" style="margin-top: .5em">

	<tbody>
  	<tr>
        <th colspan="5">
        	<div style="text-align:center;"><?php echo adrotate_stats_nav('ads', $ad_edit_id, $month, $year); ?></div>
        	<?php echo adrotate_stats_graph('ads', $ad_edit_id, 1, $monthstart, $monthend); ?>
        </th>
  	</tr>
	</tbody>

</table>	

<p><em><strong><?php _e('Note:', 'adrotate'); ?></strong> <?php _e('All statistics are indicative. They do not nessesarily reflect results counted by other parties.', 'adrotate'); ?></em></p>
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
 Name:      adrotate_export_ads

 Purpose:   Export adverts in various formats
 Receive:   $ids, $format
 Return:    -- None --
 Since:		3.11
-------------------------------------------------------------*/
function adrotate_export_ads($ids) {
	global $wpdb;

	$all_ads = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."adrotate` ORDER BY `id` ASC;", ARRAY_A);

	$filename = "AdRotate_export_".date_i18n("mdYHi")."_".uniqid().".xml";
	$fp = fopen(WP_CONTENT_DIR . '/reports/'.$filename, 'r');

	$xml = new SimpleXMLElement('<adverts></adverts>');

	foreach($all_ads as $single) {
		if(in_array($single['id'], $ids)) {
			$starttime = $stoptime = 0;
			$starttime = $wpdb->get_var("SELECT `starttime` FROM `".$wpdb->prefix."adrotate_schedule`, `".$wpdb->prefix."adrotate_linkmeta` WHERE `ad` = '".$single['id']."' AND `schedule` = `".$wpdb->prefix."adrotate_schedule`.`id` ORDER BY `starttime` ASC LIMIT 1;");
			$stoptime = $wpdb->get_var("SELECT `stoptime` FROM `".$wpdb->prefix."adrotate_schedule`, `".$wpdb->prefix."adrotate_linkmeta` WHERE `ad` = '".$single['id']."' AND  `schedule` = `".$wpdb->prefix."adrotate_schedule`.`id` ORDER BY `stoptime` DESC LIMIT 1;");

			if(!is_array($single['cities'])) $single['cities'] = array();
			if(!is_array($single['countries'])) $single['countries'] = array();
			
			$node = $xml->addChild('advert');
			$node->addChild('title', $single['title']);
			$node->addChild('bannercode', stripslashes($single['bannercode']));
			$node->addChild('imagetype', $single['imagetype']);
			$node->addChild('image', $single['image']);
			$node->addChild('link', $single['link']);
			$node->addChild('tracker', $single['tracker']);
			$node->addChild('responsive', $single['responsive']);
			$node->addChild('weight', $single['weight']);
			$node->addChild('budget', $single['budget']);
			$node->addChild('crate', $single['crate']);
			$node->addChild('irate', $single['irate']);
			$node->addChild('cities', implode(',', unserialize($single['cities'])));
			$node->addChild('countries', implode(',', unserialize($single['countries'])));
			$node->addChild('start', $starttime);
			$node->addChild('end', $stoptime);
		}
	}

	file_put_contents(WP_CONTENT_DIR . '/reports/'.$filename, $xml->saveXML());

	adrotate_return('adrotate-ads', 215, array('file' => $filename));
	exit;
}
?>
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
function adrotate_export_ads($ids, $format) {
	global $wpdb;

	$all_ads = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}adrotate` ORDER BY `id` ASC;", ARRAY_A);

	$ads = array();
	foreach($all_ads as $single) {
		if(in_array($single['id'], $ids)) {
			$starttime = $stoptime = 0;
			$starttime = $wpdb->get_var("SELECT `starttime` FROM `{$wpdb->prefix}adrotate_schedule`, `{$wpdb->prefix}adrotate_linkmeta` WHERE `ad` = '".$single['id']."' AND `schedule` = `{$wpdb->prefix}adrotate_schedule`.`id` ORDER BY `starttime` ASC LIMIT 1;");
			$stoptime = $wpdb->get_var("SELECT `stoptime` FROM `{$wpdb->prefix}adrotate_schedule`, `{$wpdb->prefix}adrotate_linkmeta` WHERE `ad` = '".$single['id']."' AND  `schedule` = `{$wpdb->prefix}adrotate_schedule`.`id` ORDER BY `stoptime` DESC LIMIT 1;");

			if(!is_array($single['cities'])) $single['cities'] = array();
			if(!is_array($single['countries'])) $single['countries'] = array();
			
			$ads[$single['id']] = array(
				'title' => $single['title'],
				'bannercode' => stripslashes($single['bannercode']),
				'imagetype' => $single['imagetype'],
				'image' => $single['image'],
				'tracker' => $single['tracker'],
				'mobile' => $single['mobile'],
				'tablet' => $single['tablet'],
				'responsive' => $single['responsive'],
				'weight' => $single['weight'],
				'budget' => $single['budget'],
				'crate' => $single['crate'],
				'irate' => $single['irate'],
				'cities' => implode(',', maybe_unserialize($single['cities'])),
				'countries' => implode(',', maybe_unserialize($single['countries'])),
				'start' => $starttime,
				'end' => $stoptime,
			);
		}
	}

 	if($ads) {
		$filename = "AdRotate_export_".date_i18n("mdYHi")."_".uniqid().".xml";

		$xml = new SimpleXMLElement('<adverts></adverts>');
		foreach($ads as $ad) {
			$node = $xml->addChild('advert');
			$node->addChild('title', $ad['title']);
			$node->addChild('bannercode', $ad['bannercode']);
			$node->addChild('imagetype', $ad['imagetype']);
			$node->addChild('image', $ad['image']);
			$node->addChild('tracker', $ad['tracker']);
			$node->addChild('mobile', $ad['mobile']);
			$node->addChild('tablet', $ad['tablet']);
			$node->addChild('responsive', $ad['responsive']);
			$node->addChild('weight', $ad['weight']);
			$node->addChild('budget', $ad['budget']);
			$node->addChild('crate', $ad['crate']);
			$node->addChild('irate', $ad['irate']);
			$node->addChild('cities', $ad['cities']);
			$node->addChild('countries', $ad['countries']);
			$node->addChild('start', $ad['start']);
			$node->addChild('end', $ad['end']);
		}

		file_put_contents(WP_CONTENT_DIR . '/reports/'.$filename, $xml->saveXML());
		unset($all_ads, $ads);

		adrotate_return('adrotate-ads', 215, array('file' => $filename));
		exit;
	} else {
		adrotate_return('adrotate-ads', 509);
	}
}
?>
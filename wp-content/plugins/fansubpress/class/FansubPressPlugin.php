<?php
require_once(dirname( __FILE__ ).'/FansubPressPluginBase.php');

class FansubPressPlugin extends FansubPressPluginBase{
  /**
   * Inicializálás
   *
   * @author NewPlayer
   * @since 2015-10-13
   */
  function init() {
    // Extra típusok megjelenítése a kezdőlapon
    add_filter('pre_get_posts', array($this, 'extraTypesInHomePage'));

    // A letöltésnél a permalinket ki kell bővíteni
    add_filter('generate_rewrite_rules', array($this, 'downloadRewriteRule'));
    add_filter('query_vars', array($this, 'downloadQueryVars'));
    add_action('parse_request', array($this, 'downloadPermalink'));

    add_action('init',array($this, 'siteInit'));
    
    self::getInstanceClass(self::CLASS_TEMPLATE)->init();
  }

  function downloadPermalink(&$wp_query) {
    $fsp_action = $wp_query->query_vars['fsp_action'];
    // var_dump($wp_query);
    if ($fsp_action === 'download') {
      $fsp_post_id = $wp_query->query_vars['fsp_post_id'];
      $fsp_type = $wp_query->query_vars['fsp_type'];
      $meta_key = '';
      $meta_key_count = '';
      if ($fsp_type === 'subtitle') {
        $meta_key = 'wpcf-subtitle-url';
        $meta_key_count = 'wpcf-subtitle-url-click';
      }
      // var_dump($meta_key);

      // Ha van meta kulcs, akkor növeljük a számlálót és átirányítunk a letöltésre
      if ($meta_key) {
        $count = (int)get_post_meta($fsp_post_id, $meta_key_count, true);
        // var_dump($count);
        $count++;
        // var_dump($count);
        update_post_meta($fsp_post_id, $meta_key_count, $count);
        // die();
        $url  = get_post_meta($fsp_post_id, $meta_key ,true);

        wp_redirect($url); die();
      }

    }
    // die();
  }

  /**
   * A letöltéshez kapcsolt permalink regisztrálása
   *
   * @author NewPlayer
   * @since 2015-10-15
   *
   * @param  [array] $rules [description]
   * @return [array]        [description]
   */
  function downloadRewriteRule( &$wp_rewrite){
  	//get the slug of the event page
  	$download_rules = array();
  	$download_rules['fsp_download/subtitle/(.+)'] = 'index.php?fsp_post_id='.$wp_rewrite->preg_index(1).'&fsp_action=download&fsp_type=subtitle';
  	$download_rules['fsp_download/video/(.+)'] = 'index.php?fsp_post_id='.$wp_rewrite->preg_index(1).'&fsp_action=download&fsp_type=video';
    // print '<pre>';
    // var_export($rules);
    // die();
  	$wp_rewrite->rules = $download_rules + $wp_rewrite->rules;
  }

  /**
   * A letöltésnél egyedileg használt
   * @param  [type] $vars [description]
   * @return [type]       [description]
   */
  function downloadQueryVars($vars) {
    array_push($vars, 'fsp_action');
    array_push($vars, 'fsp_type');
    array_push($vars, 'fsp_post_id');

    return $vars;
  }

  /**
   * A kezdőlapon megjelenítendő extra típusok
   *
   * @author NewPlayer
   * @since 2015-10-14
   *
   * @param  [type] $query [description]
   * @return [type]        [description]
   */
  function extraTypesInHomePage( $query ) {
  	if ( ( is_home() && $query->is_main_query() ) || is_feed() ) {
  		$query->set( 'post_type', array( 'post', 'download' ) );
  	   return $query;
    }
  }

  function siteInit() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
  }
}

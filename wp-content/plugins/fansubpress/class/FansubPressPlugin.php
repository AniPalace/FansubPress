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
    add_filter( 'pre_get_posts', array($this,'extraTypesInHomePage') );
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
}

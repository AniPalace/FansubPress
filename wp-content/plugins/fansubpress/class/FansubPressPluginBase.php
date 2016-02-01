<?php
class FansubPressPluginBase {
  const CLASS_BASE = '';
  const CLASS_DOWNLOAD = 'Download';

  static $instance = array();

  /**
   * Megadott osztály lekérdezése
   *
   * @author NewPlayer
   * @since 2015-10-13
   *
   * @param  [string] $class [Az osztályban található $CLASS_ változók]
   * @return [FansubPressPluginBase]        [A lekérdezett Class osztálypéldánya]
   */
  static function getInstanceClass($class = '') {
    $class = 'FansubPressPlugin'.$class;
    if (!self::$instance[$class]) {
      require_once(dirname( __FILE__ ).'/'.$class.'.php');
      self::$instance[$class] = new $class();
    }

    return self::$instance[$class];
  }
}

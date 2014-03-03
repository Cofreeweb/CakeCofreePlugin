<?php
/**
 * Se encarga de gestionar el título de la página
 *
 * @package cofree.lib
 */
class WebTitle
{
/**
 * Variable array donde se irá guardando el título
 *
 */
  protected static $_title = array();
  
  
/**
 * Añade un texto al título
 *
 * @param string $title El título a añadir
 * @param boolean $reset Si true, el título de la página se reseteará
 * @return void
 */
  public static function add( $title, $reset = false)
  {
    if( $reset)
    {
      self::$_title = array();
    }
    
    self::$_title [] = $title;
  }
  
/**
 * Devuelve el título separado por lo indicado en $separator
 *
 * @param string $separator 
 * @return string
 */  
  public static function read( $separator = ' - ')
  {
    return implode( $separator, array_reverse( self::$_title));
  }
}

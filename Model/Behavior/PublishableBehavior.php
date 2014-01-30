<?php

/**
 * Publishable behavior class
 * 
 * 
 * @package Cofree.Model.Behavior
 */
class PublishableBehavior extends ModelBehavior
{
  private $_settings = array();
  
  function setup( Model $model, $settings = array()) 
  {
    $default = array(
        'field' => 'published',
    );

    $this->_settings = (!empty($settings)) ? $settings + $default : $default;
  }


/**
 * 
 *
 * @param object $Model 
 * @param array $query 
 * @return array El query para la petición de la búsqueda
 */
  function beforeFind( Model $Model, $query)
  {
    // Si estás en el navegador
    if( class_exists( 'Router'))
    {
      $params = Router::getParams();

      if( !isset( $params ['admin']) && (isset( $params ['controller']) && $params ['controller'] != 'crud'))
      {
        $query ['conditions'][$Model->alias .'.'. $this->_settings ['field']] = 1;    
      }
    }
    
    return $query;  
  }
  
}

?>
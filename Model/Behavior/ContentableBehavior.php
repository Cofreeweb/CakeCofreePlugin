<?php

/**
 * Contentable behavior class
 * 
 * 
 * @package Cofree.Model.Behavior
 */
class ContentableBehavior extends ModelBehavior
{
  
  public function setup( Model $Model, $settings = array())
  {
    
  }

/**
 * Coloca el valor del ContentType, que será el id del registro que tiene el nombre del model
 *
 * @param string $Model 
 * @return boolean
 */
  public function beforeSave( Model $Model)
  {
    $Model->data [$Model->alias]['content_type'] = $Model->name;
    return true;
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
    // Siempre le suma la clave para el tipo de contenido
    if( !isset( $query ['conditions'][$Model->alias .'.content_type']))
    {
      $query ['conditions'][$Model->alias .'.content_type'] = $Model->name;    
    }
    return $query;  
  }
  
}

?>
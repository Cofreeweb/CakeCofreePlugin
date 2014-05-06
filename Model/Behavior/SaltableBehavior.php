<?php

/**
 * Saltable behavior class
 * 
 * Se encarga de crear un salt en un campo concreto, cuando se crea el registro
 * 
 * @package Cofree.Model.Behavior
 */
class SaltableBehavior extends ModelBehavior
{
  private $_settings = array();
  
  function setup( Model $model, $settings = array()) 
  {
    $default = array(
        'salt'
    );

    $this->_settings = (!empty($settings)) ? $settings + $default : $default;
  }


/**
 * Colocal el salt en los campos indicados
 *
 * @param object $Model 
 * @return boolean
 */
  function beforeSave( Model $Model)
  {
    if( empty( $Model->id))
    {
      foreach( $this->_settings as $field)
      {
        if( $Model->hasField( $field))
        {
          $Model->data [$Model->alias][$field] = Security::hash(String::uuid());
        }
      }
    }
    
    return true;  
  }
  
}

?>
<?php

/**
 * Langable behavior class
 * 
 * 
 * @package Cofree.Model.Behavior
 */
class LangableBehavior extends ModelBehavior
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
  public function beforeSave( Model $Model, $options = array())
  {
    if( empty( $Model->id))
    {
      if( Configure::read( 'Config.language') && $Model->hasField( 'language'))
      {
        $Model->data [$Model->alias]['language'] = Configure::read( 'Config.language');
      }
    }
    
    return true;
  }

  
}

?>
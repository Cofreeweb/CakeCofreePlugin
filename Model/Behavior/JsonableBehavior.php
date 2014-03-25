<?php

/**
 * Jsonable behavior class
 * 
 * 
 * @package Cofree.Model.Behavior
 */
class JsonableBehavior extends ModelBehavior
{
  
  public function setup( Model $model, $settings = array())
  {
    $this->settings = $settings;
  }
  
  public function beforeSave( Model $model)
  {
    foreach( $this->settings ['fields'] as $field)
    {
      if( isset( $model->data [$model->alias][$field]))
      {
        $model->data [$model->alias][$field] = json_encode( $model->data [$model->alias][$field]);
      }
    }
    
    return true;
  }
  
  public function afterFind( Model $model, $results)
  {
    if( isset( $results [0][$model->alias]))
    {
      foreach( $this->settings ['fields'] as $field)
      {
        foreach( $results as $key => $result)
        {
          if( isset( $results [$key][$model->alias][$field]))
          {
            $data = json_decode( $results [$key][$model->alias][$field], true);
            $results [$key][$model->alias][$field] = $data;
          }
        }
      }
    }
    
    return $results;
  }
  
}

?>
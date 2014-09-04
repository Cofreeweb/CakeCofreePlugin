<?php

/**
 * Jsonable behavior class
 * 
 * 
 * @package Cofree.Model.Behavior
 */
class JsonableBehavior extends ModelBehavior
{
  
/**
 * Opciones por defecto
 *
 * @var array
 */
  private $__defaults = array(
    'fields' => array()
  );
  
  public function setup( Model $model, $settings = array())
  {
    $this->settings = array_merge( $this->__defaults, $settings);
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
    if( empty( $this->settings ['fields']))
    {
      return $results;
    }
    
    if( isset( $results [0][$model->alias]))
    {
      foreach( $this->settings ['fields'] as $field)
      {
        foreach( $results as $key => $result)
        {
          if( array_key_exists( $field, $result [$model->alias]))
          {
            $data = json_decode( $results [$key][$model->alias][$field], true);

            if( empty( $data))
            {
              $data = array();
            }

            $results [$key][$model->alias][$field] = $data;
          }
        }
      }
    }
    
    return $results;
  }
  
/**
 * Guarda el array de las secciones para ser usado por un js sortable
 * Utiliza la clave 'items' para las secciones hijo
 *
 * @param array $datas 
 * @return array
 */
  public function buildTreeForJson( Model $Model, $datas)
  {
    $sections = array();
  
    foreach( $datas as $data)
    {
      $section = $data [$Model->alias];
    
      if( !empty( $data ['children']))
      {
        $section ['items'] = $this->buildTreeForJson( $Model, $data ['children']);
      }
      else
      {
        $section ['items'] = array();
      }
    
      $sections [] = $section;
    }
    return $sections;
  }
  
}

?>
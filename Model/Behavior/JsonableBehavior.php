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
    'fields' => array(),
    'translate' => array()
  );
  
  public function setup( Model $model, $settings = array())
  {
    $this->settings = array_merge( $this->__defaults, $settings);
  }
  
  public function beforeSave( Model $model, $options = array())
  {
    foreach( $model->actsAs ['Cofree.Jsonable']['fields'] as $field)
    {
      if( isset( $model->data [$model->alias][$field]) && is_array( $model->data [$model->alias][$field]))
      {
        $model->data [$model->alias][$field] = json_encode( $model->data [$model->alias][$field]);
      }
    }

    return true;
  }
  
  public function afterFind( Model $model, $results, $primary = false)
  {
    if( empty( $this->settings ['fields']))
    {
      return $results;
    }

    if( isset( $results [0][$model->alias]))
    {
      foreach( $model->actsAs ['Cofree.Jsonable']['fields'] as $field)
      {
        foreach( $results as $key => $result)
        {
          if( array_key_exists( $field, $result [$model->alias]))
          {
            $data = json_decode( $results [$key][$model->alias][$field], true);

            $data = $this->setTranslates( $model, $data, $field);

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

  public function setTranslates( Model $Model, $data, $field)
  {
    if( !is_array( $data))
    {
      return $data;
    }

    $first_key = key( $data);
    $is_data_array = is_numeric( $first_key);

    if( array_key_exists( $field, $this->settings ['translate']))
    {
      $keys = $this->settings ['translate'][$field];
      
      foreach( $keys as $key)
      {
        if( $is_data_array)
        {
          foreach( $data as &$_data)
          {
            $_data = $this->_translate( $_data, $key);
          }
        }
        else
        {
          $data = $this->_translate( $data, $key);
        }
      }
    }
    
    return $data;
  }

  private function _translate( $data, $key)
  {
    if( !isset( $data [$key]) || !is_array( $data [$key]))
    {
      $data [$key] = array();
    }

    foreach( Configure::read( 'Config.languages') as $locale)
    {
      if( !isset( $data [$key][$locale]))
      {
        $data [$key][$locale] = null;
      }
    }

    $data ['_'. $key] = $data [$key][Configure::read( 'Config.language')];
    return $data;
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
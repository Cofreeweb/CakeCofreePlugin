<?php

/**
 * Utilities behavior class
 * 
 * 
 * @package Cofree.Model.Behavior
 */
class UtilitiesBehavior extends ModelBehavior
{
  
  public function setup( Model $Model, $settings = array())
  {
    $this->settings = $settings;
  }

  public function deleteHasMany( Model $Model, $model, $data)
  {
    $association = $Model->hasMany [$model];
    $olds_ids = $Model->$model->find( 'list', array(
        $Model->$model->alias .'.'. $association ['foreignKey'] => $Model->id,
        'fields' => array(
            $Model->$model->primaryKey
        )
    ));

    $news_ids = Hash::extract( $data [$model], '{n}.id');

    $diff = array_diff( $olds_ids, $news_ids);

    foreach( $diff as $id)
    {
      $Model->$model->delete( $id);
    }
  }
}

?>
<?php

/**
 * Utilities behavior class
 *
 * Colección de métodos utiles para los models
 *
 * En este Behavior NUNCA se usarán los callbacks
 * 
 *ç * @package Cofree.Model.Behavior
 */

class UtilitiesBehavior extends ModelBehavior
{
  
/**
 * Al guardar un contenido que en su edición usa los registros de hasMany se puede llamar a este método para 
 * que se borren los registros que se han eliminado en la edición, es decir, aquellos que no llegan del formulario
 * En resumen, este método borra todos los registros que no estén presentes en Request::data
 * 
 * @param  Model  $Model
 * @param  string $model El nombre del modelo a borrar
 * @param  arra $data  Los datos de Request::data
 * @return void        
 */
  public function deleteHasMany( Model $Model, $model, $data)
  {
    $association = $Model->hasMany [$model];

    // Recopila los ids relacionados con el model, que están en la base de datos
    $olds_ids = $Model->$model->find( 'list', array(
        'conditions' => array(
            $Model->$model->alias .'.'. $association ['foreignKey'] => $Model->id,
        ),
        'fields' => array(
            $Model->$model->primaryKey
        )
    ));

    // Toma los ids que han llegado del formulario
    $news_ids = Hash::extract( $data [$model], '{n}.id');

    // Calcula los ids que sobran, es decir, los que hay que borrar
    $diff = array_diff( $olds_ids, $news_ids);

    // Itera los ids y los borra
    foreach( $diff as $id)
    {
      $Model->$model->delete( $id);
    }
  }


  public function sortable( Model $Model, $data)
  {
    if( !is_array( $data))
    {
      return;
    }

    $sort = 1;

    foreach( $data as $id)
    {
      $Model->id = $id;
      $Model->saveField( 'sort', $sort);
      $sort++;
    }
  }

  
}

?>
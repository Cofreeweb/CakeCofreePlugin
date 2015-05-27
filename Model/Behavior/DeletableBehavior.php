<?php

/**
 * Modifica el comportamiento del Model, de manera que condiciona la clave 'deleted' para indicar que un registro está borrado
 * Es útil para no borrar nunca de la base de datos los registros eliminados.
 *
 * Este behavior se encarga tanto de la lectura como de la escritura
 * 
 * 
 * @package Cofree.Model.Behavior
 */
class DeletableBehavior extends ModelBehavior
{
  
  // public function beforeValidate( Model $Model, $query)
  // {
  //   $Model->data [$Model->alias .'.deleted'] = array( 0, 1);
  //   
  //   return parent::beforeValidate( $Model);
  // }

/**
 * 
 *
 * @param object $Model 
 * @param array $query 
 * @return array El query para la petición de la búsqueda
 */
  function beforeFind( Model $Model, $query)
  {   
    // Le añade la clave deleted para no tomar los contenidos borrados
    if( !isset( $query ['conditions']['deleted']))
    {
      $query ['conditions'][$Model->alias .'.deleted'] = 0;
    }
    
    return $query;  
  }
  
  public function beforeDelete( Model $Model, $cascade = true)
  {
    $updateCounterCache = false;
    
		if (!empty( $Model->belongsTo)) 
		{
			foreach ($Model->belongsTo as $assoc) 
			{
				if (!empty($assoc['counterCache'])) 
				{
					$updateCounterCache = true;
					break;
				}
			}
			if ($updateCounterCache) 
			{
				$keys = $Model->find( 'first', array(
					'conditions' => array($Model->alias . '.' . $Model->primaryKey => $id),
					'recursive' => -1,
					'callbacks' => false
				));
				
				$Model->updateCounterCache($keys[$Model->alias]);
			}
		}

    $Model->saveField( 'deleted', 1); 
    return false;
  }
  
}

?>
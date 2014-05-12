<?php

App::uses( 'StartupShell', 'Cofree.Console/Command');
    
class WebsysShell extends StartupShell
{

/**
 * Nombre de los grupos por defecto creados en la instalación
 * Si se pasa un valor al array, éstos son los aros a los que tiene acceso ese grupo
 */
  public $groups = array(
      'Member'
  );
  
  public $languages = array(
      'es' => 'Español', 
  );
  
  public function app()
  {
    $this->schemaCreate( 'app');
    $this->base();
  }
  
}
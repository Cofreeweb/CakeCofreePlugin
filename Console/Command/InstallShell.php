<?php

App::uses( 'StartupShell', 'Cofree.Console/Command');
    
class InstallShell extends StartupShell
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
  
/**
 * El título del sitio web
 * Se usará para la configuración por defecto en la base de datos
 *
 * @var string
 */
  public $siteTitle;
  
/**
 * El dominio del sitio
 * Solo se usa para colocarlo en el fichero core.php (no va en git)
 *
 * @var string
 */
  public $siteDomain;
  
  
/**
 * Realiza la instalación
 *
 * @return void
 */
  public function app()
  {
    $this->setDatabase();
    $this->setBootstrap();
    $this->__configs();
    $this->__controller();
    $this->base();
    
    $this->out( '=====================================');
    $this->out( '¡BIEN! INSTALACIÓN FINALIZADA');
    $this->out( '=====================================');
    $this->out( '');
    $this->out( 'Puedes acceder al website en http://'. $this->siteDomain);
    $this->out( 'Entra como administrador en http://'. $this->siteDomain . '/admin/users/login');
    $this->out( '');
    $this->out( '¡Feliz trabajo!');
  }
  
  
/**
 * General el fichero AppController.php tomándolo de la plantilla
 *
 * @return void
 */
  private function __controller()
  {
    $this->header( 'Creando AppController');
    $this->Template->templatePaths ['default'] = App::pluginPath( 'Cofree') . 'Console' .DS. 'Templates' .DS. 'default' .DS;
    $content = $this->Template->generate( 'controller', 'AppController');
    $filename = APP . 'Controller' .DS. 'AppController.php';
    $this->createFile( $filename, $content);
  }


/**
 * Genera los ficheros de configuración
 * uploads.php
 * routes.php
 * events.php
 * section.php
 * asset_compress.ini
 * management.php
 * core.php
 *
 * @return void
 */
  private function __configs()
  {
    $this->header( 'Creando ficheros de configuración');
    $this->Template->templatePaths ['default'] = App::pluginPath( 'Cofree') . 'Console' .DS. 'Templates' .DS. 'default' .DS;
    
    $this->siteTitle = $this->in( 'Indica un nombre para el web');
    $this->siteDomain = $this->in( 'Indica un dominio para el web');
    
    $this->Template->set( array(
        'site_title' => $this->siteTitle,
        'site_domain' => $this->siteDomain
    ));
    
    $configs = array(
        'upload' => 'upload.php',
        'routes' => 'routes.php',
        'events' => 'events.php',
        'management' => 'management.php',
        'core' => 'core.php'
    );
    
    foreach( $configs as $key => $file)
    {
      $content = $this->Template->generate( 'config', $key);

      $filename = APP . 'Config' .DS. $file;
      $this->createFile( $filename, $content);
    }
  }

  
}
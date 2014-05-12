<?php

class StartupShell extends AppShell
{
  
/**
 * Nombre de los grupos por defecto de la aplicación
 */
  public $groups = array();
  
  
/**
 * Idiomas por defecto
 *
 * @var string
 */
  public $languages = array();
  
  
/**
 * La conexión a la base de datos cuando se hacen los schemas
 *
 * @var string
 */
  public $dbConnection = 'default';
  
  
  public function startup()
  {
    if( !empty( $this->params ['connection']))
    {
      $this->dbConnection = $this->params ['connection'];
    }
  }
  
/**
 * Lanza un comando system() en el shell
 *
 * @param string $cmd 
 * @return void
 */
  public function cmd( $cmd)
  {
    $this->out( $cmd);
    system( $cmd);
  }
  
/**
 * Lanza un comando de creación de tabla de la base de datos
 *
 * @param string $cmd 
 * @example $this->schemaCreate( 'tabla')
 * @return void
 */
  public function schemaCreate( $cmd)
  {
    $this->cmd( 'bin/cake schema create '. $cmd . ' -c '. $this->dbConnection);
  }
  
/**
 * Conjunto de comandos para iniciar un proyecto
 *
 * @return void
 */
  public function base()
  {
    $plugins = App::objects( 'plugin');
    
    $this->out( "************************************");
    $this->out( "Instalando base de datos");
    $this->out( "************************************");
    
    // Plugin I18n
    if( in_array( 'I18n', $plugins))
    {
      $this->schemaCreate( 'i18n');
      $this->schemaCreate( 'i18n locales --plugin I18n');
    }
    
    // Plugin Upload
    if( in_array( 'Upload', $plugins))
    {
      $this->schemaCreate( 'Upload.upload');
    }
    
    // Plugin Comments
    if( in_array( 'Comments', $plugins))
    {
      $this->schemaCreate( '--plugin Comments --name comments');
    }
    
    // Plugin Setting
    if( in_array( 'Setting', $plugins))
    {
      $this->schemaCreate( '--plugin Setting --name settings');
    }
    
    if( in_array( 'I18n', $plugins) && !empty( $this->languages))
    {
      $this->languages();
    }
    
    // Plugin Section
    if( in_array( 'Section', $plugins))
    {
      $this->schemaCreate( 'Section.section');
    }
    
    // Plugin Configuration
    if( in_array( 'Configuration', $plugins))
    {
      $this->schemaCreate( 'Configuration.configuration');
    }
    
    // Plugin Configuration
    if( in_array( 'Dictionary', $plugins))
    {
      $this->schemaCreate( 'Dictionary.dictionaries');
    }
    
    // Plugin Configuration
    if( in_array( 'Rating', $plugins))
    {
      $this->schemaCreate( 'Rating.ratings');
    }
    
    // Plugin Acl (por defecto tiene que estar siempre activo)
    $this->schemaCreate( 'Acl.acl');
    
    // Sincronización de los controllers/actions de ACL
    $this->out( 'Sincronizando acl...');
    $this->cmd( 'bin/cake Acl.acl_mgm sync');
        
    // Creando grupo Admin
    $Group = ClassRegistry::init( array(
        'class' => 'Acl.Group',
        'ds' => $this->dbConnection
    ));
    
    $Group->create();
    $Group->save( array(
        'name' => 'Admin',
        'level' => 1
    ));
    
    // Otorgando permisos a Controller para el grupo Admin
    $ArosAco = ClassRegistry::init( array(
        'class' => 'ArosAco',
        'ds' => $this->dbConnection
    ));
    
    $ArosAco->save( array(
        'aro_id' => 1,
        'aco_id' => 1,
        '_create' => 1,
        '_read' => 1,
        '_update' => 1,
        '_delete' => 1
    ));
    
    $level = 100;
    
    foreach( $this->groups as $group => $acos)
    {
      if( !is_array( $acos))
      {
        $group = $acos;
      }
      
      $Group->create();
      $Group->save( array(
          'name' => $group,
          'level' => $level
      ));
      
      $level += 100;
      
      if( is_array( $acos))
      {
        $Aco = ClassRegistry::init( array(
            'class' => 'Aco',
            'ds' => $this->dbConnection
        ));
        $Aro = ClassRegistry::init( array(
            'class' => 'Aro',
            'ds' => $this->dbConnection
        ));
        
        $node = $Aro->node( array(
            'foreign_key' => $Group->id,
            'model' => 'Group'
        ));
        
        $aro_id = $node [0]['Aro']['id'];
        
        foreach( $acos as $aco)
        {
          $node = $Aco->node( $aco);
          $aco_id = $node [0]['Aco']['id'];
          
          if( $node)
          {
            $ArosAco->create();
            
            $ArosAco->save( array(
                'aro_id' => $aro_id,
                'aco_id' => $aco_id,
                '_create' => 1,
                '_read' => 1,
                '_update' => 1,
                '_delete' => 1
            ));
          }
        }
      }
    }
    
    // Añade un usuario
    $this->cmd( 'bin/cake acl.acl_mgm add_user');
  }
  
  
  public function languages()
  {
    $this->out( 'Creando idiomas...');
    
    App::uses( 'Language', 'I18n.Model');
    
    // Creando idiomas por defecto
    $Language = ClassRegistry::init( array(
        'class' => 'I18n.Language',
        'ds' => $this->dbConnection
    ));
    
    foreach( $this->languages as $lang => $name)
    {
      $Language->create();
      $Language->save( array(
          'iso2' => $lang,
          'name' => $name
      ));      
    }
  }
  
/**
 * Define las opciones del shell
 * Ver en core Console/ConsoleOptionParser::addOption()
 *
 * @return void
 */
  public function getOptionParser()
  {
    $parser = parent::getOptionParser();
    $parser->addOption( 'connection', array(
        'short' => 'c'
    ));
    
    return $parser;
   
  }
}
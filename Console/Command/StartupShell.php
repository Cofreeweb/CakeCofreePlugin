<?php

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class StartupShell extends AppShell
{
  
  public $tasks = array( 'Template');
  
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
 * Da una salida de una cabecera
 *
 * @param string $text 
 * @return void
 */
  public function header( $text)
  {
    $this->hr();
    $this->out( '###########################################');
    $this->out( strtoupper( $text));
    $this->out( '###########################################');
    $this->hr();
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
    $this->create_database();
    
    CakePlugin::load( 'Acl', array( 'bootstrap' => true, 'routes' => true));
    
    // Plugin Acl (por defecto tiene que estar siempre activo)
    $this->schemaCreate( 'Acl.acl');
    
    // Sincronización de los controllers/actions de ACL
    $this->out( 'Sincronizando acl...');
    $this->cmd( 'bin/cake Acl.acl_mgm sync');
        
    $this->cmd( 'bin/cake Cofree.startup create_groups');
    $this->cmd( 'bin/cake Cofree.startup create_slinks');
  }

  public function create_database()
  {
    $plugins = App::objects( 'plugin');
    
    $this->header( 'Creando tablas en las bases de datos');
    
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
  }
  
  public function create_groups()
  {
    // Creando grupo Admin
    $Group = ClassRegistry::init( array(
        'class' => 'Acl.Group',
        'ds' => $this->dbConnection
    ));
    
    $Group->create();
    $Group->save( array(
        'name' => 'Admin',
        'level' => 1,
        'permissions' => '*'
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
    CakePlugin::load( 'I18n', array( 'bootstrap' => true, 'routes' => true));
    
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
  
/**
 * Escribe el fichero database.php
 *
 * @return void
 */
  public function setDatabase()
  {
    $this->header( 'Configurando las bases de datos');
    $this->dbs = array();
    $this->Template->templatePaths ['default'] = App::pluginPath( 'Cofree') . 'Console' .DS. 'Templates' .DS. 'default' .DS;
    
    $this->__setDB();
    
		$filename = APP . 'Config' .DS. 'database.php';
				
		if( !empty( $this->dbs))
		{
		  $this->Template->set( array(
  		    'dbs' => implode( "\n\n", $this->dbs)
  		));
  		
		  $contents = $this->Template->generate( 'config', 'database');
		  $this->createFile( $filename, $contents);
		}
		
  }
  
/**
 * Toma el contenido de una configuración de database para que sea escrita posteriormente en database.php
 *
 * @return void
 */
  private function __setDB()
  {    
    $connectors = array(
        '1' => 'mysql',
        '2' => 'mongodb'
    );
    
    $this->out( '1. Mysql');
    $this->out( '2. MongoDB');
    
    $db_driver = $this->in( 'Selecciona un conector', false, '1');
    
    if( !isset( $connectors [$db_driver]))
    {
      $this->out( 'El conector seleccionado no existe');
      die();
    }
    
    $db_default = $connectors [$db_driver] == 'mysql' ? 'default' : 'mongo';
    
    $db_name = $this->in( 'Nombre de la conexión (variable de PHP)', false, $db_default);
    $db_database = $this->in( 'Nombre de la base de datos');
    $db_login = $this->in( 'Nombre de usuario', false, 'root');
    $db_password = $this->in( 'Contraseña', false, 'root');
    
    $this->Template->set( compact( array(
        'db_database',
        'db_login',
        'db_password',
        'db_name'
    )));
    
    $content = $this->Template->generate( 'config', '_'. $connectors [$db_driver]);
    $this->dbs [] = $content;
    
    $other = $this->in( '¿Deseas definir otra base de datos', array(
        's',
        'n'
    ));
    
    if( $other == 's')
    {
      $this->__setDB();
    }
    else
    {
      return;
    }
  }
  
/**
 * Crea el fichero bootstrap.php
 *
 * @return void
 */
  public function setBootstrap()
  {
    $this->header( 'Creando fichero de bootstrap.php');
    Configure::load( 'plugins');
    
    $plugins = Configure::read( 'AppPlugins');
    $plugins_path = App::path( 'plugins');
    $plugins_path = $plugins_path [0];
    
    $contents = array();
    
    foreach( $plugins as $plugin)
    {
      $array = array();
      
      $plugin_path = $plugins_path . $plugin;
      
      if( !is_dir( $plugin_path))
      {
        continue;
      }
      
      if( file_exists( $plugins_path . $plugin .DS. 'Config' .DS. 'bootstrap.php'))
      {
        $array [] = "'bootstrap' => true";
      }
      
      if( file_exists( $plugins_path . $plugin .DS. 'Config' .DS. 'routes.php'))
      {
        $array [] = "'routes' => true";
      }
      
      $_content = "CakePlugin::load( '". $plugin ."'";
      
      if( !empty( $array))
      {
        $_content .= ", array( ". implode( ', ', $array) .")";
      }
      
      $_content .= ")";
      
      $contents [] = $_content;
    }
    
    $this->Template->templatePaths ['default'] = App::pluginPath( 'Cofree') . 'Console' .DS. 'Templates' .DS. 'default' .DS;
    $this->Template->set( 'plugins', implode( ";\n", $contents) .';');
    $content = $this->Template->generate( 'config', 'bootstrap');
    
    $filename = APP . 'Config' .DS. 'bootstrap.php';
    $this->createFile( $filename, $content);
  }
  

  
/**
 * Crea los enlaces simbólicos para los assets de los plugins y los themes
 *
 * @return void
 */
  public function create_slinks()
  {
    $this->header( 'Creando enlaces simbólicos');
    $plugins = CakePlugin::loaded();
    $plugins_path = App::path( 'plugins');
    $plugins_path = $plugins_path [0];
    
    // Fichero de .gitignore para añadir los enlaces simbólicos
    $gitignore = new File( ROOT .DS. '.gitignore');
    
    
    foreach( $plugins as $plugin)
    {
      $webroot = $plugins_path . $plugin .DS. 'webroot';
      $dest = WWW_ROOT . Inflector::underscore( $plugin);
      
      if( is_dir( $webroot) && !file_exists( $dest))
      {
        $cmd = 'ln -s '. $webroot . ' '. $dest;
        $this->cmd( $cmd);
        
        // Añade la linea en .gitignore para ignorar ese fichero
        $gitignore->append( "\n" . str_replace( ROOT . DS, '', $dest));
      }
    }
    
    $themes = App::path( 'views');
    $themes = $themes [0];
    $path_themed = $themes . 'Themed';
    
    $Folder = new Folder( $path_themed);
    
    list( $dirs, $files) = $Folder->read( false, true);
    
    foreach( $dirs as $dir)
    {
      if( !is_dir( WWW_ROOT . 'theme'))
      {
        $Folder->create( WWW_ROOT . 'theme');
        $gitignore->append( "\n" . str_replace( ROOT . DS, '', WWW_ROOT .'theme' .DS.'*'));
      }
      
      $theme_dir = App::themePath( $dir);

      $webroot = $theme_dir . 'webroot';
      $dest = WWW_ROOT . 'theme' .DS. $dir;
      
      if( !file_exists( $dest))
      {
        $cmd = 'ln -s '. $webroot . ' '. $dest;
        $this->cmd( $cmd);
        $gitignore->append( "\n" . str_replace( ROOT . DS, '', $dest));
      }
    }
  }
}
<?php
/**
 * GitShell
 *
 * Shell de utilidades para gestionar un proyecto CakePHP con git
 *
 * Necesario usar en Config del proyecto un fichero plugins con el siguiente formato
 * Estos serán los plugins usados por la aplicación
 *
 * $config ['AppPlugins'] = array(
 *     array(
 *         "name" => "PluginName",
 *         "url" => "https://github.com/Dir/PluginName.git",
 *         "branch" => "master" (u otro branch)
 *     ),
 * );
 *
 * @author Alfonso Etxeberria
 */

class GitShell extends AppShell 
{

/**
 * El listado de plugins
 *
 * @access private
 */
  private $plugins = array();
  
/**
 * El directorio de los plugins
 *
 * @access private
 */
  private $pluginsDir = null;
  
/**
 * Plugins disponibles
 *
 */
  private $availablePlugins = array(
      array(
          "name" => "Cofree",
          "url" => "https://github.com/Cofreeweb/CakeCofreePlugin.git",
          "branch" => "master"
      ),
      array(
          "name" => "Acl",
          "url" => "https://github.com/Cofreeweb/CakeAclPlugin.git",
          "branch" => "master"
      ),
      array(
          "name" => "Comments",
          "url" => "https://github.com/Cofreeweb/comments.git",
          "branch" => "master"
      ),
      array(
          "name" => "I18n",
          "url" => "https://github.com/Cofreeweb/CakeI18nPlugin.git",
          "branch" => "master"
      ),
      array(
          "name" => "Upload",
          "url" => "https://github.com/Cofreeweb/CakeUploadPlugin.git",
          "branch" => "master"
      ),
      array(
          "name" => "Website",
          "url" => "https://github.com/Cofreeweb/CakeWebsitePlugin.git",
          "branch" => "master"
      ),
      array(
          "name" => "Management",
          "url" => "https://github.com/Cofreeweb/CakeManagementPlugin.git",
          "branch" => "master"
      ),
      array(
          "name" => "Search",
          "url" => "https://github.com/Cofreeweb/search.git",
          "branch" => "master"
      ),
      array(
          "name" => "Weather",
          "url" => "https://github.com/Cofreeweb/CakeWeatherPlugin.git",
          "branch" => "master"
      ),
      array(
          "name" => "Geocoder",
          "url" => "https://github.com/Cofreeweb/CakeGeocoderPlugin.git",
          "branch" => "master"
      )
  );
  
  
  private $folders = array(
      'webroot/files/photos'
  );
  
  private $ignoreFolders = array(
      'tmp/cache/models',
      'tmp/cache/persistent',
      'tmp/cache/views',
      'tmp/sessions',
      'tmp/log',
      'tmp/tests',
      'webroot/files/photos'
  );
  
  private $appDir = null;

/**
 * Callback de Shell
 * Setea las propiedades del objeto
 *
 * @return void
 */
  public function initialize() 
  {
    $app = str_replace( ROOT .'/', '', APP);
    
    if( !file_exists( APP . 'Config' .DS. 'plugins.php'))
    {
      $this->generatePluginsConfig();
    }
    
		Configure::load( 'plugins');
		$this->plugins = Configure::read( 'AppPlugins');
		$this->pluginsDir = $app . 'Plugin'. DS;
		$this->appDir = str_replace( ROOT .'/', '', APP);
	}
	
	private function generatePluginsConfig()
	{
	  App::uses('File', 'Utility');
	  
	  $filecontent = "<?php\n\$config ['AppPlugins'] = array(\n";
	  
	  foreach( $this->availablePlugins as $plugin)
	  {
	    $bool = $this->in( "¿Quieres usar el plugin ". $plugin ['name'] . "?", array( 'y', 'n'), 'y');
	    
	    if( $bool == 'y')
	    {
	      $filecontent .= "  array(\n    'name' => '{$plugin ['name']}',\n    'url' => '{$plugin ['url']}',\n    'branch' => '{$plugin ['branch']}'\n  ),\n";
	    }
	  }
	  
	  $filecontent .= ");\n";
	  
	  $File = new File( APP . 'Config' .DS. 'plugins.php');
	  $File->write( $filecontent);
	}
	
/**
 * Ejectua un comando de shell
 *
 * @param string $cmd El comando a usar
 * @example GitShell::ex( 'git status')
 * @return void
 */
  private function ex( $cmd)
  {
    $this->out( $cmd);
    exec( $cmd);
  }
  
  
/**
 * Ejecuta un comando git que afecta a un plugin concreto
 *
 * @param string $plugin El plugin en el que se ejectuará el comando
 * @param string $cmd El comando a ejectuar
 * @example GitShell::gitPlugin( 'Management', 'status')
 * @return void
 */
  private function gitPlugin( $plugin, $cmd)
  {
    $this->ex( 'git --git-dir='. $this->pluginsDir . $plugin .'/.git '. $cmd);
  }
 
/**
 * Comando Shell
 * Inicializa la aplicación
 * Es necesario ejectuarlo una vez creado el proyecto en el repositiorio externo
 *
 * @example bin/cake cofree.git install
 * @return void
 */
	public function install()
	{
	  $this->createFoldersFiles();
	  $this->ignore();
	  $url = $this->in( "Escribe la URL del repositorio");
	  $this->ex( 'touch README.md');
	  $this->ex( 'git init');
	  $this->ex( 'git commit -a -m "first commit"');
	  $this->ex( 'git remote add origin '. $url);
	  $this->ex( 'git push -u origin master');
	  
	  $this->init();

	  $this->create_plugins();
	  $this->__commit( 'Creado plugins', 'master');
	}
  
/**
 * Inicializa el proyecto después de un git clone
 *
 * @example bin/cake cofree.git init
 * @return void
 */
  public function init()
  {    
    $this->copyfiles();
	  $this->change_mod();
  }
  
/**
 * Cambia el mod a 777 de los directorios necesarios
 *
 * @example bin/cake cofree.git change_mod
 * @return void
 */
  public function change_mod()
  {
    $this->ex( 'chmod 777 '. $this->appDir . 'tmp/logs');
    $this->ex( 'chmod 777 '. $this->appDir . 'tmp/sessions');
    $this->ex( 'chmod 777 '. $this->appDir . 'tmp/cache/models');
    $this->ex( 'chmod 777 '. $this->appDir . 'tmp/cache/persistent');
    $this->ex( 'chmod 777 '. $this->appDir . 'tmp/cache/views');
    $this->ex( 'chmod 777 '. $this->appDir . 'webroot/files/photos');
  }
  
/**
 * Copia los ficheros ignorados en el repositorio git
 *
 * @example bin/cake cofree.git copyfiles
 * @return void
 */
  public function copyfiles()
  {
    $this->ex( 'cp '. $this->appDir . 'Config/core.php.default ' . $this->appDir . 'Config/core.php');
	  $this->ex( 'cp '. $this->appDir . 'Config/database.php.default ' . $this->appDir . 'Config/database.php');
	  $this->ex( 'cp '. $this->appDir . 'Config/email.php.default ' . $this->appDir . 'Config/email.php');
  }
  
/**
 * Ver pluginCheckout()
 *
 * @example bin/cake cofree.git pl_checkout
 * @return void
 */
  public function pl_checkout()
  {
    if( !isset( $this->args [0]))
    {
      $this->out( 'Es necesario indicar como primer argumento un plugin');
      die();
    }
    
    $plugin = $this->args [0];
        
    if( !isset( $this->plugins [$plugin]))
    {
      $this->out( "El plugin indicado no existe en la configuración. Asegúrate que has usado correctamente las mayúsculas.");
    }
    
    if( !isset( $this->args [1]))
    {
      $branch = !isset( $this->args [1]) 
        ? $this->plugins [$plugin] 
        : $this->args [1];
    }
    
    $this->pluginCheckout( $plugin, $branch);
  }

	
/**
 * Crea los plugins indicados en Configure::read( 'AppPlugins')
 *
 * @return void
 * @example bin/cake cofree.git create_plugins
 */
	public function create_plugins()
	{
	  foreach( $this->plugins as $plugin)
	  {
	    if( !CakePlugin::loaded( $plugin  ['name']))
  	  {
        $this->ex( 'git submodule add '. $plugin ['url'] .' '. $this->pluginsDir . $plugin ['name']);
  	    $this->pluginCheckout( $plugin ['name'], $plugin ['branch']);
  	    $this->gitPlugin( $plugin ['name'], 'remote set-url origin ' . $plugin ['url']);
  	  }
	  }
	}
	
/**
 * Actualiza los plugins indicados en Configure::read( 'AppPlugins')
 *
 * @return void
 * @example bin/cake cofree.git update_plugins
 */
	public function update_plugins()
	{
	  foreach( $this->plugins as $plugin)
	  {
	    $this->pluginCheckout( $plugin ['name'], $plugin ['branch']);
	    $this->gitPlugin( $plugin ['name'], 'pull');
	  }
	}

/**
 * Hace un commit y un push
 * Se puede indicar como argumento el nombre del branch (master por defecto)
 *
 * @example bin/cake cofree.git commit 0.3
 * @return void
 */
  public function commit()
  {
    $branch = !empty( $this->args [0]) ? $this->args [0] : 'master';

    $msg = $this->in( "Escribe un mensaje");

    if( empty( $msg))
    {
      $this->out( "Es necesario indicar un mensaje");
      die();
    }

    $this->__commit( $msg, $branch);
  }
  
  private function __commit( $msg, $branch)
  {
    $this->ex( 'git add *');
    $this->ex( 'git commit -a -m "'. $msg .'"');
    $this->ex( 'git push -u origin '. $branch);
  }

/**
 * Hace el commit&push de un plugin, que ha de indicarse como primer parámetro
 * También se puede indicar como segundo parámetro el nombre del branch (master por defecto)
 *
 * @example bin/cake cofree.git pl_commit Management 2.0
 * @return void
 */
  public function pl_commit()
  {    
    if( !isset( $this->args [0]))
    {
      $this->out( 'Es necesario indicar como primer argumento un plugin');
      die();
    }
    
    $msg = $this->in( "Escribe un mensaje");

    if( empty( $msg))
    {
      $this->out( "Es necesario indicar un mensaje");
      die();
    }

    $branch = !empty( $this->args [1]) ? $this->args [1] : 'master';
    $plugin = $this->args [0];
    $config = $this->__getConfig( $plugin);
    $this->__checkPlugin( $plugin);
    $this->pluginCheckout( $plugin, $branch);
    $this->gitPlugin( $config ['name'], 'remote set-url origin ' . $config ['url']);
    $this->gitPlugin( $plugin, 'commit -a -m "'. $msg .'"');
    $this->gitPlugin( $plugin, 'push -u origin '. $branch);
  }
/**
 * Hace un checkout del branch en cada plugin, atendiendo a lo indicado en Configure::read( 'AppPlugins')
 *
 * @param string $plugin 
 * @param string $branch 
 * @return void
 */
	private function pluginCheckout( $plugin, $branch)
	{
	  $this->gitPlugin( $plugin, 'checkout '. $branch);
	}
  
  private function createFoldersFiles()
  {
    App::uses('Folder', 'Utility');
    App::uses('File', 'Utility');
    
    foreach( $this->folders as $folder)
    {
      new Folder( APP . $folder, true, 0777);
    }
    
    $this->ex( 'cp '. $this->appDir . 'Config/core.php ' . $this->appDir . 'Config/core.php.default');
    $this->ex( 'rm '. $this->appDir . 'Config/core.php');
  }
  
  private function ignore()
  {
    App::uses('Folder', 'Utility');
    App::uses('File', 'Utility');
    
    foreach( $this->ignoreFolders as $folder)
    {
      $File = new File( APP . $folder .'/.gitignore');
      $File->write( "*\n!.gitignore");
    }
    
    $File = new File( '.gitignore');
    $content =  $this->appDir ."Config/database.php\n" .
                $this->appDir ."Config/core.php\n" .
                $this->appDir ."Config/email.php\n";
    
    $File->write( $content);
  }
 
/**
 * Verifica que el plugin existe en la configuración
 *
 * @param string $plugin 
 * @return boolean
 */
	private function __checkPlugin( $plugin)
	{
	  $exists = false;
	  
	  foreach( $this->plugins as $_plugin)
	  {
	    if( $_plugin ['name'] == $plugin)
	    {
	      $exists = true;
	      break;
	    }
	  }
	  
	  if( !$exists)
    {
      $this->out( "El plugin $plugin no existe en la configuración. Asegúrate que has usado correctamente las mayúsculas.");
      die();
    }

    return true;
	}
	
/**
 * Devuelve la configuración de un plugin, dado el nombre del mismo
 *
 * @param string $name 
 * @return array
 */
	private function __getConfig( $name)
	{
	  foreach( $this->plugins as $plugin)
	  {
	    if( $plugin ['name'] == $name)
	    {
	      return $plugin;
	    }
	  }
	  
	  return false;
	}
}
?>
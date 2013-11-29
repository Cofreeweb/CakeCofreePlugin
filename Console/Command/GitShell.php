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
    system( $cmd);
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
 * @example Console/cake cofree.git install
 * @return void
 */
	public function install()
	{
	  $this->createFoldersFiles();
	  $this->ignore();
	  $url = $this->in( "Escribe la URL del repositorio");
	  $this->ex( 'touch README.md');
	  $this->ex( 'git init');
	  $this->ex( 'git add README.md');
	  $this->ex( 'git commit -m "first commit"');
	  $this->ex( 'git remote add origin '. $url);
	  $this->ex( 'git push -u origin master');
	}

/**
 * Ver pluginCheckout()
 *
 * @example Console/cake cofree.git pl_checkout
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
 * @example Console/cake cofree.git create_plugins
 */
	public function create_plugins()
	{
	  foreach( $this->plugins as $plugin)
	  {
	    $this->ex( 'git submodule add '. $plugin ['url'] .' '. $this->pluginsDir . $plugin ['name']);
	    $this->pluginCheckout( $plugin ['name'], $plugin ['branch']);
	  }
	}
	
/**
 * Actualiza los plugins indicados en Configure::read( 'AppPlugins')
 *
 * @return void
 * @example Console/cake cofree.git update_plugins
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
      $this->ex( 'cp '. $this->appDir . 'Config/core.php ' . $this->appDir . 'Config/core.php.default');
    }
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
    $File->write( $this->appDir ."Config/database.php\n");
    $File->write( $this->appDir ."Config/core.php\n");
    $File->write( $this->appDir ."Config/email.php\n");
  }
}
?>
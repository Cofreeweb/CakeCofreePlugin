<?= '<?php' ?>

  
/**
 * Importante! Es necesario que se lean primeramente las rutas de Section
 */
  
  
  Router::connect( '/:lang', array(
	    'controller' => 'pages', 
	    'action' => 'display',
	    'home' 
	), array('routeClass' => 'I18nRoute'));
	
  Router::parseExtensions( 'json', 'jpg', 'png');
	
	Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
	
  
	
/**
 * ...and connect the rest of 'Pages' controller's URLs.
 */
	Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'), array('routeClass' => 'I18nRoute'));

/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();
  
 
/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';

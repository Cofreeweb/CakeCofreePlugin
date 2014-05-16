<?= '<?php' ?>

// Setup a 'default' cache configuration for use in the application.
Cache::config('default', array('engine' => 'File'));
Cache::config('dictionaries', array('engine' => 'File'));

Configure::write('Dispatcher.filters', array(
	'AssetDispatcher',
	'CacheDispatcher'
));

/**
 * Configures default file logging options
 */
App::uses('CakeLog', 'Log');
CakeLog::config('debug', array(
	'engine' => 'File',
	'types' => array('notice', 'info', 'debug'),
	'file' => 'debug',
));
CakeLog::config('error', array(
	'engine' => 'File',
	'types' => array('warning', 'error', 'critical', 'alert', 'emergency'),
	'file' => 'error',
));

define('DEFAULT_LANGUAGE', 'spa'); // The 3 letters code for your default language


Configure::load( 'upload');
Configure::load( 'events');

<?= $plugins ?>

App::uses('CofreeEventManager', 'Cofree.Event');
CofreeEventManager::loadListeners();


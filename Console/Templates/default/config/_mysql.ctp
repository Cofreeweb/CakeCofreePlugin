public $<?= $db_name ?> = array(
	'datasource' => 'Database/Mysql',
	'persistent' => false,
	'host' => 'localhost',
	'login' => '<?= $db_login ?>',
	'password' => '<?= $db_password ?>',
	'database' => '<?= $db_database ?>',
	'prefix' => '',
  'encoding' => 'utf8',
);
public $<?= $db_name ?> = array(
	'datasource' => 'Mongodb.MongodbSource',
  'host' => 'localhost',
  'database' => '<?= $db_database ?>',
  'port' => 27017,
  'prefix' => '',
  'persistent' => 'true',
);
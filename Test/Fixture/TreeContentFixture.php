<?php


/**
 * TreeContentFixture class
 *
 * @package       Cofree.Test.Fixture
 */
class TreeContentFixture extends CakeTestFixture {

/**
 * fields property
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'parent_id' => array('type' => 'integer'),
		'lft' => array('type' => 'integer'),
		'rght' => array('type' => 'integer'),
		'name' => array('type' => 'string', 'length' => 255, 'null' => false)
	);

/**
 * records property
 *
 * @var array
 */
	public $records = array(
		array('id' => 1, 'parent_id' => null, 'lft' => 1, 'rght' => 2, 'name' => 'One'),
		array('id' => 2, 'parent_id' => 1, 'lft' => 3, 'rght' => 4, 'name' => 'Two'),
		array('id' => 3, 'parent_id' => 2, 'lft' => 5, 'rght' => 6, 'name' => 'Three'),
		array('id' => 4, 'parent_id' => 1, 'lft' => 7, 'rght' => 12, 'name' => 'Four'),
		array('id' => 5, 'parent_id' => null, 'lft' => 8, 'rght' => 9, 'name' => 'Five'),
		array('id' => 6, 'parent_id' => null, 'lft' => 10, 'rght' => 11, 'name' => 'Six'),
		array('id' => 7, 'parent_id' => null, 'lft' => 13, 'rght' => 14, 'name' => 'Seven')
	);
}

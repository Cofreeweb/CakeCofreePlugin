<?php
App::uses('JsonableBehavior', 'Cofree.Model/Behavior');
App::uses('Model', 'Model');
App::uses('AppModel', 'Model');

class TreeContent extends CakeTestModel 
{
	public $name = 'TreeContent';

	public $actsAs = array( 'Cofree.Jsonable');
}

/**
 * JsonableBehavior Test Case
 *
 */
class JsonableBehaviorTest extends CakeTestCase 
{
    
  public $fixtures = array(
    'plugin.Cofree.tree_content'
  );
  
/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Jsonable = new JsonableBehavior();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Jsonable);

		parent::tearDown();
	}
  
  
  public function testBuildTreeForJson()
  {
    $Model = new TreeContent();
    $contents = $Model->find( 'threaded');
    $contents = $Model->buildTreeForJson( $contents);
  
    $expected = array(
  		'id' => '1',
  		'parent_id' => null,
  		'lft' => '1',
  		'rght' => '2',
  		'name' => 'One',
  		'items' => array(
  			array(
  				'id' => '2',
  				'parent_id' => '1',
  				'lft' => '3',
  				'rght' => '4',
  				'name' => 'Two',
  				'items' => array(
  					array(
  						'id' => '3',
  						'parent_id' => '2',
  						'lft' => '5',
  						'rght' => '6',
  						'name' => 'Three',
  						'items' => array()
  					)
  				)
  			),
  			array(
  				'id' => '4',
  				'parent_id' => '1',
  				'lft' => '7',
  				'rght' => '12',
  				'name' => 'Four',
  				'items' => array()
  			)
  		)
  	);
    
    $this->assertEquals( $expected, $contents [0]);
  }
}

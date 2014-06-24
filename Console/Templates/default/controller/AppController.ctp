<?= '<?php' ?>

App::uses( 'Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller 
{
  public $helpers = array(
		'Session',
		'Management.AdminNav',
		'Acl.Auth',
		'Management.Inline',
		'Cofree.Chtml',
	);
  
  public $components = array(
      'Acl',
      'Auth' => array(
          'authenticate' => array(
              'Form' => array(
                  'fields' => array('username' => 'email'),
                  'scope'  => array('User.status' => 1)
              )
          ),
          'authorize' => array(
              'Actions' => array( 'actionPath' => 'controllers')
          ),
          'loginAction' => array(
              'plugin' => 'acl',
              'controller' => 'users',
              'action' => 'login'
          )
      ),
      'Session',
      'Management.Manager',
      'RequestHandler',
      'Acl.AclAccess'
  );
  
}

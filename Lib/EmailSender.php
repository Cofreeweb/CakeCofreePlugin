<?php

/**
 * Se encarga de enviar mails
 * Es necesario implementar esta clase usando funciones de envio
 *
 */
App::uses('CakeEmail', 'Network/Email');

class EmailSender extends Object
{

/**
 * Representa el nombre de la class que implementa a EmailSender
 * Es modificada por el método configure() de la class que lo implementa
 */
  public static $name;
  
/**
 * Configuración de la class
 * Una vez declarada la class que implementa a EmailSender, es necesario llamar (en el mismo fichero) a 
 * EmailSender::configure( array(
 *    'class' => 'NombreClase',
 *     
 * ))
 *
 * @param array $data 
 * @return void
 */
  public function configure( $data)
  {
    self::$name = $data ['class'];
  }
  
  public function getInstance()
  {
    $Email = new CakeEmail( 'default');
    $Email->domain( Configure::read( 'Config.domain'));
    return $Email;
  }
  
  public function config( CakeEmail $Email, $key = false)
  {
    if( $key === false)
    {
      $key = 'notify';
    }
    
    $config = Configure::read( 'Email.'. $key);
    $Email->from( $config ['from']);
    return $Email;
  }
  
  public function sendMail( CakeEmail $Email)
  {
    try {
      return $Email->send();
    } catch (Exception $e) {
      $template = $Email->template();
      $to = $Email->to();
      $from = $Email->from();
      $result = sprintf( "Error al enviar un mail a %s.\n
      De: %s\n
      Tema: %s\n
      Template: %s\n
      Error: %s\n
      Servidor: %s", 
      current( $to),
      current( $from),
      $Email->subject(),
      $template ['template'] .' ('. $template ['layout'] .')',
      $e->getMessage(),
      gethostname());
      $this->log( $result, LOG_DEBUG);
    }
  }
  
  public function send( $method)
  { 
    $args	= func_get_args();
    $method = $args [0];
    $Email = self::getInstance();
    unset( $args [0]);
    $args [0] = $Email;
    ksort( $args);
    call_user_func_array( array( self::$name, $method), $args);
    return self::sendMail( $Email);
  }


/**
 * Enviado después del registro
 *
 * @param array data $user 
 * @return void
 * @since Ebident 0.1
 */
  protected function registration( CakeEmail $Email, $user)
  {
    $Email->subject( __( "¡Ebident te da la bienvenida!"));
        
    $link = Router::url( array(
        'plugin' => 'acl_management',
        'controller' => 'users',
        'action' => 'confirm_register',
        $user ['User']['id'],
        $user ['User']['key']
    ), true);
		
		$Email->viewVars( compact( 'link'));
		
    $Email->template( 'Users/registration', 'users');
    $Email->to( $user ['User']['email']);
    $Email->viewVars( compact( 'user'));
  }
  
}
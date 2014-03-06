<?php

/**
 * Se encarga de enviar mails
 * Es necesario implementar esta clase usando funciones de envio
 *
 */
App::uses('CakeEmail', 'Network/Email');

class EmailSender
{

/**
 * Representa el nombre de la class que implementa a EmailSender
 * Es modificada por el método configure() de la class que lo implementa
 */
  public static $name;
  
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
  
  public function changeLanguage( $lang)
  {
    Configure::write( 'Config.language', $lang);
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
      CakeLog::write( $result, LOG_DEBUG);
    }
  }
  
  public static function send( $method)
  { 
    $args	= func_get_args();
    $method = $args [0];
    $Email = self::getInstance();
    unset( $args [0]);
    $args [0] = $Email;
    ksort( $args);
    call_user_func_array( array( get_called_class(), $method), $args);
    
    if( Configure::read( 'debug') > 0)
    {
      return self::sendMail( $Email);
    }
    else
    {
      return self::sendMail( $Email);
      // ClassRegistry::init( 'Cofree.RabbitMail')->publish( serialize( $Email), 'email_sender.email_sender', AMQP_DURABLE, 'email_sender');      
    }
  }
  
  public static function consume()
  {
    $queue = ClassRegistry::init( 'Cofree.RabbitMail')->consumeQueue( 'email_sender.email_sender', 'email_sender', AMQP_DURABLE);
    
    $queue->consume( function( $envelope, $queue){
        $response = $envelope->getBody();
        $queue->ack( $envelope->getDeliveryTag());
        
        // Creamos una instancia para que se autocarguen todas las clases necesarias. 
        // Si no lo hacemos el objeto unserializado estará incompleto.
        new CakeEmail( 'default');
        $Email = unserialize( $response);
        
        if( $Email instanceof CakeEmail)
        {
          EmailSender::sendMail( $Email);
        }
    });
  }

  
}
<?php
/**
 * RabbitMail Model
 * 
 **/
class RabbitMail extends AppModel 
{
  public $name = 'RabbitMail';
  public $useTable = false;
  public $useDbConfig = 'rabbit';

}

class RabbitMailResponse extends AppModel 
{
  public $name = 'RabbitMailResponse';
  public $useTable = false;
  public $useDbConfig = 'rabbit2';

}
?>

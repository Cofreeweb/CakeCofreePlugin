<?php
/**
 * ChtmlHelper
 * 
 * Utilidades para el HTML
 *
 * @package Cofree.View.Helper
 **/

class ChtmlHelper extends AppHelper 
{

  public $helpers = array('Html', 'Form');

/**
 * Dado un texto plano (sin etiquetas HTML) devuelve un texto en el que cada linea está entre una etiqueta dada
 *
 * @param string $text 
 * @param string $tag 
 * @return void
 */
  public function lineTag( $text, $tag = 'p')
  {
    $lines = explode( "\n", $text);
    
    $out = array();
    
    foreach( $lines as $line)
    {
      $out [] = $this->Html->tag( $tag, $line);
    }
    
    return implode( "\n", $out);
  }
  
  public function analytics( $code)
  {
    $js = <<<EOF
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', '$code', '{$this->request->domain()}');
    ga('send', 'pageview');

EOF;
    
    return $this->Html->scriptBlock( $js);
  }
}
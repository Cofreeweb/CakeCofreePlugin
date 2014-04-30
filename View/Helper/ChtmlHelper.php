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
  
  
/**
 * Devuelve una url en formato string
 * Útil para las urls que introduce el usuario en los campos de un contenido
 * Previene que el usuario haya puesto el http://
 * Muestra el enlace sin http://
 *
 * @param string $url 
 * @param boolean $link Si true, devolverá un enlace
 * @param array $attributes Los atributos de la etiqueta <a>
 * @return string
 */
  public function urlString( $url, $link = false, $attributes = array())
  {
    $url = preg_replace("(https?://)", "", $url);
    
    if( $link)
    {
      return $this->Html->link( $url, 'http://'. $url, $attributes);
    }
    else
    {
      return $url;
    }    
  }
}
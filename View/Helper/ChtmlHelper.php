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
}
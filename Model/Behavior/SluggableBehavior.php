<?php

App::import('Inflector');

class SluggableBehavior extends ModelBehavior {

    private $_settings = array();

    function setup( Model $model, $settings = array()) {
        $default = array(
            'type' => 'string',
            'fields' => 'title',
            'scope' => false,
            'conditions' => false,
            'slugfield' => 'slug',
            'separator' => '-',
            'overwrite' => false,
            'length' => 256,
            'lower' => true,
            'contain' => array()
        );

        $this->_settings[$model->alias] = (!empty($settings)) ? $settings + $default : $default;
    }

    function afterValidate( Model $model) 
    {
      if( empty( $model->id) || $this->_settings[$model->alias]['overwrite'])
      {
        if( $this->_settings [$model->alias]['type'] == 'string')
        {
          $this->__stringSlug( $model);
        }
        
        if( $this->_settings [$model->alias]['type'] == 'alpha' || empty( $model->data[$model->alias][$this->_settings [$model->alias]['slugfield']]))
        {
          $slugfield = $this->_settings [$model->alias]['slugfield'];

          if( empty( $model->data [$model->alias][$slugfield]))
          {          
            $this->__alphaId( $model); 
          }
        }
      }
      
      return parent::afterValidate( $model);
    }
    
    // function afterSave(&$model, $created)
    // {
    //   if( $this->_settings [$model->alias]['type'] == 'alpha')
    //   {
    //     $slugfield = $this->_settings [$model->alias]['slugfield'];
    //    
    //     if( empty( $model->data [$model->alias][$slugfield]))
    //     {          
    //       $this->__alphaId( $model); 
    //     }
    //   }
    //   
    //   return parent::afterSave( $model, $created);
    // }

  /**
   * Crea un slug a partir de un string
   *
   * El string "Un título cualquiera" nos dará un slug "un-titulo-cualquiera"
   *
   * @param object $model 
   * @return void
   * @since Shokesu 0.1
   */
    private function __stringSlug( Model $model)
    {
      $fields = (array) $this->_settings [$model->alias]['fields'];
      $scope = (array) $this->_settings [$model->alias]['scope'];
      $conditions = !empty($this->_settings [$model->alias]['conditions']) ? (array) $this->_settings[$model->alias]['conditions'] : array();
      $slugfield = $this->_settings [$model->alias]['slugfield'];
      $hasFields = true;

      foreach ($fields as $field) 
      {
        if (!$model->hasField($field)) 
        {
          $hasFields = false;
        }

        if (!isset($model->data[$model->alias][$field])) 
        {
          $hasFields = false;
        }
      }
      if ($hasFields && $model->hasField($slugfield) && ($this->_settings[$model->alias]['overwrite'] || empty($model->id))) {
          $toSlug = array();

          foreach ($fields as $field) 
          {
            // Si el campo es un array, entonces es un campo traducible con TranslateBehavior
            // Tomamos el primero de todos
            if( is_array( $model->data[$model->alias][$field]))
            {
              $value = current( $model->data[$model->alias][$field]);
            } 
            else
            {
              $value = $model->data[$model->alias][$field];
            }
            
            $toSlug [] = $this->translit( $model, $value);
          }
          
          $toSlug = join(' ', $toSlug);

          $slug = Inflector::slug($toSlug, $this->_settings[$model->alias]['separator']);
          $slug = $this->toAscii( $model, $slug);
          
          if ($this->_settings[$model->alias]['lower']) {
              $slug = strtolower($slug);
          }

          if (strlen($slug) > $this->_settings[$model->alias]['length']) {
              $slug = substr($slug, 0, $this->_settings[$model->alias]['length']);
          }

          $conditions[$model->alias . '.' . $slugfield . ' LIKE'] = $slug . '%';

          if (!empty($model->id)) {
              $conditions[$model->alias . '.' . $model->primaryKey . ' !='] = $model->id;
          }

          if (!empty($scope)) {
              foreach ($scope as $s) {
                  if (isset($model->data[$model->alias][$s]) && !empty($model->data[$model->alias][$s])) {
                      $conditions[$model->alias . '.' . $s] = $model->data[$model->alias][$s];
                  }
              }
          }

          $sameUrls = $model->find('all', array(
              'contain' => $this->_settings[$model->alias]['contain'],
              'conditions' => $conditions
          ));

          $sameUrls = (!empty($sameUrls)) ?
              Set::extract($sameUrls, '{n}.' . $model->alias . '.' . $slugfield) :
              null;

          if ($sameUrls) {
              if (in_array($slug, $sameUrls)) {
                  $begginingSlug = $slug;
                  $index = 1;

                  while ($index > 0) {
                      if (!in_array($begginingSlug . $this->_settings[$model->alias]['separator'] . $index, $sameUrls)) {
                          $slug = $begginingSlug . $this->_settings[$model->alias]['separator'] . $index;
                          $index = -1;
                      }

                      $index++;
                  }
              }
          }
          
          if (!empty($model->whitelist) && !in_array($slugfield, $model->whitelist)) {
              $model->whitelist[] = $slugfield;
          }

          $model->data[$model->alias][$slugfield] = $slug;
      }
    }
    
  /**
   * Genera un string slug basado en el id (tipo Youtube)
   *
   * @param object $model 
   * @return void
   * @since Shokesu 0.1
   */
    private function __alphaID( Model $model)
    {
      $last = $model->find( 'first', array(
          'fields' => array(
              'MAX('. $model->alias .'.id) AS last'
          )
      ));
      $last_id = $last [0]['last'];
      $chars='l07tZhrm9qpaXKjWnz8E1wsuo4v3FIfTYxBOCDNPVMbkHieU2y5cGRgLJQASd';
      $sum = 1234567890;
      $val = ($last_id) + $sum;

      $str = '';
      $nums = str_split( $val);
      foreach( $nums as $num)
      {
        $str .= substr( $chars, $num, 1);
      }
      
      $slugfield = $this->_settings [$model->alias]['slugfield'];
      $model->data [$model->alias][$slugfield] = $str;
      return $str;
    }
    
    
    function toAscii( Model $model, $str, $delimiter='-') 
    {
      $clean = @iconv('UTF-8', 'ASCII//TRANSLIT', $str);
      
      if( empty( $clean))
      {
        return $this->__alphaId( $model);
      }
    	
    	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    	$clean = strtolower(trim($clean, '-'));
    	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

    	return $clean;
    }
    
    function translit( Model $model, $str, $options = array())
    {
      // Make sure string is in UTF-8 and strip invalid UTF-8 characters
    	$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

    	$defaults = array(
    		'delimiter' => '-',
    		'limit' => null,
    		'lowercase' => true,
    		'replacements' => array(),
    		'transliterate' => true,
    	);

    	// Merge options
    	$options = array_merge($defaults, $options);

    	$char_map = array(
    		// Latin
    		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
    		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
    		'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
    		'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
    		'ß' => 'ss', 
    		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
    		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
    		'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
    		'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
    		'ÿ' => 'y',

    		// Latin symbols
    		'©' => '(c)',

    		// Greek
    		'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
    		'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
    		'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
    		'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
    		'Ϋ' => 'Y',
    		'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
    		'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
    		'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
    		'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
    		'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

    		// Turkish
    		'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
    		'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 

    		// Russian
    		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
    		'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
    		'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
    		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
    		'Я' => 'Ya',
    		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
    		'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
    		'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
    		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
    		'я' => 'ya',

    		// Ukrainian
    		'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
    		'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

    		// Czech
    		'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
    		'Ž' => 'Z', 
    		'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
    		'ž' => 'z', 

    		// Polish
    		'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
    		'Ż' => 'Z', 
    		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
    		'ż' => 'z',

    		// Latvian
    		'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
    		'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
    		'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
    		'š' => 's', 'ū' => 'u', 'ž' => 'z'
    	);

    	// Make custom replacements
    	$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

    	// Transliterate characters to ASCII
    	if ($options['transliterate']) {
    		$str = str_replace(array_keys($char_map), $char_map, $str);
    	}

    	// Replace non-alphanumeric characters with our delimiter
    	$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

    	// Remove duplicate delimiters
    	$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

    	// Truncate slug to max. characters
    	$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

    	// Remove delimiter from ends
    	$str = trim($str, $options['delimiter']);

    	$return = $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    	
    	if( !$this->isLatin( $return))
    	{
    	  return $this->__alphaID( $model);
    	}
    	
    	return $return;
    }
    
    public function isLatin( $string)
    {
      $result = preg_match('/[^\\p{Common}\\p{Latin}]/u', $string);
      return $result == 0;
    }
}
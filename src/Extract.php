<?php
/**
 * Extract.php - Sugar for getting data out of string in PHP.
 *
 * @author Lim Yuan Qing <hello@yuanqing.sg>
 * @license MIT
 * @link https://github.com/yuanqing/extract
 */

namespace yuanqing\Extract;

class Extract
{
  private $keys;
  private $regex;

  /**
   * @param string $format The format to match strings against
   * @throws InvalidArgumentException
   * @throws UnexpectedValueException
   */
  public function __construct($format)
  {
    if (!is_string($format)) {
      throw new \InvalidArgumentException('$format must be a string');
    }
    $this->keys = array();

    # escape characters not inside tags (ie. outside double braces)
    $regex = preg_replace_callback('/[^}]+(?={{)/', function($matches) {
      return preg_quote($matches[0], '/'); # also escape '/'!
    }, $format . '{{');
    $regex = substr($regex, 0, -2); # drop the '{{' that was appended

    # replace tags with named capturing groups
    $regex = preg_replace_callback('/{{(.+?)}}/', function($matches) {

      $match = trim($matches[1]);
      if (!$match) {
        throw new \UnexpectedValueException('Capturing groups in $format must be named');
      }

      list($key, $specifier) = explode(':', $match); # split on ':'
      $this->keys[] = $key;

      if ($specifier === null) { # no specifier
        return '([^{}]+)';
      }

      $lastChar = substr($specifier, -1); # $type is the last char of $specifier
      if (!ctype_alpha($lastChar)) { # no type
        return sprintf('([^{}]{%s})', $lastChar);
      }

      $type = $lastChar;

      $len = rtrim($specifier, $type) ?: '1,'; # default $len is {1,}

      if ($type == 'f') {
        $lenBeforeDot = $lenAfterDot = '1,'; # defaults to {1,}
        if ($len[0] == '.') {
          $lenAfterDot = substr($len, 1); # drop first '.'
        } else if (substr($len, -1) == '.') {
          $lenBeforeDot = substr($len, 0, -1); # drop last '.'
        } else {
          list($lenBeforeDot, $lenAfterDot) = explode('.', $len);
        }
      }

      # finally we return the capturing group
      switch ($type) {
        case 's':
          return sprintf('([^{}]{%s})', $len);
        case 'd':
          return sprintf('(\d{%s})', $len);
        case 'f':
          return sprintf('(\d{%s}\.\d{%s})',  $lenBeforeDot, $lenAfterDot);
      }

    }, $regex);

    # match the string from beginning to end
    $this->regex = '/^' . $regex . '$/';
  }

  /**
   * Extract values from $str based on {$format}.
   *
   * @param string $str The string to extract values from
   * @return array
   * @throws InvalidArgumentException
   */
  public function extract($str = null)
  {
    if (!$this->canCastToString($str)) {
      throw new \InvalidArgumentException('$str could not be cast to string');
    }
    $str = (string) $str;

    # match $str against $this->regex
    $matches = array();
    if (!preg_match($this->regex, $str, $matches)) {
      return null;
    }
    unset($matches[0]); # we only want the capturing groups

    # combine matches with their capturing group names
    $matches = array_combine($this->keys, $matches);
    $matches = $this->unflatten($matches);
    array_walk_recursive($matches, function(&$str) {
      $str = $this->typeCast($str);
    });

    return $matches;
  }

  /**
   * Unflattens $arr, with each key expanded on '.'.
   *
   * @example
   * $arr = array('foo.bar' => 'baz');
   * var_dump($this->unflattenArr($arr)); #=> array('foo' => array('bar' => 'baz'))
   * @param array $arr The array to unflatten
   * @return array
   */
  private function unflatten(array $arr)
  {
    $result = array();
    foreach ($arr as $key => $val) {
      if (strpos($key, '.') !== false) {
        parse_str('result[' . str_replace('.', '][', $key) . ']=' . $val);
      } else {
        $result[$key] = $val;
      }
    }
    return $result;
  }

  /**
   * Casts the $str string (if possible) to an integer or float.
   *
   * @example
   * var_dump($this->typeCast('1')); #=> 1
   * var_dump($this->typeCast('1.2')); #=> 1.2
   * var_dump($this->typeCast('foo')); #=> 'foo'
   * @param array $str The str to cast
   * @return mixed
   */
  private function typeCast($str)
  {
    if (ctype_digit($str)) {
      $cast = intval($str);
      if ($str == (string) $cast) {
        return $cast;
      }
      return $str;
    }
    if (is_numeric($str)) {
      return floatval($str);
    }
    return $str;
  }

  /**
   * Returns true if $obj can be cast to string
   *
   * @param mixed $obj
   * @return boolean
   */
  private function canCastToString($obj)
  {
    if (is_scalar($obj) || is_null($obj)) {
      return true;
    }
    return is_object($obj) && method_exists($obj, '__toString');
  }

}

<?php
/**
 * Extract.php - Sugar for getting data out of strings in PHP.
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
   */
  public function __construct($format = null)
  {
    if ($format === null || !is_string($format)) {
      throw new \InvalidArgumentException('$format must be a string');
    }
    $this->keys = array();

    # escape characters not inside tags (ie. outside double braces)
    $regex = preg_replace_callback('/[^}]+(?={{)/', function($matches) {
      return preg_quote($matches[0], '/'); # also escape '/'
    }, $format . '{{'); # append '{{' to make the regex work
    $regex = substr($regex, 0, -2); # drop the '{{'

    # replace tags with capturing groups; callback is a class method to accommodate PHP 5.3
    $regex = preg_replace_callback('/{{(.+?)}}/', array($this, 'replaceTags'), $regex);

    if (empty($this->keys)) {
      throw new \InvalidArgumentException('$format must have at least one capturing group');
    }

    # match the string from beginning to end; use /s flag so that wildcard char also matches "\n"
    $this->regex = '/^' . $regex . '$/s';
  }

  /**
   * Callback for preg_replace_callback called in __construct
   *
   * @param string $matches Array of matched elements
   * @throws UnexpectedValueException
   */
  private function replaceTags($matches) # $matches is the tag
  {
    $match = trim($matches[1]);
    if ($match === '') {
      throw new \InvalidArgumentException(sprintf('Invalid capturing group name: %s', $matches[0]));
    }

    $split = explode(':', $match); # split on ':'
    $this->keys[] = trim($split[0]);

    # specifier absent
    if (!isset($split[1])) {
      return '(.+?)';
    }

    # specifier present
    $specifier = trim($split[1]);
    if ($specifier === '0') {
      throw new \InvalidArgumentException('Invalid length specifier: 0');
    }

    $lastChar = substr($specifier, -1); # $lastChar of $specifier is the type
    switch ($lastChar) {
      case 'd': # integer
      case 'f': # float
      case 's': # string
        $len = trim(substr($specifier, 0, -1)) ?: '1,'; # $len defaults to >=1
        # if float type, find $lenBeforeDec and $lenAfterDec
        if ($lastChar == 'f') {
          if ($len === '1,') { # ie. no length specified
            $lenBeforeDec = $lenAfterDec = '1,';
          } else {
            if ($len[0] === '.') { # first char is '.'
              $lenBeforeDec = '0,';
              $lenAfterDec = substr($len, 1);
            } else if (substr($len, -1) === '.') { # last char is '.'
              $lenBeforeDec = substr($len, 0, -1);
              $lenAfterDec = '0,';
            } else {
              if ($len === '0.0') {
                throw new \InvalidArgumentException('Invalid length specifier: 0.0');
              }
              $split = explode('.', $len);
              $lenBeforeDec = trim($split[0]);
              $lenAfterDec = trim($split[1]);
            }
          }
        }
        break;
      default: # no type
        $cast = (int) $specifier;
        if ($specifier !== (string) $cast) {
          throw new \InvalidArgumentException(sprintf('Invalid length specifier: %s', $specifier));
        }
        $len = $specifier;
    }

    # finally, return the capturing group
    switch ($lastChar) {
      case 'd':
        return sprintf('(\d{%s})', $len);
      case 'f':
        return sprintf('(\d{%s}\.\d{%s})', $lenBeforeDec, $lenAfterDec);
      default:
        return sprintf('(.{%s})', $len);
    }
  }

  /**
   * Extract values from $str based on {$format}
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

    # cast each element in $matches to integer or float if possible
    array_walk_recursive($matches, array($this, 'typeCast'));

    return empty($matches) ? null : $matches;
  }

  /**
   * Unflattens $arr, with each key expanded on '.'
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
   * Casts the $str string (passed by reference) to integer or float if possible, else leaves
   * $str unchanged
   *
   * @param array $str The str to cast
   * @return null
   */
  private function typeCast(&$str)
  {
    if (is_numeric($str)) {
      $int = intval($str);
      if ($str == (string) $int) {
        $str = $int;
      } else {
        $str = floatval($str);
      }
    }
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

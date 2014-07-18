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
   * @throws InvalidArgumentException
   */
  public function __construct($format = null)
  {
    if (!is_string($format)) {
      throw new \InvalidArgumentException('$format must be a string');
    }
    $this->parseFormat($format);
  }

  /**
   * Parses the given $format into {$regex}, and populates the {$keys} array
   *
   * @param string $format The format to match strings against
   * @throws InvalidArgumentException
   */
  public function parseFormat($format)
  {
    $this->keys = array();

    $regex = '';
    $curr = '';
    $beforeGroup = '';
    $afterGroup = '';

    $split = str_split($format);
    $count = count($split);
    for ($i=0; $i<$count; $i++) {
      $c = $split[$i];
      if ($c == '{') {
        $regex .= preg_quote($curr, '/');
        $curr = '';
        if ($i+1 < $count && $split[$i+1] == '{') {
          $beforeGroup = $i != 0 ? $split[$i-1] : '';
          $i++;
          continue;
        }
      } else if ($c == '}' && ($i+1 < $count && $split[$i+1] == '}')) {
        $afterGroup = $i+2 < $count ? $split[$i+2] : '';
        $regex .= $this->parseCapturingGroup(trim($curr), $beforeGroup, $afterGroup);
        $curr = '';
        $i++;
        continue;
      }
      $curr .= $c;
    }

    if (empty($this->keys)) {
      throw new \InvalidArgumentException('$format must have at least one capturing group');
    }

    # use /s flag so that wildcard char also matches "\n"
    $this->regex = '/^' . $regex . preg_quote($curr, '/') . '$/s';
  }

  /**
   * Parse a capturing group into a RegEx capturing group
   *
   * @param string $group The string between the opening '{{' and closing '}}'
   * @param string $charBefore The character before the opening '{{'
   * @param string $charAfter The character after the closing '}}'
   * @throws UnexpectedValueException
   */
  private function parseCapturingGroup($group, $charBefore, $charAfter)
  {
    $split = explode(':', $group); # split on ':'

    # $key is before the ':''
    $key = trim($split[0]);
    if ($key === '' || $key == '.') {
      throw new \InvalidArgumentException('Invalid capturing group');
    }
    $this->keys[] = $key;

    # $specifier is after the ':'
    if (!isset($split[1])) { # no $specifier
      if ($charAfter !== '' || $charBefore !== '') {
        return sprintf('([^%s]+)', preg_quote($charAfter !== '' ? $charAfter : $charBefore, '/'));
      }
      return '(.+)';
    }

    $specifier = trim($split[1]);
    if ($specifier === '0') {
      throw new \InvalidArgumentException('Invalid length specifier: 0');
    }

    # last char of $specifier is the $type
    $type = substr($specifier, -1);
    switch ($type) {
      case 'd': # integer
      case 's': # string
        $len = trim(substr($specifier, 0, -1));
        if ($len === '') {
          $len = '1,';
        } else {
          if (!$this->canCastToInteger($len) || intval($len) === 0) {
            throw new \InvalidArgumentException(sprintf('Invalid length specifier: %s', $len));
          }
        }
        break;
      case 'f': # float
        $len = trim(substr($specifier, 0, -1));
        if ($len === '') { # ie. no length specified
          $lenBeforeDec = $lenAfterDec = '1,';
        } else {
          if ($len[0] === '.') { # first char is '.'
            $lenBeforeDec = '0,';
            $lenAfterDec = substr($len, 1);
          } else if (substr($len, -1) === '.') { # last char is '.'
            $lenBeforeDec = substr($len, 0, -1);
            $lenAfterDec = '0,';
          } else {
            if ($len === '0.0' || $len === '0') {
              throw new \InvalidArgumentException('Invalid length specifier: 0.0');
            }
            $split = explode('.', $len);
            $lenBeforeDec = trim($split[0]);
            $lenAfterDec = trim($split[1]);
          }
        }
        break;
      default: # no type
        $len = $specifier;
        if (!$this->canCastToInteger($len)) {
          throw new \InvalidArgumentException(sprintf('Invalid length specifier: %s', $specifier));
        }
    }

    # finally, return the capturing group
    switch ($type) {
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
   * Returns true if $obj can be cast to integer
   *
   * @param mixed $obj
   * @return boolean
   */
  private function canCastToInteger($obj)
  {
    $cast = intval($obj);
    if (is_numeric($obj) && (string) $cast == (string) $obj) {
      return true;
    }
    return false;
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

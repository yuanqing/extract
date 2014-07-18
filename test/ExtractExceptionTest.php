<?php
/**
 * Extract.php - Sugar for getting data out of strings in PHP.
 *
 * @author Lim Yuan Qing <hello@yuanqing.sg>
 * @license MIT
 * @link https://github.com/yuanqing/extract
 */

use yuanqing\Extract\Extract;

class ExtractExceptionTest extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider invalidFormatProvider
   * @expectedException InvalidArgumentException
   */
  public function testInvalidFormat($format)
  {
    new Extract($format);
  }
  public function invalidFormatProvider()
  {
    return array(

      # invalid type
      array(array()),
      array(null),

      # no capturing groups
      array(''),
      array('foo'),

      # invalid capturing group
      array('{{ }}'),
      array('{{ . }}'),
      array('{{ : }}'),

      # invalid length specifier
      array('{{ foo : 0 }}'),
      array('{{ foo : 1.2 }}'),

      # invalid length specifier, string type
      array('{{ foo : 0.0s }}'),
      array('{{ foo : 0s }}'),
      array('{{ foo : 0.s }}'),
      array('{{ foo : .0s }}'),
      array('{{ foo : 1.2s }}'),
      array('{{ foo : .2s }}'),

      # invalid length specifier, integer type
      array('{{ foo : 0.0d }}'),
      array('{{ foo : 0d }}'),
      array('{{ foo : 0.d }}'),
      array('{{ foo : .0d }}'),
      array('{{ foo : 1.2d }}'),
      array('{{ foo : .2d }}'),

      # invalid length specifier, float type
      array('{{ foo : 0f }}'),
      array('{{ foo : 0.0f }}')

    );
  }

  /**
   * @dataProvider invalidStringProvider
   * @expectedException InvalidArgumentException
   */
  public function testInvalidString($str)
  {
    $e = new Extract('{{ foo }}');
    $e->extract($str);
  }
  public function invalidStringProvider()
  {
    return array(

      # cannot be cast to string
      array(array()),
      array(new StdClass)

    );
  }

}

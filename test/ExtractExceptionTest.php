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

      # invalid length specifier
      array('{{ foo : 0 }}'),
      array('{{ foo : 12.3 }}'),
      array('{{ foo : 0.0f }}')

    );
  }

  /**
   * @dataProvider invalidStringProvider
   * @expectedException InvalidArgumentException
   */
  public function testInvalidString($str)
  {
    (new Extract('{{ foo }}'))->extract($str);
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

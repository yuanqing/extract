<?php
/**
 * Extract.php - Sugar for getting data out of string in PHP.
 *
 * @author Lim Yuan Qing <hello@yuanqing.sg>
 * @license MIT
 * @link https://github.com/yuanqing/extract
 */

use yuanqing\Extract\Extract;

class ExtractTest extends PHPUnit_Framework_TestCase
{
  /**
   * @expectedException InvalidArgumentException
   */
  public function testInvalidFormatArgument()
  {
    $e = new Extract(null);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testInvalidStringArgument()
  {
    $e = new Extract('{{ foo }}');
    $e->extract(array());
  }

  public function testEmptyFormat()
  {
    $e = new Extract('');
    $str = 'foo';
    $expected = null;
    $this->assertSame($expected, $e->extract($str));
  }

  public function testSingleGroup()
  {
    $e = new Extract('{{ foo }}');
    $str = 'bar';
    $expected = array('foo' => 'bar');
    $this->assertSame($expected, $e->extract($str));
  }

}

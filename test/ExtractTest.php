<?php
/**
 * Extract.php - Sugar for getting data out of strings in PHP.
 *
 * @author Lim Yuan Qing <hello@yuanqing.sg>
 * @license MIT
 * @link https://github.com/yuanqing/extract
 */

use yuanqing\Extract\Extract;

class ExtractTest extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider stringProvider
   */
  public function testString($format, $str, $expected)
  {
    $e = new Extract($format);
    $this->assertSame($expected, $e->extract($str));
  }
  public function stringProvider()
  {
    return array(

      # empty string
      array('{{ foo }}', '', null),

      # capture string
      array('{{ foo }}', 'bar', array(
        'foo' => 'bar'
      )),

      # capture string, multi-line
      array('{{ foo }}', "bar\nbaz", array(
        'foo' => "bar\nbaz"
      )),

      # capture integer
      array('{{ foo }}', '42', array(
        'foo' => 42
      )),

      # capture float
      array('{{ foo }}', '3.14', array(
        'foo' => 3.14
      ))

    );
  }

  /**
   * @dataProvider formatProvider
   */
  public function testFormat($format, $str, $expected)
  {
    $e = new Extract($format);
    $this->assertSame($expected, $e->extract($str));
  }
  public function formatProvider()
  {
    return array(

      # string key
      array('{{ foo }}', '', null),
      array('{{ foo }}', 'bar', array(
        'foo' => 'bar'
      )),
      array('{{foo}}', 'bar', array(
        'foo' => 'bar'
      )),
      array('{{  foo  }}', 'bar', array(
        'foo' => 'bar'
      )),

      # string key, with whitespace
      array('{{ foo bar }}', 'baz', array(
        'foo bar' => 'baz'
      )),
      array('{{foo bar}}', 'baz', array(
        'foo bar' => 'baz'
      )),

      # string key, nested
      array('{{ foo.bar }}', 'baz', array(
        'foo' => array(
          'bar' => 'baz'
        )
      )),

      # numeric key
      array('{{ 0 }}', 'foo', array(
        0 => 'foo'
      )),

      # numeric key, nested
      array('{{ 1.2 }}', 'foo', array(
        1 => array(
          2 => 'foo'
        )
      ))

    );
  }

  /**
   * @dataProvider formatWithSpecifierProvider
   */
  public function testFormatWithSpecifier($format, $str, $expected)
  {
    $e = new Extract($format);
    $this->assertSame($expected, $e->extract($str));
  }
  public function formatWithSpecifierProvider()
  {
    return array(

      # size only; assumes string type
      array('{{ foo : 4 }}', 'abcd', array(
        'foo' => 'abcd'
      )),
      array('{{ foo : 4 }}', '1234', array(
        'foo' => 1234
      )),
      array('{{ foo : 4 }}', '1.23', array(
        'foo' => 1.23
      )),
      array('{{ foo : 4 }}', '', null),
      array('{{ foo : 4 }}', 'abc', null),
      array('{{ foo : 4 }}', 'abcde', null),
      array('{{ foo : 4 }}', '123', null),
      array('{{ foo : 4 }}', '12345', null),
      array('{{ foo : 4 }}', '1.2', null),
      array('{{ foo : 4 }}', '1.234', null),

      # string type
      array('{{ foo : s }}', 'abc', array(
        'foo' => 'abc'
      )),
      array('{{ foo : s }}', '123', array(
        'foo' => 123
      )),
      array('{{ foo : s }}', '1.2', array(
        'foo' => 1.2
      )),
      array('{{ foo : s }}', '', null),
      array('{{ foo : s }}', 'ab', array(
        'foo' => 'ab'
      )),
      array('{{ foo : s }}', 'abcd', array(
        'foo' => 'abcd'
      )),

      # string type, with size
      array('{{ foo : 3s }}', 'abc', array(
        'foo' => 'abc'
      )),
      array('{{ foo : 3s }}', '123', array(
        'foo' => 123
      )),
      array('{{ foo : 3s }}', '1.2', array(
        'foo' => 1.2
      )),
      array('{{ foo : 3s }}', '', null),
      array('{{ foo : 3s }}', 'ab', null),
      array('{{ foo : 3s }}', 'abcd', null),

      # integer type
      array('{{ foo : d }}', '', null),
      array('{{ foo : d }}', 'bar', null),
      array('{{ foo : d }}', '123', array(
        'foo' => 123
      )),
      array('{{ foo : d }}', '1.23', null),

      # integer type, with size
      array('{{ foo : 3d }}', '123', array(
        'foo' => 123
      )),
      array('{{ foo : 3d }}', '', null),
      array('{{ foo : 3d }}', 'abc', null),
      array('{{ foo : 3d }}', '12', null),
      array('{{ foo : 3d }}', '1234', null),
      array('{{ foo : 3d }}', '.12', null),
      array('{{ foo : 3d }}', '1.2', null),
      array('{{ foo : 3d }}', '12.', null),

      # float type
      array('{{ foo : f }}', '1.2', array(
        'foo' => 1.2
      )),
      array('{{ foo : f }}', '', null),
      array('{{ foo : f }}', 'bar', null),
      array('{{ foo : f }}', '1', null),
      array('{{ foo : f }}', '1.', null),
      array('{{ foo : f }}', '.1', null),

      # float type, with before-decimal length
      array('{{ foo : 2.f }}', '12.', array(
        'foo' => 12
      )),
      array('{{ foo : 2.f }}', '12.3', array(
        'foo' => 12.3
      )),
      array('{{ foo : 2.f }}', '', null),
      array('{{ foo : 2.f }}', 'ab.', null),
      array('{{ foo : 2.f }}', '1.', null),
      array('{{ foo : 2.f }}', '123.', null),
      array('{{ foo : 0.f }}', '.1', array(
        'foo' => 0.1
      )),
      array('{{ foo : 0.f }}', '', null),
      array('{{ foo : 0.f }}', 'a.', null),
      array('{{ foo : 0.f }}', '1.', null),

      # float type, with after-decimal length
      array('{{ foo : .2f }}', '.12', array(
        'foo' => 0.12
      )),
      array('{{ foo : .2f }}', '1.23', array(
        'foo' => 1.23
      )),
      array('{{ foo : .2f }}', '', null),
      array('{{ foo : .2f }}', '.ab', null),
      array('{{ foo : .2f }}', '.1', null),
      array('{{ foo : .2f }}', '.123', null),
      array('{{ foo : .0f }}', '1.', array(
        'foo' => 1
      )),
      array('{{ foo : .0f }}', '', null),
      array('{{ foo : .0f }}', '.a', null),
      array('{{ foo : .0f }}', '.1', null),

      # float type, with before-decimal and after-decimal lengths
      array('{{ foo : 1.2f }}', '1.23', array(
        'foo' => 1.23
      )),
      array('{{ foo : 1.2f }}', '', null),
      array('{{ foo : 1.2f }}', 'a.bc', null),
      array('{{ foo : 1.2f }}', '.12', null),
      array('{{ foo : 1.2f }}', '12.34', null),
      array('{{ foo : 1.2f }}', '1.2', null),
      array('{{ foo : 1.2f }}', '1.234', null),
      array('{{ foo : 1.2f }}', '123', null),
      array('{{ foo : 1.0f }}', '1.', array(
        'foo' => 1
      )),
      array('{{ foo : 1.0f }}', '', null),
      array('{{ foo : 1.0f }}', '1.2', null),
      array('{{ foo : 0.2f }}', '.12', array(
        'foo' => 0.12
      )),
      array('{{ foo : 0.2f }}', '', null),
      array('{{ foo : 0.2f }}', '1.23', null),

    );
  }

  /**
   * @dataProvider formatMultipleGroupsProvider
   */
  public function testFormatMultipleGroups($format, $str, $expected)
  {
    $e = new Extract($format);
    $this->assertSame($expected, $e->extract($str));
  }
  public function formatMultipleGroupsProvider()
  {
    return array(

      # non-special characters between groups
      array(' {{ foo }} {{ bar }} {{ baz }} ', ' qux quux bam ', array(
        'foo' => 'qux',
        'bar' => 'quux',
        'baz' => 'bam'
      )),
      array(' {{ foo : 3 }} {{ bar : 4 }} {{ baz : 3 }} ', ' qux quux bam ', array(
        'foo' => 'qux',
        'bar' => 'quux',
        'baz' => 'bam'
      )),

      # special characters between groups
      array('/{{ foo }}/{{ bar }}/{{ baz }}/', '/qux/quux/bam/', array(
        'foo' => 'qux',
        'bar' => 'quux',
        'baz' => 'bam'
      )),
      array('/{{ foo : 3 }}/{{ bar : 4 }}/{{ baz : 3 }}/', '/qux/quux/bam/', array(
        'foo' => 'qux',
        'bar' => 'quux',
        'baz' => 'bam'
      )),

    );
  }

}

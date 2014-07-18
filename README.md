# Extract.php [![Packagist Version](http://img.shields.io/packagist/v/yuanqing/extract.svg)](https://packagist.org/packages/yuanqing/extract) [![Build Status](https://img.shields.io/travis/yuanqing/extract.svg)](https://travis-ci.org/yuanqing/extract) [![Coverage Status](https://img.shields.io/coveralls/yuanqing/extract.svg)](https://coveralls.io/r/yuanqing/extract)

Regular Expression sugar for extracting data out of a string:

```php
$format = '{{ foo }}, {{ bar }}!';
$str = 'Hello, World!';
$e = new yuanqing\Extract\Extract($format);
var_dump($e->extract($str)); #=> array('foo' => 'Hello', 'bar' => 'World')
```

The `$format` is parsed and converted into a Regular Expression, which is then used to match against the given `$str`.

## Usage

1. You can restrict both the *type* and *size* of each capturing group.

    ```php
    $format = '{{ foo : 5s }}, {{ bar : 5s }}!';
    $str = 'Hello, World!';
    $e = new yuanqing\Extract\Extract($format);
    var_dump($e->extract($str)); #=> array('foo' => 'Hello', 'bar' => 'World')
    ```

2. The `extract` method returns `null` if the given `$str` does not match the format.

Read [the tests](https://github.com/yuanqing/extract/blob/master/test/ExtractTest.php) for more usage examples.

## Requirements

Extract.php requires at least **PHP 5.3**, or **HHVM**.

## Installation

### Install with Composer

1. Install [Composer](http://getcomposer.org/).

2. Install [the Extract.php Composer package](https://packagist.org/packages/yuanqing/extract):

    ```
    $ composer require yuanqing/extract ~0.1
    ```

3. In your PHP, require the Composer autoloader:

    ```php
    require_once __DIR__ . '/vendor/autoload.php';
    ```

### Install manually

1. Clone this repository:

    ```
    $ git clone https://github.com/yuanqing/extract
    ```

    Or just [grab the zip](https://github.com/yuanqing/extract/archive/master.zip).

2. In your PHP, require [`Extract.php`](https://github.com/yuanqing/extract/blob/master/src/Extract.php):

    ```php
    require_once __DIR__ . '/src/Extract.php';
    ```

## Testing

You need [PHPUnit](http://phpunit.de/) to run the tests:

```
$ git clone https://github.com/yuanqing/extract
$ cd extract
$ phpunit
```

## License

MIT license

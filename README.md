# Extract.php [![Packagist Version](http://img.shields.io/packagist/v/yuanqing/extract.svg)](https://packagist.org/packages/yuanqing/extract) [![Build Status](https://img.shields.io/travis/yuanqing/extract.svg)](https://travis-ci.org/yuanqing/extract) [![Coverage Status](https://img.shields.io/coveralls/yuanqing/extract.svg)](https://coveralls.io/r/yuanqing/extract)

Regex sugar for getting data out of strings:

```php
use yuanqing\Extract\Extract;

$e = new Extract('{{ foo.bar }}, {{ foo.baz }}!');
$e->extract('Hello, World!'); #=> ['foo' => ['bar' => 'Hello', 'baz' => 'World']]
```

Boom.

## Usage

1. If the given string does not match the required format, `null` is returned.

2. Each capturing group is enclosed in double braces. Within said braces, we have:

    1. The name of the capturing group
    2. *(optional)* A character length
    3. *(optional)* A type specifier

3. A capturing group can be an arbitrary **string** (`s`):

    ```php
    $e = new Extract('{{ foo: s }}, {{ bar: s }}!');
    $e->extract('Hello, World!'); #=> ['foo' => 'Hello', 'bar' => 'World']
    $e->extract('Hola, World!'); #=> ['foo' => 'Hola', 'bar' => 'World']

    $e = new Extract('{{ foo: 5s }}, {{ bar: 5s }}!');
    $e->extract('Hello, World!'); #=> ['foo' => 'Hello', 'bar' => 'World']
    $e->extract('Hola, World!'); #=> null
    ```

4. ...or an **integer** (`d`):

    ```php
    $e = new Extract('{{ day: d }}-{{ month: d }}-{{ year: d }}');
    $e->extract('31-12-2014'); #=> ['day' => 31, 'month' => 12, 'year' => 2014]
    $e->extract('31-12-14'); #=> ['day' => 31, 'month' => 12, 'year' => 14]
    $e->extract('31-Dec-2014'); #=> null

    $e = new Extract('{{ day: 2d }}-{{ month: 2d }}-{{ year: 4d }}');
    $e->extract('31-12-2014'); #=> ['day' => 31, 'month' => 12, 'year' => 2014]
    $e->extract('31-12-14'); #=> null
    ```

5. ...or a **float** (`f`):

    ```php
    $e = new Extract('{{ tau: f }}, {{ pi: f }}');
    $e->extract('6.28, 3.14'); #=> ['tau' => 6.28, 'pi' => 3.14]
    $e->extract('tau, pi'); #=> null

    $e = new Extract('{{ tau: 1.f }}, {{ pi: 1.f }}');
    $e->extract('6.28, 3.14'); #=> ['tau' => 6.28, 'pi' => 3.14]
    $e->extract('06.28, 03.14'); #=> null

    $e = new Extract('{{ tau: .2f }}, {{ pi: .2f }}');
    $e->extract('6.28, 3.14'); #=> ['tau' => 6.28, 'pi' => 3.14]
    $e->extract('6.283, 3.142'); #=> null

    $e = new Extract('{{ tau: 1.2f }}, {{ pi: 1.2f }}');
    $e->extract('6.28, 3.14'); #=> ['tau' => 6.28, 'pi' => 3.14]
    $e->extract('6.3, 3.1'); #=> null
    ```

All the examples in this README are in [the examples.php file](https://github.com/yuanqing/extract/blob/master/examples.php). You can also find more usage examples in [the tests](https://github.com/yuanqing/extract/tree/master/test).

## Requirements

Extract.php requires at least **PHP 5.3**, or **HHVM**.

## Installation

### Install with Composer

1. Install [the Extract.php Composer package](https://packagist.org/packages/yuanqing/extract):

    ```
    $ composer require yuanqing/extract ~0.1
    ```

2. In your PHP, require the Composer autoloader:

    ```php
    require_once __DIR__ . '/vendor/autoload.php';
    ```

### Install manually

1. Clone this repository:

    ```
    $ git clone https://github.com/yuanqing/extract
    ```

    Or just [grab the zip](https://github.com/yuanqing/extract/archive/master.zip).

2. In your PHP, require [Extract.php](https://github.com/yuanqing/extract/blob/master/src/Extract.php):

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

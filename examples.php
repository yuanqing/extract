<?php
/**
 * Extract.php - Sugar for getting data out of strings in PHP.
 *
 * @author Lim Yuan Qing <hello@yuanqing.sg>
 * @license MIT
 * @link https://github.com/yuanqing/extract
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/src/Extract.php';

use yuanqing\Extract\Extract;

$e = new Extract('{{ foo.bar }}, {{ foo.baz }}!');
$e->extract('Hello, World!'); #=> ['foo' => ['bar' => 'Hello', 'baz' => 'World']]


# string

$e = new Extract('{{ foo: s }}, {{ bar: s }}!');
$e->extract('Hello, World!'); #=> ['foo' => 'Hello', 'bar' => 'World']
$e->extract('Hola, World!'); #=> ['foo' => 'Hola', 'bar' => 'World']

$e = new Extract('{{ foo: 5s }}, {{ bar: 5s }}!');
$e->extract('Hello, World!'); #=> ['foo' => 'Hello', 'bar' => 'World']
$e->extract('Hola, World!'); #=> null


# integer

$e = new Extract('{{ day: d }}-{{ month: d }}-{{ year: d }}');
$e->extract('31-12-2014'); #=> ['day' => 31, 'month' => 12, 'year' => 2014]
$e->extract('31-12-14'); #=> ['day' => 31, 'month' => 12, 'year' => 14]
$e->extract('31-Dec-2014'); #=> null

$e = new Extract('{{ day: 2d }}-{{ month: 2d }}-{{ year: 4d }}');
$e->extract('31-12-2014'); #=> ['day' => 31, 'month' => 12, 'year' => 2014]
$e->extract('31-12-14'); #=> null


# float

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

[![Latest Stable Version](https://img.shields.io/packagist/v/gpslab/cqrs.svg?maxAge=3600&label=stable)](https://packagist.org/packages/gpslab/cqrs)
[![Total Downloads](https://img.shields.io/packagist/dt/gpslab/cqrs.svg?maxAge=3600)](https://packagist.org/packages/gpslab/cqrs)
[![Build Status](https://img.shields.io/travis/gpslab/cqrs.svg?maxAge=3600)](https://travis-ci.org/gpslab/cqrs)
[![Coverage Status](https://img.shields.io/coveralls/gpslab/cqrs.svg?maxAge=3600)](https://coveralls.io/github/gpslab/cqrs?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/gpslab/cqrs.svg?maxAge=3600)](https://scrutinizer-ci.com/g/gpslab/cqrs/?branch=master)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/a7885c13-685e-49bc-b1e7-635010540f21.svg?maxAge=3600&label=SLInsight)](https://insight.sensiolabs.com/projects/a7885c13-685e-49bc-b1e7-635010540f21)
[![StyleCI](https://styleci.io/repos/92310135/shield?branch=master)](https://styleci.io/repos/92310135)
[![License](https://img.shields.io/packagist/l/gpslab/cqrs.svg?maxAge=3600)](https://github.com/gpslab/cqrs)

# Infrastructure for creating CQRS applications.

![CQRS base scheme](cqrs_schema.png)

## Installation

Pretty simple with [Composer](http://packagist.org), run:

```sh
composer require gpslab/cqrs
```

## Command

* [Simple usage](docs/command/simple_usage.md)
* [Bus](docs/command/command_bus.md)
* Handler
  * [Create handler](docs/command/handler.md)
  * Locator
    * Direct binding locator
    * PSR-11 Container locator
    * Symfony container locator
* Queue
  * Custom queue
  * Memory queue
  * Memory unique queue
  * Predis queue
  * Predis unique queue
* [Middleware](https://github.com/gpslab/middleware)
* [Payload](https://github.com/gpslab/payload)

## Query

* [Simple usage](docs/query/simple_usage.md)
* Bus
  * Handler located bus
  * Custom bus
* Handler
  * Create handler
  * Locator
    * Direct binding locator
    * PSR-11 Container locator
    * Symfony container locator
* [Middleware](https://github.com/gpslab/middleware)
* [Payload](https://github.com/gpslab/payload)
* [Doctrine specification query](https://github.com/gpslab/specification-query)

## License

This bundle is under the [MIT license](http://opensource.org/licenses/MIT). See the complete license in the file: LICENSE

# Liaison Revision

![build](https://github.com/paulbalandan/liaison-revision/workflows/build/badge.svg?branch=develop)
[![PHP version](https://img.shields.io/packagist/php-v/liaison/revision)](https://php.net)
[![Coverage Status](https://coveralls.io/repos/github/paulbalandan/liaison-revision/badge.svg?branch=develop)](https://coveralls.io/github/paulbalandan/liaison-revision?branch=develop)
[![Latest Stable Version](https://poser.pugx.org/liaison/revision/v)](//packagist.org/packages/liaison/revision)
[![Latest Unstable Version](https://poser.pugx.org/liaison/revision/v/unstable)](//packagist.org/packages/liaison/revision)
[![license](https://img.shields.io/github/license/paulbalandan/liaison-revision)](LICENSE)
[![Total Downloads](https://poser.pugx.org/liaison/revision/downloads)](//packagist.org/packages/liaison/revision)

**Liaison Revision** is a software updates library that handles the updating of files in
your CodeIgniter4 projects.

## System Requirements

Liaison Revision requires PHP 7.3+ to run. It also requires the PHP extensions `ext-intl` and `ext-mbstring`
to be installed. Additionally, you can have the `ext-dom` extension enabled to use the `XmlLogHandler`.

Liaison Revision needs to run on versions of CodeIgniter 4 greater than v4.0.4 due to the classes used
within the library which are not available on v4.0.4 and below. You can also opt to use the develop branch
for the latest changes.

## Installation

### Composer installation

You can add this library as a local, per-project dependency to your project using Composer:

    composer require liaison/revision

If you only need this library during development, for instance to run your project's test suite, then you should add it as a development-time dependency:

    composer require --dev liaison/revision

### Manual installation

TBD

## Documentation

- [Overview](docs/overview.md)
    - [Introduction](docs/overview.md#introduction)
    - [Configuration](docs/overview.md#configuration)
    - [Localisation](docs/overview.md#localisation)
    - [Temporary Files Source Control](docs/overview.md#temporary-files-source-control)
- [Configuration](docs/configuration.md)

## Contributing

Contributions must adhere to the [Contributing Guidelines](.github/CONTRIBUTING.md).

## Code of Conduct

Please have a look at [Code of Conduct](.github/CODE_OF_CONDUCT.md).

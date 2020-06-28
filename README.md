# Smartsheet

This library serves as a convenience wrapper around the [REST API that smartsheet exposes](https://smartsheet-platform.github.io/api-docs/).  

## Installation

The preferred method of installing this library is with Composer by running the following from your project root:

```bash
$ composer require infamoustrey/smartsheet
```

## Usage 

This api uses a fluent style for interacting with Smartsheet.

```php

$smartsheetClient = new \Smartsheet\Client([ 'token' => 'yourapitoken' ]);

$smartsheetClient->getSheet('sheetid');

```

## Issues

Use this repository's [issue tracker](https://github.com/Infamoustrey/smartsheet/issues) to resolve issues and ask questions.

## Roadmap

Full api coverage! There's a lot missing, if you see something missing then put in a PR! Your help is appreciated!

## Contributing

Feel free to [submit a PR](https://github.com/Infamoustrey/smartsheet/compare), just be sure to explain what you are trying to fix/add when submitting it. 
If you do decide to add functionality, it must be covered by a test. See the [contribution guide](./CONTRIBUTING.md) for more info. 

To run the tests simply run

```bash
$ ./vendor/bin/phpunit
```

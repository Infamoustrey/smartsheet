# Smartsheet

[![Latest Stable Version](https://poser.pugx.org/infamoustrey/smartsheet/v)](//packagist.org/packages/infamoustrey/smartsheet) [![Total Downloads](https://poser.pugx.org/infamoustrey/smartsheet/downloads)](//packagist.org/packages/infamoustrey/smartsheet) [![Latest Unstable Version](https://poser.pugx.org/infamoustrey/smartsheet/v/unstable)](//packagist.org/packages/infamoustrey/smartsheet) [![License](https://poser.pugx.org/infamoustrey/smartsheet/license)](//packagist.org/packages/infamoustrey/smartsheet)

This library serves as a convenience wrapper around the [REST API that smartsheet exposes](https://smartsheet-platform.github.io/api-docs/).
It also uses the [Collections](https://packagist.org/packages/illuminate/collections) library from the Illuminate library in lieu of arrays, so check that out if you are unfamiliar with it.

# Table of Contents

- [Installation](#installation)
- [Usage](#usage) 
    - [Sheets](#sheets) 
    - [Workspace](#workspace) 
    - [Folder](#folder) 
- [Issues](#issues)
- [Roadmap](#roadmap)
- [Contributing](#contributing)

## Installation

The preferred method of installing this library is with Composer by running the following from your project root:

```bash
composer require infamoustrey/smartsheet
```

## Usage 

This library provides a fluent api for interacting with Smartsheet.

```php
$smartsheetClient = new \Smartsheet\SmartsheetClient([ 'token' => 'yourapitoken' ]);

$smartsheetClient->getSheet('sheetid');
```

### Sheets

Fetch a list of sheets

```php
$smartsheetClient = new \Smartsheet\SmartsheetClient([ 'token' => 'yourapitoken' ]);

$smartsheetClient->listSheets(); // Collection<Sheet>
```

Access a sheet, see the [Sheet Object](https://smartsheet-platform.github.io/api-docs/#sheet-object) for a list of possible properties.

```php
$smartsheetClient = new \Smartsheet\SmartsheetClient([ 'token' => 'yourapitoken' ]);

$sheet = $smartsheetClient->getSheet('4583173393803140');

// Access some fields
$sheet->getId(); // '4583173393803140'
$sheet->getName(); // 'sheet 1'

// Add some rows
$sheet->addRow([
    'ID' => "39424808324",
    'Transaction Desc' => "Toys",
    'Amount' => 754.23,
]);

```

### Workspace

Fetch a workspace and access its properties. See the [Workspace Object](https://smartsheet-platform.github.io/api-docs/#objects-28) for a list of possible properties.

```php
$smartsheetClient = new \Smartsheet\SmartsheetClient([ 'token' => 'yourapitoken' ]);

$workspace = $smartsheetClient->getWorkspace('7116448184199044'); // \Smartsheet\Resources\Workspace

$workspace->getId(); // '7116448184199044'
$workspace->getName(); // 'New workspace'

$workspace->listSheets(); // Collection<Sheet>

// Fetch a sheet by name
$workspace->getSheet('sheet name'); // Sheet

// Create a sheet with some columns
$workspace->createSheet('sheet name', [
    [
        "title" => "Primary",
        "type" => "TEXT_NUMBER",
        "primary" => true
    ]
]);
```

### Folder

Fetch a folder and access its properties. See the [Folder Object](https://smartsheet-platform.github.io/api-docs/#folders) for a list of possible properties.

```php
$smartsheetClient = new \Smartsheet\Client([ 'token' => 'yourapitoken' ]);

$folder = $smartsheetClient->getFolder('7116448184199044'); // Folder

// Access some fields
$folder->getId(); // '7116448184199044'
$folder->getName(); // 'Projects'
$sheet = $folder->getSheet('sheet name');
```

## Issues

Use this repository's [issue tracker](https://github.com/Infamoustrey/smartsheet/issues) to resolve issues and ask questions.

## Roadmap

Full api coverage! There's a lot missing, if you see something missing then put in a PR! Your help is appreciated!

## Contributing

Feel free to [submit a PR](https://github.com/Infamoustrey/smartsheet/compare), just be sure to explain what you are trying to fix/add when submitting it. 
If you do decide to add functionality, it must be covered by a test. See the [contribution guide](./CONTRIBUTING.md) for more info. 

To run the tests simply run, you'll want to add a `.env` file(see `.env.example`) with a valid api token value in the `SMARTSHEET_API_TOKEN` variable. 
Tests are also run on pull requests. 

```bash
./vendor/bin/phpunit
```

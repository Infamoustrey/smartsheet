# Smartsheet

[![Latest Stable Version](https://poser.pugx.org/infamoustrey/smartsheet/v)](//packagist.org/packages/infamoustrey/smartsheet) [![Total Downloads](https://poser.pugx.org/infamoustrey/smartsheet/downloads)](//packagist.org/packages/infamoustrey/smartsheet) [![Latest Unstable Version](https://poser.pugx.org/infamoustrey/smartsheet/v/unstable)](//packagist.org/packages/infamoustrey/smartsheet) [![License](https://poser.pugx.org/infamoustrey/smartsheet/license)](//packagist.org/packages/infamoustrey/smartsheet)

This library serves as a convenience wrapper around the [REST API that smartsheet exposes](https://smartsheet-platform.github.io/api-docs/).
It also uses the [Collections](https://packagist.org/packages/illuminate/collections) library from the Illuminate library in lieu of arrays, so check that out if you are unfamiliar with it.

# Table of Contents

- [Installation](#installation)
- [Usage](#usage) 
    - [Client](#client)
    - [Sheets](#sheets) 
    - [Rows](#rows)
    - [Workspace](#workspace) 
    - [Folder](#folder) 
    - [Contacts](#contacts)
    - [Generic Resource Helpers](#generic-resource-helpers)
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

### Client

The `SmartsheetClient` class is the entry point for the currently supported API surface.

```php
$smartsheetClient = new \Smartsheet\SmartsheetClient([
    'token' => 'yourapitoken',
]);

$smartsheetClient->listSheets(); // Collection<Sheet>
$smartsheetClient->getSheet('4583173393803140'); // Sheet
$smartsheetClient->getRow('4583173393803140', '7813666446436228'); // Row
$smartsheetClient->getFolder('7116448184199044'); // Folder
$smartsheetClient->getWorkspace('7116448184199044'); // Workspace
$smartsheetClient->listWorkspaces(); // Collection<Workspace>
$smartsheetClient->listContacts(); // Collection<Contact>
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
 
Additional sheet operations:

```php
$sheet = $smartsheetClient->getSheet('4583173393803140');

$sheet->getColumns(); // array
$sheet->getRows(); // Collection<Row>
$sheet->getColumnId('Primary'); // string

$sheet->addRows([
    ['Primary' => 'row 1', 'Status' => 'Open'],
    ['Primary' => 'row 2', 'Status' => 'Closed'],
]);

$sheet->updateRow('7813666446436228', [
    'Status' => 'Done',
]);

$sheet->updateRows([
    '7813666446436228' => ['Status' => 'Done'],
    '1122334455667788' => ['Status' => 'Queued'],
]);

$sheet->deleteRow('7813666446436228');
$sheet->deleteRows(['7813666446436228', '1122334455667788']);

$sheet->rename('Renamed Sheet');
$sheet->copyTo('Copied Sheet', '7116448184199044');
$sheet->copyRowsTo(['7813666446436228'], '9988776655443322');

$sheet->addColumn([
    'title' => 'Status',
    'type' => 'TEXT_NUMBER',
]);

$sheet->addColumns([
    ['title' => 'Status', 'type' => 'TEXT_NUMBER'],
    ['title' => 'Owner', 'type' => 'TEXT_NUMBER'],
]);

$sheet->shareSheet([
    [
        'email' => 'user@example.com',
        'accessLevel' => 'EDITOR',
    ],
]);

$sheet->getShares();
```

For replacing data in-place, the library also exposes:

```php
$sheet->createRow(['Primary' => 'new row']);
$sheet->replaceFirstRow(['Primary' => 'updated first row']);
$sheet->replaceRows($rows, 'Primary');
$sheet->sync($rows, 'Primary');
$sheet->dropAllRows();
$sheet->dropAndReplace($rows);
$sheet->dropAllColumnsExcept(['Primary', 'Status']);
```

Summary field helpers:

```php
$sheet->addSummaryField('Total', '=COUNT([Primary]:[Primary])');
$sheet->getSummaryFields();
$sheet->getSummaryFieldByName('Total');
$sheet->updateSummaryField([
    'id' => 123,
    'title' => 'Total',
    'formula' => '=COUNT([Primary]:[Primary])',
]);
$sheet->updateSummaryFieldByName('Total', [
    'title' => 'Total',
    'formula' => '=COUNT([Primary]:[Primary])',
]);
$sheet->deleteSummaryField('123');
$sheet->deleteSummaryFields(['123', '456']);
$sheet->deleteAllSummaryFields();
```

### Rows

Rows can be fetched directly from the client or from a sheet collection.

```php
$row = $smartsheetClient->getRow('4583173393803140', '7813666446436228');

$row->getId();
$row->getSheet(); // Sheet
$row->getCells(); // Collection<Cell>
$row->getCell('Primary'); // Cell|null

$row->addAttachmentLink([
    'attachmentType' => 'LINK',
    'name' => 'Project Docs',
    'description' => 'External project documentation',
    'url' => 'https://example.com/docs',
]);

$row->addAttachment('/path/to/file.pdf');
$row->delete();
```

### Workspace

Fetch a workspace and access its properties. See the [Workspace Object](https://smartsheet-platform.github.io/api-docs/#objects-28) for a list of possible properties.

```php
$smartsheetClient = new \Smartsheet\SmartsheetClient([ 'token' => 'yourapitoken' ]);

$workspace = $smartsheetClient->getWorkspace('7116448184199044'); // \Smartsheet\Resources\Workspace

$workspace->getId(); // '7116448184199044'
$workspace->getName(); // 'New workspace'
$workspace->getSheets(); // array

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
$smartsheetClient = new \Smartsheet\SmartsheetClient([ 'token' => 'yourapitoken' ]);

$folder = $smartsheetClient->getFolder('7116448184199044'); // Folder

// Access some fields
$folder->getId(); // '7116448184199044'
$folder->get('name'); // 'Projects'
$folder->getPermaLink(); // 'https://app.smartsheet.com/...'
$folder->getSheets(); // array

$sheet = $folder->getSheet('sheet name');

$folder->createSheet('sheet name');
$folder->createSheets([
    'sheet one',
    'sheet two',
]);
```

### Contacts

Fetch account contacts:

```php
$contacts = $smartsheetClient->listContacts(); // Collection<Contact>
```

### Generic Resource Helpers

All resource objects extend `Smartsheet\Resources\Resource`, which provides a few helpers for accessing raw payload data:

```php
$sheet = $smartsheetClient->getSheet('4583173393803140');

$sheet->getData(); // full API payload as an array
$sheet->get('permalink'); // a single field from the payload
$sheet->toJSON(); // payload encoded as JSON
```

## Issues

Use this repository's [issue tracker](https://github.com/Infamoustrey/smartsheet/issues) to resolve issues and ask questions.

## Roadmap

Full api coverage! There's a lot missing, if you see something missing then put in a PR! Your help is appreciated!

## Contributing

Feel free to [submit a PR](https://github.com/Infamoustrey/smartsheet/compare), just be sure to explain what you are trying to fix/add when submitting it. 
If you do decide to add functionality, it must be covered by a test. See the [contribution guide](./CONTRIBUTING.md) for more info. 

To run the tests simply run:

```bash
./vendor/bin/phpunit
```

Static analysis can be run with:

```bash
vendor/bin/phpstan analyse src tests --memory-limit 1G
```

<?php

namespace Smartsheet\Resources;

use Exception;
use Illuminate\Support\Collection;
use Smartsheet\SmartsheetClient;

class Sheet extends Resource
{
    protected SmartsheetClient $client;

    protected string $id;

    protected string $name;

    protected string $version;

    protected bool $hasSummaryFields;

    protected string $permalink;

    protected string $createdAt;

    protected string $modifiedAt;

    protected bool $isMultiPickListEnabled;

    protected array $columns;

    protected array $rows;

    /**
     * Create a sheet resource.
     *
     * @param  SmartsheetClient  $client  The API client instance.
     * @param  array  $data  The raw sheet payload.
     */
    public function __construct(SmartsheetClient $client, array $data)
    {
        parent::__construct($data);

        $this->client = $client;
    }

    /**
     * Replace all sheet rows with the provided rows in batches.
     *
     * @param  array  $rows  The rows to insert after clearing the sheet.
     * @return void
     */
    public function dropAndReplace(array $rows)
    {
        $this->dropAllRows();

        foreach (collect($rows)->chunk(500) as $chunk) {
            $this->addRows($chunk->toArray());
        }
    }

    /**
     * Delete all rows currently loaded on the sheet.
     *
     * @return void
     */
    public function dropAllRows()
    {
        foreach (collect($this->get('rows'))->chunk(400) as $chunk) {
            $this->deleteRows(
                $chunk
                    ->pluck('id')
                    ->toArray()
            );
        }
    }

    /**
     * Delete all columns except the provided column names.
     *
     * @param  array  $columnNames  The column titles to keep.
     * @return void
     */
    public function dropAllColumnsExcept(array $columnNames)
    {
        $columnsToDelete = collect($this->columns)->filter(function ($column) use ($columnNames) {
            return ! in_array($column->title, $columnNames);
        })->pluck('id');

        foreach ($columnsToDelete as $columnId) {
            $this->client->delete("sheets/$this->id/columns/$columnId");
            sleep(1);
        }
    }

    /**
     * Copy the sheet to a destination folder.
     *
     * @param  string  $sheetName  The new sheet name.
     * @param  string  $destinationFolderId  The destination folder identifier.
     * @return object|null
     */
    public function copyTo(string $sheetName, string $destinationFolderId)
    {
        return $this->client->post("sheets/$this->id/copy", [
            'json' => [
                'newName' => $sheetName,
                'destinationType' => 'folder',
                'destinationId' => $destinationFolderId,
            ],
        ]);
    }

    /**
     * Copy rows from this sheet to another sheet.
     *
     * @param  array  $rowIds  The row identifiers to copy.
     * @param  string  $sheetId  The destination sheet identifier.
     * @return object|null
     */
    public function copyRowsTo(array $rowIds, string $sheetId)
    {
        return $this->client->post("sheets/$this->id/rows/copy?include=all", [
            'json' => [
                'rowIds' => $rowIds,
                'to' => [
                    'sheetId' => $sheetId,
                ],
            ],
        ]);
    }

    /**
     * Get the sheet rows as hydrated row resources.
     */
    public function getRows(): Collection
    {
        return collect($this->rows)
            ->map(function ($row) {
                return new Row($this->client, (array) $row, $this);
            });
    }

    /**
     * Get the identifier for a column by title.
     *
     * @param  mixed  $title  The column title.
     *
     * @throws Exception
     */
    public function getColumnId($title): string
    {
        $column = collect($this->columns)
            ->first(function ($col) use ($title) {
                return $col->title == $title;
            });

        if (is_null($column)) {
            throw new Exception('Unable to find column with the name: '.$title);
        }

        return $column->id;
    }

    /**
     * Convert row cell input into Smartsheet row cell payloads.
     *
     * @param  array  $cells  The row cells keyed by column title.
     *
     * @throws Exception
     */
    protected function generateRowCells(array $cells): array
    {
        $newCells = [];

        foreach ($cells as $title => $value) {
            if (is_array($value)) {
                if (array_key_exists('formula', $value)) {
                    $newCells[] = [
                        'columnId' => $this->getColumnId($title),
                        'formula' => $value['formula'],
                    ];
                } elseif (array_key_exists('hyperlink', $value)) {
                    $newCells[] = [
                        'columnId' => $this->getColumnId($title),
                        'value' => $value['value'],
                        'hyperlink' => $value['hyperlink'],
                    ];
                } else {
                    $newCells[] = [
                        'columnId' => $this->getColumnId($title),
                        'objectValue' => $value,
                    ];
                }
            } else {
                $newCells[] = [
                    'columnId' => $this->getColumnId($title),
                    'value' => $value,
                ];
            }
        }

        return $newCells;
    }

    /**
     * Adds a row to the sheet
     *
     * @param  array  $rows  The row payload to send to the API.
     */
    protected function insertRows(array $rows): object
    {
        return $this->client->post("sheets/$this->id/rows", [
            'json' => $rows,
        ]);
    }

    /**
     * Adds a row to the sheet
     *
     * @param  array  $cells  The row values keyed by column title.
     *
     * @throws Exception
     */
    public function addRow(array $cells): object
    {
        return $this->insertRows([
            'toBottom' => true,
            'cells' => $this->generateRowCells($cells),
        ]);
    }

    /**
     * Add multiple rows to the sheet.
     *
     * @param  array  $rows  A list of row definitions keyed by column title.
     */
    public function addRows(array $rows): object
    {
        return $this->insertRows(
            collect($rows)
                ->map(function ($cells) {
                    return [
                        'toBottom' => true,
                        'cells' => $this->generateRowCells($cells),
                    ];
                })
                ->values()
                ->toArray()
        );
    }

    /**
     * Update multiple rows on the sheet.
     *
     * @param  array  $rows  Row definitions keyed by row identifier.
     * @return mixed
     *
     * @throws Exception
     */
    public function updateRows(array $rows)
    {
        $rowsToUpdate = [];

        foreach ($rows as $id => $row) {
            $rowsToUpdate[] = [
                'id' => $id,
                'cells' => $this->generateRowCells($row),
            ];
        }

        return $this->client->put("sheets/$this->id/rows", [
            'json' => $rowsToUpdate,
        ]);
    }

    /**
     * Update a single row on the sheet.
     *
     * @param  mixed  $rowId  The row identifier.
     * @param  array  $cells  The row values keyed by column title.
     *
     * @throws Exception
     */
    public function updateRow($rowId, array $cells): mixed
    {
        $rowsToUpdate[] = [
            'id' => $rowId,
            'cells' => $this->generateRowCells($cells),
        ];

        return $this->client->put("sheets/$this->id/rows", [
            'json' => $rowsToUpdate,
        ]);
    }

    /**
     * Replace the first loaded row or create one if none exist.
     *
     * @param  array  $cells  The row values keyed by column title.
     * @return void
     */
    public function replaceFirstRow(array $cells)
    {
        if (count($this->rows) > 0) {
            $this->updateRow($this->rows[0]->id, $cells);
        } else {
            $this->addRows([$cells]);
        }
    }

    /**
     * Synchronize rows using a primary column match.
     *
     * @param  array  $rows  The row values to sync.
     * @param  string  $primaryColumnName  The primary matching column.
     * @return void
     */
    public function sync(array $rows, string $primaryColumnName = 'primary')
    {
        $this->replaceRows($rows, $primaryColumnName);
    }

    /**
     * Replace matching rows using a primary column value.
     *
     * @param  array  $cells  The row values to apply.
     * @param  string  $primaryColumnName  The matching column name.
     * @return void
     */
    public function replaceRows(array $cells, string $primaryColumnName)
    {
        if (count($this->rows) > 0) {
            $rowsToUpdate = [];

            foreach ($cells as $cell) {
                foreach ($this->getRows() as $row) {
                    if ($row->getCell($primaryColumnName) == $cell[$primaryColumnName]) {
                        $id = $row->getId();
                    }
                }

                if (isset($id)) {
                    $rowsToUpdate[$id] = $cell;
                }
            }

            $this->updateRows($rowsToUpdate);
        } else {

            $this->dropAllRows();

            $this->addRows([$cells]);
        }
    }

    /**
     * Adds a row to the sheet
     *
     * @param  array  $cells  The row values keyed by column title.
     *
     * @throws Exception
     */
    public function createRow(array $cells): object
    {
        return $this->insertRows([
            'toBottom' => true,
            'cells' => $this->generateRowCells($cells),
        ]);
    }

    /**
     * Get the sheet identifier.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the sheet name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Rename the sheet.
     *
     * @param  string  $newName  The new sheet name.
     * @return mixed
     */
    public function rename(string $newName)
    {
        return $this->client->put("sheets/$this->id", [
            'json' => [
                'name' => $newName,
            ],
        ]);
    }

    /**
     * Get the shares for the sheet.
     *
     * @return mixed
     */
    public function getShares()
    {
        return $this->client->get("sheets/$this->id/shares")->data;
    }

    /**
     * Share the sheet with the provided recipients.
     *
     * @param  array  $shares  The share definitions to create.
     * @return mixed
     */
    public function shareSheet(array $shares)
    {
        return $this->client->post("sheets/$this->id/shares", [
            'json' => [...$shares],
        ]);
    }

    /**
     * Delete a single row from the sheet.
     *
     * @param  string  $rowId  The row identifier.
     * @return mixed
     */
    public function deleteRow(string $rowId)
    {
        return $this->deleteRows([$rowId]);
    }

    /**
     * Delete multiple rows from the sheet.
     *
     * @param  array  $rowIds  The row identifiers to delete.
     * @return mixed
     */
    public function deleteRows(array $rowIds)
    {
        return $this->client->delete("sheets/$this->id/rows?ids=".implode(',', $rowIds));
    }

    /**
     * Get the sheet columns payload.
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Add a single column to the sheet.
     *
     * @param  array  $column  The column definition.
     * @return mixed
     */
    public function addColumn(array $column)
    {
        return $this->addColumns([$column]);
    }

    /**
     * Add multiple columns to the sheet.
     *
     * @param  array  $columns  The column definitions.
     * @return mixed
     */
    public function addColumns(array $columns)
    {
        return $this->client->post("sheets/$this->id/columns", [
            'json' => $columns,
        ]);
    }

    /**
     * Add a summary field to the sheet.
     *
     * @param  string  $title  The summary field title.
     * @param  string  $formula  The summary field formula.
     * @param  string  $type  The summary field type.
     * @return mixed
     */
    public function addSummaryField(string $title, string $formula, string $type = 'TEXT_NUMBER')
    {
        $options = [
            [
                'title' => $title,
                'type' => $type,
                'formula' => $formula,
            ],
        ];

        return $this->client->post("sheets/$this->id/summary/fields",
            ['json' => [...$options]]
        );
    }

    /**
     * Update a summary field by name.
     *
     * @param  string  $fieldName  The existing summary field name.
     * @param  array  $summaryFieldDefinition  The updated field definition.
     * @return mixed
     */
    public function updateSummaryFieldByName(string $fieldName, array $summaryFieldDefinition)
    {
        $summaryField = $this->getSummaryFieldByName($fieldName);
        $summaryFieldDefinition['id'] = $summaryField->id;

        return $this->updateSummaryField($summaryFieldDefinition);
    }

    /**
     * Update a single summary field.
     *
     * @param  array  $summaryField  The summary field definition.
     * @return mixed
     */
    public function updateSummaryField(array $summaryField)
    {
        return $this->updateSummaryFields([$summaryField]);
    }

    /**
     * Update multiple summary fields.
     *
     * @param  array  $summaryFields  The summary field definitions.
     * @return mixed
     */
    public function updateSummaryFields(array $summaryFields)
    {
        return $this->client->put("sheets/$this->id/summary/fields",
            ['json' => [...$summaryFields]]
        );
    }

    /**
     * Get a summary field by name.
     *
     * @param  string  $fieldName  The summary field title.
     * @return mixed
     */
    public function getSummaryFieldByName(string $fieldName)
    {
        return collect($this->getSummaryFields()->fields)
            ->first(fn ($field) => $field->title == $fieldName);
    }

    /**
     * Get the sheet summary field payload.
     *
     * @return mixed
     */
    public function getSummaryFields()
    {
        return $this->client->get("sheets/$this->id/summary");
    }

    /**
     * Delete multiple summary fields.
     *
     * @param  array  $fieldIds  The summary field identifiers to delete.
     * @return mixed
     */
    public function deleteSummaryFields(array $fieldIds)
    {
        return $this->client->delete("sheets/$this->id/summary/fields?ids=".implode(',', $fieldIds));
    }

    /**
     * Delete a single summary field.
     *
     * @param  string  $fieldId  The summary field identifier.
     * @return mixed
     */
    public function deleteSummaryField(string $fieldId)
    {
        return $this->deleteSummaryFields([$fieldId]);
    }

    /**
     * Delete all summary fields currently loaded on the sheet.
     *
     * @return mixed
     */
    public function deleteAllSummaryFields()
    {
        return $this->deleteSummaryFields(
            collect($this->getSummaryFields()->fields)
                ->pluck('id')
                ->toArray()
        );
    }
}

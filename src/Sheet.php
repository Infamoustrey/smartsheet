<?php

namespace Smartsheet;

use Exception;
use Tightenco\Collect\Support\Collection;

class Sheet extends Result
{

    protected Client $client;

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
     * Sheet constructor.
     * @param $data
     * @param Client $client
     */
    public function __construct($data, Client $client)
    {
        parent::__construct($data);

        $this->client = $client;
    }

    /**
     * Drops columns in a given list of column names
     * @param array $columnNames
     */
    public function dropColumns(array $columnNames): void
    {
        $columnsToDelete = collect($this->columns)->filter(function ($column) use ($columnNames) {
            return in_array($column->title, $columnNames);
        })->pluck('id');

        foreach ($columnsToDelete as $columnId) {
            $this->client->delete("sheets/$this->id/columns/$columnId");
            sleep(1);
        }
    }

    /**
     * Drops all columns excluding a list of given column names
     * @param array $columnNames
     */
    public function dropAllColumnsExcept(array $columnNames): void
    {
        $columnsToDelete = collect($this->columns)->filter(function ($column) use ($columnNames) {
            return !in_array($column->title, $columnNames);
        })->pluck('id');

        foreach ($columnsToDelete as $columnId) {
            $this->client->delete("sheets/$this->id/columns/$columnId");
            sleep(1);
        }
    }

    /**
     * Copy a sheet to the specified folder
     * @param string $sheetName
     * @param string $destinationFolderId
     * @return mixed
     */
    public function copyTo(string $sheetName, string $destinationFolderId)
    {
        return $this->client->post("sheets/$this->id/copy", [
            "json" => [
                'newName' => $sheetName,
                'destinationType' => 'folder',
                'destinationId' => $destinationFolderId
            ]
        ]);
    }

    /**
     *
     * @return Collection
     */
    public function getRows()
    {
        return collect($this->rows)->map(function ($row) {
            return new Row($row, $this->client, $this);
        });
    }

    /**
     * Deletes a give row
     * @param string $rowId
     * @return mixed
     */
    public function deleteRow(string $rowId)
    {
        return $this->client->delete("sheets/$this->id/rows?ids=$rowId");
    }

    /**
     * Returns a column's id given its title
     * @param $title
     * @return mixed
     * @throws Exception
     */
    public function getColumnId($title)
    {
        $column = collect($this->columns)
            ->first(function ($col) use ($title) {
                return $col->title == $title;
            });

        if (is_null($column)) {
            throw new \Exception('Unable to find column with the name: ' . $title);
        }

        return $column->id;
    }

    /**
     * Takes an array of column name to column value and maps it to a cell object
     * @param array $cells
     * @return array
     * @throws Exception
     */
    protected function mapDataToCell(array $cells)
    {
        $newCells = [];

        foreach ($cells as $title => $value) {
            if (is_array($value)) {
                $newCells[] = [
                    'columnId' => $this->getColumnId($title),
                    'objectValue' => $value
                ];
            } else {
                $newCells[] = [
                    'columnId' => $this->getColumnId($title),
                    'value' => $value
                ];
            }
        }

        return $newCells;
    }

    /**
     * Adds a row to the sheet
     *
     * @param array $rows
     * @return array
     */
    public function insertRows(array $rows)
    {
        return $this->client->post("sheets/$this->id/rows", [
            'json' => $rows
        ]);
    }

    /**
     * Adds a row to the sheet
     *
     * @param array $cells
     * @return array
     * @throws Exception
     */
    public function addRow(array $cells)
    {
        return $this->insertRows([
            'toBottom' => true,
            'cells' => $this->mapDataToCell($cells)
        ]);
    }

    /**
     * Adds a row to the sheet
     *
     * @param array $rows
     * @return array
     * @throws Exception
     */
    public function addRows(array $rows)
    {
        $rowsToInsert = collect($rows)->map(function ($cells) {
            return [
                'toBottom' => true,
                'cells' => $this->mapDataToCell($cells)
            ];
        });

        return $this->insertRows(
            $rowsToInsert->toArray()
        );
    }

    /**
     * Update rows
     * @param array $rows
     * @return mixed
     * @throws Exception
     */
    public function updateRows(array $rows)
    {
        $rowsToUpdate = [];

        foreach ($rows as $id => $row) {
            $rowsToUpdate[] = [
                'id' => $id,
                'cells' => $this->mapDataToCell($row)
            ];
        }

        return $this->client->put("sheets/$this->id/rows", [
            'json' => $rowsToUpdate
        ]);
    }

    /**
     * Get the sheet id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the sheet columns
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }


}

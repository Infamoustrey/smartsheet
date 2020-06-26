<?php

namespace Smartsheet;

use SplFileInfo;
use Tightenco\Collect\Support\Collection;

class Row extends Result
{
    protected Client $client;

    protected string $id;
    protected string $sheetId;
    protected int $rowNumber;
    protected array $cells;

    protected Sheet $sheet;

    public function __construct($data, Client $client, Sheet $sheet = null)
    {
        parent::__construct($data);

        $this->client = $client;

        $this->sheet = $sheet ?? $this->client->getSheet($this->sheetId);
    }

    /**
     * Returns the Row Id
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the instance of the sheet the row belongs to.
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->client->getSheet($this->sheetId);
    }

    /**
     * Return the cell for a given column name
     * @param string $columnName
     * @return mixed
     */
    public function getCell(string $columnName)
    {
        return $this->getCells()->first(function ($cell) use ($columnName) {
            return $cell->getColumnId() == $this->sheet->getColumnId($columnName);
        });
    }

    /**
     * Returns all of the cells for the row
     * @return Collection
     */
    public function getCells()
    {
        return collect($this->cells)->map(function ($cell) {
            return new Cell($cell);
        });
    }

    /**
     * @param array $attachment
     * @return mixed
     */
    public function addAttachmentLink(array $attachment)
    {
        return $this->client->post("sheets/$this->sheetId/rows/$this->id/attachments", [
            'json' => $attachment
        ]);
    }

    /**
     * @param SplFileInfo $file
     * @return bool|string
     */
    public function addAttachment(SplFileInfo $file)
    {
        $authHeader = "Bearer " . $this->client->getAuthToken();

        $request = curl_init("https://api.smartsheet.com/2.0/sheets/$this->sheetId/rows/$this->id/attachments");

        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt(
            $request,
            CURLOPT_HTTPHEADER,
            [
                'Authorization: ' . $authHeader,
                'Content-Disposition: attachment; filename="' . $file->getFilename() . '"'
            ]
        );
        curl_setopt($request, CURLOPT_POSTFIELDS, $file->get());
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

        $results = curl_exec($request);
        curl_close($request);

        return $results;
    }

    public function delete()
    {
        return $this->client->delete("sheets/$this->sheetId/rows?ids=$this->id");
    }
}

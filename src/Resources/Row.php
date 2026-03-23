<?php

namespace Smartsheet\Resources;

use Illuminate\Support\Collection;
use Smartsheet\SmartsheetClient;

class Row extends Resource
{
    protected SmartsheetClient $client;

    protected string $id;

    protected string $sheetId;

    protected int $rowNumber;

    protected array $cells;

    protected Sheet $sheet;

    /**
     * Create a row resource.
     *
     * @param  SmartsheetClient  $client  The API client instance.
     * @param  array  $data  The raw row payload.
     * @param  Sheet|null  $sheet  The already-hydrated parent sheet, if available.
     */
    public function __construct(SmartsheetClient $client, array $data, ?Sheet $sheet = null)
    {
        parent::__construct($data);

        $this->client = $client;

        $this->sheet = $sheet ?? $this->client->getSheet($data['sheetId']);
    }

    /**
     * Get the row identifier.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Fetch the parent sheet for the row.
     */
    public function getSheet(): Sheet
    {
        return $this->client->getSheet($this->sheetId);
    }

    /**
     * Get a cell by column name.
     *
     * @param  string  $columnName  The column title to match.
     * @return mixed
     */
    public function getCell(string $columnName)
    {
        return $this->getCells()->first(function ($cell) use ($columnName) {
            return $cell->getColumnId() == $this->sheet->getColumnId($columnName);
        });
    }

    /**
     * Get the row cells as hydrated cell resources.
     */
    public function getCells(): Collection
    {
        return collect($this->cells)->map(function ($cell) {
            return new Cell($this->client, (array) $cell);
        });
    }

    /**
     * Add a link attachment to the row.
     *
     * @param  array  $attachment  The attachment payload.
     */
    public function addAttachmentLink(array $attachment): object
    {
        return $this->client->post("sheets/$this->sheetId/rows/$this->id/attachments", [
            'json' => $attachment,
        ]);
    }

    /**
     * Upload a file attachment to the row.
     *
     * @param  string  $filepath  The local file path to upload.
     */
    public function addAttachment(string $filepath): string
    {
        $authHeader = 'Bearer '.$this->client->getToken();

        $request = curl_init("https://api.smartsheet.com/2.0/sheets/$this->sheetId/rows/$this->id/attachments");

        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt(
            $request,
            CURLOPT_HTTPHEADER,
            [
                'Authorization: '.$authHeader,
                'Content-Disposition: attachment; filename="'.basename($filepath).'"',
                'Content-Type: '.mime_content_type($filepath),
            ]
        );
        curl_setopt($request, CURLOPT_POSTFIELDS, file_get_contents($filepath));
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

        $results = curl_exec($request);
        curl_close($request);

        return $results;
    }

    /**
     * Delete the row.
     *
     * @return mixed
     */
    public function delete()
    {
        return $this->client->delete("sheets/$this->sheetId/rows?ids=$this->id");
    }
}

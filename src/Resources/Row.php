<?php

namespace Smartsheet\Resources;

use Smartsheet\SmartsheetClient;

use Tightenco\Collect\Support\Collection;

class Row extends Resource
{
    protected SmartsheetClient $client;

    protected string $id;
    protected string $sheetId;
    protected int $rowNumber;
    protected array $cells;

    protected Sheet $sheet;

    public function __construct($data, Sheet $sheet = null)
    {
        parent::__construct($data);

        $this->client = resolve(SmartsheetClient::class);

        $this->sheet = $sheet ?? $this->client->getSheet($this->sheetId);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSheet()
    {
        return $this->client->getSheet($this->sheetId);
    }

    public function getCell(string $columnName)
    {
        return $this->getCells()->first(function ($cell) use ($columnName) {
            return $cell->getColumnId() == $this->sheet->getColumnId($columnName);
        });
    }

    /**
     * @return Collection
     */
    public function getCells()
    {
        return collect($this->cells)->map(function ($cell) {
            return new Cell($cell);
        });
    }

    public function addAttachmentLink(array $attachment)
    {
        $this->client->post("sheets/$this->sheetId/rows/$this->id/attachments", [
            'json' => $attachment
        ]);
    }

    public function addAttachment(UploadedFile $file)
    {
        $authHeader = "Bearer " . config('services.smartsheet.token');

        $request = curl_init("https://api.smartsheet.com/2.0/sheets/$this->sheetId/rows/$this->id/attachments");

        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt(
            $request,
            CURLOPT_HTTPHEADER,
            [
                'Authorization: ' . $authHeader,
                'Content-Disposition: attachment; filename="' . $file->getClientOriginalName() . '"',
                'Content-Type: ' . $file->getMimeType()
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

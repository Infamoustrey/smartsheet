<?php

namespace Smartsheet;

class Sheets
{

    protected $client;

    public function __construct(Client $client = null)
    {
        $this->client = $client;
    }

    public function list()
    {
        $response = $this->client->get('sheets');

        $sheets = json_decode($response->getBody())->data;

        return $sheets;
    }

    public function fetch($sheetId)
    {
        $response = $this->client->get("sheets/$sheetId");

        $sheets = json_decode($response->getBody());

        return $sheets;
    }

    public function insertRow(string $sheetId, array $rows)
    {
        return $this->client->post("sheets/$sheetId/rows", [
            'json' => $rows
        ]);
    }

    public function addLinkAttachmentToRow(string $sheetId, string $rowId, array $attachment)
    {
        return $this->client->post("sheets/$sheetId/rows/$rowId/attachments", [
            'json' => $attachment
        ]);
    }

    public function deleteRow(string $sheetId, string $rowId)
    {
        return $this->client->delete("sheets/$sheetId/rows?ids=$rowId");
    }
}

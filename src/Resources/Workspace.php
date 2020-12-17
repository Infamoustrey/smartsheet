<?php

namespace Smartsheet\Resources;

use Exception;
use Smartsheet\SmartsheetClient;

const DEFAULT_COLUMNS = [
    [
        "title" => "Primary",
        "type" => "TEXT_NUMBER",
        "primary" => true
    ]
];

class Workspace extends Resource
{
    protected SmartsheetClient $client;

    protected string $id;
    protected string $name;
    protected array $sheets = [];

    public function __construct($data)
    {
        parent::__construct($data);

        $this->client = resolve(SmartsheetClient::class);
    }

    /**
     * 
     * @throws Exception
     */
    public function createSheet($name, $columns = DEFAULT_COLUMNS)
    {
        return $this->client->post("workspaces/$this->id/sheets", [
            'json' => [
                'name' => $name,
                'columns' => $columns
            ]
        ]);
    }

    /**
     * @return array
     */
    public function getSheets()
    {
        return $this->sheets;
    }

    /**
     * Fetches the sheet if it exists
     * @param string $name
     * @return string $id
     * @throws Exception
     */
    public function getSheetId(string $name)
    {
        $sheet = collect($this->sheets)
            ->first(function ($sheet) use ($name) {
                return $sheet->name === $name;
            });

        if (is_null($sheet)) {
            throw new Exception('Sheet does not exist.');
        }

        return $sheet->id;
    }

    /**
     * Fetches the sheet if it exists
     * @param string $name
     * @return Sheet $sheet
     * @throws Exception
     */
    public function getSheet(string $name)
    {
        return $this->client->getSheet($this->getSheetId($name));
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}

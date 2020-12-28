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

    /**
     * Workspace constructor.
     * @param SmartsheetClient $client
     * @param $data
     */
    public function __construct(SmartsheetClient $client, $data)
    {
        parent::__construct($data);

        $this->client = $client;
    }

    /**
     *
     * @param $name
     * @param array[] $columns
     * @return mixed
     */
    public function createSheet($name, $columns = DEFAULT_COLUMNS): mixed
    {
        return $this->client->post("workspaces/$this->id/sheets", [
            'json' => [
                'name' => $name,
                'columns' => $columns
            ]
        ]);
    }

    /**
     * Fetches the sheet if it exists
     * @param string $name
     * @return Sheet $sheet
     * @throws Exception
     */
    public function getSheet(string $name): Sheet
    {
        return $this->client->getSheet($this->getSheetId($name));
    }

    /**
     * @return array
     */
    public function getSheets(): array
    {
        return $this->sheets;
    }

    /**
     * Fetches the sheet if it exists
     * @param string $name
     * @return string $id
     * @throws Exception
     */
    public function getSheetId(string $name): string
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

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

}

<?php

namespace Smartsheet\Resources;

use Exception;
use Smartsheet\SmartsheetClient;

class Folder extends Resource
{
    protected SmartsheetClient $client;

    protected string $id;

    protected string $name;

    protected string $permaLink;

    protected array $sheets = [];

    /**
     * Create a folder resource.
     *
     * @param  SmartsheetClient  $client  The API client instance.
     * @param  array  $data  The raw folder payload.
     */
    public function __construct(SmartsheetClient $client, array $data)
    {
        parent::__construct($data);

        $this->client = $client;
    }

    /**
     * Create missing sheets in the folder by name.
     *
     * @param  array  $sheetNames  The list of sheet names to ensure exist.
     * @param  mixed  $columns  The column definition payload for new sheets.
     * @return Folder
     */
    public function createSheets(array $sheetNames, $columns = DEFAULT_COLUMNS): Folder
    {
        $sheets = collect($this->getSheets());

        foreach ($sheetNames as $sheetName) {
            if (is_null($sheets->firstWhere('name', $sheetName))) {
                $this->createSheet($sheetName, $columns);
            }
        }

        return $this->client->getFolder($this->id);
    }

    /**
     * Create a sheet in the folder.
     *
     * @param  mixed  $name  The sheet name.
     * @param  mixed  $columns  The column definition payload.
     * @return object|null
     *
     * @throws SmartsheetApiException
     */
    public function createSheet($name, $columns = DEFAULT_COLUMNS)
    {
        return $this->client->post("folders/$this->id/sheets", [
            'json' => [
                'name' => $name,
                'columns' => $columns,
            ],
        ]);
    }

    /**
     * Get the folder permalink.
     */
    public function getPermaLink(): string
    {
        return $this->permaLink;
    }

    /**
     * Get the folder sheets payload.
     *
     * @return array
     */
    public function getSheets(): array
    {
        return $this->sheets;
    }

    /**
     * Fetches the sheet if it exists
     *
     * @param  string  $name  The sheet name.
     * @return string
     *
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

    /**
     * Fetches the sheet if it exists
     *
     * @param  string  $name  The sheet name.
     * @return Sheet
     *
     * @throws Exception
     */
    public function getSheet(string $name): Sheet
    {
        return $this->client->getSheet(
            $this->getSheetId($name)
        );
    }

    /**
     * Get the folder identifier.
     */
    public function getId(): string
    {
        return $this->id;
    }
}

<?php

namespace Smartsheet;

use Illuminate\Support\Collection;
use Smartsheet\Resources\Contact;
use Smartsheet\Resources\Sheet;
use Smartsheet\Resources\Row;
use Smartsheet\Resources\Folder;
use Smartsheet\Resources\Workspace;

class SmartsheetClient extends APIClient
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    protected function instantiate(string $class, object|array $target): Object|Collection {
        if(is_array($target)) {
            $temp = [];

            foreach($target as $t) {
                $temp = new $class($this, $target);
            }
            
            return $temp;
        } else {
            return new $class($this, $target);
        }
    }

    /**
     * List Account Sheets
     *
     * @return Contact[]
     */
    public function listContacts(): Collection
    {
        $response = $this->get('contacts');

        if ($response->totalPages > 1) {

            $contacts = $response->data;

            for ($page = 2; $page <= $response->totalPages; $page++) {
                $contacts = array_merge($contacts, $this->get("contacts?page=$page")->data);
            }

            return $this->instantiate(Contact::class, $contacts);
        } else {
            return $this->instantiate(Contact::class, $response->data);
        }
    }

    /**
     * List Account Sheets
     *
     * @return Sheet[]
     */
    public function listSheets(): array
    {
        return $this->get('sheets')->data;
    }

    /**
     * Fetch a specific sheet
     *
     * @param string $sheetId
     * @return Sheet
     */
    public function getSheet(string $sheetId): Sheet
    {
        $response = $this->get("sheets/$sheetId");

        return new Sheet((array)$response);
    }

    /**
     * Fetch a specific row in a sheet
     *
     * @param string $sheetId
     * @param string $rowId
     * @return Row
     */
    public function getRow(string $sheetId, string $rowId): Row
    {
        $response = $this->get("sheets/$sheetId/rows/$rowId");

        return new Row((array)$response);
    }

    /**
     * @param string $folderId
     * @return Folder
     */
    public function getFolder(string $folderId)
    {
        return new Folder($this->get("folders/$folderId"));
    }

    /**
     * @param string $workspaceId
     * @return Workspace
     */
    public function getWorkspace(string $workspaceId)
    {
        return new Workspace($this->get("workspaces/$workspaceId"));
    }

    /**
     * Returns a list of workspaces
     * @return Workspace
     */
    public function listWorkspaces()
    {
        return $this->get("workspaces")->data;
    }
}

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

    protected function instantiateCollection(string $class, array $target): Collection
    {
        $temp = collect([]);

        foreach ($target as $t) {
            $temp->add(new $class($this, (array)$t));
        }

        return $temp;
    }

    /**
     * List Account Contacts
     */
    public function listContacts(): Collection
    {
        $response = $this->get('contacts');

        if ($response->totalPages > 1) {

            $contacts = $response->data;

            for ($page = 2; $page <= $response->totalPages; $page++) {
                $contacts = array_merge($contacts, $this->get("contacts?page=$page")->data);
            }

            return $this->instantiateCollection(Contact::class, $contacts);
        } else {
            return $this->instantiateCollection(Contact::class, $response->data);
        }
    }

    /**
     * List Account Sheets
     */
    public function listSheets(array $options = []): Collection
    {
        return $this->instantiateCollection(Sheet::class, $this->get('sheets', $options)->data);
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

        return new Sheet($this, (array)$response);
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

        return new Row($this, (array)$response);
    }

    /**
     * Fetch a folder with a given ID
     *
     * @param string $folderId
     * @return Folder
     */
    public function getFolder(string $folderId): Folder
    {
        return new Folder($this, (array) $this->get("folders/$folderId"));
    }

    /**
     * Fetch a workspace with a given ID
     *
     * @param string $workspaceId
     * @return Workspace
     */
    public function getWorkspace(string $workspaceId): Workspace
    {
        return new Workspace($this, (array) $this->get("workspaces/$workspaceId"));
    }

    /**
     * Returns a list of workspaces
     */
    public function listWorkspaces(): Collection
    {
        return $this->instantiateCollection(Workspace::class, $this->get("workspaces")->data);
    }
}

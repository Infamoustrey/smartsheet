<?php

namespace Smartsheet;

use Illuminate\Support\Collection;
use Smartsheet\Resources\Contact;
use Smartsheet\Resources\Folder;
use Smartsheet\Resources\Row;
use Smartsheet\Resources\Sheet;
use Smartsheet\Resources\Workspace;

class SmartsheetClient extends APIClient
{
    /**
     * Create a Smartsheet API client.
     *
     * @param  array  $config  Client configuration passed to the API client.
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * Hydrate a collection of Smartsheet resources.
     *
     * @param  class-string  $class  The resource class to instantiate.
     * @param  array  $target  The raw API payload items.
     */
    protected function instantiateCollection(string $class, array $target): Collection
    {
        $temp = collect([]);

        foreach ($target as $t) {
            $temp->add(new $class($this, (array) $t));
        }

        return $temp;
    }

    /**
     * List account contacts.
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
     * List account sheets.
     */
    public function listSheets(): Collection
    {
        return $this->instantiateCollection(Sheet::class, $this->get('sheets')->data);
    }

    /**
     * Fetch a specific sheet.
     *
     * @param  string  $sheetId  The sheet identifier.
     */
    public function getSheet(string $sheetId): Sheet
    {
        $response = $this->get("sheets/$sheetId");

        return new Sheet($this, (array) $response);
    }

    /**
     * Fetch a specific row in a sheet.
     *
     * @param  string  $sheetId  The parent sheet identifier.
     * @param  string  $rowId  The row identifier.
     */
    public function getRow(string $sheetId, string $rowId): Row
    {
        $response = $this->get("sheets/$sheetId/rows/$rowId");

        return new Row($this, (array) $response);
    }

    /**
     * Fetch a folder with a given ID.
     *
     * @param  string  $folderId  The folder identifier.
     */
    public function getFolder(string $folderId): Folder
    {
        return new Folder($this, (array) $this->get("folders/$folderId"));
    }

    /**
     * Fetch a workspace with a given ID.
     *
     * @param  string  $workspaceId  The workspace identifier.
     */
    public function getWorkspace(string $workspaceId): Workspace
    {
        return new Workspace($this, (array) $this->get("workspaces/$workspaceId"));
    }

    /**
     * Returns a list of workspaces.
     */
    public function listWorkspaces(): Collection
    {
        return $this->instantiateCollection(Workspace::class, $this->get('workspaces')->data);
    }
}

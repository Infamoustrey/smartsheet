<?php

namespace Smartsheet;

class Client extends APIClient
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * List Account Sheets
     *
     * @return Contact[]
     */
    public function listContacts(): array
    {
        $response = $this->get('contacts');

        if ($response->totalPages > 1) {

            $contacts = $response->data;

            for ($page = 2; $page <= $response->totalPages; $page++) {
                $contacts = array_merge($contacts, $this->get("contacts?page=$page")->data);
            }

            return $contacts;
        } else {
            return $response->data;
        }
    }

    /**
     * List Account Sheets
     * @return array
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

        return new Sheet((array)$response, $this);
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

        return new Row((array)$response, $this);
    }

    /**
     * @param string $folderId
     * @return Folder
     */
    public function getFolder(string $folderId)
    {
        return new Folder($this->get("folders/$folderId"), $this);
    }

    /**
     * @param string $workspaceId
     * @return mixed
     */
    public function getWorkspace(string $workspaceId)
    {
        $response = $this->get("workspaces/$workspaceId");

        return new Workspace((array)$response, $this);
    }

    /**
     * @return mixed
     */
    public function listWorkspaces()
    {
        $response = $this->get("workspaces");

        return $response->data;
    }


}

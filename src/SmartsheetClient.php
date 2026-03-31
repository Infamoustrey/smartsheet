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
     * Uses folder metadata + token-paginated children (replaces deprecated GET /folders/{id}).
     *
     * @param  string  $folderId  The folder identifier.
     */
    public function getFolder(string $folderId): Folder
    {
        $folderId = (string) $folderId;
        $meta = $this->get("folders/{$folderId}/metadata");
        $children = $this->fetchAllFolderChildren($folderId);

        return new Folder($this, $this->hydrateContainerFromMetaAndChildren($meta, $children));
    }

    /**
     * Fetch a workspace with a given ID.
     *
     * Uses workspace metadata + token-paginated children (replaces deprecated GET /workspaces/{id}).
     *
     * @param  string  $workspaceId  The workspace identifier.
     */
    public function getWorkspace(string $workspaceId): Workspace
    {
        $workspaceId = (string) $workspaceId;
        $meta = $this->get("workspaces/{$workspaceId}/metadata");
        $children = $this->fetchAllWorkspaceChildren($workspaceId);

        return new Workspace($this, $this->hydrateContainerFromMetaAndChildren($meta, $children));
    }

    /**
     * Returns a list of workspaces.
     *
     * Uses token-based pagination (replaces deprecated offset/includeAll defaults on GET /workspaces).
     */
    public function listWorkspaces(): Collection
    {
        $all = [];
        $lastKey = null;

        do {
            $query = http_build_query(array_filter([
                'paginationType' => 'token',
                'maxItems' => 100,
                'lastKey' => $lastKey,
            ], fn($v) => $v !== null && $v !== ''));

            $response = $this->get('workspaces?' . $query);
            $page = $response->data ?? [];

            foreach ($page as $row) {
                $all[] = $row;
            }

            $lastKey = $response->lastKey ?? null;
        } while (! empty($lastKey));

        return $this->instantiateCollection(Workspace::class, $all);
    }

    /**
     * @return array<int, object>
     */
    protected function fetchAllFolderChildren(string $folderId): array
    {
        return $this->fetchAllContainerChildren("folders/{$folderId}/children");
    }

    /**
     * @return array<int, object>
     */
    protected function fetchAllWorkspaceChildren(string $workspaceId): array
    {
        return $this->fetchAllContainerChildren("workspaces/{$workspaceId}/children");
    }

    /**
     * @return array<int, object>
     */
    protected function fetchAllContainerChildren(string $basePath): array
    {
        $all = [];
        $lastKey = null;

        do {
            $query = http_build_query(array_filter([
                'childrenResourceTypes' => 'sheets,folders',
                'maxItems' => 100,
                'lastKey' => $lastKey,
            ], fn($v) => $v !== null && $v !== ''));

            $response = $this->get($basePath . '?' . $query);
            $page = $response->data ?? [];

            foreach ($page as $item) {
                $all[] = $item;
            }

            $lastKey = $response->lastKey ?? null;
        } while (! empty($lastKey));

        return $all;
    }

    /**
     * Build a folder/workspace payload compatible with {@see Folder} and {@see Workspace}.
     *
     * @param  array<int, object>  $children
     * @return array<string, mixed>
     */
    protected function hydrateContainerFromMetaAndChildren(object $meta, array $children): array
    {
        $sheets = [];
        $folders = [];

        foreach ($children as $child) {
            // Children endpoints may use `resourceType` ("sheet"/"folder") or legacy `type` ("SHEET"/"FOLDER").
            $raw = $child->type ?? $child->resourceType ?? '';
            $type = strtoupper((string) $raw);

            if ($type === 'SHEET') {
                $sheets[] = $child;
            } elseif ($type === 'FOLDER') {
                $folders[] = $child;
            }
        }

        $permalink = (string) ($meta->permalink ?? '');

        return [
            'id' => (string) $meta->id,
            'name' => (string) $meta->name,
            'permalink' => $permalink,
            'permaLink' => $permalink,
            'sheets' => $sheets,
            'folders' => $folders,
        ];
    }
}

<?php

/**
 * This function is print the data
 * Instead of print_r
 *
 * @param [type] $array
 * @param boolean $exit
 * @return void
 */
function pre($array, $exit = true)
{
    echo '<pre>';
    print_r($array);
    echo '</pre>';

    if ($exit) {
        exit();
    }
}

if (!function_exists('permissionMiddleware')) {
    /**
     * Standard CRUD permission gate for a controller's HasMiddleware::middleware()
     * — one line per controller instead of repeating the same 4-entry block.
     *
     * Action-name grouping is a superset of every naming convention already
     * used across controllers (list/all/show/withStock => view, destroy/
     * destroyByUuid/bulkAction => delete, etc.) — `only()` is a no-op for
     * method names a given controller doesn't actually have, so passing the
     * full superset to every controller is safe.
     *
     * Usage: return permissionMiddleware('bank');
     * Non-standard action names (e.g. a bespoke `search` or `types` method
     * that isn't already covered by the view group) can be added per call:
     * return permissionMiddleware('item', ['view' => ['withStock']]);
     */
    function permissionMiddleware(string $module, array $extra = []): array
    {
        $groups = [
            'view' => ['list', 'all', 'show', 'search', 'types', 'sales', 'loginHistory', 'bySalesman', 'current', 'details', 'approverOptions', 'withStock'],
            'create' => ['store'],
            'edit' => ['update'],
            'delete' => ['destroy', 'destroyByUuid', 'bulkAction'],
        ];

        foreach ($extra as $action => $methods) {
            $groups[$action] = array_merge($groups[$action] ?? [], $methods);
        }

        return collect($groups)
            ->map(fn (array $methods, string $action) => new \Illuminate\Routing\Controllers\Middleware(
                "permission:{$module}.{$action}",
                only: $methods,
            ))
            ->values()
            ->all();
    }
}

if (!function_exists('paginated')) {
    function paginated($items, $itemName = 'items', $simple = false)
    {
        if ($simple) {
            return [
                'currentPage' => $items->currentPage(),
                'nextPage' => ($items->nextPageUrl() ? $items->currentPage() + 1 : null),
                $itemName => $items->items()
            ];
        }

        return [
            'total' => $items->total(),
            'currentPage' => $items->currentPage(),
            'nextPage' => $items->currentPage() < $items->lastPage() ? ($items->currentPage() + 1) : null,
            'lastPage' => $items->lastPage(),
            $itemName => $items->items()
        ];
    }
}
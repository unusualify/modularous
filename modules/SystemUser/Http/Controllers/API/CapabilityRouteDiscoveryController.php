<?php

namespace Modules\SystemUser\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class CapabilityRouteDiscoveryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = (string) ($request->query('search') ?? $request->query('q', ''));
        $onlyNamed = (bool) $request->boolean('only_named', true);
        $perPage = max(1, min((int) $request->query('itemsPerPage', $request->query('limit', 50)), 300));
        $page = max(1, (int) $request->query('page', 1));

        $routes = collect(Route::getRoutes())->map(function ($route) {
            $methods = array_values(array_filter(
                $route->methods(),
                fn ($method) => ! in_array($method, ['HEAD', 'OPTIONS'], true)
            ));

            return [
                'name' => $route->getName(),
                'route_name' => $route->getName(),
                'uri' => $route->uri(),
                'method' => $methods[0] ?? null,
                'methods' => $methods,
                'name_with_uri' => $route->uri() . ' - ' . $route->getName(),
            ];
        });

        if ($onlyNamed) {
            $routes = $routes->filter(fn ($item) => is_string($item['name']) && $item['name'] !== '');
        }

        if ($search !== '') {
            $routes = $routes->filter(function ($item) use ($search) {
                return str_contains((string) ($item['name'] ?? ''), $search)
                    || str_contains((string) ($item['uri'] ?? ''), $search);
            });
        }

        $items = $routes
            ->sortBy(fn ($item) => (string) ($item['name'] ?? $item['uri']))
            ->values();

        $paginated = $this->paginate($items, $page, $perPage);

        return response()->json([
            'resource' => [
                'data' => $paginated['data'],
                'current_page' => $paginated['current_page'],
                'last_page' => $paginated['last_page'],
                'per_page' => $perPage,
                'total' => $paginated['total'],
            ],
        ]);
    }

    private function paginate(Collection $items, int $page, int $perPage): array
    {
        $total = $items->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = min($page, $lastPage);

        return [
            'data' => $items->forPage($page, $perPage)->values()->all(),
            'current_page' => $page,
            'last_page' => $lastPage,
            'total' => $total,
        ];
    }
}

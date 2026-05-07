<?php

namespace Unusualify\Modularity\Http\Controllers\Utility;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use InvalidArgumentException;
use Unusualify\Modularity\Services\SlugInputValidationService;

/**
 * POST body: module, route, source (title-like string), locale?, locale_scoped?, exclude_id?
 *
 * @see SlugInputValidationService::proposeUniqueSlug()
 */
class SlugInputGenerateController extends Controller
{
    public function __invoke(Request $request, SlugInputValidationService $service): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string',
            'route' => 'required|string',
            'source' => 'required|string',
            'locale' => 'nullable|string|max:32',
            'locale_scoped' => 'sometimes|boolean',
            'exclude_id' => 'nullable|integer',
        ]);

        try {
            $result = $service->proposeUniqueSlug(
                $validated['module'],
                $validated['route'],
                $validated['source'],
                $validated['locale'] ?? null,
                $validated['locale_scoped'] ?? true,
                $validated['exclude_id'] ?? null,
            );

            return response()->json($result);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

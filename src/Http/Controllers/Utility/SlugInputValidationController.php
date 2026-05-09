<?php

namespace Unusualify\Modularous\Http\Controllers\Utility;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use InvalidArgumentException;
use Unusualify\Modularous\Services\SlugInputValidationService;

class SlugInputValidationController extends Controller
{
    /**
     * POST body: module, route, value?, locale?, locale_scoped?, exclude_id?
     */
    public function __invoke(Request $request, SlugInputValidationService $service): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'required|string',
            'route' => 'required|string',
            'value' => 'nullable|string',
            'locale' => 'nullable|string|max:32',
            'locale_scoped' => 'sometimes|boolean',
            'exclude_id' => 'nullable|integer',
        ]);

        try {
            $result = $service->validate(
                $validated['module'],
                $validated['route'],
                $validated['value'] ?? '',
                $validated['locale'] ?? null,
                $validated['locale_scoped'] ?? true,
                $validated['exclude_id'] ?? null,
            );

            return response()->json($result);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'valid' => false,
                'message' => $e->getMessage(),
                'normalized' => '',
            ], 422);
        }
    }
}

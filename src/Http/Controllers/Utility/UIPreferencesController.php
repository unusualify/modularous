<?php

namespace Unusualify\Modularity\Http\Controllers\Utility;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Http\Controllers\Traits\MakesResponses;

/**
 * Handles user UI preferences (navigation, sidebar, topbar, etc.).
 * Preferences are merged with PHP config defaults and persisted to DB.
 */
class UIPreferencesController extends Controller
{
    use MakesResponses;

    /**
     * Allowed keys for ui_preferences (whitelist for security).
     */
    protected array $allowedKeys = [
        'sidebar' => ['rail', 'location', 'width', 'expandOnHover', 'hideIcons', 'pinned', 'status'],
        'topbar' => ['enabled', 'fixed', 'order', 'showOnMobile', 'showOnDesktop'],
        'bottomNavigation' => ['enabled', 'showOnMobile', 'showOnDesktop'],
    ];

    /**
     * Update the authenticated user's UI preferences.
     */
    public function update(Request $request): JsonResponse
    {
        $user = Auth::guard('modularity')->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $input = $request->validate([
            'ui_preferences' => 'sometimes|array',
            'ui_preferences.sidebar' => 'sometimes|array',
            'ui_preferences.topbar' => 'sometimes|array',
            'ui_preferences.bottomNavigation' => 'sometimes|array',
        ]);

        $preferences = $request->input('ui_preferences', []);

        if (empty($preferences)) {
            return $this->respondWithSuccess(__('messages.save-success'), [
                'ui_preferences' => $user->ui_preferences ?? [],
            ]);
        }

        $filtered = $this->filterAllowedPreferences($preferences);
        $existing = $user->ui_preferences ?? [];
        $merged = array_replace_recursive($existing, $filtered);

        $user->update(['ui_preferences' => $merged]);

        return $this->respondWithSuccess(__('messages.save-success'), [
            'ui_preferences' => $user->fresh()->ui_preferences,
        ]);
    }

    /**
     * Filter allowed preference keys only.
     */
    protected function filterAllowedPreferences(array $preferences): array
    {
        $filtered = [];

        foreach ($this->allowedKeys as $section => $keys) {
            if (! isset($preferences[$section]) || ! is_array($preferences[$section])) {
                continue;
            }

            $filtered[$section] = array_intersect_key(
                $preferences[$section],
                array_flip($keys)
            );
        }

        return $filtered;
    }
}

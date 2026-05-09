<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

/**
 * Handles AJAX index requests (getByIds, getJSONData).
 * Extracted from BaseController::index() for separation of concerns.
 */
trait ManageIndexAjax
{
    /**
     * Respond to AJAX index request. Returns JSON response or null if not applicable.
     */
    protected function respondToIndexAjax(): ?JsonResponse
    {
        if (! $this->request->ajax()) {
            return null;
        }

        if (method_exists($this, 'isInertiaRequest') && $this->isInertiaRequest()) {
            return null;
        }

        if ($this->request->has('ids')) {
            return $this->respondToIndexAjaxByIds();
        }

        return $this->respondToIndexAjaxWithEager();
    }

    protected function respondToIndexAjaxByIds(): JsonResponse
    {
        $ids = $this->request->get('ids');
        $ids = is_string($ids) ? explode(',', $ids) : $ids;

        $eagers = $this->request->get('eagers') ?? [];
        $eagers = is_string($eagers) ? explode(',', $eagers) : $eagers;

        $scopes = $this->request->get('scopes') ?? [];
        $scopes = is_string($scopes) ? explode(',', $scopes) : $scopes;

        $orders = $this->request->get('orders') ?? [];
        $orders = is_string($orders) ? explode(',', $orders) : $orders;

        $appends = $this->request->get('appends') ?? [];
        $appends = is_string($appends) ? explode(',', $appends) : $appends;

        return Response::json(
            $this->repository->getByIds(
                ids: $ids,
                appends: $appends,
                with: $eagers,
                scopes: $scopes,
                orders: $orders,
            )
        );
    }

    protected function respondToIndexAjaxWithEager(): JsonResponse
    {
        $with = $this->request->get('eager', $this->request->get('with', []));
        $with = is_string($with) ? explode(',', $with) : $with;
        $with = is_array($with) ? $with : [];

        return Response::json([
            'resource' => $this->getJSONData(with: $with),
            'mainFilters' => $this->getTableMainFilters($this->getExactScope()),
            'replaceUrl' => $this->getReplaceUrl(),
        ]);
    }
}

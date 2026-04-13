<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Unusualify\Modularity\Services\MessageStage;

trait ManagePreview
{
    protected function addMiddlewarePermissionsManagePreview()
    {
        if($this->module && $this->routeHasTrait('revisions')) {
            $permissions = [
                'REVISION_RESTORE' => [ 'only' => ['restoreRevision']],
                'REVISION_APPROVE' => [ 'only' => ['approveRevision']],
                'REVISION_REJECT' => [ 'only' => ['rejectRevision']],
            ];

            foreach ($permissions as $permission => $options) {
                $this->setMiddlewarePermission($permission, $options);
            }
        }
    }
    public function previewData($item)
    {
        return [];
    }

    public function showView($id)
    {
        if ($this->request->has('revisionId')) {
            $item = $this->repository->previewForRevision($id, $this->request->get('revisionId'), $this->formSchema);
        } else {
            $formRequest = $this->validateFormRequest();
            $item = $this->repository->preview($id, $formRequest->all());
        }

        if ($this->request->has('activeLanguage')) {
            App::setLocale($this->request->get('activeLanguage'));
        }

        $previewView = $this->previewView ?? (Config::get('modularity.frontend.views_path', 'site') . '.' . Str::singular(
            $this->moduleName
        ));

        return View::exists($previewView) ? View::make(
            $previewView,
            array_replace([
                'item' => $item,
            ], $this->previewData($item))
        ) : View::make('twill::errors.preview', [
            'moduleName' => Str::singular($this->moduleName),
        ]);
    }

    public function listRevisions($id)
    {
        if(! $this->routeHasTrait('revisions')) {
            return $this->respondWithError(__('Revisions are not enabled for this route.'));
        }

        $object = $this->repository->getModel()->newQuery()->findOrFail($id);

        return $object->revisionsArray();
    }

    public function restoreRevision($id)
    {
        if (! $this->routeHasTrait('revisions')) {
            return $this->respondWithError(__('Revisions are not enabled for this route.'));
        }

        $params = $this->request->route()->parameters();
        $id = last($params);
        $revisionId = (int) $this->request->get('revisionId');
        // dd($revisionId);

        if ($revisionId < 1) {
            return $this->respondWithError(__('Revision id is required.'));
        }

        if ($this->request->get('preview')) {
            // dd("preview is called for revision id: $revisionId");
            $rawPayload = $this->repository->getRevisionPayload((int) $id, $revisionId);

            return Response::json([
                'form_fields' => $rawPayload,
            ]);
        }

        $item = $this->repository->restoreRevision((int) $id, $revisionId);
        // dd($item);

        return Response::json([
            'message' => __('Revision restored successfully.'),
            'variant' => MessageStage::SUCCESS,
            'revisions' => $item->revisionsArray(),
            'form_fields' => $this->repository->getFormFields($item, $this->getPreviousRouteSchema()),
        ]);
    }

    public function approveRevision($id)
    {
        if (! $this->routeHasTrait('revisions')) {
            return $this->respondWithError(__('Revisions are not enabled for this route.'));
        }

        $params = $this->request->route()->parameters();
        $id = last($params);
        $revisionId = (int) $this->request->get('revisionId');

        if ($revisionId < 1) {
            return $this->respondWithError(__('Revision id is required.'));
        }

        $item = $this->repository->approveRevision((int) $id, $revisionId);

        return Response::json([
            'message' => __('messages.revision.approved-success'),
            'variant' => MessageStage::SUCCESS,
            'revisions' => $item->revisionsArray(),
            'form_fields' => $this->repository->getFormFields($item, $this->getPreviousRouteSchema()),
        ]);
    }

    public function rejectRevision($id)
    {
        if (! $this->routeHasTrait('revisions')) {
            return $this->respondWithError(__('Revisions are not enabled for this route.'));
        }

        $params = $this->request->route()->parameters();
        $id = last($params);
        $revisionId = (int) $this->request->get('revisionId');

        if ($revisionId < 1) {
            return $this->respondWithError(__('Revision id is required.'));
        }

        $item = $this->repository->rejectRevision((int) $id, $revisionId);

        return Response::json([
            'message' => __('messages.revision.rejected-success'),
            'variant' => MessageStage::SUCCESS,
            'revisions' => $item->revisionsArray(),
            'form_fields' => $this->repository->getFormFields($item, $this->getPreviousRouteSchema()),
        ]);
    }

}

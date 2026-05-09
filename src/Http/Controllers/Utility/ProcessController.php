<?php

namespace Unusualify\Modularous\Http\Controllers\Utility;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Unusualify\Modularous\Entities\Process;
use Unusualify\Modularous\Facades\Modularous;

class ProcessController extends Controller
{
    public function show(Request $request, Process $process)
    {
        $processableModel = App::make($process->processable_type);
        $with = ['lastHistory'];

        $eager = $request->get('eager') ?? null;

        $definedRelations = $process->processable()->getRelated()->definedRelations();
        $nonEagerRelations = [];

        if ($eager) {
            $eagerRelations = array_intersect($definedRelations, explode(',', $eager));
            $nonEagerRelations = array_diff(explode(',', $eager), $definedRelations);
            $with['processable'] = array_merge(['fileponds'], $eagerRelations);

            // $with['processable'] = array_merge(['fileponds'], explode(',', $eager));
        } else {
            $with[] = 'processable.fileponds';
        }

        $process = Process::with($with)->find($process->id);

        if (count($nonEagerRelations) > 0) {
            $process->processable->load($nonEagerRelations);
        }

        $serializedProcess = $process->toArray();

        if (method_exists($processableModel, 'moduleName') && method_exists($processableModel, 'routeName')) {

            $module = Modularous::find($processableModel->getModuleName());
            $repository = App::make($module->getRouteClass($processableModel->getRouteName(), 'repository'));

            $processableFields = [];
            $schema = $module->getRouteInputs($processableModel->getRouteName());

            $processableFields = [];

            if (count($schema) > 0) {
                $processInput = Arr::first($schema, function ($input) {
                    return $input['type'] == 'process';
                });

                if ($processInput && isset($processInput['schema'])) {
                    $processableFields = $repository->getFormFields($process->processable, $schema);
                    $processableFields = Arr::only($processableFields, array_map(fn ($i) => $i['name'], $processInput['schema']));
                }

                // $processableFields = array_merge($processInput);
            }

            $serializedProcess['processable'] = array_merge($serializedProcess['processable'], $processableFields);
        }

        return response()->json($serializedProcess);
    }

    public function update(Request $request, Process $process)
    {
        $processableModel = App::make($process->processable_type);

        $processFields = Arr::only($request->all(), $process->getFillable());

        if (count($processFields) > 0) {
            $process->processable->setProcessStatus($request->get('status'), $request->get('reason') ?? null);
            $process->processable->touch();
        } elseif (method_exists($processableModel, 'moduleName') && method_exists($processableModel, 'routeName')) {
            $module = Modularous::find($processableModel->getModuleName());
            $schema = $module->getRouteInputs($processableModel->getRouteName());
            $repository = App::make($module->getRouteClass($processableModel->getRouteName(), 'repository'));

            foreach ($schema as $input) {
                if (isset($input['type']) && $input['type'] == 'process' && isset($input['schema'])) {
                    foreach ($input['schema'] as $schemaInput) {
                        if (isset($schemaInput['name'])) {
                            $schema[] = $schemaInput;
                        }
                    }
                }
            }

            $repository->update($process->processable_id, $request->all(), $schema);

            $process->processable->touch();
        }

        $process->refresh();

        return response()->json([
            'variant' => 'success',
            'message' => __('Process updated successfully'),
            'process_status' => $process->status,
        ]);
    }
}

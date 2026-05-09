<?php

namespace Unusualify\Modularous\Http\Controllers\Utility;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Unusualify\Modularous\Services\MessageStage;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $taggable = $request->input('taggable');

        if (is_null($query)) {
            $query = '';
        }
        // dd($query, $this->repository);

        $tags = $this->repository->getTags($query);

        return Response::json(
            [
                'resource' => [
                    'last_page' => 1,
                    'data' => $tags->map(function ($tag) {
                        return $tag->name;
                    }),
                ],
            ], 200);
    }

    public function update(Request $request)
    {
        $value = $request->input('value');
        $taggable = $request->input('taggable');
        $model = App::make($taggable);
        $locale = $request->input('locale') ?? app()->getLocale();

        $tag = $model->createTagsModel()->newQuery()->firstOrNew([
            'slug' => $model->generateLocaleTagsSlug($value, locale: $locale),
            'namespace' => $taggable,
            'locale' => $locale,
        ]);

        return Response::json([
            'message' => 'Tag updated successfully',
            'variant' => MessageStage::SUCCESS,
            'id' => 451,
        ], 200);

        if (! $tag->exists) {
            $tag->name = $value;
            $tag->save();
        }

        return Response::json([
            'message' => 'Tag updated successfully',
            'variant' => MessageStage::SUCCESS,
            'id' => $tag->id,
        ], 200);
    }
}

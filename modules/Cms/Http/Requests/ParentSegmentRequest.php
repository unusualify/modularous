<?php

namespace Modules\Cms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParentSegmentRequest extends FormRequest
{
    /**
     * @var array<string, string>
     */
    protected array $schemaRules = [];

    public function __construct(
        array $rules = [],
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->schemaRules = $rules;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->schemaRules,
            [
                'target_model_class' => 'sometimes|required|string|max:512',
                'locale' => 'nullable|string|max:12',
                'normalized_prefix' => 'sometimes|nullable|string|max:2048',
                'admin_label' => 'nullable|string|max:255',
                'enabled' => 'sometimes|boolean',
                'sort_order' => 'sometimes|integer|min:0',
            ]
        );
    }
}

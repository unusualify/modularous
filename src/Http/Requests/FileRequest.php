<?php

namespace Unusualify\Modularous\Http\Requests;

class FileRequest extends Request
{
    /**
     * Gets the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch (modularousConfig('file_library.endpoint_type')) {
            case 'local':
                return [
                    'qqfilename' => 'required',
                    'qqfile' => 'required',
                    'qqtotalfilesize' => 'required',
                ];
            case 'azure':
                return [
                    'blob' => 'required',
                    'name' => 'required',
                ];
            case 's3':
            default:
                return [
                    'key' => 'required',
                    'name' => 'required',
                ];
        }
    }
}

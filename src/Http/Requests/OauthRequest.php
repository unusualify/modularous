<?php

namespace Unusualify\Modularous\Http\Requests;

use Illuminate\Validation\Rule;

class OauthRequest extends Request
{
    /**
     * Include route parameters for validation
     *
     * @return array
     */
    public function all($keys = null)
    {

        $data = parent::all();
        $data['provider'] = $this->input('provider', $this->route('provider'));

        return $data;
    }

    /**
     * Gets the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $providers = array_keys(modularousConfig('oauth.providers', []));

        return [
            'provider' => [
                'required',
                Rule::in($providers),
            ],
        ];
    }

    protected function getRedirectUrl()
    {
        $url = $this->redirector->getUrlGenerator();

        return $url->route(config('modularous.admin_route_name_prefix') . '.loginHandleCallbackProvider', ['provider' => $provider]);

    }
}

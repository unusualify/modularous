<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Unusualify\Modularous\Entities\Company;
use Unusualify\Modularous\Events\ModularousUserRegistered;
use Unusualify\Modularous\Events\ModularousUserRegistering;
use Unusualify\Modularous\Services\MessageStage;

class RegisterController extends Controller
{
    public function showForm()
    {
        if (modularousConfig('email_verified_register')) {
            return redirect()->route(Route::hasAdmin('register.email_form'));
        }

        return $this->viewFactory->make(modularousBaseKey() . '::auth.register', $this->buildAuthViewData('register'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, $this->rules());
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User
     */
    protected function register(Request $request)
    {
        $emailVerifiedRegister = modularousConfig('email_verified_register');

        if ($emailVerifiedRegister) {
            return $request->wantsJson()
                ? new JsonResponse([
                    'variant' => MessageStage::ERROR,
                    'message' => 'Restricted Registration',
                    'redirector' => route(Route::hasAdmin('register.email_form')),
                    'login_page' => route(Route::hasAdmin('login.form')),
                ], 200)
                : redirect()->route(Route::hasAdmin('register.email_form'));
        }

        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return $request->wantsJson()
                ? new JsonResponse([
                    'errors' => $validator->errors(),
                    'message' => $validator->messages()->first(),
                    'variant' => MessageStage::WARNING,
                ], 422)
                : $request->validate($this->rules());

            return $res;
        }

        event(new ModularousUserRegistering($request));

        $user = Company::create([
            'name' => $request['company'] ?? '',
            'spread_payload' => [
                'is_personal' => $request['company'] ? false : true,
            ],
        ])->users()->create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'language' => $request['language'] ?? app()->getLocale(),
        ]);

        $user->assignRole(modularousConfig('default_register_role'));

        event(new ModularousUserRegistered($user, $request));

        return $request->wantsJson()
            ? new JsonResponse([
                'status' => 'success',
                'message' => 'User registered successfully',
                'redirector' => route(Route::hasAdmin('register.success')),
                'login_page' => route(Route::hasAdmin('login')),
            ], 200)
            : $this->sendLoginResponse($request);
    }

    public function rules()
    {
        $usersTable = modularousConfig('tables.users', 'um_users');

        return [
            'name' => ['required', 'string', 'max:255'],
            // Surname is not mandatory.
            'surname' => ['required', 'string', 'max:255'],
            // 'company' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . $usersTable . ',email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // 'tos' => ['required', 'boolean'],
        ];
    }

    public function success()
    {
        return view(modularousBaseKey() . '::auth.success', [
            'taskState' => [
                'status' => 'success',
                'title' => __('authentication.register-title'),
                'description' => __('authentication.register-description'),
                'button_text' => __('authentication.register-button-text'),
                'button_url' => route('admin.login'),
            ],
        ]);
    }
}

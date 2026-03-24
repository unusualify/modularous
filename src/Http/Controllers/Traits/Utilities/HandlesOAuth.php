<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Http\Controllers\Traits\Utilities;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Modules\SystemUser\Repositories\UserRepository;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Events\ModularityUserRegistered;
use Unusualify\Modularity\Events\ModularityUserRegistering;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Requests\OauthRequest;

/**
 * Provides OAuth authentication flow: redirect, callback, password confirmation, and provider linking.
 *
 * Required from using controller: $authManager, $redirector, $redirectTo, $viewFactory,
 * afterAuthentication(), attemptLogin(), authFormTitle(), authFormBaseAttributes(), authFormBottomSlots().
 */
trait HandlesOAuth
{
    /**
     * Redirect user to the OAuth provider.
     */
    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the OAuth provider callback.
     */
    public function handleProviderCallback(string $provider, OauthRequest $request)
    {
        try {
            $oauthUser = Socialite::driver($provider)->user();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->oauthErrorRedirect('Authentication Cancelled', 'Google authentication was cancelled. Please try again or use alternative login methods.');
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return $this->oauthErrorRedirect('Invalid State', 'Google authentication was invalid. Please try again or use alternative login methods.');
        } catch (\Exception $e) {
            return $this->oauthErrorRedirect('General Error', 'An error occurred during authentication. Please try again or use alternative login methods.');
        }

        $repository = App::make(UserRepository::class);

        if ($user = $repository->oauthUser($oauthUser)) {
            if ($repository->oauthIsUserLinked($oauthUser, $provider)) {
                $user = $repository->oauthUpdateProvider($oauthUser, $provider);
                $this->authManager->guard(Modularity::getAuthGuardName())->login($user);

                return $this->afterAuthentication($request, $user);
            }

            if ($user->password) {
                $request->session()->put('oauth:user_id', $user->id);
                $request->session()->put('oauth:user', $oauthUser);
                $request->session()->put('oauth:provider', $provider);

                return $this->redirector->to(route(Route::hasAdmin('admin.login.oauth.showPasswordForm')));
            }

            $user->linkProvider($oauthUser, $provider);
            $this->authManager->guard(Modularity::getAuthGuardName())->login($user);

            return $this->afterAuthentication($request, $user);
        }

        $request->merge([
            'email' => $oauthUser->email,
            'name' => $oauthUser->name ?? '',
            'surname' => $oauthUser->surname ?? $oauthUser->family_name ?? '',
        ]);

        event(new ModularityUserRegistering($request, isOauth: true));

        $user = $repository->oauthCreateUser($oauthUser);

        event(new ModularityUserRegistered($user, $request, isOauth: true));

        $user->linkProvider($oauthUser, $provider);
        $this->authManager->guard(Modularity::getAuthGuardName())->login($user);

        return $this->redirector->intended($this->redirectTo);
    }

    /**
     * Show password form when linking OAuth to existing account.
     */
    public function showPasswordForm(\Illuminate\Http\Request $request)
    {
        $userId = $request->session()->get('oauth:user_id');
        $user = User::findOrFail($userId);

        $oauthSchema = [
            'email' => [
                'type' => 'text',
                'name' => 'email',
                'label' => ___('authentication.email'),
                'hint' => 'enter @example.com',
                'default' => '',
                'col' => ['lg' => 12],
                'rules' => [['email']],
                'readonly' => true,
                'clearable' => false,
            ],
            'password' => [
                'type' => 'password',
                'name' => 'password',
                'label' => 'Password',
                'default' => '',
                'appendInnerIcon' => '$non-visibility',
                'slotHandlers' => ['appendInner' => 'password'],
                'col' => ['lg' => 12],
                'rules' => [],
            ],
        ];

        $provider = $request->session()->get('oauth:provider');

        $viewData = $this->buildAuthViewData('oauth_password', [
            'pageTitle' => __('authentication.confirm-provider', ['provider' => $provider]),
            'formAttributes' => array_merge(
                [
                    'title' => $this->authFormTitle(
                        __('authentication.confirm-provider', ['provider' => $provider]),
                        ['transform' => 'uppercase']
                    ),
                    'modelValue' => ['email' => $user->email, 'password' => ''],
                ],
                $this->authFormBaseAttributes(
                    $oauthSchema,
                    route(Route::hasAdmin('login.oauth.linkProvider')),
                    __('authentication.sign-in')
                )
            ),
        ]);

        return $this->viewFactory->make(modularityBaseKey() . '::auth.login', $viewData);
    }

    /**
     * Link OAuth provider after password verification.
     */
    public function linkProvider(\Illuminate\Http\Request $request)
    {
        if ($this->attemptLogin($request)) {
            $userId = $request->session()->get('oauth:user_id');
            $user = User::findOrFail($userId);

            $user->linkProvider($request->session()->get('oauth:user'), $request->session()->get('oauth:provider'));
            $this->authManager->guard(Modularity::getAuthGuardName())->login($user);

            $request->session()->forget(['oauth:user_id', 'oauth:user', 'oauth:provider']);

            return $this->afterAuthentication($request, $user);
        }

        throw ValidationException::withMessages([
            'password' => [trans('auth.failed')],
        ]);
    }

    /**
     * Redirect to login with OAuth error modal.
     */
    protected function oauthErrorRedirect(string $title, string $description)
    {
        $modalService = modularity_modal_service(
            'error',
            'mdi-alert-circle-outline',
            $title,
            $description,
            [
                'noCancelButton' => true,
                'confirmText' => 'Return to Login',
                'confirmButtonAttributes' => [
                    'color' => 'primary',
                    'variant' => 'elevated',
                ],
            ]
        );

        return redirect(merge_url_query(route('admin.login.form'), [
            'modalService' => $modalService,
        ]));
    }
}

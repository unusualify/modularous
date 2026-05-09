<?php

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Modules\SystemUser\Repositories\UserRepository;

class AuthorizationMiddleware
{
    /**
     * @var AuthFactory
     */
    protected $authFactory;

    public function __construct(AuthFactory $authFactory)
    {
        $this->authFactory = $authFactory;
    }

    public function handle($request, Closure $next)
    {
        view()->composer(modularousBaseKey() . '::layouts.master', function ($view) {
            $user = auth()->user();
            $userRepository = app()->make(UserRepository::class);
            $profileShortcutSchema = modularous_format_inputs(getFormDraft('profile_shortcut'));
            $profileShortcutModel = $userRepository->getFormFields($user, $profileShortcutSchema);
            $loginShortcutSchema = modularous_format_inputs(getFormDraft('login_shortcut'));

            $view->with(array_merge($view->getData(), [
                'authorization' => get_modularous_authorization_config(),
                'profileShortcutSchema' => $profileShortcutSchema,
                'profileShortcutModel' => $profileShortcutModel,
                'loginShortcutModel' => [],
                'loginShortcutSchema' => $loginShortcutSchema,
            ]));
        });

        return $next($request);
    }
}

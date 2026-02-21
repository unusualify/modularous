<?php

namespace Unusualify\Modularity\Http\Controllers\Auth;

use Illuminate\Auth\AuthManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Factory as ViewFactory;
use PragmaRX\Google2FA\Google2FA;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\Traits\Utilities\HandlesOAuth;
use Unusualify\Modularity\Services\MessageStage;

class LoginController extends Controller
{
    use AuthenticatesUsers, HandlesOAuth;

    /**
     * @var AuthManager
     */
    protected $authManager;

    /**
     * @var Encrypter
     */
    protected $encrypter;

    public function __construct(
        Config $config,
        AuthManager $authManager,
        Encrypter $encrypter,
        Redirector $redirector,
        ViewFactory $viewFactory
    ) {
        parent::__construct();

        $this->authManager = $authManager;
        $this->encrypter = $encrypter;
        $this->redirector = $redirector;
        $this->viewFactory = $viewFactory;
        $this->config = $config;

        $this->redirectTo = modularityConfig('auth_login_redirect_path', '/');
    }

    protected function guestMiddlewareExcept(): array
    {
        return ['logout'];
    }

    protected function guard()
    {
        return $this->authManager->guard(Modularity::getAuthGuardName());
    }

    public function showForm()
    {
        return $this->viewFactory->make(modularityBaseKey() . '::auth.login', $this->buildAuthViewData('login'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function showLogin2FaForm()
    {
        return $this->viewFactory->make(modularityBaseKey() . '::auth.2fa');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $this->redirector->to(route(Route::hasAdmin('login.form')));
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        return $this->afterAuthentication($request, $user);
    }

    protected function afterAuthentication(Request $request, $user)
    {
        // dd('here',$user->google_2fa_secret && $user->google_2fa_enabled);

        if ($user->google_2fa_secret && $user->google_2fa_enabled) {
            $this->guard()->logout();

            $request->session()->put('2fa:user:id', $user->id);

            return $request->wantsJson()
                ? new JsonResponse([
                    'redirector' => $this->redirector->to(route(Route::hasAdmin('admin.login-2fa.form')))->getTargetUrl(),
                ])
                : $this->redirector->to(route(Route::hasAdmin('admin.login-2fa.form')));
        }

        $previousRouteName = previous_route_name();

        $body = [
            'variant' => MessageStage::SUCCESS,
            'timeout' => 1500,
            'message' => __('authentication.login-success-message'),
        ];

        if (in_array($previousRouteName, ['admin.login.form', 'admin.login.oauth.showPasswordForm'])) {
            // 'redirector' => $this->redirector->intended($this->redirectPath())->getTargetUrl() . '?status=success',
            $body['redirector'] = redirect()->intended($this->redirectTo)->getTargetUrl();
        }

        if ($request->has('_timezone')) {
            session()->put('timezone', $request->get('_timezone'));
        }

        return $request->wantsJson()
            ? new JsonResponse($body, 200)
            : $this->redirector->intended($this->redirectPath());

    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function login2Fa(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');

        $user = User::findOrFail($userId);

        $valid = (new Google2FA)->verifyKey(
            $user->google_2fa_secret,
            $request->input('verify-code')
        );

        if ($valid) {
            $this->authManager->guard(Modularity::getAuthGuardName())->loginUsingId($userId);

            $request->session()->pull('2fa:user:id');

            return $this->redirector->intended($this->redirectTo);
        }

        return $this->redirector->to(route(Route::hasAdmin('admin.login-2fa.form')))->withErrors([
            'error' => 'Your one time password is invalid.',
        ]);
    }

    public function redirectTo()
    {
        return route(Route::hasAdmin('dashboard'));
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse([
                $this->username() => [trans('auth.failed')],
                'message' => __('auth.failed'),
                'variant' => MessageStage::WARNING,
            ], 200);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
            'message' => __('auth.failed'),
            'variant' => MessageStage::WARNING,
        ]);
    }



}

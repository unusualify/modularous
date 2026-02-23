<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Http\Controllers\Auth;

use Illuminate\Config\Repository as Config;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Factory as ViewFactory;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\Controller as BaseController;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularity\Http\Controllers\Traits\Utilities\AuthFormBuilder;

class Controller extends BaseController
{
    use AuthFormBuilder, ManageUtilities;

    protected ?Config $config = null;

    protected ?Redirector $redirector = null;

    protected ?ViewFactory $viewFactory = null;

    protected string $redirectTo = '/';

    public function __construct(
        ?Config $config = null,
        ?Redirector $redirector = null,
        ?ViewFactory $viewFactory = null
    ) {
        parent::__construct();

        $this->config = $config ?? app(Config::class);
        $this->redirector = $redirector ?? app(Redirector::class);
        $this->viewFactory = $viewFactory ?? app(ViewFactory::class);
        $this->redirectTo = modularityConfig('auth_login_redirect_path', '/');

        $except = $this->guestMiddlewareExcept();
        $this->middleware('modularity.guest', $except ? ['except' => $except] : []);
    }

    /**
     * Return route method names to exclude from guest middleware (e.g. ['logout']).
     *
     * @return array<int, string>
     */
    protected function guestMiddlewareExcept(): array
    {
        return [];
    }

    /**
     * Get the guard to be used during authentication.
     */
    protected function guard()
    {
        return Auth::guard(Modularity::getAuthGuardName());
    }

    /**
     * Get the redirect path after successful auth action.
     */
    protected function redirectPath()
    {
        return $this->redirectTo;
    }
}

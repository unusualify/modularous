<?php

namespace Unusualify\Modularous\Http\ViewComposers;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Unusualify\Modularous\Facades\Modularous;

class CurrentUser
{
    /**
     * @var AuthFactory
     */
    protected $authFactory;

    public function __construct(AuthFactory $authFactory)
    {
        $this->authFactory = $authFactory;
    }

    /**
     * Binds data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $currentUser = $this->authFactory->guard(Modularous::getAuthGuardName())->user();

        if ($currentUser) {
            $currentUser = get_user_profile($currentUser);
        }

        $view->with(compact('currentUser'));
    }
}

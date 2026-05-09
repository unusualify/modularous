<?php

namespace Unusualify\Modularous\Http\Controllers\Utility;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Modules\SystemUser\Repositories\UserRepository;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Controller;

class ImpersonateController extends Controller
{
    // /**
    //  * @var AuthManager
    //  */
    // protected $authManager;

    public function __construct(protected AuthManager $authManager)
    {
        parent::__construct();

        // $this->authManager = $authManager;
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function impersonate($id, UserRepository $users)
    {
        if ($this->authManager->guard(Modularous::getAuthGuardName())->user()->can('impersonate')) {
            $user = $users->getById($id);
            $this->authManager->guard(Modularous::getAuthGuardName())->user()->setImpersonating($user->id);
        }

        return back();
    }

    /**
     * @return RedirectResponse
     */
    public function stopImpersonate()
    {
        $this->authManager->guard(Modularous::getAuthGuardName())->user()->stopImpersonating();

        return back();
    }
}

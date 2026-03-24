<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Modules\SystemUser\Repositories\UserRepository;
use Unusualify\Modularity\Facades\Modularity;

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
        if ($this->authManager->guard(Modularity::getAuthGuardName())->user()->can('impersonate')) {
            $user = $users->getById($id);
            $this->authManager->guard(Modularity::getAuthGuardName())->user()->setImpersonating($user->id);
        }

        return back();
    }

    /**
     * @return RedirectResponse
     */
    public function stopImpersonate()
    {
        $this->authManager->guard(Modularity::getAuthGuardName())->user()->stopImpersonating();

        return back();
    }
}

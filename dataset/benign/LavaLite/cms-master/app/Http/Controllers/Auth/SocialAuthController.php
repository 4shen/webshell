<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Litepie\User\Traits\Auth\SocialAuthentication;
use Litepie\User\Traits\RoutesAndGuards;
use App\Http\Response\Auth\Response as AuthResponse;

class SocialAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */
    use SocialAuthentication, RoutesAndGuards;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $guard = request()->guard;
        guard($guard . '.web');
        $this->response   = resolve(AuthResponse::class);
        $this->middleware('guest', ['except' => 'logout']);
    }
}

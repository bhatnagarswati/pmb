<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
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

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest.admin')->except('logout');
    }

    public function showLoginForm()
    {
        //return "ddd";
        return view('admin.auth.login');
    }

    protected function guard()
    {
        return \Auth::guard('admins');
    }

    public function logout(Request $request)
    {
        \Auth::guard('admins')->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect('/admin');
    }
}

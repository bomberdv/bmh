<?php

namespace App\Http\Controllers\Auth;

use Mail;
use App\Models\User;
use Validator;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\AdminSettings;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function login(Request $request)
    {

         // get our login input
        //$login = $request->input('email');

        // check login field
        //$login_type = filter_var( $login, FILTER_VALIDATE_EMAIL ) ? 'email' : 'username';

        // merge our login field into the request with either email or username as key
        // $request->merge([ $login_type => $login ]);

        // let's validate and set our credentials
        /* if ( $login_type == 'email' ) {

             $this->validate($request, [
                 'username_or_email'    => 'required|email',
                 'password' => 'required',
             ]);

             $credentials = $request->only( 'email', 'password' );

         } else {

             $this->validate($request, [
                 'username_or_email' => 'required',
                 'password' => 'required',
             ]);

             $credentials = $request->only( 'username', 'password' );

         }*/

        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if ($this->auth->attempt($credentials, $request->has('remember'))) {
            if ($this->auth->User()->status == 'active') {
                $address = Address::where('user_id', '=', $this->auth->User()->id)->count();

                if ($address < 1) {
                    // Create Home and Company Address
                    $home = new Address;
                    $home->user_id = $this->auth->User()->id;
                    $home->jenis = 'rumah';
                    $home->save();

                    $company = new Address;
                    $company->user_id = $this->auth->User()->id;
                    $company->jenis = 'kantor';
                    $company->save();

                    return redirect()->intended('/');
                } else {
                    return redirect()->intended('/');
                }
            } elseif ($this->auth->User()->status == 'suspended') {
                $this->auth->logout();

                return redirect()->back()
                    ->withErrors([
                        'status' => trans('validation.user_suspended'),
                    ]);
            } elseif ($this->auth->User()->status == 'pending') {
                $this->auth->logout();

                return redirect()->back()
                    ->withErrors([
                        'status' => trans('validation.account_not_confirmed'),
                    ]);
            }
        }

        return redirect()->back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'email' => $this->getFailedLoginMessage(),
                    ]);
    }

    public function mobileLogin(Request $request)
    {
        $this->validate($request, [
          'email'    => 'required|email',
          'password' => 'required',
      ]);

        $credentials = $request->only('email', 'password');

        if ($this->auth->attempt($credentials, $request->has('remember'))) {
            if ($this->auth->User()->status == 'active') {
                // return redirect()->intended('/');
                return $this->auth->User();
            } elseif ($this->auth->User()->status == 'suspended') {
                return array( 'status' => 'error',
                      'status' => trans('validation.user_suspended'));
            } elseif ($this->auth->User()->status == 'pending') {
                return array( 'status' => 'error',
                        'status' => trans('validation.account_not_confirmed'));
            }
        }

        return array('status' => 'error','message' => $this->getFailedLoginMessage());
    }

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedLoginMessage()
    {
        return trans('auth.error_logging');
    }
}

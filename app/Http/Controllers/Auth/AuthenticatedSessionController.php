<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\Contact;
use App\Providers\AppServiceProvider;
use App\Rules\PhoneNumber;
use App\Traits\General;
use App\Traits\Notification;
use App\Traits\Recaptcha;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    use AuthenticatesUsers;
    use Notification;
    use General;
    use Recaptcha;

    protected $username;

    public function __construct()
    {
        $this->username = $this->findUsername();
    }

    public function findUsername()
    {
      
        $login = request()->input('login');
 
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : (preg_match('/^[0-9]+$/', $login) ? 'phone' : 'username');

        request()->merge([$fieldType => $login]);
        request()->merge(['fieldType' => $fieldType]);

        
        return $fieldType;
    }
 
    public function username()
    {
        return $this->username;
    }

    /**
     * Display the admin login view.
     *
     * @return \Inertia\Response
     */
    public function admin()
    {
        return Inertia::render('Auth/AdminLogin');
    }


    /**
     * Display the user login view.
     *
     * @return \Inertia\Response
     */
    public function user()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        if($request->get('recaptcha')){
            $result = $this->checkRecaptcha($request->get('recaptcha'));
            if ($result->success) {
                $request->authenticate();
                $request->session()->regenerate();
                return redirect()->intended(AppServiceProvider::HOME);
            }
            return redirect()->back()->with('error', 'بخش من ربات نیستم را تکمیل نمایید'); 
        }else{
            $request->authenticate();
            $request->session()->regenerate();
            return redirect()->intended(AppServiceProvider::HOME);
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function login(Request $request)
    {
        if ($request->get('phone')) {
            $request->merge([
                'phone' => $this->to_english_numbers($request->get('phone')),
            ]);
        }
        $request->validate([
            'phone' => ['required', 'max:11', new PhoneNumber],
        ]);
        $finduser = User::where('phone', $request->get('phone'))->first();
        $otp = random_int(1000, 9999);
        session()->put("otp", $otp);
        session()->put("phone", $request->get('phone'));
        session()->put("register", $finduser ? false : true);
        
        if($finduser){
            session()->put("password", ($finduser->password && !$request->get('otp')) ? true : false);
        }else{
            session()->put("password", false);
        }
     
        if($finduser && $finduser->password && !$request->get('otp')){
            return redirect()->route('login.verify');
        }
     
        $this->send_otp($otp, $request->get('phone'), env('GHASEDAKAPI_LOGIN_TEMPLATE'));
       
        return redirect()->route('login.verify')->with('success', 'کد تایید به تلفن همراه شما ارسال شد.');
    }

    public function verify()
    {
        if(session()->get("password")){
            return Inertia::render('Auth/GetPassword', [
                'phone' => session()->get("phone")
            ]);
        }else{
            return Inertia::render('Auth/OTP', [
                'phone' => session()->get("phone")
            ]);
        }
        
    }

    public function otp_verify(Request $request)
    {
        if ($request->get('otp')) {
            $request->merge([
                'otp' => $this->to_english_numbers($request->get('otp')),
            ]);
        }

        $request->validate([
            'otp' => ['required', 'min:4', 'max:4', 'in:' . session()->get("otp") . ''],
        ]);

        if (session()->get("register")) {
            $user = User::create([
                'phone' => session()->get("phone"),
                'role_id' => 2,
                'active' => true,
            ]);
            event(new Registered($user));
            Contact::create([
                'user_id' => $user->id,
            ]);
        }
        $finduser = User::where('phone', session()->get("phone"))->first();
        Auth::login($finduser);
        return redirect()->intended(AppServiceProvider::HOME);
    }
    

    public function forgotForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'recaptcha' => ['required'],
        ], ['recaptcha.required' => 'بخش من ربات نیستم را تکمیل نمایید',
            'email.exists' => 'کاربری با این ایمیل یافت نشد']);

        $result = $this->checkRecaptcha($request->get('recaptcha'));

        if ($result->success) {
            $status = Password::sendResetLink($request->only('email'));
            $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
            if ($status === "passwords.sent") {
                return redirect()->route('auth')->with('success', "ایمیل بازنشانی رمز عبور ارسال گردید");
            }
            return redirect()->back()->with('error', "در ارسال ایمیل خطایی رخ داده است یا دفعات ارسال بیش از حد مجاز است");
        }
        return redirect()->back()->with('error', 'بخش من ربات نیستم را تکمیل نمایید');

    }

    public function resetForm(Request $request)
    {
        return Inertia::render('Auth/ResetPassword', ['token' => $request->token, "email" => $request->email]);
    }

    public function ResetPassword(Request $request)
    {

        $result = $this->checkRecaptcha($request->get('recaptcha'));

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'password_confirmation' => ['required' ],
            'recaptcha' => ['required'],
        ], ['recaptcha.required' => 'بخش من ربات نیستم را تکمیل نمایید',
            'email.exists' => 'کاربری با این ایمیل یافت نشد',
            'password.confirmed' => 'تکرار رمز عبور اشتباه است']);

        if ($result->success) {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->setRememberToken(Str::random(60));
                    $user->save();
                    event(new PasswordReset($user));
                }
            );

            $status === Password::PASSWORD_RESET
            ? redirect()->route('auth')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
            if ($status == "passwords.reset") {
                return redirect()->route('auth')->with('success', "رمز عبور شما با موفقیت تغییر یافت");
            }
            return redirect()->back()->with('error', "خطا");

        }
        return redirect()->back()->with('error', 'بخش من ربات نیستم را تکمیل نمایید');

    }

}

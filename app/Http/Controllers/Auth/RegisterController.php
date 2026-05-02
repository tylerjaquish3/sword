<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'g-recaptcha-response' => ['required'],
        ]);

        $recaptcha = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ])->json();

        if (empty($recaptcha['success'])) {
            return back()->withErrors(['g-recaptcha-response' => 'Please complete the CAPTCHA.'])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => false,
            'is_admin' => false,
        ]);

        $adminEmail = env('ADMIN_EMAIL');
        if ($adminEmail) {
            $name      = $user->name;
            $email     = $user->email;
            $createdAt = now()->toDateTimeString();
            dispatch(function () use ($adminEmail, $name, $email, $createdAt) {
                Mail::raw(
                    "New user registered and is pending activation:\n\nName: {$name}\nEmail: {$email}\nRegistered: {$createdAt}",
                    fn ($msg) => $msg->to($adminEmail)->subject('Sword – New Pending User')
                );
            })->afterResponse();
        }

        return redirect()->route('login')
            ->with('warning', 'Your account has been created and is pending activation. An administrator will review your account shortly.');
    }
}

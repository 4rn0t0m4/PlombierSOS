<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Plumber;
use App\Models\User;
use App\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => 'Identifiants incorrects.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($validated);
        Auth::login($user);

        return redirect('/')->with('success', 'Bienvenue !');
    }

    public function showRegisterPro()
    {
        return view('auth.register-pro');
    }

    public function registerPro(Request $request, SlugService $slugService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'title' => 'required|string|max:255',
            'type' => 'required|in:0,1,2,3',
            'phone' => 'required|string|max:20',
            'siret' => 'nullable|string|max:14',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:5',
            'city' => 'required|string|max:255',
            'service_radius' => 'nullable|integer|min:1|max:100',
            'emergency_24h' => 'boolean',
            'free_quote' => 'boolean',
            'rge_certified' => 'boolean',
            'description' => 'nullable|string|max:5000',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'username' => Str::slug($validated['name'], '_'),
            'first_name' => $validated['name'],
            'phone' => $validated['phone'],
            'postal_code' => $validated['postal_code'],
            'city' => $validated['city'],
            'password' => $validated['password'],
        ]);

        $department = substr($validated['postal_code'], 0, 2);
        if (in_array($department, ['97', '98'])) {
            $department = substr($validated['postal_code'], 0, 3);
        }

        $cityModel = City::where('postal_code', $validated['postal_code'])->first()
            ?? City::where('name', 'LIKE', $validated['city'].'%')->where('department', $department)->first();

        $plumber = Plumber::create([
            'title' => $validated['title'],
            'slug' => $slugService->generateUniqueSlug($validated['title'], Plumber::class),
            'type' => (int) $validated['type'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'siret' => $validated['siret'],
            'address' => $validated['address'],
            'postal_code' => $validated['postal_code'],
            'city' => $validated['city'],
            'department' => $department,
            'city_id' => $cityModel?->id,
            'service_radius' => $validated['service_radius'] ?? 20,
            'emergency_24h' => $request->boolean('emergency_24h'),
            'free_quote' => $request->boolean('free_quote'),
            'rge_certified' => $request->boolean('rge_certified'),
            'description' => $validated['description'],
            'is_active' => false, // En attente de validation admin
        ]);

        $user->plumbers()->attach($plumber->id);

        // Notify admin
        Mail::raw(
            "Nouvelle inscription professionnel :\n\n"
            ."Entreprise : {$plumber->title}\n"
            ."Type : {$plumber->type_label}\n"
            ."Contact : {$validated['name']} ({$validated['email']})\n"
            ."Tél : {$validated['phone']}\n"
            ."Adresse : {$validated['address']}, {$validated['postal_code']} {$validated['city']}\n"
            .($validated['siret'] ? "SIRET : {$validated['siret']}\n" : '')
            ."\nValider dans l'admin : ".url('/admin/plombiers/'.$plumber->id.'/edit'),
            function ($msg) {
                $msg->to('contact@plombier-sos.fr')
                    ->bcc('arnotoma@gmail.com')
                    ->subject('Plombier SOS - Nouvelle inscription pro à valider');
            }
        );

        Auth::login($user);

        return redirect()->route('pro.dashboard')
            ->with('success', 'Votre inscription a bien été enregistrée. Votre fiche sera publiée après vérification par notre équipe.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect('/connexion')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}

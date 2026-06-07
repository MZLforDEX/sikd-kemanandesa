<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            ActivityLog::log('Login ke sistem', $user->id);

            return $this->redirectBasedOnRole($user)->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
        ]);

        // Default role is 'warga'
        $wargaRole = Role::where('name', 'warga')->first();
        if (!$wargaRole) {
            return back()->withErrors(['email' => 'Registrasi tidak dapat diproses saat ini.'])->onlyInput('email');
        }

        $user = User::create([
            'role_id' => $wargaRole->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        Auth::login($user);

        ActivityLog::log('Registrasi akun baru', $user->id);

        return redirect()->route('warga.dashboard')->with('success', 'Registrasi berhasil! Selamat datang di Sistem Keamanan Desa.');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::log('Logout dari sistem', Auth::id());
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Anda telah berhasil logout.');
    }

    public function switchRole($roleName)
    {
        if (config('app.env') !== 'local' && config('app.env') !== 'testing') {
            abort(403, 'Aksi simulasi hanya diizinkan di lingkungan lokal.');
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            abort(404);
        }

        $user = User::where('role_id', $role->id)->first();
        if ($user) {
            Auth::login($user);
            ActivityLog::log("Beralih simulasi peran ke: " . $role->display_name, $user->id);
            return $this->redirectBasedOnRole($user)->with('success', 'Beralih simulasi ke peran: ' . $role->display_name);
        }

        return redirect()->back()->with('error', 'Akun simulasi untuk peran tersebut tidak ditemukan.');
    }

    protected function redirectBasedOnRole($user)
    {
        $role = $user->role;
        if (!$role) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun Anda tidak memiliki peran yang valid.');
        }

        switch ($role->name) {
            case 'warga':
                return redirect()->route('warga.dashboard');
            case 'perangkat':
                return redirect()->route('perangkat.dashboard');
            case 'kades':
                return redirect()->route('kades.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login')->with('error', 'Role tidak dikenali.');
        }
    }
}

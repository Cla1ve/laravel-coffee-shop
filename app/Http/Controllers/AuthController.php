<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => ['nullable', 'regex:/^\+?[0-9]{10,15}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        // пароль хешируется сам (cast hashed в модели), роль по умолчанию user
        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Вы зарегистрированы');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($data, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Неверная почта или пароль'])->onlyInput('email');
        }

        // новая сессия после входа, чтобы не подменили старый идентификатор
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

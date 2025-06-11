<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Jobs\User\CreateUserJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function register(CreateUserRequest $request, Dispatcher $bus): RedirectResponse
    {
        $bus->dispatchSync(new CreateUserJob(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password')
        ));

        return redirect()->route('sign-in')->with('status', 'Registration successful. Please log in.');
    }

//    public function login(Request $request, UserQueryService $userQuery)
//    {
//        $credentials = $request->validate([
//            'email' => 'required|email',
//            'password' => 'required|string',
//        ]);
//
//        // Use Laravel's Auth or custom query service
//        if (Auth::attempt($credentials)) {
//            $request->session()->regenerate();
//            return redirect()->intended('dashboard');
//        }
//
//        return back()->withErrors([
//            'email' => 'The provided credentials do not match our records.',
//        ]);
//    }
//
//    public function logout(Request $request)
//    {
//        Auth::logout();
//        $request->session()->invalidate();
//        $request->session()->regenerateToken();
//        return redirect('/');
//    }
}

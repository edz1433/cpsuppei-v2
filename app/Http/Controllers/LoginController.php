<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{   
    //
    public function getLogin(){
        return view('login');
    }

    public function postLogin(Request $request){
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:5'
        ]);

        // Attempt to authenticate the user
        if (auth()->attempt([
            'username' => $request->username,
            'password' => $request->password,
        ])) {
            $user = auth()->user();
            if ($user->role === 'Campus Admin') {
                auth()->logout();
                return redirect()->back()->with('error', 'Your account is disabled at this time.');
            }
            return redirect()->route('dashboard')->with('success', 'Login Successfully');
        } else {
            return redirect()->back()->with('error', 'Invalid Credentials');
        }
    }
}

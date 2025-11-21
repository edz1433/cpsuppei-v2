<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{   
    public function getLogin(){
        return view('login');
    }

    public function postLogin(Request $request){
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (auth()->attempt([
            'username' => $request->username,
            'password' => $request->password,
        ])) {

            $user = auth()->user();

            if ($user->role === 'Technician') {
                if (isMobileDevice()) {
                    return redirect()->route('qr-scan')->with('success', 'Login Successfully');
                }
                return redirect()->route('dashboard')->with('success', 'Login Successfully');
            }

            return redirect()->route('dashboard')->with('success', 'Login Successfully');
        }

        return redirect()->back()->with('error', 'Invalid Credentials');
    }
}

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
            'username'=>'required',
            'password'=>'required|min:5|max:12'
        ]);

        $validated=auth()->attempt([
            'username'=>$request->username,
            'password'=>$request->password,
        ],$request->password);

        if($validated){
            return redirect()->route('dashboard')->with('success','Login Successfully');
        }else{
            return redirect()->back()->with('error','Invalid Credentials');
            //console.log($validated);
        }
    }
}

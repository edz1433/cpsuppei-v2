<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Campus;
use App\Models\Setting;

class UserController extends Controller
{
    //
    public function userRead() {
        $camp = Campus::all();
        $setting = Setting::firstOrNew(['id' => 1]);

        $user = User::join('campuses', 'users.campus_id', '=', 'campuses.id')
            ->select('users.id as uid', 'users.*', 'campuses.*')
            ->get();

        return view("users.list", compact('user', 'camp', 'setting'));
    }

    public function userCreate(Request $request) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $user = User::join('campuses', 'users.campus_id', '=', 'campuses.id')
                ->select('users.id as uid', 'users.*', 'campuses.*')
                ->get();

        if ($request->isMethod('post')) {
            $request->validate([
                'lname' => 'required',
                'fname' => 'required',
                'mname' => 'required',
                'gender' => 'required',
                'username' => 'required|string|min:5|unique:users,username',
                'password' => 'required|string|min:8',
                'campus_id' => 'required',
                'role' => 'required',
            ]);

            $userName = $request->input('username'); 
            $existingUser = User::where('username', $userName)->first();

            if ($existingUser) {
                return redirect()->route('userRead')->with('error', 'User already exists!');
            }

            try {
                User::create([
                    'lname' => $request->input('lname'),
                    'fname' => $request->input('fname'),
                    'mname' => $request->input('mname'),
                    'gender' => $request->input('gender'),
                    'username' => $userName,
                    'password' => Hash::make($request->input('password')),
                    'campus_id' => $request->input('campus_id'),
                    'role' => $request->input('role'),
                ]);

                return redirect()->route('userRead')->with('success', 'User stored successfully!');
            } catch (\Exception $e) {
                return redirect()->route('userRead')->with('error', 'Failed to store user!');
            }
        }
    }

    public function userEdit($id) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $camp = Campus::all();
        
        $user = User::join("campuses", "users.campus_id", "=", "campuses.id")
            ->select('users.id as uid', 'users.*', 'campuses.*')
            ->get();

        $selectedUser = User::join("campuses", "users.campus_id", "=", "campuses.id")
            ->select('users.id as uid', 'users.*', 'campuses.*')
            ->where('users.id', $id) 
            ->first();

        return view('users.list', compact('setting', 'user', 'camp', 'selectedUser'));
    }

    public function userUpdate(Request $request) {
        $request->validate([
            'id' => 'required',
            'lname' => 'required',
            'fname' => 'required',
            'mname' => 'required',
            'gender' => 'required',
            'username' => 'required',
            'password' => 'required',
            'campus_id' => 'required',
            'role' => 'required',
        ]);

        try {
            $userName = $request->input('username');
            $existingUser = User::where('username', $userName)->where('id', '!=', $request->input('id'))->first();

            if ($existingUser) {
                return redirect()->back()->with('error', 'User already exists!');
            }

            $user = User::findOrFail($request->input('id'));
            $user->update([
                'lname' => $request->input('lname'),
                'fname' => $request->input('fname'),
                'mname' => $request->input('mname'),
                'gender' => $request->input('gender'),
                'username' => $userName,
                'password' => Hash::make($request->input('password')),
                'campus_id' => $request->input('campus_id'),
                'role' => $request->input('role'), 
            ]);

            return redirect()->route('userEdit', ['id' => $user->id])->with('success', 'Updated Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update User!');
        }
    }
    public function userDelete($id){
        $users = User::find($id);
        $users->delete();

        return response()->json([
            'status'=>200,
            'uid'=>$id,
        ]);
    }

    public function appLogin(Request $request)
    {
        $credentials = $request->only('uname', 'pass');
        $uname = $credentials['uname'];
        $pass = $credentials['pass'];

        if (Auth::attempt(['username' => $uname, 'password' => $pass])) {
            $user = Auth::user();

            $token = ([
                'id' => $user->id,
                'role' => $user->role,
                'fname' => $user->fname,
                'lname' => $user->lname
            ]);
    
            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => '0'], 401);
    }
}

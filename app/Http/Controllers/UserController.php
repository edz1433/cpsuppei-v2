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
    public function userRead() {
        $camp = Campus::all();
        $setting = Setting::firstOrNew(['id' => 1]);

        $user = User::join('campuses', 'users.campus_id', '=', 'campuses.id')
            ->select('users.id as uid', 'users.*', 'campuses.*')
            // Supply Officers cannot see Administrator accounts
            ->when(auth()->user()->role === 'Supply Officer', function ($q) {
                $q->where('users.role', '!=', 'Administrator');
            })
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

            // Supply Officers are not allowed to create Administrator accounts
            if (auth()->user()->role === 'Supply Officer' && $request->input('role') === 'Administrator') {
                return redirect()->route('userRead')->with('error', 'You are not allowed to assign the Administrator role!');
            }

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
        
        $isSupplyOfficer = auth()->user()->role === 'Supply Officer';

        $user = User::join("campuses", "users.campus_id", "=", "campuses.id")
            ->select('users.id as uid', 'users.*', 'campuses.*')
            // Supply Officers cannot see Administrator accounts
            ->when($isSupplyOfficer, function ($q) {
                $q->where('users.role', '!=', 'Administrator');
            })
            ->get();

        $selectedUser = User::join("campuses", "users.campus_id", "=", "campuses.id")
            ->select('users.id as uid', 'users.*', 'campuses.*')
            ->where('users.id', $id)
            ->first();

        // Prevent Supply Officers from opening an Administrator account for editing
        if ($isSupplyOfficer && $selectedUser && $selectedUser->role === 'Administrator') {
            return redirect()->route('userRead')->with('error', 'You are not allowed to edit an Administrator account!');
        }

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
            $isSupplyOfficer = auth()->user()->role === 'Supply Officer';

            // Supply Officers cannot assign the Administrator role, nor edit an existing Administrator
            if ($isSupplyOfficer) {
                $targetUser = User::find($request->input('id'));

                if ($request->input('role') === 'Administrator' || ($targetUser && $targetUser->role === 'Administrator')) {
                    return redirect()->back()->with('error', 'You are not allowed to manage an Administrator account!');
                }
            }

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

        // Supply Officers cannot delete Administrator accounts
        if (auth()->user()->role === 'Supply Officer' && $users && $users->role === 'Administrator') {
            return response()->json([
                'status'  => 403,
                'message' => 'You are not allowed to delete an Administrator account!',
            ], 403);
        }

        $users->delete();

        return response()->json([
            'status'=>200,
            'uid'=>$id,
        ]);
    }
}

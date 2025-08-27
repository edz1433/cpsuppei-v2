<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Campus;
use App\Models\Setting;

use Exception;

class SettingsController extends Controller
{
    //
    public function user_settings() {
        $setting = Setting::firstOrNew(['id' => 1]);
        return view('settings.account_settings', compact('setting'));
    }

    public function setting_list() {
        $setting = Setting::firstOrNew(['id' => 1]);
        return view('settings.system_name', compact('setting'));
    }

    public function profileUpdate(Request $request) {
        try {
            $request->validate([
                'lname' => 'required|string|max:255',
                'fname' => 'required|string|max:255',
                'mname' => 'required|string|max:255',
                'username' => 'required|string|max:255',
                'role' => 'required',
                'gender' => 'required',
            ]);

            $user = Auth::user();
            $exist = User::where('username', $request->input('username'))
                        ->where('id', '!=', $user->id)
                        ->first();

            $updateData = [
                'lname'  => $request->input('lname'),
                'fname'  => $request->input('fname'),
                'mname'  => $request->input('mname'),
                'gender' => $request->input('gender'),
            ];

            if (!$exist) {
                $updateData['username'] = $request->input('username');
            }

            $user->update($updateData);

            return redirect()->route('user_settings')->with('success', 'Profile updated successfully');
        } catch (Exception $e) {
            return redirect()->route('user_settings')->with('error', 'Failed to update profile');
        }
    }

    public function profilePassUpdate(Request $request) {
        try {
            $request->validate([
                'password' => 'required|string|min:5,' . Auth::id(),
            ]);

            Auth::guard('web')->user()->update([
                'password' => Hash::make($request->input('password')),
            ]);

            return redirect()->route('user_settings')->with('success', 'Password updated successfully');
        } catch (Exception $e) {
            return redirect()->route('user_settings')->with('error', 'Failed to update Password');
        }
    }

    public function upload(Request $request) {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '_' . $photo->getClientOriginalName();
            $photo->move(public_path('uploads'), $filename);
            $systemName = $request->input('system_name');

            $setting = Setting::find(1);

            if ($setting && $setting->photo_filename) {
                $oldPhotoPath = public_path('uploads') . '/' . $setting->photo_filename;
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            if (!$setting) {
                $setting = new Setting(['id' => 1]);
            }

            $setting->photo_filename = $filename;
            $setting->system_name = $systemName; 
            $setting->save();
        }

        return redirect()->back()->with('success', 'Photo uploaded successfully!');
    }
}

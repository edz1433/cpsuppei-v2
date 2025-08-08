<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\Setting;
use App\Models\Accountable; 
use App\Models\Inventory;
use App\Models\InvQR;
use App\Models\Office;

class EnduserController extends Controller
{
    public function accountableRead() {
        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::all();
        
        $accnt = Accountable::join('offices', 'accountable.off_id', '=', 'offices.id')
            ->select('accountable.*', 'offices.office_name', 'accountable.id as aid');

        if (auth()->user()->role == "Campus Admin") {
            $accnt->where('camp_id', auth()->user()->campus_id);
        }

        $accnt = $accnt->get();
                
        return view('manage.enduser.accntlist', compact('setting', 'accnt', 'office'));
    }

    public function accountableCreate(Request $request)
    {
        $setting = Setting::firstOrNew(['id' => 1]);
        $accnt = Accountable::all();

        $request->validate([
            'person_accnt' => 'required|string|max:255',
            'desig_offid' => 'nullable',
            'off_id' => 'required',
            'accnt_role' => 'required',
        ]);

        $accntName = $request->input('person_accnt');
        $accnt_role = $request->input('accnt_role');
        $existingAccnt = Accountable::where('person_accnt', $accntName)->first();

        if ($existingAccnt) {
            return redirect()->route('accountableRead')->with('error', 'Accountable Person already exists!');
        }

        // Normalize desig_offid to always be an array or empty array
        $desigOffices = $request->input('desig_offid');

        if (is_null($desigOffices)) {
            $desigOffices = [];
        } elseif (!is_array($desigOffices)) {
            $desigOffices = [$desigOffices];
        }

        Accountable::create([
            'person_accnt' => $accntName,
            'desig_offid' => json_encode($desigOffices),
            'off_id' => $request->input('off_id'),
            'accnt_role' => $accnt_role,
        ]);

        return redirect()->route('accountableRead')->with('success', 'Item stored successfully!');
    }

    public function accountableEdit($id) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::all();

        $accnt = Accountable::join('offices', 'accountable.off_id', '=', 'offices.id')
                ->select('accountable.*', 'offices.office_name', 'accountable.id as aid');

        if (auth()->user()->role == "Campus Admin") {
            $accnt->where('camp_id', auth()->user()->campus_id);
        }

        $accnt = $accnt->get();

        $selectedAccnt = Accountable::join('offices', 'accountable.off_id', '=', 'offices.id')
                ->select('accountable.*', 'offices.office_name', 'accountable.id as aid')
                ->where('accountable.id', $id)
                ->first();

        return view('manage.enduser.accntlist', compact('setting', 'accnt', 'selectedAccnt', 'office'));
    }

    public function accountableUpdate(Request $request)
    {
        // Validate request data
        $request->validate([
            'id' => 'required|integer|exists:accountable,id',
            'person_accnt' => 'required|string|max:255',
            'desig_offid' => 'nullable',
            'off_id' => 'required',
            'accnt_role' => 'required|in:0,1,2',
        ]);

        $accountId = $request->input('id');
        $accntName = $request->input('person_accnt');
        $accnt_role = $request->input('accnt_role');

        // Normalize desig_offid to always be an array or empty array
        $desigOffices = $request->input('desig_offid');
        if (is_null($desigOffices)) {
            $desigOffices = [];
        } elseif (!is_array($desigOffices)) {
            $desigOffices = [$desigOffices];
        }

        // Determine off_id based on user role
        $role = auth()->user()->role;
        if ($role === "Campus Admin") {
            $offId = Office::where('camp_id', auth()->user()->campus_id)->first()->id ?? 0;
        } else {
            $offId = $request->input('off_id');
        }

        // Check if person_accnt already exists (excluding current record)
        $existingAccnt = Accountable::where('person_accnt', $accntName)
                                    ->where('id', '!=', $accountId)
                                    ->first();

        if ($existingAccnt) {
            return redirect()->back()->with('error', 'Accountable person already exists!');
        }

        // Find and update record
        $accnt = Accountable::findOrFail($accountId);
        $accnt->update([
            'person_accnt' => $accntName,
            'desig_offid' => json_encode($desigOffices),
            'off_id' => $offId,
            'accnt_role' => $accnt_role,
        ]);

        return redirect()->route('accountableEdit', ['id' => $accnt->id])
                        ->with('success', 'Updated Successfully');

    }

    public function accountableDelete($id){
        $accnt = Accountable::find($id);
        $accnt->delete();

        return response()->json([
            'status'=>200,
            'message'=>'Deleted Successfully',
        ]);
    }
}

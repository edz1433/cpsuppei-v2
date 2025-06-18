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

    public function accountableCreate(Request $request) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $accnt = Accountable::all();

        if ($request->isMethod('post')) {
            $request->validate([
                'person_accnt' => 'required|string|max:255',
            ]);
            
            $accntName = $request->input('person_accnt');
            $existingAccnt = Accountable::where('person_accnt', $accntName)->first();

            if ($existingAccnt) {
                return redirect()->route('accountableRead')->with('error', 'Accountable Person already exists!');
            }

            try {
                $role = auth()->user()->role;
                $officeId = $request->input('off_id', auth()->user()->campus_id);

                Accountable::create([
                    'person_accnt' => $request->input('person_accnt'),
                    'off_id' => $request->input('off_id', $officeId),
                ]);

                return redirect()->route('accountableRead')->with('success', 'Item stored successfully!');
            } catch (\Exception $e) {
                return redirect()->route()->with('error', 'Failed to store Accountable!');
            }
        }
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

    public function accountableUpdate(Request $request) {
        // Validate request data
        $request->validate([
            'id' => 'required',
            'person_accnt' => 'required',
        ]);
    
        try {
            $accountId = $request->input('id');
            $accntName = $request->input('person_accnt');

            $role = auth()->user()->role;
            if ($role == "Campus Admin") {
                $offId = Office::where('camp_id', auth()->user()->campus_id)->first()->id;
            }else {
                $offId = auth()->user()->campus_id;
            }
            
            $existingAccnt = Accountable::where('person_accnt', $accntName)
                                        ->where('id', '!=', $accountId)
                                        ->first();
    
            if ($existingAccnt) {
                return redirect()->back()->with('error', 'Accountable person already exists!');
            }
    
            $existingPersonAccnt = Accountable::where('person_accnt', $accntName)
                                              ->where('id', '!=', $accountId)
                                              ->first();
            if ($existingPersonAccnt) {
                return redirect()->back()->with('error', 'Accountable person already assigned to another office!');
            }
    
            $accnt = Accountable::findOrFail($accountId);
            $accnt->update([
                'person_accnt' => $accntName,
                'off_id' => $offId,
            ]);
    
            $inventories = Inventory::where('person_accnt', $accountId)->get();
            foreach ($inventories as $inventory) {
                if ($accntName != $inventory->person_accnt_name) {
                    $inventory->update([
                        'person_accnt_name' => $accntName,
                    ]);
    
                    InvQR::create([
                        'uid' => auth()->check() ? auth()->user()->id : null,
                        'inv_id' => $inventory->id,
                        'accnt_type' => 'accountable',
                        'person_accnt' => $accntName,
                        'remarks' => $inventory->remarks,
                        'comment' => 'Updating accountable person',
                    ]);
                }
            }
    
            return redirect()->route('accountableEdit', ['id' => $accnt->id])->with('success', 'Updated Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update Accountable!');
        }
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

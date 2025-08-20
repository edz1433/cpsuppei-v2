<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use App\Models\EnduserProperty;
use App\Models\Office;
use App\Models\InvQR;
use Carbon\Carbon;

class OfficeController extends Controller
{
    //
    public function officeRead($code){
        $setting = Setting::firstOrNew(['id' => 1]);
        $office = ($code == 1)
            ? Office::where('office_code', '!=', '0000')->get()
            : Office::where('office_code', '0000')->get();

        $campus = Office::whereNotNull('camp_id')
        ->where('camp_id', '!=', '')
        ->get();


        return view('manage.office.list', compact('setting', 'office', 'code', 'campus'));
    }
    
    public function officeCreate(Request $request) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::where('office_code', '!=', 0000)->get();

        if ($request->isMethod('post')) {
            $request->validate([
                'code' => 'required',
                'office_code' => 'required|numeric|digits:4',
                'office_name' => 'required',
                'office_abbr' => 'nullable',
                'office_officer' => 'nullable',
                'loc_camp' => 'nullable',
            ]);

            $officeName = $request->input('office_name');
            $existingOffice = Office::where('office_name', $officeName)->first();

            if ($existingOffice) {
                return redirect()->route('officeRead', $request->code)->with('error', 'Office already exists!');
            }

            try {
                Office::create([
                    'office_code' => $request->input('office_code'),
                    'office_name' => $request->input('office_name'),
                    'loc_camp' => $request->input('loc_camp'),
                    'office_abbr' => $request->input('office_abbr'),
                    'office_officer' => $request->input('office_officer'),
                ]);

                return redirect()->route('officeRead', $request->code)->with('success', 'Office stored successfully!');
            } catch (\Exception $e) {
                return redirect()->route('officeRead', $request->code)->with('error', 'Failed to store Office!');
            }
        }
    }

    public function officeEdit($id, $code) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $office = ($code == 1)
            ? Office::where('office_code', '!=', '0000')->get()
            : Office::where('office_code', '0000')->get();

        $campus = Office::whereNotNull('camp_id')
        ->where('camp_id', '!=', '')
        ->get();

        $selectedOffice = Office::findOrFail($id);

        return view('manage.office.list', compact('setting', 'office', 'selectedOffice', 'code', 'campus'));
    }

    public function officeUpdate(Request $request) {
        // Validate request data
        $request->validate([
            'id' => 'required',
            'code' => 'required',
            'office_code' => 'required',
            'office_name' => 'required',
            'office_abbr' => 'nullable',
            'office_officer' => 'nullable',
            'loc_camp' => 'nullable',
        ]);

        $officeId = $request->input('id');
        $officeName = $request->input('office_name');
        $officeOfficer = $request->input('office_officer');
    
        $existingOffice = Office::where('office_name', $officeName)
                                ->where('id', '!=', $officeId)
                                ->first();

        if ($existingOffice) {
            $error = ($request->code == 1) ? 'Office already exists!' : 'Location already exists!';
            return redirect()->back()->with('error', $error);
        }
    
        // $existingOfficer = Office::where('office_officer', $officeOfficer)
        //                          ->where('id', '!=', $officeId)
        //                          ->first();
        // if ($existingOfficer) {
        //     return redirect()->back()->with('error', 'Office officer already assigned to another office!');
        // }
    
        $office = Office::findOrFail($officeId);
        $office->update([
            'office_code' => $request->input('office_code'),
            'office_name' => $officeName,
            'office_abbr' => $request->input('office_abbr'),
            'office_officer' => $officeOfficer,
            'loc_camp' => $request->input('loc_camp'),
        ]);

        return redirect()->route('officeEdit', ['id' => $office->id, 'code' => $request->code])->with('success', 'Updated Successfully');
    }


    public function officeDelete($id){
        $office = Office::find($id);
        $office->delete();

        return response()->json([
            'status'=>200,
            'message'=>'Deleted Successfully',
        ]);
    }
}

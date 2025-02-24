<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\Setting;
use App\Models\Unit;

class UnitController extends Controller
{
    //
    public function unitRead(){
        $setting = Setting::firstOrNew(['id' => 1]);
        $unit = Unit::all();
        return view('manage.units.list', compact('setting', 'unit'));
    }

    public function unitCreate(Request $request) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $unit = Unit::all();

        if ($request->isMethod('post')) {
            $request->validate([
                'unit_name' => 'required|string|max:255',
            ]);

            $unitName = $request->input('unit_name');
            $existingUnit = Unit::where('unit_name', $unitName)->first();

            if ($existingUnit) {
                return redirect()->route('unitRead')->with('error', 'Unit already exists!');
            }

            try {
                Unit::create([
                    'unit_name' => $request->input('unit_name'),
                ]);

                return redirect()->route('unitRead')->with('success', 'Unit stored successfully!');
            } catch (\Exception $e) {
                return redirect()->route()->with('error', 'Failed to store unit!');
            }
        }
    }

    public function unitEdit($id) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $unit = Unit::all();

        $selectedUnit = Unit::findOrFail($id);

        return view('manage.units.list', compact('setting', 'unit', 'selectedUnit'));
    }

    public function unitUpdate(Request $request) {
        $request->validate([
            'id' => 'required',
            'unit_name' => 'required',
        ]);

        try {
            $unitName = $request->input('unit_name');
            $existingUnit = Unit::where('unit_name', $unitName)->where('id', '!=', $request->input('id'))->first();

            if ($existingUnit) {
                return redirect()->back()->with('error', 'Unit already exists!');
            }

            $unit = Unit::findOrFail($request->input('id'));
            $unit->update([
                'unit_name' => $unitName,
            ]);

            return redirect()->route('unitEdit', ['id' => $unit->id])->with('success', 'Updated Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update unit!');
        }
    }

    public function unitDelete($id){
        $unit = Unit::find($id);
        $unit->delete();

        return response()->json([
            'status'=>200,
            'message'=>'Deleted Successfully',
        ]);
    }
    

}

<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\EnduserProperty;
use App\Models\Setting;
use App\Models\Repair;

class TechController extends Controller
{
    public function repairCreate(Request $request)
    {
        $propid = $request->query('propid');
        $uid = $request->query('uid');
        $issue = $request->query('issue');
        $urgency = $request->query('urgency');
        $remarks = $request->query('remarks', null);
    
        if (!$propid || !$uid || !$issue || !$urgency) {
            return response()->json(['error' => 'Missing required fields.'], 400);
        }
    
        $enduser = EnduserProperty::where('property_no_generated', $propid)->first(['id']);
    
        if (!$enduser) {
            return response()->json(['error' => 'Invalid property number.'], 404);
        }
    
        $repair = Repair::create([
            'prop_id' => $enduser->id,
            'uid' => $uid,
            'issue' => $issue,
            'urgency' => $urgency,
            'remarks' => $remarks,
        ]);
    
        return response()->json(['success' => 'New issue added successfully!', 'repair' => $repair], 201);
    }

    public function repairRead(){ 

        $setting = Setting::firstOrNew(['id' => 1]);
        $enduserall = EnduserProperty::all();
        $repairs = DB::table('repairs')
        ->join('enduser_property', 'repairs.prop_id', '=', 'enduser_property.id') 
        ->select('repairs.*', 'enduser_property.remarks', 'repairs.status','enduser_property.property_no_generated','repairs.id as repair_id', 'repairs.prop_id', 'enduser_property.qty','enduser_property.id','enduser_property.item_id','enduser_property.item_descrip') 
        ->get();
    
          return view('repair.repairtech', compact('setting','repairs', 'enduserall'));
    }
    
}


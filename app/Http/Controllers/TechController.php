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
    
        $inventory = EnduserProperty::where('property_no_generated', $propid)->first(['id']);
    
        if (!$inventory) {
            return response()->json(['error' => 'Invalid property number.'], 404);
        }
    
        $repair = Repair::create([
            'prop_id' => $inventory->id,
            'uid' => $uid,
            'issue' => $issue,
            'urgency' => $urgency,
            'remarks' => $remarks,
        ]);
    
        return response()->json(['success' => 'New issue added successfully!', 'repair' => $repair], 201);
    }
      

    public function repairRead(){ 

        $setting = Setting::firstOrNew(['id' => 1]);

        $repairs = DB::table('repairs')
        ->join('enduser_property', 'repairs.prop_id', '=', 'enduser_property.id') 
        ->select('repairs.*', 'enduser_property.remarks', 'repairs.status','enduser_property.property_no_generated','repairs.id as repair_id', 'repairs.prop_id', 'enduser_property.qty','enduser_property.id','enduser_property.item_id','enduser_property.item_descrip') 
        ->get();
    
          return view('properties.repairtech', compact('setting','repairs'));
    }

   public function editEssue($id){

   $setting = Setting::firstOrNew(['id' => 1]);
    try {
        
        $repairs = DB::table('repairs')
            ->join('enduser_property', 'repairs.prop_id', '=', 'enduser_property.id') 
            ->select('repairs.*', 'enduser_property.remarks', 'repairs.status','enduser_property.property_no_generated', 'repairs.prop_id','repairs.id as repair_id', 'enduser_property.qty','enduser_property.id','enduser_property.item_id','enduser_property.item_descrip') 
            ->get();
        
            $issues = Repair::find($id);
            
            return view('properties.repairtech', compact('setting','repairs','issues'));
            
    } catch (QueryException $e) {
       
        echo 'Query failed: ' . $e->getMessage();
    } catch (\Exception $e) {
       
        echo 'An error occurred: ' . $e->getMessage();
    }
        }
    public function issueUpdate(Request $request){
 
    $issue = Repair::findOrFail($request->id);       

    $issue->status = $request->input('status');
    $issue->remarks = $request->input('remarks');
    $issue->urgency = $request->input('urgency');
    $issue->issue = $request->input('issue');

    $issue->save();

    return redirect()->back()->with('success', 'Updated Successfully');

        }
    public function issueInsertion(){
        $setting = Setting::firstOrNew(['id' => 1]);
        try {
             
        $datas = EnduserProperty::where('item_descrip', $prop)->first(['id']);
           
        if ($datas) {
            $id = $datas->id;
            $insert_issue = new Repair();
            $insert_issue->prop_id = $id;
            $insert_issue->issue = $issue;
            $insert_issue->urgency =$urgency;
            $insert_issue->save();  
                session()->flash('success', 'The operation was successful!');
                return redirect()->route('repairProp')->with('success', 'New issue added successful!');
      
               } else {
                   throw new Exception("No inventory data found for the given property number.");
               }
           
           } catch (\Exception $e) {
             
               echo "Error: " . $e->getMessage();
           }

        }
    
}


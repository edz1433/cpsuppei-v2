<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\EnduserProperty;
use App\Models\Purchases;
use App\Models\Office;
use App\Models\Accountable;
use App\Models\property;
use App\Models\Properties;
use App\Models\Unit;
use App\Models\Item;
use App\Models\Campus;
use App\Models\Category;
use App\Models\InvQR;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Setting;
use App\Models\InvSetting;
use App\Models\YearlyInventory;
use App\Models\InventoryHistory;
use PDF;

class InventoryController extends Controller
{
    public function inventoryRead(Request $request) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $yearinvs = YearlyInventory::all();
        $countInv = YearlyInventory::where('inv_status', 'Ongoing')->count();
        
        return view('inventory.invlist', compact('setting', 'yearinvs', 'countInv'));
    }

    public function inventoryView(Request $request) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $yearinvs = YearlyInventory::all();
        $countInv = YearlyInventory::where('inv_status', 'Ongoing')->count();

        $ucampid = auth()->user()->campus_id;
        $exists = Office::whereNotNull('camp_id')
            ->where('camp_id', $ucampid)
            ->exists();

        $inventoryQuery = InventoryHistory::join('enduser_property', 'inventory_histories.prop_id', '=', 'enduser_property.id')
            ->join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->select(
            'offices.camp_id',
            \DB::raw("COALESCE(offices.office_name, 'MAIN CAMPUS') as office_name"),
            \DB::raw("SUM(CASE WHEN inventory_histories.inv_status = 1 THEN 1 ELSE 0 END) as total"),
            \DB::raw("SUM(CASE WHEN inventory_histories.inv_status = 2 THEN 1 ELSE 0 END) as done")
            )
            ->groupBy('offices.camp_id', 'offices.office_name');

        if ($exists) {
            $inventoryQuery->where('offices.camp_id', $ucampid);
        }

        $inventory = $inventoryQuery->get()->map(function($row) {
            if (is_null($row->camp_id)) {
            $row->office_name = 'MAIN CAMPUS';
            }
            return $row;
        });
        
        
        return view('inventory.listajax', compact('setting', 'yearinvs', 'countInv', 'inventory'));
    }

    public function getInventory()
    {
        $ucampid = auth()->user()->campus_id;
        $exists = Office::whereNotNull('camp_id')
            ->where('camp_id', $ucampid)
            ->exists();

        $inventory = InventoryHistory::join('enduser_property', 'inventory_histories.prop_id', '=', 'enduser_property.id')
            ->join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->join('property', 'enduser_property.properties_id', '=', 'property.id')
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            ->leftjoin('purchases', 'enduser_property.purch_id', '=', 'purchases.id')
            ->select('inventory_histories.*', 'inventory_histories.remarks as his_remarks', 'enduser_property.*', 'offices.office_name', 'property.abbreviation', 'items.item_name', 'purchases.po_number');
            
        if ($exists){
            $inventory->where('offices.camp_id', $ucampid);
        }
        
        $data = $inventory->get();

        return response()->json(['data' => $data]);
    }
    
    public function startInventory(Request $request)
    {
        // Check if there is any ongoing inventory
        $ongoingInventory = YearlyInventory::where('inv_status', 'Ongoing')->exists();
        
        if ($ongoingInventory) {
            return response()->json(['error' => 'There is already an ongoing inventory.'], 400);
        }

        // Create new yearly inventory
        $yearinve = YearlyInventory::create([
            'inv_status' => 'Ongoing', 
        ]);
    
        $invId = $yearinve->id;
    
        $properties = EnduserProperty::leftJoin('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->select('enduser_property.id', 'enduser_property.person_accnt', 'enduser_property.person_accnt_name', 'enduser_property.remarks', 'enduser_property.office_id', 'offices.office_officer')
            ->get();
        
        $inventoryData = [];
        foreach ($properties as $property) {
            $inventoryData[] = [
                'prop_id' => $property->id,
                'inv_id' => $invId,
                'office_id' => $property->office_id,
                'accnt_type' => !is_null($property->person_accnt) ? 1 : 2,
                'person_accnt' => !empty($property->person_accnt_name) ? $property->person_accnt_name : $property->office_officer,
                'item_status' => $property->remarks,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    
        if (!empty($inventoryData)) {
            InventoryHistory::insert($inventoryData);
        }
    
        return response()->json(['success' => 'Inventory created for all properties!'], 200);
    }

    public function geneQr(Request $request)
    {
        $uid = $request->query('uid');
        $qr = $request->query('qrcode');
        $accttype = $request->query('accnt_type'); //1=Office Officer || 2=Office Accountable
        $item_status = $request->query('item_status');
        $remarks = $request->query('remarks');
        $person_accnt = $request->query('person_accnt');
        $isInv = $request->query('isInv'); //1 || 0

        // ?uid=1&qrcode=2024-06-05-030-135-035&accnt_type=2&item_status=Good Condition&remarks=balo&person_accnt=36

        $accountableperson  = Accountable::find($person_accnt);
        $officeaccountable = Office::find($person_accnt); 
        
        // $accountable_name = ($accttype == 1) ? $accountableperson->person_accnt : $officeaccountable->office_officer;

        if($accttype == 1){
            $accountable_name = $accountableperson->person_accnt;
        }
        if($accttype == 2){
            $accountable_name = $officeaccountable->office_officer;
        }
        
        $userproperties = EnduserProperty::where('property_no_generated', $qr)
            ->leftJoin('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->select('enduser_property.id as prop_id', 'enduser_property.person_accnt', 'offices.office_officer')
            ->first();
        
        if (!$userproperties) {
            return response()->json(['error' => 'Property not found'], 404);
        }
     
        $prop_id = $userproperties->prop_id;

        // dd($paccount);
        if($isInv == 1){
            $updated = InventoryHistory::where('prop_id', $prop_id)->update([
                'uid' => $uid,
                'accnt_type' => $accttype,
                'person_accnt' => $accountable_name,
                'item_status' => $item_status,
                'remarks' => $remarks,
                'inv_status' => '2',
            ]);

            $updateData = [
                'person_accnt_name' => $accountable_name,
                'remarks' => $item_status,
            ];
            if ($accttype == 2) {
                $updateData['person_accnt'] = null;
                $updateData['person_accnt1'] = null;
                $updateData['office_id'] = $officeaccountable->id;
            } else {
                $updateData['person_accnt'] = $person_accnt;
            }
            $updated = EnduserProperty::where('id', $prop_id)->update($updateData);

            // Check if this is the last record with inv_status = 2
            $remainingCount = InventoryHistory::where('inv_status', '2')->count();
            if ($remainingCount == 0) {
                YearlyInventory::where('inv_status', '2')->update(['inv_status' => 'Done']);
            }
        }else{
            $updateData = [
                'person_accnt_name' => $accountable_name,
                'remarks' => $item_status,
            ];
            if ($accttype == 2) {
                $updateData['person_accnt'] = null;
                $updateData['person_accnt1'] = null;
                $updateData['office_id'] = $officeaccountable->id;
            } else {
                $updateData['person_accnt'] = $person_accnt;
            }
            $updated = EnduserProperty::where('id', $prop_id)->update($updateData);
        }

        return $updated ? "1" : "0";
    }  

    public function checkInv(Request $request)
    {
        $ongoingInventory = YearlyInventory::where('inv_status', 'Ongoing')->exists();
        
        return $ongoingInventory ? "1" : "0";
    }

}

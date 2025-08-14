<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\EnduserProperty;
use App\Models\Accountable;
use App\Models\InventoryHistory;
use App\Models\YearlyInventory;


class AppController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'uname' => 'required|string',
            'pass' => 'required|string',
        ]);
        if (Auth::attempt(['username' => $validated['uname'], 'password' => $validated['pass']])) {
            $user = Auth::user();
            return response()->json([
                'token' => [
                    'id' => $user->id,
                    'role' => $user->role,
                    'fname' => $user->fname,
                    'lname' => $user->lname
                ]
            ]);
        }
        return response()->json(['error' => 'Invalid credentials!'], 401);
    }

    public function scanQr(Request $request)
    {
        $qr = $request->input('q');

        $inventoryQuery = EnduserProperty::where('property_no_generated', $qr)
            ->leftJoin('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->leftJoin('items', 'enduser_property.item_id', '=', 'items.id')
            ->leftJoin('accountable as person_accountable', 'enduser_property.person_accnt', '=', 'person_accountable.id')
            ->leftJoin('accountable as end_user', 'enduser_property.person_accnt1', '=', 'end_user.id')
            ->select(
                'enduser_property.id',
                'enduser_property.office_id',
                'enduser_property.property_no_generated as property_number',
                'items.item_name as property_name',
                'enduser_property.person_accnt as accountable_person_id',
                'person_accountable.person_accnt as accountable_person_name',
                'enduser_property.person_accnt1 as enduser_id',
                'end_user.person_accnt as enduser_name',
                'enduser_property.remarks as status'                
            );

        $count = $inventoryQuery->count();

        if ($count == 1) {
            $inventory = $inventoryQuery->first();
            $accnt = Accountable::select('id', 'person_accnt')->get();

            return response()->json([
                'status' => 'match',
                'data' => [
                    'invmatch' => $inventory,
                    'accnt' => $accnt,
                ]
            ]);
        } elseif ($count > 1) {
            return response()->json(['status' => 'multiple']);
        } else {
            return response()->json(['status' => 'not_found']);
        }
    }

    public function checkInvstat(Request $request)
    {
        $ongoingInventory = YearlyInventory::where('inv_status', 'Ongoing')->exists();
        return response()->json([
            'status' => $ongoingInventory ? 'ongoing' : 'none'
        ], 200);
    }

    public function saveQr(Request $request)
    {
        $uid = $request->input('uid');
        $qr = $request->input('qrcode');
        $item_status = $request->input('item_status');
        $remarks = $request->input('remarks');
        $person_accnt = $request->input('person_accnt');
        $isInv = $request->input('isInv');

        // Validate accountable person
        $accountable = Accountable::find($person_accnt);
        if (!$accountable) {
            return response()->json(['success' => false, 'error' => 'Accountable person not found'], 404);
        }
        $accountable_name = $accountable->person_accnt;
        
        // Validate property
        $userproperties = EnduserProperty::where('property_no_generated', $qr)->first();
        if (!$userproperties) {
            return response()->json(['success' => false, 'error' => 'Property not found'], 404);
        }
        $prop_id = $userproperties->id;

        // Prepare update data
        $updateData = [
            'person_accnt' => $person_accnt,
            'person_accnt_name' => $accountable_name,
            'remarks' => $item_status,
        ];

        // Update inventory history if applicable
        if ((int) $isInv === 1) {
            InventoryHistory::where('prop_id', $prop_id)->update([
                'uid' => $uid,
                'person_accnt' => $accountable_name,
                'item_status' => $item_status,
                'remarks' => $remarks,
                'inv_status' => '2',
            ]);
        }

        // Update property record
        $updated = EnduserProperty::where('id', $prop_id)->update($updateData);
        if (!$updated) {
            return response()->json(['success' => false, 'error' => 'Failed to update property record'], 500);
        }

        // Finalize yearly inventory if needed
        if ((int) $isInv === 1) {
            $remainingCount = InventoryHistory::where('inv_status', '2')->count();
            if ($remainingCount === 0) {
                YearlyInventory::where('inv_status', '2')->update(['inv_status' => 'Done']);
            }
        }

        return response()->json(['success' => true], 200);
    }
}

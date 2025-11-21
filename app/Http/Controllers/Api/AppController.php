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
use App\Models\Log;

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
        $qr = trim((string) $request->input('q', ''));

        $inventoryRows = EnduserProperty::where('property_no_generated', $qr)
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
            )
            ->limit(2)
            ->get();

        $count = $inventoryRows->count();
        if ($count === 0) return response()->json(['status' => 'not_found']);
        if ($count > 1)   return response()->json(['status' => 'multiple']);

        // Important: include role + office label (“MC” if camp_id is NULL)
        $accnt = Accountable::leftJoin('offices', 'accountable.off_id', '=', 'offices.id')
            ->whereNotNull('accountable.person_accnt')
            ->where('accountable.person_accnt', '!=', '')
            ->select(
                'accountable.id',
                'accountable.person_accnt',
                DB::raw('COALESCE(accountable.accnt_role, 0) AS accnt_role'),
                'accountable.off_id',
                DB::raw("CASE WHEN offices.camp_id IS NULL THEN 'MC' ELSE offices.office_abbr END AS office_abbr")
            )
            ->orderByRaw('TRIM(LOWER(accountable.person_accnt))')
            ->get();

        return response()->json([
            'status' => 'match',
            'data' => [
                'invmatch' => $inventoryRows->first(),
                'accnt'    => $accnt,
            ],
        ]);
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
        $uid           = trim((string) $request->input('uid', ''));
        $qr            = trim((string) $request->input('qrcode', ''));
        $item_status   = trim((string) $request->input('item_status', ''));
        $remarks       = $request->input('remarks', '');
        $person_accnt  = trim((string) $request->input('person_accnt', ''));   // REQUIRED
        $person_accnt1 = trim((string) $request->input('person_accnt1', ''));  // OPTIONAL ("" => NULL)
        $isInv         = (int) $request->input('isInv', 0);

        $ongoingInventory = YearlyInventory::where('inv_status', 'Ongoing')->first();

        // Validate property & people
        $prop = EnduserProperty::where('property_no_generated', $qr)->first();
        if (!$prop) return response()->json(['success' => false, 'error' => 'Property not found'], 404);

        $accountable = Accountable::find($person_accnt);
        if (!$accountable) return response()->json(['success' => false, 'error' => 'Accountable person not found'], 404);

        $endUser = null;
        if ($person_accnt1 !== '') {
            $endUser = Accountable::find($person_accnt1);
            if (!$endUser) return response()->json(['success' => false, 'error' => 'End user not found'], 404);
        }

        // Keep legacy meaning: EnduserProperty.remarks stores STATUS; free-text remarks go to InventoryHistory
        $updateData = [
            'person_accnt'      => (int) $accountable->id,
            'person_accnt_name' => $accountable->person_accnt,
            'person_accnt1'     => $endUser ? (int) $endUser->id : null, // nullable
            'remarks'           => $item_status,
        ];

        try {
            DB::beginTransaction();

            $updated = EnduserProperty::where('id', $prop->id)->update($updateData);
            if (!$updated) {
                DB::rollBack();
                return response()->json(['success' => false, 'error' => 'Failed to update property record'], 500);
            }else{
                Log::create([
                    'user_id' => $uid,
                    'module_id' => $prop->id,
                    'module' => 'enduser_property',
                    'action' => 'update',
                ]);
            }

            if ($isInv === 1) {
                InventoryHistory::where('prop_id', $prop->id)->where('inv_id', $ongoingInventory->id)->update([
                    'uid'          => $uid,
                    'person_accnt' => $accountable->person_accnt, // name, same as before
                    'item_status'  => $item_status,
                    'remarks'      => $remarks, // free-text
                    'inv_status'   => '2',
                ]);

                $remaining = InventoryHistory::where('inv_status', '2')->count();
                if ($remaining === 0) {
                    YearlyInventory::where('inv_status', '2')->update(['inv_status' => 'Done']);
                }
            }

            DB::commit();
            return response()->json(['success' => true], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            // For local debugging only:
            // return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            return response()->json(['success' => false, 'error' => 'Server error. Please try again.'], 500);
        }
    }

    public function endUserProperty(){
        $enduserproperty = EnduserProperty::all();

        return response()->json([
            'enduserproperty' => $enduserproperty
        ]);
    }
}

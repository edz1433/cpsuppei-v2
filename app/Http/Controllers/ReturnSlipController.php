<?php

namespace App\Http\Controllers;

use App\Models\EnduserProperty;
use App\Models\Office;
use App\Models\ReturnSlip;
use App\Models\ReturnSlipItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnSlipController extends Controller
{
    private function isApprover(): bool
    {
        return in_array(auth()->user()->role, ['Administrator', 'Supply Officer']);
    }

    public function index()
    {
        $setting = Setting::firstOrNew(['id' => 1]);
        $user = auth()->user();

        $slips = ReturnSlip::query()
            ->leftJoin('users', 'return_slips.user_id', '=', 'users.id')
            ->leftJoin('campuses', 'return_slips.campus_id', '=', 'campuses.id')
            ->leftJoin('offices', 'return_slips.target_office_id', '=', 'offices.id')
            ->select(
                'return_slips.*',
                DB::raw("CONCAT(COALESCE(users.fname, ''), ' ', COALESCE(users.lname, '')) as requester_name"),
                'campuses.campus_name',
                'offices.office_name as target_office_name'
            )
            ->withCount('items')
            ->when(!$this->isApprover(), function ($query) use ($user) {
                return $query->where('return_slips.user_id', $user->id);
            })
            ->latest('return_slips.created_at')
            ->get();

        $pendingCount = $slips->where('status', 'Pending')->count();
        $approvedCount = $slips->where('status', 'Confirmed')->count();

        return view('return_slips.index', compact('setting', 'slips', 'pendingCount', 'approvedCount'));
    }

    public function create()
    {
        if ($this->isApprover()) {
            return redirect()->route('returnSlips.index')->with('error', 'Administrators and Supply Officers confirm return slips from the list.');
        }

        $setting = Setting::firstOrNew(['id' => 1]);
        $user = auth()->user();
        $offices = Office::orderBy('office_name')->get();
        $properties = $this->ownedPropertiesQuery()
            ->where(function ($query) {
                $query->whereNull('enduser_property.remarks')
                    ->orWhere('enduser_property.remarks', '!=', 'Unserviceable');
            })
            ->orderBy('items.item_name')
            ->get();

        return view('return_slips.create', compact('setting', 'user', 'offices', 'properties'));
    }

    public function store(Request $request)
    {
        if ($this->isApprover()) {
            return redirect()->route('returnSlips.index')->with('error', 'Only requesting offices can submit a return slip.');
        }

        $validated = $request->validate([
            'request_type' => 'required|in:Unserviceable,Transfer',
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'required|integer',
            'target_office_id' => 'nullable|required_if:request_type,Transfer|integer',
            'reason' => 'nullable|string|max:1000',
        ]);

        $properties = $this->ownedPropertiesQuery()
            ->whereIn('enduser_property.id', $validated['property_ids'])
            ->get();

        if ($properties->count() !== count(array_unique($validated['property_ids']))) {
            return redirect()->back()->withInput()->with('error', 'Some selected properties are not available for your account.');
        }

        DB::transaction(function () use ($validated, $properties) {
            $user = auth()->user();

            $slip = ReturnSlip::create([
                'user_id' => $user->id,
                'requested_by' => trim($user->fname.' '.$user->lname),
                'campus_id' => $user->campus_id,
                'request_type' => $validated['request_type'],
                'target_office_id' => $validated['target_office_id'] ?? null,
                'reason' => $validated['reason'] ?? null,
                'status' => 'Pending',
            ]);

            foreach ($properties as $property) {
                ReturnSlipItem::create([
                    'return_slip_id' => $slip->id,
                    'enduser_property_id' => $property->pid,
                    'property_no_generated' => $property->property_no_generated,
                    'item_name' => $property->item_name,
                    'serial_number' => $property->serial_number,
                    'current_office_id' => $property->office_id,
                    'current_location_id' => $property->location,
                    'current_status' => $property->remarks,
                    'status' => 'Pending',
                ]);
            }
        });

        return redirect()->route('returnSlips.index')->with('success', 'Return slip submitted successfully.');
    }

    public function show($id)
    {
        $setting = Setting::firstOrNew(['id' => 1]);
        $slip = $this->slipQuery()->where('return_slips.id', $id)->firstOrFail();

        if (!$this->isApprover() && $slip->user_id !== auth()->id()) {
            abort(403);
        }

        $items = ReturnSlipItem::where('return_slip_id', $id)
            ->leftJoin('offices', 'return_slip_items.current_office_id', '=', 'offices.id')
            ->select('return_slip_items.*', 'offices.office_name as current_office_name')
            ->get();

        return view('return_slips.show', compact('setting', 'slip', 'items'));
    }

    public function confirmItem(Request $request, $itemId)
    {
        if (!$this->isApprover()) {
            abort(403);
        }

        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        $item = ReturnSlipItem::where(function ($query) {
                $query->where('status', 'Pending')->orWhereNull('status');
            })
            ->findOrFail($itemId);
        $slip = ReturnSlip::findOrFail($item->return_slip_id);

        DB::transaction(function () use ($request, $slip, $item) {
            $update = [];

            if ($slip->request_type === 'Unserviceable') {
                $update['remarks'] = 'Unserviceable';
            }

            if ($slip->request_type === 'Transfer') {
                $update['office_id'] = $slip->target_office_id;
                $update['location'] = $slip->target_office_id;
            }

            if (!empty($update)) {
                EnduserProperty::where('id', $item->enduser_property_id)->update($update);
            }

            $item->update([
                'status' => 'Confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
                'action_remarks' => $request->input('remarks'),
            ]);

            $this->syncSlipStatus($slip->id);
        });

        return redirect()->route('returnSlips.show', $slip->id)->with('success', 'Return slip item confirmed successfully.');
    }

    public function cancelItem(Request $request, $itemId)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        $item = ReturnSlipItem::where(function ($query) {
                $query->where('status', 'Pending')->orWhereNull('status');
            })
            ->findOrFail($itemId);
        $slip = ReturnSlip::findOrFail($item->return_slip_id);

        if (!$this->isApprover() && $slip->user_id !== auth()->id()) {
            abort(403);
        }

        $item->update([
            'status' => 'Cancelled',
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
            'action_remarks' => $request->input('remarks'),
        ]);

        $this->syncSlipStatus($slip->id);

        return redirect()->route('returnSlips.show', $slip->id)->with('success', 'Return slip item cancelled.');
    }

    public function deleteItem($itemId)
    {
        $item = ReturnSlipItem::where(function ($query) {
                $query->where('status', 'Pending')->orWhereNull('status');
            })
            ->findOrFail($itemId);
        $slip = ReturnSlip::findOrFail($item->return_slip_id);

        if ($slip->user_id !== auth()->id()) {
            abort(403);
        }

        DB::transaction(function () use ($slip, $item) {
            $item->delete();

            if (!ReturnSlipItem::where('return_slip_id', $slip->id)->exists()) {
                $slip->delete();
                return;
            }

            $this->syncSlipStatus($slip->id);
        });

        if (!ReturnSlip::where('id', $slip->id)->exists()) {
            return redirect()->route('returnSlips.index')->with('success', 'Return slip deleted because no items remained.');
        }

        return redirect()->route('returnSlips.show', $slip->id)->with('success', 'Pending return slip item deleted.');
    }

    private function ownedPropertiesQuery()
    {
        $user = auth()->user();

        return EnduserProperty::query()
            ->join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            ->leftJoin('offices as location_offices', 'enduser_property.location', '=', 'location_offices.id')
            ->select(
                'enduser_property.*',
                'enduser_property.id as pid',
                'offices.office_name',
                'location_offices.office_name as location_name',
                'items.item_name'
            )
            ->where('enduser_property.deleted', 0)
            ->where(function ($query) use ($user) {
                $query->where('offices.camp_id', $user->campus_id)
                    ->orWhere('offices.loc_camp', $user->campus_id);
            });
    }

    private function slipQuery()
    {
        return ReturnSlip::query()
            ->leftJoin('users', 'return_slips.user_id', '=', 'users.id')
            ->leftJoin('campuses as source_campus', 'return_slips.campus_id', '=', 'source_campus.id')
            ->leftJoin('offices', 'return_slips.target_office_id', '=', 'offices.id')
            ->select(
                'return_slips.*',
                DB::raw("CONCAT(COALESCE(users.fname, ''), ' ', COALESCE(users.lname, '')) as requester_name"),
                'source_campus.campus_name as source_campus_name',
                'offices.office_name as target_office_name'
            );
    }

    private function syncSlipStatus($slipId): void
    {
        $items = ReturnSlipItem::where('return_slip_id', $slipId)->get();
        $pending = $items->filter(function ($item) {
            return empty($item->status) || $item->status === 'Pending';
        })->count();
        $confirmed = $items->where('status', 'Confirmed')->count();
        $cancelled = $items->where('status', 'Cancelled')->count();

        if ($pending > 0) {
            ReturnSlip::where('id', $slipId)->update(['status' => 'Pending']);
            return;
        }

        if ($confirmed > 0 && $cancelled === 0) {
            ReturnSlip::where('id', $slipId)->update([
                'status' => 'Confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);
            return;
        }

        if ($cancelled > 0 && $confirmed === 0) {
            ReturnSlip::where('id', $slipId)->update(['status' => 'Cancelled']);
            return;
        }

        ReturnSlip::where('id', $slipId)->update([
            'status' => 'Partially Confirmed',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);
    }
}

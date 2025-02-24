<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\Setting;
use App\Models\Item;
use App\Models\Inventory;
use App\Models\Campus;
use App\Models\Office;

class ItemController extends Controller
{
    //
    public function itemRead(Request $request){
        $off = $request->off;
        $setting = Setting::firstOrNew(['id' => 1]);
        $item = Item::all();
        $campus = Campus::all();
        $office = Office::all();

        $inventoryCount = [];

        foreach ($item as $ite) {
            $query = Inventory::where('item_id', $ite->id);
            if (isset($off)) {
                $query->where('office_id', $off);
            }
            $inventoryCount[$ite->id] = $query->count();
        }

        return view('manage.items.list', compact('setting', 'item', 'inventoryCount', 'campus', 'office'));
    }

    public function itemCreate(Request $request) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $item = Item::all();

        if ($request->isMethod('post')) {
            $request->validate([
                'item_name' => 'required|string|max:255',
            ]);

            $itemName = $request->input('item_name');
            $existingItem = Item::where('item_name', $itemName)->first();

            if ($existingItem) {
                return redirect()->route('itemRead')->with('error', 'Item already exists!');
            }

            try {
                Item::create([
                    'item_name' => $request->input('item_name'),
                ]);

                return redirect()->route('itemRead')->with('success', 'Item stored successfully!');
            } catch (\Exception $e) {
                return redirect()->route()->with('error', 'Failed to store unit!');
            }
        }
    }

    public function itemEdit(Request $request, $id) {
        $off = $request->off;
        $setting = Setting::firstOrNew(['id' => 1]);
        $item = Item::all();
        $campus = Campus::all();
        $office = Office::all();

        $inventoryCount = [];

        foreach ($item as $ite) {
            $query = Inventory::where('item_id', $ite->id);
            if (isset($off)) {
                $query->where('office_id', $off);
            }
            $inventoryCount[$ite->id] = $query->count();
        }

        $selectedItem = Item::findOrFail($id);

        return view('manage.items.list', compact('setting', 'item', 'selectedItem', 'inventoryCount', 'campus', 'office'));
    }

    public function itemUpdate(Request $request) {
        $request->validate([
            'id' => 'required',
            'item_name' => 'required',
        ]);

        try {
            $itemName = $request->input('item_name');
            $existingItem = Item::where('item_name', $itemName)->where('id', '!=', $request->input('id'))->first();

            if ($existingItem) {
                return redirect()->back()->with('error', 'Item already exists!');
            }

            $item = Item::findOrFail($request->input('id'));
            $item->update([
                'item_name' => $itemName,
            ]);

            return redirect()->route('itemEdit', ['id' => $item->id])->with('success', 'Updated Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update item!');
        }
    }

    public function itemDelete($id){
        $item = Item::find($id);
        $item->delete();

        return response()->json([
            'status'=>200,
            'message'=>'Deleted Successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use App\Models\property;
use App\Models\Properties;
use App\Models\Category;
use Carbon\Carbon;

class PropertyTypeLowController extends Controller
{
    public function lvREADcat($id) {
        $cats = category::where('id', $id)
            ->select('cat_name', 'cat_code', 'id')
            ->get();
        
        $options = "";
        foreach ($cats as $cat) {
            $options .= "<option value='".$cat->code_code."'>".$cat->cat_code."</option>";
        }

        return response()->json([
            "options" => $options,
        ]);

    }

    public function lvRead(){
        $setting = Setting::firstOrNew(['id' => 1]);
        $categories = Category::all();
        $properties = Properties::join("categories", "properties.category_id", "=", "categories.id")
            ->orderBy('properties.account_number', 'asc')
            ->select('properties.*', 'categories.cat_name')
            ->where('properties.property_id', '=', 2)
            ->get();
    
        $property = Property::where('id', 2)->first();
    
        return view("manage.property.listLV", compact('setting', 'categories', 'property', 'properties'));
    }

    public function lvCreate(Request $request) {
        $setting = Setting::firstOrNew(['id' => 1]);

        if ($request->isMethod('post')) {
            $request->validate([
                'property_id' => 'required|integer|max:100',
                'category_id' => 'required|string|max:100',
                'account_number' => 'required|string|max:200',
                'account_title' => 'required|string|max:200',
                'account_title_abbr' => 'required|string|max:200',
                'code' => 'required|string|max:100',
            ]);

            $existingAccountNumber = Properties::where('account_number', $request->input('account_number'))->first();
            if ($existingAccountNumber) {
                return redirect()->back()->with('error', 'The account number already exists.');
            }

            try {
                Properties::create([
                    'property_id' => $request->input('property_id'),
                    'category_id' => $request->input('category_id'),
                    'account_number' => $request->input('account_number'),
                    'code' => $request->input('code'),
                    'account_title' => $request->input('account_title'),
                    'account_title_abbr' => $request->input('account_title_abbr'),
                ]);

                return redirect()->back()->with('success', 'Item stored successfully!');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Failed to store property!');
            }
        }
    }

    public function lvEdit($id){
        $setting = Setting::firstOrNew(['id' => 1]);
        $categories = Category::all();
        $properties = Properties::join("categories", "properties.category_id", "=", "categories.id")
            ->orderBy('properties.account_number', 'asc')
            ->select('properties.*', 'categories.cat_name')
            ->where('properties.property_id', '=', 2)
            ->get();

        $lvProperties = Properties::join("categories", "properties.category_id", "=", "categories.id")
        ->where('properties.id', $id)
        ->select('properties.*', 'categories.cat_name')
        ->first();
    
        $property = Property::where('id', 2)->first();

        return view("manage.property.listLV", compact('setting', 'categories', 'property',  'properties',  'lvProperties'));
    }

    public function lvUpdate(Request $request) {
        $request->validate([
            'id' => 'required',
            'property_id' => 'required',
            'category_id' => 'required',
            'account_number' => 'required',
            'account_title' => 'required',
            'account_title_abbr' => 'required',
        ]);

        try {
            // $AccountCodeName = $request->input('account_number');
            // $existingAccountCode = Properties::where('account_number', $AccountCodeName)->where('id', '!=', $request->input('id'))->first();

            // if ($existingAccountCode) {
            //     return redirect()->back()->with('error', 'Account number already exists!');
            // }

            $accountCode = Properties::findOrFail($request->input('id'));
            $accountCode->update([
                'property_id' => $request->input('property_id'),
                'category_id' => $request->input('category_id'), 
                'account_number' => $request->input('account_number'),
                'account_title' => $request->input('account_title'),
                'account_title_abbr' => $request->input('account_title_abbr'),
            ]);

            return redirect()->route('lvEdit', ['id' => $accountCode->id])->with('success', 'Updated Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update Property!');
        }
    }

    public function lvDelete($id){
        $properties = Properties::find($id);
        $properties->delete();

        return response()->json([
            'status'=>200,
            'message'=>'Deleted Successfully',
        ]);
    }
}

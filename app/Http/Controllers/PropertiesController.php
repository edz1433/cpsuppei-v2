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
use App\Models\InventoryHistory;
use App\Models\Setting;
use App\Models\InvSetting;
use App\Models\Log;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;
use PDF;

class PropertiesController extends Controller
{
    public function propertiesRead(Request $request, $cat) {
        $ucampid = auth()->user()->campus_id;
        $exists = Office::whereNotNull('camp_id')
            ->where('camp_id', $ucampid)
            ->where('office_code', '!=', '0000')
            ->exists();

        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::where('office_code', '!=', '0000')->get();

        $role = auth()->user()->role;
        $locations = [];
        if ($role == "Campus Admin") {
            $officeId = Office::where('camp_id', auth()->user()->campus_id)->where('office_code', '!=', '0000')->first()->id;
            $accnt = Accountable::where('off_id', $officeId)->where('accnt_role', '!=', 2)->get();
            $locations = Office::where('loc_camp', auth()->user()->campus_id)->where('office_code', '0000')->get();
        }else{
            $accnt = Accountable::all();
        }

        $item = Item::all();
        $unit = Unit::all();
        $category = Category::all();
        $currentPrice = floatval(str_replace(',', '', $request->input('item_cost'))) ?? 0;
        $property = Property::whereIn('id', [1, 2, 3])->get();
        $properties = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
                    ->join('property', 'enduser_property.properties_id', '=', 'property.id')
                    ->join('items', 'enduser_property.item_id', '=', 'items.id')
                    ->leftjoin('purchases', 'enduser_property.purch_id', '=', 'purchases.id')
                    ->select('enduser_property.*', 'offices.id', 'offices.office_abbr', 'property.abbreviation', 'offices.office_name', 'items.item_name', 'purchases.po_number');
                    
        if ($exists){
            $properties->where('offices.camp_id', $ucampid);
        }
        
        $properties = $properties->get();
        // $page = (auth()->user()->role == "Campus Admin") ? 'list' : 'listajax';
        $page = 'listajax';
        return view('properties.'.$page, compact('setting', 'office', 'locations', 'accnt', 'item', 'unit', 'property', 'currentPrice','category', 'cat', 'properties'));
    }

    public function returnSlip($id){
        $inventory = EnduserProperty::find($id);
        $pdf = PDF::loadView('properties.return-slip', compact('inventory'))->setPaper('Legal', 'portrait');
        return $pdf->stream();
    }

    // public function propertiesppeRead(Request $request) {
    //     $ucampid = auth()->user()->campus_id;
    //     $exists = Office::whereNotNull('camp_id')
    //         ->where('camp_id', $ucampid)
    //         ->exists();

    //     $setting = Setting::firstOrNew(['id' => 1]);
    //     $office = Office::all();
    //     $accnt = Accountable::all();
    //     $item = Item::all();
    //     $unit = Unit::all();
    //     $category = Category::all();
    //     $currentPrice = floatval(str_replace(',', '', $request->input('item_cost'))) ?? 0;
    //     $property = Property::whereIn('id', [1, 2, 3])->get();
    //     $properties = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
    //                 ->join('property', 'enduser_property.properties_id', '=', 'property.id')
    //                 ->join('items', 'enduser_property.item_id', '=', 'items.id')
    //                 ->select('enduser_property.*', 'offices.office_abbr', 'property.abbreviation', 'offices.office_name', 'items.item_name')
    //                 ->where('enduser_property.properties_id', '=', '3');

    //     if ($exists){
    //         $properties->where('offices.camp_id', $ucampid);
    //     }
        
    //     $properties = $properties->get();

    //     return view('properties.list', compact('setting', 'office', 'accnt','item', 'unit', 'property', 'currentPrice','category', 'properties'));
    // }
    
    // public function propertieshighRead(Request $request) {
    //     $ucampid = auth()->user()->campus_id;
    //     $exists = Office::whereNotNull('camp_id')
    //         ->where('camp_id', $ucampid)
    //         ->exists();

    //     $setting = Setting::firstOrNew(['id' => 1]);    
    //     $office = Office::all();
    //     $accnt = Accountable::all();
    //     $item = Item::all();
    //     $unit = Unit::all();
    //     $category = Category::all();
    //     $currentPrice = floatval(str_replace(',', '', $request->input('item_cost'))) ?? 0;
    //     $property = Property::whereIn('id', [1, 2, 3])->get();
    //     $properties = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
    //     ->join('property', 'enduser_property.properties_id', '=', 'property.id')
    //     ->join('items', 'enduser_property.item_id', '=', 'items.id')
    //     ->select('enduser_property.*', 'offices.office_abbr', 'property.abbreviation', 'offices.office_name', 'items.item_name')
    //     ->where('enduser_property.properties_id', '=', '1');
    
    //     if ($exists){
    //         $properties->where('offices.camp_id', $ucampid);
    //     }
        
    //     $properties = $properties->get();


    //     return view('properties.list', compact('setting', 'office', 'accnt', 'item', 'unit', 'property', 'currentPrice','category', 'properties'));
    // }

    public function getProperties($cat) {
        $ucampid = auth()->user()->campus_id;

        $exists = Office::whereNotNull('camp_id')
            ->where('camp_id', $ucampid)
            ->first();
           
        $data = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->join('property', 'enduser_property.properties_id', '=', 'property.id')
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            ->leftJoin('purchases', 'enduser_property.purch_id', '=', 'purchases.id')
            ->leftJoin('accountable as acc1', 'enduser_property.person_accnt', '=', 'acc1.id')
            ->leftJoin('accountable as acc2', 'enduser_property.person_accnt1', '=', 'acc2.id')
            ->leftJoin('offices as location', 'enduser_property.location', '=', 'location.id') // ✅ join offices as location
            ->select(
                'enduser_property.*',
                'offices.office_name',
                'property.abbreviation',
                'items.item_name',
                'purchases.po_number',
                'acc1.person_accnt as accountableName',
                'acc2.person_accnt as accountableNames',
                'location.office_name as location_name'
            );


        if ($exists){
            $data->where('offices.camp_id', $ucampid);
        }

        if($cat != 4){
            $data->where('enduser_property.properties_id', '=', $cat);
        }
        
        $data = $data->where('deleted', 0)->get();

        return response()->json(['data' => $data]);
    }

    // public function propertieslowRead(Request $request) {
    //     $ucampid = auth()->user()->campus_id;
    //     $exists = Office::whereNotNull('camp_id')
    //         ->where('camp_id', $ucampid)
    //         ->exists();

    //     $setting = Setting::firstOrNew(['id' => 1]);
    //     $office = Office::all();
    //     $accnt = Accountable::all();
    //     $item = Item::all();
    //     $unit = Unit::all();
    //     $category = Category::all();
    //     $currentPrice = floatval(str_replace(',', '', $request->input('item_cost'))) ?? 0;
    //     $property = Property::whereIn('id', [1, 2, 3])->get();
    //     $properties = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
    //                 ->join('property', 'enduser_property.properties_id', '=', 'property.id')
    //                 ->join('items', 'enduser_property.item_id', '=', 'items.id')
    //                 ->select('enduser_property.*', 'offices.office_abbr', 'property.abbreviation', 'offices.office_name', 'items.item_name')
    //                 ->where('enduser_property.properties_id', '=', '2');

    //     if ($exists){
    //         $properties->where('offices.camp_id', $ucampid);
    //     }
        
    //     $properties = $properties->get();     

    //     return view('properties.list', compact('setting', 'office', 'accnt', 'item', 'unit', 'property', 'currentPrice','category', 'properties'));
    // }

    public function propertiesintangibleRead(Request $request) {
        $ucampid = auth()->user()->campus_id;
        $exists = Office::whereNotNull('camp_id')
            ->where('camp_id', $ucampid)
            ->exists();

        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::all();
        $accnt = Accountable::all();
        $item = Item::all();
        $unit = Unit::all();
        $category = Category::all();
        $currentPrice = floatval(str_replace(',', '', $request->input('item_cost'))) ?? 0;
        $property = Property::where('id', 4)->get();
        $properties = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
                    ->join('property', 'enduser_property.properties_id', '=', 'property.id')
                    ->join('items', 'enduser_property.item_id', '=', 'items.id')
                    ->select('enduser_property.*', 'offices.office_abbr', 'property.abbreviation', 'offices.office_name', 'items.item_name')
                    ->where('enduser_property.properties_id', '=', '4');

        if ($exists){
            $properties->where('offices.camp_id', $ucampid);
        }
        
        $properties = $properties->get();     
            
        return view('properties.listajax', compact('setting', 'office', 'accnt', 'item', 'unit', 'property', 'currentPrice','category', 'properties'));
    }
    
    public function stickerRead()
    {
        $setting = Setting::firstOrNew(['id' => 1]);

        $campoff = DB::table('offices')
            ->select(
                'offices.id',
                'offices.office_name',
                DB::raw("COUNT(CASE
                    WHEN offices.id = 1 AND (eo.camp_id IS NULL OR eo.camp_id = '') THEN 1
                    WHEN offices.id != 1 AND enduser_property.office_id = offices.id THEN 1
                    ELSE NULL
                END) as property_count")
            )
            // Join all enduser_property rows
            ->leftJoin('enduser_property', function ($join) {
                $join->on(DB::raw('1'), '=', DB::raw('1')); // cross join style (include all properties)
            })
            // Join to get camp_id for the office associated with each enduser_property
            ->leftJoin('offices as eo', 'enduser_property.office_id', '=', 'eo.id')
            ->join('items', 'items.id', '=', 'enduser_property.item_id')
            ->where('offices.office_code', '!=', '0000')
            ->where('items.ct', 1)
            ->where('enduser_property.deleted', 0)
            ->where('enduser_property.remarks', '!=', 'Unserviceable')
            ->groupBy('offices.id', 'offices.office_name')
            ->get();

        return view('properties.sticker', compact('setting', 'campoff'));
    }

    public function stickerReadPost(Request $request) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $campoff = Office::where('office_code', '!=', '0000')->get();
        $selectcampoff = Office::find($request->camp_id);
        return view('properties.sticker', compact('setting', 'campoff', 'selectcampoff'));
    }

    public function stickerReadJson($range, $campus)
    {
        try {
            [$start, $end] = explode('-', $range ?? '1-1000');
            $start = max(0, ((int)$start) - 1); // offset
            $length = ((int)$end) - ((int)$start);

            $properties = EnduserProperty::select(
                    'enduser_property.id',
                    'enduser_property.property_no_generated',
                    'enduser_property.serial_number',
                    'enduser_property.item_model',
                    'enduser_property.item_cost',
                    'enduser_property.date_acquired',
                    'enduser_property.office_id',
                    'enduser_property.categories_id',
                    'enduser_property.person_accnt',
                    'enduser_property.person_accnt1',
                    'items.item_name',
                    'properties.account_title_abbr',
                    'offices.office_name',
                    'a1.person_accnt as person_accnt_fname1',
                    'a2.person_accnt as person_accnt_fname2'
                )
                ->join('items', 'items.id', '=', 'enduser_property.item_id')
                ->join('offices', 'offices.id', '=', 'enduser_property.office_id')
                ->leftJoin('accountable as a1', 'enduser_property.person_accnt', '=', 'a1.id')
                ->leftJoin('accountable as a2', 'enduser_property.person_accnt1', '=', 'a2.id')
                ->leftJoin('properties', 'enduser_property.selected_account_id', '=', 'properties.id')
                ->when($campus == 1, function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->whereNull('offices.camp_id')
                                ->orWhere('offices.camp_id', '');
                    });
                }, function ($query) use ($campus) {
                    $query->where('enduser_property.office_id', $campus);
                })
                ->where('enduser_property.remarks', '!=', 'Unserviceable')
                ->where('items.ct', 1)
                ->where('enduser_property.deleted', 0)
                ->orderByRaw('CAST(enduser_property.item_cost AS DECIMAL(15,2)) ASC')
                ->orderBy('properties.account_title_abbr', 'ASC')
                ->orderBy('enduser_property.office_id')
                ->skip($start)
                ->take($length)
                ->get();

            $data = [];

            foreach ($properties as $property) {
                $qrCode = new QrCode($property->property_no_generated);
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $qrBase64 = base64_encode($result->getString());

                $data[] = [
                    'id' => $property->id,
                    'property_no_generated' => $property->property_no_generated,
                    'serial_number' => $property->serial_number,
                    'item_model' => $property->item_model,
                    'item_cost' => $property->item_cost,
                    'date_acquired' => $property->date_acquired,
                    'item_name' => $property->item_name,
                    'account_title_abbr' => $property->account_title_abbr,
                    'office_name' => $property->office_name,
                    'person_accnt_fname1' => $property->person_accnt_fname1,
                    'person_accnt_fname2' => $property->person_accnt_fname2,
                    'qr_base64' => $qrBase64,
                ];
            }

            return response()->json([
                'campus_id' => $campus,
                'count' => count($data),
                'stickers' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function stickerReadPdf($campus)
    // {
    //     $properties = EnduserProperty::select(
    //             'enduser_property.id',
    //             'enduser_property.property_no_generated',
    //             'enduser_property.serial_number',
    //             'enduser_property.item_model',
    //             'enduser_property.item_cost',
    //             'enduser_property.date_acquired',
    //             'enduser_property.office_id',
    //             'enduser_property.categories_id',
    //             'enduser_property.person_accnt',
    //             'enduser_property.person_accnt1',
    //             'items.item_name',
    //             'properties.account_title_abbr',
    //             'offices.office_name',
    //             'a1.person_accnt as person_accnt_fname1',
    //             'a2.person_accnt as person_accnt_fname2'
    //         )
    //         ->join('items', 'items.id', '=', 'enduser_property.item_id')
    //         ->join('properties', 'properties.category_id', '=', 'enduser_property.categories_id')
    //         ->join('offices', 'offices.id', '=', 'enduser_property.office_id')
    //         ->leftJoin('accountable as a1', 'enduser_property.person_accnt', '=', 'a1.id')
    //         ->leftJoin('accountable as a2', 'enduser_property.person_accnt1', '=', 'a2.id')
    //         ->where('enduser_property.office_id', $campus)
    //         ->limit(100) // You can remove or increase this for more items
    //         ->get();

    //     $setting = Setting::firstOrNew(['id' => 1]);

    //     foreach ($properties as $property) {
    //         $qrCode = QrCode::create($property->property_no_generated);
    //         $writer = new PngWriter();
    //         $result = $writer->write($qrCode);
    //         $property->qr_base64 = base64_encode($result->getString());
    //     }

    //     $pdf = \PDF::loadView('properties.sticker-pdf', compact('setting', 'properties'))
    //         ->setPaper('A4', 'portrait');

    //     return $pdf->stream('qr-stickers.pdf');
    // }


    public function propertiesStickerTemplate() {
        $setting = Setting::firstOrNew(['id' => 1]);
        return view('properties.blank-sticker', compact('setting'));
    }

    public function propertiesStickerTemplatePDF() {
        $setting = Setting::firstOrNew(['id' => 1]);
        $pdf = PDF::loadView('properties.blank-sticker-pdf')->setPaper('A4', 'portrait');
        return $pdf->stream();
    }

    public function propertiesREADcat($id) {
        $accounts = Properties::where('category_id', $id)
            ->select('account_number', 'account_title', 'code', 'id')
            ->get();
        
        $options = "";
        foreach ($accounts as $account) {
            $options .= "<option value='".$account->code."'>".$account->code.' '.$account->account_title."</option>";
        }

        return response()->json([
            "options" => $options,
        ]);

    }

    public function propertiesPrntSticker($id) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $user = User::where('role', '=', 'Supply Officer')->first();
        $inventory = EnduserProperty::leftJoin('offices', 'enduser_property.office_id', '=', 'offices.id')
                    ->leftJoin('items', 'enduser_property.item_id', '=', 'items.id')
                    ->leftJoin('properties', 'enduser_property.selected_account_id', '=', 'properties.id')
                    ->leftJoin('accountable', 'enduser_property.person_accnt', '=', 'accountable.id')
                    ->select('enduser_property.*', 'enduser_property.id as pid', 'offices.*', 'items.*', 'properties.*', 'accountable.*' )
                    ->findOrFail($id);
                    $propertiesId = $inventory->properties_id;
        return view('properties.modal-sticker', compact('setting', 'inventory', 'user', 'propertiesId'));
    }

    public function propertiesCreate(Request $request) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $inventory = EnduserProperty::all();
        $office = Office::where('id', $request->office_id)->first();
        $accnt = Accountable::find($request->person_accnt);
        $date = Carbon::now();
        $dateAcquired = $request->input('date_acquired');
        
        $accountablePer = !isset($request->person_accnt) ? $office->office_officer : $accnt->person_accnt;

        if ($dateAcquired) {
            $formattedDate = date('Y', strtotime($dateAcquired));
        } else {
            $formattedDate = $date->format('Y');
        }

        if ($request->input('item_cost') >= 50001) {
            $propertyCode = "06";
        } else {
            $propertyCode = "04";
        }
        $categoriesCode = $request->categories_id;
        $propertiesCode = $request->property_id;
        $accountID = $request->id;

        $lastItemNumber = EnduserProperty::where([
            //'office_id' => $request->input('office_id'),
            'item_id' => $request->input('item_id'),
            'property_id' => $request->input('property_id'),
        ])->max('item_number');
        $newItemNumber = str_pad($lastItemNumber + 1, 3, '0', STR_PAD_LEFT);

        $officeCode = $office->office_code;
        $propertyCodeGen = $formattedDate.'-'.$propertyCode.'-'.$categoriesCode.'-'.$propertiesCode.'-'.$newItemNumber.'-'.$officeCode;

        if ($request->isMethod('post')) {
            $request->validate([
                'office_id' => 'required',
                'item_id' => 'required',
                'item_descrip' => 'required',
                'item_model' => 'required',
                'serial_number' => 'required',
                'unit_id' => 'required',
                'qty' => 'required',
                'item_cost' => 'required',
                'total_cost' => 'required',
                'properties_id' => 'required',
            ]);

            // try {
                EnduserProperty::create([
                    'office_id' => $request->input('office_id'),
                    'item_id' => $request->input('item_id'),
                    'item_descrip' => $request->input('item_descrip'),
                    'item_model' => $request->input('item_model'),
                    'serial_number' => $request->input('serial_number'),
                    'date_acquired' => $request->input('date_acquired'),
                    'unit_id' => $request->input('unit_id'),
                    'qty' => $request->input('qty'),
                    'item_cost' => $request->input('item_cost'),
                    'total_cost' => $request->input('total_cost'),
                    'properties_id' => $request->input('properties_id'),
                    'categories_id' => $request->input('categories_id'),
                    'property_id' => $request->input('property_id'),
                    'item_number' => $newItemNumber,
                    'property_no_generated' => $propertyCodeGen,
                    'selected_account_id' => $request->input('selected_account_id'),
                    'remarks' => $request->input('remarks'),
                    'price_stat' => $request->input('price_stat'),
                    'person_accnt' => $request->input('person_accnt'),
                    'person_accnt_name' => $accountablePer,
                ]);

                return redirect()->back()->with('success', 'Purchase Item  stored successfully!');
            // } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Failed to store Purchase Item!');
            // }
        }
    }

    public function propertiesEdit(Request $request, $id) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $inventory = EnduserProperty::findOrFail($id);
        // $property = Property::whereIn('id', [1, 2, 3])->get();
        $propertyIds = ($inventory->properties_id == 4) ? [4] : [1, 2, 3];
        $property = Property::whereIn('id', $propertyIds)->get();
        
        $office = Office::all();
        $accnt = Accountable::all();
        $item = Item::all();
        $unit = Unit::all();
        $category = Category::all();

        $selectedOfficeId = $inventory->office_id;
        $selectedPerson = $inventory->person_accnt;
        $selectedPerson1 = $inventory->person_accnt1;
        $selectedItemId = $inventory->item_id;
        $selectedUnitId = $inventory->unit_id;
        $selectedCatId = $inventory->categories_id;
        $selectedAccId = $inventory->property_id;
        $selectedPropId = $inventory->properties_id;
        $category1 = Category::where('id', $selectedCatId)->first();
        $catcode = $category1->cat_code;

        $property1 = Properties::where('category_id', $catcode)
        ->where('property_id', $selectedPropId)
        ->get();

        $currentPrice = floatval(str_replace(',', '', $request->input('item_cost'))) ?? 0;

        $selectedPerson1 = explode(';', $inventory->person_accnt1);
        $serialOwned = explode(';', $inventory->serial_owned);

        return view('properties.edit', compact('setting', 'property', 'property1', 'inventory', 'office', 'accnt', 'item', 'unit', 'category', 'selectedOfficeId', 'selectedPerson', 'selectedPerson1', 'serialOwned', 'selectedItemId', 'selectedUnitId', 'currentPrice', 'selectedCatId', 'selectedAccId', 'selectedPropId'));
    }

    public function propertiesUpdate(Request $request) {
        $inventory = EnduserProperty::find($request->id);
        $office = Office::where('id', $inventory->office_id)->first();
        $date = Carbon::now();
        $dateAcquired = $request->input('date_acquired');

        if ($dateAcquired) {
            $formattedDate = date('Y', strtotime($dateAcquired));
        } else {
            $formattedDate = $date->format('Y');
        }

        if ($request->input('item_cost') >= 50001) {
            $propertyCode = "06";
        } else {
            $propertyCode = "04";
        }
        $categoriesCode = $request->categories_id;
        $propertiesCode = $request->property_id;
        $accountID = $request->id;

        $lastItemNumber = EnduserProperty::where([
            //'office_id' => $request->input('office_id'),
            'item_id' => $request->input('item_id'),
            'property_id' => $request->input('property_id'),
        ])->max('item_number');
        $newItemNumber = str_pad($lastItemNumber + 1, 3, '0', STR_PAD_LEFT);
        
        $officeCode = $office->office_code;
        $propertyCodeGen = $formattedDate.'-'.$propertyCode.'-'.$categoriesCode.'-'.$propertiesCode.'-'.$inventory->item_number.'-'.$officeCode;

        $request->validate([
            'id' => 'required',
            'office_id' => 'required',
            'item_id' => 'required',
            'item_descrip' => 'required',
            'serial_number' => 'required',
            'unit_id' => 'required',
            'qty' => 'required',
            'item_cost' => 'required',
            'total_cost' => 'required',
            'properties_id' => 'required',
            'person_accnt1' => 'nullable',
            'serial_owned' => 'nullable',
        ]);
        
        $inventory = EnduserProperty::findOrFail($request->input('id'));
        $inventory->update([
            'office_id' => $request->input('office_id'),
            'item_id' => $request->input('item_id'),
            'item_descrip' => $request->input('item_descrip'),
            'item_model' => $request->input('item_model'),
            'serial_number' => $request->input('serial_number'),
            'date_acquired' => $request->input('date_acquired'),
            'unit_id' => $request->input('unit_id'),
            'qty' => $request->input('qty'),
            'item_cost' => $request->input('item_cost'),
            'total_cost' => $request->input('total_cost'),
            'properties_id' => $request->input('properties_id'),
            'categories_id' => $request->input('categories_id'),
            'property_id' => $request->input('property_id'),
            'selected_account_id' => $request->input('selected_account_id'),
            'remarks' => $request->input('remarks'),
            'price_stat' => $request->input('price_stat'),
            'person_accnt' => $request->input('person_accnt'),
            'person_accnt1' => $request->input('person_accnt1'),
            'serial_owned' => null,
        ]);

        Log::create([
            'camp_id' => auth()->user()->campus_id,
            'user_id' => auth()->user()->id,
            'module_id' => $request->input('id'),
            'module' => 'enduser_property',
            'action' => 'update',
        ]);

        return redirect()->route('propertiesEdit', ['id' => $inventory->id])->with('success', 'Updated Successfully');
    }

    public function enduserUpdate(Request $request)
    {
        $request->validate([
            'prop_id' => 'required',
            'person_accnt1' => 'nullable',
            'location' => 'nullable',
        ]);

        $properties = EnduserProperty::find($request->input('prop_id'));

        if (!$properties) {
            return redirect()->back()->with('error', 'Property not found.');
        }
// dd($request->input('person_accnt1'));
        $updated = $properties->update([
            'person_accnt1' => $request->input('person_accnt1'),
            'location' => $request->input('location'),
        ]);

        if ($updated) {
            Log::create([
                'camp_id'   => auth()->user()->campus_id,
                'user_id'   => auth()->user()->id,
                'module_id' => $request->input('prop_id'),
                'module'    => 'enduser_property',
                'action'    => 'update',
            ]);

            return redirect()->back()->with('success', 'Updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Update failed, please try again.');
        }
    }

    public function propertiesCat($id, $mode) {
        if ($id) {
            $accounts = Properties::where('category_id', $id)
                ->where('property_id', $mode)
                ->select('account_number', 'account_title', 'code', 'id')
                ->get();
    
            $options = "<option value='All' data-account-id='All' selected>All</option>"; 
    
            foreach ($accounts as $account) {
                $options .= "<option value='".$account->code."'  data-account-id='".$account->id."'>".$account->code.' '.$account->account_title."</option>";
            }
        } else {
            $options = "<option value=''>Select a category first</option>";
        }
    
        return response()->json([
            "options" => $options,
        ]);
    }

    public function invCatIcsPar($id, $mode) {
        $modeArray = explode(',', $mode);
        if ($id) {
            $accounts = Properties::join('property', 'property.id', '=', 'properties.property_id')
                ->where('properties.category_id', $id)
                ->whereIn('properties.property_id', $modeArray)
                ->select('properties.account_number', 'properties.account_title', 'properties.code', 'properties.id', 'property.abbreviation')
                ->get();
    
            $options = "<option value='All' data-account-id='All' selected>All</option>"; 
            
            foreach ($accounts as $account) {
                $options .= "<option value='".$account->code."' data-account-id='".$account->id."'>".$account->code.'|'.$account->abbreviation.'|'.$account->account_title."</option>";
            }
        } else {
            $options = "<option value=''>Select a category first</option>";
        }
    
        return response()->json([
            "options" => $options,
        ]);
    }    
    

    public function propertiesDelete($id, Request $request)
    {
        $property = EnduserProperty::find($id);

        if (!$property) {
            return response()->json([
                'status' => 404,
                'message' => 'Property not found',
            ]);
        }

        $property->update(['deleted' => 1]);

        Log::create([
            'camp_id' => auth()->user()->campus_id,
            'user_id' => auth()->user()->id,
            'module_id' => $id,
            'module' => 'enduser_property',
            'action' => 'delete',
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Deleted Successfully',
        ]);
    }

    public function propertiesReportsOtption() {
        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::all();
        $property = Property::all();
        $category = Category::all();

        return view('properties.reportsOption', compact('setting', 'office', 'property', 'category'));
    }

    public function propertiesReportsOtptionGen(Request $request) {
        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::all();
        $item = Item::all();
        $unit = Unit::all();
        $category = Category::all();

        $officeId = $request->query('office_id');
        $propertiesId = $request->query('properties_id');
        $categoriesId = $request->query('categories_id');
        $propId = $request->query('property_id');
        $startDate = $request->query('start_date_acquired');
        $endDate = $request->query('end_date_acquired');
        $selectId = $request->query('selected_account_id');

        $cond = ($categoriesId == 'All') ? '!=' : '=';

        $propId = ($categoriesId == 'All') ? $propId = '0' : $propId;
        $selectId = ($categoriesId == 'All') ? $selectId = '0' : $selectId;
        $categoriesId = ($categoriesId == 'All') ? $categoriesId = '0' : $categoriesId;

        $inventory = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->join('properties', 'enduser_property.selected_account_id', '=', 'properties.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->where('enduser_property.office_id', $officeId)
            ->where('enduser_property.properties_id', $propertiesId)
            ->where('enduser_property.categories_id', $cond, $categoriesId)
            ->where('enduser_property.property_id', $cond, $propId)
            ->where('enduser_property.selected_account_id', $cond, $selectId)
            ->where(function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('enduser_property.date_acquired', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->where('enduser_property.date_acquired', '>=', $startDate);
                } elseif ($endDate) {
                    $query->where('enduser_property.date_acquired', '<=', $endDate);
                }
            })
            ->get();


        $data = [
            'inventory' => $purchase,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedPropertyId' => $selectId,
            'category_id' => $purchase
        ];

        $pdf = PDF::loadView('properties.printRPCPPE', $data)->setPaper('Legal', 'landscape');
        return $pdf->stream();
    }
}

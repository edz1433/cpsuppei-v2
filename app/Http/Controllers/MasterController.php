<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Setting;
use App\Models\Office;
use App\Models\Campus;
use App\Models\User;
use App\Models\property;
use App\Models\Inventory;
use App\Models\Purchases;

class MasterController extends Controller
{
    //
    public function dashboard(){
        $setting = Setting::firstOrNew(['id' => 1]);

        $camp = Campus::all();
        $userCount = User::count();
        $offCount = Office::count();
        $campusCount = Campus::count();
        $propertyCount = property::count();

        $inventoryPPECount = Inventory::where('properties_id', '3')->count();
        $inventoryHighCount = Inventory::where('properties_id', '1')->count();
        $inventoryLowCount = Inventory::where('properties_id', '2')->count();

        $MainPpeCount = Inventory::where('properties_id', '3')->whereIn('office_id', [2, 5, 6, 7, 8, 12, 13, 14, 15, 16, 17])->count();
        $MainHighCount = Inventory::where('properties_id', '1')->whereIn('office_id', [2, 5, 6, 7, 8, 12, 13, 14, 15, 16, 17])->count();
        $MainLowCount = Inventory::where('properties_id', '2')->whereIn('office_id', [2, 5, 6, 7, 8, 12, 13, 14, 15, 16, 17])->count();


        $IlogPpeCount = Inventory::where('properties_id', '3')->where('office_id', '7')->count();
        $IlogHighCount = Inventory::where('properties_id', '1')->where('office_id', '7')->count();
        $IlogLowCount = Inventory::where('properties_id', '2')->where('office_id', '7')->count();

        $CauayanPpeCount = Inventory::where('properties_id', '3')->where('office_id', '2')->count();
        $CauayanHighCount = Inventory::where('properties_id', '1')->where('office_id', '2')->count();
        $CauayanLowCount = Inventory::where('properties_id', '2')->where('office_id', '2')->count();

        $SipalayPpeCount = Inventory::where('properties_id', '3')->where('office_id', '5')->count();
        $SipalayHighCount = Inventory::where('properties_id', '1')->where('office_id', '5')->count();
        $SipalayLowCount = Inventory::where('properties_id', '2')->where('office_id', '5')->count();

        $HinobaanPpeCount = Inventory::where('properties_id', '3')->where('office_id', '6')->count();
        $HinobaanHighCount = Inventory::where('properties_id', '1')->where('office_id', '6')->count();
        $HinobaanLowCount = Inventory::where('properties_id', '2')->where('office_id', '6')->count();

        $HinigaranPpeCount = Inventory::where('properties_id', '3')->where('office_id', '12')->count();
        $HinigaranHighCount = Inventory::where('properties_id', '1')->where('office_id', '12')->count();
        $HinigaranLowCount = Inventory::where('properties_id', '2')->where('office_id', '12')->count();

        $MoisesPpeCount = Inventory::where('properties_id', '3')->where('office_id', '13')->count();
        $MoisesHighCount = Inventory::where('properties_id', '1')->where('office_id', '13')->count();
        $MoisesLowCount = Inventory::where('properties_id', '2')->where('office_id', '13')->count();

        $SancarlosPpeCount = Inventory::where('properties_id', '3')->where('office_id', '16')->count();
        $SancarlosHighCount = Inventory::where('properties_id', '1')->where('office_id', '16')->count();
        $SancarlosLowCount = Inventory::where('properties_id', '2')->where('office_id', '16')->count();

        $VictoriasPpeCount = Inventory::where('properties_id', '3')->where('office_id', '15')->count();
        $VictoriasHighCount = Inventory::where('properties_id', '1')->where('office_id', '15')->count();
        $VictoriasLowCount = Inventory::where('properties_id', '2')->where('office_id', '15')->count();


        return view("home.dashboard", compact('camp', 
                                            'userCount', 
                                            'offCount', 
                                            'campusCount', 
                                            'propertyCount', 
                                            'inventoryPPECount', 
                                            'inventoryHighCount', 
                                            'inventoryLowCount',
                                            'MainPpeCount',
                                            'MainHighCount',
                                            'MainLowCount',
                                            'IlogPpeCount',
                                            'IlogHighCount',
                                            'IlogLowCount',
                                            'CauayanPpeCount',
                                            'CauayanHighCount',
                                            'CauayanLowCount',
                                            'SipalayPpeCount',
                                            'SipalayHighCount',
                                            'SipalayLowCount', 
                                            'HinobaanPpeCount',
                                            'HinobaanHighCount',
                                            'HinobaanLowCount',
                                            'HinigaranPpeCount',
                                            'HinigaranHighCount',
                                            'HinigaranLowCount', 
                                            'MoisesPpeCount',
                                            'MoisesHighCount',
                                            'MoisesLowCount', 
                                            'SancarlosPpeCount',
                                            'SancarlosHighCount',
                                            'SancarlosLowCount',
                                            'VictoriasPpeCount',
                                            'VictoriasHighCount',
                                            'VictoriasLowCount', 
                                            'setting'));
    }

    public function logout(){
        auth()->logout();
        return redirect()->route('getLogin')->with('success','You have been Successfully Logged Out');
    }
}

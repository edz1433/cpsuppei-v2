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
use App\Models\EnduserProperty;
use App\Models\Repair;

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
        $user = auth()->user();
        $role = $user->role;
        $isAdministrator = $role === 'Administrator';
        $isSupplyOfficer = $role === 'Supply Officer';
        $isCampusAdmin = $role === 'Campus Admin';
        $isTechnician = $role === 'Technician';
        $dashboardMode = $isTechnician ? 'technician' : ($isCampusAdmin ? 'campus' : ($isSupplyOfficer ? 'supply' : 'administrator'));
        $scopeToCampus = !$isAdministrator && !$isSupplyOfficer;
        $campusOfficeIds = collect();

        if ($scopeToCampus) {
            $campusOfficeIds = Office::query()
                ->where(function ($query) use ($user) {
                    $query->where('camp_id', $user->campus_id)
                        ->orWhere('loc_camp', $user->campus_id);
                })
                ->pluck('id');
        }

        $applyCampusScope = function ($query) use ($scopeToCampus, $campusOfficeIds) {
            if (!$scopeToCampus) {
                return $query;
            }

            return $campusOfficeIds->isNotEmpty()
                ? $query->whereIn('office_id', $campusOfficeIds)
                : $query->whereRaw('1 = 0');
        };

        $reportBase = $applyCampusScope(EnduserProperty::query()->where('deleted', 0));
        $reportableItemsCount = (clone $reportBase)->count();
        $reportableAssetsValue = (clone $reportBase)->sum('item_cost');
        $reportableQuantity = (clone $reportBase)->sum('qty');
        $latestReportableItem = (clone $reportBase)->max('date_acquired');

        $serviceableReportBase = function () use ($applyCampusScope) {
            return $applyCampusScope(EnduserProperty::query()
                ->where('deleted', 0)
                ->where(function ($query) {
                    $query->whereNull('remarks')
                        ->orWhere('remarks', '!=', 'Unserviceable');
                }));
        };

        $unserviceableReportBase = function () use ($applyCampusScope) {
            return $applyCampusScope(EnduserProperty::query()
                ->where('deleted', 0)
                ->where('remarks', 'Unserviceable'));
        };

        // These buckets match the active ReportsController report_type filters above //OLD.
        $rpcppeCount = $serviceableReportBase()->where('item_cost', '>=', 50000)->count();
        $rpcppeValue = $serviceableReportBase()->where('item_cost', '>=', 50000)->sum('item_cost');
        $rpcsepCount = $serviceableReportBase()->where('item_cost', '<', 50000)->count();
        $rpcsepValue = $serviceableReportBase()->where('item_cost', '<', 50000)->sum('item_cost');
        $unserviceableCount = $unserviceableReportBase()->count();
        $unserviceableValue = $unserviceableReportBase()->sum('item_cost');

        $inventoryPPECount = $rpcppeCount;
        $inventoryHighCount = $rpcsepCount;
        $inventoryLowCount = $unserviceableCount;

        $officeReportRows = Office::query()
            ->where('id', '!=', 1)
            ->where('office_code', '!=', '0000')
            ->when($scopeToCampus, function ($query) use ($campusOfficeIds) {
                return $campusOfficeIds->isNotEmpty()
                    ? $query->whereIn('id', $campusOfficeIds)
                    : $query->whereRaw('1 = 0');
            })
            ->orderBy('office_abbr')
            ->get()
            ->map(function ($office) use ($serviceableReportBase, $unserviceableReportBase) {
                $officeServiceable = function () use ($serviceableReportBase, $office) {
                    return $serviceableReportBase()->where('office_id', $office->id);
                };

                $officeUnserviceable = function () use ($unserviceableReportBase, $office) {
                    return $unserviceableReportBase()->where('office_id', $office->id);
                };

                $rpcppe = $officeServiceable()->where('item_cost', '>=', 50000)->count();
                $rpcsep = $officeServiceable()->where('item_cost', '<', 50000)->count();
                $unserviceable = $officeUnserviceable()->count();

                return [
                    'id' => $office->id,
                    'label' => $office->office_abbr ?: $office->office_name,
                    'is_extension' => !empty($office->camp_id),
                    'rpcppe' => $rpcppe,
                    'rpcsep' => $rpcsep,
                    'unserviceable' => $unserviceable,
                    'total' => $rpcppe + $rpcsep + $unserviceable,
                ];
            });

        $mainReportRows = $officeReportRows->where('is_extension', false)->values();
        $extensionReportRows = $officeReportRows->where('is_extension', true)->values();
        $visibleExtensionRows = $extensionReportRows->where('total', '>', 0)->take(12)->values();

        if ($visibleExtensionRows->isEmpty()) {
            $visibleExtensionRows = $extensionReportRows->take(12)->values();
        }

        $mainCampusTotal = $mainReportRows->sum('total');
        $extensionCampusTotal = $extensionReportRows->sum('total');

        $MainPpeCount = $mainReportRows->sum('rpcppe');
        $MainHighCount = $mainReportRows->sum('rpcsep');
        $MainLowCount = $mainReportRows->sum('unserviceable');

        $campusReportLabels = $visibleExtensionRows->pluck('label')->values();
        $campusReportRpcppe = $visibleExtensionRows->pluck('rpcppe')->values();
        $campusReportRpcsep = $visibleExtensionRows->pluck('rpcsep')->values();
        $campusReportUnserviceable = $visibleExtensionRows->pluck('unserviceable')->values();
        $mainReportLabels = ['Main Campus'];
        $mainReportRpcppe = [$MainPpeCount];
        $mainReportRpcsep = [$MainHighCount];
        $mainReportUnserviceable = [$MainLowCount];

        $repairScope = Repair::query();
        if ($scopeToCampus && $campusOfficeIds->isNotEmpty()) {
            $repairScope->whereIn('prop_id', EnduserProperty::query()
                ->whereIn('office_id', $campusOfficeIds)
                ->select('id'));
        } elseif ($scopeToCampus) {
            $repairScope->whereRaw('1 = 0');
        }

        $repairTotalCount = (clone $repairScope)->count();
        $repairPendingCount = (clone $repairScope)
            ->where(function ($query) {
                $query->whereNull('diagnosis')
                    ->orWhere('diagnosis', '');
            })
            ->count();
        $repairForReleaseCount = (clone $repairScope)
            ->whereNotNull('diagnosis')
            ->whereNull('release_date')
            ->count();
        $repairReleasedCount = (clone $repairScope)->whereNotNull('release_date')->count();


        return view("home.dashboard", compact('camp', 
                                            'dashboardMode',
                                            'role',
                                            'isAdministrator',
                                            'isSupplyOfficer',
                                            'isCampusAdmin',
                                            'isTechnician',
                                            'userCount', 
                                            'offCount', 
                                            'campusCount', 
                                            'propertyCount', 
                                            'reportableItemsCount',
                                            'reportableAssetsValue',
                                            'reportableQuantity',
                                            'latestReportableItem',
                                            'rpcppeCount',
                                            'rpcppeValue',
                                            'rpcsepCount',
                                            'rpcsepValue',
                                            'unserviceableCount',
                                            'unserviceableValue',
                                            'inventoryPPECount', 
                                            'inventoryHighCount', 
                                            'inventoryLowCount',
                                            'mainCampusTotal',
                                            'extensionCampusTotal',
                                            'campusReportLabels',
                                            'campusReportRpcppe',
                                            'campusReportRpcsep',
                                            'campusReportUnserviceable',
                                            'mainReportLabels',
                                            'mainReportRpcppe',
                                            'mainReportRpcsep',
                                            'mainReportUnserviceable',
                                            'MainPpeCount',
                                            'MainHighCount',
                                            'MainLowCount',
                                            'repairTotalCount',
                                            'repairPendingCount',
                                            'repairForReleaseCount',
                                            'repairReleasedCount',
                                            'setting'));
    }

    public function logout(){
        auth()->logout();
        return redirect()->route('getLogin')->with('success','You have been Successfully Logged Out');
    }
}

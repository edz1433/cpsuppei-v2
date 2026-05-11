<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\EnduserProperty;
use App\Models\Purchases;
use App\Models\Office;
use App\Models\property;
use App\Models\Properties;
use App\Models\Unit;
use App\Models\Item; 
use App\Models\Campus;
use App\Models\Category;
use App\Models\User;
use App\Models\Accountable;
use Carbon\Carbon;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Barryvdh\Snappy\Facades\SnappyPdf as Snappy; 

class ReportsController extends Controller
{
    public function reportForm() {
        $setting = Setting::firstOrNew(['id' => 1]);
        $user = auth()->user();

        if($user->role == "Administrator"){
            $office = Office::all();
            
        }else{
            $office = Office::where('camp_id', $user->campus_id)->get();
        }

        $uid = auth()->user()->campus_id;
        $uoffice = Office::where('camp_id', $uid)->first();
        $property = Property::all();
        $category = Category::all();
        if($uoffice){
            $accntables = Accountable::where('off_id', $uoffice->id)->get();
        }else{
            $accntables = Accountable::all();
        }
        return view('reports.report_form', compact('setting', 'uoffice', 'office', 'property', 'accntables', 'category'));
    }

    public function reportFormGenerate() {

    }   
    
    public function selectType() {

    }   

    //SELECT AUTO DISPLAY
    public function genPropType($id)
    {
        $reportType = $id;

        $html = '';

        if (!$reportType) {
            return $html;
        }

        // Start with a fresh query builder (NOT Property::all())
        $query = Property::query();

        if (in_array($reportType, [1, 4])) {
            $query->where('id', 3);
        } elseif (in_array($reportType, [2, 3])) {
            $query->whereIn('id', [1, 2]);
        }else{
            $query->whereIn('id', [1, 2, 3]);
        }

        $properties = $query->get();

        foreach ($properties as $property) {
            $html .= '<option selected value="' . $property->id . '">' 
                    . htmlspecialchars($property->abbreviation) 
                    . ' - ' 
                    . htmlspecialchars($property->property_name) 
                    . '</option>';
        }

        if ($properties->isEmpty()) {
            $html = '<option value="" disabled selected>--- No Property Types Available ---</option>';
        }

        return $html;
    }

    // ==================== generateMix ====================
    public function generateMix($id)
    {
        $office = Office::find($id);

        if (!$office) {
            return response()->json([
                'selectlocation'    => '<option disabled selected value=""> --- select --- </option>',
                'selectedaccntable' => '<option disabled selected value=""> --- select --- </option>',
                'selectedenduser'   => '<option disabled selected value=""> --- select --- </option>',
                'selectitems'       => '<option value=""> --- select items --- </option><option value="All">All Items</option>'
            ]);
        }

        $label = '';

        if (!is_null($office->camp_id)) {
            // Camp / Custodian Case
            $location = Office::where('loc_camp', $office->camp_id)
                ->where('office_code', '0000')
                ->get();

            $accntable = Accountable::where('off_id', $id)
                ->whereIn('accnt_role', [1, 2])
                ->get();

            $enduser = Accountable::where('off_id', $id)
                ->where('accnt_role', 0)
                ->get();

            $label = ' - CUSTODIAN';
        } else {
            // Regular Office Case
            $enduserlocation = EnduserProperty::where('office_id', $id)
                ->pluck('location');
            
            $location = Office::where('loc_camp', 1)
                ->where('office_code', '0000')
                ->whereIn('id', $enduserlocation)
                ->get();

            $accntable = Accountable::where('off_id', $id)
                ->where('accnt_role', 1)
                ->get();

            $enduser = Accountable::where('off_id', $id)
                ->where('accnt_role', 0)
                ->get();
        }

        // Build Location
        $selectlocation = '<option disabled value=""> --- select --- </option>';
        $first = true;

        foreach ($location as $loc) {
            $selectlocation .= '<option value="' . $loc->id . '" ' . ($first ? 'selected' : '') . '>'
                . htmlspecialchars($loc->office_name ?? '', ENT_QUOTES, 'UTF-8') . '</option>';

            $first = false;
        }

        // Build Accountable
        $selectedaccntable = '<option disabled value=""> --- select --- </option>';
        $first = true;
        foreach ($accntable as $acc) {
            $selectedaccntable .= '<option value="' . $acc->id . '" ' . ($first ? 'selected' : '') . '>' 
                . htmlspecialchars($acc->person_accnt ?? '', ENT_QUOTES, 'UTF-8')
                . $label . '</option>';
            $first = false;
        }

        // Build End User
        $selectedenduser = '<option disabled selected value=""> --- select --- </option>';
        foreach ($enduser as $user) {
            $selectedenduser .= '<option value="' . $user->id . '">' 
                . htmlspecialchars($user->person_accnt ?? '', ENT_QUOTES, 'UTF-8') . '</option>';
        }

        $selectitems = '<option value=""> --- select items --- </option><option value="All">All Items</option>';

        return response()->json([
            'selectlocation'    => $selectlocation,
            'selectedaccntable' => $selectedaccntable,
            'selectedenduser'   => $selectedenduser,
            'selectitems'       => $selectitems
        ]);
    }

    // ==================== generateItems ====================
    public function generateItems(Request $request)
    {
        try {
            $query = EnduserProperty::query()
                ->where('enduser_property.deleted', 0)
                ->join('items', 'items.id', '=', 'enduser_property.item_id')
                ->leftJoin('accountable as accountable_person', 'accountable_person.id', '=', 'enduser_property.person_accnt')
                ->leftJoin('accountable as end_user', 'end_user.id', '=', 'enduser_property.person_accnt1')
                ->select(
                    'enduser_property.*',
                    'accountable_person.person_accnt as accountable_person',
                    'end_user.person_accnt as end_user',
                    'items.*',
                    'enduser_property.id as pid'
                );

            if ($request->filled('office_id')) {
                $query->where('enduser_property.office_id', $request->office_id);
            }

            if ($request->filled('location') && $request->location !== 'All') {
                $query->where('enduser_property.location', $request->location);
            }

            if ($request->filled('categories_id') && $request->categories_id !== 'All') {
                $query->where('enduser_property.categories_id', $request->categories_id);
            }

            if ($request->filled('property_id') && $request->property_id !== 'All') {
                $query->where('enduser_property.property_id', $request->property_id);
            }

            // Safe accountable filter
            if ($request->filled('person_accnt1')) {
                $query->where('enduser_property.person_accnt1', $request->person_accnt1);
            } elseif ($request->filled('person_accnt')) {
                $query->where('enduser_property.person_accnt', $request->person_accnt);
            }

            if ($request->filled('report_type')) {
                if ((string) $request->report_type === '5') {
                    $query->where('enduser_property.remarks', 'Unserviceable');
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('enduser_property.remarks')
                        ->orWhere('enduser_property.remarks', '!=', 'Unserviceable');
                    });
                }

                switch ((string) $request->report_type) {
                    case '1': // RPCPPE
                    case '4': // PAR
                        $query->where('enduser_property.item_cost', '>=', 50000);
                        break;

                    case '2': // RPCSEP
                    case '3': // ICS
                        $query->where('enduser_property.item_cost', '<', 50000);
                        break;

                    case '5': // UNSERVICEABLE
                        break;
                }
            }

            if ($request->filled('date_range') && $request->date_range !== 'All' && trim($request->date_range) !== '') {
                $dateString = trim($request->date_range);
                $cleaned = preg_replace('/\s+/', ' ', $dateString);
                $dates = explode(' - ', $cleaned);

                if (count($dates) === 2) {
                    $start = trim($dates[0]);
                    $end   = trim($dates[1]);

                    try {
                        $startDate = Carbon::createFromFormat('Y-m-d', $start)->format('Y-m-d');
                        $endDate   = Carbon::createFromFormat('Y-m-d', $end)->format('Y-m-d');

                        $query->whereBetween('enduser_property.date_acquired', [$startDate, $endDate]);
                    } catch (\Exception $e) {
                        \Log::warning('Invalid date_range format: ' . $dateString);
                    }
                }
            }

            $query->orderBy('enduser_property.date_acquired', 'desc');

            $items = $query->get();

            $selectitems = '<option value=""> --- select items --- </option>';
            $selectitems .= '<option value="All">All Items</option>';

            foreach ($items as $item) {
                $display = trim(($item->item_name ?? '') . ' ' . ($item->item_descrip ?? ''));

                $accountable = $item->accountable_person ?? 'N/A';
                $enduser     = $item->end_user ?? 'N/A';

                $acquiredDate = $item->date_acquired
                    ? date('Y-m-d', strtotime($item->date_acquired))
                    : 'N/A';

                $selectitems .= '<option value="' . $item->pid . '">'
                    . htmlspecialchars($display)
                    . ' | AP: ' . htmlspecialchars($accountable)
                    . ' | EU: ' . htmlspecialchars($enduser)
                    . ' (Acquired: ' . $acquiredDate . ')'
                    . '</option>';
            }

            return response()->json(['selectitems' => $selectitems]);

        } catch (\Exception $e) {
            \Log::error('generateItems Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());

            return response()->json([
                'selectitems' => '<option value=""> --- select items --- </option><option value="All">All Items</option>'
            ]);
        }
    }

    /**
     * Format date range for display - Converts YYYY-MM-DD to readable format like "APR 1, 2000 - MAY 31, 2026"
     */
    private function formatDateRangeForDisplay($dateRange)
    {
        if (empty($dateRange) || trim($dateRange) === '') {
            return strtoupper(Carbon::now()->format('M j, Y'));
        }
        
        try {
            // Clean the date string
            $dateRange = trim($dateRange);
            
            // Check if it's a range with space-dash-space format (2000-04-01 - 2026-05-31)
            if (preg_match('/(\d{4}-\d{2}-\d{2})\s*-\s*(\d{4}-\d{2}-\d{2})/', $dateRange, $matches)) {
                $start = Carbon::parse($matches[1]);
                $end = Carbon::parse($matches[2]);
                
                // Check if it's actually a single date (same day)
                if ($start->isSameDay($end)) {
                    return strtoupper($start->format('M j, Y'));
                }
                
                // Same month and year: "APR 21 - 28, 2026"
                if ($start->isSameMonth($end) && $start->isSameYear($end)) {
                    return strtoupper($start->format('M j') . ' - ' . $end->format('j, Y'));
                }
                
                // Same year, different months: "APR 21 - MAY 28, 2026"
                if ($start->isSameYear($end)) {
                    return strtoupper($start->format('M j') . ' - ' . $end->format('M j, Y'));
                }
                
                // Different years: "APR 1, 2000 - MAY 31, 2026"
                return strtoupper($start->format('M j, Y') . ' - ' . $end->format('M j, Y'));
            }
            
            // Check for 'to' separator
            if (str_contains($dateRange, ' to ')) {
                $parts = explode(' to ', $dateRange);
                if (count($parts) === 2) {
                    $start = Carbon::parse(trim($parts[0]));
                    $end = Carbon::parse(trim($parts[1]));
                    
                    if ($start->isSameDay($end)) {
                        return strtoupper($start->format('M j, Y'));
                    }
                    if ($start->isSameMonth($end) && $start->isSameYear($end)) {
                        return strtoupper($start->format('M j') . ' - ' . $end->format('j, Y'));
                    }
                    if ($start->isSameYear($end)) {
                        return strtoupper($start->format('M j') . ' - ' . $end->format('M j, Y'));
                    }
                    return strtoupper($start->format('M j, Y') . ' - ' . $end->format('M j, Y'));
                }
            }
            
            // Single date: "2026-05-31"
            return strtoupper(Carbon::parse($dateRange)->format('M j, Y'));
            
        } catch (\Exception $e) {
            \Log::warning('Date format error: ' . $e->getMessage() . ' for input: ' . $dateRange);
            return strtoupper($dateRange);
        }
    }

    /**
     * Calculate Balance Brought Forward (items acquired before the selected date range)
     */
    private function calculateBroughtForward($request, $baseQuery = null)
    {
        // If no date range is selected, no brought forward balance
        if (!$request->filled('date_range') || $request->date_range === 'All' || trim($request->date_range) === '') {
            return 0;
        }
        
        // Parse the date range to get the start date
        $dateString = trim($request->date_range);
        $cleaned = preg_replace('/\s+/', ' ', $dateString);
        $dates = explode(' - ', $cleaned);
        
        if (count($dates) !== 2) {
            return 0;
        }
        
        try {
            $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
            
            // Build a fresh query for brought forward items
            $broughtForwardQuery = EnduserProperty::query()
                ->where('enduser_property.deleted', 0);
            
            // Apply all the same filters EXCEPT date range
            $broughtForwardQuery = $this->applyFiltersWithoutDateRange($broughtForwardQuery, $request);
            
            // Add condition for items acquired BEFORE the start date
            $broughtForwardQuery->where('enduser_property.date_acquired', '<', $startDate);
            
            // Get the sum of item costs for brought forward items
            $bforward = $broughtForwardQuery->sum('enduser_property.item_cost');
            
            return $bforward ?: 0;
            
        } catch (\Exception $e) {
            \Log::error('Brought Forward Calculation Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Apply all filters except date range (for brought forward calculation)
     */
    private function applyFiltersWithoutDateRange($query, $request)
    {
        if ($request->filled('office_id')) {
            $query->where('enduser_property.office_id', $request->office_id);
        }
        
        if ($request->filled('location') && $request->location !== 'All') {
            $query->where('enduser_property.location', $request->location);
        }
        
        if ($request->filled('categories_id') && $request->categories_id !== 'All') {
            $query->where('enduser_property.categories_id', $request->categories_id);
        }
        
        if ($request->filled('property_id') && $request->property_id !== 'All') {
            $query->where('enduser_property.property_id', $request->property_id);
        }
        
        // Accountable filter
        if ($request->filled('person_accnt1')) {
            $query->where('enduser_property.person_accnt1', $request->person_accnt1);
        } elseif ($request->filled('person_accnt')) {
            $query->where('enduser_property.person_accnt', $request->person_accnt);
        }
        
        // Report type filters
        if ($request->filled('report_type')) {
            if ((string) $request->report_type === '5') {
                $query->where('enduser_property.remarks', 'Unserviceable');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('enduser_property.remarks')
                        ->orWhere('enduser_property.remarks', '!=', 'Unserviceable');
                });
            }
            
            switch ((string) $request->report_type) {
                case '1': // RPCPPE
                case '4': // PAR
                    $query->where('enduser_property.item_cost', '>=', 50000);
                    break;
                case '2': // RPCSEP
                case '3': // ICS
                    $query->where('enduser_property.item_cost', '<', 50000);
                    break;
                case '5': // UNSERVICEABLE
                    break;
            }
        }
        
        // Item filter
        $itemIds = $request->input('item_id', []);
        $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];
        $itemIds = array_filter($itemIds, function ($id) {
            return $id !== null && $id !== '';
        });
        
        if (!empty($itemIds) && !in_array('All', $itemIds, true)) {
            $query->whereIn('enduser_property.id', $itemIds);
        }
        
        return $query;
    }

    /**
     * Calculate total cost for current items
     */
    private function calculateTotalCost($items)
    {
        return $items->sum('item_cost');
    }

    /**
     * Get category breakdown for detailed reporting
     */
    private function getCategoryBreakdown($items)
    {
        return $items->groupBy('cat_name')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_cost' => $group->sum('item_cost'),
                'total_quantity' => $group->sum('quantity')
            ];
        });
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'report_type'   => 'required|in:1,2,3,4,5',
            'properties_id' => 'nullable|array',
            'office_id'     => 'required',
            'location'      => 'nullable',
            'categories_id' => 'nullable',
            'property_id'   => 'nullable',
            'date_range'    => 'nullable',
            'person_accnt'  => 'nullable',
            'person_accnt1' => 'nullable',
            'item_id'       => 'nullable|array',
            'columns'       => 'nullable|array',
            'balance_bforward' => 'nullable|boolean',
            'eachpage_subtotal' => 'nullable|boolean',
            'grand_total' => 'nullable|boolean',
            'eachpage_header' => 'nullable|boolean',
            'eachpage_footer' => 'nullable|boolean',
        ]);

        $reportType  = $request->report_type;
        $reportTitle = $this->getReportTitle($reportType);

        $query = EnduserProperty::query()
            ->where('enduser_property.deleted', 0)
            ->leftJoin('items', 'items.id', '=', 'enduser_property.item_id')
            ->leftJoin('property', 'property.id', '=', 'enduser_property.property_id')
            ->leftJoin('categories', 'categories.id', '=', 'enduser_property.categories_id')
            ->leftJoin('accountable as accountable_person', 'accountable_person.id', '=', 'enduser_property.person_accnt')
            ->leftJoin('accountable as end_user', 'end_user.id', '=', 'enduser_property.person_accnt1')
            ->leftJoin('offices', 'offices.id', '=', 'enduser_property.office_id')
            ->leftJoin('units', 'units.id', '=', 'enduser_property.unit_id')
            ->select(
                'enduser_property.*',
                'units.unit_name',
                'accountable_person.person_accnt as accountable_person_name',
                'accountable_person.accnt_role as accountable_role',
                'end_user.person_accnt as end_user_name',
                'items.*',
                'property.property_name',
                'property.abbreviation',
                'categories.cat_name',
                'categories.cat_code',
                'offices.office_name',
                'offices.office_abbr',
                'offices.camp_id',
                'enduser_property.id as pid'
            );

        $query = $this->applyReportFilters($query, $request);
        $items = $query->get();

        // Calculate Balance Brought Forward
        $bforward = $this->calculateBroughtForward($request);
        
        // Calculate Total Cost of current items
        $totalCost = $this->calculateTotalCost($items);
        
        // Calculate Grand Total
        $grandTotal = $bforward + $totalCost;
        
        // Get category breakdown
        $categoryBreakdown = $this->getCategoryBreakdown($items);

        $office = Office::find($request->office_id);
        $selectedColumns = $request->input('columns', []);

        // Format the date range for display
        $hasDateFilter = $request->filled('date_range') && $request->date_range !== 'All' && trim($request->date_range) !== '';
        $formattedDateRange = '';
        
        if ($hasDateFilter) {
            $formattedDateRange = $this->formatDateRangeForDisplay($request->date_range);
        }

        // Get checkbox values from form (using boolean method)
        $showBalanceForward = $request->boolean('balance_bforward');
        $showGrandTotal = $request->boolean('grand_total');
        $showEachpageSubtotal = $request->boolean('eachpage_subtotal');
        $showEachpageHeader = $request->boolean('eachpage_header');
        $showEachpageFooter = $request->boolean('eachpage_footer');

        $data = [
            'title'            => $reportTitle,
            'report_type'      => $reportType,
            'items'            => $items,
            'office'           => $office,
            'filters'          => $this->getFilterSummary($request),
            'selected_columns' => $selectedColumns,
            'generated_date'   => Carbon::now()->format('F d, Y h:i A'),
            'report_number'    => $this->generateReportNumber($reportType),
            
            // Financial calculations
            'bforward'         => $bforward,
            'total_cost'       => $totalCost,
            'grand_total'      => $grandTotal,
            'has_date_filter'  => $hasDateFilter,
            'date_range_display' => $formattedDateRange,
            'raw_date_range'   => $request->date_range,
            
            // Checkbox options from form
            'show_balance_forward' => $showBalanceForward,
            'show_grand_total' => $showGrandTotal,
            'show_eachpage_subtotal' => $showEachpageSubtotal,
            'show_eachpage_header' => $showEachpageHeader,
            'show_eachpage_footer' => $showEachpageFooter,
            
            // Category breakdown
            'category_breakdown' => $categoryBreakdown,
            
            // Additional stats
            'total_items_count' => $items->count(),
            'total_quantity'    => $items->sum('quantity'),
        ];

        $view = $this->getReportView($reportType);
        $filename = $this->getReportFilename($reportType, $office);

        // Fix orientation logic (explicit and readable)
        $orientation = in_array($reportType, [3, 4, 5]) ? 'Portrait' : 'Landscape';

        $pdf = Snappy::loadView($view, $data)
            ->setOption('page-size', 'Tabloid') // better than setPaper for wkhtmltopdf
            ->setOption('orientation', $orientation)
            ->setOption('disable-smart-shrinking', true)
            ->setOption('no-stop-slow-scripts', true)
            ->setOption('quiet', true)
            ->setOption('margin-top', '5mm')
            ->setOption('margin-bottom', '5mm')
            ->setOption('margin-left', '5mm')
            ->setOption('margin-right', '5mm');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
    
    // Helper method to apply all filters
    private function applyReportFilters($query, $request)
    {
        if ($request->filled('office_id')) {
            $query->where('enduser_property.office_id', $request->office_id);
        }

        if ($request->filled('location') && $request->location !== 'All') {
            $query->where('enduser_property.location', $request->location);
        }

        if ($request->filled('categories_id') && $request->categories_id !== 'All') {
            $query->where('enduser_property.categories_id', $request->categories_id);
        }

        if ($request->filled('property_id') && $request->property_id !== 'All') {
            $query->where('enduser_property.property_id', $request->property_id);
        }

        // Same accountable behavior as generateItems
        if ($request->filled('person_accnt1')) {
            $query->where('enduser_property.person_accnt1', $request->person_accnt1);
        } elseif ($request->filled('person_accnt')) {
            $query->where('enduser_property.person_accnt', $request->person_accnt);
        }

        // Same report type logic as generateItems
        if ($request->filled('report_type')) {
            if ((string) $request->report_type === '5') {
                $query->where('enduser_property.remarks', 'Unserviceable');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('enduser_property.remarks')
                    ->orWhere('enduser_property.remarks', '!=', 'Unserviceable');
                });
            }

            switch ((string) $request->report_type) {
                case '1': // RPCPPE
                case '4': // PAR
                    $query->where('enduser_property.item_cost', '>=', 50000);
                    break;

                case '2': // RPCSEP
                case '3': // ICS
                    $query->where('enduser_property.item_cost', '<', 50000);
                    break;

                case '5': // UNSERVICEABLE
                    break;
            }
        }

        if ($request->filled('date_range') && $request->date_range !== 'All' && trim($request->date_range) !== '') {
            $dateString = trim($request->date_range);
            $cleaned = preg_replace('/\s+/', ' ', $dateString);
            $dates = explode(' - ', $cleaned);

            if (count($dates) === 2) {
                $start = trim($dates[0]);
                $end   = trim($dates[1]);

                try {
                    $startDate = Carbon::createFromFormat('Y-m-d', $start)->format('Y-m-d');
                    $endDate   = Carbon::createFromFormat('Y-m-d', $end)->format('Y-m-d');

                    $query->whereBetween('enduser_property.date_acquired', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    \Log::warning('Invalid date_range format: ' . $dateString);
                }
            }
        }

        // Safe item filter, supports item_id[] = All
        $itemIds = $request->input('item_id', []);
        $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];
        $itemIds = array_filter($itemIds, function ($id) {
            return $id !== null && $id !== '';
        });

        if (!empty($itemIds) && !in_array('All', $itemIds, true)) {
            $query->whereIn('enduser_property.id', $itemIds);
        }

        $query->orderBy('enduser_property.date_acquired', 'desc');

        return $query;
    }

    // Get report title
    private function getReportTitle($reportType)
    {
        $titles = [
            1 => 'REPORT ON PROPERTY, PLANT AND EQUIPMENT (RPCPPE)',
            2 => 'REPORT ON SEMI-EXPENDABLE PROPERTY (RPCSEP)',
            3 => 'INVENTORY CUSTODIAN SLIP (ICS)',
            4 => 'PROPERTY ACKNOWLEDGMENT RECEIPT (PAR)',
            5 => 'REPORT ON UNSERVICEABLE PROPERTY'
        ];
        
        return $titles[$reportType] ?? 'PROPERTY REPORT';
    }

    // Get report view based on type
    private function getReportView($reportType)
    {
        $views = [
            1 => 'reports.rpcppe_report',
            2 => 'reports.rpcsep_report',
            3 => 'reports.ics_report',
            4 => 'reports.par_report',
            5 => 'reports.unserviceable_report'
        ];
        
        return $views[$reportType] ?? 'reports.default_report';
    }

    // Generate report filename
    private function getReportFilename($reportType, $office)
    {
        $typeNames = [
            1 => 'RPCPPE',
            2 => 'RPCSEP',
            3 => 'ICS',
            4 => 'PAR',
            5 => 'UNSERVICEABLE'
        ];
        
        $typeName = $typeNames[$reportType] ?? 'REPORT';
        $officeName = $office ? str_replace(' ', '_', $office->office_abbr ?? $office->office_name ?? 'OFFICE') : 'ALL';
        $date = Carbon::now()->format('Ymd_His');
        
        return "{$typeName}_{$officeName}_{$date}.pdf";
    }

    // Generate report number
    private function generateReportNumber($reportType)
    {
        $prefixes = [
            1 => 'RPCPPE',
            2 => 'RPCSEP',
            3 => 'ICS',
            4 => 'PAR',
            5 => 'UNSERV'
        ];
        
        $prefix = $prefixes[$reportType] ?? 'RPT';
        $year = Carbon::now()->format('Y');
        $sequence = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}-{$sequence}";
    }

    // Get filter summary for display
    private function getFilterSummary($request)
    {
        $summary = [];
        
        if ($request->filled('date_range') && $request->date_range !== 'All') {
            // Use formatted date range
            $summary['Date Range'] = $this->formatDateRangeForDisplay($request->date_range);
        }
        
        if ($request->filled('categories_id') && $request->categories_id !== 'All') {
            $category = Category::find($request->categories_id);
            $summary['Category'] = $category ? $category->cat_name : $request->categories_id;
        }
        
        if ($request->filled('property_id') && $request->property_id !== 'All') {
            $property = Property::find($request->property_id);
            $summary['Account Title'] = $property ? $property->property_name : $request->property_id;
        }
        
        if ($request->filled('location') && $request->location !== 'All') {
            $location = Office::find($request->location);
            $summary['Location'] = $location ? $location->office_name : $request->location;
        }
        
        if ($request->filled('person_accnt')) {
            $accountable = Accountable::find($request->person_accnt);
            $summary['Accountable Person'] = $accountable ? $accountable->person_accnt : $request->person_accnt;
        }
        
        if ($request->filled('person_accnt1')) {
            $endUser = Accountable::find($request->person_accnt1);
            $summary['End User'] = $endUser ? $endUser->person_accnt : $request->person_accnt1;
        }
        
        return $summary;
    }
    
    //OLD FUNCTIONS

    public function reportOption($repcat) {
        $setting = Setting::firstOrNew(['id' => 1]);
        
        $office = Office::leftJoin('campuses', 'offices.loc_camp', '=', 'campuses.id')
        ->select('offices.*', 'campuses.campus_abbr') 
        ->get();
        
        $uid = auth()->user()->campus_id;
        $uoffice = Office::where('camp_id', $uid)->first();
        $property = Property::all();
        $category = Category::all();

        return view('reports.report_option', compact('setting', 'uoffice', 'office', 'property', 'category', 'repcat'));
    }

    public function reportOptionView(Request $request) {
        ini_set('memory_limit', '2G');  
        set_time_limit(600);            

        $ucampid = auth()->user()->campus_id;
        $exists = Office::whereNotNull('camp_id')
            ->where('camp_id', $ucampid)
            ->exists();
        $repcat = $request->repcat;
        // dd($repcat);
        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::all();
        $item = Item::all();
        $unit = Unit::all();
        $category = Category::all();

        $serial = $request->serial;
        $acquired = $request->acquired;
        $locationcolumn = $request->locationcolumn;
        // dd($serial);

        $officeId = $request->office_id;
        $officeId1 = $request->office_id;
        $propertiesId = $request->properties_id;
        $categoriesId = $request->categories_id;
        $propId = $request->property_id;
        $startDate = $request->start_date_acquired;
        $endDate = $request->end_date_acquired;
        $selectId = $request->selected_account_id;

        $formattedStartDate = $startDate ? date('M. d, Y', strtotime($startDate)) : '';
        $formattedEndDate = $endDate ? date('M. d, Y', strtotime($endDate)) : '';

        $allOffice = ($officeId == 'All') ? '!=' : '=';

        $location = $request->input('location');
        // dd($selectId);
        $purchase = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->leftJoin('offices as locations', 'enduser_property.location', '=', 'locations.id')
            ->join('properties', 'enduser_property.selected_account_id', '=', 'properties.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            ->select('enduser_property.*', 'offices.*', 'properties.*', 'units.*', 'items.*', 'locations.office_abbr as itemlocated')
            ->where('enduser_property.deleted', 0)
            ->where(function ($query) use ($officeId, $allOffice) {
                if ($officeId == 1) {
                    $query->whereNotIn('enduser_property.office_id', [2, 5, 6, 7, 8, 12, 13, 14, 15, 16, 17]);
                } else {
                    $query->where('enduser_property.office_id', $allOffice, $officeId);
                }
            })
            ->when(!empty($propertiesId), function ($query) use ($propertiesId) {
                if (is_array($propertiesId)) {
                    $query->whereIn('enduser_property.properties_id', $propertiesId);
                } else {
                    $query->where('enduser_property.properties_id', $propertiesId);
                }
            })
            ->where(function ($query) use ($categoriesId) {
                if ($categoriesId !== 'All') {
                    $query->where('enduser_property.categories_id', $categoriesId);
                }
            })
            ->where(function ($query) use ($propId) {
                if ($propId !== 'All') {
                    $query->where('enduser_property.property_id', $propId);
                }
            })
            // ->where(function ($query) use ($selectId) {
            //     if ($selectId !== 'All') {
            //         $query->where('enduser_property.selected_account_id', $selectId);
            //     }
            // })
            ->where(function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('enduser_property.date_acquired', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->where('enduser_property.date_acquired', '>=', $startDate);
                } elseif ($endDate) {
                    $query->where('enduser_property.date_acquired', '<=', $endDate);
                }
            })
            ->where(function ($query) use ($location) {
                if ($location === 'All') {
                    // No filter applied — get everything including nulls
                } elseif ($location === 'null') {
                    $query->whereNull('enduser_property.location');
                } elseif (!empty($location)) {
                    $query->where('enduser_property.location', $location);
                }
            });

        if ($exists) {
            $purchase->where('offices.camp_id', $ucampid);
        }

        $purchase = $purchase->get();

        $cond = ($categoriesId == 'All') ? '!=' : '=';

        $baseQuery = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->join('properties', 'enduser_property.selected_account_id', '=', 'properties.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            ->where('enduser_property.deleted', 0)
            ->when(!empty($propertiesId), function ($query) use ($propertiesId) {
                if (is_array($propertiesId)) {
                    $query->whereIn('enduser_property.properties_id', $propertiesId);
                } else {
                    $query->where('enduser_property.properties_id', $propertiesId);
                }
            })
            ->where(function ($query) use ($categoriesId) {
                if ($categoriesId !== 'All') {
                    $query->where('enduser_property.categories_id', $categoriesId);
                }
            })
            ->where(function ($query) use ($propId) {
                if ($propId !== 'All') {
                    $query->where('enduser_property.property_id', $propId);
                }
            })
            // ->where('enduser_property.selected_account_id', $cond, $selectId)
            ->when($exists, function ($query) use ($ucampid) {
                $query->where('offices.camp_id', $ucampid);
            });

        // PURCHASE 1
        $purchase1Query = clone $baseQuery;
        $purchase1Query->where(function ($query) use ($officeId, $cond) {
            if ($officeId == 1) {
                $query->whereNotIn('enduser_property.office_id', [2, 5, 6, 7, 8, 12, 13, 14, 15, 16, 17]);
            } else {
                $query->where('enduser_property.office_id', $cond, $officeId);
            }
        });
        if ($startDate) {
            $purchase1Query->where('enduser_property.date_acquired', '<', $startDate);
        }
        $purchase1 = $purchase1Query->selectRaw("
            SUM(total_cost) as total_cost_sum,
            SUM(qty) as qty_sum
        ")->first();

        // PURCHASE 2
        $purchase2Query = clone $baseQuery;
        $purchase2Query->where(function ($query) use ($officeId, $allOffice, $cond) {
            if ($officeId == 1) {
                $query->whereNotIn('enduser_property.office_id', [2, 5, 6, 7, 8, 12, 13, 14, 15, 16, 17]);
            } else {
                $query->where('enduser_property.office_id', $cond, $allOffice);
            }
        });
        if ($endDate) {
            $purchase2Query->where('enduser_property.date_acquired', '>', $endDate);
        }
        $purchase2 = $purchase2Query->selectRaw("
            SUM(total_cost) as total_cost_sum,
            SUM(qty) as qty_sum
        ")->first();

        // Assign sums
        $bforward  = $purchase1->total_cost_sum ?? 0;
        $bforward1 = $purchase2->total_cost_sum ?? 0;
        $countBforward  = $purchase1->qty_sum ?? 0;
        $countBforward1 = $purchase2->qty_sum ?? 0;

        $data = [
            'purchase' => $purchase,
            'startDate' => $formattedStartDate,
            'endDate' => $formattedEndDate,
            'selectedPropertyId' => $selectId,
            'category_id' => $purchase,
            'bforward' => $bforward, 
            'bforward1' => $bforward1, 
            'countBforward' => $countBforward,
            'countBforward1' => $countBforward1,
            'locationcolumn' => $locationcolumn,
            'serial' => $serial,
            'acquired' => $acquired,
        ];

        $page = ($repcat == 1)
            ? 'reports.report_generated'
            : (($repcat == 2)
                ? 'reports.rpcsep_option_reportsGen'
                : '');

        if($request->file_type == "PDF"){
                $pdf = Snappy::loadView($page, $data)
                    ->setPaper('Legal')
                    ->setOrientation('landscape')
                    ->setOption('disable-smart-shrinking', true)
                    ->setOption('no-stop-slow-scripts', true)
                    ->setOption('quiet', true);

                return $pdf->inline();
        }else {
            $filePath = public_path('Forms/RPCPPE Reports.xlsx');
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);

            $sheet = $spreadsheet->getActiveSheet();
            
            $row = 10;
            $overallTotal = 0;

            $sheetC3 = '';

            $sheetC3 = (($request->categories_id) != "All")  ? isset($purchase->first()->account_title_abbr) ? $purchase->first()->account_title_abbr : '' : 'All';
        
    
            
            $sheetC5 = 'As at ' . $formattedStartDate . ' to ' . $formattedEndDate;
            
            $sheet->mergeCells('C3:H3');
            $sheet->mergeCells('C5:H5');
            $sheet->setCellValue('C3', $sheetC3);
            $sheet->setCellValue('C4', '(Type of Property, Plant and Equipment)');
            $sheet->setCellValue('C5', $sheetC5);
            $sheet->setCellValue('F9', $countBforward);
            $sheet->mergeCells('G9:L9');
            $sheet->setCellValue('G9', number_format($bforward, 2));
            
            foreach ($purchase as $pur) {
                
                if (is_numeric(str_replace(',', '', $pur->total_cost))){
                    $overallTotal += str_replace(',', '', $pur->total_cost); 
                }

                $sheet->setCellValue('A' . $row, $pur->item_name);
                $sheet->setCellValue('B' . $row, $pur->item_descrip);
                $sheet->setCellValue('C' . $row, $pur->property_no_generated);
                $sheet->setCellValue('D' . $row, $pur->unit_name);
                $sheet->setCellValue('E' . $row, $pur->item_cost);
                $sheet->setCellValue('F' . $row, $pur->qty);
                $sheet->setCellValue('G' . $row, $pur->total_cost);
                $sheet->setCellValue('H' . $row, '');
                $sheet->setCellValue('I' . $row, $pur->qty);
                $sheet->setCellValue('J' . $row, '');
                $sheet->setCellValue('K' . $row, $pur->remarks);
                $sheet->setCellValue('L' . $row, $pur->office_name);
                if ($serial == 1) {
                    $sheet->setCellValue('M' . $row, $pur->serial_number ?? 'N/A');
                }
                if ($acquired == 1) {
                    $sheet->setCellValue('N' . $row, $pur->date_acquired ?? 'N/A');
                }
                $style = $sheet->getStyle('A' . $row . ':L' . $row);
                $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $row++;

            }
            
            $footerRow = $row;

            $rows = [
                ['label' => 'Total' . $countBforward1, 'value' => $overallTotal],
                ['label' => 'Grand Total' . $countBforward + $countBforward1, 'value' => $overallTotal + $bforward + $bforward1],
            ];
            
            foreach ($rows as $rowData) {
                $label = $rowData['label'];
                $value = $rowData['value'];
            
                $rangeAtoF = 'A' . $footerRow . ':F' . $footerRow;
                $sheet->mergeCells($rangeAtoF);
                $sheet->setCellValue('A' . $footerRow, $label)
                ->getStyle('A' . $footerRow)
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
                $rangeGtoL = 'G' . $footerRow . ':L' . $footerRow;
                $sheet->mergeCells($rangeGtoL);
                $sheet->setCellValue('G' . $footerRow, number_format($value, 2));
            
                $footerStyleAtoF = $sheet->getStyle($rangeAtoF);
                $footerStyleAtoF->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $footerStyleAtoF->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerStyleGtoL = $sheet->getStyle($rangeGtoL);
                $footerStyleGtoL->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $footerStyleGtoL->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerRow++;
            }

            $footerRow = $row + 2;

            $rows = [
                ['Certified Correct by:', 'Approved by:', 'Verify by:'],
                ['', '', ''],
                ['MA. SOCORRO T. LLAMAS', 'ALADINO C. MORACA, Ph.D.', 'JERENIAS G. AGUI'],
                ['Administrative Officer V/Supply Officer designate', 'SUC President', 'State Auditor IV'],
            ];
            
            $rows = [
                ['Certified Correct by:', 'Approved by:', 'Verify by:'],
                ['', '', ''],
                ['MA. SOCORRO T. LLAMAS', 'ALADINO C. MORACA, Ph.D.', 'JERENIAS G. AGUI'],
                ['Administrative Officer V/Supply Officer designate', 'SUC President', 'State Auditor IV'],
            ];
            
            foreach ($rows as $rowData) {
                $label = $rowData[0];
                $value = $rowData[1];
            
                $rangeAtoB = 'A' . $footerRow . ':B' . $footerRow;
                $sheet->mergeCells($rangeAtoB);
                $sheet->setCellValue('A' . $footerRow, $label);
            
                $rangeCtoG = 'C' . $footerRow . ':G' . $footerRow;
                $sheet->mergeCells($rangeCtoG);
            
                $sheet->setCellValue('C' . $footerRow, is_numeric($value) ? number_format((float)$value, 2) : $value);
            
                $rangeHtoL = 'H' . $footerRow . ':L' . $footerRow;
                $sheet->mergeCells($rangeHtoL);
            
                $sheet->setCellValue('H' . $footerRow, is_numeric($value) ? number_format((float)$value, 2) : $value);
            
                $footerStyleAtoB = $sheet->getStyle($rangeAtoB);
                $footerStyleAtoB->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerStyleCtoG = $sheet->getStyle($rangeCtoG);
                $footerStyleCtoG->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerStyleHtoL = $sheet->getStyle($rangeHtoL);
                $footerStyleHtoL->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerRow++;
            }

            $randomFilename = 'RPCPPE_Reports_' . uniqid() . '.xlsx';

            $copyFilename = 'RPCPPE_Reports_' . date('dmYHis') . '.xlsx';
            $copyPath = public_path("Downloaded Form/" . $copyFilename);
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($copyPath);
            
            return redirect()->back()->with('download', $copyFilename)->with('successcopy', 'File copied successfully!');
            
        }
    }

    public function rpcppeOption() {
        $setting = Setting::firstOrNew(['id' => 1]);
        
        $office = Office::leftJoin('campuses', 'offices.loc_camp', '=', 'campuses.id')
        ->select('offices.*', 'campuses.campus_abbr') 
        ->get();

        $uid = auth()->user()->campus_id;
        $uoffice = Office::where('camp_id', $uid)->first();
        $property = Property::whereIn('id', [3])->get();
        $category = Category::all();

        return view('reports.rpcppe_option', compact('setting', 'uoffice', 'office', 'property', 'category'));
    }
    
    public function rpcppeOptionReportGen(Request $request) {
        $ucampid = auth()->user()->campus_id;
        $exists = Office::whereNotNull('camp_id')
            ->where('camp_id', $ucampid)
            ->exists();

        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::all();
        $item = Item::all();
        $unit = Unit::all();
        $category = Category::all();

        $serial = $request->serial;
        $acquired = $request->acquired;
        $locationcolumn = $request->locationcolumn;
        
        $officeId = $request->query('office_id');
        $officeId1 = $request->query('office_id');
        $propertiesId = $request->query('properties_id');
        $categoriesId = $request->query('categories_id');
        $propId = $request->query('property_id');
        $startDate = $request->query('start_date_acquired');
        $endDate = $request->query('end_date_acquired');
        $selectId = $request->query('selected_account_id');

        $formattedStartDate = $startDate ? date('M. d, Y', strtotime($startDate)) : '';
        $formattedEndDate = $endDate ? date('M. d, Y', strtotime($endDate)) : '';

        $cond = ($categoriesId == 'All') ? '!=' : '=';
        $allOffice = ($officeId == 'All') ? '!=' : '=';

        $propId = ($categoriesId == 'All') ? $propId = '0' : $propId;
        $selectId = ($categoriesId == 'All') ? $selectId = '0' : $selectId;
        $categoriesId = ($categoriesId == 'All') ? $categoriesId = '0' : $categoriesId;

        $location = $request->input('location');

        $purchase = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->leftJoin('offices as locations', 'enduser_property.location', '=', 'locations.id')
            ->join('properties', 'enduser_property.selected_account_id', '=', 'properties.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            ->select('enduser_property.*', 'offices.*', 'properties.*', 'units.*', 'items.*', 'locations.office_abbr as itemlocated')
            ->where(function ($query) use ($officeId, $allOffice, $cond) {
                if ($officeId == 1) {
                    $query->whereNotIn('enduser_property.office_id', [2, 5, 6, 7, 8, 12, 13, 14, 15, 16, 17]);
                } else {
                    $query->where('enduser_property.office_id', $allOffice, $officeId);
                }
            })
            ->where('enduser_property.deleted', 0)
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
            ->where(function ($query) use ($location) {
                if ($location === 'All') {
                    // No filter applied — get everything including nulls
                } elseif ($location === 'null') {
                    $query->whereNull('enduser_property.location'); // N/A option
                } elseif (!empty($location)) {
                    $query->where('enduser_property.location', $location); // Specific location
                }
            });

        if ($exists){
            $purchase->where('offices.camp_id', $ucampid);
        } 

        $purchase = $purchase->get();

        $purchase1 = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->join('properties', 'enduser_property.selected_account_id', '=', 'properties.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            ->where(function ($query) use ($officeId, $allOffice, $cond) {
                if ($officeId == 1) {
                    $query->whereNotIn('enduser_property.office_id', [2, 5, 6, 7, 8, 12, 13, 14, 15, 16, 17]);
                } else {
                    $query->where('enduser_property.office_id', $allOffice, $officeId);
                }
            })
            ->where('enduser_property.deleted', 0)
            ->where('enduser_property.properties_id', $propertiesId)
            ->where('enduser_property.categories_id', $cond, $categoriesId)
            ->where('enduser_property.property_id', $cond, $propId)
            ->where('enduser_property.selected_account_id', $cond, $selectId)
            ->where(function ($query) use ($startDate) {
                if ($startDate) {
                    $query->where('enduser_property.date_acquired', '<', $startDate);
                }
            });

        if ($exists){
            $purchase1->where('offices.camp_id', $ucampid);
        } 
        
        $purchase1 = $purchase1->get();


        $purchase2 = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->join('properties', 'enduser_property.selected_account_id', '=', 'properties.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            // ->where('enduser_property.office_id', $allOffice, $officeId)
            ->where(function ($query) use ($officeId, $allOffice, $cond) {
                if ($officeId == 1) {
                    $query->whereNotIn('enduser_property.office_id', [2, 5, 6, 7, 8, 12, 13, 14, 15, 16, 17]);
                } else {
                    $query->where('enduser_property.office_id', $cond, $allOffice);
                }
            })
            ->where('enduser_property.deleted', 0)
            ->where('enduser_property.properties_id', $propertiesId)
            ->where('enduser_property.categories_id', $cond, $categoriesId)
            ->where('enduser_property.property_id', $cond, $propId)
            ->where('enduser_property.selected_account_id', $cond, $selectId)
            ->where(function ($query) use ($endDate) {
                if ($endDate) {
                    $query->where('enduser_property.date_acquired', '>', $endDate);
                }
            });

        if ($exists){
            $purchase2->where('offices.camp_id', $ucampid);
        } 
        
        $purchase2 = $purchase2->get();

        $bforward = $purchase1->sum(function ($purchase1) {
            return str_replace(',', '', $purchase1->total_cost);
        });

        // Ensure $purchase2 is not null or empty before attempting to use it
        $bforward1 = $purchase2->isEmpty() ? 0 : $purchase2->sum(function ($purchase) {
            return str_replace(',', '', $purchase->total_cost);
        });

        $countBforward  = 0;
        $countBforward1 = 0;

        foreach($purchase1 as $purchaseData){
            if (is_numeric(str_replace(',', '', $purchaseData->qty))){
                $countBforward += (float) str_replace(',', '', $purchaseData->qty); 
            }
        }

        foreach($purchase as $purchaseData1){
            if (is_numeric(str_replace(',', '', $purchaseData1->qty))){
                $countBforward1 += (float) str_replace(',', '', $purchaseData1->qty);
            }
        }

        $data = [
            'purchase' => $purchase,
            'startDate' => $formattedStartDate,
            'endDate' => $formattedEndDate,
            'selectedPropertyId' => $selectId,
            'category_id' => $purchase,
            'bforward' => $bforward, 
            'bforward1' => $bforward1, 
            'countBforward' => $countBforward,
            'locationcolumn' => $locationcolumn,
            'serial' => $serial,
            'acquired' => $acquired,
        ];
 
        if($request->file_type == "PDF"){
            $pdf = PDF::loadView('reports.rpcppe_option_reportsGen', $data)->setPaper('Legal', 'landscape');
            return $pdf->stream();
        }else {
            $filePath = public_path('Forms/RPCPPE Reports.xlsx');
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);

            $sheet = $spreadsheet->getActiveSheet();
            
            $row = 10;
            $overallTotal = 0;

            $sheetC3 = '';

            $sheetC3 = ($request->query('categories_id') != "All")  ? isset($purchase->first()->account_title_abbr) ? $purchase->first()->account_title_abbr : '' : 'All';
        
    
            
            $sheetC5 = 'As at ' . $formattedStartDate . ' to ' . $formattedEndDate;
            
            $sheet->mergeCells('C3:H3');
            $sheet->mergeCells('C5:H5');
            $sheet->setCellValue('C3', $sheetC3);
            $sheet->setCellValue('C4', '(Type of Property, Plant and Equipment)');
            $sheet->setCellValue('C5', $sheetC5);
            $sheet->setCellValue('F9', $countBforward);
            $sheet->mergeCells('G9:L9');
            $sheet->setCellValue('G9', number_format($bforward, 2));
            
            foreach ($purchase as $pur) {
                
                if (is_numeric(str_replace(',', '', $pur->total_cost))){
                    $overallTotal += str_replace(',', '', $pur->total_cost); 
                }

                $sheet->setCellValue('A' . $row, $pur->item_name);
                $sheet->setCellValue('B' . $row, $pur->item_descrip);
                $sheet->setCellValue('C' . $row, $pur->property_no_generated);
                $sheet->setCellValue('D' . $row, $pur->unit_name);
                $sheet->setCellValue('E' . $row, $pur->item_cost);
                $sheet->setCellValue('F' . $row, $pur->qty);
                $sheet->setCellValue('G' . $row, $pur->total_cost);
                $sheet->setCellValue('H' . $row, '');
                $sheet->setCellValue('I' . $row, $pur->qty);
                $sheet->setCellValue('J' . $row, '');
                $sheet->setCellValue('K' . $row, $pur->remarks);
                $sheet->setCellValue('L' . $row, $pur->office_name);
            
                $style = $sheet->getStyle('A' . $row . ':L' . $row);
                $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $row++;

            }
            
            $footerRow = $row;

            $rows = [
                ['label' => 'Total' . $countBforward1, 'value' => $overallTotal],
                ['label' => 'Grand Total' . $countBforward + $countBforward1, 'value' => $overallTotal + $bforward + $bforward1],
            ];
            
            foreach ($rows as $rowData) {
                $label = $rowData['label'];
                $value = $rowData['value'];
            
                $rangeAtoF = 'A' . $footerRow . ':F' . $footerRow;
                $sheet->mergeCells($rangeAtoF);
                $sheet->setCellValue('A' . $footerRow, $label)
                ->getStyle('A' . $footerRow)
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
                $rangeGtoL = 'G' . $footerRow . ':L' . $footerRow;
                $sheet->mergeCells($rangeGtoL);
                $sheet->setCellValue('G' . $footerRow, number_format($value, 2));
            
                $footerStyleAtoF = $sheet->getStyle($rangeAtoF);
                $footerStyleAtoF->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $footerStyleAtoF->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerStyleGtoL = $sheet->getStyle($rangeGtoL);
                $footerStyleGtoL->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $footerStyleGtoL->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerRow++;
            }

            $footerRow = $row + 2;

            $rows = [
                ['Certified Correct by:', 'Approved by:', 'Verify by:'],
                ['', '', ''],
                ['MA. SOCORRO T. LLAMAS', 'ALADINO C. MORACA, Ph.D.', 'JERENIAS G. AGUI'],
                ['Administrative Officer V/Supply Officer designate', 'SUC President', 'State Auditor IV'],
            ];
            
            $rows = [
                ['Certified Correct by:', 'Approved by:', 'Verify by:'],
                ['', '', ''],
                ['MA. SOCORRO T. LLAMAS', 'ALADINO C. MORACA, Ph.D.', 'JERENIAS G. AGUI'],
                ['Administrative Officer V/Supply Officer designate', 'SUC President', 'State Auditor IV'],
            ];
            
            foreach ($rows as $rowData) {
                $label = $rowData[0];
                $value = $rowData[1];
            
                $rangeAtoB = 'A' . $footerRow . ':B' . $footerRow;
                $sheet->mergeCells($rangeAtoB);
                $sheet->setCellValue('A' . $footerRow, $label);
            
                $rangeCtoG = 'C' . $footerRow . ':G' . $footerRow;
                $sheet->mergeCells($rangeCtoG);
            
                $sheet->setCellValue('C' . $footerRow, is_numeric($value) ? number_format((float)$value, 2) : $value);
            
                $rangeHtoL = 'H' . $footerRow . ':L' . $footerRow;
                $sheet->mergeCells($rangeHtoL);
            
                $sheet->setCellValue('H' . $footerRow, is_numeric($value) ? number_format((float)$value, 2) : $value);
            
                $footerStyleAtoB = $sheet->getStyle($rangeAtoB);
                $footerStyleAtoB->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerStyleCtoG = $sheet->getStyle($rangeCtoG);
                $footerStyleCtoG->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerStyleHtoL = $sheet->getStyle($rangeHtoL);
                $footerStyleHtoL->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerRow++;
            }

            $randomFilename = 'RPCPPE_Reports_' . uniqid() . '.xlsx';

            $copyFilename = 'RPCPPE_Reports_' . date('dmYHis') . '.xlsx';
            $copyPath = public_path("Downloaded Form/" . $copyFilename);
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($copyPath);
            
            return redirect()->back()->with('download', $copyFilename)->with('successcopy', 'File copied successfully!');
            
        }
    }

    public function rpcsepOption() {
        $setting = Setting::firstOrNew(['id' => 1]);

        $office = Office::leftJoin('campuses', 'offices.loc_camp', '=', 'campuses.id')
        ->select('offices.*', 'campuses.campus_abbr') 
        ->get();

        $uid = auth()->user()->campus_id;
        $uoffice = Office::where('camp_id', $uid)->first();
        $property = Property::whereIn('id', [1, 2])->get();
        $category = Category::all();

        return view('reports.rpcsep_option', compact('setting', 'uoffice', 'office', 'property', 'category'));
    }
    
    public function rpcsepOptionReportGen(Request $request) {
        $ucampid = auth()->user()->campus_id;
        $exists = Office::whereNotNull('camp_id')
            ->where('camp_id', $ucampid)
            ->exists();

        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::all();
        $item = Item::all();
        $unit = Unit::all();
        $category = Category::all();

        $serial = $request->serial;

        $officeId = $request->office_id;
        $propertiesId = $request->properties_id;
        $propId = $request->property_id;
        $categoriesId = $request->categories_id;
        $selectId = $request->selected_account_id;
        $startDate = $request->start_date_acquired;
        $endDate = $request->end_date_acquired;

        $condProperties = ($propertiesId == 'All') ? '!=' : '=';
        $condpropId = ($propId == 'All') ? '!=' : '=';
        $condCategories = ($categoriesId == 'All') ? '!=' : '=';
        $condselectId = ($selectId == 'All') ? '!=' : '=';
        
        $propertiesId = ($condProperties == 'All') ? '0' : $propertiesId;
        $categoriesId = ($condCategories == 'All') ? '0' : $categoriesId;
        $selectId = ($condselectId == 'All') ? '0' : $selectId;
        $propId = ($condpropId == 'All') ? '0' : $propId;

        // dd($propertiesId);
        
        $allOffice = ($officeId == 'All') ? '!=' : '=';
        $officeId = ($officeId == 'All') ? '0' : $officeId;
        
        $formattedStartDate = $startDate ? date('M. d, Y', strtotime($startDate)) : '';
        $formattedEndDate = $endDate ? date('M. d, Y', strtotime($endDate)) : '';
        
        $purchase = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->join('properties', 'enduser_property.selected_account_id', '=', 'properties.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            ->where('enduser_property.deleted', 0)
            ->where(function ($query) use ($officeId, $allOffice) {
                if ($officeId == 1) {
                    $query->whereNotIn('enduser_property.office_id', [2, 5, 6, 7, 8, 12, 13, 14, 15, 16, 17]);
                } else {
                    $query->where('enduser_property.office_id', $allOffice, $officeId);
                }
            })
            ->where(function ($query) use ($condProperties, $propertiesId) {
                if (!empty($propertiesId)) {
                    $query->where('enduser_property.properties_id', $condProperties, $propertiesId);
                }
            })
            ->where(function ($query) use ($condpropId, $propId) {
                if (!empty($propId)) {
                    $query->where('enduser_property.property_id', $condpropId, $propId);
                }
            })
            ->where('enduser_property.categories_id', $condCategories, $categoriesId)
            ->where(function ($query) use ($selectId) {
                if (!empty($selectId)) {
                    $query->where('enduser_property.selected_account_id', $selectId);
                }
            })
            ->where(function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('enduser_property.date_acquired', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->where('enduser_property.date_acquired', '>=', $startDate);
                } elseif ($endDate) {
                    $query->where('enduser_property.date_acquired', '<=', $endDate);
                }
            });
            
        if ($exists){
            $purchase->where('offices.camp_id', $ucampid);
        } 

        $purchase = $purchase->get();
            
        $purchase1 = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->join('properties', 'enduser_property.selected_account_id', '=', 'properties.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            ->where('enduser_property.deleted', 0)
            ->where(function ($query) use ($officeId, $allOffice) {
                if ($officeId == 1) {
                    $query->whereNotIn('enduser_property.office_id', [2, 5, 6, 7, 8, 12, 13, 14, 15, 16, 17]);
                } else {
                    $query->where('enduser_property.office_id', $allOffice, $officeId);
                }
            })
            ->where(function ($query) use ($condProperties, $propertiesId) {
                if (!empty($propertiesId)) {
                    $query->where('enduser_property.properties_id', $condProperties, $propertiesId);
                }
            })
            ->where(function ($query) use ($condpropId, $propId) {
                if (!empty($propId)) {
                    $query->where('enduser_property.property_id', $condpropId, $propId);
                }
            })
            ->where('enduser_property.categories_id', $condCategories, $categoriesId)
            ->where(function ($query) use ($selectId) {
                if (!empty($selectId)) {
                    $query->where('enduser_property.selected_account_id', $selectId);
                }
            })
            ->where(function ($query) use ($startDate) {
                if ($startDate) {
                    $query->where('enduser_property.date_acquired', '<', $startDate);
                }
            })
            ->get();


        $purchase2 = EnduserProperty::join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->join('properties', 'enduser_property.selected_account_id', '=', 'properties.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            ->where('enduser_property.office_id', $allOffice, $officeId)
            ->where('enduser_property.deleted', 0)
            ->where(function ($query) use ($condProperties, $propertiesId) {
                if (!empty($propertiesId)) {
                    $query->where('enduser_property.properties_id', $condProperties, $propertiesId);
                }
            })
            ->where(function ($query) use ($condpropId, $propId) {
                if (!empty($propId)) {
                    $query->where('enduser_property.property_id', $condpropId, $propId);
                }
            })
            ->where('enduser_property.categories_id', $condCategories, $categoriesId)
            ->where(function ($query) use ($selectId) {
                if (!empty($selectId)) {
                    $query->where('enduser_property.selected_account_id', $selectId);
                }
            })
            ->where(function ($query) use ($endDate) {
                if ($endDate) {
                    $query->where('enduser_property.date_acquired', '>', $endDate);
                }
            })
            ->get();

        $bforward = $purchase1->sum(function ($purchase) {
            return str_replace(',', '', $purchase->total_cost);
        });

        $bforward1 = $purchase2->isEmpty() ? 0 : $purchase2->sum(function ($purchase) {
            return str_replace(',', '', $purchase->total_cost);
        });


        $data = [
            'purchase' => $purchase,
            'startDate' => $formattedStartDate,
            'endDate' => $formattedEndDate,
            'selectedPropertyId' => $selectId,
            'category_id' => $purchase,
            'bforward' => $bforward, 
            'bforward1' => $bforward1, 
            'serial' => $serial,
        ];

        if($request->file_type == "PDF"){
            $pdf = PDF::loadView('reports.rpcsep_option_reportsGen', $data)->setPaper('Legal', 'landscape');
            return $pdf->stream();
        }else {
            $filePath = public_path('Forms/RPCSEP Reports.xlsx');
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            
            $sheet = $spreadsheet->getActiveSheet();
            
            $row = 10;
            $overallTotal = 0;

            $sheetC3 = '';

            if ($request->categories_id === 'All' || empty($categoriesId)) {
                if ($request->categories_id === 'All')
                    $sheetC3 = 'ALL';
                else {
                    $sheetC3 = isset($purchase->first()->account_title_abbr) ? $purchase->first()->account_title_abbr : '';
                }
            } elseif ($purchase->isEmpty()) {
                $sheetC3 = '____________________';
            }
            
            $sheetC5 = 'As at ' . $formattedStartDate . ' to ' . $formattedEndDate;
            
            $sheet->mergeCells('C3:H3');
            $sheet->mergeCells('C5:H5');
            $sheet->setCellValue('C3', $sheetC3);
            $sheet->setCellValue('C4', '(Type of Property, Plant and Equipment)');
            $sheet->setCellValue('C5', $sheetC5);

            $sheet->mergeCells('G9:L9');
            $sheet->setCellValue('G9', number_format($bforward, 2));
            
            foreach ($purchase as $pur) {
                
                if (is_numeric(str_replace(',', '', $pur->total_cost))){
                    $overallTotal += str_replace(',', '', $pur->total_cost); 
                }

                $sheet->setCellValue('A' . $row, $pur->item_name);
                $sheet->setCellValue('B' . $row, $pur->item_descrip);
                $sheet->setCellValue('C' . $row, $pur->property_no_generated);
                $sheet->setCellValue('D' . $row, $pur->unit_name);
                $sheet->setCellValue('E' . $row, $pur->item_cost);
                $sheet->setCellValue('F' . $row, $pur->qty);
                $sheet->setCellValue('G' . $row, $pur->total_cost);
                $sheet->setCellValue('H' . $row, '');
                $sheet->setCellValue('I' . $row, $pur->qty);
                $sheet->setCellValue('J' . $row, '');
                $sheet->setCellValue('K' . $row, $pur->remarks);
                $sheet->setCellValue('L' . $row, $pur->office_name);
            
                $style = $sheet->getStyle('A' . $row . ':L' . $row);
                $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $row++;

            }
            
            $footerRow = $row;

            $rows = [
                ['label' => 'Total', 'value' => $overallTotal],
                ['label' => 'Grand Total', 'value' => $overallTotal + $bforward + $bforward1],
            ];
            
            foreach ($rows as $rowData) {
                $label = $rowData['label'];
                $value = $rowData['value'];
            
                $rangeAtoF = 'A' . $footerRow . ':F' . $footerRow;
                $sheet->mergeCells($rangeAtoF);
                $sheet->setCellValue('A' . $footerRow, $label)
                ->getStyle('A' . $footerRow)
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
                $rangeGtoL = 'G' . $footerRow . ':L' . $footerRow;
                $sheet->mergeCells($rangeGtoL);
                $sheet->setCellValue('G' . $footerRow, number_format($value, 2));
            
                $footerStyleAtoF = $sheet->getStyle($rangeAtoF);
                $footerStyleAtoF->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $footerStyleAtoF->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerStyleGtoL = $sheet->getStyle($rangeGtoL);
                $footerStyleGtoL->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $footerStyleGtoL->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerRow++;
            }

            $footerRow = $row + 2;

            $rows = [
                ['Certified Correct by:', 'Approved by:', 'Verify by:'],
                ['', '', ''],
                ['MA. SOCORRO T. LLAMAS', 'ALADINO C. MORACA, Ph.D.', ''],
                ['Administrative Officer IV/Supply Officer designate', 'SUC President', 'Signature over Printed Name of COA Representative'],
            ];
            
            $rows = [
                ['Certified Correct by:', 'Approved by:', 'Verify by:'],
                ['', '', ''],
                ['MA. SOCORRO T. LLAMAS', 'ALADINO C. MORACA, Ph.D.', ''],
                ['Administrative Officer IV/Supply Officer designate', 'SUC President', 'Signature over Printed Name of COA Representative'],
            ];
            
            foreach ($rows as $rowData) {
                $label = $rowData[0];
                $value = $rowData[1];
            
                $rangeAtoB = 'A' . $footerRow . ':B' . $footerRow;
                $sheet->mergeCells($rangeAtoB);
                $sheet->setCellValue('A' . $footerRow, $label);
            
                $rangeCtoG = 'C' . $footerRow . ':G' . $footerRow;
                $sheet->mergeCells($rangeCtoG);
            
                $sheet->setCellValue('C' . $footerRow, is_numeric($value) ? number_format((float)$value, 2) : $value);
            
                $rangeHtoL = 'H' . $footerRow . ':L' . $footerRow;
                $sheet->mergeCells($rangeHtoL);
            
                $sheet->setCellValue('H' . $footerRow, is_numeric($value) ? number_format((float)$value, 2) : $value);
            
                $footerStyleAtoB = $sheet->getStyle($rangeAtoB);
                $footerStyleAtoB->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerStyleCtoG = $sheet->getStyle($rangeCtoG);
                $footerStyleCtoG->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerStyleHtoL = $sheet->getStyle($rangeHtoL);
                $footerStyleHtoL->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
                $footerRow++;
            }

            $randomFilename = 'RPCPPE_Reports_' . uniqid() . '.xlsx';

            $copyFilename = 'RPCPPE_Reports_' . date('dmYHis') . '.xlsx';
            $copyPath = public_path("Downloaded Form/" . $copyFilename);
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($copyPath);
            
            return redirect()->back()->with('download', $copyFilename)->with('successcopy', 'File copied successfully!');
            
        }
    }

    public function unserviceForm(){
        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::all();
        $property = Property::all();
        $category = Category::all();

        return view('reports.unserviceable_form', compact('setting', 'office', 'property', 'category'));
    }

    public function unserviceReport(Request $request){
        $setting = Setting::firstOrNew(['id' => 1]);
        $officeId = $request->office_id;
        $personaccountable = $request->person_accnt;
        $itemId = $request->item_id;
        $pAccountable = $request->pAccountable;
        $categoriesId = $request->categories_id;
        $propId = $request->property_id;
        $startDate = $request->start_date_acquired;
        $endDate = $request->end_date_acquired;
        $selectId = $request->selected_account_id;

        $serial = $request->serial;
        
        $formattedStartDate = $startDate ? date('M. d, Y', strtotime($startDate)) : '';
        $formattedEndDate = $endDate ? date('M. d, Y', strtotime($endDate)) : '';
        $datereport = $formattedStartDate.'-'.$formattedEndDate;
        $condcategories = ($categoriesId == 'All') ? '!=' : '=';
        $condpropid = ($propId == 'All') ? '!=' : '=';
        $conaccountid = ($selectId == 'All' || $selectId == '') ? '!=' : '=';
        $officecond = ($officeId == 'All') ? '!=' : '=';
    
        $categoriesId = ($categoriesId == 'All') ? '0' : $categoriesId;
        $propId = ($propId == 'All') ? '0' : $propId;
        $selectId = ($selectId == 'All') ? '0' : $selectId;
        $officeId = ($officeId == 'All') ? '0' : $officeId;
    
        $selectedItem = Item::whereIn('id', $itemId)->get();
        $condAccnt = ($pAccountable == 'accountable') ? 'enduser_property.person_accnt' : 'enduser_property.office_id';
    
        $unservicequery = EnduserProperty::join('items', 'enduser_property.item_id', '=', 'items.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->select('enduser_property.*', 'items.*', 'offices.*',  'offices.id as oid', 'items.id as itemid', 'units.*', 'offices.office_abbr', 'offices.office_officer');
    
        if ($itemId[0] != 'All' && !in_array('All', $itemId)) {
            $unservicequery->whereIn('enduser_property.id', $itemId);
        }
    
        if ($pAccountable == 'accountable') {
            $unservicequery->where($condAccnt, $personaccountable);
        } else {
            $unservicequery->where('enduser_property.office_id', $officecond, $officeId);
        }
    
        $unservitems = $unservicequery
            ->where('enduser_property.categories_id', $condcategories, $categoriesId)
            ->where('enduser_property.property_id', $condpropid, $propId)
            ->where('enduser_property.selected_account_id', $conaccountid, $selectId)
            ->where('enduser_property.deleted', 0)
            ->where(function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('enduser_property.date_acquired', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                } elseif ($startDate) {
                    $query->where('enduser_property.date_acquired', '>=', $startDate . ' 00:00:00');
                } elseif ($endDate) {
                    $query->where('enduser_property.date_acquired', '<=', $endDate . ' 23:59:59');
                }
            })
            ->where('enduser_property.remarks', 'Unserviceable')
            ->get();

        if($unservitems->isNotEmpty()){
            $pdf = PDF::loadView('reports.unserviceable_report', compact('selectedItem', 'unservitems', 'serial', 'itemId', 'pAccountable', 'datereport'))->setPaper('Legal', 'landscape');
            return $pdf->stream();
        }else{
            return redirect()->back()->with('error', 'No Item Found Belong to this End User!');
        }
    }

    public function icsOption() {
        $setting = Setting::firstOrNew(['id' => 1]);

        $office = Office::leftJoin('campuses', 'offices.loc_camp', '=', 'campuses.id')
            ->select('offices.*', 'campuses.campus_abbr') 
            ->get();

        $uid = auth()->user()->campus_id;
        $uoffice = Office::where('camp_id', $uid)->first();
        $property = Property::all();
        $category = Category::all();
        $purchase = EnduserProperty::join('accountable', 'enduser_property.person_accnt', '=', 'accountable.id')
                    ->select('enduser_property.*', 'accountable.person_accnt')
                    ->get();
        if($uoffice){
            $accntables = Accountable::where('off_id', $uoffice->id)->get();
        }else{
            $accntables = Accountable::all();
        }
        return view('reports.ics_option', compact('setting', 'uoffice', 'office', 'property', 'category', 'purchase', 'accntables'));
    }

    public function icsOptionReportGen(Request $request) {
        $ucampid = auth()->user()->campus_id;
        $exists = Office::whereNotNull('camp_id')
            ->where('camp_id', $ucampid)
            ->exists();

        $setting = Setting::firstOrNew(['id' => 1]);
        $officeId = $request->office_id;
        $personaccountable = $request->person_accnt;
        $itemId = $request->item_id;
        $pAccountable = $request->pAccountable;
        $categoriesId = $request->categories_id;
        $propId = $request->property_id;
        $startDate = $request->start_date_acquired;
        $endDate = $request->end_date_acquired;
        $selectId = $request->selected_account_id;

        $formattedStartDate = $startDate ? date('M. d, Y', strtotime($startDate)) : '';
        $formattedEndDate = $endDate ? date('M. d, Y', strtotime($endDate)) : '';
        $datereport = $formattedStartDate.'-'.$formattedEndDate;
        $condcategories = ($categoriesId == 'All') ? '!=' : '=';
        $condpropid = ($propId == 'All') ? '!=' : '=';
        $condsel = ($selectId == 'All' || $selectId == '') ? '!=' : '=';
        $officecond = ($officeId == 'All') ? '!=' : '=';
        $accntcond = ($personaccountable == 'All') ? '!=' : '=';

        $categoriesId = ($categoriesId == 'All') ? '0' : $categoriesId;
        $propId = ($propId == 'All') ? '0' : $propId;
        $selectId = ($selectId == 'All') ? '0' : $selectId;
        $officeId = ($officeId == 'All') ? '0' : $officeId;
        $paccount = ($personaccountable == 'All') ? '0' : $personaccountable;

        $selectedItem = Item::whereIn('id', $itemId)->get();

        $locationcolumn = $request->locationcolumn;

        $icsitemquery = EnduserProperty::query()
            ->join('items', 'enduser_property.item_id', '=', 'items.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->leftJoin('offices as locations', 'enduser_property.location', '=', 'locations.id')
            ->leftJoin('accountable', 'enduser_property.person_accnt1', '=', 'accountable.id')
            ->where('enduser_property.deleted', 0)
            ->select(
                'enduser_property.*',
                'items.*',
                'offices.*',
                'offices.id as oid',
                'enduser_property.person_accnt as person_accnt_id',
                'items.id as itemid',
                'units.*',
                'offices.office_abbr',
                'offices.office_officer',
                'locations.office_abbr as itemlocated',
                'accountable.person_accnt as enduser_name'
            );

        // ────────────────────────────────────────────────
        // Priority filter: person_accnt1 (employee / person accountable)
        $icsitemquery->when($request->filled('person_accnt1'), function ($q) use ($request) {
            // When person_accnt1 is sent → ONLY filter by this field
            $q->where('enduser_property.person_accnt1', $request->person_accnt1);
            
            // IMPORTANT: we do NOT apply person_accnt or office_id in this case
        });

        // Fallback: only apply old logic when NO person_accnt1 was provided
        $icsitemquery->when(!$request->filled('person_accnt1'), function ($q) use ($pAccountable, $accntcond, $paccount, $officecond, $officeId) {
            if ($pAccountable === 'accountable') {
                $q->where('enduser_property.person_accnt', $accntcond, $paccount);
            } else {
                $q->where('enduser_property.office_id', $officecond, $officeId);
            }
        });

        // ────────────────────────────────────────────────
        // Common filters (always applied)
        if ($itemId[0] !== 'All' && !in_array('All', $itemId)) {
            $icsitemquery->whereIn('enduser_property.id', $itemId);
        }

        $icsitemquery
            ->whereIn('enduser_property.properties_id', [1, 2])
            ->where('enduser_property.categories_id', $condcategories, $categoriesId)
            ->where('enduser_property.property_id', $condpropid, $propId)
            ->where('enduser_property.selected_account_id', $condsel, $selectId)
            ->where(function ($q) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $q->whereBetween('enduser_property.date_acquired', [
                        $startDate . ' 00:00:00',
                        $endDate   . ' 23:59:59'
                    ]);
                } elseif ($startDate) {
                    $q->where('enduser_property.date_acquired', '>=', $startDate . ' 00:00:00');
                } elseif ($endDate) {
                    $q->where('enduser_property.date_acquired', '<=', $endDate . ' 23:59:59');
                }
            })
            ->where('enduser_property.item_cost', '<', 50000);

        if ($exists) {
            $icsitemquery->where('offices.camp_id', $ucampid);
        }

        // ────────────────────────────────────────────────
        // Final execution
        $icsitems = $icsitemquery->get();

        // dd($icsitems);

        // // For debugging – use one of these (not both at once)
        // dd(
        //     $icsitems->count(),           // how many rows?
        //     $icsitems->pluck('person_accnt', 'enduser_property1')->unique(), // see which accountable persons appear
        //     // or: $icsitemquery->toSql(), $icsitemquery->getBindings()
        // );

        $pAccountable2 = $request->person_accnt1;

        if($icsitems->isNotEmpty()){
            $pdf = PDF::loadView('reports.ics_option_reportsGen', compact('selectedItem', 'icsitems', 'itemId', 'pAccountable', 'pAccountable2', 'datereport', 'locationcolumn'))->setPaper('Legal', 'portrait');
            return $pdf->stream();
        }else{
            return redirect()->back()->with('error', 'No Item Found Belong to this End User!');
        }
    }

    public function parOption() {
        $setting = Setting::firstOrNew(['id' => 1]);
        $office = Office::all();
        $uid = auth()->user()->campus_id;
        $uoffice = Office::where('camp_id', $uid)->first();
        $property = Property::all();
        $category = Category::all();
        if($uoffice){
            $accntables = Accountable::where('off_id', $uoffice->id)->get();
        }else{
            $accntables = Accountable::all();
        }
        return view('reports.par_option', compact('setting', 'uoffice', 'office', 'property', 'accntables', 'category'));
    }

    public function parOptionReportGen(Request $request) {
        $ucampid = auth()->user()->campus_id;
        $exists = Office::whereNotNull('camp_id')
            ->where('camp_id', $ucampid)
            ->exists();
        $setting = Setting::firstOrNew(['id' => 1]);
    
        $officeId = $request->office_id;
        $personaccountable = $request->person_accnt;
        $itemId = $request->item_id;
        $pAccountable = $request->pAccountable;
        $categoriesId = $request->categories_id;
        $propId = $request->property_id;
        $startDate = $request->start_date_acquired;
        $endDate = $request->end_date_acquired;
        $selectId = $request->selected_account_id;

        $formattedStartDate = $startDate ? date('M. d, Y', strtotime($startDate)) : '';
        $formattedEndDate = $endDate ? date('M. d, Y', strtotime($endDate)) : '';
        $datereport = $formattedStartDate.'-'.$formattedEndDate;
        $condcategories = ($categoriesId == 'All') ? '!=' : '=';
        $condpropid = ($propId == 'All') ? '!=' : '=';
        $conaccountid = ($selectId == 'All' || $selectId == '') ? '!=' : '=';
        $officecond = ($officeId == 'All') ? '!=' : '=';
    
        $categoriesId = ($categoriesId == 'All') ? '0' : $categoriesId;
        $propId = ($propId == 'All') ? '0' : $propId;
        $selectId = ($selectId == 'All') ? '0' : $selectId;
        $officeId = ($officeId == 'All') ? '0' : $officeId;
    
        $selectedItem = Item::whereIn('id', $itemId)->get();
        $condAccnt = ($pAccountable == 'accountable') ? 'enduser_property.person_accnt' : 'enduser_property.office_id';

        $locationcolumn = $request->locationcolumn;

        $paritemquery = EnduserProperty::join('items', 'enduser_property.item_id', '=', 'items.id')
            ->join('units', 'enduser_property.unit_id', '=', 'units.id')
            ->join('offices', 'enduser_property.office_id', '=', 'offices.id')
            ->leftJoin('offices as locations', 'enduser_property.location', '=', 'locations.id')
            ->select('enduser_property.*', 'items.*', 'offices.*', 'offices.id as oid', 'items.id as itemid', 'units.*', 'offices.office_abbr', 'offices.office_officer', 'locations.office_abbr as itemlocated');
    
        $itemId = $request->input('item_id');
        $itemId = is_array($itemId) ? $itemId : (array) $itemId;
        $itemId = array_filter($itemId);

        if (!empty($itemId) && !in_array('All', $itemId, true)) {
            $paritemquery->whereIn('enduser_property.id', $itemId);
        }

        $desigOffice = Accountable::where('id', $request->person_accnt)->value('desig_offid');
        $desigOfficeArray = json_decode($desigOffice, true);

        // $officeFilter = array_merge([$officeId], $desigOfficeArray); 

        $paritems = $paritemquery
            ->where('enduser_property.item_cost', '>=', 50000)
            ->where('enduser_property.categories_id', $condcategories, $categoriesId)
            ->where('enduser_property.property_id', $condpropid, $propId)
            ->where('enduser_property.office_id', $officeId)
            ->where('enduser_property.person_accnt', $personaccountable)
            ->where('enduser_property.selected_account_id', $conaccountid, $selectId)
            ->where('enduser_property.deleted', 0)
            ->when(!empty($request->person_accnt1), function ($q) use ($request) {
                $q->where('person_accnt1', $request->person_accnt1);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('enduser_property.date_acquired', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                } elseif ($startDate) {
                    $query->where('enduser_property.date_acquired', '>=', $startDate . ' 00:00:00');
                } elseif ($endDate) {
                    $query->where('enduser_property.date_acquired', '<=', $endDate . ' 23:59:59');
                }
            });

        if ($exists){
            $paritems->where('offices.camp_id', $ucampid);
        } 
        
        $paritems = $paritems->get();

        $enduser = "N/A";        

        if (!empty($request->person_accnt1)) {
            $accountable = Accountable::find($request->person_accnt1);
            if ($accountable) {
                $enduser = $accountable->person_accnt;
            }
        }

        if($paritems->isNotEmpty()){
            $pdf = PDF::loadView('reports.par_option_reportsGen', compact('selectedItem', 'paritems', 'itemId', 'pAccountable', 'enduser', 'datereport', 'locationcolumn'))->setPaper('Legal', 'portrait');
            return $pdf->stream();
        }else{
            return redirect()->back()->with('error', 'No Item Found Belong to this End User!');
        }
        
    }

    public function genOption(Request $request) {
        $id = $request->id;
        $type = $request->type;
        $pAccountable = $request->pAccountable;

        if ($type == 'campus') {
            $userAccountable = Accountable::where('off_id', $id)
                ->select('person_accnt', 'id')
                ->get();
 
            $officeAccountable = Office::where('id', $id)
                ->select('office_officer', 'id')
                ->get();

            $options = "";
            foreach ($userAccountable as $accnt) {
                $options .= "<option value='".$accnt->id."' data-person-cat='accountable' data-account-id='".$accnt->id."'>".$accnt->person_accnt."</option>";
            }
            foreach ($officeAccountable as $officeAccount) {
                $options .= "<option value='".$officeAccount->id."' data-person-cat='officeAccountable' data-account-id='".$officeAccount->id."'>".$officeAccount->office_officer."- Office Head</option>";
            }


        } else {
            if($pAccountable == 'officeAccountable'){
                $itempar = EnduserProperty::join('items', 'items.id', '=', 'enduser_property.item_id')
                ->select('enduser_property.*', 'items.*', 'enduser_property.id as pid')
                ->where('office_id', $id)
                ->get();
            }else{
                 $itempar = EnduserProperty::where('person_accnt', $id)
                ->join('items', 'items.id', '=', 'enduser_property.item_id')
                ->select('enduser_property.*', 'items.*', 'enduser_property.id as pid')
                ->get();
            }

            $options = "";
            $options .= "<option>All</option>";
            foreach ($itempar as $parItem) {
                $options .= "<option value='".$parItem->pid."'>".$parItem->item_name.' '.$parItem->item_descrip."</option>";
            }
        }

        return response()->json([
            "options" => $options,
        ]);
    }
    
    public function allgenOption(Request $request) {
        $id = $request->id;
        $type = $request->type;
        $properties_id = $request->properties_id;
        $startDate = $request->start_date_acquired;
        $endDate = $request->end_date_acquired;

        if ($properties_id == 'ics') {
            $propertiesid = [1, 2];
        } elseif ($properties_id == 'par') {
            $propertiesid = [3];
        } else {
            $propertiesid = [1, 2, 3];
        }

        $categoriesId = $request->category;
        $propId = $request->accnt_title;
        $selaccnt = $request->selected_account_id;

        $condcategories = ($categoriesId == 'All') ? '!=' : '=';
        $categoriesId = ($categoriesId == 'All') ? '0' : $categoriesId;

        $condpropid = ($propId == 'All') ? '!=' : '=';
        $propId = ($propId == 'All') ? '0' : $propId;

        $condselaccnt = ($selaccnt == 'All') ? '!=' : '=';
        $selaccnt = ($selaccnt == 'All') ? '0' : $selaccnt;

        $options = "";  
        $options1 = "";

        if ($type == 'campus') {
            $office = Office::find($id);
            
            $endusers = "<option value=''>None</option>";

            if ($office->camp_id == '') { 

                $userAccountable = Accountable::where('off_id', $id)->get();
                $userDesignate = Accountable::whereJsonContains('desig_offid', (string)$id)->get();

                // ACCOUNTABLE
                $options  = "<option value=''>Select Accountable</option>";

                // END USER
                // $options1 = "<option value=''>Select End User</option>";

                // Keep track of added IDs to avoid duplicates in $options
                $addedAccountables = [];

                // Add accountables first
                foreach ($userAccountable as $accnt) {
                    $roleLabel = ($accnt->accnt_role == 1) ? ' - HEAD' : '';

                    $options .= "<option value='{$accnt->id}'
                                    data-person-cat='accountable'
                                    data-account-id='{$accnt->id}'>
                                    {$accnt->person_accnt}{$roleLabel}
                                </option>";

                    $addedAccountables[] = $accnt->id;

                    // END USER (exclude roles 1,2,3)
                    if (!in_array($accnt->accnt_role, [1,2,3])) {
                        $options1 .= "<option value='{$accnt->id}'
                                        data-person-cat='enduser'
                                        data-account-id='{$accnt->id}'>
                                        {$accnt->person_accnt}
                                    </option>";
                    }
                }

                // Add designates to ACCOUNTABLE dropdown if not already included
                foreach ($userDesignate as $desig) {
                    if (!in_array($desig->id, $addedAccountables)) {
                        $options .= "<option value='{$desig->id}'
                                        data-person-cat='accountable'
                                        data-account-id='{$desig->id}'>
                                        {$desig->person_accnt}
                                    </option>";
                        $addedAccountables[] = $desig->id;
                    }
                }
            } else {
                /* ================= ACCOUNTABLE ================= */

                $custodians = Accountable::where('off_id', $id)
                    ->where('accnt_role', 2)
                    ->get();

                $options = "<option value=''>Select Accountable</option>";

                foreach ($custodians as $accnt) {
                    $options .= "<option value='{$accnt->id}'
                                    data-person-cat='accountable'
                                    data-account-id='{$accnt->id}'>
                                    {$accnt->person_accnt} - CUSTODIAN
                                </option>";
                }

                /* ================= END USER (SAME LOGIC) ================= */

                $endUsersAccountable = Accountable::where('off_id', $id)->get();

                // $options1 = "<option value=''>Select End User</option>";

                foreach ($endUsersAccountable as $accnt) {

                    // SAME filter: NOT IN (1,2,3)
                    if (!in_array($accnt->accnt_role, [1, 2, 3])) {
                        $options1 .= "<option value='{$accnt->id}'
                                        data-person-cat='enduser'
                                        data-account-id='{$accnt->id}'>
                                        {$accnt->person_accnt}
                                    </option>";
                    }
                }
            }

            // Get the campus location options
            $officeloc = Office::find($id);
            $locations = Office::where('office_code', '0000')
                ->when($officeloc->camp_id, function($query) use ($officeloc) {
                    return $query->where('loc_camp', $officeloc->camp_id);
                }, function($query) {
                    return $query->where('loc_camp', 1)
                        ->where(function($q) {
                            $q->whereNull('camp_id')
                            ->orWhere('camp_id', '');
                        });
                })
                ->get();

            $locoptions = "<option value='All' selected>All Locations</option>";
            foreach ($locations as $location) {
                $locoptions .= "<option value='" . $location->id . "'>" . htmlspecialchars($location->office_name) . "</option>";
            }
        } else {
            $itempar = EnduserProperty::query()
                ->where(function ($q) use ($id) {
                    $q->where('person_accnt', $id)
                    ->orWhere('person_accnt1', $id);
                })
                ->join('items', 'items.id', '=', 'enduser_property.item_id')
                ->select('enduser_property.*', 'items.*', 'enduser_property.id as pid')
                ->when($properties_id == 'ics', function ($query) {
                    return $query->whereIn('enduser_property.properties_id', [1, 2]);
                })
                ->when($properties_id == 'par', function ($query) {
                    return $query->where('enduser_property.properties_id', 3)
                                ->where('enduser_property.item_cost', '>=', 50000);
                })
                ->where('enduser_property.categories_id', $condcategories, $categoriesId)
                ->where('enduser_property.property_id', $condpropid, $propId)
                ->when($startDate || $endDate, function ($query) use ($startDate, $endDate) {
                    if ($startDate && $endDate) {
                        $query->whereBetween('enduser_property.date_acquired', [
                            $startDate . ' 00:00:00', 
                            $endDate   . ' 23:59:59'
                        ]);
                    } elseif ($startDate) {
                        $query->where('enduser_property.date_acquired', '>=', $startDate . ' 00:00:00');
                    } elseif ($endDate) {
                        $query->where('enduser_property.date_acquired', '<=', $endDate . ' 23:59:59');
                    }
                })
                ->where('enduser_property.deleted', 0)
                ->get();

            $options = "<option value=''>Select Items</option>";
            $options = "<option value='All'>All Items</option>";
            foreach ($itempar as $icsItem) {
                $options .= "<option value='" . $icsItem->pid . "'>"
                    . $icsItem->item_name . ' ' 
                    . $icsItem->item_descrip 
                    . ' (Acquired: ' . date('Y-m-d', strtotime($icsItem->date_acquired)) . ")"
                    . "</option>";
            }

            // For non-campus type, return empty location options
            $locoptions = "<option value='All' selected>All Locations</option>";
            $endusers = "<option value=''>None</option>";
        }

        return response()->json([
            "options" => $options,
            "options1" => $options1,
            "endusers" => $endusers,
            "location" => $locoptions,
        ]);
    }
}

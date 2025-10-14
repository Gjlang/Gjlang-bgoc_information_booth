<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\ItemsExport;
use Maatwebsite\Excel\Facades\Excel;

class ItemDashboardController extends Controller
{
    // ---------- Utilities ----------
    /** Convert "undefined" / "" to null for all known filters */
    protected function cleanFilters(Request $request): array
    {
        $raw = $request->only([
            'date_in_from','date_in_to','deadline_from','deadline_to',
            'assign_by_id','assign_to_id','type_label','company_id',
            'pic_name','product_id','status',
        ]);

        return collect($raw)->map(function ($v) {
            return ($v === 'undefined' || $v === '') ? null : $v;
        })->all();
    }

    /** Base query - NO MORE JOINS since company_id and product_id are now strings */
    protected function baseQuery(array $filters)
    {
        $q = Item::query()->select('items.*');

        if (!empty($filters['date_in_from']))  $q->whereDate('items.date_in', '>=', $filters['date_in_from']);
        if (!empty($filters['date_in_to']))    $q->whereDate('items.date_in', '<=', $filters['date_in_to']);
        if (!empty($filters['deadline_from'])) $q->whereDate('items.deadline','>=', $filters['deadline_from']);
        if (!empty($filters['deadline_to']))   $q->whereDate('items.deadline','<=', $filters['deadline_to']);

        foreach (['assign_by_id','assign_to_id','type_label','company_id','pic_name','product_id','status'] as $f) {
            if (!empty($filters[$f])) {
                $f === 'pic_name'
                    ? $q->where("items.$f", 'like', '%'.$filters[$f].'%')
                    : $q->where("items.$f", $filters[$f]);
            }
        }

        return $q;
    }

    // ---------- Pages ----------
    public function index(Request $request)
    {
        // Distincts for filters
        $distinct = [
            'type_labels' => Item::whereNotNull('type_label')->distinct()->orderBy('type_label')->pluck('type_label'),
            'statuses'    => Item::whereNotNull('status')->distinct()->orderBy('status')->pluck('status'),
            'assign_by'   => Item::whereNotNull('assign_by_id')->distinct()->orderBy('assign_by_id')->pluck('assign_by_id'),
            'companies'   => Item::whereNotNull('company_id')->distinct()->orderBy('company_id')->pluck('company_id'),
            'pic_names'   => Item::whereNotNull('pic_name')->distinct()->orderBy('pic_name')->pluck('pic_name'),
            'products'    => Item::whereNotNull('product_id')->distinct()->orderBy('product_id')->pluck('product_id'),
        ];

        return view('dashboard', compact('distinct'));
    }

    // ---------- JSON endpoints ----------
    public function list(Request $request)
    {
        $filters = $this->cleanFilters($request);

        try {
            $items = $this->baseQuery($filters)
                ->orderBy('items.deadline','asc')
                ->orderBy('items.date_in','asc')
                ->limit(2000)
                ->get();

            return response()->json(['ok' => true, 'data' => $items]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function events(Request $request)
    {
        $filters = $this->cleanFilters($request);

        try {
            $rows = $this->baseQuery($filters)
                ->whereNotNull('items.deadline')
                ->orderBy('items.deadline','asc')
                ->limit(2000)
                ->get();

            $events = $rows->map(function ($row) {
                $titleParts = [
                    $row->company_id ?? '-',
                    $row->product_id ?? '-',
                    $row->pic_name   ?? '-',
                    $row->status     ?? '-',
                ];
                return [
                    'id'     => $row->id,
                    'title'  => implode(' | ', array_filter($titleParts, fn($v) => $v !== null && $v !== '' && $v !== '-')),
                    'start'  => $row->deadline,
                    'allDay' => true,
                ];
            })->values();

            return response()->json($events);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // FIXED: Changed to string validation
        $data = $request->validate([
            'date_in'      => 'nullable|date',
            'deadline'     => 'nullable|date',
            'assign_by_id' => 'nullable|string|max:255',
            'assign_to_id' => 'nullable|string|max:255',
            'type_label'   => 'nullable|string|max:255',
            'company_id'   => 'nullable|string|max:255',
            'pic_name'     => 'nullable|string|max:255',
            'product_id'   => 'nullable|string|max:255',
            'status'       => 'required|string|max:255',
            'remarks'      => 'nullable|string',
        ]);

        try {
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            $item = Item::create($data);

            return response()->json(['ok' => true, 'message' => 'Item created', 'data' => $item], 201);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $item = Item::where('id', $id)->firstOrFail();
            return response()->json($item);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 404);
        }
    }
}

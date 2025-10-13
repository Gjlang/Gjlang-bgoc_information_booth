<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemDashboardController extends Controller
{
    public function index(Request $request)
    {
        // For initial filter dropdowns we’ll pull distinct values from items.
        // If you have Users/Companies/Products tables, you can hydrate with proper names.
        $distinct = [
            'type_labels' => Item::whereNotNull('type_label')->distinct()->pluck('type_label'),
            'statuses'    => Item::whereNotNull('status')->distinct()->pluck('status'),
            'assign_by'   => Item::whereNotNull('assign_by_id')->distinct()->pluck('assign_by_id'),
            'assign_to'   => Item::whereNotNull('assign_to_id')->distinct()->pluck('assign_to_id'),
            'companies'   => Item::whereNotNull('company_id')->distinct()->pluck('company_id'),
            'pic_names'   => Item::whereNotNull('pic_name')->distinct()->pluck('pic_name'),
            'products'    => Item::whereNotNull('product_id')->distinct()->pluck('product_id'),
        ];


        return view('dashboard', compact('distinct'));
    }

    public function list(Request $request)
    {
        $q = Item::query()
            // Left join to get names if these tables exist in your DB:
            ->leftJoin('companies', 'companies.id', '=', 'items.company_id')
            ->leftJoin('products',  'products.id',  '=', 'items.product_id')
            ->select([
                'items.*',
                DB::raw('COALESCE(companies.name, items.company_id) as company_name'),
                DB::raw('COALESCE(products.name, items.product_id)  as product_name'),
            ]);

        // Filters
        if ($request->filled('date_in_from'))  $q->whereDate('items.date_in', '>=', $request->date_in_from);
        if ($request->filled('date_in_to'))    $q->whereDate('items.date_in', '<=', $request->date_in_to);
        if ($request->filled('deadline_from')) $q->whereDate('items.deadline','>=', $request->deadline_from);
        if ($request->filled('deadline_to'))   $q->whereDate('items.deadline','<=', $request->deadline_to);

        foreach ([
            'assign_by_id','assign_to_id','type_label','company_id',
            'pic_name','product_id','status'
        ] as $f) {
            if ($request->filled($f)) $q->where("items.$f", $request->$f);
        }

        // Order newest first by deadline, then date_in as tie-breaker
        $items = $q->orderBy('items.deadline','asc')
                   ->orderBy('items.date_in','asc')
                   ->limit(2000) // keep client payload reasonable
                   ->get();

        return response()->json(['data' => $items]);
    }

    public function events(Request $request)
    {
        // Re-use same filters so calendar matches the table (nice!)
        $req = Request::create(route('items.list'), 'GET', $request->all());
        $res = app()->handle($req);
        $payload = json_decode($res->getContent(), true);

        $events = collect($payload['data'])->filter(function($row) {
            return !empty($row['deadline']);
        })->map(function($row) {
            $titleParts = [
                $row['company_name'] ?? $row['company_id'],
                $row['product_name'] ?? $row['product_id'],
                $row['pic_name']     ?? '-',
                $row['status']       ?? '-',
            ];
            return [
                'id'    => $row['id'],
                'title' => implode(' | ', $titleParts),
                'start' => $row['deadline'], // single-day marker
                'allDay'=> true,
            ];
        })->values();

        return response()->json($events);
    }

    public function show($id)
    {
        $item = Item::leftJoin('companies', 'companies.id', '=', 'items.company_id')
            ->leftJoin('products',  'products.id',  '=', 'items.product_id')
            ->select([
                'items.*',
                DB::raw('COALESCE(companies.name, items.company_id) as company_name'),
                DB::raw('COALESCE(products.name, items.product_id)  as product_name'),
            ])
            ->where('items.id', $id)
            ->firstOrFail();

        return response()->json($item);
    }
}

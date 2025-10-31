<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\ItemsExport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Cache;

class ItemDashboardController extends Controller
{
    use AuthorizesRequests;

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

    // 🔴 Special handling for virtual "Expired"
    if (!empty($filters['status']) && strcasecmp($filters['status'], 'Expired') === 0) {
        $q->where(function ($qq) {
            $qq->whereDate('items.deadline', '<', now()->toDateString())
               ->whereNotIn('items.status', ['Completed','Done','Cancelled','Expired'])
               ->orWhere('items.status', 'Expired'); // real expired if someone set it
        });
        unset($filters['status']); // prevent the default equality filter below
    }

    foreach (['assign_by_id','assign_to_id','type_label','company_id','pic_name','product_id','status'] as $f) {
        if (!empty($filters[$f])) {
            $f === 'pic_name'
                ? $q->where("items.$f", 'like', '%'.$filters[$f].'%')
                : $q->where("items.$f", $filters[$f]);
        }
    }

    // ✅ Expose a UI status that matches your frontend logic
    $q->addSelect(DB::raw("
        CASE
          WHEN items.deadline IS NOT NULL
           AND DATE(items.deadline) < CURDATE()
           AND items.status NOT IN ('Completed','Done','Cancelled','Expired')
          THEN 'Expired'
          ELSE items.status
        END AS status_ui
    "));

    return $q;
}



  public function index(Request $request)
{
    // Authorization: anyone logged in can view
    $this->authorize('viewAny', Item::class);

    // Distinct Assign By
    $assignBy = Item::query()
        ->select('assign_by_id')
        ->whereNotNull('assign_by_id')->where('assign_by_id', '!=', '')
        ->groupBy('assign_by_id')
        ->orderBy('assign_by_id')
        ->pluck('assign_by_id');

    // ✅ NEW: Distinct Assign To
    $assignTo = Item::query()
        ->select('assign_to_id')
        ->whereNotNull('assign_to_id')->where('assign_to_id', '!=', '')
        ->groupBy('assign_to_id')
        ->orderBy('assign_to_id')
        ->pluck('assign_to_id');

    // Distinct Internal/Client
    $typeLabels = Item::query()
        ->select('type_label')
        ->whereNotNull('type_label')->where('type_label', '!=', '')
        ->groupBy('type_label')
        ->orderBy('type_label')
        ->pluck('type_label');

    // Distinct Company
    $companies = Item::query()
        ->select('company_id')
        ->whereNotNull('company_id')->where('company_id', '!=', '')
        ->groupBy('company_id')
        ->orderBy('company_id')
        ->pluck('company_id');

    // Distinct PIC
    $picNames = Item::query()
        ->select('pic_name')
        ->whereNotNull('pic_name')->where('pic_name', '!=', '')
        ->groupBy('pic_name')
        ->orderBy('pic_name')
        ->pluck('pic_name');

    // Distinct Product
    $products = Item::query()
        ->select('product_id')
        ->whereNotNull('product_id')->where('product_id', '!=', '')
        ->groupBy('product_id')
        ->orderBy('product_id')
        ->pluck('product_id');

    // Status options
    $statuses = collect(['Pending', 'In Progress', 'Completed']);

    // Combine everything for the Blade view
    $distinct = [
        'assign_by'   => $assignBy,
        'assign_to'   => $assignTo, // ✅ fixed variable name
        'type_labels' => $typeLabels,
        'companies'   => $companies,
        'pic_names'   => $picNames,
        'products'    => $products,
        'statuses'    => $statuses,
    ];

    return view('dashboard', compact('distinct'));
}


    public function list(Request $request)
    {
        $this->authorize('viewAny', Item::class);

        $filters = $this->cleanFilters($request);

        try {
            $items = $this->baseQuery($filters)
                ->orderBy('items.deadline','asc')
                ->orderBy('items.date_in','asc')
                ->limit(2000)
                ->get();

            $user = $request->user();

            $data = $items->map(function ($i) use ($user) {
                return [
                    'id'           => $i->id,
                    'date_in'      => $i->date_in,
                    'deadline'     => $i->deadline,
                    'assign_by_id' => $i->assign_by_id,
                    'assign_to_id' => $i->assign_to_id,
                    'type_label'   => $i->type_label,
                    'company_id'   => $i->company_id,
                    'pic_name'     => $i->pic_name,
                    'product_id'   => $i->product_id,
                    'status' => $i->status_ui ?? $i->status,
                    'remarks'      => $i->remarks,
                    'task'         => $i->task,

                    'can_update'   => $user?->can('update', $i) ?? false,
                    'can_delete'   => $user?->can('delete', $i) ?? false,
                ];
            });

            return response()->json(['ok' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

 public function export(Request $request)
{
    $this->authorize('export', Item::class);

    try {
        $filters = method_exists($this, 'cleanFilters')
            ? $this->cleanFilters($request)
            : $request->only([
                'date_in_from',
                'deadline_from',
                'assign_by_id',
                'assign_to_id',
                'company_id',
                'pic_name',
                'product_id',
                'task',
                'remarks',
                'type_label',
                'status',
            ]);

        $filename = 'Info_Hub_Status_' . now()->format('Ymd_His') . '.xlsx';

        // ✅ SOLUSI: Download langsung tanpa temp file
        return Excel::download(
            new ItemsExport($filters),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );

    } catch (\Throwable $e) {
        report($e);
        return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
    }
}
    public function store(Request $request)
    {
        $this->authorize('create', Item::class);

        $data = $request->validate([
            'date_in'      => 'nullable|date',
            'deadline'     => 'nullable|date',
            'assign_by_id' => 'nullable|string|max:255',
            'assign_to_id' => 'nullable|string|max:255',
            'type_label'   => 'nullable|string|max:255',
            'company_id'   => 'nullable|string|max:255',
            'task'         => 'nullable|string|max:255',
            'pic_name'     => 'nullable|string|max:255',
            'product_id'   => 'nullable|string|max:255',
            'status'       => 'nullable|string|max:255',
            'remarks'      => 'nullable|string',
        ]);

        $item = new Item($data);
        $item->created_by = Auth::id();
        $item->updated_by = Auth::id();
        $item->save();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return response()->json([
            'ok'   => true,
            'data' => array_merge($item->toArray(), [
                'can_update' => $user?->can('update', $item) ?? false,
                'can_delete' => $user?->can('delete', $item) ?? false,
            ]),
        ]);
    }

    public function editPayload($id)
    {
        $item = Item::findOrFail($id);
        $this->authorize('update', $item); // <-- blokir non-pemilik

        // Jika ada data tambahan untuk form (dropdown dsb), kirim di sini.
        return response()->json([
            'ok'   => true,
            'data' => $item,
        ]);
    }

    public function show($id)
    {
        $item = Item::findOrFail($id);
        $this->authorize('view', $item);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return response()->json(array_merge($item->toArray(), [
            'can_update' => $user?->can('update', $item) ?? false,
            'can_delete' => $user?->can('delete', $item) ?? false,
            'can_open_edit'=> $user?->can('update', $item) ?? false,
        ]));
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $this->authorize('update', $item);

        $data = $request->validate([
            'date_in'      => 'nullable|date',
            'deadline'     => 'nullable|date',
            'assign_by_id' => 'nullable|string|max:255',
            'assign_to_id' => 'nullable|string|max:255',
            'type_label'   => 'nullable|string|max:255',
            'company_id'   => 'nullable|string|max:255',
            'task'         => 'nullable|string|max:255',
            'pic_name'     => 'nullable|string|max:255',
            'product_id'   => 'nullable|string|max:255',
            'status'       => 'nullable|string|max:255',
            'remarks'      => 'nullable|string',
        ]);

        $item->fill($data);
        $item->updated_by = Auth::id();
        $item->save();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return response()->json([
            'ok'   => true,
            'data' => array_merge($item->toArray(), [
                'can_update' => $user?->can('update', $item) ?? false,
                'can_delete' => $user?->can('delete', $item) ?? false,
            ]),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $this->authorize('update', $item);

        $data = $request->validate([
            'status' => ['required', Rule::in(['Pending','In Progress','Completed'])],
        ]);

        $item->status = $data['status'];
        $item->updated_by = Auth::id();
        $item->save();

        return response()->json([
            'ok' => true,
            'id' => (int)$id,
            'status' => $item->status,
        ]);
    }

    public function destroy(int $id)
    {
        $item = Item::findOrFail($id);
        $this->authorize('delete', $item);

        $item->delete();

        return response()->json(['ok' => true, 'deleted_id' => $id]);
    }

    public function events(Request $request)
    {
        $this->authorize('viewAny', Item::class);

        $filters = $this->cleanFilters($request);

        try {
            $rows = $this->baseQuery($filters)
                ->whereNotNull('items.deadline')
                ->orderBy('items.deadline','asc')
                ->limit(2000)
                ->get();

            $events = $rows->map(function ($row) {
                $titleParts = [
                    $row->assign_to_id ?? '-',
                    $row->task         ?? '-',
                    $row->company_id   ?? '-',
                    $row->status       ?? '-',

                ];
                return [
                    'id'     => $row->id,
                    'title'  => implode(' | ', array_filter($titleParts, fn($v) => $v !== null && $v !== '' && $v !== '-')),
                    'start'  => $row->deadline,
                    'status' => $row->status_ui ?? $row->status,
                    'allDay' => true,
                ];
            })->values();

            return response()->json($events);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

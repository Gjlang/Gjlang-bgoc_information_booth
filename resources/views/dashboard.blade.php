@extends('layouts.app')

@section('head')
<title>Items Dashboard</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

{{-- FullCalendar v5.11.3 CSS (only if not already included) --}}
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

<style>
  :root{
    --paper:#F7F7F9; --surface:#FFFFFF; --ink:#1C1E26; --hair:#EAEAEA;
    --brand:#22255b; --brand2:#4bbbed; --danger:#d33831; --muted:#6B7280;
  }
  body{ background:var(--paper); color:var(--ink); font-family:Inter,system-ui; }
  .card{ background:var(--surface); border:1px solid var(--hair); border-radius:14px; }
  .badge{ display:inline-flex; align-items:center; padding:.125rem .5rem; font-size:.75rem;
          border-radius:9999px; border:1px solid var(--hair); }
  .badge.Pending{ background:#fff7ed; border-color:#fdba74; }
  .badge.In\ Progress{ background:#eff6ff; border-color:#93c5fd; }
  .badge.Done{ background:#ecfdf5; border-color:#86efac; }
  .badge.Hold,.badge.Blocked{ background:#fef2f2; border-color:#fecaca; }
  .table-sticky thead th{ position:sticky; top:0; background:var(--surface); z-index:10; }
  .row-hover:hover{ background:#fafafa; }
  .modal{ display:none; position:fixed; inset:0; background:rgba(0,0,0,.35); z-index:50; }
  .modal.show{ display:flex; align-items:center; justify-content:center; }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- Filters --}}
  <div class="card p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-3">
      <div>
        <label class="text-xs text-gray-500">DATE IN (From)</label>
        <input type="date" id="f_date_in_from" class="w-full border rounded-md px-2 py-1">
      </div>
      <div>
        <label class="text-xs text-gray-500">DATE IN (To)</label>
        <input type="date" id="f_date_in_to" class="w-full border rounded-md px-2 py-1">
      </div>
      <div>
        <label class="text-xs text-gray-500">DEADLINE (From)</label>
        <input type="date" id="f_deadline_from" class="w-full border rounded-md px-2 py-1">
      </div>
      <div>
        <label class="text-xs text-gray-500">DEADLINE (To)</label>
        <input type="date" id="f_deadline_to" class="w-full border rounded-md px-2 py-1">
      </div>

      <div>
        <label class="text-xs text-gray-500">ASSIGN BY (ID)</label>
        <select id="f_assign_by_id" class="w-full border rounded-md px-2 py-1">
          <option value="">All</option>
          @foreach($distinct['assign_by'] as $v)
            <option value="{{ $v }}">{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs text-gray-500">ASSIGN TO (ID)</label>
        <select id="f_assign_to_id" class="w-full border rounded-md px-2 py-1">
          <option value="">All</option>
          @foreach($distinct['assign_to'] as $v)
            <option value="{{ $v }}">{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs text-gray-500">INTERNAL/CLIENT</label>
        <select id="f_type_label" class="w-full border rounded-md px-2 py-1">
          <option value="">All</option>
          @foreach($distinct['type_labels'] as $v)
            <option value="{{ $v }}">{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs text-gray-500">COMPANY (ID)</label>
        <select id="f_company_id" class="w-full border rounded-md px-2 py-1">
          <option value="">All</option>
          @foreach($distinct['companies'] as $v)
            <option value="{{ $v }}">{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs text-gray-500">PIC</label>
        <select id="f_pic_name" class="w-full border rounded-md px-2 py-1">
          <option value="">All</option>
          @foreach($distinct['pic_names'] as $v)
            <option value="{{ $v }}">{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs text-gray-500">PRODUCT (ID)</label>
        <select id="f_product_id" class="w-full border rounded-md px-2 py-1">
          <option value="">All</option>
          @foreach($distinct['products'] as $v)
            <option value="{{ $v }}">{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs text-gray-500">STATUS</label>
        <select id="f_status" class="w-full border rounded-md px-2 py-1">
          <option value="">All</option>
          @foreach($distinct['statuses'] as $v)
            <option value="{{ $v }}">{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex items-end gap-2">
        <button id="btnApply" class="px-3 py-2 rounded-md bg-[var(--brand)] text-white">Apply</button>
        <button id="btnClear" class="px-3 py-2 rounded-md border">Clear</button>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="card p-4">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-semibold">Tasks</h2>
      <div class="text-xs text-gray-500">Auto-rotates 7 rows every 5s • Auto-refresh 30s</div>
    </div>

    <div class="overflow-x-auto table-sticky">
      <table class="min-w-full text-sm">
        <thead class="border-b">
          <tr class="text-left">
            <th class="py-2 px-2">DATE IN</th>
            <th class="py-2 px-2">DEADLINE</th>
            <th class="py-2 px-2">ASSIGN BY</th>
            <th class="py-2 px-2">ASSIGN TO</th>
            <th class="py-2 px-2">INTERNAL/CLIENT</th>
            <th class="py-2 px-2">COMPANY</th>
            <th class="py-2 px-2">PIC</th>
            <th class="py-2 px-2">PRODUCT</th>
            <th class="py-2 px-2">STATUS</th>
            <th class="py-2 px-2">REMARKS</th>
          </tr>
        </thead>
        <tbody id="rows" class="divide-y"></tbody>
      </table>
    </div>
  </div>

  {{-- Calendar --}}
  <div class="card p-4">
    <h2 class="font-semibold mb-3">Calendar (Deadlines)</h2>
    <div id="calendar"></div>
  </div>
</div>


{{-- jQuery (if not already loaded) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
{{-- FullCalendar v5.11.3 JS (if not already loaded) --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
(function(){
  const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // State
  let allData = [];      // full filtered dataset from server
  let windowStart = 0;   // rotating window index
  const WINDOW_SIZE = 7;
  let rotateTimer = null;
  let refreshTimer = null;

  // Helpers
  function hasAnyFilter(){
    return [
      '#f_date_in_from','#f_date_in_to','#f_deadline_from','#f_deadline_to',
      '#f_assign_by_id','#f_assign_to_id','#f_type_label','#f_company_id',
      '#f_pic_name','#f_product_id','#f_status'
    ].some(sel => $(sel).val());
  }

  function statusBadge(status){
    if(!status) return '<span class="badge">-</span>';
    const safe = String(status).replace(/\s/g,'\\ ');
    return `<span class="badge ${safe}">${status}</span>`;
  }

  function fmt(d){
    if(!d) return '-';
    try{
      const dt = new Date(d);
      if(Number.isNaN(dt.getTime())) return d;
      return dt.toISOString().slice(0,10);
    }catch(e){ return d; }
  }

  function rowHtml(r){
    return `
      <tr class="row-hover cursor-pointer" data-id="${r.id}">
        <td class="py-2 px-2">${fmt(r.date_in)}</td>
        <td class="py-2 px-2">${fmt(r.deadline)}</td>
        <td class="py-2 px-2">${r.assign_by_id ?? '-'}</td>
        <td class="py-2 px-2">${r.assign_to_id ?? '-'}</td>
        <td class="py-2 px-2">${r.type_label ?? '-'}</td>
        <td class="py-2 px-2">${r.company_name ?? r.company_id ?? '-'}</td>
        <td class="py-2 px-2">${r.pic_name ?? '-'}</td>
        <td class="py-2 px-2">${r.product_name ?? r.product_id ?? '-'}</td>
        <td class="py-2 px-2">${statusBadge(r.status)}</td>
        <td class="py-2 px-2 truncate max-w-[200px]" title="${(r.remarks||'').replace(/"/g,'&quot;')}">${r.remarks ?? ''}</td>
      </tr>
    `;
  }

  function renderWindow(){
    const tbody = $('#rows');
    tbody.empty();

    if(allData.length === 0){
      tbody.append(`<tr><td class="py-6 px-2 text-center text-muted" colspan="10">No data</td></tr>`);
      return;
    }

    // If filters applied, show first 7 only (no rotation while filtered)
    let start = hasAnyFilter() ? 0 : windowStart;
    const end = Math.min(start + WINDOW_SIZE, allData.length);
    const slice = allData.slice(start, end);

    slice.forEach(r => tbody.append(rowHtml(r)));
  }

  function rotate(){
    if(hasAnyFilter() || allData.length <= WINDOW_SIZE) return; // paused or not enough to rotate
    windowStart = (windowStart + WINDOW_SIZE) % allData.length;
    renderWindow();
  }

  function getFilters(){
    return {
      date_in_from:  $('#f_date_in_from').val(),
      date_in_to:    $('#f_date_in_to').val(),
      deadline_from: $('#f_deadline_from').val(),
      deadline_to:   $('#f_deadline_to').val(),
      assign_by_id:  $('#f_assign_by_id').val(),
      assign_to_id:  $('#f_assign_to_id').val(),
      type_label:    $('#f_type_label').val(),
      company_id:    $('#f_company_id').val(),
      pic_name:      $('#f_pic_name').val(),
      product_id:    $('#f_product_id').val(),
      status:        $('#f_status').val(),
    };
  }

  function fetchData({resetWindow=true} = {}){
    const params = new URLSearchParams(getFilters()).toString();
    return fetch(`{{ route('items.list') }}?` + params, {
      headers: { 'X-CSRF-TOKEN': CSRF }
    })
      .then(r => r.json())
      .then(j => {
        allData = j.data || [];
        if(resetWindow) windowStart = 0;
        renderWindow();
        if(calendar){ // refresh events to match filters
          calendar.refetchEvents();
        }
      })
      .catch(console.error);
  }

  // Rotation timer (5s)
  function startRotation(){
    if(rotateTimer) clearInterval(rotateTimer);
    rotateTimer = setInterval(rotate, 5000);
  }
  // Auto-refresh (30s)
  function startAutoRefresh(){
    if(refreshTimer) clearInterval(refreshTimer);
    refreshTimer = setInterval(()=>fetchData({resetWindow:false}), 30000);
  }

  // Event delegation: row click -> open modal
  $('#rows').on('click', 'tr[data-id]', function(){
    const id = $(this).data('id');
    openDetail(id);
  });

  // Modal
  const $modal = $('#detailModal');
  $('#modalClose').on('click', ()=> $modal.removeClass('show'));
  $modal.on('click', (e)=>{ if(e.target === $modal[0]) $modal.removeClass('show'); });

  function openDetail(id){
    fetch(`{{ route('items.show', ['id'=>'__ID__']) }}`.replace('__ID__', id), {
      headers:{ 'X-CSRF-TOKEN': CSRF }
    })
      .then(r=>r.json())
      .then(d=>{
        const html = `
          <div><div class="text-xs text-gray-500">ID</div><div>${d.id}</div></div>
          <div><div class="text-xs text-gray-500">DATE IN</div><div>${fmt(d.date_in)}</div></div>
          <div><div class="text-xs text-gray-500">DEADLINE</div><div>${fmt(d.deadline)}</div></div>
          <div><div class="text-xs text-gray-500">ASSIGN BY</div><div>${d.assign_by_id ?? '-'}</div></div>
          <div><div class="text-xs text-gray-500">ASSIGN TO</div><div>${d.assign_to_id ?? '-'}</div></div>
          <div><div class="text-xs text-gray-500">TYPE</div><div>${d.type_label ?? '-'}</div></div>
          <div><div class="text-xs text-gray-500">COMPANY</div><div>${d.company_name ?? d.company_id ?? '-'}</div></div>
          <div><div class="text-xs text-gray-500">PIC</div><div>${d.pic_name ?? '-'}</div></div>
          <div><div class="text-xs text-gray-500">PRODUCT</div><div>${d.product_name ?? d.product_id ?? '-'}</div></div>
          <div class="col-span-2"><div class="text-xs text-gray-500">STATUS</div><div>${statusBadge(d.status)}</div></div>
          <div class="col-span-2"><div class="text-xs text-gray-500">REMARKS</div><div>${d.remarks ?? ''}</div></div>
        `;
        $('#detailBody').html(html);
        $modal.addClass('show');
      })
      .catch(console.error);
  }

  // Filters: apply/clear
  $('#btnApply').on('click', async ()=>{
    await fetchData();
    // pause rotation while filtered
    renderWindow();
  });
  $('#btnClear').on('click', async ()=>{
    $('#f_date_in_from,#f_date_in_to,#f_deadline_from,#f_deadline_to,#f_assign_by_id,#f_assign_to_id,#f_type_label,#f_company_id,#f_pic_name,#f_product_id,#f_status').val('');
    await fetchData();
  });

  // Also pause/resume rotation immediately when filter values change
  $('select,input[type="date"]').on('change', ()=> {
    renderWindow();
  });

  // Calendar
  let calendar;
  function initCalendar(){
    const el = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(el, {
      initialView: 'dayGridMonth',
      height: 'auto',
      eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
      events: (info, success, failure) => {
        const params = new URLSearchParams(getFilters()).toString();
        fetch(`{{ route('items.events') }}?` + params, { headers:{ 'X-CSRF-TOKEN': CSRF }})
          .then(r=>r.json())
          .then(data=>success(data))
          .catch(err=>failure(err));
      },
      eventClick: function(info){
        const id = info.event.id;
        if(id) openDetail(id);
      }
    });
    calendar.render();
  }

  // Boot
  (async function boot(){
    initCalendar();
    await fetchData();
    startRotation();
    startAutoRefresh();
  })();

})();
</script>
@endsection

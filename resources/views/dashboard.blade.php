@extends('layouts.app')

@section('head')
<title>Items Dashboard</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

{{-- FullCalendar v5.11.3 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

<style>
  :root{
    --paper:#F7F7F9;
    --surface:#FFFFFF;
    --ink:#1C1E26;
    --hair:#EAEAEA;
    --brand:#22255b;
    --brand2:#4bbbed;
    --danger:#d33831;
    --muted:#6B7280;
  }

  body{
    background:var(--paper);
    color:var(--ink);
    font-family:Inter,system-ui;
  }

  body.modal-open{
    overflow:hidden !important;
  }

  .card{
    background:var(--surface);
    border:1px solid var(--hair);
    border-radius:14px;
  }

  .badge{
    display:inline-flex;
    align-items:center;
    padding:.125rem .5rem;
    font-size:.75rem;
    border-radius:9999px;
    border:1px solid var(--hair);
  }

  .badge.Pending{ background:#fff7ed; border-color:#fdba74; }
  .badge.In\ Progress{ background:#eff6ff; border-color:#93c5fd; }
  .badge.Done{ background:#ecfdf5; border-color:#86efac; }
  .badge.Completed{ background:#ecfdf5; border-color:#86efac; }
  .badge.Hold,.badge.Blocked{ background:#fef2f2; border-color:#fecaca; }

  .table-sticky thead th{
    position:sticky;
    top:0;
    background:var(--surface);
    z-index:10;
  }

  .row-hover:hover{
    background:#fafafa;
    cursor:pointer;
  }

  /* MODAL STYLES - CRITICAL FOR CENTERING */
  .modal-overlay{
    display:none !important;
    position:fixed !important;
    top:0 !important;
    left:0 !important;
    right:0 !important;
    bottom:0 !important;
    background:rgba(0,0,0,.5) !important;
    z-index:9999 !important;
    align-items:center !important;
    justify-content:center !important;
    padding:1rem !important;
  }

  .modal-overlay.active{
    display:flex !important;
  }

  .modal-content{
    background:white !important;
    border-radius:1rem !important;
    box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04) !important;
    max-height:90vh !important;
    overflow-y:auto !important;
    position:relative !important;
  }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- Header with Create and Export Buttons --}}
<div class="flex justify-between items-center">
  <h1 class="text-2xl font-semibold text-gray-800">Items Dashboard</h1>
  <div class="flex gap-3">
    {{-- EXPORT BUTTON --}}
    <button
      id="btnExport"
      type="button"
      class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors duration-200 flex items-center gap-2 shadow-sm">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      Export Excel
    </button>

    {{-- CREATE BUTTON --}}
    <button
      id="btnCreate"
      type="button"
      class="bg-[var(--brand)] text-white px-4 py-2 rounded-md hover:bg-[var(--brand2)] transition-colors duration-200 flex items-center gap-2 shadow-sm">
      <span class="text-lg">+</span> Create Task
    </button>
  </div>
</div>

  {{-- Filters --}}
  <div class="card p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-3">
      <div>
        <label class="text-xs text-gray-500">DATE IN</label>
        <input type="date" id="f_date_in_from" class="w-full border rounded-md px-2 py-1">
      </div>
      <div>
        <label class="text-xs text-gray-500">ASSIGN BY</label>
        <select id="f_assign_by_id" class="w-full border rounded-md px-2 py-1">
          <option value="">All</option>
          @foreach($distinct['assign_by'] as $v)
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
        <label class="text-xs text-gray-500">COMPANY</label>
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
        <label class="text-xs text-gray-500">PRODUCT</label>
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
        <button id="btnApply" class="px-3 py-2 rounded-md bg-[var(--brand)] text-white hover:bg-[var(--brand2)] transition-colors">Apply</button>
        <button id="btnClear" class="px-3 py-2 rounded-md border hover:bg-gray-50 transition-colors">Clear</button>
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

{{-- CREATE ITEM MODAL - ALL STRING INPUTS EXCEPT DATES --}}
<div id="createModal" class="modal-overlay">
  <div class="modal-content w-full max-w-2xl p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold text-gray-800">Create New Task</h2>
      <button id="btnCloseCreate" type="button" class="text-gray-400 hover:text-gray-600 text-3xl leading-none transition-colors">&times;</button>
    </div>

    <form id="createForm">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- DATE IN - Keep as date --}}
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">Date In <span class="text-red-500">*</span></label>
          <input type="date" name="date_in" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-[var(--brand2)] focus:border-transparent">
        </div>

        {{-- DEADLINE - Keep as date --}}
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">Deadline <span class="text-red-500">*</span></label>
          <input type="date" name="deadline" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-[var(--brand2)] focus:border-transparent">
        </div>

        {{-- ASSIGN BY - Changed to text --}}
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">Assign By</label>
          <input type="text" name="assign_by_id" placeholder="Enter Assign By" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-[var(--brand2)] focus:border-transparent">
        </div>

        {{-- ASSIGN TO - Changed to text --}}
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">Assign To</label>
          <input type="text" name="assign_to_id" placeholder="Enter Assign To" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-[var(--brand2)] focus:border-transparent">
        </div>

        {{-- INTERNAL/CLIENT - Keep as dropdown --}}
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">Internal/Client</label>
          <select name="type_label" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-[var(--brand2)] focus:border-transparent">
            <option value="">Select Type</option>
            <option value="INTERNAL">INTERNAL</option>
            <option value="CLIENT">CLIENT</option>
          </select>
        </div>

        {{-- COMPANY - Changed to text --}}
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">Company</label>
          <input type="text" name="company_id" placeholder="Enter Company Name/ID" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-[var(--brand2)] focus:border-transparent">
        </div>

        {{-- PIC NAME - Already text --}}
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">PIC Name</label>
          <input type="text" name="pic_name" maxlength="150" placeholder="Enter PIC Name" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-[var(--brand2)] focus:border-transparent">
        </div>

        {{-- PRODUCT - Changed to text --}}
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">Product</label>
          <input type="text" name="product_id" placeholder="Enter Product Name/ID" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-[var(--brand2)] focus:border-transparent">
        </div>

        {{-- STATUS - Keep as dropdown --}}
        <div class="md:col-span-2">
          <label class="text-sm font-medium text-gray-700 block mb-1">Status <span class="text-red-500">*</span></label>
          <select name="status" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-[var(--brand2)] focus:border-transparent">
            <option value="">Select Status</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
          </select>
        </div>

        {{-- REMARKS - Already textarea --}}
        <div class="md:col-span-2">
          <label class="text-sm font-medium text-gray-700 block mb-1">Remarks</label>
          <textarea name="remarks" rows="3" placeholder="Enter any additional notes or remarks..." class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-[var(--brand2)] focus:border-transparent"></textarea>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button type="button" id="btnCancelCreate" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
          Cancel
        </button>
        <button type="submit" id="btnSubmitCreate" class="px-4 py-2 bg-[var(--brand)] text-white rounded-md hover:bg-[var(--brand2)] transition-colors">
          Save Task
        </button>
      </div>
    </form>
  </div>
</div>

{{-- DETAIL MODAL --}}
<div id="detailModal" class="modal-overlay">
  <div class="modal-content w-full max-w-2xl p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold text-gray-800">Task Details</h2>
      <button id="modalClose" type="button" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
    </div>
    <div id="detailBody" class="grid grid-cols-2 gap-4"></div>
  </div>
</div>
@endsection

@section('scripts')
{{-- FullCalendar v5.11.3 JS --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
$(function(){
  try {
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // State
    let allData = [];
    let windowStart = 0;
    const WINDOW_SIZE = 7;
    let rotateTimer = null;
    let refreshTimer = null;
    let calendar = null;

    // Modal helpers
    function openModal(modalId){
      const modal = document.getElementById(modalId);
      modal.classList.add('active');
      document.body.classList.add('modal-open');
      console.log('Modal opened:', modalId);
    }

    function closeModal(modalId){
      const modal = document.getElementById(modalId);
      modal.classList.remove('active');
      document.body.classList.remove('modal-open');
      console.log('Modal closed:', modalId);
    }

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
        <tr class="row-hover" data-id="${r.id}">
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

      let start = hasAnyFilter() ? 0 : windowStart;
      const end = Math.min(start + WINDOW_SIZE, allData.length);
      const slice = allData.slice(start, end);

      slice.forEach(r => tbody.append(rowHtml(r)));
    }

    function rotate(){
      if(hasAnyFilter() || allData.length <= WINDOW_SIZE) return;
      windowStart = (windowStart + WINDOW_SIZE) % allData.length;
      renderWindow();
    }
function getFilters(){
  return {
    date_in_from:  $('#f_date_in_from').val() || '',
    assign_by_id:  $('#f_assign_by_id').val() || '',
    type_label:    $('#f_type_label').val() || '',
    company_id:    $('#f_company_id').val() || '',
    pic_name:      $('#f_pic_name').val() || '',
    product_id:    $('#f_product_id').val() || '',
    status:        $('#f_status').val() || '',
  };
}

    function fetchData({resetWindow=true} = {}){
      const params = new URLSearchParams(getFilters()).toString();
      return fetch(`{{ route('dashboard.items.list') }}?` + params, {
        headers: { 'X-CSRF-TOKEN': CSRF }
      })
        .then(r => r.json())
        .then(j => {
          allData = j.data || [];
          if(resetWindow) windowStart = 0;
          renderWindow();
          if(calendar){
            calendar.refetchEvents();
          }
        })
        .catch(console.error);
    }

    function startRotation(){
      if(rotateTimer) clearInterval(rotateTimer);
      rotateTimer = setInterval(rotate, 5000);
    }

    function startAutoRefresh(){
      if(refreshTimer) clearInterval(refreshTimer);
      refreshTimer = setInterval(()=>fetchData({resetWindow:false}), 30000);
    }

    // CREATE MODAL EVENTS
    $('#btnCreate').on('click', function(e){
      e.preventDefault();
      console.log('Create button clicked');
      openModal('createModal');
    });

    $('#btnCloseCreate, #btnCancelCreate').on('click', function(e){
      e.preventDefault();
      closeModal('createModal');
      $('#createForm')[0].reset();
    });

    // Close modal when clicking backdrop
    $('#createModal').on('click', function(e){
      if(e.target.id === 'createModal'){
        closeModal('createModal');
        $('#createForm')[0].reset();
      }
    });

    // Close modal on ESC key
    $(document).on('keydown', function(e){
      if(e.key === 'Escape'){
        if($('#createModal').hasClass('active')){
          closeModal('createModal');
          $('#createForm')[0].reset();
        }
        if($('#detailModal').hasClass('active')){
          closeModal('detailModal');
        }
      }
    });

    // Handle CREATE form submission
    $('#createForm').on('submit', async function(e){
      e.preventDefault();

      const submitBtn = $('#btnSubmitCreate');
      const originalText = submitBtn.text();
      submitBtn.prop('disabled', true).text('Saving...');

      const formData = new FormData(this);

      try {
        const response = await fetch('{{ route("dashboard.items.store") }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': CSRF
          },
          body: formData
        });

        if(response.ok){
          alert('New task created successfully!');
          this.reset();
          closeModal('createModal');
          await fetchData();
        } else {
          const error = await response.json();
          alert('Failed to create task: ' + (error.message || 'Unknown error'));
        }
      } catch(err) {
        console.error(err);
        alert('Failed to create task.');
      } finally {
        submitBtn.prop('disabled', false).text(originalText);
      }
    });

    // Row click -> detail modal
    $('#rows').on('click', 'tr[data-id]', function(){
      const id = $(this).data('id');
      openDetail(id);
    });

    // DETAIL MODAL
    $('#modalClose').on('click', ()=> closeModal('detailModal'));
    $('#detailModal').on('click', (e)=>{
      if(e.target.id === 'detailModal') closeModal('detailModal');
    });

    function openDetail(id){
      fetch(`{{ route('dashboard.items.show', ['id'=>'__ID__']) }}`.replace('__ID__', id), {
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
          openModal('detailModal');
        })
        .catch(console.error);
    }

    // EXPORT BUTTON
    $('#btnExport').on('click', function(e){
    e.preventDefault();
    const params = new URLSearchParams(getFilters()).toString();
    const exportUrl = `{{ route('dashboard.items.export') }}?${params}`;

    // Open in new window to download
    window.location.href = exportUrl;

    console.log('Exporting with filters:', getFilters());
    });

    // Filters
    $('#btnApply').on('click', async ()=>{
      await fetchData();
      renderWindow();
    });

    $('#btnClear').on('click', async ()=>{
      $('#f_date_in_from,#f_date_in_to,#f_deadline_from,#f_deadline_to,#f_assign_by_id,#f_assign_to_id,#f_type_label,#f_company_id,#f_pic_name,#f_product_id,#f_status').val('');
      await fetchData();
    });

    // Calendar
    function initCalendar(){
      const el = document.getElementById('calendar');
      calendar = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        height: 'auto',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        events: (info, success, failure) => {
          const params = new URLSearchParams(getFilters()).toString();
          fetch(`{{ route('dashboard.items.events') }}?` + params, { headers:{ 'X-CSRF-TOKEN': CSRF }})
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

  } catch (e) {
    console.error('Boot error:', e);
  }
});
</script>
@endsection

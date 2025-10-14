@extends('layouts.app')

@section('head')
@vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js'])
<title>Outstanding Matters</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
@endsection

@section('content')
<div class="dashboard-wrapper">
  <div class="bg-gradient-animated"></div>
  <div class="particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
  </div>

  {{-- Toolbar --}}
  <div class="toolbar glass-morphism">
    <div class="toolbar-content">
      <h1 class="page-title">
        <span class="title-icon">📊</span>
        <span class="title-text">Outstanding Matters</span>
        <span class="title-pulse"></span>
      </h1>
      <div class="toolbar-actions">
        @can('export', \App\Models\Item::class)
          <button id="btnExport" type="button" class="btn btn-success magnetic">
            <span class="btn-bg"></span>
            <span class="btn-content">
              <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export Excel
            </span>
          </button>
        @endcan

        @can('create', \App\Models\Item::class)
          <button id="btnCreate" type="button" class="btn btn-primary magnetic">
            <span class="btn-bg"></span>
            <span class="btn-content">
              <span class="btn-plus">+</span> Create Task
            </span>
          </button>
        @endcan

        @role('admin')
            <button id="btnOpenRegisterUser" type="button" class="btn btn-secondary magnetic">
            <span class="btn-bg"></span>
            <span class="btn-content">Register User/Admin</span>
            </button>
        @endrole
      </div>

      <form method="POST" action="{{ route('logout') }}">
  @csrf
  <button type="submit" class="btn btn-secondary magnetic">
    <span class="btn-bg"></span>
    <span class="btn-content">Logout</span>
  </button>
</form>
    </div>
  </div>
  {{-- Filters --}}
  <div class="card">
    <div class="filter-panel glass-morphism slide-in-up">
      <div class="filter-grid">
        <div class="form-group animate-fade-in" style="animation-delay: 0.05s;">
          <label class="form-label">Date In</label>
          <input type="date" id="f_date_in_from" class="form-input glow-on-focus">
        </div>
        <div class="form-group animate-fade-in" style="animation-delay: 0.1s;">
          <label class="form-label">Assign By</label>
          <select id="f_assign_by_id" class="form-select">
            <option value="">All</option>
            @foreach($distinct['assign_by'] as $v)
              <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group animate-fade-in" style="animation-delay: 0.12s;">
        <label class="form-label">Assign To</label>
        <select id="f_assign_to_id" class="form-select">
            <option value="">All</option>
            @foreach($distinct['assign_to'] as $v)
            <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
        </select>
        </div>
        <div class="form-group animate-fade-in" style="animation-delay: 0.15s;">
          <label class="form-label">Internal/Client</label>
          <select id="f_type_label" class="form-select">
            <option value="">All</option>
            @foreach($distinct['type_labels'] as $v)
              <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group animate-fade-in" style="animation-delay: 0.2s;">
          <label class="form-label">Company</label>
          <select id="f_company_id" class="form-select">
            <option value="">All</option>
            @foreach($distinct['companies'] as $v)
              <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group animate-fade-in" style="animation-delay: 0.25s;">
          <label class="form-label">PIC</label>
          <select id="f_pic_name" class="form-select">
            <option value="">All</option>
            @foreach($distinct['pic_names'] as $v)
              <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group animate-fade-in" style="animation-delay: 0.3s;">
          <label class="form-label">Product</label>
          <select id="f_product_id" class="form-select">
            <option value="">All</option>
            @foreach($distinct['products'] as $v)
              <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group animate-fade-in" style="animation-delay: 0.35s;">
          <label class="form-label">Status</label>
          <select id="f_status" class="form-select">
            <option value="">All</option>
            @foreach($distinct['statuses'] as $v)
              <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
          </select>
        </div>
        <div class="filter-actions animate-fade-in" style="animation-delay: 0.4s;">
          <button id="btnApply" class="btn btn-primary btn-filter magnetic">Apply</button>
          <button id="btnClear" class="btn btn-secondary btn-filter magnetic">Clear</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="card">
    <div class="table-section glass-morphism slide-in-up" style="animation-delay: 0.2s;">
      <div class="table-header">
        <h2>
          <span class="section-icon">📋</span>
          Tasks
        </h2>
        <div class="table-meta">
          <span class="pulse-dot"></span>
          Auto-rotates 7 rows every 5s • Auto-refresh 30s
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th><span class="th-content">Date In</span></th>
              <th><span class="th-content">Deadline</span></th>
              <th><span class="th-content">Assign By</span></th>
              <th><span class="th-content">Assign To</span></th>
              <th><span class="th-content">Internal/Client</span></th>
              <th><span class="th-content">Company</span></th>
              <th><span class="th-content">Task</span></th>
              <th><span class="th-content">PIC</span></th>
              <th><span class="th-content">Product</span></th>
              <th><span class="th-content">Status</span></th>
              <th><span class="th-content">Remarks</span></th>
              <th><span class="th-content">Action</span></th>
            </tr>
          </thead>
          <tbody id="rows"></tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Calendar --}}
  <div class="card">
    <div class="calendar-section glass-morphism slide-in-up" style="animation-delay: 0.3s;">
      <div class="calendar-header">
        <h2>
          <span class="section-icon">📅</span>
          Calendar (Deadlines)
        </h2>
      </div>
      <div class="calendar-wrapper">
        <div id="calendar"></div>
      </div>
    </div>
  </div>
</div>

@role('admin')
<div id="modalRegisterUser" class="modal-backdrop hidden">
  <div class="modal-card glass-morphism">
    <div class="modal-header">
      <h3>Register User / Admin</h3>
      <button type="button" class="modal-close" id="btnCloseRegisterUser">×</button>
    </div>

    <form id="formRegisterUser" method="POST" action="{{ route('admin.users.store') }}">
      @csrf
      <div class="modal-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="form-group">
          <label class="form-label">Name</label>
          <input class="form-input" type="text" name="name" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-input" type="email" name="email" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input class="form-input" type="password" name="password" minlength="8" required>
        </div>
        <div class="form-group">
          <label class="form-label">Role</label>
          <select class="form-select" name="role" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" id="btnCancelRegisterUser">Cancel</button>
        <button type="submit" class="btn btn-primary">Create</button>
      </div>
    </form>
  </div>
</div>
@endrole


{{-- CREATE MODAL --}}
<div id="createModal" class="modal-overlay">
  <div class="modal-content modal-scale-in">
    <div class="modal-header">
      <h2>
        <span class="modal-icon">✨</span>
        Create New Task
      </h2>
      <button id="btnCloseCreate" type="button" class="modal-close">&times;</button>
    </div>

    <form id="createForm">
      @csrf
      <div class="modal-form-grid">
        <div class="modal-form-group">
          <label class="modal-form-label">Date In <span class="required">*</span></label>
          <input type="date" name="date_in" required class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Deadline <span class="required">*</span></label>
          <input type="date" name="deadline" required class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Assign By</label>
          <input type="text" name="assign_by_id" placeholder="Enter Assign By" class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Assign To</label>
          <input type="text" name="assign_to_id" placeholder="Enter Assign To" class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Internal/Client</label>
          <select name="type_label" class="modal-form-select">
            <option value="">Select Type</option>
            <option value="INTERNAL">INTERNAL</option>
            <option value="CLIENT">CLIENT</option>
          </select>
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Company</label>
          <input type="text" name="company_id" placeholder="Enter Company Name/ID" class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Task</label>
          <input type="text" name="task" placeholder="Enter Task" class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">PIC Name</label>
          <input type="text" name="pic_name" maxlength="150" placeholder="Enter PIC Name" class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Product</label>
          <input type="text" name="product_id" placeholder="Enter Product Name/ID" class="modal-form-input">
        </div>
        <div class="modal-form-group full-width">
          <label class="modal-form-label">Status <span class="required">*</span></label>
          <select name="status" required class="modal-form-select">
            <option value="">Select Status</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
          </select>
        </div>
        <div class="modal-form-group full-width">
          <label class="modal-form-label">Remarks</label>
          <textarea name="remarks" rows="3" placeholder="Enter any additional notes or remarks..." class="modal-form-textarea"></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" id="btnCancelCreate" class="btn btn-secondary magnetic">Cancel</button>
        <button type="submit" id="btnSubmitCreate" class="btn btn-primary magnetic">
          <span class="btn-bg"></span>
          <span class="btn-content">Save Task</span>
        </button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT MODAL --}}
<div id="editModal" class="modal-overlay">
  <div class="modal-content modal-scale-in">
    <div class="modal-header">
      <h2>
        <span class="modal-icon">✏️</span>
        Edit Task
      </h2>
      <button id="btnCloseEdit" type="button" class="modal-close">&times;</button>
    </div>

    <form id="editForm">
      @csrf
      <input type="hidden" name="id">

      <div class="modal-form-grid">
        <div class="modal-form-group">
          <label class="modal-form-label">Date In <span class="required">*</span></label>
          <input type="date" name="date_in" required class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Deadline <span class="required">*</span></label>
          <input type="date" name="deadline" required class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Assign By</label>
          <input type="text" name="assign_by_id" placeholder="Enter Assign By" class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Assign To</label>
          <input type="text" name="assign_to_id" placeholder="Enter Assign To" class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Internal/Client</label>
          <select name="type_label" class="modal-form-select">
            <option value="">Select Type</option>
            <option value="INTERNAL">INTERNAL</option>
            <option value="CLIENT">CLIENT</option>
          </select>
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Company</label>
          <input type="text" name="company_id" placeholder="Enter Company Name/ID" class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Task</label>
          <input type="text" name="task" placeholder="Enter Task" class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">PIC Name</label>
          <input type="text" name="pic_name" maxlength="150" placeholder="Enter PIC Name" class="modal-form-input">
        </div>
        <div class="modal-form-group">
          <label class="modal-form-label">Product</label>
          <input type="text" name="product_id" placeholder="Enter Product Name/ID" class="modal-form-input">
        </div>
        <div class="modal-form-group full-width">
          <label class="modal-form-label">Status <span class="required">*</span></label>
          <select name="status" required class="modal-form-select">
            <option value="">Select Status</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
          </select>
        </div>
        <div class="modal-form-group full-width">
          <label class="modal-form-label">Remarks</label>
          <textarea name="remarks" rows="3" placeholder="Enter any additional notes or remarks..." class="modal-form-textarea"></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" id="btnCancelEdit" class="btn btn-secondary magnetic">Cancel</button>
        <button type="submit" id="btnSubmitEdit" class="btn btn-primary magnetic">
          <span class="btn-bg"></span>
          <span class="btn-content">Update Task</span>
        </button>
      </div>
    </form>
  </div>
</div>

{{-- DETAIL MODAL --}}
<div id="detailModal" class="modal-overlay">
  <div class="modal-content modal-scale-in">
    <div class="modal-header">
      <h2>
        <span class="modal-icon">🔍</span>
        Task Details
      </h2>
      <div style="display: flex; gap: 0.5rem;">
        <button id="btnEdit" type="button" class="btn btn-primary magnetic" style="display: none;">
          <span class="btn-content">✏️ Edit</span>
        </button>
        <button id="modalClose" type="button" class="modal-close">&times;</button>
      </div>
    </div>
    <div id="detailBody" class="detail-grid"></div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
$(function(){
  try {
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let allData = [];
    let windowStart = 0;
    const WINDOW_SIZE = 7;
    let rotateTimer = null;
    let refreshTimer = null;
    let calendar = null;
    let currentDetailData = null;

    // Magnetic button effect
    document.querySelectorAll('.magnetic').forEach(btn => {
      btn.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;
        this.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
      });
      btn.addEventListener('mouseleave', function() {
        this.style.transform = 'translate(0, 0)';
      });
    });

    // Tom Select Configuration
    const tsBrowseSearch = {
      allowEmptyOption: true,
      create: false,
      preload: true,
      openOnFocus: true,
      maxOptions: 1000,
      searchField: ['text'],
      closeAfterSelect: true,
      dropdownParent: 'body',
      plugins: { clear_button: { title: 'Clear selection' } },
      render: {
        option: function(data, escape) { return '<div>' + escape(data.text) + '</div>'; },
        item: function(data, escape) { return '<div>' + escape(data.text) + '</div>'; }
      }
    };

    new TomSelect('#f_assign_by_id', tsBrowseSearch);
    new TomSelect('#f_assign_to_id', tsBrowseSearch);
    new TomSelect('#f_type_label',   tsBrowseSearch);
    new TomSelect('#f_company_id',   tsBrowseSearch);
    new TomSelect('#f_pic_name',     tsBrowseSearch);
    new TomSelect('#f_product_id',   tsBrowseSearch);
    new TomSelect('#f_status',       tsBrowseSearch);

    function openModal(modalId){
      const modal = document.getElementById(modalId);
      modal.classList.add('active');
      document.body.classList.add('modal-open');
    }

    function closeModal(modalId){
      const modal = document.getElementById(modalId);
      modal.classList.remove('active');
      document.body.classList.remove('modal-open');
    }

    function setInput(el, val){
      if(el) el.value = (val ?? '');
    }

    function hasAnyFilter(){
    return [
        '#f_date_in_from','#f_assign_by_id','#f_assign_to_id','#f_type_label',
        '#f_company_id','#f_pic_name','#f_product_id','#f_status','#f_q'
    ].some(sel => $(sel).val());
    }

    function statusBadge(status){
      if(!status) return '<span class="badge">-</span>';
      const safe = String(status).replace(/\s/g,'\\ ');
      return `<span class="badge badge-animated ${safe}">${status}</span>`;
    }

    function fmt(d){
      if (!d) return '-';
      if (typeof d === 'string') {
        const m = d.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (m) {
          const yy = m[1].slice(-2);
          const mm = m[2];
          const dd = m[3];
          return `${dd}/${mm}/${yy}`;
        }
      }
      const dt = new Date(d);
      if (Number.isNaN(dt.getTime())) return d;
      const dd = String(dt.getDate()).padStart(2,'0');
      const mm = String(dt.getMonth()+1).padStart(2,'0');
      const yy = String(dt.getFullYear()).slice(-2);
      return `${dd}/${mm}/${yy}`;
    }

   function rowHtml(r){


  const btnDelete = r.can_delete
    ? `<button type="button" class="btn btn-danger btnDelete magnetic" data-id="${r.id}">
         <span class="btn-content">Delete</span>
       </button>`
    : '';

  return `
    <tr data-id="${r.id}" data-can-update="${r.can_update ? '1':'0'}" class="table-row-animated">
      <td><span class="cell-content">${fmt(r.date_in)}</span></td>
      <td><span class="cell-content">${fmt(r.deadline)}</span></td>
      <td><span class="cell-content">${r.assign_by_id ?? '-'}</span></td>
      <td><span class="cell-content">${r.assign_to_id ?? '-'}</span></td>
      <td><span class="cell-content">${r.type_label ?? '-'}</span></td>
      <td><span class="cell-content">${r.company_name ?? r.company_id ?? '-'}</span></td>
      <td><span class="cell-content">${r.task ?? r.task_name ?? r.task_id ?? '-'}</span></td>
      <td><span class="cell-content">${r.pic_name ?? '-'}</span></td>
      <td><span class="cell-content">${r.product_name ?? r.product_id ?? '-'}</span></td>
      <td>${statusBadge(r.status)}</td>
      <td class="truncate max-w-200" title="${(r.remarks||'').replace(/"/g,'&quot;')}"><span class="cell-content">${r.remarks ?? ''}</span></td>
      <td style="display:flex; gap:.5rem;">
        ${btnDelete}
      </td>
    </tr>
  `;
}

function openEditModal(id){
  fetch(`{{ route('dashboard.items.editPayload', ['id' => '__ID__']) }}`.replace('__ID__', id), {
    headers: { 'X-CSRF-TOKEN': CSRF }
  })
  .then(async (r) => {
    if (r.status === 403) {
      alert("You don't have permission to edit this item.");
      throw new Error('403');
    }
    if (!r.ok) {
      throw new Error(`HTTP ${r.status}`);
    }
    return r.json();
  })
  .then(({data}) => {
    currentDetailData = data;
    fillEditForm(data);
    openModal('editModal'); // <-- hanya kebuka kalau authorized
  })
  .catch(() => {/* diam: kita blok modal untuk non-owner */});
}



    // Delete click (AJAX)
    $('#rows').on('click', '.btnDelete', async function(e){
      e.stopPropagation();
      const id = $(this).data('id');
      if(!confirm('Delete this item?')) return;



      try{
        const res = await fetch(`{{ route('dashboard.items.destroy', ['id' => '__ID__']) }}`.replace('__ID__', id), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({_method:'DELETE'})
        });

        if(!res.ok){
          const err = await res.json().catch(()=>({}));
          throw new Error(err.message || `HTTP ${res.status}`);
        }

        await fetchData();
      }catch(err){
        console.error(err);
        alert('Failed to delete: ' + err.message);
      }
    });

    // Edit click (from table)
    $('#rows').on('click', '.btnEdit', function(e){
    e.stopPropagation();
    const id = $(this).data('id');
    const row = $(this).closest('tr');
    const canUpdate = row.data('can-update') === 1 || row.data('can-update') === '1';

    if (!canUpdate) {
        alert("You are not allowed to edit this item.");
        return;
    }

    openEditModal(id);
    });

    function renderWindow(){
      const tbody = $('#rows');
      tbody.empty();

      if(allData.length === 0){
        tbody.append(`<tr><td colspan="12" style="padding:3rem 2rem; text-align:center; color:var(--muted);">No data</td></tr>`);
        return;
      }

      let start = hasAnyFilter() ? 0 : windowStart;
      const end = Math.min(start + WINDOW_SIZE, allData.length);
      const slice = allData.slice(start, end);

      slice.forEach(r => tbody.append(rowHtml(r)));

      tbody.find('.magnetic').each(function() {
        this.addEventListener('mousemove', function(e) {
          const rect = this.getBoundingClientRect();
          const x = e.clientX - rect.left - rect.width / 2;
          const y = e.clientY - rect.top - rect.height / 2;
          this.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
        });
        this.addEventListener('mouseleave', function() {
          this.style.transform = 'translate(0, 0)';
        });
      });
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
        assign_to_id:  $('#f_assign_to_id').val() || '',
        type_label:    $('#f_type_label').val() || '',
        company_id:    $('#f_company_id').val() || '',
        pic_name:      $('#f_pic_name').val() || '',
        product_id:    $('#f_product_id').val() || '',
        status:        $('#f_status').val() || '',
        q:             ($('#f_q').val() || '').trim(),
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
      openModal('createModal');
    });

    $('#btnCloseCreate, #btnCancelCreate').on('click', function(e){
      e.preventDefault();
      closeModal('createModal');
      $('#createForm')[0].reset();
    });

    $('#createModal').on('click', function(e){
      if(e.target.id === 'createModal'){
        closeModal('createModal');
        $('#createForm')[0].reset();
      }
    });

    $('#createForm').on('submit', async function(e){
      e.preventDefault();

      const submitBtn = $('#btnSubmitCreate');
      const originalText = submitBtn.find('.btn-content').text();
      submitBtn.prop('disabled', true).find('.btn-content').text('Saving...');

      const formData = new FormData(this);

      try {
        const response = await fetch('{{ route("dashboard.items.store") }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': CSRF },
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
        submitBtn.prop('disabled', false).find('.btn-content').text(originalText);
      }
    });

    // EDIT MODAL EVENTS
    $('#btnCloseEdit, #btnCancelEdit').on('click', function(e){
      e.preventDefault();
      closeModal('editModal');
    });

    $('#editModal').on('click', function(e){
      if(e.target.id === 'editModal'){
        closeModal('editModal');
      }
    });

    function fillEditForm(d){
      const f = document.getElementById('editForm');
      setInput(f.elements['id'], d.id);
      setInput(f.elements['date_in'], (d.date_in ?? '').slice(0,10));
      setInput(f.elements['deadline'], (d.deadline ?? '').slice(0,10));
      setInput(f.elements['assign_by_id'], d.assign_by_id);
      setInput(f.elements['assign_to_id'], d.assign_to_id);
      setInput(f.elements['type_label'], d.type_label);
      setInput(f.elements['company_id'], d.company_id);
      setInput(f.elements['task'], d.task);
      setInput(f.elements['pic_name'], d.pic_name);
      setInput(f.elements['product_id'], d.product_id);
      setInput(f.elements['status'], d.status);
      setInput(f.elements['remarks'], d.remarks);
    }

    $('#editForm').on('submit', async function(e){
      e.preventDefault();
      const f = e.currentTarget;
      const id = f.elements['id'].value;

      const payload = {
        date_in:      f.elements['date_in'].value || null,
        deadline:     f.elements['deadline'].value || null,
        assign_by_id: f.elements['assign_by_id'].value || null,
        assign_to_id: f.elements['assign_to_id'].value || null,
        type_label:   f.elements['type_label'].value || null,
        company_id:   f.elements['company_id'].value || null,
        task:         f.elements['task'].value || null,
        pic_name:     f.elements['pic_name'].value || null,
        product_id:   f.elements['product_id'].value || null,
        status:       f.elements['status'].value,
        remarks:      f.elements['remarks'].value || null,
      };

      const btn = $('#btnSubmitEdit');
      const orig = btn.find('.btn-content').text();
      btn.prop('disabled', true).find('.btn-content').text('Updating...');

      try {
        const res = await fetch(`{{ route('dashboard.items.update', ['id' => '__ID__']) }}`.replace('__ID__', id), {
          method: 'PATCH',
          headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(payload)
        });

        if(!res.ok){
          const err = await res.json().catch(()=>({}));
          throw new Error(err.error || `HTTP ${res.status}`);
        }

        alert('Task updated successfully!');
        closeModal('editModal');
        await fetchData();
        if (window.calendar) calendar.refetchEvents();
      } catch (err){
        console.error(err);
        alert('Update failed: ' + err.message);
      } finally {
        btn.prop('disabled', false).find('.btn-content').text(orig);
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

    $('#btnEdit').on('click', function(){
      if(currentDetailData){
        fillEditForm(currentDetailData);
        closeModal('detailModal');
        openModal('editModal');
      }
    });

    function openDetail(id){
      fetch(`{{ route('dashboard.items.show', ['id'=>'__ID__']) }}`.replace('__ID__', id), {
        headers:{ 'X-CSRF-TOKEN': CSRF }
      })
        .then(r=>r.json())
        .then(d=>{
          currentDetailData = d;

          // Show/hide Edit button by permission
          const editBtn = document.getElementById('btnEdit');
          if (editBtn) {
            editBtn.style.display = d.can_update ? 'inline-flex' : 'none';
          }

          const html = `
            <div class="detail-item">
              <div class="detail-label">ID</div>
              <div class="detail-value">${d.id}</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Date In</div>
              <div class="detail-value">${fmt(d.date_in)}</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Deadline</div>
              <div class="detail-value">${fmt(d.deadline)}</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Assign By</div>
              <div class="detail-value">${d.assign_by_id ?? '-'}</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Assign To</div>
              <div class="detail-value">${d.assign_to_id ?? '-'}</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Type</div>
              <div class="detail-value">${d.type_label ?? '-'}</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Company</div>
              <div class="detail-value">${d.company_name ?? d.company_id ?? '-'}</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Task</div>
              <div class="detail-value">${d.task ?? d.task_name ?? d.task_id ?? '-'}</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">PIC</div>
              <div class="detail-value">${d.pic_name ?? '-'}</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Product</div>
              <div class="detail-value">${d.product_name ?? d.product_id ?? '-'}</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Status</div>
              <div class="detail-value">${d.status ?? '-'}</div>
            </div>
            <div class="detail-item full-width">
              <div class="detail-label">Remarks</div>
              <div class="detail-value">${d.remarks ?? '-'}</div>
            </div>
          `;
          $('#detailBody').html(html);
          openModal('detailModal');
        })
        .catch(console.error);
    }

    $(document).on('keydown', function(e){
      if(e.key === 'Escape'){
        if($('#createModal').hasClass('active')){
          closeModal('createModal');
          $('#createForm')[0].reset();
        }
        if($('#editModal').hasClass('active')){
          closeModal('editModal');
        }
        if($('#detailModal').hasClass('active')){
          closeModal('detailModal');
        }
      }
    });

    $('#btnExport').on('click', function(e){
      e.preventDefault();
      const params = new URLSearchParams(getFilters()).toString();
      const exportUrl = `{{ route('dashboard.items.export') }}?${params}`;
      window.location.href = exportUrl;
    });

    $('#btnApply').on('click', async ()=>{
      await fetchData();
      renderWindow();
    });

    $('#btnClear').on('click', async ()=>{
    $('#f_date_in_from').val('');
    $('#f_q').val(''); // clear search
    const selectors = [
        '#f_assign_by_id','#f_assign_to_id','#f_type_label',
        '#f_company_id','#f_pic_name','#f_product_id','#f_status'
    ];
    selectors.forEach(sel => {
        const instance = $(sel)[0].tomselect;
        if(instance) instance.clear();
    });
    await fetchData();
    });

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
      window.calendar = calendar;
    }

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

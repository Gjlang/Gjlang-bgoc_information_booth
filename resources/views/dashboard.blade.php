@extends('layouts.app')
@section('head')

@vite(['resources/css/app.css', 'resources/js/app.js'])
<title>Outstanding Matters</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">

{{-- OVERDUE ROW STYLING - MUST BE LAST TO OVERRIDE EVERYTHING --}}
<style>

/* ===== EXISTING STYLES ===== */
.th-2line {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  line-height: 1.2;
  gap: 2px;
}

.th-2line .top {
  font-size: 0.85em;
  font-weight: 600;
}

.th-2line .sub {
  font-size: 0.75em;
  opacity: 0.8;
}

/* 🔴 SECTION HEADERS IN PREVIEW - UPDATED: LEFT ALIGN + RED TEXT */
#previewTable .section-header-row {
  background: linear-gradient(135deg, #b4c7e7 0%, #8fa9d4 100%) !important;
  border-bottom: 2px solid #6b8dc7 !important;
}

#previewTable .section-header-cell {
  padding: 12px 16px !important;
  text-align: left !important;  /* 🔴 CHANGED: CENTER → LEFT */
  font-weight: 700 !important;
  font-size: 1rem !important;
  text-transform: uppercase !important;
  letter-spacing: 1px !important;
  color: #dc2626 !important;  /* 🔴 CHANGED: #1f4e78 (blue) → #dc2626 (red) */
}

/* Revert button styling */
.btnRevert {
  transition: all 0.2s ease;
  font-weight: 500;
}

.btnRevert:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btnRevert:active {
  transform: translateY(0);
}

/* Modal backdrop styling */
#previewModal, #completedModal {
  backdrop-filter: blur(4px);
}

/* Hairline border utility */
.hairline {
  border-width: 1px;
  border-color: #e5e7eb;
}

/* Table hover effect */
#previewTable tbody tr:hover,
#completedTable tbody tr:hover {
  background-color: #f9fafb !important;
  transition: background-color 0.15s ease;
}

/* Red deadline for expired */
.text-red-deadline {
  color: #dc2626 !important;
  font-weight: 600 !important;
}
/* Modal backdrop styling */
#previewModal, #completedModal {
  backdrop-filter: blur(4px);
}

/* Hairline border utility */
.hairline {
  border-width: 1px;
  border-color: #e5e7eb;
}

/* Table hover effect */
#previewTable tbody tr:hover,
#completedTable tbody tr:hover {
  background-color: #f9fafb !important;
  transition: background-color 0.15s ease;
}

/* Red deadline for expired */
.text-red-deadline {
  color: #dc2626 !important;
  font-weight: 600 !important;
}
/* OVERDUE ROW - CRITICAL FINAL OVERRIDE */
/* === Layout tabel & kolom === */
/* === Force fixed layout === */
.data-table {
  table-layout: fixed !important;
  width: 100%;
}

/* === Date In (1) & Deadline (2) - Compact date columns === */
.data-table thead th:nth-child(1),
.data-table tbody td:nth-child(1),
.data-table thead th:nth-child(2),
.data-table tbody td:nth-child(2) {
  width: 70px !important;
  max-width: 70px !important;
  min-width: 70px !important;
  text-align: center;
  white-space: nowrap;
  padding: 4px 2px;
  font-size: 0.9em;
}

/* === Assign By (3) & Assign To (4) - Narrow name columns === */
.data-table thead th:nth-child(3),
.data-table tbody td:nth-child(3),
.data-table thead th:nth-child(4),
.data-table tbody td:nth-child(4) {
  width: 70px !important;
  max-width: 70px !important;
  min-width: 70px !important;
  text-align: center;
  white-space: nowrap;
  padding: 4px 2px;
  font-size: 0.9em;
}

/* Two-line header styling for ASSIGN columns */
.th-2line {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  line-height: 1;
  gap: 1px;
  padding: 2px;
}

.th-2line .top {
  font-size: 0.85em;
  font-weight: 600;
}

.th-2line .sub {
  font-size: 0.75em;
  opacity: 0.8;
}

/* === Company (5) - Medium width === */
.data-table thead th:nth-child(5),
.data-table tbody td:nth-child(5) {
  width: 130px !important;
  max-width: 130px !important;
  min-width: 130px !important;
  padding: 6px 8px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* === PIC (6) - Small and centered === */
.data-table thead th:nth-child(6),
.data-table tbody td:nth-child(6) {
  width: 90px !important;
  max-width: 90px !important;
  min-width: 90px !important;
  text-align: center;
  white-space: nowrap;
  padding: 6px;
}

/* === Product (7) - Medium width === */
.data-table thead th:nth-child(7),
.data-table tbody td:nth-child(7) {
  width: 130px !important;
  max-width: 130px !important;
  min-width: 130px !important;
  padding: 6px 8px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* === Task (8) - Wider with ellipsis === */
.data-table thead th:nth-child(8),
.data-table tbody td:nth-child(8) {
  width: 190px !important;
  max-width: 190px !important;
  min-width: 190px !important;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  padding: 6px 8px;
}

/* === Remarks (9) - Wider with ellipsis === */
.data-table thead th:nth-child(9),
.data-table tbody td:nth-child(9) {
  width: 210px !important;
  max-width: 210px !important;
  min-width: 210px !important;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  padding: 6px 8px;
}

/* === Int/Client (10) & Status (11) - Small and centered === */
.data-table thead th:nth-child(10),
.data-table tbody td:nth-child(10),
.data-table thead th:nth-child(11),
.data-table tbody td:nth-child(11) {
  width: 100px !important;
  max-width: 100px !important;
  min-width: 100px !important;
  text-align: center;
  white-space: nowrap;
  padding: 6px;
}

/* === Action (12) - Small and centered === */
.data-table thead th:nth-child(12),
.data-table tbody td:nth-child(12) {
  width: 80px !important;
  max-width: 80px !important;
  min-width: 80px !important;
  text-align: center;
  white-space: nowrap;
  padding: 6px;
}

/* === General table styling for readability === */
.data-table thead th {
  font-weight: 600;
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  padding: 6px 4px;
  font-size: 0.85em;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.data-table tbody td {
  border-bottom: 1px solid #e9ecef;
  vertical-align: middle;
  padding: 6px 4px;
  font-size: 0.9em;
}

/* Optional: Add hover effect for better UX */
.data-table tbody tr:hover {
  background-color: #f8f9fa;
}

</style>

@endsection

@section('content')
<div class="dashboard-wrapper page-dashboard">
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

          <button
  id="btnPreviewTables"
  type="button"
  class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border hairline bg-white hover:bg-neutral-50 text-neutral-800"
>
  <!-- eye icon (heroicons outline) -->
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z" />
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
  </svg>
  Preview Tables
</button>

<button id="btnCompletedTasks" type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border hairline bg-emerald-50 hover:bg-emerald-100 text-emerald-700">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>
  Completed Tasks
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
              <option value="{{ $v }}">{{ strtoupper($v) === 'INTERNAL' ? 'INT' : $v }}</option>
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
            @php
                $statuses = collect($distinct['statuses'] ?? [])
                ->map(fn($s) => (string)$s)
                ->unique()
                ->values();

                if (!$statuses->contains('Expired')) { $statuses->push('Expired'); }
            @endphp
            @foreach($statuses as $v)
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
          Auto-rotates 7 rows every 8s •
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th><span class="th-content">Date In</span></th>
<th><span class="th-content">Deadline</span></th>
<th>
  <span class="th-content">
    <span class="th-2line">
      <span class="top">ASSIGN</span>
      <span class="sub">BY</span>
    </span>
  </span>
</th>
<th>
  <span class="th-content">
    <span class="th-2line">
      <span class="top">ASSIGN</span>
      <span class="sub">TO</span>
    </span>
  </span>
</th>

<th><span class="th-content">Company</span></th>
<th><span class="th-content">PIC</span></th>
<th><span class="th-content">Product</span></th>
<th><span class="th-content">Task</span></th>
<th><span class="th-content">Remarks</span></th>
<th><span class="th-content">Int/Client</span></th>
<th><span class="th-content">Status</span></th>
<th><span class="th-content">Action</span></th>

            </tr>
          </thead>
          <tbody id="rows"></tbody>
        </table>
      </div>

      <div id="tableInfo" class="text-xs text-neutral-500 mt-2 px-4 py-2" style="background: rgba(255,255,255,0.5); border-top: 1px solid rgba(229,231,235,0.5);"></div>
    </div>
  </div>

  <!-- Completed Tasks Modal -->
<div id="completedModal" class="fixed inset-0 z-[999] hidden">
  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black/40"></div>

  <!-- Dialog -->
  <div class="relative mx-auto my-8 w-[95vw] max-w-7xl bg-white rounded-2xl shadow-xl">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b hairline">
      <h3 class="text-lg font-semibold text-neutral-900">✅ Completed Tasks</h3>
      <button id="completedClose" class="p-2 rounded-lg hover:bg-neutral-100" aria-label="Close">✕</button>
    </div>

    <!-- Body -->
    <div class="px-6 py-5">
      <!-- Summary -->
      <div class="flex items-center gap-3 mb-4">
        <span id="completedCount" class="px-3 py-1 rounded-full border hairline bg-emerald-50 text-emerald-700 text-sm font-medium">
          0 completed
        </span>
      </div>

      <!-- Table Container -->
      <div class="overflow-auto max-h-[65vh]">
        <table class="min-w-full border hairline rounded-xl" id="completedTable">
          <thead class="bg-neutral-50 text-neutral-700 text-sm sticky top-0">
            <tr id="completedHeadRow">
              <!-- Populated by JS -->
            </tr>
          </thead>
          <tbody id="completedBody" class="text-sm text-neutral-900">
            <!-- Populated by JS -->
          </tbody>
        </table>
        <div id="completedEmpty" class="hidden py-16 text-center text-neutral-500">
          No completed tasks found.
        </div>
      </div>

      <!-- Pagination -->
      <div id="completedPagination" class="flex items-center justify-between mt-4 pt-4 border-t hairline">
        <div class="text-xs text-neutral-500">
          Showing <span id="completedShowingStart">0</span>-<span id="completedShowingEnd">0</span> of <span id="completedTotal">0</span>
        </div>
        <div class="flex items-center gap-2">
          <button id="completedPrevPage" class="px-3 py-1 rounded-lg border hairline hover:bg-neutral-50 disabled:opacity-50 disabled:cursor-not-allowed text-sm">
            Previous
          </button>
          <span id="completedPageInfo" class="text-sm text-neutral-600">Page 1</span>
          <button id="completedNextPage" class="px-3 py-1 rounded-lg border hairline hover:bg-neutral-50 disabled:opacity-50 disabled:cursor-not-allowed text-sm">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="flex items-center justify-between gap-3 px-6 py-4 border-t hairline">
      <div class="text-xs text-neutral-500">
        Read-only view • Export via main Export button
      </div>
      <button id="completedClose2" class="px-4 py-2 rounded-xl border hairline bg-white hover:bg-neutral-50">Close</button>
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
        <button id="printCalBtn" type="button" class="btn btn-secondary">
            Print Calendar
        </button>
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
            <option value="INTERNAL">INT</option>
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
            <option value="Expired">Expired</option>
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
            <option value="INTERNAL">INT</option>
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
            <option value="Expired">Expired</option>

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

<!-- ADD: Preview Tables Modal -->
<div id="previewModal" class="fixed inset-0 z-[999] hidden">
  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black/40"></div>

  <!-- Dialog -->
  <div class="relative mx-auto my-8 w-[95vw] max-w-7xl bg-white rounded-2xl shadow-xl">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b hairline">
      <h3 class="text-lg font-semibold text-neutral-900">Preview Tables (Before Export)</h3>
      <button id="previewClose" class="p-2 rounded-lg hover:bg-neutral-100" aria-label="Close preview">✕</button>
    </div>

    <!-- Body -->
    <div class="px-6 py-5">
      <!-- Summary chips -->
      <div id="previewSummary" class="flex flex-wrap gap-2 mb-4"></div>

      <!-- Scroll container -->
      <div class="overflow-auto max-h-[70vh]">
        <table class="min-w-full border hairline rounded-xl" id="previewTable">
          <thead class="bg-neutral-50 text-neutral-700 text-sm">
            <tr id="previewHeadRow">
              <!-- Populated by JS -->
            </tr>
          </thead>
          <tbody id="previewBody" class="text-sm text-neutral-900">
            <!-- Populated by JS -->
          </tbody>
        </table>
        <div id="previewEmpty" class="hidden py-16 text-center text-neutral-500">
          No data for current filters.
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="flex items-center justify-between gap-3 px-6 py-4 border-t hairline">
      <div class="text-xs text-neutral-500">
        This preview mirrors your current filters. Use Export to download.
      </div>
      <div class="flex items-center gap-2">
        <!-- Optional: quick jump to Export -->
        <a href="{{ route('dashboard.items.export') }}" class="px-4 py-2 rounded-xl bg-[#22255b] text-white hover:opacity-90">
          Go to Export
        </a>
        <button id="previewClose2" class="px-4 py-2 rounded-xl border hairline bg-white hover:bg-neutral-50">Close</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>


// ===== COMPLETED TASKS MODAL =====
let completedData = [];
let completedPage = 1;
const COMPLETED_PER_PAGE = 20;

function openCompletedModal() {
  document.getElementById('completedModal')?.classList.remove('hidden');
}

function closeCompletedModal() {
  document.getElementById('completedModal')?.classList.add('hidden');
}

// ===== COMPLETED TASKS MODAL WITH REVERT =====
function renderCompletedTable(page = 1) {
  const head = document.getElementById('completedHeadRow');
  const body = document.getElementById('completedBody');
  const empty = document.getElementById('completedEmpty');
  if (!head || !body) return;

  const esc = s => {
    if (s === null || s === undefined) return '';
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  };

  const columns = [
    { key: 'date_in',      label: 'Date In',  format: 'date' },
    { key: 'deadline',     label: 'Deadline', format: 'date' },
    { key: 'assign_by_id', label: 'Assign By' },
    { key: 'assign_to_id', label: 'Assign To' },
    { key: 'company_name', label: 'Company', render: r => (r.company_name ?? r.company_id ?? '') },
    { key: 'pic_name',     label: 'PIC' },
    { key: 'product_name', label: 'Product', render: r => (r.product_name ?? r.product_id ?? '') },
    { key: 'task',         label: 'Task' },
    { key: 'remarks',      label: 'Remarks' },
    { key: 'type_label',   label: 'Int/Client', render: r => uiTypeLabel(r.type_label) },
    { key: 'action',       label: 'Action' }, // 🔴 NEW: Action column
  ];

  // Head
  if (!head.children.length) {
    head.innerHTML = columns
      .map(c => `<th class="px-3 py-2 text-left font-semibold border-b hairline">${esc(c.label)}</th>`)
      .join('');
  }

  // Empty check
  if (!completedData || completedData.length === 0) {
    body.innerHTML = '';
    empty?.classList.remove('hidden');
    document.getElementById('completedCount').textContent = '0 completed';
    document.getElementById('completedPagination').style.display = 'none';
    return;
  }
  empty?.classList.add('hidden');

  // Pagination
  const total = completedData.length;
  const totalPages = Math.ceil(total / COMPLETED_PER_PAGE);
  const start = (page - 1) * COMPLETED_PER_PAGE;
  const end = Math.min(start + COMPLETED_PER_PAGE, total);
  const pageData = completedData.slice(start, end);

  // Update count
  document.getElementById('completedCount').textContent = `${total} completed`;
  document.getElementById('completedTotal').textContent = total;
  document.getElementById('completedShowingStart').textContent = start + 1;
  document.getElementById('completedShowingEnd').textContent = end;
  document.getElementById('completedPageInfo').textContent = `Page ${page} of ${totalPages}`;
  document.getElementById('completedPagination').style.display = 'flex';

  // Enable/disable pagination buttons
  document.getElementById('completedPrevPage').disabled = (page === 1);
  document.getElementById('completedNextPage').disabled = (page === totalPages);

  // Render rows
  const tr = pageData.map(r => {
    const tds = columns.map(c => {
      if (c.key === 'action') {
        // 🔴 REVERT BUTTON (only if user can update)
        if (r.can_update) {
          return `<td class="px-3 py-2 border-b hairline">
            <button
              class="btnRevert px-3 py-1 text-sm rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200"
              data-id="${r.id}"
            >
              ↩️ Revert
            </button>
          </td>`;
        } else {
          return `<td class="px-3 py-2 border-b hairline text-neutral-400">-</td>`;
        }
      }

      let val = (typeof c.render === 'function') ? c.render(r) : r[c.key];
      if (c.format === 'date' && typeof window.fmt === 'function') {
        val = window.fmt(val);
      }
      return `<td class="px-3 py-2 border-b hairline align-top">${esc(val)}</td>`;
    }).join('');
    return `<tr class="hover:bg-neutral-50">${tds}</tr>`;
  }).join('');

  body.innerHTML = tr;

  // 🔴 WIRE REVERT BUTTONS
  document.querySelectorAll('.btnRevert').forEach(btn => {
    btn.addEventListener('click', async function() {
      const id = this.dataset.id;
      if (!confirm('Revert this task back to In Progress?')) return;

      try {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch(`{{ route('dashboard.items.update', ['id' => '__ID__']) }}`.replace('__ID__', id), {
          method: 'PATCH',
          headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ status: 'In Progress' })
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        alert('Task reverted to In Progress!');

        // Reload completed tasks
        await loadCompletedTasks();

        // Refresh main table
        if (typeof fetchData === 'function') {
          await fetchData();
        }
      } catch (err) {
        console.error('Revert failed:', err);
        alert('Failed to revert task. See console.');
      }
    });
  });
}

async function loadCompletedTasks() {
  try {
    // Fetch ALL items (no filter) with status=Completed
    const params = new URLSearchParams({ status: 'Completed' });
    const url = `{{ route('dashboard.items.list') }}?${params}`;
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

    const res = await fetch(url, { headers: { 'X-CSRF-TOKEN': CSRF } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const payload = await res.json();
    completedData = Array.isArray(payload) ? payload : (payload.data || []);

    // Sort newest first
    completedData.sort((a, b) => {
      const dateA = new Date(a.updated_at || a.created_at || 0);
      const dateB = new Date(b.updated_at || b.created_at || 0);
      return dateB - dateA;
    });

    completedPage = 1;
    renderCompletedTable(completedPage);
    openCompletedModal();
  } catch (e) {
    console.error('Failed to load completed tasks:', e);
    alert('Failed to load completed tasks. See console.');
  }
}

// Wire up events
document.getElementById('btnCompletedTasks')?.addEventListener('click', () => {
  loadCompletedTasks();
});

document.getElementById('completedClose')?.addEventListener('click', closeCompletedModal);
document.getElementById('completedClose2')?.addEventListener('click', closeCompletedModal);

document.getElementById('completedPrevPage')?.addEventListener('click', () => {
  if (completedPage > 1) {
    completedPage--;
    renderCompletedTable(completedPage);
  }
});

document.getElementById('completedNextPage')?.addEventListener('click', () => {
  const totalPages = Math.ceil(completedData.length / COMPLETED_PER_PAGE);
  if (completedPage < totalPages) {
    completedPage++;
    renderCompletedTable(completedPage);
  }
});

document.getElementById('completedModal')?.addEventListener('click', (ev) => {
  if (ev.target?.id === 'completedModal') closeCompletedModal();
});

let allData = [];
let windowStart = 0;
const WINDOW_SIZE = 7;

function hasAnyFilter(){
  return [
      '#f_date_in_from','#f_assign_by_id','#f_assign_to_id','#f_type_label',
      '#f_company_id','#f_pic_name','#f_product_id','#f_status','#f_q'
  ].some(sel => $(sel).val());
}

function uiTypeLabel(v){
  if (!v) return '-';
  return String(v).trim().toUpperCase() === 'INTERNAL' ? 'INT' : v;
}
window.uiTypeLabel = uiTypeLabel;


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

function updateTableCounter(visibleCount = 0){
  const el = document.getElementById('tableInfo');
  if (!el) return;

  const total = allData.length;
  const showing = visibleCount;
   console.log('DEBUG Counter:', { showing, total, allData });

  let range = '';
  if (!hasAnyFilter() && total > 0){  // ✅ Now accessible
    const start = windowStart + 1;
    const end = Math.min(windowStart + visibleCount, total);
    range = ` • Rows ${start}-${end}`;
  }

  el.textContent = `Showing ${showing} of ${total} item(s)` + range;
}



// === PREVIEW TABLES (Before Export) =================================

function getCurrentFilterParams() {
  // Prefer the one defined later inside $(function){...}
  if (typeof window.getFilters === 'function') {
    return new URLSearchParams(window.getFilters()).toString();
  }

  // Fallback: read inputs directly matching your table columns
  const filterParams = {
    date_in_from:  document.querySelector('#f_date_in_from')?.value || '',
    deadline_from: document.querySelector('#f_deadline_from')?.value || '',
    assign_by_id:  document.querySelector('#f_assign_by_id')?.value || '',
    assign_to_id:  document.querySelector('#f_assign_to_id')?.value || '',
    company_id:    document.querySelector('#f_company_id')?.value || '',
    pic_name:      document.querySelector('#f_pic_name')?.value || '',
    product_id:    document.querySelector('#f_product_id')?.value || '',
    task:          document.querySelector('#f_task')?.value || '',
    remarks:       document.querySelector('#f_remarks')?.value || '',
    type_label:    document.querySelector('#f_type_label')?.value || '', // Int/Client
    status:        document.querySelector('#f_status')?.value || '',
  };

  return new URLSearchParams(filterParams).toString();
}

// (2) Open/Close modal helpers
function openPreviewModal() {
  document.getElementById('previewModal')?.classList.remove('hidden');
}
function closePreviewModal() {
  document.getElementById('previewModal')?.classList.add('hidden');
}

// (3) Render helpers
function renderSummaryChips(rows) {
  const wrap = document.getElementById('previewSummary');
  if (!wrap) return;

  // simple counts by status
  const counts = rows.reduce((acc, r) => {
    const s = (r.status || '').toString();
    acc[s] = (acc[s] || 0) + 1;
    return acc;
  }, {});

  wrap.innerHTML = '';
  const total = rows.length;
  const chip = (label, value) => `
    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border hairline bg-white">
      <span class="text-xs text-neutral-500">${label}</span>
      <span class="text-sm font-medium text-neutral-900">${value}</span>
    </span>`;

  wrap.insertAdjacentHTML('beforeend', chip('Total', total));
  Object.keys(counts).sort().forEach(k => {
    wrap.insertAdjacentHTML('beforeend', chip(k || '(no status)', counts[k]));
  });
}

// ===== PREVIEW TABLES WITH SECTIONS =====
// ===== PREVIEW TABLES WITH SECTIONS =====
function renderPreviewTable(rows) {
  const head = document.getElementById('previewHeadRow');
  const body = document.getElementById('previewBody');
  const empty = document.getElementById('previewEmpty');
  if (!head || !body) return;

  const esc = s => {
    if (s === null || s === undefined) return '';
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  };

  const columns = [
    { key: 'date_in',      label: 'Date In',  format: 'date' },
    { key: 'deadline',     label: 'Deadline', format: 'date' },
    { key: 'assign_by_id', labelHtml: '<span class="th-2line"><span class="top">ASSIGN</span><span class="sub">BY</span></span>' },
    { key: 'assign_to_id', labelHtml: '<span class="th-2line"><span class="top">ASSIGN</span><span class="sub">TO</span></span>' },
    { key: 'company_name', label: 'Company', render: r => (r.company_name ?? r.company_id ?? '') },
    { key: 'pic_name',     label: 'PIC' },
    { key: 'product_name', label: 'Product', render: r => (r.product_name ?? r.product_id ?? '') },
    { key: 'task',         label: 'Task' },
    { key: 'remarks',      label: 'Remarks' },
    { key: 'type_label',   label: 'Int/Client', render: r => uiTypeLabel(r.type_label) },
    { key: 'status',       label: 'Status' },
  ];

  // Head
  head.innerHTML = columns
    .map(c => {
      const headerContent = c.labelHtml || esc(c.label || '');
      return `<th class="px-3 py-2 text-left font-semibold border-b hairline">${headerContent}</th>`;
    })
    .join('');

  // Empty state
  if (!rows || rows.length === 0) {
    body.innerHTML = '';
    empty?.classList.remove('hidden');
    return;
  }
  empty?.classList.add('hidden');

  // 🔴 GROUP BY STATUS (same order as export)
  const statusOrder = ['Expired', 'Pending', 'In Progress', 'Completed'];

  // Group items by status
  const grouped = {};
  statusOrder.forEach(status => {
    grouped[status] = rows.filter(r => {
      const s = (r.status || '').trim();
      return s.toLowerCase() === status.toLowerCase();
    }).sort((a, b) => {
      // Sort by deadline (earliest first)
      const dateA = a.deadline ? new Date(a.deadline).getTime() : Infinity;
      const dateB = b.deadline ? new Date(b.deadline).getTime() : Infinity;
      return dateA - dateB;
    });
  });

  // 🔴 RENDER SECTIONS WITH PROPER CLASSES
  let html = '';
  statusOrder.forEach(status => {
    const items = grouped[status];
    if (items && items.length > 0) {
      // Section header - USE THE CSS CLASSES
      html += `
        <tr class="section-header-row">
          <td colspan="${columns.length}" class="section-header-cell">
            ${status}
          </td>
        </tr>
      `;

      // Items in this section
      items.forEach(r => {
        const tds = columns.map(c => {
          let val = (typeof c.render === 'function') ? c.render(r) : r[c.key];
          if (c.format === 'date' && typeof window.fmt === 'function') {
            val = window.fmt(val);
          }

          // Red deadline for Expired
          let cellClass = 'px-3 py-2 border-b hairline align-top';
          if (c.key === 'deadline' && status === 'Expired') {
            cellClass += ' text-red-deadline';
          }

          return `<td class="${cellClass}">${esc(val)}</td>`;
        }).join('');

        html += `<tr class="hover:bg-neutral-50">${tds}</tr>`;
      });
    }
  });

  body.innerHTML = html;
}
// (4) Fetch data from your existing list endpoint and show modal
async function loadPreviewAndOpen() {
  const params = getCurrentFilterParams();
  // IMPORTANT: the route below exists in your app list (GET dashboard/items/list)
  const url = `{{ route('dashboard.items.list') }}?${params}`;
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

  const res = await fetch(url, { headers: { 'X-CSRF-TOKEN': CSRF } });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  const payload = await res.json();

  // Normalize: some apps put data in payload.data; others return array directly
  const rows = Array.isArray(payload) ? payload : (payload.data || payload.items || []);
  renderSummaryChips(rows);
  renderPreviewTable(rows);
  openPreviewModal();
}

// (5) Wire events
document.getElementById('btnPreviewTables')?.addEventListener('click', async () => {
  try {
    // Optional: show a tiny loading state (you can enhance with a spinner)
    document.getElementById('btnPreviewTables').disabled = true;
    await loadPreviewAndOpen();
  } catch (e) {
    console.error('Preview error:', e);
    alert('Failed to load preview. See console for details.');
  } finally {
    document.getElementById('btnPreviewTables').disabled = false;
  }
});

document.getElementById('previewClose')?.addEventListener('click', closePreviewModal);
document.getElementById('previewClose2')?.addEventListener('click', closePreviewModal);
// Close when clicking backdrop
document.getElementById('previewModal')?.addEventListener('click', (ev) => {
  if (ev.target?.id === 'previewModal') closePreviewModal();
});

// ====================================================================

// ---- CAPTURE MODE: rapihin halaman sebelum snapshot ----
function withCaptureMode(run) {
  // sisipkan style sementara
  const style = document.createElement('style');
  style.id = 'captureStyles';
  style.textContent = `
    body.capture-mode { background:#fff !important; }
    body.capture-mode .bg-gradient-animated,
    body.capture-mode .particles,
    body.capture-mode .toolbar,
    body.capture-mode .card .table-section,
    body.capture-mode .card .filter-panel { display:none !important; }

    body.capture-mode .calendar-section { box-shadow:none !important; }
    body.capture-mode .calendar-wrapper { padding:0 !important; margin:0 !important; }
    body.capture-mode #calendar { background:#fff !important; }
    body.capture-mode .fc { font-size:12px !important; } /* sedikit lebih rapat */
    body.capture-mode .fc-scrollgrid,
    body.capture-mode .fc-scrollgrid-section { box-shadow:none !important; overflow:visible !important; }
  `;
  document.head.appendChild(style);
  document.body.classList.add('capture-mode');

  // pastikan font sudah siap supaya tidak berubah saat render
  const fontsReady = ('fonts' in document && document.fonts?.ready) ? document.fonts.ready : Promise.resolve();

  return fontsReady.then(run).finally(() => {
    document.body.classList.remove('capture-mode');
    style.remove();
  });
}

// ---- TANGKAP KALENDER DENGAN SKALA TINGGI ----
async function captureCalendarCanvas() {
  const el = document.getElementById('calendar');
  if (!el) throw new Error('#calendar not found');

  // paksa kalender resize biar lebarnya maksimal
  if (window.calendar?.updateSize) window.calendar.updateSize();

  // pakai ukuran scroll sebenarnya + scale tinggi untuk ketajaman
  const canvas = await html2canvas(el, {
    scale: 3,                // <= naikin kalau masih kurang tajam (maks 4)
    useCORS: true,
    backgroundColor: '#ffffff',
    width: el.scrollWidth,
    height: el.scrollHeight,
    scrollX: 0,
    scrollY: 0
  });
  return canvas;
}

function currentCalLabel() {
  try {
    const d = (window.calendar && window.calendar.getDate()) ? window.calendar.getDate() : new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    return `${y}${m}`;
  } catch { return ''; }
}

// ---- DOWNLOAD PDF ----
async function downloadCalendarPDF() {
  await withCaptureMode(async () => {
    const canvas = await captureCalendarCanvas();
    const imgData = canvas.toDataURL('image/png');

    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

    const pageW = pdf.internal.pageSize.getWidth();
    const pageH = pdf.internal.pageSize.getHeight();

    // skala proporsional ke lebar halaman
    const imgWpx = canvas.width;
    const imgHpx = canvas.height;
    const ratio  = imgHpx / imgWpx;

    let w = pageW, h = w * ratio, x = 0, y = 0;

    // kalau tinggi melebihi halaman, skala berdasar tinggi
    if (h > pageH) {
      h = pageH;
      w = h / ratio;
      x = (pageW - w) / 2;
    } else {
      y = (pageH - h) / 2; // center vertical
    }

    pdf.addImage(imgData, 'PNG', x, y, w, h, undefined, 'FAST');
    pdf.save(`calendar-${currentCalLabel()}.pdf`);
  });
}

// ---- DOWNLOAD PNG ----
async function downloadCalendarPNG() {
  await withCaptureMode(async () => {
    const canvas = await captureCalendarCanvas();
    const link = document.createElement('a');
    link.href = canvas.toDataURL('image/png');
    link.download = `calendar-${currentCalLabel()}.png`;
    document.body.appendChild(link);
    link.click();
    link.remove();
  });
}

// Tombol yang kamu punya (kalau pakai 1 tombol saja)
document.getElementById('printCalBtn')?.addEventListener('click', () => {
  downloadCalendarPDF().catch(err => {
    console.error(err);
    alert('Failed to generate PDF.');
  });
});


// Wire up buttons
document.addEventListener('click', function(e){
  if (e.target && e.target.id === 'dlPdfBtn') {
    downloadCalendarPDF().catch(err => {
      console.error(err); alert('Failed to generate PDF.');
    });
  }
  if (e.target && e.target.id === 'dlPngBtn') {
    downloadCalendarPNG().catch(err => {
      console.error(err); alert('Failed to generate PNG.');
    });
  }
});


$(function(){
  try {
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');


    let rotateTimer = null;
    let refreshTimer = null;
    let calendar = null;
    let currentDetailData = null;
    let silentRefresh = false;

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

     $('#f_date_in_from').on('change', function() {
      console.log('Date changed, auto-filtering...');
      fetchData();
    });

    // TomSelect dropdowns auto-filter
    const filterSelectors = [
      '#f_assign_by_id',
      '#f_assign_to_id',
      '#f_type_label',
      '#f_company_id',
      '#f_pic_name',
      '#f_product_id',
      '#f_status'
    ];

    filterSelectors.forEach(selector => {
      const element = document.querySelector(selector);
      if (element && element.tomselect) {
        element.tomselect.on('change', function(value) {
          console.log(`${selector} changed to:`, value);
          fetchData();
        });
      }
    });

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

   function statusBadge(status, overdue){
  let s = (status || '').trim();
  if (!s) s = 'Pending';

  // Check if status is in a closed state
  const sLower = s.toLowerCase();
  const closedStates = ['completed', 'done', 'cancelled', 'expired'];
  const isClosed = closedStates.includes(sLower);

  // If overdue and status is still open, display Expired in UI
  if (overdue && !isClosed) {
    s = 'Expired';
  }

  const cls = (s === 'Expired') ? 'badge-expired' : '';
  const safe = String(s).replace(/\s/g,'\\ ');
  return `<span class="badge badge-animated ${safe} ${cls}">${s}</span>`;
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
    window.fmt = fmt;




function rowHtml(r){
  // Check if task is overdue
  const sLower = String(r.status || '').toLowerCase();
  const closedStates = ['completed', 'done', 'cancelled'];
  const isClosed = closedStates.includes(sLower);
  const overdue = isOverdue(r.deadline) && !isClosed;

  const btnDelete = r.can_delete
    ? `<button type="button" class="btn btn-danger btnDelete magnetic" data-id="${r.id}">
         <span class="btn-content">Delete</span>
       </button>`
    : '<span style="color: var(--muted);">-</span>';

  // Build row with inline styles for overdue
  const rowStyle = overdue
    ? 'style="background-color: #fef2f2 !important;"'
    : '';

  const cellStyle = overdue
    ? 'style="background-color: #fef2f2 !important; color: #7f1d1d !important; font-weight: 600 !important;"'
    : '';

return `
  <tr data-id="${r.id}"
      data-can-update="${r.can_update ? '1' : '0'}"
      class="${silentRefresh ? '' : 'table-row-animated'} ${overdue ? 'row-overdue' : ''}"
      ${rowStyle}>
    <td ${cellStyle}>${fmt(r.date_in)}</td>
    <td ${cellStyle}>${fmt(r.deadline)}</td>
    <td ${cellStyle}>${r.assign_by_id ?? '-'}</td>
    <td ${cellStyle}>${r.assign_to_id ?? '-'}</td>
    <td ${cellStyle}>${r.company_name ?? r.company_id ?? '-'}</td>
    <td ${cellStyle}>${r.pic_name ?? '-'}</td>
    <td ${cellStyle}>${r.product_name ?? r.product_id ?? '-'}</td>
    <td ${cellStyle}>${r.task ?? r.task_name ?? r.task_id ?? '-'}</td>
    <td ${cellStyle} class="truncate max-w-200" title="${(r.remarks||'').replace(/"/g,'&quot;')}">${r.remarks ?? '-'}</td>
    <td ${cellStyle}>${uiTypeLabel(r.type_label)}</td>
    <td ${cellStyle}>${statusBadge(r.status, overdue)}</td>
    <td ${cellStyle}>${btnDelete}</td>
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
        updateTableCounter(0);
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
      updateTableCounter(slice.length);
    }

    function rotate(){
      if(hasAnyFilter() || allData.length <= WINDOW_SIZE) return;
      windowStart = (windowStart + WINDOW_SIZE) % allData.length;
      renderWindow();

    }

    function sortNewestFirst(arr){
  const key = r => (
    r.created_at ? new Date(r.created_at).getTime()
    : r.updated_at ? new Date(r.updated_at).getTime()
    : (parseInt(r.id, 10) || 0)
  );
  arr.sort((a, b) => key(b) - key(a)); // newest first
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
// expose ke global, supaya dipakai oleh kode Preview di atas
window.getFilters = getFilters;


    function fetchData({resetWindow=true, silent=false} = {}){
        silentRefresh = !!silent;
      const params = new URLSearchParams(getFilters()).toString();
      return fetch(`{{ route('dashboard.items.list') }}?` + params, {
        headers: { 'X-CSRF-TOKEN': CSRF }
      })
        .then(r => r.json())
        .then(j => {
  // Filter out completed items from main table
  const rawData = j.data || [];
  allData = rawData.filter(item => {
    const status = (item.status || '').toLowerCase();
    return status !== 'completed';
  });
  sortNewestFirst(allData);
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
      rotateTimer = setInterval(rotate, 8000);
    }

    function startAutoRefresh(){
      if(refreshTimer) clearInterval(refreshTimer);
      refreshTimer = setInterval(()=>fetchData({resetWindow:false, silent:true}), 3000);
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
              <div class="detail-value">${uiTypeLabel(d.type_label)}</div>
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

   function isOverdue(deadline){
  if (!deadline) return false;

  // Handle both ISO strings and Date objects
  let d = (deadline instanceof Date) ? new Date(deadline) : new Date(String(deadline));
  if (Number.isNaN(d.getTime())) {
    const m = String(deadline).match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (m) d = new Date(`${m[1]}-${m[2]}-${m[3]}T00:00:00`);
  }
  if (Number.isNaN(d.getTime())) return false;

  // Set to end of day (23:59:59.999)
  d.setHours(23, 59, 59, 999);
  return d.getTime() < Date.now();
}

 function initCalendar(){
  const el = document.getElementById('calendar');
  if (!el) return;

  calendar = new FullCalendar.Calendar(el, {
    initialView: 'dayGridMonth',
    height: 'auto',
    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

    // Fetch events from server
    events: (info, success, failure) => {
      const params = new URLSearchParams(getFilters()).toString();
      fetch(`{{ route('dashboard.items.events') }}?` + params, {
        headers: { 'X-CSRF-TOKEN': CSRF }
      })
        .then(r => r.json())
        .then(data => success(data))
        .catch(err => failure(err));
    },

    // Map status to CSS classes for color coding
   eventClassNames: function(arg){
  const s = (arg.event.extendedProps.status || '').toLowerCase();
  const end = arg.event.end || arg.event.start;

  // Check if event is overdue
  const closedStates = ['completed', 'done', 'cancelled'];
  const isClosed = closedStates.includes(s);
  const over = end ? (new Date(end) < new Date()) : false;

  // If overdue and not closed, show as expired
  if (over && !isClosed) {
    return ['ev-status-expired'];
  }

  // Standard status colors
  if (s === 'expired')     return ['ev-status-expired'];
  if (s === 'completed')   return ['ev-status-completed'];
  if (s === 'in progress') return ['ev-status-in-progress'];
  if (s === 'pending')     return ['ev-status-pending'];
  return [];
},


    // PLAN B: Direct inline styling (bypasses CSS entirely)
    eventDidMount: function(info){
      const s = (info.event.extendedProps.status || '').toLowerCase();
      const el = info.el;

      if (s === 'expired'){
        el.style.backgroundColor = '#fee2e2';
        el.style.borderColor = '#fca5a5';
        el.style.color = '#991b1b';
      } else if (s === 'completed'){
        el.style.backgroundColor = '#dcfce7';
        el.style.borderColor = '#86efac';
        el.style.color = '#14532d';
      } else if (s === 'in progress'){
        el.style.backgroundColor = '#fef9c3';
        el.style.borderColor = '#fde68a';
        el.style.color = '#713f12';
      } else if (s === 'pending'){
        el.style.backgroundColor = '#e0f2fe';
        el.style.borderColor = '#bae6fd';
        el.style.color = '#0c4a6e';
      }
    },

    // Handle event clicks
    eventClick: function(info){
      const id = info.event.id;
      if (id) openDetail(id);
    }
  });

  calendar.render();
  window.calendar = calendar;
}

// BOOT SEQUENCE - Initialize everything on page load
initCalendar();
fetchData().then(() => {
  startRotation();
  startAutoRefresh();
}).catch(err => {
  console.error('Boot error:', err);
});

  } catch (e) {
    console.error('Main error:', e);
  }
}); // End of $(function(){...})
</script>
@endsection

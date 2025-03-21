@extends('layouts.main')

@section('title', 'Data Revenue Account Manager')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/revenue.css') }}">
<!-- Font Awesome untuk ikon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="main-content">
    <!-- Header Dashboard -->
    <div class="header-dashboard">
        <h1 class="header-title">
            Data Revenue Account Manager <span class="ms-2">📊</span>
        </h1>
        <p class="header-subtitle">
            Kelola dan monitoring data pendapatan Account Manager Telkom
        </p>
    </div>

    <!-- Welcome Section & Export Button -->
    <div class="dashboard-card">
        <div class="welcome-section">
            <div class="welcome-text">
                Selamat datang, {{ $user->name ?? 'User' }}!
            </div>
            <div class="welcome-actions">
                <button class="btn-export me-2">
                    <i class="fas fa-download"></i> Export Data
                </button>
                <button class="btn-import" data-bs-toggle="modal" data-bs-target="#importRevenueModal">
                    <i class="fas fa-upload"></i> Import Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Snackbar untuk notifikasi -->
    <div id="snackbar"></div>

    <!-- Error Message Display -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <p class="mb-0"><i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <p class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <p class="mb-0"><i class="fas fa-check-circle me-2"></i> {{ session('success') }}</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Form Tambah Data Revenue -->
    <div class="dashboard-card">
        <div class="card-header">
            <div>
                <h5 class="card-title">Tambah Data Revenue</h5>
                <p class="text-muted small mb-0">Tambahkan data revenue baru untuk Account Manager</p>
            </div>
        </div>
        <div class="form-section">
            <form action="{{ route('revenue.store') }}" method="POST" id="revenueForm">
                @csrf
                <div class="form-row">
                    <div class="form-group form-col-6">
                        <!-- Nama Account Manager -->
                        <label for="account_manager" class="form-label">Nama Account Manager</label>
                        <div class="position-relative">
                            <input type="text" id="account_manager" class="form-control" placeholder="Cari Account Manager..." required>
                            <input type="hidden" name="account_manager_id" id="account_manager_id">
                            <div id="account_manager_suggestions" class="suggestions-container"></div>
                        </div>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#addAccountManagerModal" class="add-link">
                            <i class="fas fa-plus-circle"></i> Tambah Account Manager Baru
                        </a>
                    </div>
                    <div class="form-group form-col-6">
                        <!-- Nama Corporate Customer -->
                        <label for="corporate_customer" class="form-label">Nama Corporate Customer</label>
                        <div class="position-relative">
                            <input type="text" id="corporate_customer" class="form-control" placeholder="Cari Corporate Customer..." required>
                            <input type="hidden" name="corporate_customer_id" id="corporate_customer_id">
                            <div id="corporate_customer_suggestions" class="suggestions-container"></div>
                        </div>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#addCorporateCustomerModal" class="add-link">
                            <i class="fas fa-plus-circle"></i> Tambah Corporate Customer Baru
                        </a>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-col-4">
                        <!-- Target Revenue -->
                        <label for="target_revenue" class="form-label">Target Revenue</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="target_revenue" id="target_revenue" placeholder="Masukkan target revenue" required>
                        </div>
                    </div>
                    <div class="form-group form-col-4">
                        <!-- Real Revenue -->
                        <label for="real_revenue" class="form-label">Real Revenue</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="real_revenue" id="real_revenue" placeholder="Masukkan real revenue" required>
                        </div>
                    </div>
                    <div class="form-group form-col-4">
                        <!-- Bulan Capaian -->
                        <label for="month_year_picker" class="form-label">Bulan Capaian</label>
                        <div class="month-picker-container">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="text" id="month_year_picker" class="form-control input-date" placeholder="Pilih Bulan dan Tahun" readonly>
                            </div>
                            <input type="hidden" name="bulan_month" id="bulan_month" value="{{ date('m') }}">
                            <input type="hidden" name="bulan_year" id="bulan_year" value="{{ date('Y') }}">
                            <input type="hidden" name="bulan" id="bulan" value="{{ date('Y-m') }}">
                            <div id="month_picker" class="month-picker">
                                <div class="month-picker-header">
                                    <div class="year-selector">
                                        <button type="button" id="prev_year"><i class="fas fa-chevron-left"></i></button>
                                        <span id="current_year">{{ date('Y') }}</span>
                                        <button type="button" id="next_year"><i class="fas fa-chevron-right"></i></button>
                                    </div>
                                </div>
                                <div class="month-grid" id="month_grid">
                                    <!-- Month items will be populated by JS -->
                                </div>
                                <div class="month-picker-footer">
                                    <button type="button" class="cancel" id="cancel_month">BATAL</button>
                                    <button type="button" class="apply" id="apply_month">PILIH</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Raw Data RLEGS Telkom -->
    <div class="dashboard-card">
        <div class="card-header">
            <div>
                <h5 class="card-title">Raw Data RLEGS Telkom</h5>
                <p class="text-muted small mb-0">Data revenue lengkap Account Manager Telkom</p>
            </div>
            <div class="d-flex">
                <div class="search-box me-2">
                    <form action="{{ route('revenue.data') }}" method="GET" class="search-input">
                        <div class="input-group">
                            <input class="form-control" type="search" name="search" placeholder="Cari data..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <button class="btn btn-light" id="filterToggle">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>

        <!-- Filter Area (Collapsed by default) -->
        <div class="tab-content p-3 border-bottom" id="filterArea" style="display:none;">
            <form action="{{ route('revenue.data') }}" method="GET">
                <div class="form-row">
                    <div class="form-group form-col-4">
                        <label class="form-label small">Witel</label>
                        <select name="witel" class="form-control">
                            <option value="">Semua Witel</option>
                            @foreach($witels as $witel)
                            <option value="{{ $witel->id }}" {{ request('witel') == $witel->id ? 'selected' : '' }}>
                                {{ $witel->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group form-col-4">
                        <label class="form-label small">Bulan</label>
                        <select name="month" class="form-control">
                            <option value="">Semua Bulan</option>
                            @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group form-col-4">
                        <label class="form-label small">Tahun</label>
                        <select name="year" class="form-control">
                            <option value="">Semua Tahun</option>
                            @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    <a href="{{ route('revenue.data') }}" class="btn btn-light me-2">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab Menu untuk Tabel Data -->
        <div class="tab-menu-container">
            <ul class="tabs">
                <li class="tab-item active" data-tab="revenueTab"><i class="fas fa-chart-line me-2"></i> Revenue Data</li>
                <li class="tab-item" data-tab="amTab"><i class="fas fa-user-tie me-2"></i> Account Manager</li>
                <li class="tab-item" data-tab="ccTab"><i class="fas fa-building me-2"></i> Corporate Customer</li>
            </ul>
        </div>

        <!-- Tab Content untuk Revenue -->
        <div id="revenueTab" class="tab-content active">
            @if($revenues->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <p class="empty-state-text">Belum ada data revenue tersedia.</p>
                </div>
            @else
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Nama AM</th>
                                    <th>Nama Customer</th>
                                    <th>Target Revenue</th>
                                    <th>Real Revenue</th>
                                    <th>Achievement</th>
                                    <th>Bulan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revenues as $revenue)
                                @php
                                    $achievement = $revenue->target_revenue > 0
                                        ? round(($revenue->real_revenue / $revenue->target_revenue) * 100, 1)
                                        : 0;

                                    $statusClass = $achievement >= 100
                                        ? 'bg-success-soft'
                                        : ($achievement >= 80 ? 'bg-warning-soft' : 'bg-danger-soft');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('img/profile.png') }}" class="am-profile-pic" alt="{{ $revenue->accountManager->nama }}">
                                            <span class="ms-2">{{ $revenue->accountManager->nama }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $revenue->corporateCustomer->nama }}</td>
                                    <td>Rp {{ number_format($revenue->target_revenue, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($revenue->real_revenue, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="status-badge {{ $statusClass }}">
                                            {{ $achievement }}%
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($revenue->bulan . '-01')->format('F Y') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('revenue.edit', $revenue->id) }}" class="action-btn edit-btn" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('revenue.destroy', $revenue->id) }}" method="POST" style="display:inline;" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete-btn" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($revenues->hasPages())
                    <div class="pagination-container">
                        <ul class="pagination">
                            <!-- Previous Page Link -->
                            @if($revenues->onFirstPage())
                                <li class="pagination-item">
                                    <span class="pagination-link" aria-disabled="true">
                                        <i class="fas fa-chevron-left"></i> Prev
                                    </span>
                                </li>
                            @else
                                <li class="pagination-item">
                                    <a href="{{ $revenues->previousPageUrl() }}" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i> Prev
                                    </a>
                                </li>
                            @endif

                            <!-- Pagination Elements -->
                            @foreach($revenues->getUrlRange(1, $revenues->lastPage()) as $page => $url)
                                @if($page == $revenues->currentPage())
                                    <li class="pagination-item">
                                        <span class="pagination-link active">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="pagination-item">
                                        <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            <!-- Next Page Link -->
                            @if($revenues->hasMorePages())
                                <li class="pagination-item">
                                    <a href="{{ $revenues->nextPageUrl() }}" class="pagination-link">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="pagination-item">
                                    <span class="pagination-link" aria-disabled="true">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                        <div class="pagination-info">
                            Showing {{ $revenues->firstItem() }} to {{ $revenues->lastItem() }} of {{ $revenues->total() }} results
                        </div>
                    </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Tab Content untuk Account Manager -->
        <div id="amTab" class="tab-content">
            @if($accountManagers->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <p class="empty-state-text">Belum ada data Account Manager tersedia.</p>
                </div>
            @else
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>Witel</th>
                                    <th>Divisi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accountManagers as $am)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('img/profile.png') }}" class="am-profile-pic" alt="{{ $am->nama }}">
                                            <span class="ms-2">{{ $am->nama }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $am->nik }}</td>
                                    <td>{{ $am->witel->nama }}</td>
                                    <td>{{ $am->divisi->nama }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('account_manager.edit', $am->id) }}" class="action-btn edit-btn" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('account_manager.destroy', $am->id) }}" method="POST" style="display:inline;" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete-btn" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Tab Content untuk Corporate Customer -->
        <div id="ccTab" class="tab-content">
            @if($corporateCustomers->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <p class="empty-state-text">Belum ada data Corporate Customer tersedia.</p>
                </div>
            @else
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>NIPNAS</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($corporateCustomers as $cc)
                                <tr>
                                    <td>{{ $cc->nama }}</td>
                                    <td>{{ $cc->nipnas }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('corporate_customer.edit', $cc->id) }}" class="action-btn edit-btn" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('corporate_customer.destroy', $cc->id) }}" method="POST" style="display:inline;" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete-btn" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

<!-- Modal Tambah Account Manager -->
<div class="modal fade" id="addAccountManagerModal" tabindex="-1" aria-labelledby="addAccountManagerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addAccountManagerModalLabel"><i class="fas fa-user-plus me-2"></i> Tambah Account Manager Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Tab Menu di Modal -->
        <div class="tab-menu-container">
            <ul class="tabs">
                <li class="tab-item active" data-tab="formTabAM"><i class="fas fa-edit me-2"></i> Form Manual</li>
                <li class="tab-item" data-tab="importTabAM"><i class="fas fa-file-import me-2"></i> Import Excel</li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div id="formTabAM" class="tab-content active">
            <form id="amForm" action="{{ route('account_manager.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nama" class="form-label">Nama Account Manager</label>
                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan Nama Account Manager" required>
                </div>
                <div class="form-group">
                    <label for="nik" class="form-label">Nomor Induk Karyawan</label>
                    <input type="text" id="nik" name="nik" class="form-control" placeholder="Masukkan 5 digit Nomor Induk Karyawan" pattern="^\d{5}$" required>
                </div>
                <div class="form-group">
                    <label for="witel_id" class="form-label">Witel</label>
                    <select name="witel_id" id="witel_id" class="form-control" required>
                        <option value="">Pilih Witel</option>
                        @foreach($witels as $witel)
                            <option value="{{ $witel->id }}">{{ $witel->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="divisi_id" class="form-label">Divisi</label>
                    <select name="divisi_id" id="divisi_id" class="form-control" required>
                        <option value="">Pilih Divisi</option>
                        @foreach($divisi as $div)
                            <option value="{{ $div->id }}">{{ $div->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab untuk Import Excel -->
        <div id="importTabAM" class="tab-content">
            <form id="amImportForm" action="{{ route('account_manager.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file_upload_am" class="form-label">Unggah File Excel</label>
                    <input type="file" name="file" id="file_upload_am" accept=".xlsx, .xls, .csv" required class="form-control">
                </div>
                <div class="mt-3 d-flex">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i> Unggah Data
                    </button>
                    <a href="{{ route('account_manager.template') }}" class="btn btn-light ms-2">
                        <i class="fas fa-download me-2"></i> Unduh Template
                    </a>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Corporate Customer -->
<div class="modal fade" id="addCorporateCustomerModal" tabindex="-1" aria-labelledby="addCorporateCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCorporateCustomerModalLabel"><i class="fas fa-building me-2"></i> Tambah Corporate Customer Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Tab Menu di Modal -->
        <div class="tab-menu-container">
            <ul class="tabs">
                <li class="tab-item active" data-tab="formTabCC"><i class="fas fa-edit me-2"></i> Form Manual</li>
                <li class="tab-item" data-tab="importTabCC"><i class="fas fa-file-import me-2"></i> Import Excel</li>
            </ul>
        </div>

        <!-- Tab Content untuk Form Manual -->
        <div id="formTabCC" class="tab-content active">
            <form id="ccForm" action="{{ route('corporate_customer.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nama_customer" class="form-label">Nama Corporate Customer</label>
                    <input type="text" name="nama" id="nama_customer" class="form-control" placeholder="Masukkan Nama Corporate Customer" required>
                </div>
                <div class="form-group">
                    <label for="nipnas" class="form-label">NIPNAS</label>
                    <input type="number" name="nipnas" id="nipnas" class="form-control" placeholder="Masukkan NIPNAS (maksimal 7 digit)" max="9999999" required>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab untuk Import Excel -->
        <div id="importTabCC" class="tab-content">
            <form id="ccImportForm" action="{{ route('corporate_customer.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file_upload_cc" class="form-label">Unggah File Excel</label>
                    <input type="file" name="file" id="file_upload_cc" accept=".xlsx, .xls, .csv" required class="form-control">
                </div>
                <div class="mt-3 d-flex">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i> Unggah Data
                    </button>
                    <a href="{{ route('corporate_customer.template') }}" class="btn btn-light ms-2">
                        <i class="fas fa-download me-2"></i> Unduh Template
                    </a>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Import Revenue -->
<div class="modal fade" id="importRevenueModal" tabindex="-1" aria-labelledby="importRevenueModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importRevenueModalLabel"><i class="fas fa-file-import me-2"></i> Import Data Revenue</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="revenueImportForm" action="{{ route('revenue.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="file_upload_revenue" class="form-label">Unggah File Excel</label>
                <input type="file" name="file" id="file_upload_revenue" accept=".xlsx, .xls, .csv" required class="form-control">
            </div>

            <div class="alert alert-info mt-3">
                <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i> Catatan Format Excel</h6>
                <p class="mb-2 small">Format Excel harus sesuai dengan template dan memiliki kolom-kolom:</p>
                <ul class="small mb-0">
                    <li>account_manager: Nama Account Manager</li>
                    <li>corporate_customer: Nama Corporate Customer</li>
                    <li>target_revenue: Target Revenue (angka)</li>
                    <li>real_revenue: Real Revenue (angka)</li>
                    <li>bulan: Format MM/YYYY (contoh: 01/2025)</li>
                </ul>
            </div>

            <div class="d-flex mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i> Unggah Data
                </button>
                <a href="{{ route('revenue.template') }}" class="btn btn-light ms-2">
                    <i class="fas fa-download me-2"></i> Unduh Template
                </a>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab Navigation
        const tabs = document.querySelectorAll('.tab-item');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabsContainer = tab.closest('.tab-menu-container');
                const parentContainer = tabsContainer.parentElement;
                let contentContainer;

                // Check if parent is modal body, or use parent container
                if (parentContainer.classList.contains('modal-body')) {
                    contentContainer = parentContainer;
                } else {
                    contentContainer = tabsContainer.parentElement;
                }

                // Remove active class from all tabs in this container
                tabsContainer.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));

                // Add active class to clicked tab
                tab.classList.add('active');

                // Hide all tab contents in this container
                contentContainer.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });

                // Show the selected tab content
                const targetId = tab.getAttribute('data-tab');
                const targetContent = contentContainer.querySelector(`#${targetId}`);
                if (targetContent) targetContent.classList.add('active');
            });
        });

        // Month Picker
        const monthYearPicker = document.getElementById('month_year_picker');
        const monthPicker = document.getElementById('month_picker');
        const monthGrid = document.getElementById('month_grid');
        const currentYearElement = document.getElementById('current_year');

        if (monthYearPicker) {
            // Initialize current date
            const now = new Date();
            let currentYear = now.getFullYear();
            let selectedMonth = now.getMonth();

            // Month names
            const monthNames = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            // Initial value
            monthYearPicker.value = `${monthNames[selectedMonth]} ${currentYear}`;

            // Update hidden inputs
            document.getElementById('bulan_month').value = String(selectedMonth + 1).padStart(2, '0');
            document.getElementById('bulan_year').value = currentYear;
            document.getElementById('bulan').value = `${currentYear}-${String(selectedMonth + 1).padStart(2, '0')}`;

            // Create month grid
            function renderMonthGrid() {
                monthGrid.innerHTML = '';
                for (let i = 0; i < 12; i++) {
                    const monthItem = document.createElement('div');
                    monthItem.className = 'month-item';
                    if (i === selectedMonth) {
                        monthItem.classList.add('selected');
                    }
                    monthItem.textContent = monthNames[i];
                    monthItem.dataset.month = i;

                    monthItem.addEventListener('click', () => {
                        document.querySelectorAll('.month-item').forEach(item => {
                            item.classList.remove('selected');
                        });
                        monthItem.classList.add('selected');
                        selectedMonth = i;
                    });

                    monthGrid.appendChild(monthItem);
                }
            }

            // Show month picker on click
            monthYearPicker.addEventListener('click', function() {
                monthPicker.style.display = 'block';
                renderMonthGrid();
                if (currentYearElement) {
                    currentYearElement.textContent = currentYear;
                }
            });

            // Year navigation
            document.getElementById('prev_year').addEventListener('click', function() {
                currentYear--;
                currentYearElement.textContent = currentYear;
                renderMonthGrid();
            });

            document.getElementById('next_year').addEventListener('click', function() {
                currentYear++;
                currentYearElement.textContent = currentYear;
                renderMonthGrid();
            });

            // Cancel button
            document.getElementById('cancel_month').addEventListener('click', function() {
                monthPicker.style.display = 'none';
            });

            // Apply button
            document.getElementById('apply_month').addEventListener('click', function() {
                monthYearPicker.value = `${monthNames[selectedMonth]} ${currentYear}`;

                // Update hidden inputs
                document.getElementById('bulan_month').value = String(selectedMonth + 1).padStart(2, '0');
                document.getElementById('bulan_year').value = currentYear;
                document.getElementById('bulan').value = `${currentYear}-${String(selectedMonth + 1).padStart(2, '0')}`;

                monthPicker.style.display = 'none';
            });

            // Close month picker when clicking outside
            document.addEventListener('click', function(e) {
                if (!monthYearPicker.contains(e.target) && !monthPicker.contains(e.target)) {
                    monthPicker.style.display = 'none';
                }
            });
        }

        // Toggle filter area
        const filterToggle = document.getElementById('filterToggle');
        const filterArea = document.getElementById('filterArea');

        if (filterToggle && filterArea) {
            filterToggle.addEventListener('click', function() {
                if (filterArea.style.display === 'none') {
                    filterArea.style.display = 'block';
                    filterToggle.classList.add('active');
                } else {
                    filterArea.style.display = 'none';
                    filterToggle.classList.remove('active');
                }
            });
        }

        // Confirm delete
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    this.submit();
                }
            });
        });

        // Account Manager Suggestions (fix for 404 error)
        const accountManagerInput = document.getElementById('account_manager');
        const accountManagerIdInput = document.getElementById('account_manager_id');
        const accountManagerSuggestions = document.getElementById('account_manager_suggestions');

        if (accountManagerInput) {
            accountManagerInput.addEventListener('input', function() {
                const search = this.value.trim();

                if (search.length < 2) {
                    accountManagerSuggestions.innerHTML = '';
                    accountManagerSuggestions.style.display = 'none';
                    return;
                }

                // Fix: Use route('revenue.searchAccountManager') instead of route that doesn't exist
                fetch('/search-am?search=' + encodeURIComponent(search))
                    .then(response => response.json())
                    .then(data => {
                        accountManagerSuggestions.innerHTML = '';

                        if (data.length === 0) {
                            const noResult = document.createElement('div');
                            noResult.className = 'suggestion-item';
                            noResult.textContent = 'Tidak ada hasil yang ditemukan';
                            accountManagerSuggestions.appendChild(noResult);
                        } else {
                            data.forEach(am => {
                                const item = document.createElement('div');
                                item.className = 'suggestion-item';
                                item.textContent = `${am.nama} - ${am.nik || 'NIK tidak tersedia'}`;

                                item.addEventListener('click', () => {
                                    accountManagerInput.value = am.nama;
                                    accountManagerIdInput.value = am.id;
                                    accountManagerSuggestions.style.display = 'none';
                                });

                                accountManagerSuggestions.appendChild(item);
                            });
                        }

                        accountManagerSuggestions.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error fetching account managers:', error);

                        accountManagerSuggestions.innerHTML = '';
                        const errorItem = document.createElement('div');
                        errorItem.className = 'suggestion-item text-danger';
                        errorItem.textContent = 'Error: Tidak dapat memuat data';
                        accountManagerSuggestions.appendChild(errorItem);
                        accountManagerSuggestions.style.display = 'block';
                    });
            });
        }

        // Corporate Customer Suggestions (fix for 404 error)
        const corporateCustomerInput = document.getElementById('corporate_customer');
        const corporateCustomerIdInput = document.getElementById('corporate_customer_id');
        const corporateCustomerSuggestions = document.getElementById('corporate_customer_suggestions');

        if (corporateCustomerInput) {
            corporateCustomerInput.addEventListener('input', function() {
                const search = this.value.trim();

                if (search.length < 2) {
                    corporateCustomerSuggestions.innerHTML = '';
                    corporateCustomerSuggestions.style.display = 'none';
                    return;
                }

                // Fix: Use route('revenue.searchCorporateCustomer') instead of route that doesn't exist
                fetch('/search-customer?search=' + encodeURIComponent(search))
                    .then(response => response.json())
                    .then(data => {
                        corporateCustomerSuggestions.innerHTML = '';

                        if (data.length === 0) {
                            const noResult = document.createElement('div');
                            noResult.className = 'suggestion-item';
                            noResult.textContent = 'Tidak ada hasil yang ditemukan';
                            corporateCustomerSuggestions.appendChild(noResult);
                        } else {
                            data.forEach(cc => {
                                const item = document.createElement('div');
                                item.className = 'suggestion-item';
                                item.textContent = `${cc.nama} - NIPNAS: ${cc.nipnas || 'Tidak tersedia'}`;

                                item.addEventListener('click', () => {
                                    corporateCustomerInput.value = cc.nama;
                                    corporateCustomerIdInput.value = cc.id;
                                    corporateCustomerSuggestions.style.display = 'none';
                                });

                                corporateCustomerSuggestions.appendChild(item);
                            });
                        }

                        corporateCustomerSuggestions.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error fetching corporate customers:', error);

                        corporateCustomerSuggestions.innerHTML = '';
                        const errorItem = document.createElement('div');
                        errorItem.className = 'suggestion-item text-danger';
                        errorItem.textContent = 'Error: Tidak dapat memuat data';
                        corporateCustomerSuggestions.appendChild(errorItem);
                        corporateCustomerSuggestions.style.display = 'block';
                    });
            });
        }
    });
</script>
@endsection
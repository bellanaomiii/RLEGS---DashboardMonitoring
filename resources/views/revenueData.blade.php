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
            <div class="d-flex">
                <a href="{{ route('revenue.export') }}" class="btn-export me-2">
                    <i class="fas fa-download"></i> Export Data
                </a>
                <button class="btn-import" data-bs-toggle="modal" data-bs-target="#importRevenueModal">
                    <i class="fas fa-upload"></i> Import Excel
                </button>
            </div>
        </div>
        <div class="form-section">
            <form action="{{ route('revenue.store') }}" method="POST" id="revenueForm">
                @csrf
                <div class="form-row">
                    <div class="form-group form-col-6">
                        <!-- Nama Account Manager -->
                        <label for="account_manager" class="form-label"><strong>Nama Account Manager</strong></label>
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
                        <label for="corporate_customer" class="form-label"><strong>Nama Corporate Customer</strong></label>
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
                        <label for="target_revenue" class="form-label"><strong>Target Revenue</strong></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="target_revenue" id="target_revenue" placeholder="Masukkan target revenue" required>
                        </div>
                    </div>
                    <div class="form-group form-col-4">
                        <!-- Real Revenue -->
                        <label for="real_revenue" class="form-label"><strong>Real Revenue</strong></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="real_revenue" id="real_revenue" placeholder="Masukkan real revenue" required>
                        </div>
                    </div>
                    <div class="form-group form-col-4">
                        <!-- Bulan Capaian - Desain Modern -->
                        <label for="month_year_picker" class="form-label"><strong>Bulan Capaian</strong></label>
                        <div class="month-picker-container">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="text" id="month_year_picker" class="form-control input-date" placeholder="Pilih Bulan dan Tahun" readonly>
                                <span class="input-group-text cursor-pointer" id="open_month_picker"><i class="fas fa-chevron-down"></i></span>
                            </div>
                            <input type="hidden" name="bulan_month" id="bulan_month" value="{{ date('m') }}">
                            <input type="hidden" name="bulan_year" id="bulan_year" value="{{ date('Y') }}">
                            <input type="hidden" name="bulan" id="bulan" value="{{ date('Y-m') }}">
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
            <div class="d-flex align-items-center">
                <!-- Search Box - Diperbarui -->
                <div class="search-box me-2">
                    <div class="input-group">
                        <input class="form-control" type="search" id="globalSearch" placeholder="Cari data..." autocomplete="off" value="{{ request('search') }}">
                        <button class="btn btn-primary" type="button" id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <!-- Container untuk hasil pencarian -->
                    <div id="searchResultsContainer" class="search-results-container" style="display:none;">
                        <div class="search-results-content">
                            <div class="search-summary">
                                <p class="mb-0">Hasil pencarian untuk "<span id="search-term-display" class="fw-bold"></span>"</p>
                            </div>
                            <div id="search-results-loading" class="search-loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p class="mt-2 mb-0">Sedang mencari...</p>
                            </div>
                            <div id="search-results-content" style="display:none;">
                                <div class="p-3">
                                    <p class="mb-2 fw-bold">Ditemukan:</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-primary" id="total-am-count">AM: 0</span>
                                        <span class="badge bg-info" id="total-cc-count">CC: 0</span>
                                        <span class="badge bg-success" id="total-rev-count">Revenue: 0</span>
                                    </div>
                                    <p class="mt-2 mb-0 small text-muted">Hasil telah ditampilkan pada tab terkait</p>
                                </div>
                            </div>
                            <div id="search-no-results" style="display:none;" class="p-3">
                                <div class="text-center py-3">
                                    <i class="fas fa-search fa-2x mb-2 text-muted"></i>
                                    <p class="mb-0">Tidak ada hasil yang ditemukan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-light" id="filterToggle" style="height: 38px;">
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
                        <div class="select-container">
                            <select name="year" class="form-control custom-scroll">
                                <option value="">Semua Tahun</option>
                                @foreach($yearRange as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                                @endforeach
                            </select>
                        </div>
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


<!-- Deskripsi Pencarian/Filter - Tampilkan jika ada parameter pencarian atau filter -->
@if(request('search') || request('witel') || request('month') || request('year'))
<div class="search-description">
    <div class="d-flex align-items-center">
        <i class="fas fa-info-circle me-2"></i>
        <div>
            <strong>Menampilkan hasil:</strong>
            @if(request('search'))
                Pencarian "<span class="text-primary fw-bold">{{ request('search') }}</span>"
            @endif

            @if(request('witel'))
                @php
                    $witelInfo = $witels->where('id', request('witel'))->first();
                @endphp
                @if(request('search')) dengan @endif
                Filter Witel: <span class="text-primary fw-bold">{{ $witelInfo ? $witelInfo->nama : '' }}</span>
            @endif

            @if(request('month'))
                @if(request('search') || request('witel')) dan @endif
                Bulan: <span class="text-primary fw-bold">{{ date('F', mktime(0, 0, 0, request('month'), 1)) }}</span>
            @endif

            @if(request('year'))
                @if(request('search') || request('witel') || request('month')) dan @endif
                Tahun: <span class="text-primary fw-bold">{{ request('year') }}</span>
            @endif
        </div>
        <a href="{{ route('revenue.data') }}" class="btn btn-sm btn-light ms-auto">
            <i class="fas fa-times me-1"></i> Reset Filter
        </a>
    </div>
</div>
@endif

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
            <div id="revenue-search-empty" class="empty-search" style="display:none;">
                <i class="fas fa-chart-line"></i>
                <p>Tidak ada data revenue yang sesuai dengan pencarian "<span class="search-keyword"></span>"</p>
            </div>

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

                    <!-- Pagination yang Diperbarui -->
                    @if($revenues->hasPages())
                    <div class="pagination-container">
                        <ul class="pagination">
                            <!-- Previous Page Link -->
                            @if($revenues->onFirstPage())
                                <li class="pagination-item">
                                    <span class="pagination-link" aria-disabled="true">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="pagination-item">
                                    <a href="{{ $revenues->previousPageUrl() }}" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            <!-- Pagination Elements - Compact Version -->
                            @php
                                $currentPage = $revenues->currentPage();
                                $lastPage = $revenues->lastPage();
                                $range = 2; // Seberapa banyak nomor yang ditampilkan di kiri dan kanan halaman saat ini
                            @endphp

                            <!-- Tampilkan halaman pertama jika tidak dalam rentang awal -->
                            @if($currentPage > $range + 1)
                                <li class="pagination-item">
                                    <a href="{{ $revenues->url(1) }}" class="pagination-link">1</a>
                                </li>

                                <!-- Tampilkan ... jika ada celah -->
                                @if($currentPage > $range + 2)
                                    <li class="pagination-item">
                                        <span class="pagination-link">...</span>
                                    </li>
                                @endif
                            @endif

                            <!-- Tampilkan rentang halaman di sekitar halaman saat ini -->
                            @for($i = max(1, $currentPage - $range); $i <= min($lastPage, $currentPage + $range); $i++)
                                <li class="pagination-item">
                                    <a href="{{ $revenues->url($i) }}" class="pagination-link {{ $i == $currentPage ? 'active' : '' }}">
                                        {{ $i }}
                                    </a>
                                </li>
                            @endfor

                            <!-- Tampilkan halaman terakhir jika tidak dalam rentang akhir -->
                            @if($currentPage < $lastPage - $range)
                                <!-- Tampilkan ... jika ada celah -->
                                @if($currentPage < $lastPage - $range - 1)
                                    <li class="pagination-item">
                                        <span class="pagination-link">...</span>
                                    </li>
                                @endif

                                <li class="pagination-item">
                                    <a href="{{ $revenues->url($lastPage) }}" class="pagination-link">{{ $lastPage }}</a>
                                </li>
                            @endif

                            <!-- Next Page Link -->
                            @if($revenues->hasMorePages())
                                <li class="pagination-item">
                                    <a href="{{ $revenues->nextPageUrl() }}" class="pagination-link">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="pagination-item">
                                    <span class="pagination-link" aria-disabled="true">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                        <div class="pagination-info">
                            Menampilkan {{ $revenues->firstItem() }} sampai {{ $revenues->lastItem() }} dari {{ $revenues->total() }} hasil
                        </div>
                    </div>
                    @endif
                </div>
            @endif
        </div>

<!-- Tab Content untuk Account Manager -->
<div id="amTab" class="tab-content">
    <div id="am-search-empty" class="empty-search" style="display:none;">
        <i class="fas fa-user-tie"></i>
        <p>Tidak ada data Account Manager yang sesuai dengan pencarian "<span class="search-keyword"></span>"</p>
    </div>

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

<!-- Pagination untuk Account Manager - Diperbarui -->
@if(isset($accountManagers) && $accountManagers->hasPages())
<div class="pagination-container">
    <ul class="pagination">
        <!-- Previous Page Link -->
        @if($accountManagers->onFirstPage())
            <li class="pagination-item">
                <span class="pagination-link" aria-disabled="true">
                    <i class="fas fa-chevron-left"></i>
                </span>
            </li>
        @else
            <li class="pagination-item">
                <a href="{{ $accountManagers->previousPageUrl() }}" class="pagination-link">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        @endif

        <!-- Pagination Elements - Compact Version -->
        @php
            $currentPage = $accountManagers->currentPage();
            $lastPage = $accountManagers->lastPage();
            $range = 2; // Seberapa banyak nomor yang ditampilkan di kiri dan kanan halaman saat ini
        @endphp

        <!-- Tampilkan halaman pertama jika tidak dalam rentang awal -->
        @if($currentPage > $range + 1)
            <li class="pagination-item">
                <a href="{{ $accountManagers->url(1) }}" class="pagination-link">1</a>
            </li>

            <!-- Tampilkan ... jika ada celah -->
            @if($currentPage > $range + 2)
                <li class="pagination-item">
                    <span class="pagination-link">...</span>
                </li>
            @endif
        @endif

        <!-- Tampilkan rentang halaman di sekitar halaman saat ini -->
        @for($i = max(1, $currentPage - $range); $i <= min($lastPage, $currentPage + $range); $i++)
            <li class="pagination-item">
                <a href="{{ $accountManagers->url($i) }}" class="pagination-link {{ $i == $currentPage ? 'active' : '' }}">
                    {{ $i }}
                </a>
            </li>
        @endfor

        <!-- Tampilkan halaman terakhir jika tidak dalam rentang akhir -->
        @if($currentPage < $lastPage - $range)
            <!-- Tampilkan ... jika ada celah -->
            @if($currentPage < $lastPage - $range - 1)
                <li class="pagination-item">
                    <span class="pagination-link">...</span>
                </li>
            @endif

            <li class="pagination-item">
                <a href="{{ $accountManagers->url($lastPage) }}" class="pagination-link">{{ $lastPage }}</a>
            </li>
        @endif

        <!-- Next Page Link -->
        @if($accountManagers->hasMorePages())
            <li class="pagination-item">
                <a href="{{ $accountManagers->nextPageUrl() }}" class="pagination-link">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        @else
            <li class="pagination-item">
                <span class="pagination-link" aria-disabled="true">
                    <i class="fas fa-chevron-right"></i>
                </span>
            </li>
        @endif
    </ul>
    <div class="pagination-info">
        Menampilkan {{ $accountManagers->firstItem() }} sampai {{ $accountManagers->lastItem() }} dari {{ $accountManagers->total() }} hasil
    </div>
</div>
@endif
        </div>
    @endif
</div>

<!-- Tab Content untuk Corporate Customer -->
<div id="ccTab" class="tab-content">
    <div id="cc-search-empty" class="empty-search" style="display:none;">
        <i class="fas fa-building"></i>
        <p>Tidak ada data Corporate Customer yang sesuai dengan pencarian "<span class="search-keyword"></span>"</p>
    </div>

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

            <!-- Pagination untuk Corporate Customer - Diperbarui -->
            @if(isset($corporateCustomers) && $corporateCustomers->hasPages())
            <div class="pagination-container">
                <ul class="pagination">
                    <!-- Previous Page Link -->
                    @if($corporateCustomers->onFirstPage())
                        <li class="pagination-item">
                            <span class="pagination-link" aria-disabled="true">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        </li>
                    @else
                        <li class="pagination-item">
                            <a href="{{ $corporateCustomers->previousPageUrl() }}" class="pagination-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    @endif

                    <!-- Pagination Elements - Compact Version -->
                    @php
                        $currentPage = $corporateCustomers->currentPage();
                        $lastPage = $corporateCustomers->lastPage();
                        $range = 2; // Seberapa banyak nomor yang ditampilkan di kiri dan kanan halaman saat ini
                    @endphp

                    <!-- Tampilkan halaman pertama jika tidak dalam rentang awal -->
                    @if($currentPage > $range + 1)
                        <li class="pagination-item">
                            <a href="{{ $corporateCustomers->url(1) }}" class="pagination-link">1</a>
                        </li>

                        <!-- Tampilkan ... jika ada celah -->
                        @if($currentPage > $range + 2)
                            <li class="pagination-item">
                                <span class="pagination-link">...</span>
                            </li>
                        @endif
                    @endif

                    <!-- Tampilkan rentang halaman di sekitar halaman saat ini -->
                    @for($i = max(1, $currentPage - $range); $i <= min($lastPage, $currentPage + $range); $i++)
                        <li class="pagination-item">
                            <a href="{{ $corporateCustomers->url($i) }}" class="pagination-link {{ $i == $currentPage ? 'active' : '' }}">
                                {{ $i }}
                            </a>
                        </li>
                    @endfor

                    <!-- Tampilkan halaman terakhir jika tidak dalam rentang akhir -->
                    @if($currentPage < $lastPage - $range)
                        <!-- Tampilkan ... jika ada celah -->
                        @if($currentPage < $lastPage - $range - 1)
                            <li class="pagination-item">
                                <span class="pagination-link">...</span>
                            </li>
                        @endif

                        <li class="pagination-item">
                            <a href="{{ $corporateCustomers->url($lastPage) }}" class="pagination-link">{{ $lastPage }}</a>
                        </li>
                    @endif

                    <!-- Next Page Link -->
                    @if($corporateCustomers->hasMorePages())
                        <li class="pagination-item">
                            <a href="{{ $corporateCustomers->nextPageUrl() }}" class="pagination-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="pagination-item">
                            <span class="pagination-link" aria-disabled="true">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        </li>
                    @endif
                </ul>
                <div class="pagination-info">
                    Menampilkan {{ $corporateCustomers->firstItem() }} sampai {{ $corporateCustomers->lastItem() }} dari {{ $corporateCustomers->total() }} hasil
                </div>
            </div>
            @endif
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

    <!-- Month Picker Global (Outside Normal Flow) -->
    <div id="global_month_picker" class="month-picker">
        <div class="month-picker-header">
            <div class="year-selector">
                <button type="button" id="prev_year"><i class="fas fa-chevron-left"></i></button>
                <span id="current_year">{{ date('Y') }}</span>
                <button type="button" id="next_year"><i class="fas fa-chevron-right"></i></button>
            </div>
            <!-- Input tahun manual -->
            <div class="year-input-container mt-2">
                <input type="number" id="year_input" class="form-control form-control-sm" placeholder="Tahun" min="2000" max="2100">
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
@endsection


@section('scripts')
<!-- Load Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Load dashboard.js dengan versi baru untuk memastikan fresh load -->
<script src="{{ asset('js/dashboard.js?v=' . time()) }}"></script>
@endsection
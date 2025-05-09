@extends('layouts.main')

@section('title', 'Data Revenue Account Manager')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/revenue.css') }}">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- CSS tambahan untuk button group divisi -->
    <style>
        .divisi-btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .divisi-btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f8f9fa;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .divisi-btn.active {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .divisi-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            background-color: #f0f0f0;
            margin-right: 4px;
            font-size: 12px;
        }

        .btn-export {
            background-color: #0a1f44; 
            color: white !important; 
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
        }

        #filterToggle:hover {
            color: inherit; 
            background-color: #0a1f44; 
        }

        #filterToggle:hover .fas {
            color: white;
        }

    </style>
@endsection

@section('content')
    <div class="main-content">
        <!-- Header Dashboard -->
        <div class="header-dashboard">
            <h1 class="header-title">
                Data Revenue Account Manager 
            </h1>
            <p class="header-subtitle">
                Kelola dan Monitoring Data Pendapatan Account Manager RLEGS
            </p>
        </div>

        <!-- Snackbar untuk notifikasi -->
        <div id="snackbar"></div>

        <!-- Error Message Display -->
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <p class="mb-0"><i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <p class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <p class="mb-0"><i class="fas fa-check-circle me-2"></i> {{ session('success') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Form Tambah Data Revenue -->
        <!-- Form Tambah Data Revenue (dengan divisi yang dinamis) -->
        <div class="dashboard-card">
            <div class="card-header">
                <div>
                    <h5 class="card-title">Tambah Data Revenue</h5>
                    <p class="text-muted small mb-0">Tambahkan data revenue baru untuk Account Manager</p>
                </div>
                <div class="d-flex">
                    <a href="{{ route('revenue.export') }}" class="btn-export me-2">
                        <i class=" fas fa-download me-1"></i> Export Data
                    </a>
                    <button class="btn-import" data-bs-toggle="modal" data-bs-target="#importRevenueModal">
                        <i class=" fas fa-upload me-1"></i> Import Excel
                    </button>
                </div>
            </div>
            <div class="form-section">
                <form action="{{ route('revenue.store') }}" method="POST" id="revenueForm">
                    @csrf
                    <div class="form-row">
                        <div class="form-group form-col-4">
                            <!-- Nama Account Manager -->
                            <label for="account_manager" class="form-label"><strong>Nama Account Manager</strong></label>
                            <div class="position-relative">
                                <input type="text" id="account_manager" class="form-control"
                                    placeholder="Cari Account Manager..." required>
                                <input type="hidden" name="account_manager_id" id="account_manager_id">
                                <div id="account_manager_suggestions" class="suggestions-container"></div>
                            </div>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#addAccountManagerModal"
                                class="add-link">
                                <i class="fas fa-plus-circle"></i> Tambah Account Manager Baru
                            </a>
                        </div>

                        <div class="form-group form-col-4">
                            <!-- Divisi Account Manager -->
                            <label for="divisi_id" class="form-label"><strong>Divisi</strong></label>
                            <select id="divisi_id" name="divisi_id" class="form-control" required disabled>
                                <option value="">Pilih Divisi</option>
                                <!-- Options akan diisi melalui AJAX -->
                            </select>
                            <small class="text-muted">Pilih Account Manager terlebih dahulu</small>
                        </div>

                        <div class="form-group form-col-4">
                            <!-- Nama Corporate Customer -->
                            <label for="corporate_customer" class="form-label"><strong>Nama Corporate
                                    Customer</strong></label>
                            <div class="position-relative">
                                <input type="text" id="corporate_customer" class="form-control"
                                    placeholder="Cari Corporate Customer..." required>
                                <input type="hidden" name="corporate_customer_id" id="corporate_customer_id">
                                <div id="corporate_customer_suggestions" class="suggestions-container"></div>
                            </div>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#addCorporateCustomerModal"
                                class="add-link">
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
                                <input type="number" class="form-control" name="target_revenue" id="target_revenue"
                                    placeholder="Masukkan target revenue" required>
                            </div>
                        </div>
                        <div class="form-group form-col-4">
                            <!-- Real Revenue -->
                            <label for="real_revenue" class="form-label"><strong>Real Revenue</strong></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="real_revenue" id="real_revenue"
                                    placeholder="Masukkan real revenue" required>
                            </div>
                        </div>
                        <div class="form-group form-col-4">
                            <!-- Bulan Capaian - Desain Modern -->
                            <label for="month_year_picker" class="form-label"><strong>Bulan Capaian</strong></label>
                            <div class="month-picker-container">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" id="month_year_picker" class="form-control input-date"
                                        placeholder="Pilih Bulan dan Tahun" readonly>
                                    <span class="input-group-text cursor-pointer" id="open_month_picker"><i
                                            class="fas fa-chevron-down"></i></span>
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
                            <input class="form-control" type="search" id="globalSearch" placeholder="Cari data..."
                                autocomplete="off" value="{{ request('search') }}">
                            <button class="btn btn-primary px-3 py-1" type="button" id="searchButton" style="min-width: 5px;">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <!-- Container untuk hasil pencarian -->
                        <div id="searchResultsContainer" class="search-results-container" style="display:none;">
                            <div class="search-results-content">
                                <div class="search-summary">
                                    <p class="mb-0">Hasil pencarian untuk "<span id="search-term-display"
                                            class="fw-bold"></span>"</p>
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
                        <div class="form-group form-col-3">
                            <label class="form-label small">Witel</label>
                            <select name="witel" class="form-control">
                                <option value="">Semua Witel</option>
                                @foreach ($witels as $witel)
                                    <option value="{{ $witel->id }}"
                                        {{ request('witel') == $witel->id ? 'selected' : '' }}>
                                        {{ $witel->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group form-col-3">
                            <label class="form-label small">Regional</label>
                            <select name="regional" class="form-control">
                                <option value="">Semua Regional</option>
                                @foreach ($regionals as $regional)
                                    <option value="{{ $regional->id }}"
                                        {{ request('regional') == $regional->id ? 'selected' : '' }}>
                                        {{ $regional->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group form-col-3">
                            <label class="form-label small">Divisi</label>
                            <select name="divisi" class="form-control">
                                <option value="">Semua Divisi</option>
                                @foreach ($divisi as $div)
                                    <option value="{{ $div->id }}"
                                        {{ request('divisi') == $div->id ? 'selected' : '' }}>
                                        {{ $div->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group form-col-3">
                            <label class="form-label small">Bulan</label>
                            <select name="month" class="form-control">
                                <option value="">Semua Bulan</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group form-col-3">
                            <label class="form-label small">Tahun</label>
                            <div class="select-container">
                                <select name="year" class="form-control custom-scroll">
                                    <option value="">Semua Tahun</option>
                                    @foreach ($yearRange as $year)
                                        <option value="{{ $year }}"
                                            {{ request('year') == $year ? 'selected' : '' }}>
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
            @if (request('search') ||
                    request('witel') ||
                    request('regional') ||
                    request('divisi') ||
                    request('month') ||
                    request('year'))
                <div class="search-description">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong>Menampilkan hasil:</strong>
                            @if (request('search'))
                                Pencarian "<span class="text-primary fw-bold">{{ request('search') }}</span>"
                            @endif

                            @if (request('witel'))
                                @php
                                    $witelInfo = $witels->where('id', request('witel'))->first();
                                @endphp
                                @if (request('search'))
                                    dengan
                                @endif
                                Filter Witel: <span
                                    class="text-primary fw-bold">{{ $witelInfo ? $witelInfo->nama : '' }}</span>
                            @endif

                            @if (request('regional'))
                                @php
                                    $regionalInfo = $regionals->where('id', request('regional'))->first();
                                @endphp
                                @if (request('search') || request('witel'))
                                    dan
                                @endif
                                Regional: <span
                                    class="text-primary fw-bold">{{ $regionalInfo ? $regionalInfo->nama : '' }}</span>
                            @endif

                            @if (request('divisi'))
                                @php
                                    $divisiInfo = $divisi->where('id', request('divisi'))->first();
                                @endphp
                                @if (request('search') || request('witel') || request('regional'))
                                    dan
                                @endif
                                Divisi: <span
                                    class="text-primary fw-bold">{{ $divisiInfo ? $divisiInfo->nama : '' }}</span>
                            @endif

                            @if (request('month'))
                                @if (request('search') || request('witel') || request('regional') || request('divisi'))
                                    dan
                                @endif
                                Bulan: <span
                                    class="text-primary fw-bold">{{ date('F', mktime(0, 0, 0, request('month'), 1)) }}</span>
                            @endif

                            @if (request('year'))
                                @if (request('search') || request('witel') || request('regional') || request('divisi') || request('month'))
                                    dan
                                @endif
                                Tahun: <span class="text-primary fw-bold">{{ request('year') }}</span>
                            @endif
                        </div>
                        <a href="{{ route('revenue.data') }}" class="btn btn-sm btn-light ms-auto">
                            <i class="fas fa-times me-1"></i> Reset Filter
                        </a>
                    </div>
                </div>
            @endif
            <!-- Deskripsi Pencarian/Filter - Tampilkan jika ada parameter pencarian atau filter -->
            @if (request('search') || request('witel') || request('month') || request('year'))
                <div class="search-description">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong>Menampilkan hasil:</strong>
                            @if (request('search'))
                                Pencarian "<span class="text-primary fw-bold">{{ request('search') }}</span>"
                            @endif

                            @if (request('witel'))
                                @php
                                    $witelInfo = $witels->where('id', request('witel'))->first();
                                @endphp
                                @if (request('search'))
                                    dengan
                                @endif
                                Filter Witel: <span
                                    class="text-primary fw-bold">{{ $witelInfo ? $witelInfo->nama : '' }}</span>
                            @endif

                            @if (request('month'))
                                @if (request('search') || request('witel'))
                                    dan
                                @endif
                                Bulan: <span
                                    class="text-primary fw-bold">{{ date('F', mktime(0, 0, 0, request('month'), 1)) }}</span>
                            @endif

                            @if (request('year'))
                                @if (request('search') || request('witel') || request('month'))
                                    dan
                                @endif
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
                    <li class="tab-item active" data-tab="revenueTab"><i class="fas fa-chart-line me-2"></i> Revenue Data
                    </li>
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

                @if ($revenues->isEmpty())
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
                                        <th>Divisi</th>
                                        <th>Nama Customer</th>
                                        <th>Target Revenue</th>
                                        <th>Real Revenue</th>
                                        <th>Achievement</th>
                                        <th>Bulan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($revenues as $revenue)
                                        @php
                                            $achievement =
                                                $revenue->target_revenue > 0
                                                    ? round(
                                                        ($revenue->real_revenue / $revenue->target_revenue) * 100,
                                                        1,
                                                    )
                                                    : 0;

                                            $statusClass =
                                                $achievement >= 100
                                                    ? 'bg-success-soft'
                                                    : ($achievement >= 80
                                                        ? 'bg-warning-soft'
                                                        : 'bg-danger-soft');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ asset('img/profile.png') }}" class="am-profile-pic"
                                                        alt="{{ $revenue->accountManager->nama }}">
                                                    <span class="ms-2">{{ $revenue->accountManager->nama }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <!-- Tambahkan kolom divisi -->
                                                <span class="divisi-badge">
                                                    {{ $revenue->divisi->nama ?? 'N/A' }}
                                                </span>
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
                                                <a href="{{ route('revenue.edit', $revenue->id) }}"
                                                    class="action-btn edit-btn" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('revenue.destroy', $revenue->id) }}"
                                                    method="POST" style="display:inline;" class="delete-form">
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
                        @if ($revenues->hasPages())
                            <div class="pagination-container">
                                <ul class="pagination">
                                    <!-- Previous Page Link -->
                                    @if ($revenues->onFirstPage())
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
                                    @if ($currentPage > $range + 1)
                                        <li class="pagination-item">
                                            <a href="{{ $revenues->url(1) }}" class="pagination-link">1</a>
                                        </li>

                                        <!-- Tampilkan ... jika ada celah -->
                                        @if ($currentPage > $range + 2)
                                            <li class="pagination-item">
                                                <span class="pagination-link">...</span>
                                            </li>
                                        @endif
                                    @endif

                                    <!-- Tampilkan rentang halaman di sekitar halaman saat ini -->
                                    @for ($i = max(1, $currentPage - $range); $i <= min($lastPage, $currentPage + $range); $i++)
                                        <li class="pagination-item">
                                            <a href="{{ $revenues->url($i) }}"
                                                class="pagination-link {{ $i == $currentPage ? 'active' : '' }}">
                                                {{ $i }}
                                            </a>
                                        </li>
                                    @endfor

                                    <!-- Tampilkan halaman terakhir jika tidak dalam rentang akhir -->
                                    @if ($currentPage < $lastPage - $range)
                                        <!-- Tampilkan ... jika ada celah -->
                                        @if ($currentPage < $lastPage - $range - 1)
                                            <li class="pagination-item">
                                                <span class="pagination-link">...</span>
                                            </li>
                                        @endif

                                        <li class="pagination-item">
                                            <a href="{{ $revenues->url($lastPage) }}"
                                                class="pagination-link">{{ $lastPage }}</a>
                                        </li>
                                    @endif

                                    <!-- Next Page Link -->
                                    @if ($revenues->hasMorePages())
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
                                    Menampilkan {{ $revenues->firstItem() }} sampai {{ $revenues->lastItem() }} dari
                                    {{ $revenues->total() }} hasil
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Tab Content untuk Account Manager (Updated to include Regional) -->
            <div id="amTab" class="tab-content">
                <div id="am-search-empty" class="empty-search" style="display:none;">
                    <i class="fas fa-user-tie"></i>
                    <p>Tidak ada data Account Manager yang sesuai dengan pencarian "<span class="search-keyword"></span>"
                    </p>
                </div>

                @if ($accountManagers->isEmpty())
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
                                        <th>Regional</th> <!-- Tambahkan kolom Regional -->
                                        <th>Divisi</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($accountManagers as $am)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ asset('img/profile.png') }}" class="am-profile-pic"
                                                        alt="{{ $am->nama }}">
                                                    <span class="ms-2">{{ $am->nama }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $am->nik }}</td>
                                            <td>{{ $am->witel->nama }}</td>
                                            <td>{{ $am->regional->nama ?? 'N/A' }}</td> <!-- Tampilkan nama regional -->
                                            <td>
                                                @if ($am->divisis->count() > 0)
                                                    @foreach ($am->divisis as $divisi)
                                                        <span class="divisi-badge">{{ $divisi->nama }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('account_manager.edit', $am->id) }}"
                                                    class="action-btn edit-btn" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('account_manager.destroy', $am->id) }}"
                                                    method="POST" style="display:inline;" class="delete-form">
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

                        <!-- Pagination untuk Account Manager -->
                        @if (isset($accountManagers) && $accountManagers->hasPages())
                            <div class="pagination-container">
                                <ul class="pagination">
                                    <!-- Previous Page Link -->
                                    @if ($accountManagers->onFirstPage())
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

                                    @php
                                        $currentPage = $accountManagers->currentPage();
                                        $lastPage = $accountManagers->lastPage();
                                        $range = 2;
                                    @endphp

                                    @if ($currentPage > $range + 1)
                                        <li class="pagination-item">
                                            <a href="{{ $accountManagers->url(1) }}" class="pagination-link">1</a>
                                        </li>

                                        @if ($currentPage > $range + 2)
                                            <li class="pagination-item">
                                                <span class="pagination-link">...</span>
                                            </li>
                                        @endif
                                    @endif

                                    @for ($i = max(1, $currentPage - $range); $i <= min($lastPage, $currentPage + $range); $i++)
                                        <li class="pagination-item">
                                            <a href="{{ $accountManagers->url($i) }}"
                                                class="pagination-link {{ $i == $currentPage ? 'active' : '' }}">
                                                {{ $i }}
                                            </a>
                                        </li>
                                    @endfor

                                    @if ($currentPage < $lastPage - $range)
                                        @if ($currentPage < $lastPage - $range - 1)
                                            <li class="pagination-item">
                                                <span class="pagination-link">...</span>
                                            </li>
                                        @endif

                                        <li class="pagination-item">
                                            <a href="{{ $accountManagers->url($lastPage) }}"
                                                class="pagination-link">{{ $lastPage }}</a>
                                        </li>
                                    @endif

                                    <!-- Next Page Link -->
                                    @if ($accountManagers->hasMorePages())
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
                                    Menampilkan {{ $accountManagers->firstItem() }} sampai
                                    {{ $accountManagers->lastItem() }} dari {{ $accountManagers->total() }} hasil
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
                    <p>Tidak ada data Corporate Customer yang sesuai dengan pencarian "<span
                            class="search-keyword"></span>"</p>
                </div>

                @if ($corporateCustomers->isEmpty())
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
                                    @foreach ($corporateCustomers as $cc)
                                        <tr>
                                            <td>{{ $cc->nama }}</td>
                                            <td>{{ $cc->nipnas }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('corporate_customer.edit', $cc->id) }}"
                                                    class="action-btn edit-btn" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('corporate_customer.destroy', $cc->id) }}"
                                                    method="POST" style="display:inline;" class="delete-form">
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
                        @if (isset($corporateCustomers) && $corporateCustomers->hasPages())
                            <div class="pagination-container">
                                <ul class="pagination">
                                    <!-- Previous Page Link -->
                                    @if ($corporateCustomers->onFirstPage())
                                        <li class="pagination-item">
                                            <span class="pagination-link" aria-disabled="true">
                                                <i class="fas fa-chevron-left"></i>
                                            </span>
                                        </li>
                                    @else
                                        <li class="pagination-item">
                                            <a href="{{ $corporateCustomers->previousPageUrl() }}"
                                                class="pagination-link">
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
                                    @if ($currentPage > $range + 1)
                                        <li class="pagination-item">
                                            <a href="{{ $corporateCustomers->url(1) }}" class="pagination-link">1</a>
                                        </li>

                                        <!-- Tampilkan ... jika ada celah -->
                                        @if ($currentPage > $range + 2)
                                            <li class="pagination-item">
                                                <span class="pagination-link">...</span>
                                            </li>
                                        @endif
                                    @endif

                                    <!-- Tampilkan rentang halaman di sekitar halaman saat ini -->
                                    @for ($i = max(1, $currentPage - $range); $i <= min($lastPage, $currentPage + $range); $i++)
                                        <li class="pagination-item">
                                            <a href="{{ $corporateCustomers->url($i) }}"
                                                class="pagination-link {{ $i == $currentPage ? 'active' : '' }}">
                                                {{ $i }}
                                            </a>
                                        </li>
                                    @endfor

                                    <!-- Tampilkan halaman terakhir jika tidak dalam rentang akhir -->
                                    @if ($currentPage < $lastPage - $range)
                                        <!-- Tampilkan ... jika ada celah -->
                                        @if ($currentPage < $lastPage - $range - 1)
                                            <li class="pagination-item">
                                                <span class="pagination-link">...</span>
                                            </li>
                                        @endif

                                        <li class="pagination-item">
                                            <a href="{{ $corporateCustomers->url($lastPage) }}"
                                                class="pagination-link">{{ $lastPage }}</a>
                                        </li>
                                    @endif

                                    <!-- Next Page Link -->
                                    @if ($corporateCustomers->hasMorePages())
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
                                    Menampilkan {{ $corporateCustomers->firstItem() }} sampai
                                    {{ $corporateCustomers->lastItem() }} dari {{ $corporateCustomers->total() }} hasil
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal Tambah Account Manager -->
        <div class="modal fade" id="addAccountManagerModal" tabindex="-1" aria-labelledby="addAccountManagerModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAccountManagerModalLabel"><i class="fas fa-user-plus me-2"></i>
                            Tambah Account Manager Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Tab Menu di Modal -->
                        <div class="tab-menu-container">
                            <ul class="tabs">
                                <li class="tab-item active" data-tab="formTabAM"><i class="fas fa-edit me-2"></i> Form
                                    Manual</li>
                                <li class="tab-item" data-tab="importTabAM"><i class="fas fa-file-import me-2"></i>
                                    Import Excel</li>
                            </ul>
                        </div>

                        <!-- Tab Content -->
                        <div id="formTabAM" class="tab-content active">
                            <form id="amForm" action="{{ route('account_manager.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="nama" class="form-label">Nama Account Manager</label>
                                    <input type="text" id="nama" name="nama" class="form-control"
                                        placeholder="Masukkan Nama Account Manager" required>
                                </div>
                                <div class="form-group">
                                    <label for="nik" class="form-label">Nomor Induk Karyawan</label>
                                    <input type="text" id="nik" name="nik" class="form-control"
                                        placeholder="Masukkan 5 digit Nomor Induk Karyawan" pattern="^\d{5}$" required>
                                </div>
                                <div class="form-group">
                                    <label for="witel_id" class="form-label">Witel</label>
                                    <select name="witel_id" id="witel_id" class="form-control" required>
                                        <option value="">Pilih Witel</option>
                                        @if (isset($witels) && (is_object($witels) || is_array($witels)))
                                            @foreach ($witels as $witel)
                                                @if (is_object($witel) && isset($witel->id) && isset($witel->nama))
                                                    <option value="{{ $witel->id }}">{{ $witel->nama }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="regional_id" class="form-label">Regional</label>
                                    <select name="regional_id" id="regional_id" class="form-control" required>
                                        <option value="">Pilih Regional</option>
                                        @if (isset($regionals) && (is_object($regionals) || is_array($regionals)))
                                            @foreach ($regionals as $regional)
                                                @if (is_object($regional) && isset($regional->id) && isset($regional->nama))
                                                    <option value="{{ $regional->id }}">{{ $regional->nama }}</option>
                                                @endif
                                            @endforeach
                                        @else
                                            <!-- Fallback options jika data regional tidak valid -->
                                            <option value="1">TREG 1</option>
                                            <option value="2">TREG 2</option>
                                            <option value="3">TREG 3</option>
                                            <option value="4">TREG 4</option>
                                            <option value="5">TREG 5</option>
                                            <option value="6">TREG 6</option>
                                            <option value="7">TREG 7</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Divisi</label>
                                    <!-- Container untuk button group dengan styling hard-coded -->
                                    <div class="divisi-btn-group"
                                        style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;">
                                        @if (isset($divisi) && (is_object($divisi) || is_array($divisi)))
                                            @foreach ($divisi as $div)
                                                @if (is_object($div) && isset($div->id) && isset($div->nama))
                                                    <button type="button" class="divisi-btn"
                                                        data-divisi-id="{{ $div->id }}"
                                                        style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                                        {{ $div->nama }}
                                                    </button>
                                                @endif
                                            @endforeach
                                        @else
                                            <!-- Fallback buttons jika data divisi tidak valid -->
                                            <button type="button" class="divisi-btn" data-divisi-id="1"
                                                style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                                DGS
                                            </button>
                                            <button type="button" class="divisi-btn" data-divisi-id="2"
                                                style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                                DPS
                                            </button>
                                            <button type="button" class="divisi-btn" data-divisi-id="3"
                                                style="padding:8px 15px; border:1px solid #ddd; border-radius:4px; background-color:#f8f9fa; cursor:pointer; font-size:14px;">
                                                DSS
                                            </button>
                                        @endif
                                    </div>
                                    <!-- Hidden input untuk menyimpan divisi yang dipilih -->
                                    <input type="hidden" name="divisi_ids" id="divisi_ids" value="">
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
                            <form id="amImportForm" action="{{ route('account_manager.import') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="file_upload_am" class="form-label">Unggah File Excel/CSV</label>
                                    <input type="file" name="file" id="file_upload_am" accept=".xlsx, .xls, .csv"
                                        required class="form-control">
                                </div>
                                <div class="alert alert-info mt-3">
                                    <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i> Format Data
                                    </h6>
                                    <p class="mb-2 small">File harus memiliki kolom-kolom berikut:</p>
                                    <ul class="small mb-0">
                                        <li><strong>NIK</strong>: Nomor Induk Karyawan</li>
                                        <li><strong>NAMA AM</strong>: Nama Account Manager</li>
                                        <li><strong>WITEL HO</strong>: Nama Witel</li>
                                        <li><strong>REGIONAL</strong>: Nama Regional (TREG 1-7)</li>
                                        <li><strong>DIVISI</strong>: Nama Divisi</li>
                                    </ul>
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
        <div class="modal fade" id="addCorporateCustomerModal" tabindex="-1"
            aria-labelledby="addCorporateCustomerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCorporateCustomerModalLabel"><i class="fas fa-building me-2"></i>
                            Tambah Corporate Customer Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Tab Menu di Modal -->
                        <div class="tab-menu-container">
                            <ul class="tabs">
                                <li class="tab-item active" data-tab="formTabCC"><i class="fas fa-edit me-2"></i> Form
                                    Manual</li>
                                <li class="tab-item" data-tab="importTabCC"><i class="fas fa-file-import me-2"></i>
                                    Import Excel</li>
                            </ul>
                        </div>

                        <!-- Tab Content untuk Form Manual -->
                        <div id="formTabCC" class="tab-content active">
                            <form id="ccForm" action="{{ route('corporate_customer.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="nama_customer" class="form-label">Nama Corporate Customer</label>
                                    <input type="text" name="nama" id="nama_customer" class="form-control"
                                        placeholder="Masukkan Nama Corporate Customer" required>
                                </div>
                                <div class="form-group">
                                    <label for="nipnas" class="form-label">NIPNAS</label>
                                    <input type="number" name="nipnas" id="nipnas" class="form-control"
                                        placeholder="Masukkan NIPNAS (maksimal 7 digit)" max="9999999" required>
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
                            <form id="ccImportForm" action="{{ route('corporate_customer.import') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="file_upload_cc" class="form-label">Unggah File Excel/CSV</label>
                                    <input type="file" name="file" id="file_upload_cc" accept=".xlsx, .xls, .csv"
                                        required class="form-control">
                                </div>
                                <div class="alert alert-info mt-3">
                                    <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i> Format Data
                                    </h6>
                                    <p class="mb-2 small">File harus memiliki kolom-kolom berikut:</p>
                                    <ul class="small mb-0">
                                        <li><strong>STANDARD NAME</strong>: Nama Corporate Customer</li>
                                        <li><strong>NIPNAS</strong>: Nomor NIPNAS</li>
                                    </ul>
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
        <div class="modal fade" id="importRevenueModal" tabindex="-1" aria-labelledby="importRevenueModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importRevenueModalLabel"><i class="fas fa-file-import me-2"></i>
                            Import Data Revenue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="revenueImportForm" action="{{ route('revenue.import') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="file_upload_revenue" class="form-label">Unggah File Excel</label>
                                <input type="file" name="file" id="file_upload_revenue" accept=".xlsx, .xls, .csv"
                                    required class="form-control">
                            </div>

                            <div class="alert alert-info mt-3">
                                <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i> Catatan Format
                                    Excel</h6>
                                <p class="mb-2 small">Format Excel harus sesuai dengan template dan memiliki kolom-kolom:
                                </p>
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
                    <input type="number" id="year_input" class="form-control form-control-sm" placeholder="Tahun"
                        min="2000" max="2100">
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

    <!-- Indikator proses import -->
    <div id="importProcessingIndicator" style="display:none;" class="alert alert-info mt-3">
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div>
                Data sedang diimpor di background. Proses ini mungkin memakan waktu beberapa menit untuk file besar.
                <br>
                <small class="text-muted">Anda akan menerima notifikasi saat proses selesai.</small>
            </div>
        </div>
    </div>
    <div id="snackbar"></div>
    
@endsection

@section('scripts')

    <script>
        // Fungsi untuk reload data setelah import selesai
        window.reloadRevenueData = function() {
            // Reload halaman dengan parameter timestamp untuk mencegah cache
            window.location.href = "{{ route('revenue.data') }}?t=" + new Date().getTime();
        };
    </script>

    <!-- Load Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Load dashboard.js dengan versi baru untuk memastikan fresh load -->
    <script src="{{ asset('js/dashboard.js?v=' . time()) }}"></script>

    <!-- Script untuk menangani button group divisi -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi divisi button group saat halaman dimuat
            initDivisiButtonGroup();

            // Setup untuk modal - penting untuk memanggil init saat modal ditampilkan
            if (typeof $ !== 'undefined' && $('#addAccountManagerModal').length > 0) {
                $('#addAccountManagerModal').on('shown.bs.modal', function() {
                    console.log('Modal displayed, initializing divisi buttons');
                    initDivisiButtonGroup();
                });
            }

            // Setup untuk form submit
            const amForm = document.getElementById('amForm');
            if (amForm) {
                amForm.addEventListener('submit', function(e) {
                    const divisiIdsInput = document.getElementById('divisi_ids');
                    if (!divisiIdsInput.value) {
                        e.preventDefault();
                        alert('Silakan pilih minimal satu divisi!');
                    }
                });
            }
        });

        // Fungsi untuk menginisialisasi dan mengatur button group divisi
        function initDivisiButtonGroup() {
            console.log('Initializing divisi button group');
            const divisiButtons = document.querySelectorAll('.divisi-btn');
            const divisiIdsInput = document.getElementById('divisi_ids');

            console.log('Found divisi buttons:', divisiButtons.length);

            if (divisiButtons.length === 0) {
                console.warn('No divisi buttons found!');

                // Tambahkan fallback untuk divisi jika tidak ada dari server
                const divisiContainer = document.querySelector('.divisi-btn-group');
                if (divisiContainer && divisiContainer.children.length === 0) {
                    console.log('Adding fallback divisi buttons');

                    // Data divisi yang tetap (3 divisi yang ada)
                    const divisiData = [{
                            id: 1,
                            nama: 'DGS'
                        },
                        {
                            id: 2,
                            nama: 'DPS'
                        },
                        {
                            id: 3,
                            nama: 'DSS'
                        }
                    ];

                    // Tambahkan button untuk setiap divisi
                    divisiData.forEach(div => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'divisi-btn';
                        btn.dataset.divisiId = div.id;
                        btn.textContent = div.nama;
                        btn.style.padding = '8px 15px';
                        btn.style.border = '1px solid #ddd';
                        btn.style.borderRadius = '4px';
                        btn.style.backgroundColor = '#f8f9fa';
                        btn.style.cursor = 'pointer';
                        btn.style.margin = '0 5px 5px 0';
                        btn.style.display = 'inline-block';

                        divisiContainer.appendChild(btn);
                    });

                    // Panggil kembali fungsi ini setelah menambahkan button
                    setTimeout(initDivisiButtonGroup, 100);
                    return;
                }
            }

            // Tambahkan event listener untuk setiap button
            divisiButtons.forEach(button => {
                // Hapus event listener lama untuk mencegah duplikasi
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);

                // Tambahkan event listener pada button yang baru
                newButton.addEventListener('click', function() {
                    console.log('Button clicked:', this.dataset.divisiId);
                    this.classList.toggle('active');

                    // Ubah gaya visual saat button aktif/tidak aktif
                    if (this.classList.contains('active')) {
                        this.style.backgroundColor = '#0d6efd';
                        this.style.color = 'white';
                        this.style.borderColor = '#0d6efd';
                    } else {
                        this.style.backgroundColor = '#f8f9fa';
                        this.style.color = 'black';
                        this.style.borderColor = '#ddd';
                    }

                    updateDivisiIds();
                });
            });

            // Update nilai input hidden berdasarkan button yang aktif
            function updateDivisiIds() {
                const activeButtons = document.querySelectorAll('.divisi-btn.active');
                const selectedIds = Array.from(activeButtons).map(btn => btn.dataset.divisiId);
                divisiIdsInput.value = selectedIds.join(',');
                console.log('Selected divisi IDs:', divisiIdsInput.value);
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fungsi untuk mengambil divisi berdasarkan account manager yang dipilih
            function loadDivisiOptions(accountManagerId) {
                fetch(`/api/account-manager/${accountManagerId}/divisi`)
                    .then(response => response.json())
                    .then(data => {
                        const divisiSelect = document.getElementById('divisi_id');
                        divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';

                        if (data.divisis && data.divisis.length > 0) {
                            data.divisis.forEach(divisi => {
                                const option = document.createElement('option');
                                option.value = divisi.id;
                                option.textContent = divisi.nama;
                                divisiSelect.appendChild(option);
                            });

                            // Enable select
                            divisiSelect.disabled = false;
                        } else {
                            // Disable select jika tidak ada divisi
                            divisiSelect.disabled = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching divisi data:', error);
                    });
            }

            // Event handler untuk input account manager
            const accountManagerInput = document.getElementById('account_manager');
            const accountManagerIdInput = document.getElementById('account_manager_id');
            const divisiSelect = document.getElementById('divisi_id');

            // Sudah ada event handler untuk suggestions
            // Tambahkan kode untuk load divisi setelah account manager dipilih
            document.addEventListener('amSelected', function(event) {
                const accountManagerId = event.detail.id;
                if (accountManagerId) {
                    loadDivisiOptions(accountManagerId);
                } else {
                    divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';
                    divisiSelect.disabled = true;
                }
            });

            // Atau gunakan event listener manual jika tidak menggunakan custom event
            accountManagerInput.addEventListener('change', function() {
                const accountManagerId = accountManagerIdInput.value;
                if (accountManagerId) {
                    loadDivisiOptions(accountManagerId);
                } else {
                    divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';
                    divisiSelect.disabled = true;
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fungsi untuk mengambil divisi berdasarkan account manager yang dipilih
            function loadDivisiOptions(accountManagerId) {
                // Matikan select divisi selama loading
                const divisiSelect = document.getElementById('divisi_id');
                divisiSelect.disabled = true;
                divisiSelect.innerHTML = '<option value="">Loading divisi...</option>';

                // Buat request AJAX untuk mendapatkan divisi
                fetch(`/api/account-manager/${accountManagerId}/divisi`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Reset dropdown
                        divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';

                        if (data.success && data.divisis && data.divisis.length > 0) {
                            // Tambahkan options untuk setiap divisi
                            data.divisis.forEach(divisi => {
                                const option = document.createElement('option');
                                option.value = divisi.id;
                                option.textContent = divisi.nama;
                                divisiSelect.appendChild(option);
                            });

                            // Enable select
                            divisiSelect.disabled = false;

                            // Log info
                            console.log(
                                `Loaded ${data.divisis.length} divisi options for Account Manager ID: ${accountManagerId}`
                            );
                        } else {
                            // Jika tidak ada divisi, tampilkan pesan
                            const option = document.createElement('option');
                            option.value = "";
                            option.textContent = "Tidak ada divisi terkait";
                            divisiSelect.appendChild(option);

                            // Disable select
                            divisiSelect.disabled = true;

                            console.warn(`No divisions found for Account Manager ID: ${accountManagerId}`);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching divisi data:', error);

                        // Reset dropdown dengan pesan error
                        divisiSelect.innerHTML = '<option value="">Error loading divisi</option>';
                        divisiSelect.disabled = true;
                    });
            }

            // Pengaturan event handler untuk input account manager
            const accountManagerInput = document.getElementById('account_manager');
            const accountManagerIdInput = document.getElementById('account_manager_id');

            // Fungsi untuk memproses pemilihan account manager
            function handleAccountManagerSelection() {
                const accountManagerId = accountManagerIdInput.value;

                if (accountManagerId) {
                    console.log(`Account Manager selected: ${accountManagerInput.value} (ID: ${accountManagerId})`);
                    loadDivisiOptions(accountManagerId);
                } else {
                    // Reset divisi select jika tidak ada account manager yang dipilih
                    const divisiSelect = document.getElementById('divisi_id');
                    divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';
                    divisiSelect.disabled = true;
                }
            }

            // Event listener untuk perubahan pada input account manager
            // Gunakan event yang ada jika menggunakan library autocomplete
            if (typeof $ !== 'undefined') {
                // Jika menggunakan jQuery/Bootstrap autocomplete
                $(document).on('accountManagerSelected', function(event, data) {
                    if (data && data.id) {
                        accountManagerIdInput.value = data.id;
                        handleAccountManagerSelection();
                    }
                });
            }

            // Backup untuk metode standar
            accountManagerInput.addEventListener('change', function() {
                // Jika nilai sudah diset oleh autocomplete plugin
                setTimeout(handleAccountManagerSelection,
                    100); // Small delay untuk memberi waktu autocomplete selesai
            });

            // Deteksi event kustom jika digunakan
            document.addEventListener('amSelected', function(event) {
                if (event.detail && event.detail.id) {
                    accountManagerIdInput.value = event.detail.id;
                    handleAccountManagerSelection();
                }
            });

            // Tambahan event listener untuk opsi klik pada suggestion
            document.addEventListener('click', function(event) {
                if (event.target.closest('.suggestion-item')) {
                    // Tunggu sebentar agar nilai accountManagerIdInput diperbarui
                    setTimeout(handleAccountManagerSelection, 100);
                }
            });
        });
    </script>

    <!-- Script untuk menangani Regional -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Regional dropdown dalam modal
            initRegionalDropdown();

            // Fungsi untuk menangani form validation yang mencakup regional
            validateAccountManagerForm();
        });

        // Fungsi untuk inisialisasi dropdown Regional
        function initRegionalDropdown() {
            const regionalSelect = document.getElementById('regional_id');

            if (!regionalSelect) {
                console.warn('Regional dropdown tidak ditemukan');
                return;
            }

            console.log('Regional dropdown initialized');

            // Tambahkan event listener untuk perubahan Witel jika perlu
            const witelSelect = document.getElementById('witel_id');
            if (witelSelect) {
                witelSelect.addEventListener('change', function() {
                    // Opsional: Mengupdate regional berdasarkan witel yang dipilih
                    // Ini bisa diimplementasikan jika diperlukan relasi khusus antara witel dan regional
                    console.log('Witel changed:', this.value);
                });
            }
        }

        // Fungsi untuk validasi form yang mencakup regional
        function validateAccountManagerForm() {
            const amForm = document.getElementById('amForm');
            if (!amForm) return;

            amForm.addEventListener('submit', function(e) {
                const regionalSelect = document.getElementById('regional_id');

                // Validasi regional telah dipilih
                if (regionalSelect && regionalSelect.value === '') {
                    e.preventDefault();
                    alert('Silakan pilih Regional!');
                    return;
                }

                // Validasi divisi yang sudah ada tetap dijalankan
                const divisiIdsInput = document.getElementById('divisi_ids');
                if (divisiIdsInput && !divisiIdsInput.value) {
                    e.preventDefault();
                    alert('Silakan pilih minimal satu divisi!');
                    return;
                }
            });
        }
    </script>

    <!-- Script untuk mencari dan memfilter data berdasarkan Regional -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi filter regional
            initRegionalFilter();
        });

        // Fungsi untuk inisialisasi filter regional pada halaman utama
        function initRegionalFilter() {
            const regionalFilter = document.querySelector('select[name="regional"]');
            if (!regionalFilter) return;

            regionalFilter.addEventListener('change', function() {
                console.log('Regional filter changed:', this.value);
                // Optional: Auto-submit form ketika regional dipilih
                // this.closest('form').submit();
            });
        }
    </script>

    <!-- Script untuk impor CSV dengan kolom Regional -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi form impor CSV/Excel yang mendukung kolom Regional
            initImportForm();
        });

        function initImportForm() {
            const importForm = document.getElementById('amImportForm');
            if (!importForm) return;

            importForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const fileInput = this.querySelector('input[type="file"]');
                if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                    alert('Silakan pilih file CSV/Excel terlebih dahulu!');
                    return;
                }

                // Tampilkan loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mengunggah...';

                // Submit form via AJAX
                const formData = new FormData(this);

                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Kembalikan tombol ke state semula
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;

                        // Tampilkan notifikasi hasil
                        if (data.success) {
                            // Tampilkan pesan sukses
                            showNotification(data.message, 'success');

                            // Tutup modal setelah berhasil
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'addAccountManagerModal'));
                            if (modal) modal.hide();

                            // Reload halaman setelah impor berhasil
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            // Tampilkan pesan error
                            showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error importing file:', error);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        showNotification('Terjadi kesalahan saat mengunggah file', 'error');
                    });
            });
        }

        // Fungsi untuk menampilkan notifikasi
        function showNotification(message, type = 'info') {
            // Jika ada snackbar di halaman
            const snackbar = document.getElementById('snackbar');
            if (snackbar) {
                snackbar.className = `show ${type}`;
                snackbar.textContent = message;

                setTimeout(() => {
                    snackbar.className = snackbar.className.replace('show', '');
                }, 3000);
                return;
            }

            // Fallback ke alert
            if (type === 'error') {
                alert(`Error: ${message}`);
            } else {
                alert(message);
            }
        }
    </script>

    <script>
        // Script untuk import Excel/CSV di Corporate Customer
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi form import Corporate Customer
            initCorporateCustomerImportForm();
        });

        // Fungsi untuk inisialisasi form import Corporate Customer
        function initCorporateCustomerImportForm() {
            const importForm = document.getElementById('ccImportForm');

            if (!importForm) return;

            importForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const fileInput = this.querySelector('input[type="file"]');
                if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                    alert('Silakan pilih file Excel/CSV terlebih dahulu!');
                    return;
                }

                // Tampilkan loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mengunggah...';

                // Submit form via AJAX
                const formData = new FormData(this);

                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Kembalikan tombol ke state semula
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;

                        // Tampilkan notifikasi hasil
                        if (data.success) {
                            // Tampilkan pesan sukses
                            showNotification(data.message, 'success');

                            // Tutup modal setelah berhasil
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'addCorporateCustomerModal'));
                            if (modal) modal.hide();

                            // Reload halaman setelah impor berhasil
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            // Tampilkan pesan error
                            showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error importing file:', error);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        showNotification('Terjadi kesalahan saat mengunggah file', 'error');
                    });
            });
        }

        // Fungsi untuk menampilkan notifikasi
        function showNotification(message, type = 'info') {
            // Jika ada snackbar di halaman
            const snackbar = document.getElementById('snackbar');
            if (snackbar) {
                snackbar.className = `show ${type}`;
                snackbar.textContent = message;

                setTimeout(() => {
                    snackbar.className = snackbar.className.replace('show', '');
                }, 3000);
                return;
            }

            // Fallback ke alert
            if (type === 'error') {
                alert(`Error: ${message}`);
            } else {
                alert(message);
            }
        }
    </script>
    <script src="{{ asset('js/importStatus.js?v=' . time()) }}"></script>

@endsection

@extends('layouts.main')

@section('title', 'Leaderboard AM')

@section('styles')
<!-- CSS untuk Bootstrap Select -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    /* CSS untuk konten utama */
    .main-content {
        padding: 0 30px;
        margin-left: 85px; /* Sesuaikan dengan lebar sidebar */
        width: calc(100% - 85px);
    }

    /* Header leaderboard */
    .header-leaderboard {
        background: linear-gradient(135deg, #0e223e, #1e3c72 50%, #2a5298);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-top: 20px;
        margin-bottom: 20px;
        width: 100%;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .header-title {
        font-size: 2.2rem;
        font-weight: bold;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }

    .header-subtitle {
        font-size: 1rem;
        opacity: 0.9;
    }

    /* Modern Date & Period Filter */
    .date-period-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        position: relative; /* Important for flatpickr positioning */
    }

    .date-filter-container {
        position: relative;
        margin-bottom: 15px;
    }

    /* Flatpickr calendar positioning */
    .flatpickr-calendar {
        margin-top: 5px !important;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
        border-radius: 12px !important;
        border: none !important;
    }

    /* Ensure calendar appears above other elements */
    .flatpickr-calendar.open {
        z-index: 99999 !important;
    }

    .date-filter {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        background: linear-gradient(135deg, #1e4c9a, #2a5298);
        color: white;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(30, 76, 154, 0.3);
        transition: all 0.3s ease;
        border: none;
        width: 100%;
        text-align: left;
        outline: none;
    }

    .date-filter:hover {
        background: linear-gradient(135deg, #173b7a, #1e4c9a);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(30, 76, 154, 0.4);
    }

    .date-filter i {
        margin-right: 10px;
    }

    .date-filter i.fa-chevron-down {
        margin-left: auto;
        margin-right: 0;
        transition: transform 0.3s ease;
    }

    /* Modern Period Tabs */
    .period-tabs {
        display: flex;
        background: #e2e8f0;
        border-radius: 12px;
        padding: 6px;
        gap: 4px;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }

    .period-tab {
        flex: 1;
        padding: 12px 16px;
        text-align: center;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        background: transparent;
        border: none;
        color: #64748b;
        text-shadow: none;
    }

    .period-tab.active {
        background: linear-gradient(135deg, #1e4c9a, #2a5298) !important;
        color: #ffffff !important;
        box-shadow: 0 3px 8px rgba(30, 76, 154, 0.3);
        transform: translateY(-1px);
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }

    .period-tab.active i {
        color: #ffffff !important;
    }

    .period-tab:hover:not(.active) {
        background: rgba(255,255,255,0.8);
        color: #1e4c9a;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }

    .period-tab:active {
        transform: translateY(0);
    }

    .period-display {
        text-align: center;
        margin-top: 12px;
        font-size: 13px;
        color: #64748b;
    }

    .period-display strong {
        color: #1e4c9a;
        font-weight: 600;
    }

    /* Filter info section */
    .filter-info {
        background: linear-gradient(135deg, #f0f7ff, #e0f2fe);
        border: 1px solid #c9e0ff;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .filter-info-icon {
        color: #1e4c9a;
        font-size: 20px;
        margin-right: 12px;
    }

    .filter-info-text {
        flex-grow: 1;
        font-size: 14px;
        color: #334155;
    }

    .filter-info-reset {
        margin-left: 15px;
    }

    .reset-btn {
        background: white;
        color: #1e4c9a;
        border: 2px solid #1e4c9a;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        text-decoration: none;
    }

    .reset-btn:hover {
        background: #1e4c9a;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(30, 76, 154, 0.3);
    }

    .reset-btn i {
        margin-right: 6px;
    }

    /* Search and filter area */
    .search-filter-container {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        width: 100%;
        gap: 20px;
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
    }

    .search-box {
        flex: 0 0 25%;
    }

    .search-input {
        width: 100%;
        display: flex;
    }

    .search-input input {
        border-radius: 8px 0 0 8px;
        border: 2px solid #e2e8f0;
        padding: 12px 16px;
        font-size: 14px;
        flex-grow: 1;
        transition: border-color 0.3s ease;
    }

    .search-input input:focus {
        outline: none;
        border-color: #1e4c9a;
    }

    .search-input button {
        border-radius: 0 8px 8px 0;
        background: linear-gradient(135deg, #1e4c9a, #2a5298);
        color: white;
        border: none;
        padding: 12px 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .search-input button:hover {
        background: linear-gradient(135deg, #173b7a, #1e4c9a);
        transform: translateY(-1px);
    }

    .filter-area {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .filter-selects {
        display: flex;
        gap: 12px;
        width: 100%;
        justify-content: flex-end;
    }

    .filter-group {
        min-width: 180px;
    }

    /* Enhanced AM Cards */
    .am-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 16px;
        overflow: hidden;
        border: 2px solid transparent;
        transition: all 0.4s ease;
        cursor: pointer;
        position: relative;
    }

    .am-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #1e4c9a, #2a5298, #3b7ddd);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .am-card:hover {
        background: linear-gradient(135deg, #fafbff, #f0f7ff);
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(30, 76, 154, 0.15);
        border-color: #3b7ddd;
    }

    .am-card:hover::before {
        opacity: 1;
    }

    .am-card-body {
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .am-rank {
        font-size: 1.8rem;
        font-weight: 800;
        min-width: 50px;
        text-align: center;
        padding: 8px;
        border-radius: 12px;
        background: #f1f5f9;
        transition: all 0.3s ease;
    }

    .am-card:hover .am-rank {
        background: rgba(30, 76, 154, 0.1);
        color: #1e4c9a;
    }

    .am-profile-pic {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .am-card:hover .am-profile-pic {
        border-color: #3b7ddd;
        transform: scale(1.05);
    }

    .am-info {
        flex: 1;
    }

    .am-name {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 6px;
        color: #1e293b;
        transition: color 0.3s ease;
    }

    .am-card:hover .am-name {
        color: #1e4c9a;
    }

    .am-detail {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .am-detail i {
        font-size: 12px;
        color: #94a3b8;
    }

    .am-category-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }

    .am-category-badge.enterprise {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1e40af;
    }

    .am-category-badge.government {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
    }

    .am-category-badge.multi {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
    }

    .am-stats {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 28px;
        min-width: 400px;
    }

    .revenue-stat, .achievement-stat {
        text-align: right;
        padding: 12px 16px;
        border-radius: 12px;
        background: #f8fafc;
        transition: all 0.3s ease;
    }

    .am-card:hover .revenue-stat,
    .am-card:hover .achievement-stat {
        background: rgba(59, 125, 221, 0.08);
    }

    .revenue-label, .achievement-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .revenue-value, .achievement-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }

    .achievement-icon {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    .achievement-icon i {
        font-size: 16px;
    }

    /* Warna ranking */
    .text-gold {
        color: #FFD700;
        background: linear-gradient(135deg, #fff7ed, #fed7aa) !important;
    }
    .text-silver {
        color: #C0C0C0;
        background: linear-gradient(135deg, #f8fafc, #e2e8f0) !important;
    }
    .text-bronze {
        color: #CD7F32;
        background: linear-gradient(135deg, #fef3c7, #fde68a) !important;
    }

    /* Bootstrap select styling */
    .bootstrap-select > .dropdown-toggle {
        height: 42px;
        background: linear-gradient(135deg, #1e4c9a, #2a5298);
        color: white !important;
        border: none;
        width: 100%;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .bootstrap-select > .dropdown-toggle:hover {
        background: linear-gradient(135deg, #173b7a, #1e4c9a);
        transform: translateY(-1px);
    }

    .bootstrap-select > .dropdown-toggle.bs-placeholder {
        color: rgba(255,255,255,0.9) !important;
    }

    .filter-option-inner-inner {
        text-align: center !important;
        font-weight: 500;
        font-size: 14px;
    }

    .bootstrap-select .dropdown-menu {
        max-width: 100%;
        min-width: 100%;
        margin-top: 5px;
        border: none;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border-radius: 12px;
    }

    .bootstrap-select .dropdown-menu.show {
        top: 100% !important;
        transform: none !important;
    }

    .bs-actionsbox {
        display: none !important;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .empty-state i {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        color: #64748b;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #94a3b8;
        font-size: 0.9rem;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .filter-selects {
            flex-wrap: wrap;
        }

        .filter-group {
            min-width: 160px;
        }
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 0 15px;
        }

        .search-filter-container {
            flex-direction: column;
            gap: 15px;
        }

        .search-box {
            flex: none;
            width: 100%;
        }

        .filter-area {
            width: 100%;
        }

        .filter-selects {
            width: 100%;
            justify-content: stretch;
        }

        .filter-group {
            flex: 1;
            min-width: 0;
        }

        .am-card-body {
            flex-direction: column;
            text-align: center;
            gap: 16px;
        }

        .am-stats {
            width: 100%;
            justify-content: space-around;
            min-width: 0;
        }

        .period-tabs {
            flex-direction: column;
            gap: 8px;
        }

        .period-tab {
            padding: 12px;
        }
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <!-- Header Leaderboard -->
    <div class="header-leaderboard">
        <h1 class="header-title">
            <i class="fas fa-trophy me-3"></i>
            Leaderboard Performa Account Manager
        </h1>
        <p class="header-subtitle">
            Dashboard Performa Pendapatan dan Pencapaian Account Manager RLEGS
        </p>
    </div>

    <!-- Modern Date & Period Filter -->
    <div class="date-period-container">
        <!-- Date Filter -->
        <div class="date-filter-container">
            <button type="button" id="datePickerButton" class="date-filter">
                <i class="far fa-calendar-alt"></i>
                <span id="dateRangeText">{{ date('d M Y', strtotime($startDate ?? Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'))) }} -
                {{ date('d M Y', strtotime($endDate ?? Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'))) }}</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <input type="text" id="dateRangeSelector" style="display: none;" />
        </div>

        <!-- Modern Period Tabs -->
        <div class="period-tabs">
            <button class="period-tab {{ $currentPeriod == 'year_to_date' ? 'active' : '' }}" data-period="year_to_date">
                <i class="fas fa-calendar-year me-2"></i>Year to Date
            </button>
            <button class="period-tab {{ $currentPeriod == 'current_month' ? 'active' : '' }}" data-period="current_month">
                <i class="fas fa-calendar-day me-2"></i>Bulan Ini
            </button>
            <button class="period-tab {{ $currentPeriod == 'custom' ? 'active' : '' }}" data-period="custom">
                <i class="fas fa-calendar-alt me-2"></i>Kustom
            </button>
        </div>

        <div class="period-display">
            Tampilan: <strong id="displayPeriodText">{{ $displayPeriod }}</strong>
        </div>
    </div>

    <!-- Filter Info Section -->
    @if(request('search') || request('filter_by') || request('region_filter') || request('divisi_filter') || request('category_filter'))
    <div class="filter-info">
        <div class="filter-info-icon">
            <i class="fas fa-filter"></i>
        </div>
        <div class="filter-info-text">
            <strong>Menampilkan hasil peringkat</strong>
            @if(request('search'))
                untuk pencarian "<span class="text-primary fw-bold">{{ request('search') }}</span>"
            @endif

            @if(request('filter_by'))
                @if(request('search')) dengan @endif
                kriteria:
                @foreach(request('filter_by') as $filter)
                    <span class="text-primary fw-bold">{{ $filter }}</span>{{ !$loop->last ? ', ' : '' }}
                @endforeach
            @endif

            @if(request('region_filter'))
                @if(request('search') || request('filter_by')) di @endif
                Witel:
                @foreach(request('region_filter') as $region)
                    <span class="text-primary fw-bold">{{ $region }}</span>{{ !$loop->last ? ', ' : '' }}
                @endforeach
            @endif

            @if(request('divisi_filter'))
                @if(request('search') || request('filter_by') || request('region_filter')) | @endif
                Divisi:
                @foreach(request('divisi_filter') as $divisiId)
                    @php
                        $divisi = $divisis->find($divisiId);
                    @endphp
                    <span class="text-primary fw-bold">{{ $divisi ? $divisi->nama : $divisiId }}</span>{{ !$loop->last ? ', ' : '' }}
                @endforeach
            @endif

            @if(request('category_filter'))
                @if(request('search') || request('filter_by') || request('region_filter') || request('divisi_filter')) | @endif
                Kategori:
                @foreach(request('category_filter') as $category)
                    <span class="text-primary fw-bold">{{ ucfirst($category) }}</span>{{ !$loop->last ? ', ' : '' }}
                @endforeach
            @endif
        </div>
        <div class="filter-info-reset">
            <a href="{{ route('leaderboard', ['period' => request('period')]) }}" class="reset-btn">
                <i class="fas fa-undo"></i> Reset Filter
            </a>
        </div>
    </div>
    @endif

    <!-- Search & Filter Area -->
    <div class="search-filter-container">
        <div class="search-box">
            <form action="{{ route('leaderboard') }}" method="GET" id="searchForm" class="search-input">
                <input type="search" name="search" placeholder="Cari nama account manager..." value="{{ request('search') }}">
                <!-- Preserve all current filters -->
                @if(request('period'))
                    <input type="hidden" name="period" value="{{ request('period') }}">
                @endif
                @if(request('filter_by'))
                    @foreach(request('filter_by') as $filter)
                        <input type="hidden" name="filter_by[]" value="{{ $filter }}">
                    @endforeach
                @endif
                @if(request('region_filter'))
                    @foreach(request('region_filter') as $region)
                        <input type="hidden" name="region_filter[]" value="{{ $region }}">
                    @endforeach
                @endif
                @if(request('divisi_filter'))
                    @foreach(request('divisi_filter') as $divisi)
                        <input type="hidden" name="divisi_filter[]" value="{{ $divisi }}">
                    @endforeach
                @endif
                @if(request('category_filter'))
                    @foreach(request('category_filter') as $category)
                        <input type="hidden" name="category_filter[]" value="{{ $category }}">
                    @endforeach
                @endif
                <button type="submit">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
        </div>

        <div class="filter-area">
            <div class="filter-selects">
                <!-- Kriteria Filter -->
                <div class="filter-group">
                    <form id="filterForm1" action="{{ route('leaderboard') }}" method="GET">
                        <select class="selectpicker" id="filterSelect1" name="filter_by[]" multiple data-live-search="true" title="Pilih Kriteria" data-width="100%">
                            <option value="Revenue Realisasi Tertinggi" {{ in_array('Revenue Realisasi Tertinggi', request('filter_by', [])) ? 'selected' : '' }}>
                                Pendapatan Tertinggi
                            </option>
                            <option value="Achievement Tertinggi" {{ in_array('Achievement Tertinggi', request('filter_by', [])) ? 'selected' : '' }}>
                                Pencapaian Tertinggi
                            </option>
                        </select>
                        <!-- Preserve other filters -->
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('period'))
                            <input type="hidden" name="period" value="{{ request('period') }}">
                        @endif
                        @if(request('region_filter'))
                            @foreach(request('region_filter') as $region)
                                <input type="hidden" name="region_filter[]" value="{{ $region }}">
                            @endforeach
                        @endif
                        @if(request('divisi_filter'))
                            @foreach(request('divisi_filter') as $divisi)
                                <input type="hidden" name="divisi_filter[]" value="{{ $divisi }}">
                            @endforeach
                        @endif
                        @if(request('category_filter'))
                            @foreach(request('category_filter') as $category)
                                <input type="hidden" name="category_filter[]" value="{{ $category }}">
                            @endforeach
                        @endif
                        <button type="submit" class="d-none" id="submitFilter1">Submit</button>
                    </form>
                </div>

                <!-- Witel Filter -->
                <div class="filter-group">
                    <form id="filterForm2" action="{{ route('leaderboard') }}" method="GET">
                        <select class="selectpicker" id="filterSelect2" name="region_filter[]" multiple data-live-search="true" title="Pilih Witel" data-width="100%">
                            @foreach($witels as $witel)
                                <option value="{{ $witel->nama }}" {{ in_array($witel->nama, request('region_filter', [])) ? 'selected' : '' }}>
                                    {{ $witel->nama }}
                                </option>
                            @endforeach
                        </select>
                        <!-- Preserve other filters -->
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('period'))
                            <input type="hidden" name="period" value="{{ request('period') }}">
                        @endif
                        @if(request('filter_by'))
                            @foreach(request('filter_by') as $filter)
                                <input type="hidden" name="filter_by[]" value="{{ $filter }}">
                            @endforeach
                        @endif
                        @if(request('divisi_filter'))
                            @foreach(request('divisi_filter') as $divisi)
                                <input type="hidden" name="divisi_filter[]" value="{{ $divisi }}">
                            @endforeach
                        @endif
                        @if(request('category_filter'))
                            @foreach(request('category_filter') as $category)
                                <input type="hidden" name="category_filter[]" value="{{ $category }}">
                            @endforeach
                        @endif
                        <button type="submit" class="d-none" id="submitFilter2">Submit</button>
                    </form>
                </div>

                <!-- NEW: Divisi Filter -->
                <div class="filter-group">
                    <form id="filterForm3" action="{{ route('leaderboard') }}" method="GET">
                        <select class="selectpicker" id="filterSelect3" name="divisi_filter[]" multiple data-live-search="true" title="Pilih Divisi" data-width="100%">
                            @foreach($divisis as $divisi)
                                <option value="{{ $divisi->id }}" {{ in_array($divisi->id, request('divisi_filter', [])) ? 'selected' : '' }}>
                                    {{ $divisi->nama }}
                                </option>
                            @endforeach
                        </select>
                        <!-- Preserve other filters -->
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('period'))
                            <input type="hidden" name="period" value="{{ request('period') }}">
                        @endif
                        @if(request('filter_by'))
                            @foreach(request('filter_by') as $filter)
                                <input type="hidden" name="filter_by[]" value="{{ $filter }}">
                            @endforeach
                        @endif
                        @if(request('region_filter'))
                            @foreach(request('region_filter') as $region)
                                <input type="hidden" name="region_filter[]" value="{{ $region }}">
                            @endforeach
                        @endif
                        @if(request('category_filter'))
                            @foreach(request('category_filter') as $category)
                                <input type="hidden" name="category_filter[]" value="{{ $category }}">
                            @endforeach
                        @endif
                        <button type="submit" class="d-none" id="submitFilter3">Submit</button>
                    </form>
                </div>

                <!-- NEW: Category Filter -->
                <div class="filter-group">
                    <form id="filterForm4" action="{{ route('leaderboard') }}" method="GET">
                        <select class="selectpicker" id="filterSelect4" name="category_filter[]" multiple data-live-search="true" title="Pilih Kategori" data-width="100%">
                            <option value="enterprise" {{ in_array('enterprise', request('category_filter', [])) ? 'selected' : '' }}>
                                Enterprise
                            </option>
                            <option value="government" {{ in_array('government', request('category_filter', [])) ? 'selected' : '' }}>
                                Government
                            </option>
                            <option value="multi" {{ in_array('multi', request('category_filter', [])) ? 'selected' : '' }}>
                                Multi Divisi
                            </option>
                        </select>
                        <!-- Preserve other filters -->
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('period'))
                            <input type="hidden" name="period" value="{{ request('period') }}">
                        @endif
                        @if(request('filter_by'))
                            @foreach(request('filter_by') as $filter)
                                <input type="hidden" name="filter_by[]" value="{{ $filter }}">
                            @endforeach
                        @endif
                        @if(request('region_filter'))
                            @foreach(request('region_filter') as $region)
                                <input type="hidden" name="region_filter[]" value="{{ $region }}">
                            @endforeach
                        @endif
                        @if(request('divisi_filter'))
                            @foreach(request('divisi_filter') as $divisi)
                                <input type="hidden" name="divisi_filter[]" value="{{ $divisi }}">
                            @endforeach
                        @endif
                        <button type="submit" class="d-none" id="submitFilter4">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Leaderboard AM Cards -->
    @forelse($accountManagers as $index => $am)
        <div class="am-card" onclick="window.location.href='{{ route('account_manager.detail', $am->id) }}'">
            <div class="am-card-body">
                @if($am->global_rank == 1)
                    <div class="am-rank text-gold">
                        <i class="fas fa-crown"></i> 1
                    </div>
                @elseif($am->global_rank == 2)
                    <div class="am-rank text-silver">
                        <i class="fas fa-medal"></i> 2
                    </div>
                @elseif($am->global_rank == 3)
                    <div class="am-rank text-bronze">
                        <i class="fas fa-award"></i> 3
                    </div>
                @else
                    <div class="am-rank">{{ $am->global_rank }}</div>
                @endif

                <img src="{{ asset($am->user && $am->user->profile_image ? 'storage/'.$am->user->profile_image : 'img/profile.png') }}" class="am-profile-pic" alt="{{ $am->nama }}">

                <div class="am-info">
                    <div class="am-name">{{ $am->nama }}</div>
                    <div class="am-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        AM Witel {{ $am->witel->nama ?? 'N/A' }}
                    </div>
                    <div class="am-detail">
                        <i class="fas fa-layer-group"></i>
                        @if($am->divisis->count() > 0)
                            {{ $am->divisis->pluck('nama')->join(', ') }}
                        @else
                            N/A
                        @endif
                    </div>
                    @if(isset($am->category_info))
                        @php
                            $badgeClass = 'enterprise';
                            if($am->category_info['category'] === 'GOVERNMENT') {
                                $badgeClass = 'government';
                            } elseif($am->category_info['category'] === 'MULTI') {
                                $badgeClass = 'multi';
                            }
                        @endphp
                        <div class="am-category-badge {{ $badgeClass }}">
                            {{ $am->category_info['label'] }}
                        </div>
                    @endif
                </div>

                <div class="am-stats">
                    <div class="revenue-stat">
                        <div class="revenue-label">Pendapatan</div>
                        <div class="revenue-value">Rp {{ number_format($am->total_real_revenue, 0, ',', '.') }}</div>
                    </div>

                    <div class="achievement-stat">
                        <div class="achievement-label">Pencapaian</div>
                        <div class="achievement-value {{ $am->achievement_percentage < 100 ? 'text-danger' : 'text-success' }}">
                            <div class="achievement-icon">
                                <i class="fas {{ $am->achievement_percentage < 100 ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                                <span>{{ number_format($am->achievement_percentage, 2, ',', '.') }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="fas fa-users-slash"></i>
            <h3>Tidak Ada Data</h3>
            <p>Tidak ada Account Manager yang sesuai dengan kriteria pencarian Anda.</p>
        </div>
    @endforelse
</div>
@endsection

@section('scripts')
<!-- Script untuk Bootstrap Select -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Bootstrap Select
    $('.selectpicker').selectpicker({
        liveSearch: true,
        liveSearchPlaceholder: 'Cari opsi...',
        size: 6,
        actionsBox: false,
        dropupAuto: false,
        mobile: false,
        noneSelectedText: 'Pilih filter',
        style: '',
        styleBase: 'form-control text-center'
    });

    // Initialize period tabs based on URL parameter or default
    function initializePeriodTabs() {
        const urlParams = new URLSearchParams(window.location.search);
        let currentPeriod = urlParams.get('period') || '{{ $currentPeriod ?? "all_time" }}';

        // Remove active class from all tabs
        $('.period-tab').removeClass('active');

        // Add active class to current period
        $(`.period-tab[data-period="${currentPeriod}"]`).addClass('active');

        // If no period is set or period is all_time, default to year_to_date
        if (!currentPeriod || currentPeriod === 'all_time') {
            $('.period-tab[data-period="year_to_date"]').addClass('active');
        }

        console.log('Current period:', currentPeriod); // Debug log
    }

    // Call initialization
    initializePeriodTabs();

    // Filter form submissions
    $('#filterSelect1').on('changed.bs.select', function (e) {
        setTimeout(() => $('#submitFilter1').click(), 300);
    });

    $('#filterSelect2').on('changed.bs.select', function (e) {
        setTimeout(() => $('#submitFilter2').click(), 300);
    });

    $('#filterSelect3').on('changed.bs.select', function (e) {
        setTimeout(() => $('#submitFilter3').click(), 300);
    });

    $('#filterSelect4').on('changed.bs.select', function (e) {
        setTimeout(() => $('#submitFilter4').click(), 300);
    });

    // Modern Period Tabs with better visual feedback
    $('.period-tab').click(function() {
        const period = $(this).data('period');

        console.log('Period tab clicked:', period); // Debug log

        // Update active state immediately for better UX
        $('.period-tab').removeClass('active');
        $(this).addClass('active');

        // Update display text
        let displayText = '';
        switch(period) {
            case 'year_to_date':
                displayText = 'Year to Date';
                break;
            case 'current_month':
                displayText = 'Bulan Ini';
                break;
            case 'custom':
                displayText = 'Kustom';
                break;
        }
        $('#displayPeriodText').text(displayText);

        // Submit with period
        submitPeriodForm(period);
    });

    function submitPeriodForm(period) {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;

        // Add period parameter
        const periodInput = document.createElement('input');
        periodInput.type = 'hidden';
        periodInput.name = 'period';
        periodInput.value = period;
        form.appendChild(periodInput);

        // Preserve other filters
        @if(request('search'))
            const searchInput = document.createElement('input');
            searchInput.type = 'hidden';
            searchInput.name = 'search';
            searchInput.value = '{{ request('search') }}';
            form.appendChild(searchInput);
        @endif

        @if(request('filter_by'))
            @foreach(request('filter_by') as $filter)
                const filterInput{{ $loop->index }} = document.createElement('input');
                filterInput{{ $loop->index }}.type = 'hidden';
                filterInput{{ $loop->index }}.name = 'filter_by[]';
                filterInput{{ $loop->index }}.value = '{{ $filter }}';
                form.appendChild(filterInput{{ $loop->index }});
            @endforeach
        @endif

        @if(request('region_filter'))
            @foreach(request('region_filter') as $region)
                const regionInput{{ $loop->index }} = document.createElement('input');
                regionInput{{ $loop->index }}.type = 'hidden';
                regionInput{{ $loop->index }}.name = 'region_filter[]';
                regionInput{{ $loop->index }}.value = '{{ $region }}';
                form.appendChild(regionInput{{ $loop->index }});
            @endforeach
        @endif

        @if(request('divisi_filter'))
            @foreach(request('divisi_filter') as $divisi)
                const divisiInput{{ $loop->index }} = document.createElement('input');
                divisiInput{{ $loop->index }}.type = 'hidden';
                divisiInput{{ $loop->index }}.name = 'divisi_filter[]';
                divisiInput{{ $loop->index }}.value = '{{ $divisi }}';
                form.appendChild(divisiInput{{ $loop->index }});
            @endforeach
        @endif

        @if(request('category_filter'))
            @foreach(request('category_filter') as $category)
                const categoryInput{{ $loop->index }} = document.createElement('input');
                categoryInput{{ $loop->index }}.type = 'hidden';
                categoryInput{{ $loop->index }}.name = 'category_filter[]';
                categoryInput{{ $loop->index }}.value = '{{ $category }}';
                form.appendChild(categoryInput{{ $loop->index }});
            @endforeach
        @endif

        document.body.appendChild(form);
        form.submit();
    }

    // Date Picker Functionality
    const dateRangeInput = document.getElementById('dateRangeSelector');
    const datePickerButton = document.getElementById('datePickerButton');

    const fp = flatpickr(dateRangeInput, {
        mode: "range",
        dateFormat: "Y-m-d",
        appendTo: document.querySelector('.date-period-container'), // Append to container
        positionElement: datePickerButton, // Position relative to button
        position: "below", // Always show below
        static: false, // Allow repositioning
        defaultDate: [
            "{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}",
            "{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}"
        ],
        onChange: function(selectedDates, dateStr) {
            if (selectedDates.length === 2) {
                const startDate = formatDate(selectedDates[0]);
                const endDate = formatDate(selectedDates[1]);
                document.getElementById('dateRangeText').textContent = startDate + ' - ' + endDate;

                // Set custom period as active
                $('.period-tab').removeClass('active');
                $('.period-tab[data-period="custom"]').addClass('active');
                document.getElementById('displayPeriodText').textContent = 'Kustom';

                // Submit form with custom dates
                submitCustomDateForm(selectedDates[0], selectedDates[1]);
            }
        },
        onOpen: function() {
            // Ensure proper positioning when opened
            setTimeout(() => {
                const calendar = document.querySelector('.flatpickr-calendar');
                if (calendar) {
                    const buttonRect = datePickerButton.getBoundingClientRect();
                    const containerRect = document.querySelector('.date-period-container').getBoundingClientRect();

                    // Position relative to button
                    calendar.style.position = 'absolute';
                    calendar.style.top = (buttonRect.bottom - containerRect.top + 5) + 'px';
                    calendar.style.left = (buttonRect.left - containerRect.left) + 'px';
                    calendar.style.zIndex = '9999';
                }
            }, 10);
        }
    });

    datePickerButton.addEventListener('click', function() {
        fp.open();
    });

    function formatDate(date) {
        const day = date.getDate();
        const month = date.toLocaleString('default', { month: 'short' });
        const year = date.getFullYear();
        return `${day} ${month} ${year}`;
    }

    function submitCustomDateForm(startDate, endDate) {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;

        // Add period and dates
        const periodInput = document.createElement('input');
        periodInput.type = 'hidden';
        periodInput.name = 'period';
        periodInput.value = 'custom';
        form.appendChild(periodInput);

        const startDateInput = document.createElement('input');
        startDateInput.type = 'hidden';
        startDateInput.name = 'start_date';
        startDateInput.value = startDate.toISOString().split('T')[0];
        form.appendChild(startDateInput);

        const endDateInput = document.createElement('input');
        endDateInput.type = 'hidden';
        endDateInput.name = 'end_date';
        endDateInput.value = endDate.toISOString().split('T')[0];
        form.appendChild(endDateInput);

        // Preserve other filters
        @if(request('search'))
            const searchInput = document.createElement('input');
            searchInput.type = 'hidden';
            searchInput.name = 'search';
            searchInput.value = '{{ request('search') }}';
            form.appendChild(searchInput);
        @endif

        @if(request('filter_by'))
            @foreach(request('filter_by') as $filter)
                const filterInput{{ $loop->index }} = document.createElement('input');
                filterInput{{ $loop->index }}.type = 'hidden';
                filterInput{{ $loop->index }}.name = 'filter_by[]';
                filterInput{{ $loop->index }}.value = '{{ $filter }}';
                form.appendChild(filterInput{{ $loop->index }});
            @endforeach
        @endif

        @if(request('region_filter'))
            @foreach(request('region_filter') as $region)
                const regionInput{{ $loop->index }} = document.createElement('input');
                regionInput{{ $loop->index }}.type = 'hidden';
                regionInput{{ $loop->index }}.name = 'region_filter[]';
                regionInput{{ $loop->index }}.value = '{{ $region }}';
                form.appendChild(regionInput{{ $loop->index }});
            @endforeach
        @endif

        @if(request('divisi_filter'))
            @foreach(request('divisi_filter') as $divisi)
                const divisiInput{{ $loop->index }} = document.createElement('input');
                divisiInput{{ $loop->index }}.type = 'hidden';
                divisiInput{{ $loop->index }}.name = 'divisi_filter[]';
                divisiInput{{ $loop->index }}.value = '{{ $divisi }}';
                form.appendChild(divisiInput{{ $loop->index }});
            @endforeach
        @endif

        @if(request('category_filter'))
            @foreach(request('category_filter') as $category)
                const categoryInput{{ $loop->index }} = document.createElement('input');
                categoryInput{{ $loop->index }}.type = 'hidden';
                categoryInput{{ $loop->index }}.name = 'category_filter[]';
                categoryInput{{ $loop->index }}.value = '{{ $category }}';
                form.appendChild(categoryInput{{ $loop->index }});
            @endforeach
        @endif

        document.body.appendChild(form);
        form.submit();
    }
});
</script>
@endsection
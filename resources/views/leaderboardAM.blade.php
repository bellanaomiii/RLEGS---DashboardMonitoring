@extends('layouts.main')

@section('title', 'Leaderboard AM')

@section('styles')
<!-- CSS untuk Bootstrap Select -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
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
        border-radius: 10px;
        margin-top: 20px;
        margin-bottom: 20px;
        width: 100%;
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
    }

    /* Period selector with radio buttons */
    .period-selector {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        background-color: #f8f9fa;
        padding: 15px 20px;
        border-radius: 8px;
    }

    .period-label {
        font-weight: 500;
        margin-right: 15px;
        white-space: nowrap;
    }

    .period-options {
        display: flex;
        gap: 20px;
    }

    .radio-container {
        display: flex;
        align-items: center;
        white-space: nowrap;
        position: relative;
        padding-left: 28px;
        cursor: pointer;
        font-weight: 500;
    }

    .radio-container input[type="radio"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .radio-checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 20px;
        width: 20px;
        background-color: #eee;
        border-radius: 50%;
        border: 1px solid #ddd;
    }

    .radio-container:hover input ~ .radio-checkmark {
        background-color: #ccc;
    }

    .radio-container input:checked ~ .radio-checkmark {
        background-color: #1C2955;
    }

    .radio-checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    .radio-container input:checked ~ .radio-checkmark:after {
        display: block;
    }

    .radio-container .radio-checkmark:after {
        top: 6px;
        left: 6px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: white;
    }

    .period-display {
        margin-left: auto;
        font-weight: 500;
        white-space: nowrap;
        color: #1C2955;
    }

    /* Filter info section */
    .filter-info {
        background-color: #f0f7ff;
        border: 1px solid #c9e0ff;
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .filter-info-icon {
        color: #1C2955;
        font-size: 20px;
        margin-right: 10px;
    }

    .filter-info-text {
        flex-grow: 1;
        font-size: 14px;
    }

    .filter-info-reset {
        margin-left: 15px;
    }

    .reset-btn {
        background-color: transparent;
        color: #1C2955;
        border: 1px solid #1C2955;
        border-radius: 5px;
        padding: 5px 10px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s;
        display: flex;
        align-items: center;
    }

    .reset-btn:hover {
        background-color: #1C2955;
        color: white;
    }

    .reset-btn i {
        margin-right: 5px;
    }

    /* Search and filter area */
    .search-filter-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        width: 100%;
        gap: 20px;
    }

    .search-box {
        flex: 0 0 30%;
    }

    .search-input {
        width: 100%;
        display: flex;
    }

    .search-input input {
        border-radius: 8px 0 0 8px;
        border: 1px solid #ced4da;
        padding: 10px 15px;
        font-size: 14px;
        flex-grow: 1;
    }

    .search-input button {
        border-radius: 0 8px 8px 0;
        background-color: #1C2955;
        color: white;
        border: none;
        padding: 10px 20px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .search-input button:hover {
        background-color: #0e223e;
    }

    .filter-area {
        flex: 0 0 65%;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .filter-label {
        font-weight: 500;
        margin-right: 15px;
        white-space: nowrap;
    }

    .filter-selects {
        display: flex;
        gap: 15px;
        width: 100%;
        justify-content: flex-end;
    }

    .filter-group {
        width: 300px;
    }

    /* AM Cards */
    .am-card {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        margin-bottom: 15px;
        overflow: hidden;
        border: 1px solid #cacaca;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .am-card:hover {
        background-color: #f0f7ff;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        border-color: #3b7ddd;
    }

    .am-card-body {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .am-rank {
        font-size: 1.5rem;
        font-weight: bold;
        min-width: 40px;
        text-align: center;
    }

    .am-profile-pic {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
    }

    .am-info {
        flex: 1;
    }

    .am-name {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .am-detail {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 3px;
    }

    .am-stats {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 20px;
        min-width: 380px;
    }

    .revenue-stat {
        text-align: right;
    }

    .achievement-stat {
        text-align: right;
        min-width: 110px;
    }

    .revenue-label, .achievement-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 3px;
    }

    .revenue-value, .achievement-value {
        font-size: 1.2rem;
        font-weight: bold;
    }

    .achievement-icon {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .achievement-icon i {
        margin-right: 5px;
    }


    /* Warna */
    .text-gold { color: #FFD700; }
    .text-silver { color: #C0C0C0; }
    .text-bronze { color: #CD7F32; }

    /* Bootstrap select styling */
    .bootstrap-select > .dropdown-toggle {
        height: 40px;
        background-color: #1C2955;
        color: white !important;
        border: none;
        width: 100%;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .bootstrap-select > .dropdown-toggle.bs-placeholder {
        color: white !important; /* Lebih terang agar terlihat */
    }

    .filter-option {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .filter-option-inner {
        width: 100%;
        text-align: center;
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
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-radius: 8px;
    }

    /* Force dropdowns to appear below instead of above */
    .bootstrap-select .dropdown-menu.show {
        top: 100% !important;
        transform: none !important;
    }

    .bs-actionsbox {
        display: none !important;
    }

    /* Search button styling */
    .btn-search {
        background-color: #1C2955;
        color: white;
        border: none;
    }

    .btn-search:hover {
        background-color: #151f3d;
        color: white;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <!-- Header Leaderboard -->
    <div class="header-leaderboard">
        <h1 class="header-title">
            Leaderboard Performa Account Manager 
        </h1>
        <p class="header-subtitle">
            Dashboard Performa Pendapatan dan Pencapaian Account Manager RLEGS
        </p>
    </div>

    <!-- Period Selector with Radio Buttons -->
    <div class="period-selector">
        <div class="period-label">Pilih Periode:</div>
        <div class="period-options">
            <label class="radio-container">
                Year to Date
                <input type="radio" name="period" value="all_time" {{ $currentPeriod == 'all_time' ? 'checked' : '' }}>
                <span class="radio-checkmark"></span>
            </label>
            <label class="radio-container">
                Bulan Ini
                <input type="radio" name="period" value="current_month" {{ $currentPeriod == 'current_month' ? 'checked' : '' }}>
                <span class="radio-checkmark"></span>
            </label>
        </div>
        <div class="period-display">
            Tampilan: <strong>{{ $displayPeriod }}</strong>
        </div>
    </div>

    <!-- Filter Info Section - Only show if filters are applied -->
    @if(request('search') || request('filter_by') || request('region_filter'))
    <div class="filter-info">
        <div class="filter-info-icon">
            <i class="lni lni-funnel"></i>
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
        </div>
        <div class="filter-info-reset">
            <a href="{{ route('leaderboard', ['period' => request('period')]) }}" class="reset-btn">
                <i class="lni lni-reload"></i> Reset Filter
            </a>
        </div>
    </div>
    @endif

    <!-- Search & Filter Area -->
    <div class="search-filter-container">
        <div class="search-box">
            <form action="{{ route('leaderboard') }}" method="GET" id="searchForm" class="search-input">
                <input type="search" name="search" placeholder="Cari nama account manager..." value="{{ request('search') }}">
                <!-- Preserve all current filters when searching -->
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
                <button type="submit">
                    <i class="lni lni-search-alt"></i> Cari
                </button>
            </form>
        </div>

        <div class="filter-area">
            <div class="filter-selects">
                <div class="filter-group">
                    <form id="filterForm" action="{{ route('leaderboard') }}" method="GET">
                        <select class="selectpicker" id="filterSelect" name="filter_by[]" multiple data-live-search="true" data-dropup-auto="false" title="Pilih Kriteria" data-width="100%">
                            <option value="Revenue Realisasi Tertinggi" {{ in_array('Revenue Realisasi Tertinggi', request('filter_by', [])) ? 'selected' : '' }}>
                                Pendapatan Tertinggi
                            </option>
                            <option value="Achievement Tertinggi" {{ in_array('Achievement Tertinggi', request('filter_by', [])) ? 'selected' : '' }}>
                                Pencapaian Tertinggi
                            </option>
                        </select>
                        <!-- Preserve search term when filtering -->
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <!-- Preserve period when filtering -->
                        @if(request('period'))
                            <input type="hidden" name="period" value="{{ request('period') }}">
                        @endif
                        <!-- Preserve region filter when filtering by criteria -->
                        @if(request('region_filter'))
                            @foreach(request('region_filter') as $region)
                                <input type="hidden" name="region_filter[]" value="{{ $region }}">
                            @endforeach
                        @endif
                        <button type="submit" class="d-none" id="submitFilter">Submit</button>
                    </form>
                </div>

                <div class="filter-group">
                    <form id="filterForm2" action="{{ route('leaderboard') }}" method="GET">
                        <select class="selectpicker" id="filterSelect2" name="region_filter[]" multiple data-live-search="true" data-dropup-auto="false" title="Pilih Witel" data-width="100%">
                            @foreach($witels as $witel)
                                <option value="{{ $witel->nama }}" {{ in_array($witel->nama, request('region_filter', [])) ? 'selected' : '' }}>
                                    {{ $witel->nama }}
                                </option>
                            @endforeach
                        </select>
                        <!-- Preserve search term when filtering -->
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <!-- Preserve period when filtering -->
                        @if(request('period'))
                            <input type="hidden" name="period" value="{{ request('period') }}">
                        @endif
                        <!-- Preserve criteria filter when filtering by region -->
                        @if(request('filter_by'))
                            @foreach(request('filter_by') as $filter)
                                <input type="hidden" name="filter_by[]" value="{{ $filter }}">
                            @endforeach
                        @endif
                        <button type="submit" class="d-none" id="submitFilter2">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard AM Cards -->
    @forelse($accountManagers as $index => $am)
        <div class="am-card" onclick="window.location.href='{{ route('account_manager.detail', $am->id) }}'">
            <div class="am-card-body">
                @if($am->global_rank == 1)
                    <div class="am-rank text-gold">1</div>
                @elseif($am->global_rank == 2)
                    <div class="am-rank text-silver">2</div>
                @elseif($am->global_rank == 3)
                    <div class="am-rank text-bronze">3</div>
                @else
                    <div class="am-rank">{{ $am->global_rank }}</div>
                @endif

                <img src="{{ asset($am->user && $am->user->profile_image ? 'storage/'.$am->user->profile_image : 'img/profile.png') }}" class="am-profile-pic" alt="{{ $am->nama }}">

                <div class="am-info">
                    <div class="am-name">{{ $am->nama }}</div>
                    <div class="am-detail">AM Witel {{ $am->witel->nama ?? 'N/A' }}</div>
                    <div class="am-detail">{{ $am->divisi->nama ?? 'N/A' }}</div>
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
                                <i class="lni {{ $am->achievement_percentage < 100 ? 'lni-arrow-down' : 'lni-arrow-up' }}"></i>
                                <span>{{ number_format($am->achievement_percentage, 2, ',', '.') }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="am-card">
            <div class="am-card-body text-center">
                <p>Tidak ada data Account Manager yang tersedia.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection

@section('scripts')
<!-- Script untuk Bootstrap Select -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Bootstrap Select
    $('.selectpicker').selectpicker({
        liveSearch: true,
        liveSearchPlaceholder: 'Cari opsi...',
        size: 5,
        actionsBox: false,
        dropupAuto: false,
        mobile: false,
        noneSelectedText: 'Pilih filter',
        style: '',
        styleBase: 'form-control text-center'
    });

    // Tambahan styling untuk filter placeholders
    $('.filter-option-inner-inner').css({
        'text-align': 'center',
        'font-weight': 'bold'
    });

    // Atur fungsi submit saat filter dipilih
    $('#filterSelect').on('changed.bs.select', function (e) {
        setTimeout(function() {
            $('#submitFilter').click();
        }, 300);
    });

    $('#filterSelect2').on('changed.bs.select', function (e) {
        setTimeout(function() {
            $('#submitFilter2').click();
        }, 300);
    });

    // Period selector functionality
    $('input[name="period"]').change(function() {
        var period = $(this).val();

        // Clone form pencarian dan tambahkan parameter periode
        var $form = $('#searchForm').clone();

        // Hapus input hidden period yang mungkin sudah ada
        $form.find('input[name="period"]').remove();

        // Tambahkan input period baru
        $form.append('<input type="hidden" name="period" value="' + period + '">');

        // Submit form
        $form.appendTo('body').submit();
    });
});
</script>
@endsection
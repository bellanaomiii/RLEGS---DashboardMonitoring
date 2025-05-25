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
    }

    .radio-container input[type="radio"] {
        margin-right: 5px;
    }

    .period-display {
        margin-left: auto;
        font-weight: 500;
        white-space: nowrap;
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

    .filter-area {
        flex: 0 0 65%;
        display: flex;
        align-items: center;
    }

    .filter-label {
        flex: 0 0 60px;
        font-weight: 500;
        margin-right: 10px;
        white-space: nowrap;
    }

    .filter-selects {
        flex: 1;
        display: flex;
        gap: 15px;
    }

    .filter-group {
        flex: 1;
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
        height: 38px;
        background-color: #1C2955;
        color: white !important;
        border: none;
        width: 100%;
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
    }

    .bootstrap-select .dropdown-menu {
        max-width: 100%;
        min-width: 100%;
        margin-top: 0;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
            Peringkat Performa Account Manager⭐
        </h1>
        <p class="header-subtitle">
            Dashboard performa pendapatan Account Manager berdasarkan pendapatan nyata dan pencapaian target
        </p>
    </div>

    <!-- Period Selector with Radio Buttons -->
    <div class="period-selector">
        <div class="period-label">Pilih Periode:</div>
        <div class="period-options">
            <label class="radio-container">
                <input type="radio" name="period" value="all_time" checked> Sepanjang Waktu
            </label>
            <label class="radio-container">
                <input type="radio" name="period" value="current_month"> Bulan Ini
            </label>
        </div>
        <div class="period-display">
            Tampilan: <strong>Peringkat Maret 2024</strong>
        </div>
    </div>

    <!-- Search & Filter Area -->
    <div class="search-filter-container">
        <div class="search-box">
            <form action="{{ route('leaderboard') }}" method="GET" class="search-input">
                <input class="form-control" type="search" name="search" placeholder="Cari Nama" value="{{ request('search') }}">
                <button class="btn btn-search ms-2" type="submit">Cari</button>
            </form>
        </div>

        <div class="filter-area">
            <div class="filter-label">Filter:</div>
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
                        @if(!empty(request('search')))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <button type="submit" class="d-none" id="submitFilter">Submit</button>
                    </form>
                </div>

                <div class="filter-group">
                    <form id="filterForm2" action="{{ route('leaderboard') }}" method="GET">
                        <select class="selectpicker" id="filterSelect2" name="region_filter[]" multiple data-live-search="true" data-dropup-auto="false" title="Pilih Witel" data-width="100%">
                            <option value="Suramadu">Suramadu</option>
                            <option value="Nusa Tenggara">Nusa Tenggara</option>
                            <option value="Jatim Barat">Jatim Barat</option>
                            <option value="Yogya Jateng Selatan">Yogya Jateng Selatan</option>
                            <option value="Bali">Bali</option>
                            <option value="Semarang Jateng Utara">Semarang Jateng Utara</option>
                            <option value="Solo Jateng Timur">Solo Jateng Timur</option>
                            <option value="Jatim Timur">Jatim Timur</option>
                        </select>
                        @if(!empty(request('search')))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <button type="submit" class="d-none" id="submitFilter2">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard AM Cards -->
    @forelse($accountManagers as $index => $am)
        <div class="am-card">
            <div class="am-card-body">
                @if($index == 0)
                    <div class="am-rank text-gold">1</div>
                @elseif($index == 1)
                    <div class="am-rank text-silver">2</div>
                @elseif($index == 2)
                    <div class="am-rank text-bronze">3</div>
                @else
                    <div class="am-rank">{{ $index + 1 }}</div>
                @endif

                <img src="{{ asset('img/profile.png') }}" class="am-profile-pic" alt="{{ $am->nama }}">

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
                                <i class="lni {{ $am->achievement_percentage < 100 ? 'lni-trend-down' : 'lni-trend-up-1' }}"></i>
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
        // Lakukan perubahan tampilan sesuai periode yang dipilih
        if (period === 'all_time') {
            $('.period-display strong').text('Peringkat Sepanjang Waktu');
        } else {
            $('.period-display strong').text('Peringkat Maret 2024');
        }

        // Di sini bisa tambahkan AJAX request untuk mengambil data sesuai periode
    });
});
</script>
@endsection
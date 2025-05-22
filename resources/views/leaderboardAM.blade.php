@extends('layouts.main')

@section('title', 'Leaderboard AM')

@section('styles')
<!-- CSS untuk Bootstrap Select -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    /* Add these styles to make the date filter look like a button */
        .date-filter-container {
            position: relative;
        }

        .date-filter {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background-color: #1e4c9a;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: background-color 0.2s;
            border: none;
            width: 100%;
            text-align: left;
            outline: none;
        }

        .date-filter:hover {
            background-color: #173b7a;
        }

        .date-filter i {
            margin-right: 8px;
        }

        .date-filter i.fa-chevron-down {
            margin-left: 8px;
            margin-right: 0;
        }

        /* Style for period selector */
        .period-selector {
            margin-top: 15px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            padding: 10px 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        .period-label {
            font-weight: 500;
            margin-right: 15px;
        }

        .period-options {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-right: 20px;
        }

        /* Custom radio button styles */
        .radio-container {
            display: flex;
            align-items: center;
            position: relative;
            padding-left: 28px;
            cursor: pointer;
            font-size: 14px;
            user-select: none;
        }

        .radio-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .radio-checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 18px;
            width: 18px;
            background-color: #fff;
            border: 2px solid #ddd;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .radio-container:hover input ~ .radio-checkmark {
            border-color: #1e4c9a;
        }

        .radio-container input:checked ~ .radio-checkmark {
            background-color: #fff;
            border-color: #1e4c9a;
        }

        .radio-checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .radio-container input:checked ~ .radio-checkmark:after {
            display: block;
            top: 3px;
            left: 3px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #1e4c9a;
        }

        .period-display {
            margin-left: auto;
            font-size: 13px;
            color: #666;
        }

        .period-display strong {
            color: #333;
        }
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

<div class="filter-controls">
    <!-- Date Filter -->
    <div class="date-filter-container">
        <!-- Visible button that triggers date picker -->
        <button type="button" id="datePickerButton" class="date-filter">
            <i class="far fa-calendar-alt"></i>
            <span id="dateRangeText">{{ date('d M Y', strtotime($startDate ?? Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'))) }} - 
            {{ date('d M Y', strtotime($endDate ?? Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'))) }}</span>
            <i class="fas fa-chevron-down ms-auto"></i>
        </button>
        <!-- Hidden input for flatpickr -->
        <input type="text" id="dateRangeSelector" style="visibility: hidden; position: absolute; width: 0; height: 0;" />
    </div>

    <!-- Period Selector with Radio Buttons -->
    <div class="period-selector">
        <div class="period-label">Pilih Periode:</div>
        <div class="period-options">
            <label class="radio-container">
                Year to Date
                <input type="radio" name="period" value="year_to_date" {{ $currentPeriod == 'year_to_date' ? 'checked' : '' }}>
                <span class="radio-checkmark"></span>
            </label>
            <label class="radio-container">
                Bulan Ini
                <input type="radio" name="period" value="current_month" {{ $currentPeriod == 'current_month' ? 'checked' : '' }}>
                <span class="radio-checkmark"></span>
            </label>
            <label class="radio-container">
                Kustom
                <input type="radio" name="period" value="custom" {{ $currentPeriod == 'custom' ? 'checked' : '' }}>
                <span class="radio-checkmark"></span>
            </label>
        </div>
        <div class="period-display">
            Tampilan: <strong id="displayPeriodText">{{ $displayPeriod }}</strong>
        </div>
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
        <!-- Add a parameter to maintain original ranking -->
        <input type="hidden" name="preserve_ranking" value="true">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get chart data from controller (if available)
    const chartData = @json($chartData ?? []);
    console.log('Chart data loaded:', chartData);

    // Declare global variables for chart instances (if needed)
    let lineRevenueChartInstance;
    let donutAchievementChartInstance;
    let barDivisionChartInstance;
    let performanceWitelChartInstance;

    // Initialize flatpickr directly on the hidden input
    const dateRangeInput = document.getElementById('dateRangeSelector');
    const datePickerButton = document.getElementById('datePickerButton');
    
    // Initialize date range picker on the hidden input
    const fp = flatpickr(dateRangeInput, {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: [
            "{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}",
            "{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}"
        ],
        onChange: function(selectedDates, dateStr) {
            if (selectedDates.length === 2) {
                const startDate = formatDate(selectedDates[0]);
                const endDate = formatDate(selectedDates[1]);
                document.getElementById('dateRangeText').textContent = startDate + ' - ' + endDate;
                
                // Set the radio button to custom
                document.querySelector('input[name="period"][value="custom"]').checked = true;
                document.getElementById('displayPeriodText').textContent = 'Kustom';
                
                // Update charts with new date range
                updateCharts(selectedDates[0], selectedDates[1]);
                
                // Submit form with new date range
                submitPeriodForm('custom', dateStr);
            }
        }
    });
    
    // Make the button open the flatpickr instance
    datePickerButton.addEventListener('click', function() {
        fp.open();
    });

    // Helper function to format date
    function formatDate(date) {
        const day = date.getDate();
        const month = date.toLocaleString('default', {
            month: 'short'
        });
        const year = date.getFullYear();
        return `${day} ${month} ${year}`;
    }
    
    // Add event listeners to radio buttons
    document.querySelectorAll('input[name="period"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const periodValue = this.value;
            let startDate, endDate, displayText;
            
            const today = new Date();
            
            switch(periodValue) {
                case 'year_to_date':
                    // From January 1 of current year to today
                    startDate = new Date(today.getFullYear(), 0, 1); // January 1st
                    endDate = today;
                    displayText = 'Year to Date';
                    break;
                    
                case 'current_month':
                    // Current month
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1); // First day of current month
                    endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0); // Last day of current month
                    displayText = 'Bulan Ini';
                    break;
                    
                case 'custom':
                    // Keep current selection in date picker
                    return; // Let the datepicker handle this
            }
            
            // Update the date picker
            if (periodValue !== 'custom') {
                fp.setDate([startDate, endDate]);
                
                // Format dates for display
                const formattedStartDate = formatDate(startDate);
                const formattedEndDate = formatDate(endDate);
                document.getElementById('dateRangeText').textContent = formattedStartDate + ' - ' + formattedEndDate;
                document.getElementById('displayPeriodText').textContent = displayText;
                
                // Format dates for submission (YYYY-MM-DD)
                const formattedStart = formatDateForSubmission(startDate);
                const formattedEnd = formatDateForSubmission(endDate);
                
                // Update charts and submit form
                updateCharts(startDate, endDate);
                submitPeriodForm(periodValue, formattedStart + ' to ' + formattedEnd);
            }
        });
    });
    
    // Helper function to format date for form submission
    function formatDateForSubmission(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Function to submit form with selected period
    function submitPeriodForm(period, dateRange) {
        // Create a form to submit
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;
        
        // Add period parameter
        const periodInput = document.createElement('input');
        periodInput.type = 'hidden';
        periodInput.name = 'period';
        periodInput.value = period;
        form.appendChild(periodInput);
        
        // If custom period, add date range
        if (period === 'custom' || period === 'year_to_date') {
            const dates = dateRange.split(' to ');
            
            const startDateInput = document.createElement('input');
            startDateInput.type = 'hidden';
            startDateInput.name = 'start_date';
            startDateInput.value = dates[0];
            form.appendChild(startDateInput);
            
            const endDateInput = document.createElement('input');
            endDateInput.type = 'hidden';
            endDateInput.name = 'end_date';
            endDateInput.value = dates[1];
            form.appendChild(endDateInput);
        }
        
        // Append form to body and submit
        document.body.appendChild(form);
        form.submit();
    }
    
    // Function to update charts with new date range
    function updateCharts(startDate, endDate) {
        // This function should be implemented based on your chart requirements
        // You could use AJAX to fetch new data or filter existing data based on dates
        console.log('Updating charts with date range:', startDate, endDate);
        
        // Example: If you have a function to reload charts
        // reloadCharts(startDate, endDate);
    }
    
    // Initialize period selection based on URL parameters or defaults
    function initializePeriodSelection() {
        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const period = urlParams.get('period');
        
        if (period) {
            // Select the appropriate radio button
            const radioButton = document.querySelector(`input[name="period"][value="${period}"]`);
            if (radioButton) {
                radioButton.checked = true;
            }
        }
    }
    
    // Call initialization function
    initializePeriodSelection();
});
</script>

</body>
</html><!-- Combined Date Filter and Period Selector -->
<div class="filter-controls">
    <!-- Date Filter -->
    <div class="date-filter-container">
        <!-- Visible button that triggers date picker -->
        <button type="button" id="datePickerButton" class="date-filter">
            <i class="far fa-calendar-alt"></i>
            <span id="dateRangeText">{{ date('d M Y', strtotime($startDate ?? Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'))) }} - 
            {{ date('d M Y', strtotime($endDate ?? Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'))) }}</span>
            <i class="fas fa-chevron-down ms-auto"></i>
        </button>
        <!-- Hidden input for flatpickr -->
        <input type="text" id="dateRangeSelector" style="visibility: hidden; position: absolute; width: 0; height: 0;" />
    </div>

    <!-- Period Selector with Radio Buttons -->
    <div class="period-selector">
        <div class="period-label">Pilih Periode:</div>
        <div class="period-options">
            <label class="radio-container">
                Year to Date
                <input type="radio" name="period" value="year_to_date" {{ $currentPeriod == 'year_to_date' ? 'checked' : '' }}>
                <span class="radio-checkmark"></span>
            </label>
            <label class="radio-container">
                Bulan Ini
                <input type="radio" name="period" value="current_month" {{ $currentPeriod == 'current_month' ? 'checked' : '' }}>
                <span class="radio-checkmark"></span>
            </label>
            <label class="radio-container">
                Kustom
                <input type="radio" name="period" value="custom" {{ $currentPeriod == 'custom' ? 'checked' : '' }}>
                <span class="radio-checkmark"></span>
            </label>
        </div>
        <div class="period-display">
            Tampilan: <strong id="displayPeriodText">{{ $displayPeriod }}</strong>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get chart data from controller
    const chartData = @json($chartData ?? []);
    console.log('Chart data loaded:', chartData);

    // Declare global variables for chart instances
    let lineRevenueChartInstance;
    let donutAchievementChartInstance;
    let barDivisionChartInstance;
    let performanceWitelChartInstance;

    // Initialize flatpickr directly on the hidden input
    const dateRangeInput = document.getElementById('dateRangeSelector');
    const datePickerButton = document.getElementById('datePickerButton');
    
    // Initialize date range picker on the hidden input
    const fp = flatpickr(dateRangeInput, {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: [
            "{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}",
            "{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}"
        ],
        onChange: function(selectedDates, dateStr) {
            if (selectedDates.length === 2) {
                const startDate = formatDate(selectedDates[0]);
                const endDate = formatDate(selectedDates[1]);
                document.getElementById('dateRangeText').textContent = startDate + ' - ' + endDate;
                
                // Set the radio button to custom
                document.querySelector('input[name="period"][value="custom"]').checked = true;
                document.getElementById('displayPeriodText').textContent = 'Kustom';
                
                // Update charts with new date range
                updateCharts(selectedDates[0], selectedDates[1]);
                
                // Submit form with new date range
                submitPeriodForm('custom', dateStr);
            }
        }
    });
    
    // Make the button open the flatpickr instance
    datePickerButton.addEventListener('click', function() {
        fp.open();
    });

    // Helper function to format date
    function formatDate(date) {
        const day = date.getDate();
        const month = date.toLocaleString('default', {
            month: 'short'
        });
        const year = date.getFullYear();
        return `${day} ${month} ${year}`;
    }
    
    // Add event listeners to radio buttons
    document.querySelectorAll('input[name="period"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const periodValue = this.value;
            let startDate, endDate, displayText;
            
            const today = new Date();
            
            switch(periodValue) {
                case 'year_to_date':
                    // From January 1 of current year to today
                    startDate = new Date(today.getFullYear(), 0, 1); // January 1st
                    endDate = today;
                    displayText = 'Year to Date';
                    break;
                    
                case 'current_month':
                    // Current month
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1); // First day of current month
                    endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0); // Last day of current month
                    displayText = 'Bulan Ini';
                    break;
                    
                case 'custom':
                    // Keep current selection in date picker
                    return; // Let the datepicker handle this
            }
            
            // Update the date picker
            if (periodValue !== 'custom') {
                fp.setDate([startDate, endDate]);
                
                // Format dates for display
                const formattedStartDate = formatDate(startDate);
                const formattedEndDate = formatDate(endDate);
                document.getElementById('dateRangeText').textContent = formattedStartDate + ' - ' + formattedEndDate;
                document.getElementById('displayPeriodText').textContent = displayText;
                
                // Format dates for submission (YYYY-MM-DD)
                const formattedStart = formatDateForSubmission(startDate);
                const formattedEnd = formatDateForSubmission(endDate);
                
                // Update charts and submit form
                updateCharts(startDate, endDate);
                submitPeriodForm(periodValue, formattedStart + ' to ' + formattedEnd);
            }
        });
    });
    
    // Helper function to format date for form submission
    function formatDateForSubmission(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Function to submit form with selected period
    function submitPeriodForm(period, dateRange) {
        // Create a form to submit
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;
        
        // Add period parameter
        const periodInput = document.createElement('input');
        periodInput.type = 'hidden';
        periodInput.name = 'period';
        periodInput.value = period;
        form.appendChild(periodInput);
        
        // If custom period, add date range
        if (period === 'custom' || period === 'year_to_date') {
            const dates = dateRange.split(' to ');
            
            const startDateInput = document.createElement('input');
            startDateInput.type = 'hidden';
            startDateInput.name = 'start_date';
            startDateInput.value = dates[0];
            form.appendChild(startDateInput);
            
            const endDateInput = document.createElement('input');
            endDateInput.type = 'hidden';
            endDateInput.name = 'end_date';
            endDateInput.value = dates[1];
            form.appendChild(endDateInput);
        }
        
        // Append form to body and submit
        document.body.appendChild(form);
        form.submit();
    }
    
    // Function to update charts with new date range
    function updateCharts(startDate, endDate) {
        // This function should be implemented based on your chart requirements
        // You could use AJAX to fetch new data or filter existing data based on dates
        console.log('Updating charts with date range:', startDate, endDate);
        
        // Example: If you have a function to reload charts
        // reloadCharts(startDate, endDate);
    }
    
    // Initialize period selection based on URL parameters or defaults
    function initializePeriodSelection() {
        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const period = urlParams.get('period');
        
        if (period) {
            // Select the appropriate radio button
            const radioButton = document.querySelector(`input[name="period"][value="${period}"]`);
            if (radioButton) {
                radioButton.checked = true;
            }
        }
    }
    
    // Call initialization function
    initializePeriodSelection();
});
</script>
@endsection
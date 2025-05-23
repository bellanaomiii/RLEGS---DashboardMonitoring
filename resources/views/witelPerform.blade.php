@extends('layouts.main')

@section('title', 'Data Performansi RLEGS')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="{{ asset('css/witel.css') }}">
    <style>
        /* Improved filters row styling */
        .filters-row {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .date-filter-container,
        .divisi-filter-container {
            position: relative;
            min-width: 200px;
        }

        /* Date filter styling - with WHITE TEXT color */
        .date-filter {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            border: 1px solid #2c5aa0;
            border-radius: 6px;
            background-color: #2c5aa0;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .date-filter:hover {
            background-color: #224785;
            border-color: #224785;
        }

        .date-filter i {
            margin-right: 10px;
            color: white;
        }

        .date-filter .fa-chevron-down {
            margin-left: 10px;
            font-size: 12px;
            color: white;
        }

        #dateRangeText {
            color: white;
        }

        /* Filter button styling */
        .filter-button {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            border: 1px solid #2c5aa0;
            border-radius: 6px;
            background-color: #2c5aa0;
            color: white;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .filter-button:hover {
            background-color: #224785;
            border-color: #224785;
        }

        .filter-button i {
            color: white;
        }

        .filter-divisi-value {
            font-weight: 600;
            color: white;
            margin-left: 5px;
        }

        /* Cleaner styling for filter panel */
        #filterPanel {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 5px;
            width: 280px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1050;
            padding: 15px;
            display: none;
        }

        .regions-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            width: 100%;
        }

        .region-row {
            display: flex;
            gap: 30px;
            width: 100%;
            margin-bottom: 10px;
        }

        .region-box {
            flex: 1;
            max-width: 345px;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            font-size: 14px;
            color: #495057;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: all 0.2s ease;
        }

        .region-box:hover {
            background-color: #e9ecef;
            border-color: #ced4da;
        }

        .region-box.active {
            background-color: #1e5bb0;
            color: white;
            border-color: #1e5bb0;
        }

        /* Loading state for charts */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        /* Chart container position relative for overlay */
        .chart-body {
            position: relative;
            min-height: 350px;
        }

        /* Filter overlay */
        .filter-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.3);
            z-index: 1040;
        }

        /* Alert styling */
        .alert-container {
            transition: all 0.3s ease;
        }

        /* Filter tabs */
        .filter-tabs {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .filter-tab-btn {
            padding: 8px 15px;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .filter-tab-btn.active {
            color: #2c5aa0;
            border-bottom-color: #2c5aa0;
        }

        /* Filter content */
        .filter-content {
            padding: 10px 0;
        }

        .form-check {
            margin-bottom: 10px;
        }

        /* Empty data state */
        .empty-data-state {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            border-radius: 6px;
            z-index: 5;
        }

        .empty-data-state i {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 16px;
        }

        .empty-data-state .empty-text {
            color: #6c757d;
            font-size: 16px;
            font-weight: 500;
        }

        /* NEW: Chart.js styling */
        .chart-filters {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .chart-filters .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .chart-filters .filter-group label {
            font-size: 12px;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 0;
        }

        .chart-canvas-container {
            position: relative;
            height: 350px;
            width: 100%;
        }

        .chart-canvas-container canvas {
            max-height: 350px !important;
        }

        /* Period label styling */
        .period-label {
            font-weight: 600;
            color: #2c5aa0;
            margin-left: 5px;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <!-- Header Dashboard -->
        <div class="header-dashboard">
            <h1 class="header-title">
                Data Performanasi RLEGS
            </h1>
            <p class="header-subtitle">
                Monitoring Pendapatan Witel dan Divisi berdasarkan Periode
            </p>
        </div>

        <!-- Summary Cards - 4 cards in a row -->
        <div class="summary-cards">
            <div class="summary-card rlegs">
                <div class="summary-icon rlegs">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">RLEGS</div>
                    <div class="summary-value">Rp
                        {{ isset($summaryData['RLEGS']) ? number_format($summaryData['RLEGS']['total_real'], 2) : '0.00' }}
                        M</div>
                    <div
                        class="summary-meta {{ isset($summaryData['RLEGS']) && $summaryData['RLEGS']['percentage_change'] >= 0 ? 'up' : 'down' }}">
                        <i
                            class="fas fa-arrow-{{ isset($summaryData['RLEGS']) && $summaryData['RLEGS']['percentage_change'] >= 0 ? 'up' : 'down' }}"></i>
                        {{ isset($summaryData['RLEGS']) ? abs($summaryData['RLEGS']['percentage_change']) : '0.00' }}% dari
                        periode sebelumnya
                    </div>
                </div>
                <div class="summary-percentage red">
                    {{ isset($summaryData['RLEGS']) ? $summaryData['RLEGS']['achievement'] : '0.00' }}%</div>
            </div>

            <div class="summary-card dss">
                <div class="summary-icon dss">
                    <i class="fas fa-building"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">DSS</div>
                    <div class="summary-value">Rp
                        {{ isset($summaryData['DSS']) ? number_format($summaryData['DSS']['total_real'], 2) : '0.00' }} M
                    </div>
                    <div
                        class="summary-meta {{ isset($summaryData['DSS']) && $summaryData['DSS']['percentage_change'] >= 0 ? 'up' : 'down' }}">
                        <i
                            class="fas fa-arrow-{{ isset($summaryData['DSS']) && $summaryData['DSS']['percentage_change'] >= 0 ? 'up' : 'down' }}"></i>
                        {{ isset($summaryData['DSS']) ? abs($summaryData['DSS']['percentage_change']) : '0.00' }}% dari
                        periode sebelumnya
                    </div>
                </div>
                <div class="summary-percentage blue">
                    {{ isset($summaryData['DSS']) ? $summaryData['DSS']['achievement'] : '0.00' }}%</div>
            </div>

            <div class="summary-card dps">
                <div class="summary-icon dps">
                    <i class="fas fa-desktop"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">DPS</div>
                    <div class="summary-value">Rp
                        {{ isset($summaryData['DPS']) ? number_format($summaryData['DPS']['total_real'], 2) : '0.00' }} M
                    </div>
                    <div
                        class="summary-meta {{ isset($summaryData['DPS']) && $summaryData['DPS']['percentage_change'] >= 0 ? 'up' : 'down' }}">
                        <i
                            class="fas fa-arrow-{{ isset($summaryData['DPS']) && $summaryData['DPS']['percentage_change'] >= 0 ? 'up' : 'down' }}"></i>
                        {{ isset($summaryData['DPS']) ? abs($summaryData['DPS']['percentage_change']) : '0.00' }}% dari
                        periode sebelumnya
                    </div>
                </div>
                <div class="summary-percentage cyan">
                    {{ isset($summaryData['DPS']) ? $summaryData['DPS']['achievement'] : '0.00' }}%</div>
            </div>

            <div class="summary-card dgs">
                <div class="summary-icon dgs">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">DGS</div>
                    <div class="summary-value">Rp
                        {{ isset($summaryData['DGS']) ? number_format($summaryData['DGS']['total_real'], 2) : '0.00' }} M
                    </div>
                    <div
                        class="summary-meta {{ isset($summaryData['DGS']) && $summaryData['DGS']['percentage_change'] >= 0 ? 'up' : 'down' }}">
                        <i
                            class="fas fa-arrow-{{ isset($summaryData['DGS']) && $summaryData['DGS']['percentage_change'] >= 0 ? 'up' : 'down' }}"></i>
                        {{ isset($summaryData['DGS']) ? abs($summaryData['DGS']['percentage_change']) : '0.00' }}% dari
                        periode sebelumnya
                    </div>
                </div>
                <div class="summary-percentage yellow">
                    {{ isset($summaryData['DGS']) ? $summaryData['DGS']['achievement'] : '0.00' }}%</div>
            </div>
        </div>

        <!-- Filters Row: Date Picker and Filter Button side by side -->
        <div class="filters-row">
            <!-- Date Filter -->
            <div class="date-filter-container">
                <div class="date-filter" id="dateRangeSelector">
                    <i class="far fa-calendar-alt"></i>
                    <span
                        id="dateRangeText">{{ date('d M Y', strtotime($startDate ?? Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'))) }}
                        -
                        {{ date('d M Y', strtotime($endDate ?? Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'))) }}</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </div>
            </div>

            <!-- Divisi Filter Button -->
            <div class="divisi-filter-container">
                <button type="button" class="filter-button" id="filterButton">
                    <i class="fas fa-filter me-2"></i> Filter Divisi
                    <i class="fas fa-chevron-down ms-2"></i>
                </button>

                <!-- Filter Panel - Hidden by default but will be shown with JS -->
                <div class="card filter-panel" id="filterPanel">
                    <!-- Filter Tabs -->
                    <div class="filter-tabs">
                        <button type="button" class="filter-tab-btn active" data-target="divisiContent">Divisi</button>
                        <button type="button" class="filter-tab-btn" data-target="tregContent">Regional</button>
                    </div>

                    <!-- Filter Contents -->
                    <div class="filter-content" id="divisiContent">
                        @php
                            $divisionList = $divisis ?? ['DSS', 'DPS', 'DGS', 'RLEGS'];
                        @endphp

                        @foreach ($divisionList as $index => $divisi)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="divisi{{ $index }}"
                                    value="{{ $divisi }}">
                                <label class="form-check-label"
                                    for="divisi{{ $index }}">{{ $divisi }}</label>
                            </div>
                        @endforeach
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-sm btn-primary" id="applyDivisiFilter">Terapkan</button>
                        </div>
                    </div>

                    <!-- Regional Content -->
                    <div class="filter-content" id="tregContent" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="treg1" value="TREG 2">
                            <label class="form-check-label" for="treg1">TREG 2</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="treg2" value="TREG 3">
                            <label class="form-check-label" for="treg2">TREG 3</label>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-sm btn-primary" id="applyTregFilter">Terapkan</button>
                        </div>
                    </div>
                </div>

                <!-- Overlay for background when filter is open -->
                <div class="filter-overlay" id="filterOverlay" style="display: none;"></div>
            </div>
        </div>

        <!-- Alert Container -->
        <div class="alert-container mb-4" id="alertContainer" style="display: none;">
            <!-- Alerts will be added dynamically -->
        </div>

        <div class="regions-container">
            <!-- First Row -->
            <div class="region-row">
                <div class="region-box {{ ($selectedRegion ?? 'all') == 'all' ? 'active' : '' }}" data-region="all">
                    Semua Witel
                </div>

                @if (isset($regions) && !empty($regions))
                    @php
                        $regionCount = count($regions);
                        $i = 0;
                    @endphp
                    @foreach ($regions as $region)
                        @if ($i < 3)
                            <div class="region-box {{ ($selectedRegion ?? '') == $region ? 'active' : '' }}"
                                data-region="{{ $region }}">
                                {{ $region }}
                            </div>
                        @endif
                        @php $i++; @endphp
                    @endforeach
                @else
                    @foreach (['Suramadu', 'Nusa Tenggara', 'Jatim Barat'] as $defaultRegion)
                        <div class="region-box {{ ($selectedRegion ?? '') == $defaultRegion ? 'active' : '' }}"
                            data-region="{{ $defaultRegion }}">
                            {{ $defaultRegion }}
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Second Row -->
            <div class="region-row">
                @if (isset($regions) && !empty($regions))
                    @php $i = 0; @endphp
                    @foreach ($regions as $region)
                        @if ($i >= 3 && $i < 7)
                            <div class="region-box {{ ($selectedRegion ?? '') == $region ? 'active' : '' }}"
                                data-region="{{ $region }}">
                                {{ $region }}
                            </div>
                        @endif
                        @php $i++; @endphp
                    @endforeach
                @else
                    @foreach (['Yogya Jateng Selatan', 'Bali', 'Semarang Jateng Utara', 'Solo Jateng Timur'] as $defaultRegion)
                        <div class="region-box {{ ($selectedRegion ?? '') == $defaultRegion ? 'active' : '' }}"
                            data-region="{{ $defaultRegion }}">
                            {{ $defaultRegion }}
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- NEW: Chart.js Charts replacing ApexCharts -->
        <div class="row">
            <!-- Chart 1: Period Performance Chart (replacing Line Chart) -->
            <div class="col-12 chart-container">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h5 class="chart-title">
                                Grafik Performa Periode
                                <span class="period-label" id="periodLabel">{{ $chartData['periodLabel'] ?? 'Mei 2025' }}</span>
                            </h5>
                            <p class="chart-subtitle">Target vs Realisasi berdasarkan periode yang dipilih</p>
                        </div>

                        <div class="chart-filters">
                            <div class="filter-group">
                                <label>Tampilan</label>
                                <select id="chartType" class="selectpicker" data-style="btn-outline-primary">
                                    <option value="combined" selected>Kombinasi</option>
                                    <option value="revenue">Revenue</option>
                                    <option value="achievement">Pencapaian</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div class="chart-canvas-container">
                            <canvas id="periodPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart 2: Stacked Division Chart (NEW) -->
            <div class="col-12 chart-container">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h5 class="chart-title">Breakdown Pendapatan per Divisi & Witel</h5>
                            <p class="chart-subtitle">Distribusi DPS, DSS, DGS berdasarkan witel yang dipilih</p>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div class="chart-canvas-container">
                            <canvas id="stackedDivisionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/js/bootstrap-select.min.js"></script>
    <!-- Chart.js instead of ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get Chart.js data from controller
            const chartData = @json($chartData ?? []);
            console.log('Chart.js data loaded:', chartData);

            // Declare global variables for Chart.js instances
            let periodPerformanceChartInstance;
            let stackedDivisionChartInstance;

            // Initialize Bootstrap Select
            $('.selectpicker').selectpicker({
                liveSearch: true,
                liveSearchPlaceholder: 'Cari opsi...',
                size: 5,
                actionsBox: false,
                dropupAuto: false,
                mobile: false
            });

            // Initialize date range picker
            const dateRangePicker = flatpickr("#dateRangeSelector", {
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

                        // Update charts with new date range
                        updateCharts(selectedDates[0], selectedDates[1]);
                    }
                }
            });

            // Helper function to format date
            function formatDate(date) {
                const day = date.getDate();
                const month = date.toLocaleString('default', { month: 'short' });
                const year = date.getFullYear();
                return `${day} ${month} ${year}`;
            }

            // FILTER BUTTON LOGIC (KEEP EXISTING)
            const filterButton = document.getElementById('filterButton');
            const filterPanel = document.getElementById('filterPanel');
            const filterOverlay = document.getElementById('filterOverlay');

            // Regional Filter Logic
            const regionalButtons = document.querySelectorAll('.region-box');
            let selectedRegional = '{{ $selectedRegional ?? 'all' }}';
            let selectedWitel = '{{ $selectedWitel ?? 'all' }}';

            // Regional button click handler
            regionalButtons.forEach(button => {
                button.addEventListener('click', function() {
                    regionalButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    selectedRegional = this.getAttribute('data-region');
                    updateChartsByRegional(selectedRegional);
                });
            });

            // Toggle filter panel with vanilla JS
            if (filterButton) {
                filterButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (filterPanel.style.display === 'block') {
                        filterPanel.style.display = 'none';
                        filterOverlay.style.display = 'none';
                    } else {
                        filterPanel.style.display = 'block';
                        filterOverlay.style.display = 'block';
                    }
                });
            }

            // Close filter panel when clicking on overlay
            if (filterOverlay) {
                filterOverlay.addEventListener('click', function() {
                    filterPanel.style.display = 'none';
                    filterOverlay.style.display = 'none';
                });
            }

            // Close filter panel when clicking outside
            document.addEventListener('click', function(event) {
                if (
                    filterPanel &&
                    filterPanel.style.display === 'block' &&
                    !filterPanel.contains(event.target) &&
                    event.target !== filterButton &&
                    !(filterButton && filterButton.contains(event.target))
                ) {
                    filterPanel.style.display = 'none';
                    filterOverlay.style.display = 'none';
                }
            });

            // Tab switching in filter panel
            const tabButtons = document.querySelectorAll('.filter-tab-btn');
            const contentPanels = document.querySelectorAll('.filter-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    contentPanels.forEach(panel => panel.style.display = 'none');

                    const targetId = this.getAttribute('data-target');
                    document.getElementById(targetId).style.display = 'block';
                });
            });

            // Apply filter buttons
            const applyDivisiFilter = document.getElementById('applyDivisiFilter');
            if (applyDivisiFilter) {
                applyDivisiFilter.addEventListener('click', function() {
                    const checkedDivisions = Array.from(
                        document.querySelectorAll('#divisiContent input:checked')
                    ).map(cb => cb.value);

                    if (checkedDivisions.length > 0) {
                        applyDivisiFilterFunc(checkedDivisions);
                    } else {
                        showAlert('warning', 'Pilih minimal satu divisi untuk filter');
                    }

                    filterPanel.style.display = 'none';
                    filterOverlay.style.display = 'none';
                });
            }

            // Apply TREG filter
            const applyTregFilter = document.getElementById('applyTregFilter');
            if (applyTregFilter) {
                applyTregFilter.addEventListener('click', function() {
                    const checkedTregs = Array.from(
                        document.querySelectorAll('#tregContent input:checked')
                    ).map(cb => cb.value);

                    if (checkedTregs.length > 0) {
                        selectedRegional = checkedTregs[0];
                        updateChartsByRegional(selectedRegional);

                        regionalButtons.forEach(btn => {
                            btn.classList.remove('active');
                            if (btn.getAttribute('data-region') === selectedRegional) {
                                btn.classList.add('active');
                            }
                        });
                    } else {
                        showAlert('warning', 'Pilih minimal satu TREG untuk filter');
                    }

                    filterPanel.style.display = 'none';
                    filterOverlay.style.display = 'none';
                });
            }

            // Chart Type Selector
            $('#chartType').on('changed.bs.select', function() {
                renderPeriodPerformanceChart($(this).val());
            });

            // Update functions for filters
            function applyDivisiFilterFunc(divisionList) {
                showLoading();
                console.log('Applying division filter:', divisionList);

                const dateRange = dateRangePicker.selectedDates;
                const startDate = dateRange.length > 0 ? dateRange[0] : new Date("{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}");
                const endDate = dateRange.length > 1 ? dateRange[1] : new Date("{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}");

                const formattedStartDate = formatDateForApi(startDate);
                const formattedEndDate = formatDateForApi(endDate);

                updateFilterButtonText(divisionList);

                fetch('{{ route('witel.filter-by-divisi') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            divisi: divisionList,
                            witel: selectedWitel,
                            regional: selectedRegional,
                            start_date: formattedStartDate,
                            end_date: formattedEndDate
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }

                        updateAllCharts(data.chartData);
                        updateSummaryCards(data.summaryData);
                        hideLoading();
                    })
                    .catch(error => {
                        console.error('Error applying division filter:', error);
                        hideLoading();
                        showAlert('error', 'Gagal menerapkan filter: ' + error.message);
                    });
            }

            function updateChartsByRegional(regional) {
                showLoading();

                const dateRange = dateRangePicker.selectedDates;
                const startDate = dateRange.length > 0 ? dateRange[0] : new Date("{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}");
                const endDate = dateRange.length > 1 ? dateRange[1] : new Date("{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}");

                const formattedStartDate = formatDateForApi(startDate);
                const formattedEndDate = formatDateForApi(endDate);

                fetch('{{ route('witel.filter-by-regional') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            regional: regional,
                            witel: selectedWitel,
                            start_date: formattedStartDate,
                            end_date: formattedEndDate
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        updateAllCharts(data.chartData);
                        updateSummaryCards(data.summaryData);
                        hideLoading();
                        showAlert('success', `Data untuk ${regional === 'all' ? 'Semua Regional' : regional} berhasil dimuat`);
                    })
                    .catch(error => {
                        console.error('Error applying regional filter:', error);
                        hideLoading();
                        showAlert('error', 'Gagal menerapkan filter: ' + error.message);
                    });
            }

            function updateCharts(startDate, endDate) {
                showLoading();
                console.log('Updating charts with date range:', formatDate(startDate), '-', formatDate(endDate));

                const formattedStartDate = formatDateForApi(startDate);
                const formattedEndDate = formatDateForApi(endDate);

                fetch('{{ route('witel.update-charts') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            witel: selectedWitel,
                            regional: selectedRegional,
                            divisi: 'all',
                            start_date: formattedStartDate,
                            end_date: formattedEndDate
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            showAlert('warning', data.error);
                            if (data.chartData) {
                                updateAllCharts(data.chartData);
                                updateSummaryCards(data.summaryData);
                            }
                            hideLoading();
                            return;
                        }

                        updateAllCharts(data.chartData);
                        updateSummaryCards(data.summaryData);
                        updatePeriodLabel(data.chartData.periodLabel);

                        hideLoading();
                        showAlert('success', `Data untuk periode ${formatDate(startDate)} - ${formatDate(endDate)} berhasil dimuat`);
                    })
                    .catch(error => {
                        console.error('Error updating charts:', error);
                        hideLoading();
                        showAlert('error', 'Gagal memuat data: ' + error.message);
                    });
            }

            // Helper functions
            function updateFilterButtonText(selectedDivisions) {
                const filterButton = document.getElementById('filterButton');
                if (!filterButton) return;

                if (selectedDivisions.length === 1) {
                    filterButton.innerHTML = `
                        <i class="fas fa-filter me-2"></i>
                        Filter Divisi: <span class="filter-divisi-value">${selectedDivisions[0]}</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    `;
                } else if (selectedDivisions.length > 1 && selectedDivisions.length < 4) {
                    filterButton.innerHTML = `
                        <i class="fas fa-filter me-2"></i>
                        Filter Divisi: <span class="filter-divisi-value">${selectedDivisions.length} Divisi</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    `;
                } else {
                    filterButton.innerHTML = `
                        <i class="fas fa-filter me-2"></i>
                        Filter Divisi
                        <i class="fas fa-chevron-down ms-auto"></i>
                    `;
                }

                showFilterAlert(selectedDivisions);
            }

            function showFilterAlert(selectedDivisions) {
                if (selectedDivisions.length >= 4 || selectedDivisions.length === 0) {
                    const alertContainer = document.getElementById('alertContainer');
                    if (alertContainer) {
                        alertContainer.style.display = 'none';
                    }
                    return;
                }

                let alertText = selectedDivisions.length === 1 ?
                    `Data difilter untuk divisi: ${selectedDivisions[0]}` :
                    `Data difilter untuk divisi: ${selectedDivisions.join(', ')}`;

                const alertContainer = document.getElementById('alertContainer');
                if (!alertContainer) return;

                const alert = document.createElement('div');
                alert.className = `alert alert-light alert-dismissible fade show`;
                alert.style.backgroundColor = '#e8f5e9';
                alert.style.borderColor = '#c8e6c9';
                alert.style.color = '#2e7d32';
                alert.innerHTML = `
                    ${alertText}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                alertContainer.innerHTML = '';
                alertContainer.appendChild(alert);
                alertContainer.style.display = 'block';
            }

            function formatDateForApi(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function showLoading() {
                document.querySelectorAll('.chart-body').forEach(container => {
                    if (!container.querySelector('.loading-overlay')) {
                        const overlay = document.createElement('div');
                        overlay.className = 'loading-overlay';
                        overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                        container.appendChild(overlay);
                    }
                });
            }

            function hideLoading() {
                document.querySelectorAll('.loading-overlay').forEach(overlay => {
                    overlay.remove();
                });
            }

            function showAlert(type, message) {
                const alertContainer = document.getElementById('alertContainer');
                if (!alertContainer) return;

                const alert = document.createElement('div');
                alert.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
                alert.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                alertContainer.innerHTML = '';
                alertContainer.appendChild(alert);
                alertContainer.style.display = 'block';

                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.classList.remove('show');
                        setTimeout(() => {
                            if (alert.parentNode) {
                                alertContainer.style.display = 'none';
                                alert.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            }

            function showEmptyDataState(container) {
                const emptyState = document.createElement('div');
                emptyState.className = 'empty-data-state';
                emptyState.innerHTML = `
                    <i class="fas fa-chart-bar"></i>
                    <div class="empty-text">Maaf, belum ada data yang tercatat</div>
                `;

                if (!container.querySelector('.empty-data-state')) {
                    container.appendChild(emptyState);
                }
            }

            function updatePeriodLabel(newLabel) {
                const periodLabelEl = document.getElementById('periodLabel');
                if (periodLabelEl && newLabel) {
                    periodLabelEl.textContent = newLabel;
                }
            }

            // Chart.js rendering functions
            function updateAllCharts(data) {
                if (!data) return;

                console.log('Updating Chart.js charts with data:', data);

                // Check if data is empty
                if (data.isEmpty === true) {
                    const chartContainers = ["#periodPerformanceChart", "#stackedDivisionChart"];

                    chartContainers.forEach(chartId => {
                        const container = document.querySelector(chartId).parentElement;
                        showEmptyDataState(container);

                        // Destroy existing chart instances
                        if (chartId === "#periodPerformanceChart" && periodPerformanceChartInstance) {
                            periodPerformanceChartInstance.destroy();
                            periodPerformanceChartInstance = null;
                        }
                        if (chartId === "#stackedDivisionChart" && stackedDivisionChartInstance) {
                            stackedDivisionChartInstance.destroy();
                            stackedDivisionChartInstance = null;
                        }
                    });

                    return;
                }

                // Remove empty states if data exists
                const chartContainers = ["#periodPerformanceChart", "#stackedDivisionChart"];
                chartContainers.forEach(chartId => {
                    const container = document.querySelector(chartId).parentElement;
                    const existingEmpty = container.querySelector('.empty-data-state');
                    if (existingEmpty) {
                        existingEmpty.remove();
                    }
                });

                // Update Period Performance Chart
                if (data.periodPerformance) {
                    if (!periodPerformanceChartInstance) {
                        renderPeriodPerformanceChart('combined', data.periodPerformance);
                    } else {
                        // Update existing chart with new data
                        const chartType = $('#chartType').val() || 'combined';
                        renderPeriodPerformanceChart(chartType, data.periodPerformance);
                    }
                }

                // Update Stacked Division Chart
                if (data.stackedDivision) {
                    if (!stackedDivisionChartInstance) {
                        renderStackedDivisionChart(data.stackedDivision);
                    } else {
                        stackedDivisionChartInstance.destroy();
                        renderStackedDivisionChart(data.stackedDivision);
                    }
                }

                // Update period label
                if (data.periodLabel) {
                    updatePeriodLabel(data.periodLabel);
                }
            }

            function renderPeriodPerformanceChart(type, data = null) {
                const ctx = document.getElementById('periodPerformanceChart');
                if (!ctx) {
                    console.error('Period performance chart canvas not found');
                    return;
                }

                // Use provided data or fallback to global chartData
                const performanceData = data || (chartData && chartData.periodPerformance ? chartData.periodPerformance : null);

                if (!performanceData) {
                    console.error('No performance data available');
                    return;
                }

                // Destroy existing chart
                if (periodPerformanceChartInstance) {
                    periodPerformanceChartInstance.destroy();
                }

                // Prepare data for Chart.js
                const labels = ['Target', 'Realisasi']; // Simple labels for period view
                const datasets = [];

                if (type === 'combined' || type === 'revenue') {
                    datasets.push({
                        label: 'Target Revenue',
                        data: [performanceData.target_revenue / 1000000, 0], // Convert to millions
                        backgroundColor: 'rgba(28, 41, 85, 0.6)',
                        borderColor: 'rgba(28, 41, 85, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    });

                    datasets.push({
                        label: 'Real Revenue',
                        data: [0, performanceData.real_revenue / 1000000], // Convert to millions
                        backgroundColor: 'rgba(59, 125, 221, 0.6)',
                        borderColor: 'rgba(59, 125, 221, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    });
                }

                if (type === 'combined' || type === 'achievement') {
                    datasets.push({
                        label: 'Pencapaian (%)',
                        data: [performanceData.achievement, performanceData.achievement],
                        type: 'line',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: '#1C2955',
                        borderWidth: 2,
                        pointBackgroundColor: '#1C2955',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#1C2955',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: false,
                        tension: 0.3,
                        yAxisID: 'y1'
                    });
                }

                // Configure scales
                const scales = {};

                if (type === 'combined' || type === 'revenue') {
                    scales.y = {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (Juta Rp)',
                            font: { weight: 'bold' }
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + ' T';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(1) + ' M';
                                } else {
                                    return 'Rp ' + value.toFixed(1) + ' Jt';
                                }
                            }
                        }
                    };
                }

                if (type === 'combined' || type === 'achievement') {
                    scales.y1 = {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Pencapaian (%)',
                            font: { weight: 'bold' }
                        },
                        grid: {
                            drawOnChartArea: type !== 'combined',
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    };
                }

                // Create new chart
                periodPerformanceChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: scales,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(28, 41, 85, 0.8)',
                                titleFont: { weight: 'bold', size: 14 },
                                bodyFont: { size: 13 },
                                padding: 12,
                                cornerRadius: 6,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }

                                        if (context.dataset.yAxisID === 'y1') {
                                            label += context.parsed.y.toFixed(2) + '%';
                                        } else {
                                            const value = context.parsed.y;
                                            if (value >= 1000000) {
                                                label += 'Rp ' + (value / 1000000).toFixed(2) + ' T';
                                            } else if (value >= 1000) {
                                                label += 'Rp ' + (value / 1000).toFixed(2) + ' M';
                                            } else {
                                                label += 'Rp ' + new Intl.NumberFormat('id-ID').format(value) + ' Jt';
                                            }
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            function renderStackedDivisionChart(data) {
                const ctx = document.getElementById('stackedDivisionChart');
                if (!ctx) {
                    console.error('Stacked division chart canvas not found');
                    return;
                }

                // Destroy existing chart
                if (stackedDivisionChartInstance) {
                    stackedDivisionChartInstance.destroy();
                }

                if (!data || !data.labels || data.labels.length === 0) {
                    console.log('No stacked division data available');
                    return;
                }

                // Create new stacked bar chart
                stackedDivisionChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            x: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'Witel'
                                }
                            },
                            y: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'Revenue (Juta Rp)',
                                    font: { weight: 'bold' }
                                },
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toFixed(1) + ' M';
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(28, 41, 85, 0.8)',
                                titleFont: { weight: 'bold', size: 14 },
                                bodyFont: { size: 13 },
                                padding: 12,
                                cornerRadius: 6,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += 'Rp ' + context.parsed.y.toFixed(2) + ' M';
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            function updateSummaryCards(summaryData) {
                if (!summaryData) return;

                ['RLEGS', 'DSS', 'DPS', 'DGS'].forEach(division => {
                    if (summaryData[division]) {
                        updateSummaryCard(division, summaryData[division]);
                    }
                });
            }

            function updateSummaryCard(division, data) {
                const card = document.querySelector(`.summary-card.${division.toLowerCase()}`);
                if (!card) return;

                const valueEl = card.querySelector('.summary-value');
                if (valueEl) {
                    valueEl.textContent = `Rp ${numberFormat(data.total_real)} M`;
                }

                const metaEl = card.querySelector('.summary-meta');
                if (metaEl) {
                    metaEl.className = `summary-meta ${data.percentage_change >= 0 ? 'up' : 'down'}`;

                    const percentText = `${Math.abs(data.percentage_change).toFixed(2)}% dari periode sebelumnya`;
                    metaEl.innerHTML = `<i class="fas fa-arrow-${data.percentage_change >= 0 ? 'up' : 'down'}"></i> ${percentText}`;
                }

                const percentageEl = card.querySelector('.summary-percentage');
                if (percentageEl) {
                    percentageEl.textContent = `${data.achievement.toFixed(2)}%`;
                }
            }

            function numberFormat(number) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(number);
            }

            // Initialize charts on page load
            function initializeCharts() {
                console.log('Initializing Chart.js charts with data:', chartData);

                if (!chartData) {
                    console.log('No chart data available');
                    showEmptyDataState(document.querySelector("#periodPerformanceChart").parentElement);
                    showEmptyDataState(document.querySelector("#stackedDivisionChart").parentElement);
                    return;
                }

                // Check if data is empty
                if (chartData.isEmpty === true) {
                    console.log('Chart data is empty');
                    showEmptyDataState(document.querySelector("#periodPerformanceChart").parentElement);
                    showEmptyDataState(document.querySelector("#stackedDivisionChart").parentElement);
                    return;
                }

                // Initialize charts if data exists
                if (chartData.periodPerformance) {
                    renderPeriodPerformanceChart('combined', chartData.periodPerformance);
                }

                if (chartData.stackedDivision) {
                    renderStackedDivisionChart(chartData.stackedDivision);
                }
            }

            // Initialize charts on page load
            initializeCharts();
        });
    </script>
@endsection
@extends('layouts.main')

@section('title', 'Visualisasi Data Performa RLEGS')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
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

        .date-filter .fa-chevron-down {
            margin-left: 10px;
            font-size: 12px;
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
    </style>
@endsection

@section('content')
    {{-- untuk bisa commit  --}}
    <div class="main-content">
        <!-- Header Dashboard -->
        <div class="header-dashboard">
            <h1 class="header-title">
                <i class="fas fa-chart-line me-2"></i> Visualisasi Data Performa RLEGS Telkom
            </h1>
            <p class="header-subtitle">
                Monitoring pendapatan witel dan divisi berdasarkan periode
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
                <!-- PERBAIKAN: Hanya menggunakan satu elemen untuk date picker (tanpa input hidden) -->
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
                            <!-- Show first 3 regions + 'Semua Witel' in first row -->
                            <div class="region-box {{ ($selectedRegion ?? '') == $region ? 'active' : '' }}"
                                data-region="{{ $region }}">
                                {{ $region }}
                            </div>
                        @endif
                        @php $i++; @endphp
                    @endforeach
                @else
                    <!-- Default Regions first row -->
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
                            <!-- Show the remaining regions in second row (up to 4) -->
                            <div class="region-box {{ ($selectedRegion ?? '') == $region ? 'active' : '' }}"
                                data-region="{{ $region }}">
                                {{ $region }}
                            </div>
                        @endif
                        @php $i++; @endphp
                    @endforeach
                @else
                    <!-- Default Regions second row -->
                    @foreach (['Yogya Jateng Selatan', 'Bali', 'Semarang Jateng Utara', 'Solo Jateng Timur'] as $defaultRegion)
                        <div class="region-box {{ ($selectedRegion ?? '') == $defaultRegion ? 'active' : '' }}"
                            data-region="{{ $defaultRegion }}">
                            {{ $defaultRegion }}
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Charts in a single column layout -->
        <div class="row">
            <!-- Line Chart - Target vs Real Revenue -->
            <div class="col-12 chart-container">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h5 class="chart-title">Target vs. Realisasi Revenue</h5>
                            <p class="chart-subtitle">Perbandingan target dan pencapaian pendapatan per bulan</p>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div id="lineRevenueChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Bar Chart - Revenue by Division -->
            <div class="col-12 chart-container">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h5 class="chart-title">Revenue Berdasarkan Divisi</h5>
                            <p class="chart-subtitle">Perbandingan target dan realisasi pendapatan</p>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div id="barDivisionChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Donut Chart - Achievement per Division -->
            <div class="col-12 chart-container">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h5 class="chart-title">Achievement per Divisi</h5>
                            <p class="chart-subtitle">Persentase pencapaian target</p>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div id="donutAchievementChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

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
                        document.getElementById('dateRangeText').textContent = startDate + ' - ' +
                            endDate;

                        // Update charts with new date range
                        updateCharts(selectedDates[0], selectedDates[1]);
                    }
                }
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

            // FILTER BUTTON LOGIC
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

                    // Update chart berdasarkan regional yang dipilih
                    updateChartsByRegional(selectedRegional);
                });
            });

            // Find existing witel filter button and add event listeners
            const witelFilterButton = document.querySelector('button[data-bs-toggle="dropdown"]');
            const witelOptions = document.querySelectorAll('.dropdown-item');

            if (witelFilterButton && witelOptions) {
                witelOptions.forEach(option => {
                    option.addEventListener('click', function(e) {
                        e.preventDefault();
                        selectedWitel = this.getAttribute('data-value') || 'all';

                        // Update button text
                        witelFilterButton.innerHTML = this.innerHTML;

                        // Update chart
                        updateChartsByWitel(selectedWitel);
                    });
                });
            }

            // Toggle filter panel with vanilla JS
            if (filterButton) {
                filterButton.addEventListener('click', function(e) {
                    console.log('Filter button clicked');
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
                        // Update selectedRegional berdasarkan TREG yang dipilih
                        selectedRegional = checkedTregs[0]; // Ambil yang pertama jika multiple

                        // Update chart dengan filter regional
                        updateChartsByRegional(selectedRegional);

                        // Update region-box appearance to match selection
                        regionalButtons.forEach(btn => {
                            btn.classList.remove('active');
                            // Map TREG values to region names if needed
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

            // Update region-box click handler untuk sinkronisasi dengan TREG checkbox
            const regionBoxes = document.querySelectorAll('.region-box');
            regionBoxes.forEach(box => {
                box.addEventListener('click', function() {
                    regionBoxes.forEach(rb => rb.classList.remove('active'));
                    this.classList.add('active');

                    const selectedRegion = this.getAttribute('data-region');
                    selectedRegional = selectedRegion;

                    // Update TREG checkboxes berdasarkan region yang dipilih
                    const tregCheckboxes = document.querySelectorAll('#tregContent input');
                    tregCheckboxes.forEach(checkbox => {
                        checkbox.checked = checkbox.value === selectedRegion;
                    });

                    updateChartsByRegional(selectedRegion);
                });
            });

            // Update function for division filter
            function applyDivisiFilterFunc(divisionList) {
                showLoading();
                console.log('Applying division filter:', divisionList);

                const dateRange = dateRangePicker.selectedDates;
                const startDate = dateRange.length > 0 ? dateRange[0] : new Date(
                    "{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}");
                const endDate = dateRange.length > 1 ? dateRange[1] : new Date(
                    "{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}");

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

            // Update function for witel filter
            function updateChartsByWitel(witel) {
                showLoading();

                const dateRange = dateRangePicker.selectedDates;
                const startDate = dateRange.length > 0 ? dateRange[0] : new Date(
                    "{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}");
                const endDate = dateRange.length > 1 ? dateRange[1] : new Date(
                    "{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}");

                const formattedStartDate = formatDateForApi(startDate);
                const formattedEndDate = formatDateForApi(endDate);

                fetch('{{ route('witel.filter-by-witel') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            witel: witel,
                            regional: selectedRegional,
                            start_date: formattedStartDate,
                            end_date: formattedEndDate
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        updateAllCharts(data.chartData);
                        updateSummaryCards(data.summaryData);
                        hideLoading();
                        showAlert('success',
                            `Data untuk ${witel === 'all' ? 'Semua Witel' : witel} berhasil dimuat`);
                    })
                    .catch(error => {
                        console.error('Error applying witel filter:', error);
                        hideLoading();
                        showAlert('error', 'Gagal menerapkan filter: ' + error.message);
                    });
            }

            // Update function for regional filter
            function updateChartsByRegional(regional) {
                showLoading();

                const dateRange = dateRangePicker.selectedDates;
                const startDate = dateRange.length > 0 ? dateRange[0] : new Date(
                    "{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}");
                const endDate = dateRange.length > 1 ? dateRange[1] : new Date(
                    "{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}");

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
                        showAlert('success',
                            `Data untuk ${regional === 'all' ? 'Semua Regional' : regional} berhasil dimuat`
                        );
                    })
                    .catch(error => {
                        console.error('Error applying regional filter:', error);
                        hideLoading();
                        showAlert('error', 'Gagal menerapkan filter: ' + error.message);
                    });
            }

            // Function to update charts with new date range
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

                        hideLoading();
                        showAlert('success',
                            `Data untuk periode ${formatDate(startDate)} - ${formatDate(endDate)} berhasil dimuat`
                        );
                    })
                    .catch(error => {
                        console.error('Error updating charts:', error);
                        hideLoading();
                        showAlert('error', 'Gagal memuat data: ' + error.message);
                    });
            }

            // Function to update filter button text
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

            // Function to show filter alert
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

            // Helper function to format date for API
            function formatDateForApi(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            // Function to show loading state
            function showLoading() {
                document.querySelectorAll('.chart-body').forEach(container => {
                    if (!container.querySelector('.loading-overlay')) {
                        const overlay = document.createElement('div');
                        overlay.className = 'loading-overlay';
                        overlay.innerHTML =
                            '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                        container.appendChild(overlay);
                    }
                });
            }

            // Function to hide loading state
            function hideLoading() {
                document.querySelectorAll('.loading-overlay').forEach(overlay => {
                    overlay.remove();
                });
            }

            // Function to show alert message
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

            // Function to show empty data state
            function showEmptyDataState(container) {
                const emptyState = document.createElement('div');
                emptyState.className = 'empty-data-state';
                emptyState.innerHTML = `
                    <i class="fas fa-chart-bar"></i>
                    <div class="empty-text">Maaf, belum ada data yang tercatat</div>
                `;

                // Check if already exists, if so, don't add again
                if (!container.querySelector('.empty-data-state')) {
                    container.appendChild(emptyState);
                }
            }

            // Updated updateAllCharts function
            function updateAllCharts(data) {
                if (!data) return;

                console.log('Updating charts with data:', data);

                // Check if data is empty
                if (data.isEmpty === true) {
                    // Show empty state for all charts
                    const chartContainers = [
                        "#lineRevenueChart",
                        "#barDivisionChart",
                        "#donutAchievementChart"
                    ];

                    chartContainers.forEach(chartId => {
                        const container = document.querySelector(chartId).parentElement;
                        showEmptyDataState(container);

                        // Destroy any existing chart instances
                        if (chartId === "#lineRevenueChart" && lineRevenueChartInstance) {
                            lineRevenueChartInstance.destroy();
                            lineRevenueChartInstance = null;
                        }
                        if (chartId === "#barDivisionChart" && barDivisionChartInstance) {
                            barDivisionChartInstance.destroy();
                            barDivisionChartInstance = null;
                        }
                        if (chartId === "#donutAchievementChart" && donutAchievementChartInstance) {
                            donutAchievementChartInstance.destroy();
                            donutAchievementChartInstance = null;
                        }
                    });

                    return;
                }

                // If we have data, remove any existing empty states
                const chartContainers = [
                    "#lineRevenueChart",
                    "#barDivisionChart",
                    "#donutAchievementChart"
                ];

                chartContainers.forEach(chartId => {
                    const container = document.querySelector(chartId).parentElement;
                    const existingEmpty = container.querySelector('.empty-data-state');
                    if (existingEmpty) {
                        existingEmpty.remove();
                    }
                });

                // Update Line Chart
                if (data.lineChart) {
                    if (!lineRevenueChartInstance) {
                        initializeLineChart(data.lineChart);
                    } else {
                        lineRevenueChartInstance.updateOptions({
                            xaxis: {
                                categories: data.lineChart.months
                            },
                            series: data.lineChart.series
                        });
                    }
                }

                // Update Bar Chart
                if (data.barChart) {
                    if (!barDivisionChartInstance) {
                        initializeBarChart(data.barChart);
                    } else {
                        barDivisionChartInstance.updateOptions({
                            xaxis: {
                                categories: data.barChart.divisions
                            },
                            series: data.barChart.series
                        });
                    }
                }

                // Update Donut Chart
                if (data.donutChart) {
                    if (!donutAchievementChartInstance) {
                        initializeDonutChart(data.donutChart);
                    } else {
                        donutAchievementChartInstance.updateOptions({
                            labels: data.donutChart.labels,
                            series: data.donutChart.series
                        });
                    }
                }

                // Update Performance Witel Chart if exists
                if (data.witelPerformance && performanceWitelChartInstance) {
                    performanceWitelChartInstance.updateOptions({
                        xaxis: {
                            categories: data.witelPerformance.categories
                        },
                        series: [{
                            name: 'Achievement (%)',
                            data: data.witelPerformance.data
                        }]
                    });
                }
            }

            // Function to update summary cards with new data
            function updateSummaryCards(summaryData) {
                if (!summaryData) return;

                ['RLEGS', 'DSS', 'DPS', 'DGS'].forEach(division => {
                    if (summaryData[division]) {
                        updateSummaryCard(division, summaryData[division]);
                    }
                });
            }

            // Helper function to update individual summary card
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

                    const iconEl = metaEl.querySelector('i');
                    if (iconEl) {
                        iconEl.className = `fas fa-arrow-${data.percentage_change >= 0 ? 'up' : 'down'}`;
                    }

                    const percentText = `${Math.abs(data.percentage_change).toFixed(2)}% dari periode sebelumnya`;
                    metaEl.innerHTML =
                        `<i class="fas fa-arrow-${data.percentage_change >= 0 ? 'up' : 'down'}"></i> ${percentText}`;
                }

                const percentageEl = card.querySelector('.summary-percentage');
                if (percentageEl) {
                    percentageEl.textContent = `${data.achievement.toFixed(2)}%`;
                }
            }

            // Helper function to format numbers
            function numberFormat(number) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(number);
            }

            // Initialize line chart function with updated colors
            function initializeLineChart(data) {
                const lineChartOptions = {
                    chart: {
                        type: 'line',
                        height: 350,
                        toolbar: {
                            show: true
                        },
                        zoom: {
                            enabled: true
                        },
                        fontFamily: "'Poppins', 'Helvetica', 'Arial', sans-serif"
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    colors: ['#3b7ddd', '#10b981'], // Blue for Real Revenue, Green for Target Revenue
                    series: data.series || [],
                    grid: {
                        borderColor: '#e0e0e0',
                        row: {
                            colors: ['#f8f9fa', 'transparent'],
                            opacity: 0.5
                        }
                    },
                    markers: {
                        size: 5
                    },
                    xaxis: {
                        categories: data.months || [],
                        title: {
                            text: 'Bulan'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Revenue (Juta Rupiah)'
                        },
                        labels: {
                            formatter: function(val) {
                                return "Rp " + numberFormat(val) + " M";
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return "Rp " + numberFormat(val) + " M";
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        floating: true,
                        offsetY: -25,
                        offsetX: -5
                    }
                };

                const lineChartEl = document.querySelector("#lineRevenueChart");
                if (lineChartEl) {
                    lineRevenueChartInstance = new ApexCharts(lineChartEl, lineChartOptions);
                    lineRevenueChartInstance.render();
                }
            }

            // Initialize donut chart function
            function initializeDonutChart(data) {
                const donutChartOptions = {
                    chart: {
                        type: 'donut',
                        height: 350,
                        fontFamily: "'Poppins', 'Helvetica', 'Arial', sans-serif"
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Achievement',
                                        formatter: function(w) {
                                            const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            const len = w.globals.seriesTotals.length;
                                            return numberFormat(sum / len) + '%';
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return numberFormat(opts.w.globals.series[opts.seriesIndex]) + '%';
                        }
                    },
                    colors: ['#3b7ddd', '#10b981', '#f59e0b', '#ef4444'],
                    series: data.series || [],
                    labels: data.labels || [],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 320
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }],
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return numberFormat(val) + "%";
                            }
                        }
                    }
                };

                const donutChartEl = document.querySelector("#donutAchievementChart");
                if (donutChartEl) {
                    donutAchievementChartInstance = new ApexCharts(donutChartEl, donutChartOptions);
                    donutAchievementChartInstance.render();
                }
            }

            // Initialize bar chart function
            function initializeBarChart(data) {
                const barChartOptions = {
                    chart: {
                        type: 'bar',
                        height: 350,
                        stacked: false,
                        toolbar: {
                            show: true
                        },
                        fontFamily: "'Poppins', 'Helvetica', 'Arial', sans-serif"
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded',
                            borderRadius: 4
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    colors: ['#3b7ddd', '#10b981', '#f59e0b'],
                    series: data.series || [],
                    xaxis: {
                        categories: data.divisions || [],
                        title: {
                            text: 'Divisi'
                        }
                    },
                    yaxis: [{
                            seriesName: 'Target',
                            title: {
                                text: 'Revenue (Juta Rupiah)'
                            },
                            labels: {
                                formatter: function(val) {
                                    return "Rp " + numberFormat(val) + " M";
                                }
                            }
                        },
                        {
                            seriesName: 'Realisasi',
                            show: false
                        },
                        {
                            opposite: true,
                            seriesName: 'Achievement (%)',
                            title: {
                                text: 'Achievement (%)'
                            },
                            labels: {
                                formatter: function(val) {
                                    return numberFormat(val) + "%";
                                }
                            }
                        }
                    ],
                    tooltip: {
                        y: {
                            formatter: function(val, {
                                seriesIndex
                            }) {
                                if (seriesIndex === 2) {
                                    return numberFormat(val) + "%";
                                }
                                return "Rp " + numberFormat(val) + " M";
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'center'
                    },
                    fill: {
                        opacity: 1
                    }
                };

                const barChartEl = document.querySelector("#barDivisionChart");
                if (barChartEl) {
                    barDivisionChartInstance = new ApexCharts(barChartEl, barChartOptions);
                    barDivisionChartInstance.render();
                }
            }

            // Initialize all charts
            function initializeCharts() {
                console.log('Initializing charts with data:', chartData);

                if (!chartData) {
                    console.log('No chart data available');
                    showEmptyDataState(document.querySelector("#lineRevenueChart").parentElement);
                    showEmptyDataState(document.querySelector("#donutAchievementChart").parentElement);
                    showEmptyDataState(document.querySelector("#barDivisionChart").parentElement);
                    return;
                }

                // Check if data is empty
                if (chartData.isEmpty === true) {
                    console.log('Chart data is empty');
                    showEmptyDataState(document.querySelector("#lineRevenueChart").parentElement);
                    showEmptyDataState(document.querySelector("#donutAchievementChart").parentElement);
                    showEmptyDataState(document.querySelector("#barDivisionChart").parentElement);
                    return;
                }

                // Initialize charts only if data exists
                if (chartData.lineChart) {
                    initializeLineChart(chartData.lineChart);
                }

                if (chartData.barChart) {
                    initializeBarChart(chartData.barChart);
                }

                if (chartData.donutChart) {
                    initializeDonutChart(chartData.donutChart);
                }
            }

            // Initialize charts on page load
            initializeCharts();
        });
    </script>
@endsection
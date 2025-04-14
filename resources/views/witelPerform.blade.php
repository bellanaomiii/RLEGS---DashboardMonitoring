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

    .date-filter-container, .divisi-filter-container {
        position: relative;
        min-width: 200px;
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
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        z-index: 1050;
        padding: 15px;
        display: none;
    }

    /* Regions container styling */
    .regions-container {
        display: flex;
        justify-content: flex-start;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 25px;
        width: 100%;
    }

    .region-box {
        flex: 0 0 auto;
        padding: 8px 12px;
        text-align: center;
        min-width: 100px;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 6px;
        background-color: #f0f2f5;
        color: #495057;
    }

    .region-box.active {
        background-color: #3b7ddd;
        color: white;
        font-weight: 500;
    }

    .region-box:hover:not(.active) {
        background-color: #d8e0f0;
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
        color: #3b7ddd;
        border-bottom-color: #3b7ddd;
    }

    /* Filter content */
    .filter-content {
        padding: 10px 0;
    }

    .form-check {
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <!-- Header Dashboard -->
    <div class="header-dashboard">
        <h1 class="header-title">
            <i class="fas fa-chart-line me-2"></i> Visualisasi Data Performa RLEGS
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
                <div class="summary-value">Rp {{ isset($summaryData['RLEGS']) ? number_format($summaryData['RLEGS']['total_real'], 2) : '0.00' }} M</div>
                <div class="summary-meta {{ isset($summaryData['RLEGS']) && $summaryData['RLEGS']['percentage_change'] >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ isset($summaryData['RLEGS']) && $summaryData['RLEGS']['percentage_change'] >= 0 ? 'up' : 'down' }}"></i>
                    {{ isset($summaryData['RLEGS']) ? abs($summaryData['RLEGS']['percentage_change']) : '0.00' }}% dari periode sebelumnya
                </div>
            </div>
            <div class="summary-percentage red">{{ isset($summaryData['RLEGS']) ? $summaryData['RLEGS']['achievement'] : '0.00' }}%</div>
        </div>

        <div class="summary-card dss">
            <div class="summary-icon dss">
                <i class="fas fa-building"></i>
            </div>
            <div class="summary-content">
                <div class="summary-label">DSS</div>
                <div class="summary-value">Rp {{ isset($summaryData['DSS']) ? number_format($summaryData['DSS']['total_real'], 2) : '0.00' }} M</div>
                <div class="summary-meta {{ isset($summaryData['DSS']) && $summaryData['DSS']['percentage_change'] >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ isset($summaryData['DSS']) && $summaryData['DSS']['percentage_change'] >= 0 ? 'up' : 'down' }}"></i>
                    {{ isset($summaryData['DSS']) ? abs($summaryData['DSS']['percentage_change']) : '0.00' }}% dari periode sebelumnya
                </div>
            </div>
            <div class="summary-percentage blue">{{ isset($summaryData['DSS']) ? $summaryData['DSS']['achievement'] : '0.00' }}%</div>
        </div>

        <div class="summary-card dps">
            <div class="summary-icon dps">
                <i class="fas fa-desktop"></i>
            </div>
            <div class="summary-content">
                <div class="summary-label">DPS</div>
                <div class="summary-value">Rp {{ isset($summaryData['DPS']) ? number_format($summaryData['DPS']['total_real'], 2) : '0.00' }} M</div>
                <div class="summary-meta {{ isset($summaryData['DPS']) && $summaryData['DPS']['percentage_change'] >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ isset($summaryData['DPS']) && $summaryData['DPS']['percentage_change'] >= 0 ? 'up' : 'down' }}"></i>
                    {{ isset($summaryData['DPS']) ? abs($summaryData['DPS']['percentage_change']) : '0.00' }}% dari periode sebelumnya
                </div>
            </div>
            <div class="summary-percentage cyan">{{ isset($summaryData['DPS']) ? $summaryData['DPS']['achievement'] : '0.00' }}%</div>
        </div>

        <div class="summary-card dgs">
            <div class="summary-icon dgs">
                <i class="fas fa-globe"></i>
            </div>
            <div class="summary-content">
                <div class="summary-label">DGS</div>
                <div class="summary-value">Rp {{ isset($summaryData['DGS']) ? number_format($summaryData['DGS']['total_real'], 2) : '0.00' }} M</div>
                <div class="summary-meta {{ isset($summaryData['DGS']) && $summaryData['DGS']['percentage_change'] >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ isset($summaryData['DGS']) && $summaryData['DGS']['percentage_change'] >= 0 ? 'up' : 'down' }}"></i>
                    {{ isset($summaryData['DGS']) ? abs($summaryData['DGS']['percentage_change']) : '0.00' }}% dari periode sebelumnya
                </div>
            </div>
            <div class="summary-percentage yellow">{{ isset($summaryData['DGS']) ? $summaryData['DGS']['achievement'] : '0.00' }}%</div>
        </div>
    </div>

    <!-- Filters Row: Date Picker and Filter Button side by side -->
    <div class="filters-row">
        <!-- Date Filter -->
        <div class="date-filter-container">
            <div class="date-filter" id="dateRangeSelector">
                <i class="far fa-calendar-alt"></i>
                <span id="dateRangeText">{{ date('d M Y', strtotime($startDate ?? Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'))) }} - {{ date('d M Y', strtotime($endDate ?? Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'))) }}</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </div>
        </div>

        <!-- Divisi Filter Button -->
        <div class="divisi-filter-container">
            <button class="filter-button" id="filterButton">
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

                    @foreach($divisionList as $index => $divisi)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="divisi{{ $index }}" value="{{ $divisi }}">
                            <label class="form-check-label" for="divisi{{ $index }}">{{ $divisi }}</label>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-sm btn-primary" id="applyDivisiFilter">Terapkan</button>
                    </div>
                </div>

                <!-- Regional Content -->
                <div class="filter-content" id="tregContent" style="display: none;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="treg1" value="TREG2">
                        <label class="form-check-label" for="treg1">TREG 2</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="treg2" value="TREG3">
                        <label class="form-check-label" for="treg2">TREG 3</label>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-sm btn-primary" id="applyTregFilter">Terapkan</button>
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

    <!-- Region Selection Buttons -->
    <div class="regions-container">
        <div class="region-box {{ ($selectedRegion ?? 'all') == 'all' ? 'active' : '' }}" data-region="all">
            Semua Witel
        </div>

        @if(isset($regions) && !empty($regions))
            @foreach($regions as $region)
                <div class="region-box {{ ($selectedRegion ?? '') == $region ? 'active' : '' }}" data-region="{{ $region }}">
                    {{ $region }}
                </div>
            @endforeach
        @else
            <!-- Default Regions jika $regions tidak tersedia -->
            @foreach(['Suramadu', 'Nusa Tenggara', 'Jatim Barat', 'Yogya Jateng Selatan', 'Bali', 'Semarang Jateng Utara', 'Solo Jateng Timur', 'Jatim Timur'] as $defaultRegion)
                <div class="region-box {{ ($selectedRegion ?? '') == $defaultRegion ? 'active' : '' }}" data-region="{{ $defaultRegion }}">
                    {{ $defaultRegion }}
                </div>
            @endforeach
        @endif
    </div>

    <!-- Charts in a single column layout -->
    <div class="row">
        <!-- Line Chart - Target vs Real Revenue -->
        <div class="col-12 chart-container">
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h5 class="chart-title">Target vs. Realisasi Revenue</h5>
                        <p class="chart-subtitle">Perbandingan pendapatan {{ date('Y') }} vs {{ date('Y')-1 }}</p>
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

        <!-- Performance Witel Chart -->
        <div class="col-12 chart-container">
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h5 class="chart-title">Performa Witel</h5>
                        <p class="chart-subtitle">Perbandingan pencapaian antar witel</p>
                    </div>
                </div>
                <div class="chart-body">
                    <div id="performanceWitelChart" style="height: 350px;"></div>
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

    // Debugging untuk regions
    const regions = @json($regions ?? []);
    console.log('Regions available:', regions);

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

    // FILTER BUTTON LOGIC
    const filterButton = document.getElementById('filterButton');
    const filterPanel = document.getElementById('filterPanel');
    const filterOverlay = document.getElementById('filterOverlay');

    // Toggle filter panel with vanilla JS
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

    // Close filter panel when clicking on overlay
    filterOverlay.addEventListener('click', function() {
        filterPanel.style.display = 'none';
        filterOverlay.style.display = 'none';
    });

    // Close filter panel when clicking outside
    document.addEventListener('click', function(event) {
        if (
            filterPanel.style.display === 'block' &&
            !filterPanel.contains(event.target) &&
            event.target !== filterButton &&
            !filterButton.contains(event.target)
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
            // Remove active class from all buttons
            tabButtons.forEach(btn => btn.classList.remove('active'));

            // Add active class to clicked button
            this.classList.add('active');

            // Hide all content panels
            contentPanels.forEach(panel => panel.style.display = 'none');

            // Show the target content panel
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).style.display = 'block';
        });
    });

    // Apply filter buttons
    document.getElementById('applyDivisiFilter').addEventListener('click', function() {
        // Get checked division checkboxes
        const checkedDivisions = Array.from(
            document.querySelectorAll('#divisiContent input:checked')
        ).map(cb => cb.value);

        // Apply division filter
        if (checkedDivisions.length > 0) {
            applyDivisiFilter(checkedDivisions);
        } else {
            showAlert('warning', 'Pilih minimal satu divisi untuk filter');
        }

        // Close filter panel
        filterPanel.style.display = 'none';
        filterOverlay.style.display = 'none';
    });

    document.getElementById('applyTregFilter').addEventListener('click', function() {
        // Get checked TREG checkboxes
        const checkedTregs = Array.from(
            document.querySelectorAll('#tregContent input:checked')
        ).map(cb => cb.value);

        // Apply TREG filter
        if (checkedTregs.length > 0) {
            applyTregFilter(checkedTregs);
        } else {
            showAlert('warning', 'Pilih minimal satu TREG untuk filter');
        }

        // Close filter panel
        filterPanel.style.display = 'none';
        filterOverlay.style.display = 'none';
    });

    // Function to apply division filter
    function applyDivisiFilter(divisionList) {
        showLoading();

        // Get current date range and active region
        const dateRange = dateRangePicker.selectedDates;
        const startDate = dateRange.length > 0 ? dateRange[0] : new Date("{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}");
        const endDate = dateRange.length > 1 ? dateRange[1] : new Date("{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}");

        const activeRegion = document.querySelector('.region-box.active');
        const region = activeRegion ? activeRegion.getAttribute('data-region') : 'all';

        // Format dates for API
        const formattedStartDate = formatDateForApi(startDate);
        const formattedEndDate = formatDateForApi(endDate);

        // Make AJAX request to filter by division
        fetch('{{ route("witel.filter-by-divisi") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                divisi: divisionList,
                region: region,
                start_date: formattedStartDate,
                end_date: formattedEndDate
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }

            // Update all charts with filtered data
            updateAllCharts(data.chartData);
            updateSummaryCards(data.summaryData);

            hideLoading();
            showAlert('success', `Data difilter untuk divisi: ${divisionList.join(', ')}`);
        })
        .catch(error => {
            console.error('Error applying division filter:', error);
            hideLoading();
            showAlert('error', 'Gagal menerapkan filter: ' + error.message);
        });
    }

    // Function to apply TREG filter
    function applyTregFilter(tregList) {
        showLoading();

        // Implementation for TREG filter would go here
        // For now, show message that this feature is under development

        hideLoading();
        showAlert('info', 'Fitur filter TREG sedang dalam pengembangan');
    }

    // Region selection
    const regionBoxes = document.querySelectorAll('.region-box');

    regionBoxes.forEach(box => {
        box.addEventListener('click', function() {
            // Remove active class from all region boxes
            regionBoxes.forEach(rb => rb.classList.remove('active'));

            // Add active class to clicked region box
            this.classList.add('active');

            // Update charts with new region selection
            const selectedRegion = this.getAttribute('data-region');
            updateChartsByRegion(selectedRegion);
        });
    });

    // Function to update charts with new date range
    function updateCharts(startDate, endDate) {
        showLoading();

        // Get active region
        const activeRegion = document.querySelector('.region-box.active');
        const region = activeRegion ? activeRegion.getAttribute('data-region') : 'all';

        // Format dates for API
        const formattedStartDate = formatDateForApi(startDate);
        const formattedEndDate = formatDateForApi(endDate);

        // Make AJAX request to update charts
        fetch('{{ route("witel.update-charts") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                region: region,
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
                    // Still update with the default/empty data provided by the server
                    updateAllCharts(data.chartData);
                    updateSummaryCards(data.summaryData);
                }
                hideLoading();
                return;
            }

            // Update all charts with new data
            updateAllCharts(data.chartData);
            updateSummaryCards(data.summaryData);

            hideLoading();
            showAlert('success', `Data untuk periode ${formatDate(startDate)} - ${formatDate(endDate)} berhasil dimuat`);
        })
        .catch(error => {
            console.error('Error updating charts:', error);
            hideLoading();
            showAlert('error', 'Gagal memuat data: ' + error.message);
        });
    }

    // Function to update charts based on region selection
    function updateChartsByRegion(region) {
        showLoading();

        // Get current date range
        const dateRange = dateRangePicker.selectedDates;
        const startDate = dateRange.length > 0 ? dateRange[0] : new Date("{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}");
        const endDate = dateRange.length > 1 ? dateRange[1] : new Date("{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}");

        // Format dates for API
        const formattedStartDate = formatDateForApi(startDate);
        const formattedEndDate = formatDateForApi(endDate);

        // Make AJAX request to update charts
        fetch('{{ route("witel.update-charts") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                region: region,
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
                    // Still update with the default/empty data provided by the server
                    updateAllCharts(data.chartData);
                    updateSummaryCards(data.summaryData);
                }
                hideLoading();
                return;
            }

            // Update all charts with new data
            updateAllCharts(data.chartData);
            updateSummaryCards(data.summaryData);

            // Update page title to show selected region
            updateTitlesWithRegion(region);

            hideLoading();
            showAlert('success', `Data untuk ${region === 'all' ? 'Semua Witel' : region} berhasil dimuat`);
        })
        .catch(error => {
            console.error('Error updating charts by region:', error);
            hideLoading();
            showAlert('error', 'Gagal memuat data: ' + error.message);
        });
    }

    // Function to update all chart titles based on selected region
    function updateTitlesWithRegion(region) {
        const regionText = region === 'all' ? 'Semua Witel' : region;

        const chartTitles = document.querySelectorAll('.chart-title');
        if (chartTitles.length >= 4) {
            // Update Performance Witel Chart title
            chartTitles[3].textContent = `Performa Witel - ${regionText}`;
        }
    }

    // Function to update summary cards with new data
    function updateSummaryCards(summaryData) {
        if (!summaryData) return;

        // Update each division card with new data
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

        // Update value
        const valueEl = card.querySelector('.summary-value');
        if (valueEl) {
            valueEl.textContent = `Rp ${numberFormat(data.total_real)} M`;
        }

        // Update percentage change indicator
        const metaEl = card.querySelector('.summary-meta');
        if (metaEl) {
            metaEl.className = `summary-meta ${data.percentage_change >= 0 ? 'up' : 'down'}`;

            const iconEl = metaEl.querySelector('i');
            if (iconEl) {
                iconEl.className = `fas fa-arrow-${data.percentage_change >= 0 ? 'up' : 'down'}`;
            }

            // Update percentage text
            const percentText = `${Math.abs(data.percentage_change).toFixed(2)}% dari periode sebelumnya`;
            metaEl.innerHTML = `<i class="fas fa-arrow-${data.percentage_change >= 0 ? 'up' : 'down'}"></i> ${percentText}`;
        }

        // Update achievement percentage
        const percentageEl = card.querySelector('.summary-percentage');
        if (percentageEl) {
            percentageEl.textContent = `${data.achievement.toFixed(2)}%`;
        }
    }

    // Helper function to format date for API
    function formatDateForApi(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Helper function to format numbers
    function numberFormat(number) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(number);
    }

    // Function to show loading state
    function showLoading() {
        // Add loading overlay to chart containers
        document.querySelectorAll('.chart-body').forEach(container => {
            // Only add if not already present
            if (!container.querySelector('.loading-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'loading-overlay';
                overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                container.appendChild(overlay);
            }
        });
    }

    // Function to hide loading state
    function hideLoading() {
        // Remove loading overlays
        document.querySelectorAll('.loading-overlay').forEach(overlay => {
            overlay.remove();
        });
    }

    // Function to show alert message
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        if (!alertContainer) return;

        // Create alert element
        const alert = document.createElement('div');
        alert.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Clear previous alerts
        alertContainer.innerHTML = '';

        // Add new alert
        alertContainer.appendChild(alert);
        alertContainer.style.display = 'block';

        // Auto hide after 5 seconds
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

    // Function to update all charts with new data
    function updateAllCharts(data) {
        if (!data) return;

        // Update Line Chart
        if (data.lineChart && lineRevenueChartInstance) {
            lineRevenueChartInstance.updateOptions({
                xaxis: {
                    categories: data.lineChart.months
                },
                series: data.lineChart.series
            });
        }

        // Update Donut Chart
        if (data.donutChart && donutAchievementChartInstance) {
            donutAchievementChartInstance.updateOptions({
                labels: data.donutChart.labels,
                series: data.donutChart.series
            });
        }

        // Update Bar Chart
        if (data.barChart && barDivisionChartInstance) {
            barDivisionChartInstance.updateOptions({
                xaxis: {
                    categories: data.barChart.divisions
                },
                series: data.barChart.series
            });
        }

        // Update Performance Witel Chart
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

    // Initialize all charts
    function initializeCharts() {
        if (!chartData || Object.keys(chartData).length === 0) {
            console.warn('Chart data tidak tersedia');
            showAlert('warning', 'Data chart tidak tersedia. Menampilkan data default.');
        }

        // Line Revenue Chart
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
            colors: ['#3b7ddd', '#10b981'],
            series: chartData?.lineChart?.series || [{
                name: new Date().getFullYear().toString(),
                data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
            }, {
                name: (new Date().getFullYear() - 1).toString(),
                data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
            }],
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
                categories: chartData?.lineChart?.months || ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
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

        // Donut Achievement Chart
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
                formatter: function (val, opts) {
                    return numberFormat(opts.w.globals.series[opts.seriesIndex]) + '%';
                }
            },
            colors: ['#3b7ddd', '#10b981', '#f59e0b', '#ef4444'],
            series: chartData?.donutChart?.series || [0, 0, 0, 0],
            labels: chartData?.donutChart?.labels || ['DSS', 'DPS', 'DGS', 'RLEGS'],
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

        // Bar Division Chart
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
            series: chartData?.barChart?.series || [{
                name: 'Target',
                data: [0, 0, 0, 0]
            }, {
                name: 'Realisasi',
                data: [0, 0, 0, 0]
            }, {
                name: 'Achievement (%)',
                data: [0, 0, 0, 0]
            }],
            xaxis: {
                categories: chartData?.barChart?.divisions || ['DSS', 'DPS', 'DGS', 'RLEGS'],
                title: {
                    text: 'Divisi'
                }
            },
            yaxis: [
                {
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
                    formatter: function(val, { seriesIndex }) {
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

        // Performance Witel Chart
        const performanceChartOptions = {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: true
                },
                fontFamily: "'Poppins', 'Helvetica', 'Arial', sans-serif"
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    dataLabels: {
                        position: 'top'
                    },
                    barHeight: '70%',
                    distributed: true,
                    colors: {
                        ranges: [
                            {
                                from: 0,
                                to: 50,
                                color: '#ef4444'
                            },
                            {
                                from: 50,
                                to: 80,
                                color: '#f59e0b'
                            },
                            {
                                from: 80,
                                to: 100,
                                color: '#10b981'
                            },
                            {
                                from: 100,
                                to: 1000,
                                color: '#3b7ddd'
                            }
                        ]
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return numberFormat(val) + "%";
                },
                offsetX: 30,
                style: {
                    fontSize: '12px',
                    colors: ['#000']
                }
            },
            series: [{
                name: 'Achievement (%)',
                data: chartData?.witelPerformance?.data || [0]
            }],
            grid: {
                xaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            xaxis: {
                categories: chartData?.witelPerformance?.categories || ['Tidak ada data'],
                labels: {
                    formatter: function(val) {
                        return val;
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Achievement (%)'
                },
                labels: {
                    formatter: function(val) {
                        return numberFormat(val) + "%";
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return numberFormat(val) + "%";
                    }
                }
            },
            annotations: {
                xaxis: [{
                    x: 100,
                    strokeDashArray: 5,
                    borderColor: '#10b981',
                    label: {
                        borderColor: '#10b981',
                        style: {
                            color: '#fff',
                            background: '#10b981'
                        },
                        text: 'Target 100%'
                    }
                }]
            }
        };

        const performanceChartEl = document.querySelector("#performanceWitelChart");
        if (performanceChartEl) {
            performanceWitelChartInstance = new ApexCharts(performanceChartEl, performanceChartOptions);
            performanceWitelChartInstance.render();
        }
    }

    // Initialize charts
    initializeCharts();

    // Log for debugging
    console.log('Region boxes available:', document.qu
    erySelectorAll('.region-box').length);
    console.log('Active region box:', document.querySelector('.region-box.active')?.getAttribute('data-region') || 'none');
});
</script>
@endsection
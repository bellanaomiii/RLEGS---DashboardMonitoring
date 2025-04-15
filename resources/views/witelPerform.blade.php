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
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
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
            <!-- PERBAIKAN: Hanya menggunakan satu elemen untuk date picker (tanpa input hidden) -->
            <div class="date-filter" id="dateRangeSelector">
                <i class="far fa-calendar-alt"></i>
                <span id="dateRangeText">{{ date('d M Y', strtotime($startDate ?? Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'))) }} - {{ date('d M Y', strtotime($endDate ?? Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'))) }}</span>
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

                    @foreach($divisionList as $index => $divisi)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="divisi{{ $index }}" value="{{ $divisi }}">
                            <label class="form-check-label" for="divisi{{ $index }}">{{ $divisi }}</label>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-sm btn-primary" id="applyDivisiFilter">Terapkan</button>
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
            
            @if(isset($regions) && !empty($regions))
                @php $regionCount = count($regions); $i = 0; @endphp
                @foreach($regions as $region)
                    @if($i < 3) <!-- Show first 3 regions + 'Semua Witel' in first row -->
                        <div class="region-box {{ ($selectedRegion ?? '') == $region ? 'active' : '' }}" data-region="{{ $region }}">
                            {{ $region }}
                        </div>
                    @endif
                    @php $i++; @endphp
                @endforeach
            @else
                <!-- Default Regions first row -->
                @foreach(['Suramadu', 'Nusa Tenggara', 'Jatim Barat'] as $defaultRegion)
                    <div class="region-box {{ ($selectedRegion ?? '') == $defaultRegion ? 'active' : '' }}" data-region="{{ $defaultRegion }}">
                        {{ $defaultRegion }}
                    </div>
                @endforeach
            @endif
        </div>
        
        <!-- Second Row -->
        <div class="region-row">
            @if(isset($regions) && !empty($regions))
                @php $i = 0; @endphp
                @foreach($regions as $region)
                    @if($i >= 3 && $i < 7) <!-- Show the remaining regions in second row (up to 4) -->
                        <div class="region-box {{ ($selectedRegion ?? '') == $region ? 'active' : '' }}" data-region="{{ $region }}">
                            {{ $region }}
                        </div>
                    @endif
                    @php $i++; @endphp
                @endforeach
            @else
                <!-- Default Regions second row -->
                @foreach(['Yogya Jateng Selatan', 'Bali', 'Semarang Jateng Utara', 'Solo Jateng Timur'] as $defaultRegion)
                    <div class="region-box {{ ($selectedRegion ?? '') == $defaultRegion ? 'active' : '' }}" data-region="{{ $defaultRegion }}">
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

    // PERBAIKAN: Initialize date range picker langsung pada elemen dateRangeSelector (div.date-filter)
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

    // FILTER BUTTON LOGIC - PERBAIKAN: Debugging dan event handler
    const filterButton = document.getElementById('filterButton');
    const filterPanel = document.getElementById('filterPanel');
    const filterOverlay = document.getElementById('filterOverlay');

    // Log for debugging
    console.log('Filter button element:', filterButton);
    console.log('Filter panel element:', filterPanel);
    console.log('Filter overlay element:', filterOverlay);

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
    } else {
        console.error('Filter button not found');
    }

    // Close filter panel when clicking on overlay
    if (filterOverlay) {
        filterOverlay.addEventListener('click', function() {
            console.log('Filter overlay clicked');
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
    const applyDivisiFilter = document.getElementById('applyDivisiFilter');
    if (applyDivisiFilter) {
        applyDivisiFilter.addEventListener('click', function() {
            console.log('Apply divisi filter clicked');
            // Get checked division checkboxes
            const checkedDivisions = Array.from(
                document.querySelectorAll('#divisiContent input:checked')
            ).map(cb => cb.value);

            // Apply division filter
            if (checkedDivisions.length > 0) {
                applyDivisiFilterFunc(checkedDivisions);
            } else {
                showAlert('warning', 'Pilih minimal satu divisi untuk filter');
            }

            // Close filter panel
            filterPanel.style.display = 'none';
            filterOverlay.style.display = 'none';
        });
    }

    const applyTregFilter = document.getElementById('applyTregFilter');
    if (applyTregFilter) {
        applyTregFilter.addEventListener('click', function() {
            console.log('Apply treg filter clicked');
            // Get checked TREG checkboxes
            const checkedTregs = Array.from(
                document.querySelectorAll('#tregContent input:checked')
            ).map(cb => cb.value);

            // Apply TREG filter
            if (checkedTregs.length > 0) {
                applyTregFilterFunc(checkedTregs);
            } else {
                showAlert('warning', 'Pilih minimal satu TREG untuk filter');
            }

            // Close filter panel
            filterPanel.style.display = 'none';
            filterOverlay.style.display = 'none';
        });
    }

    // Modifikasi fungsi applyDivisiFilterFunc untuk memanggil updateFilterButtonText
    function applyDivisiFilterFunc(divisionList) {
        showLoading();
        console.log('Applying division filter:', divisionList);

        // Get current date range and active region
        const dateRange = dateRangePicker.selectedDates;
        const startDate = dateRange.length > 0 ? dateRange[0] : new Date("{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}");
        const endDate = dateRange.length > 1 ? dateRange[1] : new Date("{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}");

        const activeRegion = document.querySelector('.region-box.active');
        const region = activeRegion ? activeRegion.getAttribute('data-region') : 'all';

        // Format dates for API
        const formattedStartDate = formatDateForApi(startDate);
        const formattedEndDate = formatDateForApi(endDate);

        // Update teks button filter dengan divisi yang dipilih
        updateFilterButtonText(divisionList);

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
        })
        .catch(error => {
            console.error('Error applying division filter:', error);
            hideLoading();
            showAlert('error', 'Gagal menerapkan filter: ' + error.message);
        });
    }

    // Function to apply TREG filter
    function applyTregFilterFunc(tregList) {
        showLoading();
        console.log('Applying TREG filter:', tregList);

        // Implementation for TREG filter would go here
        // For now, show message that this feature is under development

        hideLoading();
        showAlert('info', 'Fitur filter TREG sedang dalam pengembangan');
    }

    // Region selection
    const regionBoxes = document.querySelectorAll('.region-box');

    regionBoxes.forEach(box => {
        box.addEventListener('click', function() {
            console.log('Region box clicked:', this.getAttribute('data-region'));
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
        console.log('Updating charts with date range:', formatDate(startDate), '-', formatDate(endDate));

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
        console.log('Updating charts by region:', region);

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
        console.log('Updating summary cards with data:', summaryData);

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
        console.log('Showing loading state');
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
        console.log('Hiding loading state');
        // Remove loading overlays
        document.querySelectorAll('.loading-overlay').forEach(overlay => {
            overlay.remove();
        });
    }

    // Function to show alert message
    function showAlert(type, message) {
        console.log('Showing alert:', type, message);
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
    console.log('Updating all charts with data:', data);

    // Update Line Chart
    if (data.barChart && lineRevenueChartInstance) {
        lineRevenueChartInstance.updateOptions({
            // Tetap gunakan bulan untuk categories pada x-axis
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
            },
            // Tapi gunakan data dari barChart untuk data series
            series: [
                {
                    name: 'Target Revenue',
                    data: data.barChart.series[0].data || []
                },
                {
                    name: 'Real Revenue',
                    data: data.barChart.series[1].data || []
                }
            ]
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
        console.log('Initializing charts');
        if (!chartData || Object.keys(chartData).length === 0) {
            console.warn('Chart data tidak tersedia');
            showAlert('warning', 'Data chart tidak tersedia. Menampilkan data default.');
        }

        // Modified Line Revenue Chart options
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
            series: [
                {
                    name: 'Target Revenue',
                    // Gunakan data yang sama dengan bar chart untuk target revenue
                    data: chartData?.barChart?.series?.[0]?.data || [0, 0, 0, 0]
                }, 
                {
                    name: 'Real Revenue',
                    // Gunakan data yang sama dengan bar chart untuk realisasi revenue
                    data: chartData?.barChart?.series?.[1]?.data || [0, 0, 0, 0]
                }
            ],
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
                // Tetap menggunakan bulan untuk x-axis
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
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
    console.log('Region boxes available:', document.querySelectorAll('.region-box').length);
    console.log('Active region box:', document.querySelector('.region-box.active')?.getAttribute('data-region') || 'none');
});
</script>

<script>
function updateFilterButtonText(selectedDivisions) {
    const filterButton = document.getElementById('filterButton');
    
    if (!filterButton) return;
    
    // Jika hanya ada satu divisi yang dipilih, tampilkan nama divisi
    if (selectedDivisions.length === 1) {
        // Ubah teks button menjadi nama divisi dengan teks putih
        filterButton.innerHTML = `
            <i class="fas fa-filter me-2"></i> 
            Filter Divisi: <span class="filter-divisi-value">${selectedDivisions[0]}</span>
            <i class="fas fa-chevron-down ms-auto"></i>
        `;
    } 
    // Jika ada beberapa divisi yang dipilih
    else if (selectedDivisions.length > 1 && selectedDivisions.length < 4) {
        filterButton.innerHTML = `
            <i class="fas fa-filter me-2"></i> 
            Filter Divisi: <span class="filter-divisi-value">${selectedDivisions.length} Divisi</span>
            <i class="fas fa-chevron-down ms-auto"></i>
        `;
    }
    // Jika semua divisi dipilih atau tidak ada filter yang dipilih
    else {
        filterButton.innerHTML = `
            <i class="fas fa-filter me-2"></i> 
            Filter Divisi
            <i class="fas fa-chevron-down ms-auto"></i>
        `;
    }
    
    // Tambahkan pesan alert yang menampilkan divisi yang difilter
    showFilterAlert(selectedDivisions);
}


// Fungsi untuk menampilkan alert filter
function showFilterAlert(selectedDivisions) {
    // Jika semua divisi dipilih (4 divisi) atau tidak ada filter, jangan tampilkan alert
    if (selectedDivisions.length >= 4 || selectedDivisions.length === 0) {
        // Sembunyikan alert container
        const alertContainer = document.getElementById('alertContainer');
        if (alertContainer) {
            alertContainer.style.display = 'none';
        }
        return;
    }
    
    // Buat teks alert berdasarkan jumlah divisi yang dipilih
    let alertText = '';
    if (selectedDivisions.length === 1) {
        alertText = `Data difilter untuk divisi: ${selectedDivisions[0]}`;
    } else {
        alertText = `Data difilter untuk divisi: ${selectedDivisions.join(', ')}`;
    }
    
    // Tampilkan alert info dengan teks yang sesuai
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;
    
    // Buat alert element dengan tombol close
    const alert = document.createElement('div');
    alert.className = `alert alert-light alert-dismissible fade show`;
    alert.style.backgroundColor = '#e8f5e9'; // Warna hijau muda
    alert.style.borderColor = '#c8e6c9';
    alert.style.color = '#2e7d32';
    alert.innerHTML = `
        ${alertText}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Kosongkan container dan tambahkan alert baru
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    alertContainer.style.display = 'block';
}
</script>
@endsection
@extends('layouts.main')

@section('title', 'Performansi Witel')

@section('content')
<section>
    <section class="main-container">
        <div class="regions-container">
            <div class="region-box active">Suramadu</div>
            <div class="region-box">Nusa Tenggara</div>
            <div class="region-box">Jatim Barat</div>
            <div class="region-box">Yogya Jateng Selatan</div>
            <div class="region-box">Bali</div>
            <div class="region-box">Semarang Jateng Utara</div>
            <div class="region-box">Solo Jateng Timur</div>
            <div class="region-box">Jatim Timur</div>
        </div>
    </section>

    {{-- <div class="row">
        <div class="col-sm-6 mb-3 mb-sm-0">
            <input type="text" class="form-control date-range-picker" data-coreui-toggle="date-range-picker" data-coreui-start-date="2022/08/03" data-coreui-end-date="2022/08/17" data-coreui-locale="en-US">
        </div>    
        <div class="col-sm-6">
            <input type="text" class="form-control date-range-picker" data-coreui-start-date="2022/08/03" data-coreui-end-date="2022/08/17" data-coreui-locale="en-US">
        </div>
    </div> --}}

    <div class="row">
        <div class="col-sm-6 mb-3 mb-sm-0">
            <div
                data-coreui-start-date="2022/08/03"
                data-coreui-end-date="2022/08/17"
                data-coreui-locale="en-US"
                data-coreui-toggle="date-range-picker">
            </div>
        </div>
        <div class="col-sm-6">
            <div
                data-coreui-start-date="2022/08/03"
                data-coreui-end-date="2022/09/17"
                data-coreui-locale="en-US"
                data-coreui-toggle="date-range-picker">
            </div>
        </div>
    </div>
    

    {{--   <div class="row">
    <div class="col-sm-6 mb-3 mb-sm-0">
      <div
        data-coreui-footer="true"
        data-coreui-locale="en-US"
        data-coreui-toggle="date-range-picker">
      </div>
    </div>
    <div class="col-sm-6">
      <div
        data-coreui-start-date="2022/08/03"
        data-coreui-end-date="2022/09/17"
        data-coreui-footer="true"
        data-coreui-locale="en-US"
        data-coreui-toggle="date-range-picker">
      </div>
    </div>
  </div> --}}
    
    <!-- Filter Button and Content -->
    <div class="container d-flex justify-content-end mt-5">
        <div class="filter-container">
            <!-- Filter Button -->
            <div class="text-end mb-2">
                <button class="btn btn-secondary" id="filterButton">
                    Pilih Filter <i class="fas fa-caret-down ms-1"></i>
                </button>
            </div>

            <!-- Filter Panel (initially hidden) -->
            <div class="card shadow filter-panel" id="filterPanel" style="display: none;">
                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-primary active filter-tab-btn" data-target="tregContent">TREG HO</button>
                        <button type="button" class="btn btn-outline-primary filter-tab-btn" data-target="divisiContent">Divisi</button>
                        {{-- <button type="button" class="btn btn-outline-primary filter-tab-btn" data-target="periodeContent">Periode</button> --}}
                    </div>
                </div>

                <!-- Filter Contents -->
                <div class="card-body">
                    <!-- TREG HO Content -->
                    <div class="filter-content" id="tregContent">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="treg1" value="TREG1">
                            <label class="form-check-label" for="treg1">TREG 2</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="treg2" value="TREG2">
                            <label class="form-check-label" for="treg2">TREG 3</label>
                        </div>
                    </div>

                    <!-- Divisi Content (initially hidden) -->
                    <div class="filter-content" id="divisiContent" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="divisi1" value="DGS">
                            <label class="form-check-label" for="divisi1">DGS</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="divisi2" value="DPS">
                            <label class="form-check-label" for="divisi2">DPS</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="divisi3" value="DSS">
                            <label class="form-check-label" for="divisi3">DSS</label>
                        </div>
                    </div>
        
                    {{-- <!-- Periode Content -->
                    <div class="filter-content" id="periodeContent" style="display: none;">
                        <div class="mb-5 mt-2">
                            {{-- <label for="month_year_picker" class="block text-sm font-medium mb-1"></label>
                            <div class="month-picker-container relative">
                                <input type="text" id="month_year_picker" class="form-control w-full px-4 py-2 border rounded-lg" placeholder="Pilih Bulan dan Tahun" readonly>
                                <input type="hidden" name="bulan_month" id="bulan_month" value="{{ date('m') }}">
                                <input type="hidden" name="bulan_year" id="bulan_year" value="{{ date('Y') }}">
                                <input type="hidden" name="bulan" id="bulan" value="{{ date('Y-m') }}">
                                <div id="month_picker" class="month-picker">
                                    <div class="month-picker-header">
                                        <div class="year-selector">
                                            <button type="button" id="prev_year"><i class="fas fa-chevron-left"></i></button>
                                            <span id="current_year">{{ date('Y') }}</span>
                                            <button type="button" id="next_year"><i class="fas fa-chevron-right"></i></button>
                                        </div>
                                    </div>
                                    <div class="month-grid" id="month_grid">
                                        <!-- Month items will be populated by JS -->
                                    </div>
                                    <div class="month-picker-footer">
                                        <button type="button" class="cancel" id="cancel_month">BATAL</button>
                                        <button type="button" class="apply" id="apply_month">OK</button>
                                    </div>
                                </div>
                            </div> --}}
                        {{-- </div>
                    </div> --}} 
                </div>
            </div>
        </div>
    </div>

    <div class="witel-card">
        <div class="container-fluid mt-3">
            <div class="row">
                <div class="col-md-2 ms-6">
                    <div class="overview-container mb-4">
                        <div class="overview-box active">Overview RLEGS</div>
                        <div class="overview-box">Overview DPS</div>
                        <div class="overview-box">Overview DGS</div>
                        <div class="overview-box">Overview DSS</div>
                    </div>
                </div>
                
                <div class="col-md-9 ms-4">
                    <div class="row mb-5">
                        <div class="col-md-8 mb-4">
                            <div class="p-0 bg-white rounded shadow h-100">
                                <div class="chart-container p-4 bg-gray-50 rounded h-180">
                                    {!! $lineChart->container() !!}
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4 mt-4">
                            <div class= "bg-white rounded shadow">
                                <div class="chart-container p-4 bg-gray-50 rounded">
                                    {!! $donutChart->container() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="p-4 bg-white rounded shadow">
                                <div class="chart-container p-4 bg-gray-50 rounded">
                                    {!! $barChart->container() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</section>

<script src="{{ $lineChart->cdn() }}"></script>
<script src="{{ $barChart->cdn() }}"></script>
<script src="{{ $donutChart->cdn() }}"></script>


{{ $lineChart->script() }}
{{ $barChart->script() }} 
{{ $donutChart->script() }}


<script src="sidebar/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.2.0/dist/js/coreui.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@coreui/coreui-datepicker@1.0.0/dist/js/coreui-datepicker.min.js"></script>


<style>
/* Filter container positioning */
.filter-container {
    position: relative;
    z-index: 1000;
}

/* Filter panel styling */
.filter-panel {
    position: absolute;
    right: 0;
    width: 300px;
    border-radius: 5px;
}

/* Button Group Styling */
.filter-tabs .btn-outline-primary {
    border-radius: 0;
    flex: 1;
}

.filter-tabs .btn-outline-primary.active {
    background-color: #3b7ddd;
    color: white;
    border-color: #3b7ddd;
}

/* Form elements styling */
.form-check-input {
    position: static !important;
    margin-top: 0.3rem !important;
    margin-left: 0 !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: inline-block !important;
}

.form-check {
    margin-bottom: 0.5rem;
    padding-left: 0.5rem;
}

.form-check-label {
    margin-left: 0.5rem;
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter button toggle
    const filterButton = document.getElementById('filterButton');
    const filterPanel = document.getElementById('filterPanel');

    filterButton.addEventListener('click', function() {
        if (filterPanel.style.display === 'none') {
            filterPanel.style.display = 'block';
        } else {
            filterPanel.style.display = 'none';
        }
    });

    // Tab switching
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

    // Close filter panel when clicking outside
    document.addEventListener('click', function(event) {
        if (!filterPanel.contains(event.target) && event.target !== filterButton) {
            filterPanel.style.display = 'none';
        }
    });
});
</script>

<script>
const dateRangePickerElementList = Array.prototype.slice.call(document.querySelectorAll('.date-range-picker'));
const dateRangePickerList = dateRangePickerElementList.map(dateRangePickerEl => {
    return new coreui.DateRangePicker(dateRangePickerEl);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateRangePickerElementList = Array.prototype.slice.call(document.querySelectorAll('[data-coreui-toggle="date-range-picker"]'));
    const dateRangePickerList = dateRangePickerElementList.map(dateRangePickerEl => {
        return new coreui.DateRangePicker(dateRangePickerEl);
    });
});
</script>
@endsection

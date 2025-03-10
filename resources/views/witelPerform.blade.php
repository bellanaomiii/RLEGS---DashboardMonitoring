@extends('layouts.main')

@section('title', 'Performansi Witel')

@section('content')
<section>
    <section class="main-container">
        <div class="tab-nav-bar">
            <div class="tab-navigation">
                <i class="fas fa-angle-left left-btn"></i>
                <i class="fas fa-angle-right right-btn"></i>

                <div class="tab-menu">
                    <li class="tab-btn active">Suramadu</li>
                    <li class="tab-btn">Nusa Tenggara</li>
                    <li class="tab-btn">Jatim Barat</li>
                    <li class="tab-btn">Yogya Jateng Selatan</li>
                    <li class="tab-btn">Bali</li>
                    <li class="tab-btn">Semarang Jateng Utara</li>
                    <li class="tab-btn">Solo Jateng Timur</li>
                    <li class="tab-btn">Jatim Timur</li>
                </div>
            </div>
        </div>
    </section>

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
                        <button type="button" class="btn btn-outline-primary filter-tab-btn" data-target="periodeContent">Periode</button>
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

                    <!-- Periode Content -->
                    <div class="filter-content" id="periodeContent" style="display: none;">
                        <div class="mb-5 mt-2">
                            <label for="month_year_picker" class="block text-sm font-medium mb-1"></label>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container px-4 mx-auto mt-5">
        <div class="p-6 bg-white rounded shadow">
            <h2 class="text-xl font-semibold mb-4">Grafik Performa Witel</h2>
    
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded">
                    {!! $lineChart->container() !!}
                </div>
    
                <div class="p-4 bg-gray-50 rounded">
                    {!! $radialChart->container() !!}
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Load Chart Scripts --}}
<script src="{{ $lineChart->cdn() }}"></script>
<script src="{{ $radialChart->cdn() }}"></script>

{{ $lineChart->script() }}
{{ $radialChart->script() }}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="sidebar/script.js"></script>

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
    document.addEventListener("DOMContentLoaded", function () {
    const monthYearPicker = document.getElementById("month_year_picker");
    const bulanMonth = document.getElementById("bulan_month");
    const bulanYear = document.getElementById("bulan_year");
    const bulan = document.getElementById("bulan");
    const monthPicker = document.getElementById("month_picker");
    const monthGrid = document.getElementById("month_grid");
    const currentYear = document.getElementById("current_year");
    const prevYear = document.getElementById("prev_year");
    const nextYear = document.getElementById("next_year");
    const applyMonth = document.getElementById("apply_month");
    const cancelMonth = document.getElementById("cancel_month");

    let selectedMonth = new Date().getMonth() + 1;
    let selectedYear = new Date().getFullYear();

    // Generate month grid
    function generateMonthGrid() {
        monthGrid.innerHTML = "";
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

        monthNames.forEach((month, index) => {
            let monthButton = document.createElement("button");
            monthButton.classList.add("month-item");
            monthButton.innerText = month;
            monthButton.dataset.month = index + 1;

            if (index + 1 === selectedMonth) {
                monthButton.classList.add("selected");
            }

            monthButton.addEventListener("click", function () {
                document.querySelectorAll(".month-item").forEach(btn => btn.classList.remove("selected"));
                this.classList.add("selected");
                selectedMonth = parseInt(this.dataset.month);
            });

            monthGrid.appendChild(monthButton);
        });
    }

    // Show month picker
    monthYearPicker.addEventListener("click", function () {
        monthPicker.style.display = "block";
        generateMonthGrid();
    });

    // Change year
    prevYear.addEventListener("click", function () {
        selectedYear--;
        currentYear.innerText = selectedYear;
    });

    nextYear.addEventListener("click", function () {
        selectedYear++;
        currentYear.innerText = selectedYear;
    });

    // Apply selected month & year
    applyMonth.addEventListener("click", function () {
        const formattedMonth = selectedMonth.toString().padStart(2, "0");
        const formattedDate = `${formattedMonth}-${selectedYear}`;
        
        monthYearPicker.value = formattedDate;
        bulanMonth.value = formattedMonth;
        bulanYear.value = selectedYear;
        bulan.value = `${selectedYear}-${formattedMonth}`;

        monthPicker.style.display = "none";
    });

    // Cancel picker
    cancelMonth.addEventListener("click", function () {
        monthPicker.style.display = "none";
    });
});

</script>
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
@endsection

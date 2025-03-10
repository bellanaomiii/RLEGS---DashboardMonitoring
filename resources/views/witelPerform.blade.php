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

                    <!-- Periode Content (initially hidden) -->
                    <div class="filter-content" id="periodeContent" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="periode1" value="2023">
                            <label class="form-check-label" for="periode1">2023</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="periode2" value="2024">
                            <label class="form-check-label" for="periode2">2024</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="periode3" value="2025">
                            <label class="form-check-label" for="periode3">2025</label>
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

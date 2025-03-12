@extends('layouts.main')

@section('title', 'Leaderboard AM')

@section('styles')
<!-- Tambahkan CSS untuk Bootstrap Select tapi tetap mempertahankan tampilan asli -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<style>
    /* Hide the Select All and Deselect All buttons */
    .bs-actionsbox {
        display: none !important;
    }
</style>
@endsection

@section('content')
<div class="main">
    <section class="container-leaderboard p-2">
        <div class="container-fluid rounded-4 mt-2">
            <div class="row g-4 flex-column">
                <div class="col-12">
                    <div class="bg-custom card text-white">
                        <div class="card-body align-items-center gap-2">
                            <h1>Leaderboard Performa <br> Account Manager</h1>
                            <p class="lead my-1">
                            Dashboard performa pendapatan Account Manager berdasarkan real revenue dan achievement target
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="d-flex justify-content-between align-items-center">
        <div class="container d-flex justify-content-start float-start">
            <form action="{{ route('leaderboard') }}" method="GET" class="col-md-7 col-lg-5 d-flex p-2 float-start">
                <input class="form-control me-2" type="search" name="search" placeholder="Search Name" value="{{ request('search') }}">
                <button class="btn btn-outline-info" type="submit">Go</button>
            </form>
        </div>

        <div class="container d-flex justify-content-end float-end">
            <div class="col-md-9 col-lg-5 ms-auto float-end">
              <label>Filter by</label>
              <div class="row">
                <div class="col-6">
                  <form id="filterForm" action="{{ route('leaderboard') }}" method="GET">
                    <select class="form-control" id="filterSelect" name="filter_by[]" multiple data-live-search="true" onchange="this.form.submit()">
                      <option disabled>Select One or More</option>
                      <option value="Revenue Realisasi Tertinggi" {{ in_array('Revenue Realisasi Tertinggi', request('filter_by', [])) ? 'selected' : '' }}>
                        Revenue Realisasi Tertinggi
                      </option>
                      <option value="Achievement Tertinggi" {{ in_array('Achievement Tertinggi', request('filter_by', [])) ? 'selected' : '' }}>
                        Achievement Tertinggi
                      </option>
                    </select>
                    @if(!empty(request('search')))
                      <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                  </form>
                </div>
                <div class="col-6">
                    <form id="filterForm2" action="{{ route('leaderboard') }}" method="GET">
                      <select class="form-control" id="filterSelect2" name="region_filter[]" multiple data-live-search="true" onchange="this.form.submit()">
                        <option disabled>Select One Witel</option>
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
                    </form>
                  </div>
              </div>
            </div>
          </div>
        </div>
        
    </section>

    {{-- Leaderboard AM --}}
    @forelse($accountManagers as $index => $am)
        <section class="container-leaderboard p-2">
            <div class="container-fluid rounded-4 ms-0">
                <div class="row g-4 flex-column">
                    <div class="col-12">
                        <div class="card bg-white text-black">
                            <div class="card-body d-flex align-items-center gap-3">
                                @if($index == 0)
                                    <img src="{{ asset('img/rank1.png') }}" width="35">
                                @elseif($index == 1)
                                    <img src="{{ asset('img/rank2.png') }}" width="35">
                                @elseif($index == 2)
                                    <img src="{{ asset('img/rank3.png') }}" width="35">
                                @else
                                    <p class="ms-4 fs-6">{{ $index + 1 }}</p>
                                @endif

                                <img src="{{ asset('img/profile.png') }}" width="55">
                                <div>
                                    <h3 class="mt-2 fs-4">{{ $am->nama }}</h3>
                                    <p class="mb-1 fs-6">AM Witel {{ $am->witel->nama ?? 'N/A' }}</p>
                                    <p class="mb-1 fs-6">{{ $am->divisi->nama ?? 'N/A' }}</p>
                                </div>
                                <div class="d-flex justify-content-between ms-auto text-end">
                                    <div>
                                        <p class="fw-light mb-0">Real Revenue</p>
                                        <p class="fw-bold">{{ number_format($am->total_real_revenue, 0, ',', ',') }}</p>
                                    </div>

                                    <div class="d-flex align-items-center {{ $am->achievement_percentage < 100 ? 'text-danger' : 'text-success' }}">
                                        <i class="lni {{ $am->achievement_percentage < 100 ? 'lni-trend-down' : 'lni-trend-up-1' }} ms-5"></i>
                                        <p class="fw-bold mb-0 ms-2">{{ number_format($am->achievement_percentage, 2, ',', '') }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @empty
        <section class="container-leaderboard p-2">
            <div class="container-fluid rounded-4 ms-0">
                <div class="row g-4 flex-column">
                    <div class="col-12">
                        <div class="card bg-white text-black">
                            <div class="card-body text-center">
                                <p>Tidak ada data Account Manager yang tersedia.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endforelse
</div>

<!-- Script yang sudah ada -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tambahkan jQuery dan Bootstrap Select JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>


<!-- Inisialisasi Bootstrap Select dengan fix untuk duplikasi dan searchbox -->
<script>
$(document).ready(function() {
    // Function to remove duplicate options
    function removeDuplicates(elementId) {
        const select = document.getElementById(elementId);
        const optionsSet = new Set();
        
        Array.from(select.options).forEach(option => {
            if (option.disabled) return; // Skip disabled options
            
            const value = option.value;
            if (optionsSet.has(value)) {
                option.remove(); // Remove duplicate
            } else {
                optionsSet.add(value);
            }
        });
    }
    
    // Remove duplicates for both selects
    removeDuplicates('filterSelect');
    removeDuplicates('filterSelect2');
    
    // Initialize Bootstrap Select for both selects with the same configuration
    $('#filterSelect, #filterSelect2').selectpicker({
        liveSearch: true,           // Enable search box
        actionsBox: false,          // Disable actions box (Pilih Semua/Hapus Semua)
        liveSearchPlaceholder: 'Search filter options...',
        size: 5                     // Show 5 items in dropdown
    });
    
    // Add console log to debug
    console.log('Bootstrap-select initialization complete for both selects');
});
</script>
@endsection
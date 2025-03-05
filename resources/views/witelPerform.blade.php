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




    
    <div class="container d-flex justify-content-end mt-5">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Pilih Filter
            </button>
            <div class="dropdown-menu p-3 shadow-lg" style="width: 350px;" id="filterDropdown">
                <ul class="nav nav-pills nav-justified mb-2" id="filterTabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="treg-tab" data-bs-toggle="pill" data-bs-target="#treg">TREG HO</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="divisi-tab" data-bs-toggle="pill" data-bs-target="#divisi">Divisi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="divisi-tab" data-bs-toggle="pill" data-bs-target="#periode">Periode</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="treg">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="treg1" value="TREG1">
                            <label class="form-check-label" for="treg1">TREG 2</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="treg2" value="TREG2">
                            <label class="form-check-label" for="treg2">TREG 3</label>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="divisi">
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
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="sidebar/script.js"></script>
@endsection
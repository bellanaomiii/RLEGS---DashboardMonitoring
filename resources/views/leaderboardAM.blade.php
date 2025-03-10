@extends('layouts.main')

@section('title', 'Leaderboard AM')

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
            <form action="{{ route('leaderboard') }}" method="GET" class="col-md-9 col-lg-5 ms-auto float-end">
                <label>Filter by</label>
                <select class="form-control selectpicker" name="filter_by[]" multiple onchange="this.form.submit()">
                    <option disabled>Select One or More</option>
                    <option value="Revenue Realisasi Tertinggi" {{ in_array('Revenue Realisasi Tertinggi', request('filter_by', [])) ? 'selected' : '' }}>Revenue Realisasi Tertinggi</option>
                    <option value="Achievement Tertinggi" {{ in_array('Achievement Tertinggi', request('filter_by', [])) ? 'selected' : '' }}>Achievement Tertinggi</option>
                </select>
            </form>
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
@endsection

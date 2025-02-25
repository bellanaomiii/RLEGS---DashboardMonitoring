@extends('layouts.main')

@section('title', 'Leaderboard AM')

@section('content')
<div class="main">
    {{-- <section class="bg-custom text-white p-4 w-145 mx-auto text-center text-sm-start">
        <div class="container">
            <div class="d-sm-flex align-items-center justify-content-between">
                <div>
                    <h1>Leaderboard Performa Account Manager</h1>
                    <p class="lead my-1">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Pariatur, voluptas rem? Amet dignissimos, commodi officiis itaque nulla molestias accusantium maiores hic libero at illo odit sed suscipit dolor, aspernatur rerum?
                    </p>
                </div>
            </div>
        </div>
    </section> --}}
    <section class="p-2 container-leaderboard">
        <div class="container-fluid rounded-4 ms-0">
            <div class="row g-4 flex-column">
                <div class="col-12">
                    <div class="bg-custom card text-white">
                        <div class="card-body align-items-center gap-3">
                            <h1>Leaderboard Performa <br> Account Manager</h1>
                            <p class="lead my-1">
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Pariatur, voluptas rem? Amet dignissimos, commodi officiis itaque nulla molestias accusantium maiores hic libero at illo odit sed suscipit dolor, aspernatur rerum?
                            </p>       
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </section>

    {{-- Leadearboard AM --}}
    <section class="p-2 container-leaderboard">
        <div class="container-fluid rounded-4 ms-0">
            <div class="row g-4 flex-column">
                <div class="col-12">
                    <div class="card bg-white text-black">
                        <div class="card-body d-flex align-items-center gap-3">
                            <img src="{{ asset('img/rank1.png') }}" width="35">
                            <img src="{{ asset('img/profile.png') }}" width="55">
                            <div>
                                <h3 class="mt-2 fs-4">Anna Rukmana</h3>
                                <p class="mb-1 fs-6">AM Witel Suramadu</p>
                                <p class="mb-1 fs-6">DSS</p>
                            </div>
                            <div class="d-flex justify-content-between ms-auto text-end">
                                <div>
                                    <p class="fw-light mb-0">Revenue Target</p>
                                    <p class="fw-bold">7,428,146,372</p>
                                </div>

                                <div class="d-flex align-items-center text-success">
                                    <i class="lni lni-trend-up-1 ms-5"></i>
                                    <p class="fw-bold mb-0 ms-2">132,35%</p>
                                </div>
                            </div>         
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </section>

    <section class="p-2 container-leaderboard">
        <div class="container-fluid rounded-4 ms-0">
            <div class="row g-4 flex-column">
                <div class="col-12">
                    <div class="card bg-white text-black">
                        <div class="card-body d-flex align-items-center gap-3">
                            <img src="{{ asset('img/rank2.png') }}" width="35">
                            <img src="{{ asset('img/profile.png') }}" width="55">
                            <div>
                                <h3 class="mt-2 fs-4">Anna Rukmana</h3>
                                <p class="mb-1 fs-6">AM Witel Suramadu</p>
                                <p class="mb-1 fs-6">DSS</p>
                            </div>
                            <div class="d-flex justify-content-between ms-auto text-end">
                                <div>
                                    <p class="fw-light mb-0">Revenue Target</p>
                                    <p class="fw-bold">7,428,146,372</p>
                                </div>

                                <div class="d-flex align-items-center text-success">
                                    <i class="lni lni-trend-up-1 ms-5"></i>
                                    <p class="fw-bold mb-0 ms-2">132,35%</p>
                                </div>
                            </div>         
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </section>

    <section class="p-2 container-leaderboard">
        <div class="container-fluid rounded-4 ms-0">
            <div class="row g-4 flex-column">
                <div class="col-12">
                    <div class="card bg-white text-black">
                        <div class="card-body d-flex align-items-center gap-3">
                            <img src="{{ asset('img/rank3.png') }}" width="35">
                            <img src="{{ asset('img/profile.png') }}" width="55">
                            <div>
                                <h3 class="mt-2 fs-4">Anna Rukmana</h3>
                                <p class="mb-1 fs-6">AM Witel Suramadu</p>
                                <p class="mb-1 fs-6">DSS</p>
                            </div>
                            <div class="d-flex justify-content-between ms-auto text-end">
                                <div>
                                    <p class="fw-light mb-0">Revenue Target</p>
                                    <p class="fw-bold">7,428,146,372</p>
                                </div>

                                <div class="d-flex align-items-center text-success">
                                    <i class="lni lni-trend-up-1 ms-5"></i>
                                    <p class="fw-bold mb-0 ms-2">132,35%</p>
                                </div>
                            </div>         
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </section>

    <section class="p-2 container-leaderboard">
        <div class="container-fluid rounded-4 ms-0">
            <div class="row g-4 flex-column">
                <div class="col-12">
                    <div class="card bg-white text-black">
                        <div class="card-body d-flex align-items-center gap-3">
                            <p class="ms-4 fs-6">4</p>
                            <img src="{{ asset('img/profile.png') }}" width="55">
                            <div>
                                <h3 class="mt-2 fs-4">Anna Rukmana</h3>
                                <p class="mb-1 fs-6">AM Witel Suramadu</p>
                                <p class="mb-1 fs-6">DSS</p>
                            </div>
                            <div class="d-flex justify-content-between ms-auto text-end">
                                <div>
                                    <p class="fw-light mb-0">Revenue Target</p>
                                    <p class="fw-bold">7,428,146,372</p>
                                </div>

                                <div class="d-flex align-items-center text-success">
                                    <i class="lni lni-trend-up-1 ms-5"></i>
                                    <p class="fw-bold mb-0 ms-2">132,35%</p>
                                </div>
                            </div>         
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </section>

    <section class="p-2 container-leaderboard">
        <div class="container-fluid rounded-4 ms-0">
            <div class="row g-4 flex-column">
                <div class="col-12">
                    <div class="card bg-white text-black">
                        <div class="card-body d-flex align-items-center gap-3">
                            <p class="ms-4 fs-6">5</p>
                            <img src="{{ asset('img/profile.png') }}" width="55">
                            <div>
                                <h3 class="mt-2 fs-4">Anna Rukmana</h3>
                                <p class="mb-1 fs-6">AM Witel Suramadu</p>
                                <p class="mb-1 fs-6">DSS</p>
                            </div>
                            <div class="d-flex justify-content-between ms-auto text-end">
                                <div>
                                    <p class="fw-light mb-0">Revenue Target</p>
                                    <p class="fw-bold">7,428,146,372</p>
                                </div>

                                <div class="d-flex align-items-center text-success">
                                    <i class="lni lni-trend-up-1 ms-5"></i>
                                    <p class="fw-bold mb-0 ms-2">132,35%</p>
                                </div>
                            </div>         
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </section>
</div>
@endsection

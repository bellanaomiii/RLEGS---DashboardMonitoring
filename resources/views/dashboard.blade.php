@extends('layouts.main')

@section('title', 'Dashboard Performansi AM')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/overview.css') }}">
<style>
/* Additional styles for enhanced dashboard */
.clickable-row {
    cursor: pointer;
    transition: background-color 0.2s, transform 0.2s;
}

.clickable-row:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.am-profile-pic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e9ecef;
    transition: transform 0.2s;
}

.clickable-row:hover .am-profile-pic {
    transform: scale(1.1);
    border-color: #1C2955;
}

.clickable-name {
    color: #1C2955;
    font-weight: 600;
    text-decoration: none;
}

.clickable-name:hover {
    color: #0d1526;
    text-decoration: underline;
}

/* Corporate Customer Section */
.customer-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid #e9ecef;
    margin-top: 25px;
}

.customer-header {
    background: linear-gradient(to right, #0e223e, #1e3c72);
    color: white;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.customer-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
}

.customer-title i {
    margin-right: 10px;
    font-size: 1.3rem;
}

.filter-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 8px 16px;
    border: 1px solid rgba(255,255,255,0.3);
    background: rgba(255,255,255,0.1);
    color: white;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
}

.filter-btn:hover,
.filter-btn.active {
    background: rgba(255,255,255,0.2);
    border-color: rgba(255,255,255,0.5);
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
}

.customer-table {
    padding: 0;
}

.customer-item {
    padding: 15px 25px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.2s;
}

.customer-item:hover {
    background-color: #f8f9fa;
}

.customer-item:last-child {
    border-bottom: none;
}

.customer-info h6 {
    margin: 0 0 5px 0;
    font-weight: 600;
    color: #1C2955;
}

.customer-info p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.customer-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-dps {
    background: linear-gradient(135deg, #66B2FF 0%, #3399FF 100%);
    color: white;
}

.badge-dss {
    background: linear-gradient(135deg, #001F4D 0%, #003366 100%);
    color: white;
}

.badge-dgs {
    background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
    color: white;
}

.badge-all {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    color: #475569;
}

/* Divisi Pills for AM info */
.divisi-pills {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.divisi-pill {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Category Filter for Witel */
.category-filter-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    padding: 20px 25px;
    margin-bottom: 25px;
    border: 1px solid #e9ecef;
}

.filter-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1C2955;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.filter-title i {
    margin-right: 10px;
}

.category-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.category-btn {
    padding: 10px 20px;
    border: 2px solid #e9ecef;
    background: white;
    color: #6c757d;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
}

.category-btn:hover,
.category-btn.active {
    border-color: #1C2955;
    background: #1C2955;
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(28, 41, 85, 0.2);
}

/* Loading skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.skeleton-row {
    height: 60px;
    margin-bottom: 10px;
    border-radius: 8px;
}

/* Empty state improvements */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h5 {
    margin-bottom: 10px;
    font-weight: 600;
}

.empty-state p {
    margin: 0;
    font-size: 0.95rem;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .customer-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .filter-buttons {
        width: 100%;
        justify-content: center;
    }

    .customer-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .category-buttons {
        justify-content: center;
    }
}
</style>
@endsection

@section('content')
<div class="main-content">
    <!-- Header Dashboard -->
    <div class="header-dashboard">
        <h1 class="header-title">
            Dashboard Performansi
        </h1>
        <p class="header-subtitle">
            Monitoring dan Analisis Performa Revenue Account Manager
        </p>
    </div>

    <!-- Alert Section -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <p class="mb-0">{{ session('error') }}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <p class="mb-0">{{ session('warning') }}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Welcome Section -->
    <div class="welcome-section">
        <h4 class="m-0">Selamat datang, {{ $user->name ?? 'User' }}!</h4>
        <button class="export-btn">
            <i class="fas fa-download"></i> Export Data
        </button>
    </div>

    @if($user->role === 'admin')
        <!-- ===== ADMIN DASHBOARD ===== -->
        <div class="row g-4">
            <!-- Card 1 - Total Revenue -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-indicator revenue-indicator"></div>
                    <div class="stats-title">Total Revenue</div>
                    <div class="stats-value">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
                    <div class="stats-period">{{ $periodRange ?? 'Belum ada data' }}</div>
                    <div class="stats-icon icon-revenue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <!-- Card 2 - Target Revenue -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-indicator target-indicator"></div>
                    <div class="stats-title">Target Revenue</div>
                    <div class="stats-value">Rp {{ number_format($totalTarget ?? 0, 0, ',', '.') }}</div>
                    <div class="stats-period">{{ $periodRange ?? 'Belum ada data' }}</div>
                    <div class="stats-icon icon-target">
                        <i class="fas fa-bullseye"></i>
                    </div>
                </div>
            </div>

            <!-- Card 3 - Achievement -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-indicator achievement-indicator"></div>
                    <div class="stats-title">Achievement</div>
                    <div class="stats-value">{{ $achievementPercentage ?? 0 }}%</div>
                    <div class="stats-period">
                        @if(($achievementPercentage ?? 0) >= 100)
                            <span class="text-success"><i class="fas fa-check-circle"></i> Target tercapai</span>
                        @else
                            <span class="text-danger"><i class="fas fa-times-circle"></i> Belum mencapai target</span>
                        @endif
                    </div>
                    <div class="stats-icon icon-achievement">
                        <i class="fas fa-medal"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Account Managers -->
        <div class="dashboard-card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title">Top 10 Account Managers</h5>
                    <p class="text-muted small mb-0">Berdasarkan total revenue seluruh periode</p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light" type="button" id="topAmOptions" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="topAmOptions">
                        <li><a class="dropdown-item" href="{{ route('leaderboard') }}">Lihat Semua</a></li>
                        <li><a class="dropdown-item" href="#">Filter</a></li>
                        <li><a class="dropdown-item" href="#">Refresh Data</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern m-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Witel</th>
                                <th>Divisi</th>
                                <th class="text-end">Total Revenue</th>
                                <th class="text-end">Achievement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topAMs ?? [] as $am)
                                @php
                                    $achievementClass = $am->achievement_percentage >= 100
                                        ? 'bg-success-soft'
                                        : ($am->achievement_percentage >= 80 ? 'bg-warning-soft' : 'bg-danger-soft');
                                @endphp
                                <tr class="clickable-row" onclick="window.location.href='{{ route('account_manager.detail', $am->id) }}'">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset($am->user && $am->user->profile_image ? 'storage/'.$am->user->profile_image : 'img/profile.png') }}"
                                                 class="am-profile-pic" alt="{{ $am->nama }}">
                                            <a href="{{ route('account_manager.detail', $am->id) }}" class="clickable-name ms-2">
                                                {{ $am->nama }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>{{ $am->witel->nama ?? 'N/A' }}</td>
                                    <td>
                                        <div class="divisi-pills">
                                            @if($am->divisis && $am->divisis->count() > 0)
                                                @foreach($am->divisis as $divisi)
                                                    <span class="divisi-pill badge-{{ strtolower($divisi->nama) }}">
                                                        {{ $divisi->nama }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($am->total_revenue, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <span class="status-badge {{ $achievementClass }}">
                                            {{ number_format($am->achievement_percentage, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-search"></i>
                                            <h5>Belum ada data</h5>
                                            <p>Tidak ada account manager yang ditemukan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue Chart -->
        <div class="dashboard-card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title" id="yearFilterTitle">Revenue Bulanan Keseluruhan Account Manager ({{ $selectedYear ?? date('Y') }})</h5>
                    <p class="text-muted small mb-0">Data revenue dari {{ $activeAccountManagersCount ?? 0 }} account manager aktif</p>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <div class="input-group">
                            <input type="number" class="form-control form-control-sm year-input" id="yearFilter"
                                   placeholder="Tahun" min="2020" max="2030" value="{{ $selectedYear ?? date('Y') }}">
                            <button class="btn btn-sm btn-primary" id="applyYearFilter">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern m-0" id="monthlyRevenueTable">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th class="text-end">Target</th>
                                <th class="text-end">Realisasi</th>
                                <th class="text-end">Achievement</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $months = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp

                            @forelse($monthlyRevenue ?? [] as $revenue)
                                @php
                                    $achievement = $revenue->target > 0
                                        ? round(($revenue->realisasi / $revenue->target) * 100, 1)
                                        : 0;

                                    $statusClass = $achievement >= 100
                                        ? 'bg-success-soft'
                                        : ($achievement >= 80 ? 'bg-warning-soft' : 'bg-danger-soft');

                                    $statusIcon = $achievement >= 100
                                        ? 'check-circle'
                                        : ($achievement >= 80 ? 'clock' : 'times-circle');
                                @endphp
                                <tr>
                                    <td>{{ $months[$revenue->month] ?? 'Unknown' }}</td>
                                    <td class="text-end">Rp {{ number_format($revenue->target, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($revenue->realisasi, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <span class="status-badge {{ $statusClass }}">{{ $achievement }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-{{ $statusIcon }} {{ $achievement >= 100 ? 'text-success' : ($achievement >= 80 ? 'text-warning' : 'text-danger') }}"></i>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-chart-bar"></i>
                                            <h5>Belum ada data revenue</h5>
                                            <p>Tidak ada data revenue untuk ditampilkan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @elseif($user->role === 'account_manager' && $accountManager)
        <!-- ===== ACCOUNT MANAGER DASHBOARD ===== -->
        <div class="row g-4">
            <!-- Card 1 - Total Revenue -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-indicator revenue-indicator"></div>
                    <div class="stats-title">Total Revenue</div>
                    <div class="stats-value">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
                    <div class="stats-period">{{ $periodRange ?? 'Belum ada data' }}</div>
                    <div class="stats-icon icon-revenue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <!-- Card 2 - Target Revenue -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-indicator target-indicator"></div>
                    <div class="stats-title">Target Revenue</div>
                    <div class="stats-value">Rp {{ number_format($totalTarget ?? 0, 0, ',', '.') }}</div>
                    <div class="stats-period">{{ $periodRange ?? 'Belum ada data' }}</div>
                    <div class="stats-icon icon-target">
                        <i class="fas fa-bullseye"></i>
                    </div>
                </div>
            </div>

            <!-- Card 3 - Achievement -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-indicator achievement-indicator"></div>
                    <div class="stats-title">Achievement</div>
                    <div class="stats-value">{{ $achievementPercentage ?? 0 }}%</div>
                    <div class="stats-period">
                        @if(($achievementPercentage ?? 0) >= 100)
                            <span class="text-success"><i class="fas fa-check-circle"></i> Target tercapai</span>
                        @else
                            <span class="text-danger"><i class="fas fa-times-circle"></i> Belum mencapai target</span>
                        @endif
                    </div>
                    <div class="stats-icon icon-achievement">
                        <i class="fas fa-medal"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Manager Info -->
        <div class="dashboard-card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Informasi Account Manager</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ asset($accountManager->user && $accountManager->user->profile_image ? 'storage/'.$accountManager->user->profile_image : 'img/profile.png') }}"
                                class="am-profile-pic"
                                alt="{{ $accountManager->nama ?? 'Profile' }}">

                            <div class="ms-3">
                                <h5 class="mb-1">{{ $accountManager->nama ?? 'N/A' }}</h5>
                                <p class="text-muted mb-0">{{ $accountManager->nik ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light rounded-3 border-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <p class="text-muted mb-0">Divisi</p>
                                        <div class="divisi-pills">
                                            @if($accountManager->divisis && $accountManager->divisis->count() > 0)
                                                @foreach($accountManager->divisis as $divisi)
                                                    <span class="divisi-pill badge-{{ strtolower($divisi->nama) }}">
                                                        {{ $divisi->nama }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted mb-0">Witel</p>
                                        <p class="mb-0 fw-bold">{{ $accountManager->witel->nama ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Corporate Customers Section -->
        <div class="customer-section">
            <div class="customer-header">
                <h5 class="customer-title">
                    <i class="fas fa-building"></i>
                    Corporate Customers
                </h5>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-divisi="all">
                        Semua Divisi
                    </button>
                    @if($accountManager->divisis && $accountManager->divisis->count() > 0)
                        @foreach($accountManager->divisis as $divisi)
                            <button class="filter-btn" data-divisi="{{ $divisi->id }}">
                                {{ $divisi->nama }}
                            </button>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="customer-table" id="customerList">
                @if(isset($corporateCustomers) && $corporateCustomers->count() > 0)
                    @foreach($corporateCustomers as $customer)
                        <div class="customer-item" data-divisi="{{ $customer->pivot->divisi_id ?? 'all' }}">
                            <div class="customer-info">
                                <h6>{{ $customer->nama }}</h6>
                                <p>NIPNAS: {{ $customer->nipnas }}</p>
                            </div>
                            <div>
                                @if(isset($customer->pivot->divisi_id))
                                    @php
                                        $divisiName = $accountManager->divisis->where('id', $customer->pivot->divisi_id)->first()->nama ?? 'Unknown';
                                    @endphp
                                    <span class="customer-badge badge-{{ strtolower($divisiName) }}">
                                        {{ $divisiName }}
                                    </span>
                                @else
                                    <span class="customer-badge badge-all">Semua Divisi</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="fas fa-building"></i>
                        <h5>Belum ada Corporate Customer</h5>
                        <p>Tidak ada corporate customer yang terhubung dengan account manager ini</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Monthly Revenue Performance -->
        <div class="dashboard-card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title" id="yearFilterTitle">Revenue Bulanan ({{ $selectedYear ?? date('Y') }})</h5>
                    <p class="text-muted small mb-0">Data performa revenue Anda per bulan</p>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <div class="input-group">
                            <input type="number" class="form-control form-control-sm year-input" id="yearFilter"
                                   placeholder="Tahun" min="2020" max="2030" value="{{ $selectedYear ?? date('Y') }}">
                            <button class="btn btn-sm btn-primary" id="applyYearFilter">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern m-0" id="monthlyRevenueTable">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th class="text-end">Target</th>
                                <th class="text-end">Realisasi</th>
                                <th class="text-end">Achievement</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $months = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp

                            @forelse($monthlyRevenue ?? [] as $revenue)
                                @php
                                    $achievement = $revenue->target > 0
                                        ? round(($revenue->realisasi / $revenue->target) * 100, 1)
                                        : 0;

                                    $statusClass = $achievement >= 100
                                        ? 'bg-success-soft'
                                        : ($achievement >= 80 ? 'bg-warning-soft' : 'bg-danger-soft');

                                    $statusIcon = $achievement >= 100
                                        ? 'check-circle'
                                        : ($achievement >= 80 ? 'clock' : 'times-circle');
                                @endphp
                                <tr>
                                    <td>{{ $months[$revenue->month] ?? 'Unknown' }}</td>
                                    <td class="text-end">Rp {{ number_format($revenue->target, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($revenue->realisasi, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <span class="status-badge {{ $statusClass }}">{{ $achievement }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-{{ $statusIcon }} {{ $achievement >= 100 ? 'text-success' : ($achievement >= 80 ? 'text-warning' : 'text-danger') }}"></i>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-chart-bar"></i>
                                            <h5>Belum ada data revenue</h5>
                                            <p>Tidak ada data revenue untuk ditampilkan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @elseif($user->role === 'witel')
        <!-- ===== WITEL DASHBOARD ===== -->
        <div class="row g-4">
            <!-- Card 1 - Total Revenue -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-indicator revenue-indicator"></div>
                    <div class="stats-title">Total Revenue Witel</div>
                    <div class="stats-value">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
                    <div class="stats-period">{{ $periodRange ?? 'Belum ada data' }}</div>
                    <div class="stats-icon icon-revenue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <!-- Card 2 - Target Revenue -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-indicator target-indicator"></div>
                    <div class="stats-title">Target Revenue Witel</div>
                    <div class="stats-value">Rp {{ number_format($totalTarget ?? 0, 0, ',', '.') }}</div>
                    <div class="stats-period">{{ $periodRange ?? 'Belum ada data' }}</div>
                    <div class="stats-icon icon-target">
                        <i class="fas fa-bullseye"></i>
                    </div>
                </div>
            </div>

            <!-- Card 3 - Achievement -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-indicator achievement-indicator"></div>
                    <div class="stats-title">Achievement Witel</div>
                    <div class="stats-value">{{ $achievementPercentage ?? 0 }}%</div>
                    <div class="stats-period">
                        @if(($achievementPercentage ?? 0) >= 100)
                            <span class="text-success"><i class="fas fa-check-circle"></i> Target tercapai</span>
                        @else
                            <span class="text-danger"><i class="fas fa-times-circle"></i> Belum mencapai target</span>
                        @endif
                    </div>
                    <div class="stats-icon icon-achievement">
                        <i class="fas fa-medal"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Filter Section -->
        <div class="category-filter-section">
            <div class="filter-title">
                <i class="fas fa-filter"></i>
                Filter Kategori Account Manager
            </div>
            <div class="category-buttons">
                <a href="?category=all&year={{ $selectedYear ?? date('Y') }}"
                   class="category-btn {{ ($selectedCategory ?? 'all') === 'all' ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i>Semua Kategori
                </a>
                <a href="?category=enterprise&year={{ $selectedYear ?? date('Y') }}"
                   class="category-btn {{ ($selectedCategory ?? '') === 'enterprise' ? 'active' : '' }}">
                    <i class="fas fa-building me-2"></i>Enterprise (DPS/DSS)
                </a>
                <a href="?category=government&year={{ $selectedYear ?? date('Y') }}"
                   class="category-btn {{ ($selectedCategory ?? '') === 'government' ? 'active' : '' }}">
                    <i class="fas fa-university me-2"></i>Government (DGS)
                </a>
                <a href="?category=multi&year={{ $selectedYear ?? date('Y') }}"
                   class="category-btn {{ ($selectedCategory ?? '') === 'multi' ? 'active' : '' }}">
                    <i class="fas fa-layer-group me-2"></i>Multi Divisi
                </a>
            </div>
        </div>

        <!-- Top Account Managers per Witel -->
        <div class="dashboard-card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title">Top 10 Account Managers - {{ $currentWitel->nama ?? 'Witel' }}</h5>
                    <p class="text-muted small mb-0">
                        Berdasarkan total revenue
                        @if($selectedCategory && $selectedCategory !== 'all')
                            kategori {{ ucfirst($selectedCategory) }}
                        @endif
                    </p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light" type="button" id="topAmOptions" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="topAmOptions">
                        <li><a class="dropdown-item" href="{{ route('leaderboard') }}">Lihat Semua</a></li>
                        <li><a class="dropdown-item" href="#">Filter</a></li>
                        <li><a class="dropdown-item" href="#">Refresh Data</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern m-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Divisi</th>
                                <th>Kategori</th>
                                <th class="text-end">Total Revenue</th>
                                <th class="text-end">Achievement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topAMs ?? [] as $am)
                                @php
                                    $achievementClass = $am->achievement_percentage >= 100
                                        ? 'bg-success-soft'
                                        : ($am->achievement_percentage >= 80 ? 'bg-warning-soft' : 'bg-danger-soft');
                                @endphp
                                <tr class="clickable-row" onclick="window.location.href='{{ route('account_manager.detail', $am->id) }}'">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset($am->user && $am->user->profile_image ? 'storage/'.$am->user->profile_image : 'img/profile.png') }}"
                                                 class="am-profile-pic" alt="{{ $am->nama }}">
                                            <a href="{{ route('account_manager.detail', $am->id) }}" class="clickable-name ms-2">
                                                {{ $am->nama }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="divisi-pills">
                                            @if($am->divisis && $am->divisis->count() > 0)
                                                @foreach($am->divisis as $divisi)
                                                    <span class="divisi-pill badge-{{ strtolower($divisi->nama) }}">
                                                        {{ $divisi->nama }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $am->category ?? 'Unknown' }}</span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($am->total_revenue, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <span class="status-badge {{ $achievementClass }}">
                                            {{ number_format($am->achievement_percentage, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-search"></i>
                                            <h5>Belum ada data</h5>
                                            <p>Tidak ada account manager yang ditemukan untuk kategori ini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue Chart for Witel -->
        <div class="dashboard-card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title" id="yearFilterTitle">Revenue Bulanan {{ $currentWitel->nama ?? 'Witel' }} ({{ $selectedYear ?? date('Y') }})</h5>
                    <p class="text-muted small mb-0">Data revenue dari {{ $activeAccountManagersCount ?? 0 }} account manager di witel ini</p>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <div class="input-group">
                            <input type="number" class="form-control form-control-sm year-input" id="yearFilter"
                                   placeholder="Tahun" min="2020" max="2030" value="{{ $selectedYear ?? date('Y') }}">
                            <button class="btn btn-sm btn-primary" id="applyYearFilter">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern m-0" id="monthlyRevenueTable">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th class="text-end">Target</th>
                                <th class="text-end">Realisasi</th>
                                <th class="text-end">Achievement</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $months = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp

                            @forelse($monthlyRevenue ?? [] as $revenue)
                                @php
                                    $achievement = $revenue->target > 0
                                        ? round(($revenue->realisasi / $revenue->target) * 100, 1)
                                        : 0;

                                    $statusClass = $achievement >= 100
                                        ? 'bg-success-soft'
                                        : ($achievement >= 80 ? 'bg-warning-soft' : 'bg-danger-soft');

                                    $statusIcon = $achievement >= 100
                                        ? 'check-circle'
                                        : ($achievement >= 80 ? 'clock' : 'times-circle');
                                @endphp
                                <tr>
                                    <td>{{ $months[$revenue->month] ?? 'Unknown' }}</td>
                                    <td class="text-end">Rp {{ number_format($revenue->target, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($revenue->realisasi, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <span class="status-badge {{ $statusClass }}">{{ $achievement }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-{{ $statusIcon }} {{ $achievement >= 100 ? 'text-success' : ($achievement >= 80 ? 'text-warning' : 'text-danger') }}"></i>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-chart-bar"></i>
                                            <h5>Belum ada data revenue</h5>
                                            <p>Tidak ada data revenue untuk ditampilkan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else
        <!-- Default Dashboard atau Role tidak dikenali -->
        <div class="dashboard-card">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-info-circle"></i>
                    <h4>Informasi</h4>
                    <p class="mb-3">Anda belum memiliki data yang cukup untuk melihat dashboard performa.</p>
                    @if($user->role === 'account_manager')
                        <p class="mb-4">Akun Anda belum terhubung dengan data Account Manager. Silakan hubungi administrator.</p>
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-headphones me-1"></i> Hubungi Administrator
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    console.log('Dashboard loaded successfully');

    // Corporate Customer Filter for Account Manager
    $('.filter-btn').on('click', function() {
        const divisiId = $(this).data('divisi');

        // Update active state
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');

        // Show/hide customers based on filter
        if (divisiId === 'all') {
            $('.customer-item').show();
        } else {
            $('.customer-item').hide();
            $(`.customer-item[data-divisi="${divisiId}"]`).show();
        }
    });

    // Fungsi untuk memfilter revenue berdasarkan tahun
    function filterRevenueByYear(year) {
        const currentParams = new URLSearchParams(window.location.search);
        currentParams.set('year', year);

        // Preserve other parameters like category for witel
        window.location.href = window.location.pathname + '?' + currentParams.toString();
    }

    // Event handler untuk tombol filter tahun
    $('#applyYearFilter').on('click', function() {
        const year = $('#yearFilter').val();
        if (year && year >= 2000 && year <= 2100) {
            filterRevenueByYear(year);
        }
    });

    // Mendukung tombol Enter pada input filter tahun
    $('#yearFilter').on('keyup', function(e) {
        if (e.key === 'Enter') {
            $('#applyYearFilter').click();
        }
    });

    // Clickable rows enhancement
    $('.clickable-row').on('click', function(e) {
        // Prevent navigation if clicking on a link inside the row
        if (e.target.tagName === 'A' || e.target.closest('a')) {
            return;
        }

        const href = $(this).attr('onclick');
        if (href) {
            eval(href);
        }
    });

    // Enhanced hover effects
    $('.clickable-row').hover(
        function() {
            $(this).addClass('table-hover-effect');
        },
        function() {
            $(this).removeClass('table-hover-effect');
        }
    );

    // Loading skeleton for empty states (could be enhanced with actual loading)
    function showLoadingSkeleton(targetElement) {
        const skeleton = `
            <div class="skeleton skeleton-row"></div>
            <div class="skeleton skeleton-row"></div>
            <div class="skeleton skeleton-row"></div>
        `;
        $(targetElement).html(skeleton);
    }

    // Category filter for witel (if needed via AJAX)
    $('.category-btn').on('click', function(e) {
        // Add loading state
        $(this).append(' <i class="fas fa-spinner fa-spin ms-1"></i>');
    });
});
</script>
@endsection
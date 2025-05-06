@extends('layouts.main')

@section('title', 'Dashboard Performa')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/overview.css') }}">
@endsection

@section('content')
<div class="main-content">
    <!-- Header Dashboard -->
    <div class="header-dashboard">
        <h1 class="header-title">
            Dashboard Performansi
        </h1>
        <p class="header-subtitle">
            Monitoring dan Analisis Performa Pendapatan Account Manager
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
        <!-- Admin Dashboard -->
        <div class="row g-4">
            <!-- Card 1 - Total Revenue -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-indicator revenue-indicator"></div>
                    <div class="stats-title">Total Revenue</div>
                    <div class="stats-value">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
                    <div class="stats-period">Periode: Jan - Des {{ date('Y') }}</div>
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
                    <div class="stats-period">Periode: Jan - Des {{ date('Y') }}</div>
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
                    <p class="text-muted small mb-0">Berdasarkan total pendapatan seluruh periode</p>
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
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset($am->user && $am->user->profile_image ? 'storage/'.$am->user->profile_image : 'img/profile.png') }}" class="am-profile-pic" alt="{{ $am->nama }}">
                                            <span class="ms-2">{{ $am->nama }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $am->witel->nama ?? 'N/A' }}</td>
                                    <td class="text-end">Rp {{ number_format($am->total_revenue, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <span class="status-badge {{ $achievementClass }}">
                                            {{ number_format($am->achievement_percentage, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-search fs-4 d-block mb-2"></i>
                                        Belum ada data
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
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-chart-bar fs-4 d-block mb-2"></i>
                                        Belum ada data revenue
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @elseif($user->role === 'account_manager' && $accountManager)
        <!-- Account Manager Dashboard -->
        <div class="row g-4">
            <!-- Card 1 - Total Revenue -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-indicator revenue-indicator"></div>
                    <div class="stats-title">Total Revenue</div>
                    <div class="stats-value">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
                    <div class="stats-period">Periode: Jan - Des {{ date('Y') }}</div>
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
                    <div class="stats-period">Periode: Jan - Des {{ date('Y') }}</div>
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
                                        <p class="mb-2 fw-bold">{{ $accountManager->divisi->nama ?? 'N/A' }}</p>
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
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-chart-bar fs-4 d-block mb-2"></i>
                                        Belum ada data revenue
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <!-- Default Dashboard atau Account Manager tanpa data -->
        <div class="dashboard-card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-info-circle fs-1 text-warning"></i>
                </div>
                <h4>Informasi</h4>
                <p class="text-muted mb-3">Anda belum memiliki data yang cukup untuk melihat dashboard performa.</p>
                @if($user->role === 'account_manager')
                    <p class="mb-4">Akun Anda belum terhubung dengan data Account Manager. Silakan hubungi administrator.</p>
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-headphones me-1"></i> Hubungi Administrator
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    console.log('Dashboard loaded successfully');

    // Fungsi untuk memfilter revenue berdasarkan tahun
    function filterRevenueByYear(year) {
        $.ajax({
            url: '/dashboard/revenues',
            method: 'GET',
            data: { year: year },
            dataType: 'json',
            beforeSend: function() {
                // Tampilkan loading state
                $('#monthlyRevenueTable tbody').html('<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin fs-4"></i> Loading data...</td></tr>');
            },
            success: function(response) {
                updateRevenueTable(response.data, response.year);
            },
            error: function(xhr) {
                // Tampilkan pesan error
                $('#monthlyRevenueTable tbody').html('<tr><td colspan="5" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fs-4 mb-2"></i><br>Gagal memuat data. Silakan coba lagi.</td></tr>');
                console.error('Error fetching revenue data:', xhr.responseText);
            }
        });
    }

    // Fungsi untuk mengupdate tabel revenue
    function updateRevenueTable(data, year) {
        const months = {
            1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
            5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
            9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
        };

        let tableHtml = '';

        if (data.length > 0) {
            data.forEach(function(revenue) {
                const achievement = revenue.target > 0
                    ? Math.round((revenue.realisasi / revenue.target) * 100 * 10) / 10
                    : 0;

                const statusClass = achievement >= 100
                    ? 'bg-success-soft'
                    : (achievement >= 80 ? 'bg-warning-soft' : 'bg-danger-soft');

                const statusIcon = achievement >= 100
                    ? 'check-circle'
                    : (achievement >= 80 ? 'clock' : 'times-circle');

                const iconColorClass = achievement >= 100
                    ? 'text-success'
                    : (achievement >= 80 ? 'text-warning' : 'text-danger');

                tableHtml += `
                <tr>
                    <td>${months[revenue.month] || 'Unknown'}</td>
                    <td class="text-end">Rp ${formatNumber(revenue.target)}</td>
                    <td class="text-end">Rp ${formatNumber(revenue.realisasi)}</td>
                    <td class="text-end">
                        <span class="status-badge ${statusClass}">${achievement}%</span>
                    </td>
                    <td class="text-center">
                        <i class="fas fa-${statusIcon} ${iconColorClass}"></i>
                    </td>
                </tr>
                `;
            });
        } else {
            tableHtml = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="fas fa-chart-bar fs-4 d-block mb-2"></i>
                    Tidak ada data revenue untuk tahun ${year}
                </td>
            </tr>
            `;
        }

        $('#monthlyRevenueTable tbody').html(tableHtml);
        $('#yearFilterTitle').text($('#yearFilterTitle').text().replace(/\(\d+\)/, `(${year})`));
    }

    // Helper function untuk format angka
    function formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
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
});
</script>
@endsection
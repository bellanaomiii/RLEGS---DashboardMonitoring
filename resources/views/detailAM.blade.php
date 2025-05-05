@extends('layouts.main')

@section('title', 'Detail Account Manager')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<link rel="stylesheet" href="{{ asset('css/detailAM.css') }}">
<style>
    /* Perbaikan untuk menghilangkan garis bawah pada info AM */
    .meta-item, .meta-item:hover {
        text-decoration: none !important;
    }

    /* Memperbaiki posisi dan tampilan badge naik/turun */
    .rank-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Konsistensi warna */
    .rank-badge.up {
        background-color: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }

    .rank-badge.down {
        background-color: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }

    .rank-badge.neutral {
        background-color: rgba(107, 114, 128, 0.15);
        color: #6b7280;
    }

    /* Konsistensi warna di text rank change */
    .rank-change-detail.up {
        color: #10b981;
    }

    .rank-change-detail.down {
        color: #ef4444;
    }

    .rank-change-detail.neutral {
        color: #6b7280;
    }

    /* Rounded tab navigation */
    .tab-navigation {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        overflow: hidden;
    }

    /* Add space after content wrapper */
    .content-wrapper {
        margin-bottom: 30px;
    }

    /* Add space between tab content and next section */
    .tab-content {
        padding-bottom: 40px;
    }

    /* Tab button rounded corners */
    .tab-button:first-child {
        border-top-left-radius: 15px;
    }

    .tab-button:last-child {
        border-top-right-radius: 15px;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <!-- Profile Overview -->
    <div class="profile-overview">
        <div class="profile-avatar-container">
            <img src="{{ asset($accountManager->user && $accountManager->user->profile_image ? 'storage/'.$accountManager->user->profile_image : 'img/profile.png') }}"
                 class="profile-avatar" alt="{{ $accountManager->nama }}">
        </div>
        <div class="profile-details">
            <h2 class="profile-name">{{ $accountManager->nama }}</h2>
            <div class="profile-meta">
                <div class="meta-item">
                    <i class="lni lni-id-card"></i>
                    <span>NIK: {{ $accountManager->nik }}</span>
                </div>
                <div class="meta-item">
                    <i class="lni lni-map-marker"></i>
                    <span>WITEL: {{ $accountManager->witel->nama ?? 'N/A' }}</span>
                </div>
                <div class="meta-item">
                    <i class="lni lni-network"></i>
                    <span>DIVISI: {{ ($accountManager->divisis->isNotEmpty() ? $accountManager->divisis->first()->nama : 'N/A') }}</span>
                </div>
            </div>
        </div>
    </div>

<!-- Rankings -->
<div class="rankings-container">
    <!-- Calculate ranking changes -->
    @php
        $globalRankIcon = "1-10.svg";
        if ($globalRanking['position'] > 10 && $globalRanking['position'] <= 50) {
            $globalRankIcon = "10-50.svg";
        } elseif ($globalRanking['position'] > 50) {
            $globalRankIcon = "up100.svg";
        }

        $witelRankIcon = "1-10.svg";
        if (is_numeric($witelRanking['position'])) {
            if ($witelRanking['position'] > 10 && $witelRanking['position'] <= 50) {
                $witelRankIcon = "10-50.svg";
            } elseif ($witelRanking['position'] > 50) {
                $witelRankIcon = "up100.svg";
            }
        }

        $divisionRankIcon = "1-10.svg";
        if (is_numeric($divisionRanking['position'])) {
            if ($divisionRanking['position'] > 10 && $divisionRanking['position'] <= 50) {
                $divisionRankIcon = "10-50.svg";
            } elseif ($divisionRanking['position'] > 50) {
                $divisionRankIcon = "up100.svg";
            }
        }

        // Calculate ranking changes
        $globalChange = isset($globalRanking['position_change']) ? $globalRanking['position_change'] : 0;
        if ($globalChange > 0) {
            $globalChangeClass = 'text-success';
            $globalChangeBadgeClass = 'up';
            $globalChangeIcon = 'lni-arrow-up';
            $globalChangeText = 'naik ' . $globalChange;
            $globalBadgeText = 'Naik ' . $globalChange;
        } elseif ($globalChange < 0) {
            $globalChangeClass = 'text-danger';
            $globalChangeBadgeClass = 'down';
            $globalChangeIcon = 'lni-arrow-down';
            $globalChangeText = 'turun ' . abs($globalChange);
            $globalBadgeText = 'Turun ' . abs($globalChange);
        } else {
            $globalChangeClass = 'text-muted';
            $globalChangeBadgeClass = 'neutral';
            $globalChangeIcon = 'lni-minus';
            $globalChangeText = 'tetap';
            $globalBadgeText = 'Tetap';
        }

        $witelChange = isset($witelRanking['position_change']) ? $witelRanking['position_change'] : 0;
        if ($witelChange > 0) {
            $witelChangeClass = 'text-success';
            $witelChangeBadgeClass = 'up';
            $witelChangeIcon = 'lni-arrow-up';
            $witelChangeText = 'naik ' . $witelChange;
            $witelBadgeText = 'Naik ' . $witelChange;
        } elseif ($witelChange < 0) {
            $witelChangeClass = 'text-danger';
            $witelChangeBadgeClass = 'down';
            $witelChangeIcon = 'lni-arrow-down';
            $witelChangeText = 'turun ' . abs($witelChange);
            $witelBadgeText = 'Turun ' . abs($witelChange);
        } else {
            $witelChangeClass = 'text-muted';
            $witelChangeBadgeClass = 'neutral';
            $witelChangeIcon = 'lni-minus';
            $witelChangeText = 'tetap';
            $witelBadgeText = 'Tetap';
        }

        $divisionChange = isset($divisionRanking['position_change']) ? $divisionRanking['position_change'] : 0;
        if ($divisionChange > 0) {
            $divisionChangeClass = 'text-success';
            $divisionChangeBadgeClass = 'up';
            $divisionChangeIcon = 'lni-arrow-up';
            $divisionChangeText = 'naik ' . $divisionChange;
            $divisionBadgeText = 'Naik ' . $divisionChange;
        } elseif ($divisionChange < 0) {
            $divisionChangeClass = 'text-danger';
            $divisionChangeBadgeClass = 'down';
            $divisionChangeIcon = 'lni-arrow-down';
            $divisionChangeText = 'turun ' . abs($divisionChange);
            $divisionBadgeText = 'Turun ' . abs($divisionChange);
        } else {
            $divisionChangeClass = 'text-muted';
            $divisionChangeBadgeClass = 'neutral';
            $divisionChangeIcon = 'lni-minus';
            $divisionChangeText = 'tetap';
            $divisionBadgeText = 'Tetap';
        }

        $currentMonth = date('F');
        $previousMonth = date('F', strtotime('-1 month'));

        // Translate month names
        $monthNames = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember'
        ];

        $currentMonthID = $monthNames[$currentMonth] ?? $currentMonth;
        $previousMonthID = $monthNames[$previousMonth] ?? $previousMonth;
    @endphp

    <a href="{{ route('leaderboard') }}" class="ranking-card global">
        <div class="ranking-icon">
            <img src="{{ asset('img/' . $globalRankIcon) }}" alt="Peringkat" width="40" height="40">
        </div>
        <div class="ranking-info">
            <div class="ranking-title">Peringkat Global</div>
            <div class="ranking-value">
                {{ $globalRanking['position'] }} dari {{ $globalRanking['total'] }}
                @if ($globalChange != 0)
                    <span class="{{ $globalChangeClass }} ml-2" style="font-size: 14px;">
                        <i class="lni {{ $globalChangeIcon }}"></i>
                    </span>
                @endif
            </div>
            <span class="rank-change-detail {{ $globalChangeBadgeClass }}">{{ $globalChangeText }} dari {{ $previousMonthID }}</span>
        </div>
        @if($globalChange != 0)
            <div class="rank-badge {{ $globalChangeBadgeClass }}">
                <i class="lni {{ $globalChangeIcon }}"></i>
                {{ $globalBadgeText }}
            </div>
        @endif
    </a>

    <div class="ranking-card witel">
        <div class="ranking-icon">
            <img src="{{ asset('img/' . $witelRankIcon) }}" alt="Peringkat" width="40" height="40">
        </div>
        <div class="ranking-info">
            <div class="ranking-title">Peringkat Witel</div>
            <div class="ranking-value">
                {{ $witelRanking['position'] }} dari {{ $witelRanking['total'] }}
                @if ($witelChange != 0 && is_numeric($witelRanking['position']))
                    <span class="{{ $witelChangeClass }} ml-2" style="font-size: 14px;">
                        <i class="lni {{ $witelChangeIcon }}"></i>
                    </span>
                @endif
            </div>
            @if(is_numeric($witelRanking['position']))
                <span class="rank-change-detail {{ $witelChangeBadgeClass }}">{{ $witelChangeText }} dari {{ $previousMonthID }}</span>
            @else
                <span class="rank-change-detail text-muted">belum ada data</span>
            @endif
        </div>
        @if($witelChange != 0 && is_numeric($witelRanking['position']))
            <div class="rank-badge {{ $witelChangeBadgeClass }}">
                <i class="lni {{ $witelChangeIcon }}"></i>
                {{ $witelBadgeText }}
            </div>
        @endif
    </div>

    <div class="ranking-card division">
        <div class="ranking-icon">
            <img src="{{ asset('img/' . $divisionRankIcon) }}" alt="Peringkat" width="40" height="40">
        </div>
        <div class="ranking-info">
            <div class="ranking-title">Peringkat Divisi</div>
            <div class="ranking-value">
                {{ $divisionRanking['position'] }} dari {{ $divisionRanking['total'] }}
                @if ($divisionChange != 0 && is_numeric($divisionRanking['position']))
                    <span class="{{ $divisionChangeClass }} ml-2" style="font-size: 14px;">
                        <i class="lni {{ $divisionChangeIcon }}"></i>
                    </span>
                @endif
            </div>
            @if(is_numeric($divisionRanking['position']))
                <span class="rank-change-detail {{ $divisionChangeBadgeClass }}">{{ $divisionChangeText }} dari {{ $previousMonthID }}</span>
            @else
                <span class="rank-change-detail text-muted">belum ada data</span>
            @endif
        </div>
        @if($divisionChange != 0 && is_numeric($divisionRanking['position']))
            <div class="rank-badge {{ $divisionChangeBadgeClass }}">
                <i class="lni {{ $divisionChangeIcon }}"></i>
                {{ $divisionBadgeText }}
            </div>
        @endif
    </div>
</div>

    <!-- Content Tabs -->
    <div class="content-wrapper">
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-button active" data-tab="customer-data">
                <i class="fas fa-users"></i> Data Customer
            </button>
            <button class="tab-button" data-tab="performance-analysis">
                <i class="fas fa-chart-line"></i> Analisis Performa
            </button>
        </div>

        <!-- Tab Content - Customer Data -->
        <div id="customer-data" class="tab-content active">
            <div class="tab-content-header">
                <div class="tab-content-title">
                    <i class="fas fa-users"></i> Data Customer & Revenue
                </div>

                <div class="filters-container">
                    <div class="filter-group me-2">
                        <select id="filterCustomer" class="selectpicker" title="Filter">
                            <option value="all">Semua</option>
                            <option value="highest_achievement">Pencapaian Tertinggi</option>
                            <option value="highest_revenue">Pendapatan Tertinggi</option>
                        </select>
                    </div>

                    <div class="year-selector">
                        <select name="year" id="year-select" class="selectpicker" data-live-search="true" title="Pilih Tahun">
                            @foreach($yearsList as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Customer Table -->
            <div class="data-card">
                @if(count($customerRevenues) > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>NIPNAS</th>
                                    <th>Target Revenue</th>
                                    <th>Real Revenue</th>
                                    <th>Pencapaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customerRevenues as $customer)
                                    <tr>
                                        <td>
                                            <div class="customer-name">{{ $customer->nama }}</div>
                                        </td>
                                        <td>
                                            <div class="nipnas">{{ $customer->nipnas }}</div>
                                        </td>
                                        <td>Rp {{ number_format($customer->total_target, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($customer->total_revenue, 0, ',', '.') }}</td>
                                        <td>
                                            @php
                                                $achievementClass = 'badge-danger';
                                                if ($customer->achievement >= 100) {
                                                    $achievementClass = 'badge-success';
                                                } elseif ($customer->achievement >= 80) {
                                                    $achievementClass = 'badge-warning';
                                                }
                                            @endphp
                                            <span class="achievement-badge {{ $achievementClass }}">
                                                {{ number_format($customer->achievement, 2, ',', '.') }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <p class="empty-text">Tidak ada data customer untuk tahun {{ $selectedYear }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tab Content - Performance Analysis -->
        <div id="performance-analysis" class="tab-content">
            <div class="tab-content-header">
                <div class="tab-content-title">
                    <i class="fas fa-chart-line"></i> Analisis Performa & Insight
                </div>
            </div>

            <!-- Total Revenue Summary - NEW SECTION -->
            <div class="total-revenue-summary">
                <div class="revenue-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="revenue-content">
                    <div class="revenue-label">Total Pendapatan Sepanjang Waktu</div>
                    <div class="revenue-value">
                        @php
                            $totalAllTimeRevenue = $accountManager->revenues->sum('real_revenue');
                            if ($totalAllTimeRevenue >= 1000000000) {
                                echo 'Rp ' . number_format($totalAllTimeRevenue / 1000000000, 2, ',', '.') . ' Miliar';
                            } elseif ($totalAllTimeRevenue >= 1000000) {
                                echo 'Rp ' . number_format($totalAllTimeRevenue / 1000000, 2, ',', '.') . ' Juta';
                            } else {
                                echo 'Rp ' . number_format($totalAllTimeRevenue, 0, ',', '.');
                            }
                        @endphp
                    </div>
                    <div class="revenue-period">Sejak {{ date('Y', strtotime('-3 years')) }} hingga {{ date('Y') }}</div>
                </div>
            </div>

            <!-- Insights Section -->
            <div class="insight-summary-card">
                <div class="insight-header">
                    <i class="fas fa-lightbulb"></i>
                    <h4>Ringkasan Performa</h4>
                </div>
                <div class="insight-body">
                    <p>{{ $insights['message'] }}</p>

                    <p>Berdasarkan analisis data selama {{ date('Y') }}, Account Manager <strong>{{ $accountManager->nama }}</strong>
                    menunjukkan pencapaian yang {{ $insights['avg_achievement'] >= 90 ? 'sangat baik' : ($insights['avg_achievement'] >= 80 ? 'baik' : 'perlu ditingkatkan') }}.
                    Dengan rata-rata pencapaian <strong>{{ number_format($insights['avg_achievement'], 2) }}%</strong> dan
                    tren performa yang {{ $insights['trend'] == 'up' ? 'meningkat' : ($insights['trend'] == 'down' ? 'menurun' : 'stabil') }}.</p>
                </div>
            </div>

            <div class="insight-metrics">
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Pencapaian Tertinggi</div>
                        <div class="metric-value">{{ $insights['best_achievement_month'] ? number_format($insights['best_achievement_month']['achievement'], 2) . '%' : 'N/A' }}</div>
                        <div class="metric-period">
                            @if($insights['best_achievement_month'])
                                @php
                                    $monthNames = [
                                        'January' => 'Januari',
                                        'February' => 'Februari',
                                        'March' => 'Maret',
                                        'April' => 'April',
                                        'May' => 'Mei',
                                        'June' => 'Juni',
                                        'July' => 'Juli',
                                        'August' => 'Agustus',
                                        'September' => 'September',
                                        'October' => 'Oktober',
                                        'November' => 'November',
                                        'December' => 'Desember'
                                    ];
                                    $monthName = $insights['best_achievement_month']['month_name'];
                                    echo isset($monthNames[$monthName]) ? $monthNames[$monthName] : $monthName;
                                @endphp
                            @endif
                        </div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Pendapatan Tertinggi</div>
                        <div class="metric-value">
                            @if($insights['best_revenue_month'])
                                @php
                                    $revenue = $insights['best_revenue_month']['real_revenue'];
                                    if ($revenue >= 1000000000) {
                                        echo 'Rp ' . number_format($revenue / 1000000000, 2, ',', '.') . ' M';
                                    } elseif ($revenue >= 1000000) {
                                        echo 'Rp ' . number_format($revenue / 1000000, 2, ',', '.') . ' Jt';
                                    } else {
                                        echo 'Rp ' . number_format($revenue, 0, ',', '.');
                                    }
                                @endphp
                            @else
                                N/A
                            @endif
                        </div>
                        <div class="metric-period">
                            @if($insights['best_revenue_month'])
                                @php
                                    $monthName = $insights['best_revenue_month']['month_name'];
                                    echo isset($monthNames[$monthName]) ? $monthNames[$monthName] : $monthName;
                                @endphp
                            @endif
                        </div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Rata-rata Pencapaian</div>
                        <div class="metric-value">{{ number_format($insights['avg_achievement'], 2) }}%</div>
                        <div class="metric-period">Sepanjang {{ $selectedYear }}</div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">
                        @if($insights['trend'] == 'up')
                            <i class="fas fa-arrow-up text-success"></i>
                        @elseif($insights['trend'] == 'down')
                            <i class="fas fa-arrow-down text-danger"></i>
                        @else
                            <i class="fas fa-minus text-muted"></i>
                        @endif
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Tren Performa</div>
                        <div class="metric-value">
                            @if($insights['trend'] == 'up')
                                <span class="text-success">Meningkat</span>
                            @elseif($insights['trend'] == 'down')
                                <span class="text-danger">Menurun</span>
                            @else
                                <span class="text-muted">Stabil</span>
                            @endif
                        </div>
                        <div class="metric-period">3 bulan terakhir</div>
                    </div>
                </div>
            </div>

            <!-- Performance Chart -->
            <div class="chart-container">
                <div class="chart-header">
                    <h4 class="chart-title">
                        <i class="fas fa-chart-bar"></i>
                        Grafik Performa Bulanan {{ $selectedYear }}
                    </h4>

                    <div class="chart-filters">
                        <div class="year-selector">
                            <select name="performance_year" id="performance-year-select" class="selectpicker" data-live-search="true" title="Pilih Tahun">
                                @foreach($yearsList as $year)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group me-2">
                            <select id="chartType" class="selectpicker" title="Tipe Tampilan">
                                <option value="combined" selected>Kombinasi</option>
                                <option value="revenue">Revenue</option>
                                <option value="achievement">Pencapaian</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="chart-canvas-container">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<!-- Bootstrap Select -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/js/bootstrap-select.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Bootstrap Select
    $('.selectpicker').selectpicker({
        liveSearch: true,
        liveSearchPlaceholder: 'Cari opsi...',
        size: 5,
        actionsBox: false,
        dropupAuto: false,
        mobile: false
    });

    // Year selector change event - Perbaikan agar keduanya bekerja
    $('#year-select, #performance-year-select').change(function() {
        if ($(this).val()) {
            window.location.href = "{{ route('account_manager.detail', $accountManager->id) }}?year=" + $(this).val();
        }
    });

    // Bold text for filter inner text
    $('.filter-option-inner-inner').css('font-weight', '700');

    // Tab Navigation
    $('.tab-button').on('click', function() {
        // Remove active class from all buttons and contents
        $('.tab-button').removeClass('active');
        $('.tab-content').removeClass('active');

        // Add active class to clicked button
        $(this).addClass('active');

        // Show corresponding content
        const tabId = $(this).data('tab');
        $('#' + tabId).addClass('active');

        // If switching to performance tab, render chart
        if (tabId === 'performance-analysis') {
            setTimeout(function() {
                renderPerformanceChart('combined');
            }, 100);
        }
    });

    // Customer Filters
    $('#filterCustomer').on('changed.bs.select', function() {
        const filterValue = $(this).val();

        if (filterValue === 'all') {
            // Show all rows
            $('.data-table tbody tr').show();
        } else if (filterValue === 'highest_achievement') {
            // Sort by achievement
            const rows = $('.data-table tbody tr').toArray();
            rows.sort(function(a, b) {
                const aValue = parseFloat($(a).find('.achievement-badge').text().replace(/\./g, '').replace(',', '.').replace('%', ''));
                const bValue = parseFloat($(b).find('.achievement-badge').text().replace(/\./g, '').replace(',', '.').replace('%', ''));
                return bValue - aValue;
            });

            $('.data-table tbody').empty().append(rows);
            // Show top 5 only
            $('.data-table tbody tr').hide().slice(0, 5).show();
        } else if (filterValue === 'highest_revenue') {
            // Sort by revenue
            const rows = $('.data-table tbody tr').toArray();
            rows.sort(function(a, b) {
                const aValue = parseInt($(a).find('td:eq(3)').text().replace(/[^\d]/g, ''));
                const bValue = parseInt($(b).find('td:eq(3)').text().replace(/[^\d]/g, ''));
                return bValue - aValue;
            });

            $('.data-table tbody').empty().append(rows);
            // Show top 5 only
            $('.data-table tbody tr').hide().slice(0, 5).show();
        }
    });

    // Chart Type Selector
    $('#chartType').on('changed.bs.select', function() {
        renderPerformanceChart($(this).val());
    });

    // Performance Chart
    function renderPerformanceChart(type) {
        const ctx = document.getElementById('performanceChart');
        if (!ctx) {
            console.error('Performance chart canvas not found');
            return;
        }

        // Check if chart exists and destroy it
        try {
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }
        } catch (e) {
            console.warn('Error destroying existing chart:', e);
        }

        // Convert month names to Indonesian
        const monthNames = {
            'January': 'Januari',
            'February': 'Februari',
            'March': 'Maret',
            'April': 'April',
            'May': 'Mei',
            'June': 'Juni',
            'July': 'Juli',
            'August': 'Agustus',
            'September': 'September',
            'October': 'Oktober',
            'November': 'November',
            'December': 'Desember'
        };

        // Prepare data
        const monthlyData = @json($monthlyPerformance);

        if (!monthlyData || monthlyData.length === 0) {
            $('.chart-canvas-container').html(
                '<div class="text-center py-5">' +
                '<i class="fas fa-chart-bar fs-1 text-muted mb-3"></i>' +
                '<p class="text-muted">Tidak ada data performa untuk ditampilkan</p>' +
                '</div>'
            );
            return;
        }

        // Translate month names to Indonesian
        const labels = monthlyData.map(item => {
            return monthNames[item.month_name] || item.month_name;
        });

        const revenueData = monthlyData.map(item => item.real_revenue);
        const targetData = monthlyData.map(item => item.target_revenue);
        const achievementData = monthlyData.map(item => item.achievement);

        // Create datasets based on view type
        let datasets = [];

        if (type === 'combined' || type === 'revenue') {
            datasets.push({
                label: 'Real Revenue',
                data: revenueData,
                backgroundColor: 'rgba(59, 125, 221, 0.6)',
                borderColor: 'rgba(59, 125, 221, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            });

            datasets.push({
                label: 'Target Revenue',
                data: targetData,
                backgroundColor: 'rgba(28, 41, 85, 0.2)',
                borderColor: 'rgba(28, 41, 85, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            });
        }

        if (type === 'combined' || type === 'achievement') {
            datasets.push({
                label: 'Pencapaian (%)',
                data: achievementData,
                type: 'line',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: '#1C2955',
                borderWidth: 2,
                pointBackgroundColor: '#1C2955',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#1C2955',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: false,
                tension: 0.3,
                yAxisID: 'y1'
            });
        }

        // Configure scales
        const scales = {};

        if (type === 'combined' || type === 'revenue') {
            scales.y = {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue (Rp)',
                    font: {
                        weight: 'bold'
                    }
                },
                ticks: {
                    callback: function(value) {
                        if (value >= 1000000000) {
                            return 'Rp ' + (value / 1000000000).toFixed(1) + ' M';
                        } else if (value >= 1000000) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                        } else {
                            return 'Rp ' + value;
                        }
                    }
                }
            };
        }

        if (type === 'combined' || type === 'achievement') {
            scales.y1 = {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Pencapaian (%)',
                    font: {
                        weight: 'bold'
                    }
                },
                grid: {
                    drawOnChartArea: type !== 'combined',
                },
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            };
        }

        // Create new chart
        try {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: scales,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(28, 41, 85, 0.8)',
                            titleFont: {
                                weight: 'bold',
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';

                                    if (label) {
                                        label += ': ';
                                    }

                                    if (context.dataset.yAxisID === 'y1') {
                                        label += context.parsed.y.toFixed(2) + '%';
                                    } else {
                                        const value = context.parsed.y;
                                        if (value >= 1000000000) {
                                            label += 'Rp ' + (value / 1000000000).toFixed(2) + ' M';
                                        } else if (value >= 1000000) {
                                            label += 'Rp ' + (value / 1000000).toFixed(2) + ' Jt';
                                        } else {
                                            label += 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                        }
                                    }

                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Error creating chart:', e);
            $('.chart-canvas-container').html(
                '<div class="alert alert-danger mt-3">' +
                '<i class="fas fa-exclamation-triangle me-2"></i>' +
                'Terjadi kesalahan saat membuat grafik: ' + e.message +
                '</div>'
            );
        }
    }

    // Initialize chart if performance tab is active
    if ($('#performance-analysis').hasClass('active')) {
        setTimeout(function() {
            renderPerformanceChart('combined');
        }, 300);
    }

    // Enhance ranking cards with animation
    $('.ranking-card').hover(
        function() {
            $(this).find('.ranking-icon img').css('transform', 'scale(1.15)');
        },
        function() {
            $(this).find('.ranking-icon img').css('transform', 'scale(1)');
        }
    );

    // Add subtle animation to metric cards
    $('.metric-card').hover(
        function() {
            $(this).find('.metric-value').css('transform', 'scale(1.05)');
            $(this).find('.metric-value').css('transition', 'transform 0.3s');
        },
        function() {
            $(this).find('.metric-value').css('transform', 'scale(1)');
        }
    );
});
</script>
@endsection
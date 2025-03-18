<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Selamat datang, {{ $user->name ?? 'User' }}!</h3>

                    @if(session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                            <p>{{ session('warning') }}</p>
                        </div>
                    @endif

                    @if($user->role === 'admin')
                        <!-- Admin Dashboard -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <!-- Card 1 - Total Revenue -->
                            <div class="bg-blue-50 p-4 rounded-lg shadow">
                                <div class="text-sm text-blue-800 mb-1">Total Revenue</div>
                                <div class="text-2xl font-bold text-blue-900">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
                            </div>

                            <!-- Card 2 - Target Revenue -->
                            <div class="bg-green-50 p-4 rounded-lg shadow">
                                <div class="text-sm text-green-800 mb-1">Target Revenue</div>
                                <div class="text-2xl font-bold text-green-900">Rp {{ number_format($totalTarget ?? 0, 0, ',', '.') }}</div>
                            </div>

                            <!-- Card 3 - Achievement -->
                            <div class="bg-purple-50 p-4 rounded-lg shadow">
                                <div class="text-sm text-purple-800 mb-1">Achievement</div>
                                <div class="text-2xl font-bold text-purple-900">{{ $achievementPercentage ?? 0 }}%</div>
                            </div>
                        </div>

                        <!-- Top Account Managers -->
                        <div class="bg-white p-4 rounded-lg shadow mb-6">
                            <h4 class="font-medium mb-4">Top 10 Account Managers</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topAMs ?? [] as $am)
                                            <tr>
                                                <td class="py-2 px-4 border-b border-gray-200">{{ $am->nama }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200 text-right">Rp {{ number_format($am->total_revenue, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="py-2 px-4 border-b border-gray-200 text-center text-gray-500">
                                                    Belum ada data
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Monthly Revenue Chart -->
                        <div class="bg-white p-4 rounded-lg shadow mb-6">
                            <h4 class="font-medium mb-4">Revenue Bulanan</h4>
                            <!-- Here you can add a chart -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Realisasi</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Achievement</th>
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
                                                    ? round(($revenue->realisasi / $revenue->target) * 100, 2)
                                                    : 0;

                                                $statusClass = $achievement >= 100
                                                    ? 'text-green-600'
                                                    : ($achievement >= 80 ? 'text-yellow-600' : 'text-red-600');
                                            @endphp
                                            <tr>
                                                <td class="py-2 px-4 border-b border-gray-200">{{ $months[$revenue->month] ?? 'Unknown' }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200 text-right">Rp {{ number_format($revenue->target, 0, ',', '.') }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200 text-right">Rp {{ number_format($revenue->realisasi, 0, ',', '.') }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200 text-right font-medium {{ $statusClass }}">
                                                    {{ $achievement }}%
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-2 px-4 border-b border-gray-200 text-center text-gray-500">
                                                    Belum ada data revenue
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @elseif($user->role === 'account_manager' && $accountManager)
                        <!-- Account Manager Dashboard -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <!-- Card 1 - Total Revenue -->
                            <div class="bg-blue-50 p-4 rounded-lg shadow">
                                <div class="text-sm text-blue-800 mb-1">Total Revenue</div>
                                <div class="text-2xl font-bold text-blue-900">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
                            </div>

                            <!-- Card 2 - Target Revenue -->
                            <div class="bg-green-50 p-4 rounded-lg shadow">
                                <div class="text-sm text-green-800 mb-1">Target Revenue</div>
                                <div class="text-2xl font-bold text-green-900">Rp {{ number_format($totalTarget ?? 0, 0, ',', '.') }}</div>
                            </div>

                            <!-- Card 3 - Achievement -->
                            <div class="bg-purple-50 p-4 rounded-lg shadow">
                                <div class="text-sm text-purple-800 mb-1">Achievement</div>
                                <div class="text-2xl font-bold text-purple-900">{{ $achievementPercentage ?? 0 }}%</div>
                            </div>
                        </div>

                        <!-- Account Manager & Divisi Info -->
                        <div class="bg-gray-50 p-4 rounded-lg shadow mb-6">
                            <h4 class="font-medium mb-2">Informasi Account Manager</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p><span class="font-medium">Nama:</span> {{ $accountManager->nama ?? 'Tidak tersedia' }}</p>
                                    <p><span class="font-medium">NIK:</span> {{ $accountManager->nik ?? 'Tidak tersedia' }}</p>
                                </div>
                                <div>
                                    <p><span class="font-medium">Divisi:</span> {{ $divisi->nama ?? 'Tidak tersedia' }}</p>
                                    <p><span class="font-medium">Witel:</span> {{ $witel->nama ?? 'Tidak tersedia' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Revenue Performance -->
                        <div class="bg-white p-4 rounded-lg shadow mb-6">
                            <h4 class="font-medium mb-4">Performa Revenue Bulanan</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Realisasi</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Achievement</th>
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
                                                    ? round(($revenue->realisasi / $revenue->target) * 100, 2)
                                                    : 0;

                                                $statusClass = $achievement >= 100
                                                    ? 'text-green-600'
                                                    : ($achievement >= 80 ? 'text-yellow-600' : 'text-red-600');
                                            @endphp
                                            <tr>
                                                <td class="py-2 px-4 border-b border-gray-200">{{ $months[$revenue->month] ?? 'Unknown' }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200 text-right">Rp {{ number_format($revenue->target, 0, ',', '.') }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200 text-right">Rp {{ number_format($revenue->realisasi, 0, ',', '.') }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200 text-right font-medium {{ $statusClass }}">
                                                    {{ $achievement }}%
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-2 px-4 border-b border-gray-200 text-center text-gray-500">
                                                    Belum ada data revenue
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <!-- Default Dashboard atau Account Manager tanpa data -->
                        <div class="bg-yellow-50 p-4 rounded-lg shadow mb-6">
                            <h4 class="font-medium mb-2">Informasi</h4>
                            <p>Anda belum memiliki data yang cukup untuk melihat dashboard performa.</p>
                            @if($user->role === 'account_manager')
                                <p class="mt-2">Akun Anda belum terhubung dengan data Account Manager. Silakan hubungi administrator.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
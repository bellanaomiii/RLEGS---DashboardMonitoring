@extends('layouts.main')

@section('title', 'Tabel Data Performa Account Manager')

@section('head')
    <!-- Menyertakan dashboard.css langsung di sini -->
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="container mx-auto p-4 mt-4">
    <h2 class="text-2xl font-semibold mb-4">Dashboard Revenue</h2>

    <!-- Snackbar untuk notifikasi -->
    <div id="snackbar" class="hidden"></div>

    <!-- Form Tambah Data Revenue -->
    <div class="card p-4 mb-4 border rounded-lg shadow-md">
        <h4 class="text-xl font-semibold mb-4">Tambah Revenue</h4>
        <form action="{{ route('revenue.store') }}" method="POST" id="revenueForm">
            @csrf

            <!-- Nama Account Manager -->
            <div class="mb-3">
                <label for="account_manager" class="block text-sm font-medium mb-1">Nama Account Manager</label>
                <input type="text" id="account_manager" class="form-control w-full px-4 py-2 border rounded-lg" placeholder="Cari Account Manager..." required>
                <input type="hidden" name="account_manager_id" id="account_manager_id">
                <div id="account_manager_suggestions" class="suggestions-container"></div>
                <p><a href="#" data-bs-toggle="modal" data-bs-target="#addAccountManagerModal" class="text-blue-600 hover:underline text-sm">Tambah Account Manager Baru</a></p>
            </div>

            <!-- Nama Corporate Customer -->
            <div class="mb-3">
                <label for="corporate_customer" class="block text-sm font-medium mb-1">Nama Corporate Customer</label>
                <input type="text" id="corporate_customer" class="form-control w-full px-4 py-2 border rounded-lg" placeholder="Cari Corporate Customer..." required>
                <input type="hidden" name="corporate_customer_id" id="corporate_customer_id">
                <div id="corporate_customer_suggestions" class="suggestions-container"></div>
                <p><a href="#" data-bs-toggle="modal" data-bs-target="#addCorporateCustomerModal" class="text-blue-600 hover:underline text-sm">Tambah Corporate Customer Baru</a></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Target Revenue -->
                <div class="mb-3">
                    <label for="target_revenue" class="block text-sm font-medium mb-1">Target Revenue</label>
                    <input type="number" class="form-control w-full px-4 py-2 border rounded-lg" name="target_revenue" id="target_revenue" placeholder="Masukkan target revenue" required>
                </div>

                <!-- Real Revenue -->
                <div class="mb-3">
                    <label for="real_revenue" class="block text-sm font-medium mb-1">Real Revenue</label>
                    <input type="number" class="form-control w-full px-4 py-2 border rounded-lg" name="real_revenue" id="real_revenue" placeholder="Masukkan real revenue" required>
                </div>
            </div>

            <!-- Bulan Capaian -->
            <div class="mb-4">
                <label for="month_year_picker" class="block text-sm font-medium mb-1">Bulan Capaian</label>
                <div class="relative">
                    <input type="text" id="month_year_picker" class="form-control w-full px-4 py-2 border rounded-lg" placeholder="Pilih Bulan dan Tahun" readonly>
                    <input type="hidden" name="bulan_month" id="bulan_month">
                    <input type="hidden" name="bulan_year" id="bulan_year">
                    <input type="hidden" name="bulan" id="bulan">
                    <div id="month_picker" class="month-picker">
                        <div class="month-picker-header">
                            <div class="year-selector">
                                <button type="button" id="prev_year">&lt;</button>
                                <span id="current_year">2023</span>
                                <button type="button" id="next_year">&gt;</button>
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

            <div class="flex space-x-2">
                <button type="submit" class="btn bg-[#1C2955] text-white hover:bg-blue-700 py-2 px-6 rounded-lg">Simpan</button>
                <button type="button" class="btn bg-green-600 text-white hover:bg-green-700 py-2 px-6 rounded-lg" data-bs-toggle="modal" data-bs-target="#importRevenueModal">Import Excel</button>
            </div>
        </form>
    </div>

    <!-- Raw Data RLEGS Telkom -->
    <div class="card p-4 mb-4 border rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4">Raw Data RLEGS Telkom</h3>

        <!-- Tab Menu untuk Tabel Data -->
        <div class="tab-menu-container">
            <ul class="tabs">
                <li class="tab-item active" data-tab="revenueTab">Revenue Data</li>
                <li class="tab-item" data-tab="amTab">Account Manager</li>
                <li class="tab-item" data-tab="ccTab">Corporate Customer</li>
            </ul>
        </div>

        <!-- Tab Content untuk Revenue -->
        <div id="revenueTab" class="tab-content active">
            @if($revenues->isEmpty())
                <p class="text-center text-gray-500">Tidak ada data revenue tersedia.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left bg-gray-100">Nama AM</th>
                                <th class="px-4 py-2 text-left bg-gray-100">Nama Customer</th>
                                <th class="px-4 py-2 text-left bg-gray-100">Target Revenue</th>
                                <th class="px-4 py-2 text-left bg-gray-100">Real Revenue</th>
                                <th class="px-4 py-2 text-left bg-gray-100">Bulan</th>
                                <th class="px-4 py-2 text-left bg-gray-100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($revenues as $revenue)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $revenue->accountManager->nama }}</td>
                                <td class="px-4 py-2">{{ $revenue->corporateCustomer->nama }}</td>
                                <td class="px-4 py-2">{{ number_format($revenue->target_revenue, 0, ',', '.') }}</td>
                                <td class="px-4 py-2">{{ number_format($revenue->real_revenue, 0, ',', '.') }}</td>
                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($revenue->bulan . '-01')->format('F Y') }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('revenue.edit', $revenue->id) }}" class="text-blue-600 hover:underline">Edit</a> |
                                    <form action="{{ route('revenue.destroy', $revenue->id) }}" method="POST" style="display:inline;" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $revenues->links() }}
                </div>
            @endif
        </div>

        <!-- Tab Content untuk Account Manager -->
        <div id="amTab" class="tab-content">
            @if($accountManagers->isEmpty())
                <p class="text-center text-gray-500">Tidak ada data Account Manager tersedia.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left bg-gray-100">Nama</th>
                                <th class="px-4 py-2 text-left bg-gray-100">NIK</th>
                                <th class="px-4 py-2 text-left bg-gray-100">Witel</th>
                                <th class="px-4 py-2 text-left bg-gray-100">Divisi</th>
                                <th class="px-4 py-2 text-left bg-gray-100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accountManagers as $am)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $am->nama }}</td>
                                <td class="px-4 py-2">{{ $am->nik }}</td>
                                <td class="px-4 py-2">{{ $am->witel->nama }}</td>
                                <td class="px-4 py-2">{{ $am->divisi->nama }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('account_manager.edit', $am->id) }}" class="text-blue-600 hover:underline">Edit</a> |
                                    <form action="{{ route('account_manager.destroy', $am->id) }}" method="POST" style="display:inline;" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $accountManagers->links() }}
                </div>
            @endif
        </div>

        <!-- Tab Content untuk Corporate Customer -->
        <div id="ccTab" class="tab-content">
            @if($corporateCustomers->isEmpty())
                <p class="text-center text-gray-500">Tidak ada data Corporate Customer tersedia.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left bg-gray-100">Nama</th>
                                <th class="px-4 py-2 text-left bg-gray-100">NIPNAS</th>
                                <th class="px-4 py-2 text-left bg-gray-100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($corporateCustomers as $cc)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $cc->nama }}</td>
                                <td class="px-4 py-2">{{ $cc->nipnas }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('corporate_customer.edit', $cc->id) }}" class="text-blue-600 hover:underline">Edit</a> |
                                    <form action="{{ route('corporate_customer.destroy', $cc->id) }}" method="POST" style="display:inline;" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $corporateCustomers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Tambah Account Manager -->
<div class="modal fade" id="addAccountManagerModal" tabindex="-1" aria-labelledby="addAccountManagerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addAccountManagerModalLabel">Tambah Account Manager Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Tab Menu di Modal -->
        <div class="tab-menu-container">
            <ul class="tabs">
                <li class="tab-item active" data-tab="formTabAM">Form Manual</li>
                <li class="tab-item" data-tab="importTabAM">Import Excel</li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div id="formTabAM" class="tab-content active">
            <form id="amForm" action="{{ route('account_manager.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="nama" class="block text-sm font-medium">Nama Account Manager</label>
                    <input type="text" id="nama" name="nama" class="form-control w-full px-4 py-2 border rounded-lg" placeholder="Masukkan Nama Account Manager" required>
                </div>
                <div class="mb-4">
                    <label for="nik" class="block text-sm font-medium">Nomor Induk Karyawan</label>
                    <input type="text" id="nik" name="nik" class="form-control w-full px-4 py-2 border rounded-lg" placeholder="Masukkan 5 digit Nomor Induk Karyawan" pattern="^\d{5}$" required>
                </div>
                <div class="mb-4">
                    <label for="witel_id" class="block text-sm font-medium">Witel</label>
                    <select name="witel_id" id="witel_id" class="form-control w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Pilih Witel</option>
                        @foreach($witels as $witel)
                            <option value="{{ $witel->id }}">{{ $witel->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="divisi_id" class="block text-sm font-medium">Divisi</label>
                    <select name="divisi_id" id="divisi_id" class="form-control w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Pilih Divisi</option>
                        @foreach($divisi as $div)
                            <option value="{{ $div->id }}">{{ $div->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn bg-[#1C2955] text-white hover:bg-blue-700 py-2 px-6 rounded-lg">Simpan</button>
            </form>
        </div>

        <!-- Tab untuk Import Excel -->
        <div id="importTabAM" class="tab-content">
            <form id="amImportForm" action="{{ route('account_manager.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label for="file_upload_am" class="block text-sm font-medium">Unggah File Excel</label>
                <input type="file" name="file" id="file_upload_am" accept=".xlsx, .xls, .csv" required class="w-full px-4 py-2 border rounded-lg">
                <button type="submit" class="btn bg-[#1C2955] text-white hover:bg-blue-700 py-2 px-6 rounded-lg mt-4">Unggah Data</button>
            </form>
            <a href="{{ route('account_manager.template') }}" class="text-blue-500 hover:underline mt-4 block">Unduh Template Excel</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Corporate Customer -->
<div class="modal fade" id="addCorporateCustomerModal" tabindex="-1" aria-labelledby="addCorporateCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCorporateCustomerModalLabel">Tambah Corporate Customer Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Tab Menu di Modal -->
        <div class="tab-menu-container">
            <ul class="tabs">
                <li class="tab-item active" data-tab="formTabCC">Form Manual</li>
                <li class="tab-item" data-tab="importTabCC">Import Excel</li>
            </ul>
        </div>

        <!-- Tab Content untuk Form Manual -->
        <div id="formTabCC" class="tab-content active">
            <form id="ccForm" action="{{ route('corporate_customer.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="nama_customer" class="block text-sm font-medium">Nama Corporate Customer</label>
                    <input type="text" name="nama" id="nama_customer" class="form-control w-full px-4 py-2 border rounded-lg" placeholder="Masukkan Nama Corporate Customer" required>
                </div>
                <div class="mb-4">
                    <label for="nipnas" class="block text-sm font-medium">NIPNAS</label>
                    <input type="number" name="nipnas" id="nipnas" class="form-control w-full px-4 py-2 border rounded-lg" placeholder="Masukkan NIPNAS (maksimal 7 digit)" max="9999999" required>
                </div>
                <button type="submit" class="btn bg-[#1C2955] text-white hover:bg-blue-700 py-2 px-6 rounded-lg">Simpan</button>
            </form>
        </div>

        <!-- Tab untuk Import Excel -->
        <div id="importTabCC" class="tab-content">
            <form id="ccImportForm" action="{{ route('corporate_customer.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label for="file_upload_cc" class="block text-sm font-medium">Unggah File Excel</label>
                <input type="file" name="file" id="file_upload_cc" accept=".xlsx, .xls, .csv" required class="w-full px-4 py-2 border rounded-lg">
                <button type="submit" class="btn bg-[#1C2955] text-white hover:bg-blue-700 py-2 px-6 rounded-lg mt-4">Unggah Data</button>
            </form>
            <a href="{{ route('corporate_customer.template') }}" class="text-blue-500 hover:underline mt-4 block">Unduh Template Excel</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Import Revenue -->
<div class="modal fade" id="importRevenueModal" tabindex="-1" aria-labelledby="importRevenueModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importRevenueModalLabel">Import Data Revenue</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="revenueImportForm" action="{{ route('revenue.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label for="file_upload_revenue" class="block text-sm font-medium">Unggah File Excel</label>
            <input type="file" name="file" id="file_upload_revenue" accept=".xlsx, .xls, .csv" required class="w-full px-4 py-2 border rounded-lg">
            <div class="mt-4">
                <p class="text-sm text-gray-600 mb-2">Catatan: Format Excel harus sesuai dengan template.</p>
                <ul class="text-sm text-gray-600 list-disc ml-5">
                    <li>Kolom: account_manager, corporate_customer, target_revenue, real_revenue, bulan</li>
                    <li>account_manager dan corporate_customer harus ada di database</li>
                    <li>bulan dalam format MM/YYYY (contoh: 01/2025 untuk Januari 2025)</li>
                </ul>
            </div>
            <button type="submit" class="btn bg-[#1C2955] text-white hover:bg-blue-700 py-2 px-6 rounded-lg mt-4">Unggah Data</button>
        </form>
        <a href="{{ route('revenue.template') }}" class="text-blue-500 hover:underline mt-4 block">Unduh Template Excel</a>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection

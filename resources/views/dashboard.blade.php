@extends('layouts.main')

@section('title', 'Tabel Data Performa Account Manager')

@section('head')
    <!-- Menyertakan dashboard.css langsung di sini -->
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-semibold mb-4">Dashboard Revenue</h2>

<!-- Form Tambah Data Revenue -->
<div class="card p-4 mb-4">
    <h4>Tambah Revenue</h4>
    <form action="{{ route('revenue.store') }}" method="POST">
        @csrf

        <!-- Nama Account Manager -->
        <div class="mb-3">
            <label for="account_manager">Nama Account Manager</label>
            <input type="text" id="account_manager" class="form-control w-full px-4 py-2 border rounded-lg" placeholder="Cari Account Manager..." required>
            <input type="hidden" name="account_manager_id" id="account_manager_id">
            <div id="account_manager_suggestions"></div>
            <p><a href="#" data-bs-toggle="modal" data-bs-target="#addAccountManagerModal">Tambah Account Manager Baru</a></p>
        </div>

        <!-- Nama Corporate Customer -->
        <div class="mb-3">
            <label for="corporate_customer">Nama Corporate Customer</label>
            <input type="text" id="corporate_customer" class="form-control w-full px-4 py-2 border rounded-lg" placeholder="Cari Corporate Customer..." required>
            <input type="hidden" name="corporate_customer_id" id="corporate_customer_id">
            <div id="corporate_customer_suggestions"></div>
            <p><a href="#" data-bs-toggle="modal" data-bs-target="#addCorporateCustomerModal">Tambah Corporate Customer Baru</a></p>
        </div>

        <!-- Target Revenue -->
        <div class="mb-3">
            <label for="target_revenue">Target Revenue</label>
            <input type="number" class="form-control w-full px-4 py-2 border rounded-lg" name="target_revenue" required>
        </div>

        <!-- Real Revenue -->
        <div class="mb-3">
            <label for="real_revenue">Real Revenue</label>
            <input type="number" class="form-control w-full px-4 py-2 border rounded-lg" name="real_revenue" required>
        </div>

        <!-- Bulan Capaian -->
<!-- Bulan Capaian -->
<div class="mb-3">
    <label for="bulan">Bulan Capaian</label>
    <input type="date" id="bulan" class="form-control w-full px-4 py-2 border rounded-lg" name="bulan" required>
</div>


        <button type="submit" class="btn bg-[#1C2955] text-white hover:bg-blue-700 py-2 px-6 rounded-lg">Simpan</button>
    </form>
</div>


    <!-- Tabel Revenue -->
    <div class="card p-4 mb-4 border rounded-lg shadow-md">
        <h4 class="text-xl font-semibold mb-4">Data Revenue</h4>
        @if($revenues->isEmpty())
            <p class="text-center text-gray-500">Tidak ada data revenue tersedia.</p>
        @else
            <table class="table-auto w-full border-collapse">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left">Nama AM</th>
                        <th class="px-4 py-2 text-left">Nama Customer</th>
                        <th class="px-4 py-2 text-left">Target Revenue</th>
                        <th class="px-4 py-2 text-left">Real Revenue</th>
                        <th class="px-4 py-2 text-left">Bulan</th>
                        <th class="px-4 py-2 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revenues as $revenue)
                    <tr>
                        <td class="border px-4 py-2">{{ $revenue->accountManager->nama }}</td>
                        <td class="border px-4 py-2">{{ $revenue->corporateCustomer->nama }}</td>
                        <td class="border px-4 py-2">{{ number_format($revenue->target_revenue, 0, ',', '.') }}</td>
                        <td class="border px-4 py-2">{{ number_format($revenue->real_revenue, 0, ',', '.') }}</td>
                        <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($revenue->bulan)->format('F Y') }}</td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('revenue.edit', $revenue->id) }}" class="text-blue-600 hover:underline">Edit</a> |
                            <form action="{{ route('revenue.destroy', $revenue->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
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
                <li class="tab-item active" data-tab="formTab">Form Manual</li>
                <li class="tab-item" data-tab="importTab">Import Excel</li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div id="formTab" class="tab-content active">
            <form action="{{ route('account_manager.store') }}" method="POST">
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
        <div id="importTab" class="tab-content">
            <form action="{{ route('account_manager.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label for="file_upload" class="block text-sm font-medium">Unggah File Excel</label>
                <input type="file" name="file" id="file_upload" accept=".xlsx, .xls, .csv" required class="w-full px-4 py-2 border rounded-lg">
                <button type="submit" class="btn bg-[#1C2955] text-white hover:bg-blue-700 py-2 px-6 rounded-lg mt-4">Unggah Data</button>
            </form>
            <a href="{{ asset('templates/Template_Account_Manager.xlsx') }}" class="text-blue-500 hover:underline mt-4 block">Unduh Template Excel</a>
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
        <form action="{{ route('corporate_customer.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="nama_customer">Nama Corporate Customer</label>
            <input type="text" name="nama" id="nama_customer" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection

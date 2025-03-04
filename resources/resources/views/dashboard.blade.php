@extends('layouts.main')

@section('content')
<div class="container">
    <h2 class="mb-4">Dashboard Revenue</h2>

    <!-- Form Tambah Data Revenue -->
    <div class="card p-4 mb-4">
        <h4>Tambah Revenue</h4>
        <form action="{{ route('revenue.store') }}" method="POST">
            @csrf

            <!-- Nama Account Manager -->
            <div class="mb-3">
                <label for="account_manager">Nama Account Manager</label>
                <input type="text" id="account_manager" class="form-control" placeholder="Cari Account Manager..." required>
                <input type="hidden" name="account_manager_id" id="account_manager_id">
                <div id="account_manager_suggestions"></div>
                <p><a href="{{ route('account_manager.create') }}">Tambah Account Manager Baru</a></p>
            </div>

            <!-- Nama Corporate Customer -->
            <div class="mb-3">
                <label for="corporate_customer">Nama Corporate Customer</label>
                <input type="text" id="corporate_customer" class="form-control" placeholder="Cari Corporate Customer..." required>
                <input type="hidden" name="corporate_customer_id" id="corporate_customer_id">
                <div id="corporate_customer_suggestions"></div>
                <p><a href="{{ route('corporate_customer.create') }}">Tambah Corporate Customer Baru</a></p>
            </div>

            <!-- Target Revenue -->
            <div class="mb-3">
                <label for="target_revenue">Target Revenue</label>
                <input type="number" class="form-control" name="target_revenue" required>
            </div>

            <!-- Real Revenue -->
            <div class="mb-3">
                <label for="real_revenue">Real Revenue</label>
                <input type="number" class="form-control" name="real_revenue" required>
            </div>

            <!-- Bulan Capaian -->
            <div class="mb-3">
                <label for="bulan">Bulan Capaian</label>
                <input type="month" class="form-control" name="bulan" required>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>

    <!-- Tabel Revenue -->
    <div class="card p-4">
        <h4>Data Revenue</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Nama AM</th>
                    <th>Nama Customer</th>
                    <th>Target Revenue</th>
                    <th>Real Revenue</th>
                    <th>Bulan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($revenues as $revenue)
                <tr>
                    <td>{{ $revenue->accountManager->nama }}</td>
                    <td>{{ $revenue->corporateCustomer->nama }}</td>
                    <td>{{ number_format($revenue->target_revenue, 0, ',', '.') }}</td>
                    <td>{{ number_format($revenue->real_revenue, 0, ',', '.') }}</td>
                    <td>{{ \Carbon\Carbon::parse($revenue->bulan)->format('F Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('account_manager').addEventListener('input', function() {
    fetch("{{ route('revenue.searchAccountManager') }}?search=" + this.value)
        .then(response => response.json())
        .then(data => {
            let suggestionBox = document.getElementById('account_manager_suggestions');
            suggestionBox.innerHTML = "";
            data.forEach(am => {
                let item = document.createElement('div');
                item.textContent = am.nama;
                item.onclick = function() {
                    document.getElementById('account_manager').value = am.nama;
                    document.getElementById('account_manager_id').value = am.id;
                    suggestionBox.innerHTML = "";
                };
                suggestionBox.appendChild(item);
            });
        });
});

document.getElementById('corporate_customer').addEventListener('input', function() {
    fetch("{{ route('revenue.searchCorporateCustomer') }}?search=" + this.value)
        .then(response => response.json())
        .then(data => {
            let suggestionBox = document.getElementById('corporate_customer_suggestions');
            suggestionBox.innerHTML = "";
            data.forEach(cust => {
                let item = document.createElement('div');
                item.textContent = cust.nama;
                item.onclick = function() {
                    document.getElementById('corporate_customer').value = cust.nama;
                    document.getElementById('corporate_customer_id').value = cust.id;
                    suggestionBox.innerHTML = "";
                };
                suggestionBox.appendChild(item);
            });
        });
});
</script>
@endsection

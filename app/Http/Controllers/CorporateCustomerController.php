<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorporateCustomer;

class CorporateCustomerController extends Controller
{
    // Menampilkan form untuk menambah Corporate Customer
    public function create()
    {
        return view('corporate_customer.create'); // Anda bisa menyesuaikan dengan modal jika ingin
    }

    // Menyimpan Corporate Customer baru
    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'nama' => 'required|string|unique:corporate_customers,nama'
        ]);

        // Membuat Corporate Customer baru
        CorporateCustomer::create([
            'nama' => $request->nama
        ]);

        // Mengembalikan response dengan status sukses
        return redirect()->route('dashboard')->with('success', 'Corporate Customer added successfully.');
    }
}


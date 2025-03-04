<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Revenue;
use App\Models\AccountManager;
use App\Models\CorporateCustomer;
use App\Models\Witel;
use App\Models\Divisi;

class RevenueController extends Controller
{
    // Menampilkan halaman dashboard dengan data Revenue, Witel, dan Divisi
    public function index()
    {
        $revenues = Revenue::with(['accountManager', 'corporateCustomer'])->get();
        $witels = Witel::all();
        $divisi = Divisi::all();
        return view('dashboard', compact('revenues', 'witels', 'divisi'));
    }

    // Menyimpan data revenue baru
    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'account_manager_id' => 'required|exists:account_managers,id',
            'corporate_customer_id' => 'required|exists:corporate_customers,id',
            'target_revenue' => 'required|numeric',
            'real_revenue' => 'required|numeric',
            'bulan' => 'required|date_format:Y-m-d', // Memastikan format tanggal
        ]);

        // Konversi bulan dan tahun dari format Y-m-d ke Y-m
        $bulan = \Carbon\Carbon::parse($request->bulan)->format('Y-m');

        // Menyimpan data revenue
        Revenue::create([
            'account_manager_id' => $request->account_manager_id,
            'corporate_customer_id' => $request->corporate_customer_id,
            'target_revenue' => $request->target_revenue,
            'real_revenue' => $request->real_revenue,
            'bulan' => $bulan, // Menyimpan hanya bulan dan tahun
        ]);

        return redirect()->route('dashboard')->with('success', 'Revenue berhasil ditambahkan.');
    }

    // Fungsi pencarian Account Manager
    public function searchAccountManager(Request $request)
    {
        $search = $request->input('search');
        $accountManagers = AccountManager::where('nama', 'LIKE', "%{$search}%")->get();
        return response()->json($accountManagers);
    }

    // Fungsi pencarian Corporate Customer
    public function searchCorporateCustomer(Request $request)
    {
        $search = $request->input('search');
        $corporateCustomers = CorporateCustomer::where('nama', 'LIKE', "%{$search}%")->get();
        return response()->json($corporateCustomers);
    }
}

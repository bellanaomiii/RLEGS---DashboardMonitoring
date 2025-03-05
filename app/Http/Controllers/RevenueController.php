<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Revenue;
use App\Models\AccountManager;
use App\Models\CorporateCustomer;
use App\Models\Witel;
use App\Models\Divisi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    // Menampilkan halaman dashboard dengan data Revenue, Witel, dan Divisi
    public function index()
    {
        $revenues = Revenue::with(['accountManager', 'corporateCustomer'])->orderBy('bulan', 'desc')->paginate(10);
        $accountManagers = AccountManager::with(['witel', 'divisi'])->paginate(10);
        $corporateCustomers = CorporateCustomer::paginate(10);
        $witels = Witel::all();
        $divisi = Divisi::all();

        return view('dashboard', compact('revenues', 'accountManagers', 'corporateCustomers', 'witels', 'divisi'));
    }

    // Menyimpan data revenue baru
    public function store(Request $request)
    {
        // Validasi data input
        $validator = Validator::make($request->all(), [
            'account_manager_id' => 'required|exists:account_managers,id',
            'corporate_customer_id' => 'required|exists:corporate_customers,id',
            'target_revenue' => 'required|numeric',
            'real_revenue' => 'required|numeric',
            'bulan_month' => 'required|string|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'bulan_year' => 'required|numeric|min:2000|max:2100',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Gabungkan bulan dan tahun menjadi format Y-m dengan tambahan tanggal 01
        $bulan = $request->bulan_year . '-' . $request->bulan_month;

        try {
            // Cek apakah data sudah ada
            $existingRevenue = Revenue::where('account_manager_id', $request->account_manager_id)
                ->where('corporate_customer_id', $request->corporate_customer_id)
                ->where('bulan', $bulan)
                ->first();

            if ($existingRevenue) {
                // Update data yang sudah ada
                $existingRevenue->update([
                    'target_revenue' => $request->target_revenue,
                    'real_revenue' => $request->real_revenue
                ]);

                $message = 'Data Revenue berhasil diperbarui.';
            } else {
                // Buat data baru dengan menggunakan DB::raw untuk date format
                Revenue::create([
                    'account_manager_id' => $request->account_manager_id,
                    'corporate_customer_id' => $request->corporate_customer_id,
                    'target_revenue' => $request->target_revenue,
                    'real_revenue' => $request->real_revenue,
                    'bulan' => DB::raw("DATE_FORMAT('{$bulan}-01', '%Y-%m')") // Ensure DB treats it as a date format
                ]);

                $message = 'Data Revenue berhasil ditambahkan.';
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->route('dashboard')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan revenue: ' . $e->getMessage(), [
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'target_revenue' => $request->target_revenue,
                'real_revenue' => $request->real_revenue,
                'bulan' => $bulan
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan revenue: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('dashboard')->with('error', 'Gagal menyimpan revenue: ' . $e->getMessage());
        }
    }

    // Edit data revenue
    public function edit($id)
    {
        $revenue = Revenue::findOrFail($id);
        $accountManagers = AccountManager::all();
        $corporateCustomers = CorporateCustomer::all();
        $witels = Witel::all();
        $divisi = Divisi::all();

        // Parse bulan untuk tampilan form
        $bulanParts = explode('-', $revenue->bulan);
        $year = $bulanParts[0];
        $month = $bulanParts[1];

        return view('revenue.edit', compact('revenue', 'accountManagers', 'corporateCustomers', 'witels', 'divisi', 'year', 'month'));
    }

    // Update data revenue
    public function update(Request $request, $id)
    {
        // Validasi data input
        $validator = Validator::make($request->all(), [
            'account_manager_id' => 'required|exists:account_managers,id',
            'corporate_customer_id' => 'required|exists:corporate_customers,id',
            'target_revenue' => 'required|numeric',
            'real_revenue' => 'required|numeric',
            'bulan_month' => 'required|string|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'bulan_year' => 'required|numeric|min:2000|max:2100',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Gabungkan bulan dan tahun menjadi format Y-m
        $bulan = $request->bulan_year . '-' . $request->bulan_month;

        try {
            $revenue = Revenue::findOrFail($id);

            // Update data revenue dengan DB::raw untuk memastikan format tanggal
            $revenue->update([
                'account_manager_id' => $request->account_manager_id,
                'corporate_customer_id' => $request->corporate_customer_id,
                'target_revenue' => $request->target_revenue,
                'real_revenue' => $request->real_revenue,
                'bulan' => DB::raw("DATE_FORMAT('{$bulan}-01', '%Y-%m')") // Ensure DB treats it as a date format
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Revenue berhasil diperbarui.'
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Revenue berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui revenue: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui revenue: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('dashboard')->with('error', 'Gagal memperbarui revenue: ' . $e->getMessage());
        }
    }

    // Hapus data revenue
    public function destroy($id)
    {
        try {
            $revenue = Revenue::findOrFail($id);
            $revenue->delete();

            return redirect()->route('dashboard')->with('success', 'Revenue berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Gagal menghapus revenue: ' . $e->getMessage());
        }
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

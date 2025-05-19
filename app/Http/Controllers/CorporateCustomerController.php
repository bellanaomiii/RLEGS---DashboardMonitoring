<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorporateCustomer;
use App\Models\Witel;
use App\Models\Divisi;
use App\Models\AccountManager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CorporateCustomerController extends Controller
{
    // Menampilkan form untuk menambah Corporate Customer
    public function create()
    {
        $corporateCustomers = CorporateCustomer::paginate(10);
        $accountManagers = AccountManager::with(['witel', 'divisi'])->paginate(10);
        $witels = Witel::all();
        $divisi = Divisi::all();

        return view('dashboard', compact('corporateCustomers', 'accountManagers', 'witels', 'divisi'));
    }

    // Menyimpan Corporate Customer baru
    public function store(Request $request)
    {
        // Validasi data input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|unique:corporate_customers,nama',
            'nipnas' => 'required|numeric|max:9999999'
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

        try {
            // Membuat Corporate Customer baru
            CorporateCustomer::create([
                'nama' => $request->nama,
                'nipnas' => $request->nipnas
            ]);

            // Return JSON response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Corporate Customer berhasil ditambahkan!'
                ]);
            }

            // Mengembalikan response dengan status sukses
            return redirect()->route('dashboard')->with('success', 'Corporate Customer berhasil ditambahkan!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan Corporate Customer: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menambahkan Corporate Customer: ' . $e->getMessage())->withInput();
        }
    }

    // Edit Corporate Customer
    public function edit($id)
    {
        $corporateCustomer = CorporateCustomer::findOrFail($id);

        return view('corporate_customer.edit', compact('corporateCustomer'));
    }

    // Update Corporate Customer
    public function update(Request $request, $id)
    {
        $corporateCustomer = CorporateCustomer::findOrFail($id);

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|unique:corporate_customers,nama,' . $id,
            'nipnas' => 'required|numeric|max:9999999'
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

        try {
            $corporateCustomer->update([
                'nama' => $request->nama,
                'nipnas' => $request->nipnas
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Corporate Customer berhasil diperbarui!'
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Corporate Customer berhasil diperbarui!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui Corporate Customer: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal memperbarui Corporate Customer: ' . $e->getMessage())->withInput();
        }
    }

    // Hapus Corporate Customer
    public function destroy($id)
    {
        try {
            $corporateCustomer = CorporateCustomer::findOrFail($id);
            $corporateCustomer->delete();

            return redirect()->route('dashboard')->with('success', 'Corporate Customer berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Gagal menghapus Corporate Customer: ' . $e->getMessage());
        }
    }

    // Fungsi pencarian Corporate Customer untuk autocomplete
    public function search(Request $request)
    {
        $search = $request->get('search');
        $corporateCustomers = CorporateCustomer::where('nama', 'like', "%{$search}%")
                           ->orWhere('nipnas', 'like', "%{$search}%")
                           ->limit(10)
                           ->get();

        return response()->json($corporateCustomers);
    }
    
    /**
     * Mendapatkan data Corporate Customer untuk edit via AJAX
     */
    public function getCorporateCustomerData($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses data ini.'
            ], 403);
        }

        try {
            // Ambil data corporate customer
            $corporateCustomer = CorporateCustomer::findOrFail($id);
            
            // Format data untuk response
            $data = [
                'id' => $corporateCustomer->id,
                'nama' => $corporateCustomer->nama,
                'nipnas' => $corporateCustomer->nipnas
            ];
            
            Log::info('Corporate Customer data fetched for edit:', [
                'id' => $id,
                'nama' => $corporateCustomer->nama
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Corporate Customer data: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data Corporate Customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Corporate Customer via AJAX
     */
    public function updateCorporateCustomer(Request $request, $id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui data ini.'
            ], 403);
        }

        try {
            $corporateCustomer = CorporateCustomer::findOrFail($id);

            // Validasi data input
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|unique:corporate_customers,nama,' . $id,
                'nipnas' => 'required|numeric|max:9999999'
            ]);

            if ($validator->fails()) {
                Log::warning('Corporate Customer update validation failed via AJAX:', $validator->errors()->toArray());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update corporate customer
            $corporateCustomer->update([
                'nama' => $request->nama,
                'nipnas' => $request->nipnas
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Corporate Customer berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating Corporate Customer via AJAX: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Corporate Customer: ' . $e->getMessage()
            ], 500);
        }
    }
}
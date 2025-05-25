<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountManager;
use App\Models\Witel;
use App\Models\Divisi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AccountManagerController extends Controller
{
    // Menampilkan halaman data Account Manager
    public function index()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $accountManagers = AccountManager::with(['witel', 'divisi'])->paginate(10);
        $witels = Witel::all();
        $divisi = Divisi::all();

        return view('account_manager.index', compact('accountManagers', 'witels', 'divisi'));
    }

    // Menampilkan form untuk menambah Account Manager dan mengirim data Witel dan Divisi
    public function create()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Ambil data Witel dan Divisi untuk dikirim ke view
        $witels = Witel::all(); // Mengambil semua data Witel
        $divisi = Divisi::all(); // Mengambil semua data Divisi
        $accountManagers = AccountManager::with(['witel', 'divisi'])->paginate(10);
        $corporateCustomers = collect([]); // Empty collection to avoid undefined variable

        // Kirim data Witel dan Divisi ke view
        return view('dashboard', compact('witels', 'divisi', 'accountManagers', 'corporateCustomers'));
    }

    // Menyimpan Account Manager baru
    public function store(Request $request)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk menambahkan Account Manager.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menambahkan Account Manager.');
        }

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|unique:account_managers,nama',
            'nik' => 'required|digits:5|unique:account_managers,nik', // Validasi NIK 5 digit
            'witel_id' => 'required|exists:witel,id', // Pastikan witel_id ada
            'divisi_id' => 'required|exists:divisi,id', // Pastikan divisi_id ada
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
            // Membuat Account Manager baru
            AccountManager::create([
                'nama' => $request->nama,
                'nik' => $request->nik,
                'witel_id' => $request->witel_id,  // Menambahkan witel_id
                'divisi_id' => $request->divisi_id, // Menambahkan divisi_id
            ]);

            // Return JSON response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account Manager berhasil ditambahkan!'
                ]);
            }

            // Mengembalikan response dengan status sukses
            return redirect()->route('dashboard')->with('success', 'Account Manager berhasil ditambahkan!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan Account Manager: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menambahkan Account Manager: ' . $e->getMessage())->withInput();
        }
    }

    // Edit Account Manager
    public function edit($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengedit Account Manager.');
        }

        $accountManager = AccountManager::findOrFail($id);
        $witels = Witel::all();
        $divisi = Divisi::all();

        return view('account_manager.edit', compact('accountManager', 'witels', 'divisi'));
    }

    // Update Account Manager
    public function update(Request $request, $id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui Account Manager.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui Account Manager.');
        }

        $accountManager = AccountManager::findOrFail($id);

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|unique:account_managers,nama,' . $id,
            'nik' => 'required|digits:5|unique:account_managers,nik,' . $id,
            'witel_id' => 'required|exists:witel,id',
            'divisi_id' => 'required|exists:divisi,id',
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
            $accountManager->update([
                'nama' => $request->nama,
                'nik' => $request->nik,
                'witel_id' => $request->witel_id,
                'divisi_id' => $request->divisi_id,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account Manager berhasil diperbarui!'
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Account Manager berhasil diperbarui!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui Account Manager: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal memperbarui Account Manager: ' . $e->getMessage())->withInput();
        }
    }

    // Hapus Account Manager
    public function destroy($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menghapus Account Manager.');
        }

        try {
            $accountManager = AccountManager::findOrFail($id);
            $accountManager->delete();

            return redirect()->route('dashboard')->with('success', 'Account Manager berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Gagal menghapus Account Manager: ' . $e->getMessage());
        }
    }

    // Fungsi pencarian Account Manager untuk autocomplete
    public function search(Request $request)
    {
        $search = $request->get('search');
        $accountManagers = AccountManager::where('nama', 'like', "%{$search}%")
                           ->orWhere('nik', 'like', "%{$search}%")
                           ->limit(10)
                           ->get();

        return response()->json($accountManagers);
    }
}
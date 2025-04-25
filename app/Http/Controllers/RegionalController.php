<?php

namespace App\Http\Controllers;

use App\Models\Regional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegionalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $regionals = Regional::orderBy('nama')->paginate(10);
        return view('regionals.index', compact('regionals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return view('regionals.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk menambahkan Regional.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menambahkan Regional.');
        }

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|unique:regional,nama',
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
            // Buat regional baru
            Regional::create([
                'nama' => $request->nama,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Regional berhasil ditambahkan!'
                ]);
            }

            return redirect()->route('regional.index')->with('success', 'Regional berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error creating Regional: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan Regional: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menambahkan Regional: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Regional $regional)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengedit Regional.');
        }

        return view('regionals.edit', compact('regional'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Regional $regional)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui Regional.'
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk memperbarui Regional.');
        }

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|unique:regional,nama,' . $regional->id,
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
            // Update regional
            $regional->update([
                'nama' => $request->nama,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Regional berhasil diperbarui!'
                ]);
            }

            return redirect()->route('regional.index')->with('success', 'Regional berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating Regional: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui Regional: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal memperbarui Regional: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Regional $regional)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk menghapus Regional.');
        }

        try {
            // Check if the regional is in use
            if ($regional->accountManagers()->count() > 0) {
                return redirect()->route('regional.index')->with('error', 'Regional tidak dapat dihapus karena sedang digunakan oleh Account Manager.');
            }

            // Delete regional
            $regional->delete();

            return redirect()->route('regional.index')->with('success', 'Regional berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting Regional: ' . $e->getMessage());
            return redirect()->route('regional.index')->with('error', 'Gagal menghapus Regional: ' . $e->getMessage());
        }
    }

    /**
     * Get all regionals for API.
     */
    public function getRegionals()
    {
        try {
            $regionals = Regional::select('id', 'nama')->orderBy('nama')->get();
            return response()->json([
                'success' => true,
                'data' => $regionals
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting Regionals: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data Regional: ' . $e->getMessage()
            ], 500);
        }
    }
}
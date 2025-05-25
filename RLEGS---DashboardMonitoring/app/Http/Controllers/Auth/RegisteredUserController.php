<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AccountManager;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Mengambil data Account Manager untuk dropdown/autocomplete
        $accountManagers = AccountManager::select('id', 'nama', 'nik')->get();

        // Periksa jika tidak ada Account Manager, tampilkan pesan
        $noAccountManagers = $accountManagers->isEmpty();

        return view('auth.register', compact('accountManagers', 'noAccountManagers'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi dasar untuk semua role
        $commonRules = [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'role' => ['required', 'string', 'in:admin,account_manager']
        ];

        // Validasi khusus untuk admin
        if ($request->role === 'admin') {
            $rules = array_merge($commonRules, [
                'name' => ['required', 'string', 'max:255'],
                'admin_code' => ['required', 'string']
            ]);
        }
        // Validasi khusus untuk account manager
        else {
            // Periksa apakah ada account manager di database
            $accountManagersExist = AccountManager::count() > 0;

            if ($accountManagersExist) {
                $rules = array_merge($commonRules, [
                    'account_manager_id' => ['required', 'exists:account_managers,id']
                ]);
            } else {
                return back()->withErrors([
                    'account_manager_id' => 'Belum ada data Account Manager. Silakan daftar sebagai Admin terlebih dahulu atau hubungi administrator untuk menambahkan data Account Manager.'
                ])->withInput();
            }
        }

        $validator = Validator::make($request->all(), $rules);

        // Validasi khusus untuk kode admin
        if ($request->role === 'admin') {
            $validator->after(function ($validator) use ($request) {
                if ($request->admin_code !== '123456') {
                    $validator->errors()->add('admin_code', 'Kode admin tidak valid.');
                }
            });
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Proses untuk admin
        if ($request->role === 'admin') {
            $name = $request->name;
            $accountManagerId = null;
        }
        // Proses untuk account manager
        else {
            // Mendapatkan data AM untuk nama
            $accountManager = AccountManager::find($request->account_manager_id);
            if (!$accountManager) {
                return back()->withErrors(['account_manager_id' => 'Account Manager tidak ditemukan.'])->withInput();
            }
            $name = $accountManager->nama;
            $accountManagerId = $request->account_manager_id;
        }

        // Upload profile image jika ada
        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile-images', 'public');
        }

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'account_manager_id' => $accountManagerId,
            'profile_image' => $profileImagePath,
            'admin_code' => $request->role === 'admin' ? $request->admin_code : null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Search for account managers (for AJAX requests)
     */
    public function searchAccountManagers(Request $request)
    {
        $search = $request->input('search', '');

        $accountManagers = AccountManager::where('nama', 'LIKE', "%{$search}%")
            ->orWhere('nik', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['id', 'nama', 'nik']);

        return response()->json($accountManagers);
    }
}
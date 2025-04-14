<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AccountManager;
use App\Models\Witel;
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

        // Mengambil data Witel untuk dropdown
        $witels = Witel::select('id', 'nama')->get();

        // Periksa jika tidak ada Account Manager/Witel, tampilkan pesan
        $noAccountManagers = $accountManagers->isEmpty();
        $noWitels = $witels->isEmpty();

        return view('auth.register', compact('accountManagers', 'witels', 'noAccountManagers', 'noWitels'));
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
            'role' => ['required', 'string', 'in:admin,account_manager,witel']
        ];

        // Validasi khusus berdasarkan role
        $roleSpecificRules = [];

        if ($request->role === 'admin') {
            $roleSpecificRules = [
                'name' => ['required', 'string', 'max:255'],
                'admin_code' => ['required', 'string']
            ];
        } elseif ($request->role === 'account_manager') {
            // Periksa apakah ada account manager di database
            $accountManagersExist = AccountManager::count() > 0;

            if ($accountManagersExist) {
                $roleSpecificRules = [
                    'account_manager_id' => ['required', 'exists:account_managers,id']
                ];
            } else {
                return back()->withErrors([
                    'account_manager_id' => 'Belum ada data Account Manager. Silakan hubungi administrator untuk menambahkan Anda dalam data Account Manager.'
                ])->withInput();
            }
        } elseif ($request->role === 'witel') {
            // Periksa apakah ada witel di database
            $witelsExist = Witel::count() > 0;

            if ($witelsExist) {
                $roleSpecificRules = [
                    'witel_id' => ['required', 'exists:witel,id']
                ];
            } else {
                return back()->withErrors([
                    'witel_id' => 'Belum ada data Witel. Silakan hubungi administrator untuk menambahkan data Witel.'
                ])->withInput();
            }
        }

        $rules = array_merge($commonRules, $roleSpecificRules);
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

        // Set data berdasarkan role
        $userData = [
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'account_manager_id' => null,
            'witel_id' => null,
            'admin_code' => null,
        ];

        // Proses untuk admin
        if ($request->role === 'admin') {
            $userData['name'] = $request->name;
            $userData['admin_code'] = $request->admin_code;
        }
        // Proses untuk account manager
        elseif ($request->role === 'account_manager') {
            $accountManager = AccountManager::find($request->account_manager_id);
            if (!$accountManager) {
                return back()->withErrors(['account_manager_id' => 'Account Manager tidak ditemukan.'])->withInput();
            }
            $userData['name'] = $accountManager->nama;
            $userData['account_manager_id'] = $request->account_manager_id;
        }
        // Proses untuk witel
        elseif ($request->role === 'witel') {
            $witel = Witel::find($request->witel_id);
            if (!$witel) {
                return back()->withErrors(['witel_id' => 'Witel tidak ditemukan.'])->withInput();
            }
            $userData['name'] = "Support Witel " . $witel->nama;
            $userData['witel_id'] = $request->witel_id;
        }

        // Upload profile image jika ada
        if ($request->hasFile('profile_image')) {
            $userData['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        $user = User::create($userData);

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
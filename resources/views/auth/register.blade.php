<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RLEGS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 pb-10 bg-white">
        <div class="mt-6">
            <img src="{{ asset('img/logo-telkom.png') }}" alt="Telkom Indonesia" class="w-40">
        </div>

        <div class="w-full sm:max-w-md mt-6 mb-10 px-8 py-6 bg-white shadow-md overflow-hidden sm:rounded-lg border border-gray-200">
            <h2 class="text-center text-xl font-bold mb-5">Dashboard Monitoring RLEGS</h2>

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf

                <!-- Role Selection Dropdown -->
                <div class="mb-4">
                    <label for="role" class="block font-medium text-sm text-gray-700 mb-1">Pilih Jenis Akun</label>
                    <select id="role" name="role" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="account_manager">Account Manager</option>
                        <option value="witel">Support Witel</option>
                        <option value="admin">Admin</option>
                    </select>
                    @error('role')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Account Manager Fields -->
                <div id="account_manager_fields">
                    <div class="mb-4">
                        <label for="account_manager_search" class="block font-medium text-sm text-gray-700 mb-1">Nama Account Manager</label>
                        <div class="relative">
                            <input id="account_manager_search" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" placeholder="Cari Account Manager..." required>
                            <div id="account_manager_suggestions" class="absolute z-10 w-full bg-white shadow-md rounded-lg mt-1 hidden">
                                <!-- Hasil pencarian akan ditampilkan di sini -->
                            </div>
                        </div>
                        <input type="hidden" id="account_manager_id" name="account_manager_id" required>
                        @error('account_manager_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Witel Fields -->
                <div id="witel_fields" class="hidden">
                    <div class="mb-4">
                        <label for="witel_id" class="block font-medium text-sm text-gray-700 mb-1">Pilih Witel</label>
                        <select id="witel_id" name="witel_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Pilih Witel</option>
                            @foreach($witels as $witel)
                                <option value="{{ $witel->id }}">{{ $witel->nama }}</option>
                            @endforeach
                        </select>
                        @error('witel_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Admin Fields -->
                <div id="admin_fields" class="hidden">
                    <div class="mb-4">
                        <label for="admin_name" class="block font-medium text-sm text-gray-700 mb-1">Nama Admin</label>
                        <input id="admin_name" name="name" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" placeholder="Masukkan nama admin">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="admin_code" class="block font-medium text-sm text-gray-700 mb-1">Kode Admin</label>
                        <div class="relative">
                            <input id="admin_code" name="admin_code" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pr-10" type="password" placeholder="Masukkan kode admin">
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center" data-target="admin_code">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Masukkan kode admin untuk verifikasi.</p>
                        @error('admin_code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block font-medium text-sm text-gray-700 mb-1">Email</label>
                    <input id="email" name="email" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="email" placeholder="Masukkan email" value="{{ old('email') }}" required>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password and Confirmation -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="password" class="block font-medium text-sm text-gray-700 mb-1">Kata Sandi</label>
                        <div class="relative">
                            <input id="password" name="password" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pr-10" type="password" placeholder="Masukkan kata sandi" required>
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center" data-target="password">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="block font-medium text-sm text-gray-700 mb-1">Konfirmasi Kata Sandi</label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pr-10" type="password" placeholder="Konfirmasi kata sandi" required>
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center" data-target="password_confirmation">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Foto Profil -->
                <div class="mb-6">
                    <label for="profile_image" class="block font-medium text-sm text-gray-700 mb-1">Foto Profil</label>
                    <div class="mt-1 flex items-center">
                        <label for="profile_image" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Pilih Foto
                        </label>
                        <span id="file-name" class="ml-3 text-sm text-gray-500">Belum ada file yang dipilih</span>
                        <input id="profile_image" class="hidden" type="file" name="profile_image" accept="image/*">
                    </div>
                    @error('profile_image')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Login Link and Submit Button -->
                <div class="flex items-center justify-between mt-6">
                    <div>
                        <p class="text-sm text-gray-600">Sudah punya akun?</p>
                        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">Login Sekarang</a>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-[#0e223e] text-white rounded-md hover:bg-[#1e3c72] transition-colors">
                        Daftar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePasswordButtons = document.querySelectorAll('.toggle-password');
            togglePasswordButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('svg');

                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        // Change to eye-slash icon
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
                    } else {
                        passwordInput.type = 'password';
                        // Change back to eye icon
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
                    }
                });
            });

            // File upload preview
            const profileImageInput = document.getElementById('profile_image');
            const fileNameSpan = document.getElementById('file-name');

            profileImageInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    fileNameSpan.textContent = this.files[0].name;
                } else {
                    fileNameSpan.textContent = 'Belum ada file yang dipilih';
                }
            });

            // Role selection handling
            const roleSelect = document.getElementById('role');
            const accountManagerFields = document.getElementById('account_manager_fields');
            const witelFields = document.getElementById('witel_fields');
            const adminFields = document.getElementById('admin_fields');

            roleSelect.addEventListener('change', function() {
                // Hide all role-specific fields
                accountManagerFields.classList.add('hidden');
                witelFields.classList.add('hidden');
                adminFields.classList.add('hidden');

                // Reset required attributes
                document.getElementById('account_manager_search').required = false;
                document.getElementById('account_manager_id').required = false;

                if (document.getElementById('witel_id')) {
                    document.getElementById('witel_id').required = false;
                }

                if (document.getElementById('admin_name')) {
                    document.getElementById('admin_name').required = false;
                }

                if (document.getElementById('admin_code')) {
                    document.getElementById('admin_code').required = false;
                }

                // Show fields based on selected role
                if (this.value === 'account_manager') {
                    accountManagerFields.classList.remove('hidden');
                    document.getElementById('account_manager_search').required = true;
                    document.getElementById('account_manager_id').required = true;
                } else if (this.value === 'witel') {
                    witelFields.classList.remove('hidden');
                    document.getElementById('witel_id').required = true;
                } else if (this.value === 'admin') {
                    adminFields.classList.remove('hidden');
                    document.getElementById('admin_name').required = true;
                    document.getElementById('admin_code').required = true;
                }
            });

            // Account Manager Search
            const searchInput = document.getElementById('account_manager_search');
            const suggestionsContainer = document.getElementById('account_manager_suggestions');
            const idInput = document.getElementById('account_manager_id');

            let debounceTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);

                const query = this.value.trim();

                if (query.length < 3) {
                    suggestionsContainer.classList.add('hidden');
                    return;
                }

                debounceTimer = setTimeout(function() {
                    // Show loading indicator
                    suggestionsContainer.innerHTML = '<div class="p-2 text-center text-gray-500"><svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="mt-1 block">Mencari...</span></div>';
                    suggestionsContainer.classList.remove('hidden');

                    fetch(`/search-account-managers?search=${query}`)
                        .then(response => response.json())
                        .then(data => {
                            suggestionsContainer.innerHTML = '';

                            if (data.length === 0) {
                                suggestionsContainer.innerHTML = '<div class="p-2 text-center text-gray-500">Tidak ditemukan</div>';
                                return;
                            }

                            data.forEach(function(am) {
                                const div = document.createElement('div');
                                div.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                                div.textContent = `${am.nama} (${am.nik})`;
                                div.addEventListener('click', function() {
                                    searchInput.value = am.nama;
                                    idInput.value = am.id;
                                    suggestionsContainer.classList.add('hidden');
                                });
                                suggestionsContainer.appendChild(div);
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            suggestionsContainer.innerHTML = '<div class="p-2 text-center text-gray-500">Terjadi kesalahan</div>';
                        });
                }, 300);
            });

            // Hide suggestions when clicked outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                    suggestionsContainer.classList.add('hidden');
                }
            });

            // Initialize with default role selection
            roleSelect.dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>
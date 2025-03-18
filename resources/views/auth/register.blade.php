<x-guest-layout>


        <h1 class="text-2xl font-bold text-center text-black-800 mb-6">Dashboard Monitoring RLEGS</h1>

        <div class="mb-6">
            <div class="flex border-b">
                <button id="tab-account-manager" class="w-1/2 px-4 py-2 font-medium text-sm border-b-2 border-[#133057] text-[#133057] bg-white">Account Manager</button>
                <button id="tab-admin" class="w-1/2 px-4 py-2 font-medium text-sm text-gray-500 hover:text-gray-700 bg-white">Admin</button>
            </div>
        </div>

        <!-- Form Account Manager -->
        <div id="form-account-manager">
            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="role" value="account_manager">

                <!-- Account Manager Selection -->
                <div>
                    <x-input-label for="account_manager_search" :value="__('Nama Account Manager')" />
                    <div class="relative">
                        <x-text-input id="account_manager_search" class="block mt-1 w-full" type="text" placeholder="Cari Account Manager..." required />
                        <div id="account_manager_results" class="absolute z-10 w-full bg-white shadow-md rounded-lg mt-1 hidden">
                            <!-- Hasil pencarian akan ditampilkan di sini -->
                        </div>
                    </div>
                    <input type="hidden" id="account_manager_id" name="account_manager_id" required />
                    <x-input-error :messages="$errors->get('account_manager_id')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div class="mt-4">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Kata Sandi')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- Profile Image -->
                <div class="mt-4">
                    <x-input-label for="profile_image" :value="__('Foto Profil')" />
                    <div class="mt-1 flex items-center">
                        <label for="profile_image" class="flex items-center justify-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-200 focus:outline-none cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Pilih Foto
                        </label>
                        <span id="file-name-am" class="ml-3 text-sm text-gray-500">Belum ada file yang dipilih</span>
                        <input id="profile_image" class="hidden" type="file" name="profile_image" accept="image/*" />
                    </div>
                    <x-input-error :messages="$errors->get('profile_image')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between mt-8">
                    <div class="flex flex-col">
                        <span class="text-sm text-gray-600">Sudah punya akun?</span>
                        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">Login Sekarang</a>
                    </div>

                    <x-primary-button class="ms-3 bg-[#133057] hover:bg-[#0e223e] text-white">
                        {{ __('Daftar') }}
                    </x-primary-button>
                </div>
            </form>
        </div>

        <!-- Form Admin -->
        <div id="form-admin" class="hidden">
            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="role" value="admin">

                <!-- Admin Name -->
                <div>
                    <x-input-label for="admin_name" :value="__('Nama Admin')" />
                    <x-text-input id="admin_name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div class="mt-4">
                    <x-input-label for="admin_email" :value="__('Email')" />
                    <x-text-input id="admin_email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="admin_password" :value="__('Kata Sandi')" />
                    <x-text-input id="admin_password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="admin_password_confirmation" :value="__('Konfirmasi Kata Sandi')" />
                    <x-text-input id="admin_password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- Admin Code -->
                <div class="mt-4">
                    <x-input-label for="admin_code" :value="__('Kode Admin')" />
                    <x-text-input id="admin_code" class="block mt-1 w-full" type="password" name="admin_code" required />
                    <x-input-error :messages="$errors->get('admin_code')" class="mt-2" />
                    <p class="text-sm text-gray-500 mt-1">Masukkan kode admin untuk verifikasi.</p>
                </div>

                <!-- Profile Image -->
                <div class="mt-4">
                    <x-input-label for="admin_profile_image" :value="__('Foto Profil')" />
                    <div class="mt-1 flex items-center">
                        <label for="admin_profile_image" class="flex items-center justify-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-200 focus:outline-none cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Pilih Foto
                        </label>
                        <span id="file-name-admin" class="ml-3 text-sm text-gray-500">Belum ada file yang dipilih</span>
                        <input id="admin_profile_image" class="hidden" type="file" name="profile_image" accept="image/*" />
                    </div>
                    <x-input-error :messages="$errors->get('profile_image')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between mt-8">
                    <div class="flex flex-col">
                        <span class="text-sm text-gray-600">Sudah punya akun?</span>
                        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">Login Sekarang</a>
                    </div>

                    <x-primary-button class="ms-3 bg-[#133057] hover:bg-[#0e223e] text-white">
                        {{ __('Daftar') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab Navigation
            const tabAccountManager = document.getElementById('tab-account-manager');
            const tabAdmin = document.getElementById('tab-admin');
            const formAccountManager = document.getElementById('form-account-manager');
            const formAdmin = document.getElementById('form-admin');

            tabAccountManager.addEventListener('click', function() {
                formAccountManager.classList.remove('hidden');
                formAdmin.classList.add('hidden');
                tabAccountManager.classList.add('border-[#133057]', 'text-[#133057]');
                tabAccountManager.classList.remove('text-gray-500');
                tabAdmin.classList.remove('border-[#133057]', 'text-[#133057]');
                tabAdmin.classList.add('text-gray-500');
            });

            tabAdmin.addEventListener('click', function() {
                formAccountManager.classList.add('hidden');
                formAdmin.classList.remove('hidden');
                tabAdmin.classList.add('border-[#133057]', 'text-[#133057]');
                tabAdmin.classList.remove('text-gray-500');
                tabAccountManager.classList.remove('border-[#133057]', 'text-[#133057]');
                tabAccountManager.classList.add('text-gray-500');
            });

            // File Upload Display
            const profileImageInput = document.getElementById('profile_image');
            const fileNameSpanAM = document.getElementById('file-name-am');
            if (profileImageInput && fileNameSpanAM) {
                profileImageInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        fileNameSpanAM.textContent = this.files[0].name;
                    } else {
                        fileNameSpanAM.textContent = 'Belum ada file yang dipilih';
                    }
                });
            }

            const adminProfileImageInput = document.getElementById('admin_profile_image');
            const fileNameSpanAdmin = document.getElementById('file-name-admin');
            if (adminProfileImageInput && fileNameSpanAdmin) {
                adminProfileImageInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        fileNameSpanAdmin.textContent = this.files[0].name;
                    } else {
                        fileNameSpanAdmin.textContent = 'Belum ada file yang dipilih';
                    }
                });
            }

            // Account Manager Search Functionality
            const searchInput = document.getElementById('account_manager_search');
            const resultsDiv = document.getElementById('account_manager_results');
            const idInput = document.getElementById('account_manager_id');

            // Pencarian AM dengan debounce
            if (searchInput) {
                let debounceTimer;
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        const searchTerm = this.value.trim();
                        if (searchTerm.length < 3) {
                            resultsDiv.classList.add('hidden');
                            return;
                        }

                        fetch(`/search-account-managers?search=${searchTerm}`)
                            .then(response => response.json())
                            .then(data => {
                                resultsDiv.innerHTML = '';
                                if (data.length === 0) {
                                    resultsDiv.innerHTML = '<div class="p-2 text-gray-500">Tidak ditemukan</div>';
                                    resultsDiv.classList.remove('hidden');
                                    return;
                                }

                                data.forEach(am => {
                                    const div = document.createElement('div');
                                    div.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                                    div.textContent = `${am.nama} (${am.nik})`;
                                    div.addEventListener('click', () => {
                                        searchInput.value = am.nama;
                                        idInput.value = am.id;
                                        resultsDiv.classList.add('hidden');
                                    });
                                    resultsDiv.appendChild(div);
                                });

                                resultsDiv.classList.remove('hidden');
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                    }, 300);
                });

                // Sembunyikan hasil pencarian ketika klik di luar
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                        resultsDiv.classList.add('hidden');
                    }
                });
            }

            // Jika tidak ada account manager, arahkan ke tab Admin secara otomatis
            if (typeof noAccountManagers !== 'undefined' && noAccountManagers) {
                tabAdmin.click();
            }
        });
    </script>
</x-guest-layout>

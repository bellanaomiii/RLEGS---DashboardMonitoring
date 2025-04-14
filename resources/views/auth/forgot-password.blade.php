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
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-white">
        <div class="mt-6">
            <img src="{{ asset('img/logo-telkom.png') }}" alt="Telkom Indonesia" class="w-40">
        </div>

        <div class="w-full sm:max-w-md mt-6 px-8 py-6 bg-white shadow-md overflow-hidden sm:rounded-lg border border-gray-200">
            <h2 class="text-center text-xl font-bold mb-5">Dashboard Monitoring RLEGS</h2>

            <div class="flex flex-col mb-4 text-sm text-gray-600 text-center">
                <p>Lupa kata sandi Anda? Masukkan email yang terdaftar, dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-4">
                    <label for="email" class="block font-medium text-sm text-gray-700 mb-1">Email</label>
                    <input id="email" name="email" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="email" placeholder="Masukkan email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mt-6">
                    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
                    </a>

                    <button type="submit" class="px-4 py-2 bg-[#0e223e] text-white rounded-md hover:bg-[#1e3c72] transition-colors">
                        Kirim Tautan Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
@extends('layouts.main')

@section('title', 'config('app.name', 'Laravel')')

@section('content')
    @include('layouts.profileupdate')

    @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <main>
        {{ $slot }}
    </main>
@endsection

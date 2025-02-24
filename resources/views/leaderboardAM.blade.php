@extends('layouts.main')

@section('title', 'Leaderboard AM')

@section('content')
    <section class="bg-primary text-white p-5 p-lg-0 pt-lg-5 text-center text-sm-start">
        <div class="container">
            <div class="d-sm-flex align-items-center justify-content-between">
                <div>
                    <h1>Leaderboard AM</h1>
                    <p class="lead my-4">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Pariatur, voluptas rem? Amet dignissimos, commodi officiis itaque nulla molestias accusantium maiores hic libero at illo odit sed suscipit dolor, aspernatur rerum?
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="container my-5">
        <h2 class="text-center mb-4">Top Performers</h2>

        <ul class="list-unstyled text-center">
            <li><strong>1. John Doe</strong> - Score: 95</li>
            <li><strong>2. Jane Smith</strong> - Score: 90</li>
            <li><strong>3. Alex Johnson</strong> - Score: 85</li>
            <li><strong>4. Emily Brown</strong> - Score: 80</li>
            <li><strong>5. Michael Wilson</strong> - Score: 75</li>
        </ul>
    </section>
@endsection

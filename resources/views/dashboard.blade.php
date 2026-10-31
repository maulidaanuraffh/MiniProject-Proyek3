@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="card">
        <h1>Dashboard</h1>

        @auth
            <p>Halo, {{ auth()->user()->name }} (role: {{ auth()->user()->role }}).</p>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        @else
            <p>Anda belum login. <a href="{{ route('login') }}">Login di sini</a>.</p>
        @endauth

        <p>Selamat datang di aplikasi pelaporan keamanan makanan MBG.</p>
    </div>
@endsection
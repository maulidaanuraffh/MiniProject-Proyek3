@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="card">
        <h1>Dashboard</h1>

        <p>Halo, {{ auth()->user()->name }} (role: {{ auth()->user()->role }}).</p>

        @if (auth()->user()->role === 'pelapor')
            <p><a href="{{ route('reports.create') }}">Buat Laporan Kejadian</a></p>
        @elseif (in_array(auth()->user()->role, ['pemantau', 'koordinator'], true))
            <p><a href="{{ route('reports.index') }}">Daftar Laporan</a></p>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>

        <p>Selamat datang di aplikasi pelaporan keamanan makanan MBG.</p>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
        $roleLabel = [
            'pelapor' => 'Pelapor',
            'pemantau' => 'Pemantau Komunitas',
            'koordinator' => 'Koordinator Komunitas',
        ][$user->role] ?? $user->role;
    @endphp

    <div class="card dashboard-header">
        <div>
            <h1>Halo, {{ $user->name }}</h1>
            <p class="total">Role: {{ $roleLabel }}</p>
        </div>
    </div>

    <div class="menu-grid">
        @if ($user->role === 'pelapor')
            <a class="menu-card" href="{{ route('reports.create') }}">
                <span class="menu-title">Buat Laporan Kejadian</span>
                <span class="menu-desc">Laporkan kejadian setelah mengonsumsi makanan MBG, lengkap dengan bukti pendukung.</span>
            </a>

            <a class="menu-card" href="{{ route('reports.index') }}">
                <span class="menu-title">Laporan Saya</span>
                <span class="menu-desc">Pantau status laporan yang sudah kamu kirim.</span>
            </a>
        @endif

        @if (in_array($user->role, ['pemantau', 'koordinator'], true))
            <a class="menu-card" href="{{ route('reports.index') }}">
                <span class="menu-title">Daftar Laporan</span>
                <span class="menu-desc">Lihat semua laporan, filter berdasarkan status atau sekolah.</span>
            </a>

            <a class="menu-card" href="{{ route('recap.index') }}">
                <span class="menu-title">Rekap Laporan</span>
                <span class="menu-desc">Ringkasan jumlah laporan per status dan per sekolah.</span>
            </a>
        @endif

        @if ($user->role === 'koordinator')
            <a class="menu-card" href="{{ route('manage.schools.index') }}">
                <span class="menu-title">Kelola Sekolah dan Distribusi</span>
                <span class="menu-desc">Tambah sekolah dan jadwal distribusi makanan.</span>
            </a>
        @endif
    </div>

    <form method="POST" action="{{ route('logout') }}" style="margin-top: 24px;">
        @csrf
        <button type="submit">Logout</button>
    </form>
@endsection

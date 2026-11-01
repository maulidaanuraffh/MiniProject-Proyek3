@extends('layouts.app')

@section('title', 'Kelola Sekolah')

@section('content')
    <div class="card">
        <h1>Kelola Sekolah</h1>

        @if (session('status'))
            <div style="background: #e6f4ea; border-left: 4px solid #1b7a3a; color: #1b5e20; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-weight: 600;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: #fdecea; border-left: 4px solid #b00020; color: #7a0010; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <h2>Tambah sekolah</h2>

        <form method="POST" action="{{ route('manage.schools.store') }}" class="filter-form">
            @csrf

            <div class="filter-field">
                <label for="name">Nama sekolah</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required>
            </div>

            <div class="filter-field">
                <label for="address">Alamat (opsional)</label>
                <input id="address" name="address" type="text" value="{{ old('address') }}">
            </div>

            <div class="filter-actions">
                <button type="submit">Tambah</button>
            </div>
        </form>

        <h2 style="margin-top: 28px;">Daftar sekolah</h2>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Jumlah distribusi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schools as $school)
                        <tr>
                            <td>{{ $school->name }}</td>
                            <td>{{ $school->address ?? '-' }}</td>
                            <td>{{ $school->meal_distributions_count }}</td>
                            <td>
                                <a href="{{ route('manage.distributions.index', $school) }}">Kelola distribusi</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty">Belum ada sekolah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

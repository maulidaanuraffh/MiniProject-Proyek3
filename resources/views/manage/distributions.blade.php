@extends('layouts.app')

@section('title', 'Kelola Distribusi')

@section('content')
    <div class="card">
        <p><a href="{{ route('manage.schools.index') }}">&larr; Kembali ke daftar sekolah</a></p>

        <h1>Distribusi: {{ $school->name }}</h1>

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

        <h2>Tambah distribusi</h2>

        <form method="POST" action="{{ route('manage.distributions.store', $school) }}" class="filter-form">
            @csrf

            <div class="filter-field">
                <label for="distribution_date">Tanggal distribusi</label>
                <input id="distribution_date" name="distribution_date" type="date" value="{{ old('distribution_date') }}" required>
            </div>

            <div class="filter-field">
                <label for="description">Keterangan (opsional)</label>
                <input id="description" name="description" type="text" value="{{ old('description') }}">
            </div>

            <div class="filter-actions">
                <button type="submit">Tambah</button>
            </div>
        </form>

        <h2 style="margin-top: 28px;">Daftar distribusi</h2>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($distributions as $distribution)
                        <tr>
                            <td>{{ $distribution->distribution_date->format('d M Y') }}</td>
                            <td>{{ $distribution->description ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="empty">Belum ada distribusi untuk sekolah ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

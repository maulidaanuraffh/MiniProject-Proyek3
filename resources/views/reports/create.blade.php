@extends('layouts.app')

@section('title', 'Buat Laporan')

@section('content')
    <div class="card">
        <h1>Buat Laporan Kejadian</h1>

        @if (session('status'))
            <div style="background: #e6f4ea; border-left: 4px solid #1b7a3a; color: #1b5e20; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-weight: 600;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <ul style="color: #b00020;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('reports.store') }}">
            @csrf

            <p>
                <label for="school_id">Sekolah</label><br>
                <select id="school_id" name="school_id" required>
                    <option value="">-- pilih sekolah --</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>
                            {{ $school->name }}
                        </option>
                    @endforeach
                </select>
            </p>

            <p>
                <label for="meal_distribution_id">Distribusi makanan</label><br>
                <select id="meal_distribution_id" name="meal_distribution_id" required>
                    <option value="">-- pilih distribusi --</option>
                    @foreach ($distributions as $distribution)
                        <option value="{{ $distribution->id }}" @selected(old('meal_distribution_id') == $distribution->id)>
                            {{ $distribution->school->name }} — {{ $distribution->distribution_date->format('d M Y') }}
                        </option>
                    @endforeach
                </select>
            </p>

            <p>
                <label for="title">Judul</label><br>
                <input id="title" name="title" type="text" value="{{ old('title') }}" required>
            </p>

            <p>
                <label for="incident_date">Tanggal kejadian</label><br>
                <input id="incident_date" name="incident_date" type="date" value="{{ old('incident_date') }}" required>
            </p>

            <p>
                <label for="affected_count">Jumlah yang mengalami keluhan (opsional)</label><br>
                <input id="affected_count" name="affected_count" type="number" min="1" value="{{ old('affected_count') }}">
            </p>

            <p>
                <label for="symptoms">Gejala</label><br>
                <textarea id="symptoms" name="symptoms" rows="3" required>{{ old('symptoms') }}</textarea>
            </p>

            <p>
                <label for="incident_description">Deskripsi kejadian</label><br>
                <textarea id="incident_description" name="incident_description" rows="5" required>{{ old('incident_description') }}</textarea>
            </p>

            <button type="submit">Kirim Laporan</button>
        </form>
    </div>
@endsection

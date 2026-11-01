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
                <select id="meal_distribution_id" name="meal_distribution_id" required data-old="{{ old('meal_distribution_id') }}">
                    <option value="">-- pilih sekolah dulu --</option>
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

    <script>
        (function () {
            const schoolSelect = document.getElementById('school_id');
            const distSelect = document.getElementById('meal_distribution_id');
            const baseUrl = @json(url('/schools'));

            function setPlaceholder(text) {
                distSelect.innerHTML = '';
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = text;
                distSelect.appendChild(opt);
            }

            async function loadDistributions(schoolId) {
                if (!schoolId) {
                    setPlaceholder('-- pilih sekolah dulu --');
                    return;
                }

                setPlaceholder('-- memuat distribusi --');

                try {
                    const response = await fetch(baseUrl + '/' + schoolId + '/distributions', {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }

                    const items = await response.json();
                    setPlaceholder(items.length ? '-- pilih distribusi --' : '-- belum ada distribusi untuk sekolah ini --');

                    const oldValue = distSelect.dataset.old || '';

                    items.forEach(function (item) {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.label;
                        if (String(item.id) === oldValue) {
                            opt.selected = true;
                        }
                        distSelect.appendChild(opt);
                    });
                } catch (e) {
                    setPlaceholder('-- gagal memuat distribusi, coba lagi --');
                }
            }

            schoolSelect.addEventListener('change', function () {
                distSelect.dataset.old = '';
                loadDistributions(schoolSelect.value);
            });

            // Jika form dikembalikan karena error validasi, isi ulang distribusinya
            if (schoolSelect.value) {
                loadDistributions(schoolSelect.value);
            }
        })();
    </script>
@endsection

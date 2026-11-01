@extends('layouts.app')

@section('title', 'Edit Laporan')

@section('content')
    <div class="card">
        <p><a href="{{ route('reports.show', $report) }}">&larr; Kembali ke detail laporan</a></p>

        <h1>Edit Laporan</h1>

        @if ($errors->any())
            <div style="background: #fdecea; border-left: 4px solid #b00020; color: #7a0010; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('reports.update', $report) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <p>
                <label for="school_id">Sekolah</label><br>
                <select id="school_id" name="school_id" required>
                    <option value="">-- pilih sekolah --</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" @selected((int) old('school_id', $report->school_id) === $school->id)>
                            {{ $school->name }}
                        </option>
                    @endforeach
                </select>
            </p>

            <p>
                <label for="meal_distribution_id">Distribusi makanan</label><br>
                <select id="meal_distribution_id" name="meal_distribution_id" required data-old="{{ old('meal_distribution_id', $report->meal_distribution_id) }}">
                    <option value="">-- memuat distribusi --</option>
                </select>
            </p>

            <p>
                <label for="title">Judul</label><br>
                <input id="title" name="title" type="text" value="{{ old('title', $report->title) }}" required>
            </p>

            <p>
                <label for="incident_date">Tanggal kejadian</label><br>
                <input id="incident_date" name="incident_date" type="date" value="{{ old('incident_date', $report->incident_date->format('Y-m-d')) }}" required>
            </p>

            <p>
                <label for="affected_count">Jumlah yang mengalami keluhan (opsional)</label><br>
                <input id="affected_count" name="affected_count" type="number" min="1" value="{{ old('affected_count', $report->affected_count) }}">
            </p>

            <p>
                <label for="symptoms">Gejala</label><br>
                <textarea id="symptoms" name="symptoms" rows="3" required>{{ old('symptoms', $report->symptoms) }}</textarea>
            </p>

            <p>
                <label for="incident_description">Deskripsi kejadian</label><br>
                <textarea id="incident_description" name="incident_description" rows="5" required>{{ old('incident_description', $report->incident_description) }}</textarea>
            </p>

            <p>
                <label for="evidence">Ganti bukti pendukung (opsional, JPG/PNG/PDF, maks. 2 MB)</label><br>
                @if ($report->evidence_original_name)
                    <small>Bukti saat ini: {{ $report->evidence_original_name }}. Kosongkan jika tidak ingin mengganti.</small><br>
                @endif
                <input id="evidence" name="evidence" type="file" accept=".jpg,.jpeg,.png,.pdf">
            </p>

            <button type="submit">Simpan Perubahan</button>
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

            if (schoolSelect.value) {
                loadDistributions(schoolSelect.value);
            }
        })();
    </script>
@endsection

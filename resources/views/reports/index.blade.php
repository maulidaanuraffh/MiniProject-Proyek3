@extends('layouts.app')

@section('title', 'Daftar Laporan')

@section('content')
    <div class="card">
        <h1>Daftar Laporan</h1>

        <form method="GET" action="{{ route('reports.index') }}" class="filter-form">
            <div class="filter-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="school_id">Sekolah</label>
                <select id="school_id" name="school_id">
                    <option value="">Semua sekolah</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" @selected((string) ($filters['school_id'] ?? '') === (string) $school->id)>{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit">Terapkan</button>
                <a href="{{ route('reports.index') }}">Reset</a>
            </div>
        </form>

        <p class="total">Total: {{ $reports->total() }} laporan</p>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Sekolah</th>
                        <th>Tanggal kejadian</th>
                        <th>Status</th>
                        <th>Ditandai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td>#{{ $report->id }}</td>
                            <td><a href="{{ route('reports.show', $report) }}">{{ $report->title }}</a></td>
                            <td>{{ $report->school->name }}</td>
                            <td>{{ $report->incident_date->format('d M Y') }}</td>
                            <td><span class="badge badge-{{ $report->status }}">{{ $report->status }}</span></td>
                            <td>{{ $report->flagged_for_forwarding ? 'Ya' : 'Tidak' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">Tidak ada laporan yang sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $reports->links('pagination.idn') }}
        </div>
    </div>
@endsection

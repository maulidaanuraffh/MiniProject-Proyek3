@extends('layouts.app')

@section('title', 'Rekap Laporan')

@section('content')
    <div class="card">
        <h1>Rekap Laporan</h1>

        <form method="GET" action="{{ route('recap.index') }}" class="filter-form">
            <div class="filter-field">
                <label for="from">Dari tanggal kejadian</label>
                <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}">
            </div>

            <div class="filter-field">
                <label for="to">Sampai tanggal kejadian</label>
                <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}">
            </div>

            <div class="filter-actions">
                <button type="submit">Terapkan</button>
                <a href="{{ route('recap.index') }}">Reset</a>
            </div>
        </form>

        <div style="display: flex; flex-wrap: wrap; gap: 16px; margin: 16px 0 8px;">
            <div style="flex: 1; min-width: 160px; background: #f4f6f8; border-radius: 8px; padding: 16px;">
                <div style="color: #52606d; font-size: 14px;">Total laporan</div>
                <div style="font-size: 28px; font-weight: 700;">{{ $summary['total'] }}</div>
            </div>

            <div style="flex: 1; min-width: 160px; background: #fff4d6; border-radius: 8px; padding: 16px;">
                <div style="color: #8a5a00; font-size: 14px;">Sedang diproses</div>
                <div style="font-size: 28px; font-weight: 700; color: #8a5a00;">{{ $summary['diproses'] }}</div>
            </div>

            <div style="flex: 1; min-width: 160px; background: #e6f4ea; border-radius: 8px; padding: 16px;">
                <div style="color: #1b5e20; font-size: 14px;">Sudah diteruskan</div>
                <div style="font-size: 28px; font-weight: 700; color: #1b5e20;">{{ $summary['selesai'] }}</div>
            </div>
        </div>

        <p style="color: #52606d; font-size: 14px; margin-bottom: 20px;">
            Sedang diproses: status submitted dan under_review. Sudah diteruskan: status forwarded dan closed.
        </p>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sekolah</th>
                        <th>Total</th>
                        <th>Submitted</th>
                        <th>Under review</th>
                        <th>Forwarded</th>
                        <th>Closed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($perSchool as $row)
                        <tr>
                            <td>{{ $row->school_name }}</td>
                            <td>{{ $row->total }}</td>
                            <td>{{ $row->submitted }}</td>
                            <td>{{ $row->under_review }}</td>
                            <td>{{ $row->forwarded }}</td>
                            <td>{{ $row->closed }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">Belum ada laporan pada rentang ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

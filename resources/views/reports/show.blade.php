@extends('layouts.app')

@section('title', 'Detail Laporan')

@section('content')
    <div class="card">
        <p><a href="{{ route('reports.index') }}">&larr; Kembali ke daftar</a></p>

        <h1>{{ $report->title }}</h1>

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

        <p>Status: <span class="badge badge-{{ $report->status }}">{{ $report->status }}</span></p>

        <div class="table-wrap">
            <table class="data-table">
                <tbody>
                    <tr>
                        <th>Sekolah</th>
                        <td>{{ $report->school->name }}</td>
                    </tr>
                    <tr>
                        <th>Distribusi makanan</th>
                        <td>{{ $report->mealDistribution->distribution_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal kejadian</th>
                        <td>{{ $report->incident_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Jumlah yang mengalami keluhan</th>
                        <td>{{ $report->affected_count ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Gejala</th>
                        <td>{{ $report->symptoms }}</td>
                    </tr>
                    <tr>
                        <th>Deskripsi kejadian</th>
                        <td>{{ $report->incident_description }}</td>
                    </tr>
                    <tr>
                        <th>Bukti pendukung</th>
                        <td>
                            @if ($report->evidence_path)
                                <a href="{{ route('reports.evidence', $report) }}">{{ $report->evidence_original_name }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Ditinjau oleh</th>
                        <td>{{ $report->monitor?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Catatan pemantau</th>
                        <td>{{ $report->monitor_notes ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Ditandai untuk diteruskan</th>
                        <td>{{ $report->flagged_for_forwarding ? 'Ya' : 'Tidak' }}</td>
                    </tr>
                    @if ($report->forwarded_to)
                        <tr>
                            <th>Diteruskan kepada</th>
                            <td>{{ $report->forwarded_to }}</td>
                        </tr>
                    @endif
                    @if ($report->coordinator_notes)
                        <tr>
                            <th>Catatan koordinator</th>
                            <td>{{ $report->coordinator_notes }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if (auth()->user()->role === 'pemantau' && $report->status === 'submitted')
            <h2 style="margin-top: 28px;">Tinjau Laporan</h2>

            <form method="POST" action="{{ route('reports.review', $report) }}">
                @csrf

                <p>
                    <label for="monitor_notes">Catatan pemantau (opsional)</label><br>
                    <textarea id="monitor_notes" name="monitor_notes" rows="4">{{ old('monitor_notes') }}</textarea>
                </p>

                <p>
                    <label>
                        <input type="checkbox" name="flagged_for_forwarding" value="1" @checked(old('flagged_for_forwarding'))>
                        Tandai untuk diteruskan ke pihak terkait
                    </label>
                </p>

                <button type="submit">Tinjau Laporan</button>
            </form>
        @endif

        @if (auth()->user()->role === 'koordinator' && $report->status === 'under_review')
            <h2 style="margin-top: 28px;">Teruskan Laporan</h2>

            @if (! $report->flagged_for_forwarding)
                <p style="color: #7a5a00;">Laporan ini belum ditandai pemantau untuk diteruskan.</p>
            @else
                <form method="POST" action="{{ route('reports.forward', $report) }}">
                    @csrf

                    <p>
                        <label for="forwarded_to">Diteruskan kepada</label><br>
                        <select id="forwarded_to" name="forwarded_to" required>
                            <option value="">-- pilih tujuan --</option>
                            @foreach ($forwardTargets as $target)
                                <option value="{{ $target }}" @selected(old('forwarded_to') === $target)>{{ $target }}</option>
                            @endforeach
                        </select>
                    </p>

                    <p>
                        <label for="coordinator_notes">Catatan penerusan (opsional)</label><br>
                        <textarea id="coordinator_notes" name="coordinator_notes" rows="4">{{ old('coordinator_notes') }}</textarea>
                    </p>

                    <button type="submit">Teruskan Laporan</button>
                </form>
            @endif
        @endif

        @if (auth()->user()->role === 'koordinator' && $report->status === 'forwarded')
            <h2 style="margin-top: 28px;">Tutup Laporan</h2>

            <form method="POST" action="{{ route('reports.close', $report) }}">
                @csrf
                <button type="submit">Tutup Laporan</button>
            </form>
        @endif
    </div>
@endsection

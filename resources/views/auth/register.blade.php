@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <div class="card">
        <h1>Daftar Akun Pelapor</h1>

        @if ($errors->any())
            <ul style="color: #b00020;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ url('/register') }}">
            @csrf

            <p>
                <label for="name">Nama</label><br>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required>
            </p>
            <p>
                <label for="email">Email</label><br>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            </p>
            <p>
                <label for="password">Password</label><br>
                <input id="password" name="password" type="password" required>
            </p>
            <p>
                <label for="password_confirmation">Ulangi Password</label><br>
                <input id="password_confirmation" name="password_confirmation" type="password" required>
            </p>

            <button type="submit">Daftar</button>
        </form>
    </div>
@endsection
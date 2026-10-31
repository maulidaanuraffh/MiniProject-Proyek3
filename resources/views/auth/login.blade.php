@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="card">
        <h1>Login</h1>

        @if ($errors->any())
            <ul style="color: #b00020;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <p>
                <label for="email">Email</label><br>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            </p>
            <p>
                <label for="password">Password</label><br>
                <input id="password" name="password" type="password" required>
            </p>

            <button type="submit">Login</button>
        </form>

        <p>Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai pelapor</a></p>
    </div>
@endsection
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pantau MBG')</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            margin: 0;
            background: #f4f6f8;
            color: #1f2933;
        }
        header {
            background: #1f4e79;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header a {
            color: #ffffff;
            text-decoration: none;
            margin-left: 16px;
        }
        main {
            max-width: 960px;
            margin: 24px auto;
            padding: 0 16px;
        }
        .card {
            background: #ffffff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        label {
            font-weight: 600;
        }
        input {
            width: 100%;
            max-width: 360px;
            padding: 8px 10px;
            border: 1px solid #b8c2cc;
            border-radius: 6px;
            font-size: 15px;
            box-sizing: border-box;
        }
        button {
            background: #1f4e79;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 15px;
            cursor: pointer;
        }
        button:hover {
            background: #163a5a;
        }
    </style>
</head>
<body>
    <header>
        <strong>Pantau MBG</strong>
        <nav>
            <a href="{{ url('/') }}">Beranda</a>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>

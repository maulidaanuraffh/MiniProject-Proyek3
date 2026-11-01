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

        /* Filter daftar laporan */
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
            margin-bottom: 16px;
        }
        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .filter-field select {
            min-width: 200px;
            padding: 8px 10px;
            border: 1px solid #b8c2cc;
            border-radius: 6px;
            font-size: 15px;
        }
        .filter-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .filter-actions a {
            color: #1f4e79;
        }
        .total {
            color: #52606d;
            margin: 8px 0 12px;
        }

        /* Tabel daftar laporan */
        .table-wrap {
            overflow-x: auto;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }
        .data-table th,
        .data-table td {
            text-align: left;
            padding: 10px 12px;
            border-bottom: 1px solid #e4e7eb;
            vertical-align: top;
        }
        .data-table th {
            background: #f4f6f8;
            font-weight: 600;
            border-bottom: 2px solid #ccd3da;
        }
        .data-table tbody tr:hover {
            background: #f9fbfd;
        }
        .data-table .empty {
            text-align: center;
            color: #7b8794;
            padding: 24px;
        }

        /* Badge status */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            background: #e4e7eb;
            color: #3e4c59;
        }
        .badge-submitted {
            background: #e0ecf8;
            color: #1f4e79;
        }
        .badge-under_review {
            background: #fff4d6;
            color: #8a5a00;
        }
        .badge-forwarded {
            background: #e6f4ea;
            color: #1b5e20;
        }
        .badge-closed {
            background: #ececec;
            color: #52606d;
        }

        /* Pagination */
        .pagination {
            margin-top: 28px;
        }
        .pager {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .pager-btn {
            display: inline-block;
            min-width: 40px;
            padding: 8px 14px;
            border: 1px solid #b8c2cc;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            color: #1f4e79;
            background: #ffffff;
            box-sizing: border-box;
        }
        .pager-btn.active {
            background: #1f4e79;
            border-color: #1f4e79;
            color: #ffffff;
            font-weight: 600;
        }
        .pager-btn.disabled {
            color: #9aa5b1;
            background: #f4f6f8;
        }
        .pager-info {
            text-align: center;
            color: #52606d;
            font-size: 14px;
            margin-top: 14px;
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

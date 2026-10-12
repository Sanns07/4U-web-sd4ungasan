<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Login portal akademik untuk siswa dan admin.">
    <title>@yield('title', 'Masuk Portal — SD No. 4 Ungasan')</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo_sekolah.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>@yield('content')</body>
</html>

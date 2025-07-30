<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Login')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body class="bg-gray-100 font-sans min-h-screen flex items-center justify-center">

    @yield('content')
    @stack('scripts')

</body>
</html>

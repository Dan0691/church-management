<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Church Management System</title>

    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Add some basic styles if needed -->
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
        }
        #app {
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div id="app"></div>

    <!-- Add Vue dev tools in development -->
    @if(app()->environment('local'))
        <script src="http://localhost:8098"></script>
    @endif
</body>
</html>

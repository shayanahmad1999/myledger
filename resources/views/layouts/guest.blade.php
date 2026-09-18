<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MyLedger')</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/myledger.css') }}" rel="stylesheet">
</head>

<body class="auth-body">
    <main class="container py-5">
        <div class="auth-shell mx-auto">
            <div class="text-center mb-4">
                <div class="brand-mark mx-auto mb-3"><i class="bi bi-wallet2"></i></div>
                <h1 class="h3 fw-bold mb-1">MyLedger</h1>
                <p class="text-secondary mb-0">Your complete personal finance workspace</p>
            </div>
            @yield('content')
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

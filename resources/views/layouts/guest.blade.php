<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background: #f8fafc; color: #1b1b18; min-height: 100vh; }
        .hero-bg { background: radial-gradient(circle at top left, rgba(13,110,253,.18), transparent 35%), radial-gradient(circle at bottom right, rgba(102,16,242,.18), transparent 28%), linear-gradient(180deg, #eff6ff 0%, #f8fafc 100%); }
        .form-card { border: 1px solid rgba(15, 23, 42, .08); }
    </style>
</head>
<body class="hero-bg d-flex align-items-center justify-content-center">
    <div class="container py-5">
        @yield('content')
    </div>
</body>
</html>

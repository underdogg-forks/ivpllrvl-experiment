<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvoicePlane - Laravel 12</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            color: white;
            padding: 40px;
        }
        h1 {
            font-size: 3em;
            margin-bottom: 0.5em;
        }
        p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            margin: 10px 5px;
            font-size: 0.9em;
        }
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 30px;
            margin-top: 30px;
            text-align: left;
        }
        .success {
            color: #4ade80;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 InvoicePlane</h1>
        <p class="success">Successfully upgraded to Laravel 12!</p>
        
        <div class="badge">Laravel Framework 12.x</div>
        <div class="badge">PHP <?php echo PHP_VERSION; ?></div>
        <div class="badge">Filament Admin Panel Ready</div>
        
        <div class="card">
            <h2>✅ Upgrade Complete</h2>
            <p><strong>What's been done:</strong></p>
            <ul>
                <li>Laravel 12 Framework installed</li>
                <li>Filament Admin Panel installed</li>
                <li>Modern bootstrap/app.php with exception handling</li>
                <li>bootstrap/providers.php created</li>
                <li>Artisan CLI available</li>
                <li>All namespaces verified</li>
            </ul>
            <p style="margin-top: 20px;">
                <strong>Next Steps:</strong><br>
                Run <code style="background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 4px;">php artisan filament:install --panels</code> to set up your admin panel.
            </p>
        </div>
    </div>
</body>
</html>

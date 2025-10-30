<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvoicePlane - Laravel Edition</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 60px;
            text-align: center;
            max-width: 600px;
        }
        h1 {
            color: #667eea;
            margin: 0 0 20px 0;
            font-size: 3em;
        }
        .badge {
            background: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 30px;
        }
        p {
            font-size: 1.1em;
            line-height: 1.6;
            color: #666;
            margin: 20px 0;
        }
        .features {
            text-align: left;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .features h3 {
            color: #667eea;
            margin-top: 0;
        }
        .features ul {
            list-style: none;
            padding: 0;
        }
        .features li {
            padding: 8px 0;
            color: #555;
        }
        .features li:before {
            content: "✓ ";
            color: #10b981;
            font-weight: bold;
            margin-right: 8px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #999;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>InvoicePlane</h1>
        <div class="badge">Laravel Edition</div>
        
        <p><strong>Congratulations!</strong> CodeIgniter has been completely removed and the application is now running on pure Laravel/Illuminate.</p>
        
        <div class="features">
            <h3>Migration Complete</h3>
            <ul>
                <li>CodeIgniter framework removed</li>
                <li>All models migrated to Modules/Core/Models</li>
                <li>Laravel/Illuminate components active</li>
                <li>PSR-4 autoloading in effect</li>
                <li>Modern exception handling implemented</li>
                <li>Professional bootstrap process</li>
            </ul>
        </div>
        
        <p>The application structure now follows Laravel best practices with a clean, maintainable codebase.</p>
        
        <div class="footer">
            Environment: <strong><?php echo ENVIRONMENT; ?></strong><br>
            PHP Version: <?php echo PHP_VERSION; ?>
        </div>
    </div>
</body>
</html>

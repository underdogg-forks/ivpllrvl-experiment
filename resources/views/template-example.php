<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Template Example - InvoicePlane</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            margin-top: 0;
        }
        .highlight {
            background-color: #e0e7ff;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .success {
            color: #10b981;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>✓ PHP Template System Configured</h1>
        
        <div class="highlight">
            <p class="success">This view is rendered using plain PHP templates, not Blade!</p>
        </div>

        <h2>Template Engine Details:</h2>
        <ul>
            <li><strong>Engine:</strong> PHP (PhpEngine)</li>
            <li><strong>Extension:</strong> <code>.php</code></li>
            <li><strong>Location:</strong> <code>resources/views/</code></li>
        </ul>

        <h2>Example PHP Syntax:</h2>
        <div class="highlight">
            <p><strong>Variable output:</strong></p>
            <code>&lt;?php echo $variable; ?&gt;</code>
            
            <p style="margin-top: 15px;"><strong>Conditional:</strong></p>
            <code>&lt;?php if ($condition): ?&gt; ... &lt;?php endif; ?&gt;</code>
            
            <p style="margin-top: 15px;"><strong>Loop:</strong></p>
            <code>&lt;?php foreach ($items as $item): ?&gt; ... &lt;?php endforeach; ?&gt;</code>
        </div>

        <h2>Configuration:</h2>
        <p>The template system is configured in:</p>
        <ul>
            <li><code>app/Providers/AppServiceProvider.php</code> - Registers PhpEngine as primary</li>
            <li><code>config/view.php</code> - View paths and compiled view storage</li>
            <li><code>config/modules.php</code> - Module view stub configuration</li>
        </ul>

        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
            <strong>Note:</strong> Blade templates are still available as a secondary option,
            but all new views should use plain PHP for consistency with the existing codebase.
        </p>
    </div>
</body>
</html>

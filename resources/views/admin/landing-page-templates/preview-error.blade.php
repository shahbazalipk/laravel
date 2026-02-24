<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Error - {{ $template->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 40px;
        }
        h1 {
            color: #dc2626;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .error-box {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .error-message {
            color: #991b1b;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #4f46e5;
            color: white;
        }
        .btn-primary:hover {
            background: #4338ca;
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
        .info {
            background: #dbeafe;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            color: #1e40af;
            font-size: 14px;
        }
        .info strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Template Preview Error</h1>
        
        <p>There was an error rendering the template: <strong>{{ $template->name }}</strong></p>
        
        <div class="error-box">
            <div class="error-message">{{ $error }}</div>
        </div>
        
        <div class="info">
            <strong>Common Issues:</strong>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Check for unclosed Blade directives (@if without @endif)</li>
                <li>Verify variable names match available data ($event, $speakers, etc.)</li>
                <li>Ensure proper Blade syntax ({{ }} for output, @foreach for loops)</li>
                <li>Check for typos in property names ($event->event_name not $event->name)</li>
            </ul>
        </div>
        
        <div class="actions">
            <a href="{{ route('admin.landing-page-templates.edit', $template) }}" class="btn btn-primary">
                Edit Template
            </a>
            <a href="{{ route('admin.landing-page-templates.index') }}" class="btn btn-secondary">
                Back to Templates
            </a>
        </div>
    </div>
</body>
</html>

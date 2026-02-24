<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $template->name }} - Preview</title>
    <style>
        {!! $template->css_content !!}
    </style>
</head>
<body>
    {!! $template->html_content !!}
    
    @if($template->js_content)
        <script>
            {!! $template->js_content !!}
        </script>
    @endif
</body>
</html>

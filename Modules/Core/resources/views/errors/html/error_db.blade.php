<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="utf-8">
    <title>InvoicePlane - {{ $heading }}</title>
    <style>
        html,
        html * {
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            background: #B94A48;
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2vh 2vw;
        }

        .container {
            max-width: 600px;
            width: 100%;
        }

        h4 {
            font-size: 30px;
            margin-bottom: 20px;
        }

        p {
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="container">
    <h4>{{ $heading }}</h4>
    <p>{{ $message }}</p>
</div>
</body>
</html>

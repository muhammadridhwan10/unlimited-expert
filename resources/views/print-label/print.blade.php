<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        .label {
            width: 10cm; 
            height: 3.8cm; 
            margin-right: 0.3cm; 
            margin-bottom: 0.2cm; 
            display: inline-block;
            padding: 0cm; 
            box-sizing: border-box;
            line-height: 1.5;
        }
        .page {
            width: 21cm; 
            padding-right: 10cm;
            padding-top: 0.4cm; 
            box-sizing: border-box;
            word-wrap: break-word; 
            word-break: break-all;
            white-space: normal;
        }
        .page-break {
            page-break-after: always;
            word-wrap: break-word; 
            word-break: break-all;
            white-space: normal;
        }
    </style>
</head>
<body>
    <div class="page">
        @foreach ($labels['sender_receiver'] as $index => $value)
            <div class="label">
                {{ $labels['sender_receiver'][$index] }} <br>
                {{ $labels['client_name'][$index] }} <br>
                {{ $labels['client_address'][$index] }} <br>
                {{ $labels['pic_name'][$index] }} ({{ $labels['pic_phone'][$index] }}) <br>
                {{ $labels['remarks'][$index] }}
            </div>
            @if (($index + 1) % 8 === 0)
                <div class="page-break"></div>
            @endif
        @endforeach
    </div>
</body>
</html>

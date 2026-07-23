<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Supply Request QR Code</title>
    <style>
        * { font-family: 'Archivo', system-ui, sans-serif; box-sizing: border-box; }
        body { margin: 0; padding: 40px; text-align: center; color: #14262b; }
        .poster { max-width: 520px; margin: 0 auto; border: 2px solid #0c3c7c; border-radius: 16px; padding: 36px 30px; }
        .office { font-size: 13px; letter-spacing: .08em; text-transform: uppercase; color: #64777c; }
        .heading { font-size: 30px; font-weight: 800; margin: 6px 0 4px; color: #0c3c7c; }
        .sub { font-size: 15px; color: #64777c; margin-bottom: 24px; }
        .qr { display: inline-block; padding: 14px; border: 1px solid #d8dde5; border-radius: 12px; }
        .steps { text-align: left; max-width: 340px; margin: 26px auto 0; font-size: 14px; line-height: 1.9; }
        .note { margin-top: 22px; font-size: 12px; color: #64777c; line-height: 1.5; }
        .url { margin-top: 10px; font-size: 11px; color: #9aa8a3; word-break: break-all; }
        .toolbar { margin-bottom: 20px; }
        @media print { .toolbar { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()" style="padding:8px 20px; cursor:pointer;">🖨 Print</button>
        <a href="{{ route('requests.index') }}" style="margin-left:10px; padding:8px 16px; display:inline-block; text-decoration:none; border:1px solid #666; border-radius:4px; color:#333;">← Back</a>
    </div>

    <div class="poster">
        <div class="office">SPDRRMO Sorsogon</div>
        <div class="heading">Request Supplies</div>
        <div class="sub">Scan to submit a supply request</div>

        <div class="qr">{!! QrCode::size(240)->margin(0)->generate($url) !!}</div>

        <div class="steps">
            <div>1. Connect to the office Wi-Fi.</div>
            <div>2. Scan the code with your phone camera.</div>
            <div>3. Select your name and the items you need.</div>
            <div>4. Wait for the administrator's approval.</div>
        </div>

        <div class="note">
            This page only works on the office network.<br>
            Requests are released only after approval.
        </div>
        <div class="url">{{ $url }}</div>
    </div>
</body>
</html>
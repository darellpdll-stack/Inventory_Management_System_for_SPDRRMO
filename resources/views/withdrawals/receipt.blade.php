<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $withdrawal->id }}</title>
    <style>
        @page { size: 56mm auto; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { width: 56mm; padding: 3mm; font-family: 'Courier New', monospace; font-size: 10px; line-height: 1.35; color: #000; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .office { font-size: 11px; font-weight: bold; }
        .sub { font-size: 8px; }
        .divider { border-top: 1px dashed #000; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { font-size: 9px; vertical-align: top; padding: 1px 0; }
        .qty { text-align: right; white-space: nowrap; padding-left: 4px; }
        .meta td { font-size: 9px; }
        .meta td:first-child { width: 34%; }
        .sign { margin-top: 18px; }
        .sign-line { border-top: 1px solid #000; margin-top: 20px; font-size: 8px; text-align: center; }
        .foot { font-size: 8px; margin-top: 8px; }
        .toolbar { margin-bottom: 8px; }
        @media print { .toolbar { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()" style="font-size:12px; padding:6px 14px; cursor:pointer;">🖨 Print</button>
        <a href="{{ route('withdrawals.index') }}" style="font-size:12px; margin-left:6px;">← Back</a>
    </div>

    <div class="center">
        <div class="office">SPDRRMO</div>
        <div class="sub">Sorsogon Provincial DRRM Office</div>
        <div class="sub">Supply Withdrawal Slip</div>
    </div>

    <div class="divider"></div>

    <table class="meta">
        <tr><td>No.</td><td>{{ str_pad($withdrawal->id, 5, '0', STR_PAD_LEFT) }}</td></tr>
        <tr><td>Date</td><td>{{ $withdrawal->date_withdrawn->format('M d, Y') }}</td></tr>
        <tr><td>Released to</td><td class="bold">{{ $withdrawal->withdrawn_by }}</td></tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr><td class="bold">Item</td><td class="qty bold">Qty</td></tr>
        @foreach($withdrawal->items as $line)
        <tr>
            <td>{{ $line->supplyItem->description ?? '—' }}</td>
            <td class="qty">{{ $line->quantity }} {{ $line->supplyItem->unit ?? '' }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr><td class="bold">Total items</td><td class="qty bold">{{ $withdrawal->items->sum('quantity') }}</td></tr>
    </table>

    @if($withdrawal->remark)
        <div class="divider"></div>
        <div style="font-size:8px;">Remark: {{ $withdrawal->remark }}</div>
    @endif

    <div class="sign">
        <div class="sign-line">Received by</div>
    </div>

    <div class="divider"></div>
    <div class="center foot">
        Recorded by {{ $withdrawal->recordedBy->name ?? '—' }}<br>
        {{ $withdrawal->created_at->format('M d, Y g:i A') }}
    </div>
</body>
</html>
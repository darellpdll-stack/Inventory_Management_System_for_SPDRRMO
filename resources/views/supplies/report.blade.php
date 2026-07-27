<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Report of Physical Count — {{ $category->name }}</title>
    <style>
        * { font-family: 'Times New Roman', serif; box-sizing: border-box; }
        body { margin: 25px; color: #000; font-size: 11px; }
        .head { text-align: center; margin-bottom: 8px; }
        .head .gov { font-size: 12px; }
        .head .office { font-weight: bold; font-size: 12px; }
        .head .title { font-size: 12px; margin-top: 1px; }
        .subhead { display: flex; gap: 40px; justify-content: center; margin: 6px 0 9px; font-weight: bold; }
        .subhead .cat { font-weight: bold; }
        .officer { font-size: 10px; margin: 4px 0 6px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 3px 4px; font-size: 10px; vertical-align: top; overflow-wrap: break-word; }
        th { background: #f0f0f0; text-align: center; font-weight: bold; }
        td.c { text-align: center; }
        td.r { text-align: right; }
        .neg { color: #b00; }
        .toolbar { margin-bottom: 14px; }
        @page { size: landscape; }
        @media print { .toolbar { display: none; } body { margin: 0; } tr { page-break-inside: avoid; } thead { display: table-header-group; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()" style="padding:8px 20px; cursor:pointer;">🖨 Print / Save as PDF</button>
        <a href="{{ route('supplies.report.options') }}" style="margin-left:10px; padding:8px 16px; display:inline-block; text-decoration:none; border:1px solid #666; border-radius:4px; color:#333;">← Back</a>
    </div>

    <div class="head">
        <div class="gov">PROVINCIAL GOVERNMENT OF SORSOGON</div>
        <div class="office">SORSOGON PROVINCIAL DISASTER RISK REDUCTION AND MANAGEMENT OFFICE</div>
        <div class="title">REPORT OF PHYSICAL COUNT OF INVENTORIES</div>
    </div>

    <div class="subhead">
        <span class="cat">{{ strtoupper($category->name) }}</span>
        <span>As of {{ now()->format('F d, Y') }}</span>
    </div>

    <div class="officer">
        For which <u><b>{{ \App\Models\Setting::get('officer_name', 'CECILIA V. HAINTO') }}, {{ \App\Models\Setting::get('officer_title', 'Supervising Administrative Officer (Administrative Officer IV)') }}</b></u> of <u><b>{{ \App\Models\Setting::get('office_name', 'SORSOGON PROVINCIAL DISASTER RISK REDUCTION AND MANAGEMENT OFFICE') }}</b></u> is accountable, having assumed such accountability on {{ now()->format('F d, Y') }}.
    </div>

    <table>
        <colgroup>
            <col style="width:5%"><col style="width:24%"><col style="width:8%"><col style="width:7%">
            <col style="width:10%"><col style="width:9%"><col style="width:9%">
            <col style="width:8%"><col style="width:10%"><col style="width:10%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">Article</th>
                <th rowspan="2">Description</th>
                <th rowspan="2">Stock No.</th>
                <th rowspan="2">Unit of<br>Measure</th>
                <th rowspan="2">Unit Value</th>
                <th rowspan="2">Balance<br>Per Card</th>
                <th rowspan="2">On Hand<br>Per Count</th>
                <th colspan="2">Shortage / Overage</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>Qty</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                @php
                    $balance = (int) $item->balance_per_card;
                    $onHand  = (int) $item->on_hand_per_count;
                    $diffQty = $onHand - $balance;
                    $diffVal = $diffQty * (float) $item->unit_value;
                @endphp
                <tr>
                    <td class="c">{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="c">{{ $item->stock_no ?? '' }}</td>
                    <td class="c">{{ $item->unit }}</td>
                    <td class="r">{{ $item->unit_value ? number_format($item->unit_value, 2) : '' }}</td>
                    <td class="c">{{ $balance }}</td>
                    <td class="c">{{ $onHand }}</td>
                    <td class="c {{ $diffQty < 0 ? 'neg' : '' }}">{{ $diffQty == 0 ? '—' : $diffQty }}</td>
                    <td class="r {{ $diffVal < 0 ? 'neg' : '' }}">{{ $diffQty == 0 ? '—' : number_format($diffVal, 2) }}</td>
                    <td>{{ $item->remarks ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="c">No items in this category.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
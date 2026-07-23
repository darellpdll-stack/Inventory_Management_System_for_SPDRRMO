<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Inventory Report — {{ $category->name }}</title>
    <style>
        * { font-family: 'Times New Roman', serif; box-sizing: border-box; }
        body { margin: 30px; color: #000; font-size: 12px; }
        .title-block { text-align: center; margin-bottom: 14px; }
        .title-block .line1 { font-weight: bold; font-size: 13px; text-transform: uppercase; }
        .title-block .line2 { font-weight: bold; font-size: 12px; text-transform: uppercase; margin-top: 2px; text-decoration: underline; }
        .title-block .line3 { font-size: 12px; font-style: italic; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 2px 4px; font-size: 10px; vertical-align: top; line-height: 1.2; overflow-wrap: break-word; }
        th { background: #f0f0f0; text-align: center !important; font-weight: bold; vertical-align: middle; }
        td.num { text-align: right; }
        td.center { text-align: center; }
        td.person { font-weight: bold; }
        .grand td { font-weight: bold; }
        .signatures { display: flex; gap: 60px; margin-top: 45px; font-size: 12px; }
        .sig-block { width: 240px; }
        .sig-label { margin-bottom: 18px; }
        .sig-name-wrap { text-align: center; }
        .sig-name {
            display: inline-block;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            padding-bottom: 1px;
        }
        .sig-title { text-align: center; font-size: 11px; }
        .toolbar { margin-bottom: 16px; }
        .toolbar a, .toolbar button { font-size: 14px; }
        @page { size: landscape; }
        @media print {
            .toolbar { display: none; }
            body { margin: 0; }
            .signatures { margin-top: 20px; page-break-inside: avoid; break-inside: avoid; }
            .sig-block { page-break-inside: avoid; break-inside: avoid; }
            tr { page-break-inside: avoid; break-inside: avoid; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()" style="padding:8px 20px; cursor:pointer;">🖨 Print / Save as PDF</button>
        <a href="{{ route('property.report.options') }}" style="margin-left:10px; padding:8px 16px; display:inline-block; text-decoration:none; border:1px solid #666; border-radius:4px; color:#333;">← Back</a>
    </div>

    <div class="title-block">
        <div class="line1">Inventory of {{ $request->type === 'semi-expendable' ? 'Semi-expendable' : 'Expendable' }} Property</div>
        <div class="line2">{{ $category->name }}</div>
        <div class="line3">As of {{ now()->format('F d, Y') }}</div>
    </div>

    <table>
        <colgroup>
        <col style="width:18%">  {{-- Accountable Person --}}
        <col style="width:30%">  {{-- Description --}}
        <col style="width:14%">  {{-- Property No. --}}
        <col style="width:8%">   {{-- Unit of Measure --}}
        <col style="width:10%">  {{-- Unit Value --}}
        <col style="width:8%">   {{-- On Hand --}}
        <col style="width:12%">  {{-- Remarks --}}
    </colgroup>
        <thead>
            <tr>
                <th>ACCOUNTABLE PERSON</th>
                <th>DESCRIPTION</th>
                <th>{{ $request->type === 'semi-expendable' ? 'Semi-expendable' : 'Expendable' }}<br>Property No.</th>
                <th>Unit of<br>Measure</th>
                <th>Unit Value</th>
                <th>On Hand Per<br>Count (Quantity)</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
    @forelse($items as $item)
        @php
            $numbers = $item->quantity >= 10
                ? [$item->propertyNoRange()]
                : $item->propertyNoList();
        @endphp
        @foreach($numbers as $index => $no)
        <tr>
            <td class="person">{{ $index === 0 ? strtoupper($item->personnel->name ?? '—') : '' }}</td>
            <td>{{ $index === 0 ? $item->description : '' }}</td>
            <td class="center">{{ $no }}</td>
            <td class="center">{{ $index === 0 ? $item->unit : '' }}</td>
            <td class="num">{{ $item->unit_value ? number_format($item->unit_value, 2) : '' }}</td>
            <td class="center">{{ $index === 0 ? $item->on_hand_per_count : '' }}</td>
            <td class="center">{{ $index === 0 ? ($item->remarks ?? '') : '' }}</td>
        </tr>
        @endforeach
    @empty
        <tr><td colspan="8" class="center">No items in this category.</td></tr>
    @endforelse
    <tr class="grand">
    <td>GRAND TOTAL</td>
    <td></td>
    <td></td>
    <td></td>
    <td class="num">₱{{ number_format($grandTotal, 2) }}</td>
    <td></td>
    <td></td>
</tr>
</tbody>
    </table>

    <div class="signatures">
    <div class="sig-block">
        <div class="sig-label">Prepared by:</div>
        <div class="sig-name-wrap"><span class="sig-name">CECILIA V. HAINTO</span></div>
        <div class="sig-title">Supervising Administrative Officer</div>
    </div>
    <div class="sig-block">
        <div class="sig-label">Approved by:</div>
        <div class="sig-name-wrap"><span class="sig-name">RADEN D. DIMAANO, C.E.</span></div>
        <div class="sig-title">PGDH-PDRRMO</div>
    </div>
</div>
</body>
</html>
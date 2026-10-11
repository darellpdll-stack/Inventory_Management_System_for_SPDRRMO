<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Borrower's Form — {{ $borrowing->borrowNo() }}</title>
    <style>
        @page { size: A4 portrait; margin: 18mm 16mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #000; line-height: 1.4; }
        .sheet { max-width: 180mm; margin: 0 auto; }
        .center { text-align: center; }
        .head { margin-bottom: 14pt; }
        .office { font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; }
        .sub { font-size: 11pt; }
        .doc-title { font-size: 14pt; font-weight: bold; text-transform: uppercase; margin-top: 12pt; letter-spacing: 1px; }
        .ref { font-size: 11pt; margin-top: 2pt; }
        hr { border: none; border-top: 1.5px solid #000; margin: 10pt 0 14pt; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 14pt; }
        .meta td { font-size: 11pt; padding: 3pt 0; vertical-align: top; }
        .meta td.label { width: 30%; }
        .meta td.value { border-bottom: 1px solid #000; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16pt; }
        table.items th, table.items td { border: 1px solid #000; padding: 5pt 6pt; font-size: 11pt; vertical-align: top; }
        table.items th { background: #eee; font-weight: bold; text-align: left; }
        .num { text-align: center; white-space: nowrap; }
        .undertaking { font-size: 10.5pt; text-align: justify; margin-bottom: 26pt; }
        .signs { width: 100%; border-collapse: collapse; margin-top: 10pt; }
        .signs td { width: 50%; padding: 0 10pt; vertical-align: bottom; font-size: 11pt; }
        .sign-line { border-top: 1px solid #000; padding-top: 3pt; text-align: center; }
        .sign-role { font-size: 9.5pt; text-align: center; }
        .sign-gap { height: 42pt; }
        .toolbar { margin-bottom: 14pt; }
        @media print { .toolbar { display: none; } }
    </style>
</head>
<body>
<div class="sheet">
    <div class="toolbar">
        <button onclick="window.print()" style="font-size:12px; padding:6px 14px; cursor:pointer;">🖨 Print</button>
        <a href="{{ route('borrowings.show', $borrowing) }}" style="font-size:12px; margin-left:6px;">← Back</a>
    </div>

    <div class="head center">
        <div class="sub">Republic of the Philippines</div>
        <div class="sub">Province of Sorsogon</div>
        <div class="office">Provincial Disaster Risk Reduction and Management Office</div>
        <div class="doc-title">Borrower's Form</div>
        <div class="ref">{{ $borrowing->borrowNo() }}</div>
    </div>

    <hr>

    <table class="meta">
        <tr>
            <td class="label">Name of Borrower</td>
            <td class="value">{{ $borrowing->personnel->name ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Position / Office</td>
            <td class="value">{{ $borrowing->personnel->position ?? '' }}@if($borrowing->personnel?->department) — {{ $borrowing->personnel->department }}@endif</td>
        </tr>
        <tr>
            <td class="label">Contact Number</td>
            <td class="value">{{ $borrowing->personnel->contact_number ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Date Needed</td>
            <td class="value">{{ $borrowing->date_needed->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="label">Expected Date of Return</td>
            <td class="value">{{ $borrowing->date_due?->format('F d, Y') ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Purpose</td>
            <td class="value">{{ $borrowing->purpose ?? '' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="num" style="width: 8%;">No.</th>
                <th style="width: 40%;">Description</th>
                <th style="width: 30%;">Property No.</th>
                <th class="num" style="width: 10%;">Qty</th>
                <th class="num" style="width: 12%;">Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($borrowing->groupedUnits() as $group)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td>{{ $group->item->description ?? '' }}</td>
                    <td>{{ $group->numbers->count() ? $group->numbers->implode(', ') : '—' }}</td>
                    <td class="num">{{ $group->quantity }}</td>
                    <td class="num">{{ $group->item->unit ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="undertaking">
        I hereby acknowledge receipt of the equipment described above, in good and serviceable
        condition, and undertake to use the same solely for the purpose stated herein. I further
        undertake to return the equipment to the Sorsogon Provincial Disaster Risk Reduction and
        Management Office on or before the expected date of return, in the same condition in which
        it was received, and to be held accountable for any loss or damage sustained while the
        equipment is in my custody.
    </div>

    <table class="signs">
        <tr>
            <td class="sign-gap"></td>
            <td class="sign-gap"></td>
        </tr>
        <tr>
            <td>
                <div class="sign-line">{{ Str::upper($borrowing->personnel->name ?? '') }}</div>
                <div class="sign-role">Borrower</div>
            </td>
            <td>
                <div class="sign-line">{{ Str::upper($borrowing->releasedBy->name ?? '') }}</div>
                <div class="sign-role">Released by</div>
            </td>
        </tr>
        <tr>
            <td class="sign-gap"></td>
            <td class="sign-gap"></td>
        </tr>
        <tr>
            <td>
                <div class="sign-line">@if($borrowing->date_released){{ $borrowing->date_released->format("F d, Y") }}@else&nbsp;@endif</div>
                <div class="sign-role">Date Released</div>
            </td>
            <td>
                <div class="sign-line">&nbsp;</div>
                <div class="sign-role">Date and Time Returned / Received by</div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>

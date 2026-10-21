<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Tags — {{ $property->description }}</title>
    <style>
        /* Label sheet geometry — adjust these three to match your sticker paper */
        :root {
            --label-width: 60mm;
            --label-height: 30mm;
            --label-gap: 2mm;
        }

        @page { size: A4 portrait; margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #000; }

        .toolbar { padding: 10px 0 16px; }
        .toolbar button, .toolbar a {
            font-size: 13px; padding: 6px 14px; cursor: pointer; margin-right: 6px;
        }
        .hint { font-size: 12px; color: #555; margin-top: 6px; }

        .sheet { display: flex; flex-wrap: wrap; gap: var(--label-gap); }

        .label {
            width: var(--label-width);
            height: var(--label-height);
            border: 1px dashed #bbb;
            padding: 2mm;
            display: flex;
            align-items: center;
            gap: 2mm;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .label .qr { flex: 0 0 auto; line-height: 0; }
        .label .qr svg { display: block; width: 24mm; height: 24mm; }

        .label .text { flex: 1 1 auto; min-width: 0; }
        .office { font-size: 7pt; font-weight: bold; letter-spacing: .2px; }
        .no { font-size: 11pt; font-weight: bold; font-family: 'Courier New', monospace; margin: 1mm 0; }
        .desc {
            font-size: 7pt; line-height: 1.15;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden;
        }

        @media print {
            .toolbar { display: none; }
            /* the dashed guides are for lining the sheet up on screen only */
            .label { border: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">🖨 Print</button>
        <a href="{{ route('property.show', $property) }}">← Back</a>
        <div class="hint">
            {{ count($numbers) }} {{ Str::plural('tag', count($numbers)) }} ·
            the dashed outlines are a screen guide and will not print ·
            label size is set at the top of this page's stylesheet
        </div>
        <div class="hint">
            <strong>Print these from the live site, not from localhost.</strong>
            Each code carries a link back to this system, and a phone can only
            follow a link it can actually reach.
        </div>
    </div>

    <div class="sheet">
        @foreach($numbers as $number)
            <div class="label">
                {{-- the code carries a link, so a phone camera opens the record --}}
                <div class="qr">{!! \App\Support\QrTag::svg(\App\Support\QrTag::url($number)) !!}</div>
                <div class="text">
                    <div class="office">SPDRRMO</div>
                    <div class="no">{{ $number }}</div>
                    <div class="desc">{{ $property->description }}</div>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>

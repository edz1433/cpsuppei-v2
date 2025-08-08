<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sticker PDF</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        td {
            width: 50%;
            height: 20%; /* 100% / 5 rows = 20% */
            padding: 0;
            margin: 0;
        }

        img {
            width: 100%;
            height: 20%;
            object-fit: contain;
            display: block;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @php
        $colors = ['lightgreen', 'green', 'yellow'];
        $stickers = [];
        
        foreach ($colors as $color) {
            for ($i = 0; $i < 10; $i++) {
                $stickers[] = 'template/img/' . $color . '.png';
            }
        }

        $chunks = array_chunk($stickers, 10); // 10 per page (2 cols x 5 rows)
    @endphp

    @foreach ($chunks as $index => $chunk)
        <table>
            @foreach (array_chunk($chunk, 2) as $row)
                <tr>
                    <td>
                        @if(isset($row[0]))
                            <img src="{{ asset($row[0]) }}" alt="Sticker">
                        @endif
                    </td>
                    <td>
                        @if(isset($row[1]))
                            <img src="{{ asset($row[1]) }}" alt="Sticker">
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        :root { color-scheme: light; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 24px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        .meta { font-size: 12px; color: #6b7280; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; font-size: 12px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; font-weight: 700; }
        @media print {
            body { margin: 12mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="display:flex; justify-content:flex-end; gap:8px; margin-bottom:12px;">
        <button type="button" onclick="window.print()" style="padding:8px 12px; font-size:12px; cursor:pointer;">Print</button>
        <button type="button" onclick="window.close()" style="padding:8px 12px; font-size:12px; cursor:pointer;">Close</button>
    </div>

    <h1>{{ $title }}</h1>
    <div class="meta">Generated: {{ now()->format('M d, Y H:i') }}</div>

    <div style="border: 1px solid #e5e7eb; background: #f9fafb; padding: 10px 12px; border-radius: 6px; margin-bottom: 16px;">
        <div style="font-size: 12px; font-weight: 700; margin-bottom: 6px;">Filters</div>
        <div style="font-size: 12px; color: #374151; display: grid; grid-template-columns: 140px 1fr; gap: 4px 12px;">
            <div style="color:#6b7280;">Search</div>
            <div>{{ $criteria['search'] ?? '—' }}</div>

            <div style="color:#6b7280;">Date From</div>
            <div>{{ $criteria['date_from'] ?? '—' }}</div>

            <div style="color:#6b7280;">Date To</div>
            <div>{{ $criteria['date_to'] ?? '—' }}</div>

            @if($includeShift)
                <div style="color:#6b7280;">Shift Filter</div>
                <div>{{ $criteria['shift_filter'] ?? 'all' }}</div>
            @endif

            <div style="color:#6b7280;">Sort</div>
            <div>{{ $criteria['sort'] ?? '—' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 160px;">Date</th>
                <th style="width: 220px;">Hatchery Man</th>
                <th>Machine</th>
                @if($includeShift)
                    <th style="width: 120px;">Shift</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['hatchery_man'] }}</td>
                    <td>{{ $row['machine'] }}</td>
                    @if($includeShift)
                        <td>{{ $row['shift'] ?? 'N/A' }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $includeShift ? 4 : 3 }}" style="text-align:center; color:#6b7280;">No results</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', () => {
            window.print();
        });
    </script>
</body>
</html>

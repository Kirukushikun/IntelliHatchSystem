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
        h2 { font-size: 14px; margin: 18px 0 8px; }
        .meta { font-size: 12px; color: #6b7280; margin-bottom: 16px; }
        .card { border: 1px solid #e5e7eb; background: #f9fafb; padding: 10px 12px; border-radius: 6px; margin-bottom: 16px; }
        .grid { font-size: 12px; color: #374151; display: grid; grid-template-columns: 140px 1fr; gap: 4px 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; font-size: 12px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; font-weight: 700; }
        .yes { color: #065f46; font-weight: 700; }
        .no { color: #991b1b; font-weight: 700; }
        .muted { color: #6b7280; }
        @media print {
            body { margin: 12mm; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="display:flex; justify-content:flex-end; gap:8px; margin-bottom:12px;">
        <button type="button" onclick="window.print()" style="padding:8px 12px; font-size:12px; cursor:pointer;">Print</button>
        <button type="button" onclick="window.close()" style="padding:8px 12px; font-size:12px; cursor:pointer;">Close</button>
    </div>

    <h1>{{ $title }}</h1>
    <div class="meta">Generated: {{ now()->format('d M, Y g:i A') }}</div>

    <div class="card">
        <div style="font-size: 12px; font-weight: 700; margin-bottom: 6px;">Filters</div>
        <div class="grid">
            <div class="muted">Search</div>
            <div>{{ $criteria['search'] ?? '—' }}</div>

            <div class="muted">Date From</div>
            <div>{{ $criteria['date_from'] ?? '—' }}</div>

            <div class="muted">Date To</div>
            <div>{{ $criteria['date_to'] ?? '—' }}</div>
        </div>
    </div>

    @php
        $shiftLabels = [
            '1st Shift' => '1st',
            '2nd Shift' => '2nd',
            '3rd Shift' => '3rd',
        ];
    @endphp

    @forelse($report as $i => $userReport)
        <h2>{{ $userReport['user_name'] ?? 'Unknown' }}</h2>
        <div class="meta">
            Applicable Days: {{ $userReport['totals']['applicable_days'] ?? 0 }}
            | Required Shifts: {{ $userReport['totals']['required_shifts'] ?? 0 }}
            | Submitted: {{ $userReport['totals']['submitted_shifts'] ?? 0 }}
            | Missing: {{ $userReport['totals']['missing_shifts'] ?? 0 }}
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 120px;">Date</th>
                    <th style="width: 70px; text-align:center;">1st</th>
                    <th style="width: 70px; text-align:center;">2nd</th>
                    <th style="width: 70px; text-align:center;">3rd</th>
                    <th>Missing</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($userReport['days'] ?? []) as $dayRow)
                    @php
                        $day = (string) ($dayRow['day'] ?? '');
                        $missing = (array) ($dayRow['missing'] ?? []);
                        $has = (array) ($dayRow['has'] ?? []);
                    @endphp
                    <tr>
                        <td>{{ $day !== '' ? \Carbon\Carbon::parse($day)->format('d M, Y') : '—' }}</td>
                        <td style="text-align:center;">{!! ($has['1st Shift'] ?? false) ? '<span class="yes">Yes</span>' : '<span class="no">No</span>' !!}</td>
                        <td style="text-align:center;">{!! ($has['2nd Shift'] ?? false) ? '<span class="yes">Yes</span>' : '<span class="no">No</span>' !!}</td>
                        <td style="text-align:center;">{!! ($has['3rd Shift'] ?? false) ? '<span class="yes">Yes</span>' : '<span class="no">No</span>' !!}</td>
                        <td>
                            @if(empty($missing))
                                <span class="muted">—</span>
                            @else
                                {{ implode(', ', array_map(fn ($s) => $shiftLabels[$s] ?? $s, $missing)) }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#6b7280;">No applicable days in range</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($i < count($report) - 1)
            <div class="page-break"></div>
        @endif
    @empty
        <div style="text-align:center; color:#6b7280;">No users found for the given search.</div>
    @endforelse

    <script>
        window.addEventListener('load', () => {
            window.print();
        });
    </script>
</body>
</html>

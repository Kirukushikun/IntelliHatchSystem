<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PASGAR Score — {{ $form->date_submitted ? $form->date_submitted->format('d M Y') : 'Detail' }}</title>
    <style>
        :root { color-scheme: light; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 24px; color: #111827; font-size: 13px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 14px; margin: 20px 0 8px; color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .meta { font-size: 12px; color: #6b7280; margin-bottom: 16px; }
        .info-grid { display: grid; grid-template-columns: 180px 1fr; gap: 4px 16px; margin-bottom: 16px; }
        .info-grid .label { color: #6b7280; font-weight: 600; font-size: 12px; }
        .info-grid .value { font-size: 12px; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
        .summary-box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; text-align: center; }
        .summary-box .label { font-size: 10px; color: #6b7280; text-transform: uppercase; font-weight: 600; }
        .summary-box .value { font-size: 20px; font-weight: 700; color: #111827; }
        .issues-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px; }
        .issue-box { border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px 10px; display: flex; justify-content: space-between; font-size: 12px; }
        .issue-box .label { color: #6b7280; }
        .issue-box .count { font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; font-size: 11px; text-align: left; }
        th { background: #f9fafb; font-weight: 700; }
        td.center { text-align: center; }
        .check { color: #16a34a; }
        .cross { color: #dc2626; }
        .system-notice { margin-top: 24px; padding: 12px; border: 1px solid #d1d5db; background: #f9fafb; border-radius: 6px; font-size: 11px; color: #6b7280; text-align: center; }
        .footer { margin-top: 16px; font-size: 10px; color: #9ca3af; text-align: center; }
        @media print {
            body { margin: 12mm; }
            .no-print { display: none !important; }
            .page-break-inside { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="display:flex; justify-content:flex-end; gap:8px; margin-bottom:12px;">
        <button type="button" onclick="window.print()" style="padding:8px 12px; font-size:12px; cursor:pointer;">Print / Save as PDF</button>
        <button type="button" onclick="window.close()" style="padding:8px 12px; font-size:12px; cursor:pointer;">Close</button>
    </div>

    <h1>PASGAR Score Report</h1>
    <div class="meta">
        Generated: {{ now()->format('d M, Y g:i A') }}
        &nbsp;|&nbsp; Printed by: {{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}
    </div>

    <h2>Basic Information</h2>
    <div class="info-grid">
        <div class="label">Date Submitted:</div>
        <div class="value">{{ $form->date_submitted ? $form->date_submitted->format('d M, Y g:i A') : 'N/A' }}</div>

        <div class="label">Personnel:</div>
        <div class="value">{{ $inputs['personnel_name'] ?? 'N/A' }}</div>

        <div class="label">Hatch Date:</div>
        <div class="value">{{ $inputs['hatch_date'] ?? 'N/A' }}</div>

        <div class="label">Time Started:</div>
        <div class="value">{{ $inputs['time_started'] ?? 'N/A' }}</div>

        <div class="label">Time Finished:</div>
        <div class="value">{{ $inputs['time_finished'] ?? 'N/A' }}</div>
    </div>

    <h2>Registry Information</h2>
    <div class="info-grid">
        <div class="label">PS Number:</div>
        <div class="value">{{ $inputs['machine_info']['name'] ?? 'N/A' }}</div>

        <div class="label">House Number:</div>
        <div class="value">{{ $houseNumber }}</div>

        <div class="label">Incubator:</div>
        <div class="value">{{ $incubatorName }}</div>

        <div class="label">Hatcher:</div>
        <div class="value">{{ $hatcherName }}</div>
    </div>

    <h2>Scoring Summary</h2>
    <div class="summary-grid">
        <div class="summary-box">
            <div class="label">PASGAR Average</div>
            <div class="value">{{ $inputs['pasgar_average_scoring'] ?? 'N/A' }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Avg Chick Weight</div>
            <div class="value">{{ $inputs['average_chick_weight'] ?? 'N/A' }}g</div>
        </div>
        <div class="summary-box">
            <div class="label">Total Samples</div>
            <div class="value">{{ $inputs['total_samples'] ?? count($inputs['samples'] ?? []) }}</div>
        </div>
    </div>

    <h2>Issue Totals</h2>
    <div class="issues-grid">
        <div class="issue-box"><span class="label">Low Reflex</span><span class="count">{{ $inputs['low_reflex_alertness_qty'] ?? 0 }}</span></div>
        <div class="issue-box"><span class="label">Navel Issue</span><span class="count">{{ $inputs['navel_issue_qty'] ?? 0 }}</span></div>
        <div class="issue-box"><span class="label">Leg Issue</span><span class="count">{{ $inputs['leg_issue_qty'] ?? 0 }}</span></div>
        <div class="issue-box"><span class="label">Beak Issue</span><span class="count">{{ $inputs['beak_issue_qty'] ?? 0 }}</span></div>
        <div class="issue-box"><span class="label">Belly Bloated</span><span class="count">{{ $inputs['belly_bloated_qty'] ?? 0 }}</span></div>
        <div class="issue-box"><span class="label">Vaccination</span><span class="count">{{ $inputs['vaccination_issue_qty'] ?? 0 }}</span></div>
    </div>

    @if(!empty($inputs['samples']))
    <h2>DOP Samples</h2>
    <div class="page-break-inside">
        <table>
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
                    <th style="width:80px;">Weight (g)</th>
                    <th class="center" style="width:60px;">Reflex</th>
                    <th class="center" style="width:60px;">Navel</th>
                    <th class="center" style="width:60px;">Leg</th>
                    <th class="center" style="width:60px;">Beak</th>
                    <th class="center" style="width:60px;">Belly</th>
                    <th class="center" style="width:60px;">Vaccine</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inputs['samples'] as $index => $sample)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $sample['chick_weight'] ?? 'N/A' }}</td>
                        <td class="center">{!! !empty($sample['low_reflex_alertness']) ? '<span class="cross">&#10005;</span>' : '<span class="check">&#10003;</span>' !!}</td>
                        <td class="center">{!! !empty($sample['navel_issue']) ? '<span class="cross">&#10005;</span>' : '<span class="check">&#10003;</span>' !!}</td>
                        <td class="center">{!! !empty($sample['leg_issue']) ? '<span class="cross">&#10005;</span>' : '<span class="check">&#10003;</span>' !!}</td>
                        <td class="center">{!! !empty($sample['beak_issue']) ? '<span class="cross">&#10005;</span>' : '<span class="check">&#10003;</span>' !!}</td>
                        <td class="center">{!! !empty($sample['belly_bloated']) ? '<span class="cross">&#10005;</span>' : '<span class="check">&#10003;</span>' !!}</td>
                        <td class="center">{!! !empty($sample['vaccination_issue']) ? '<span class="cross">&#10005;</span>' : '<span class="check">&#10003;</span>' !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <h2>DOP Information</h2>
    <div class="info-grid">
        <div class="label">DOP Prime Qty:</div>
        <div class="value">{{ $inputs['dop_prime_qty'] ?? 'N/A' }}</div>

        <div class="label">DOP Prime Box Numbers:</div>
        <div class="value">{{ $inputs['dop_prime_box_numbers'] ?? 'N/A' }}</div>

        <div class="label">DOP JR Prime Qty:</div>
        <div class="value">{{ $inputs['dop_jr_prime_qty'] ?? 'N/A' }}</div>

        <div class="label">DOP JR Prime Box Numbers:</div>
        <div class="value">{{ $inputs['dop_jr_prime_box_numbers'] ?? 'N/A' }}</div>
    </div>

    <div class="info-grid" style="margin-top: 12px;">
        <div class="label">QC Personnel:</div>
        <div class="value">{{ $inputs['qc_personnel'] ?? 'N/A' }}</div>
    </div>

    <div class="system-notice">
        This is a system-generated document. No signature is required.
    </div>

    <div class="footer">
        IntelliHatchSystem &mdash; Confidential
    </div>

    <script>
        window.addEventListener('load', () => {
            window.print();
        });
    </script>
</body>
</html>

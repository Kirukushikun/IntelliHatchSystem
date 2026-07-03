<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PASGAR Score Summary — {{ $form->date_submitted ? $form->date_submitted->format('d M Y') : 'Detail' }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 24px; color: #111827; font-size: 13px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 14px; margin: 20px 0 8px; color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .meta { font-size: 12px; color: #6b7280; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info-table td { padding: 4px 8px; font-size: 12px; vertical-align: top; }
        .info-table .label { color: #6b7280; font-weight: 600; width: 180px; }
        .summary-table td { border: 1px solid #e5e7eb; padding: 10px; text-align: center; }
        .summary-table .label { font-size: 10px; color: #6b7280; text-transform: uppercase; font-weight: 600; }
        .summary-table .value { font-size: 20px; font-weight: 700; color: #111827; }
        .issues-table td { border: 1px solid #e5e7eb; padding: 6px 10px; font-size: 12px; }
        .issues-table .label { color: #6b7280; }
        .issues-table .count { font-weight: 700; text-align: right; }
        .system-notice { margin-top: 24px; padding: 12px; border: 1px solid #d1d5db; background: #f9fafb; font-size: 11px; color: #6b7280; text-align: center; }
        .footer { margin-top: 16px; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <h1>PASGAR Score Summary Report</h1>
    <div class="meta">
        Generated: {{ now()->format('d M, Y g:i A') }}
        &nbsp;|&nbsp; Printed by: {{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}
    </div>

    <h2>Basic Information</h2>
    <table class="info-table">
        <tr>
            <td class="label">Date Submitted:</td>
            <td>{{ $form->date_submitted ? $form->date_submitted->format('d M, Y g:i A') : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Personnel:</td>
            <td>{{ $inputs['personnel_name'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Hatch Date:</td>
            <td>{{ $inputs['hatch_date'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Time Started:</td>
            <td>{{ $inputs['time_started'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Time Finished:</td>
            <td>{{ $inputs['time_finished'] ?? 'N/A' }}</td>
        </tr>
    </table>

    <h2>Registry Information</h2>
    <table class="info-table">
        <tr>
            <td class="label">PS Number:</td>
            <td>{{ $inputs['machine_info']['name'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">House Number:</td>
            <td>{{ $houseNumber }}</td>
        </tr>
        <tr>
            <td class="label">Incubator:</td>
            <td>{{ $incubatorName }}</td>
        </tr>
        <tr>
            <td class="label">Hatcher:</td>
            <td>{{ $hatcherName }}</td>
        </tr>
    </table>

    <h2>Scoring Summary</h2>
    <table class="summary-table">
        <tr>
            <td>
                <div class="label">PASGAR Average</div>
                <div class="value">{{ $inputs['pasgar_average_scoring'] ?? 'N/A' }}</div>
            </td>
            <td>
                <div class="label">Avg Chick Weight</div>
                <div class="value">{{ $inputs['average_chick_weight'] ?? 'N/A' }}g</div>
            </td>
            <td>
                <div class="label">Total Samples</div>
                <div class="value">{{ $inputs['total_samples'] ?? count($inputs['samples'] ?? []) }}</div>
            </td>
        </tr>
    </table>

    <h2>Issue Totals</h2>
    <table class="issues-table">
        <tr>
            <td class="label">Low Reflex</td>
            <td class="count">{{ $inputs['low_reflex_alertness_qty'] ?? 0 }}</td>
            <td class="label">Navel Issue</td>
            <td class="count">{{ $inputs['navel_issue_qty'] ?? 0 }}</td>
            <td class="label">Leg Issue</td>
            <td class="count">{{ $inputs['leg_issue_qty'] ?? 0 }}</td>
        </tr>
        <tr>
            <td class="label">Beak Issue</td>
            <td class="count">{{ $inputs['beak_issue_qty'] ?? 0 }}</td>
            <td class="label">Belly Bloated</td>
            <td class="count">{{ $inputs['belly_bloated_qty'] ?? 0 }}</td>
            <td class="label">Vaccination</td>
            <td class="count">{{ $inputs['vaccination_issue_qty'] ?? 0 }}</td>
        </tr>
    </table>

    <div class="system-notice">
        This is a system-generated document. No signature is required.
    </div>

    <div class="footer">
        IntelliHatchSystem &mdash; Confidential
    </div>
</body>
</html>

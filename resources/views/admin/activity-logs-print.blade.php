<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs — IntelliHatch</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5730a;
        }

        .header-left h1 {
            font-size: 18px;
            font-weight: 700;
            color: #e5730a;
        }

        .header-left p {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }

        .header-right {
            text-align: right;
            font-size: 10px;
            color: #555;
        }

        .filters {
            background: #f8f8f8;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 14px;
            font-size: 10px;
            color: #555;
        }

        .filters span { margin-right: 16px; }

        .summary {
            margin-bottom: 12px;
            font-size: 11px;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        thead tr {
            background: #e5730a;
            color: #fff;
        }

        thead th {
            padding: 6px 8px;
            text-align: left;
            font-weight: 600;
            white-space: nowrap;
        }

        tbody tr:nth-child(even) { background: #fafafa; }
        tbody tr:hover { background: #fff3e8; }

        tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .action-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 99px;
            font-size: 9px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-gray   { background: #f3f4f6; color: #374151; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-emerald{ background: #d1fae5; color: #064e3b; }
        .badge-yellow { background: #fef9c3; color: #713f12; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }

        .deleted-user { font-style: italic; color: #999; }

        .footer {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #888;
            display: flex;
            justify-content: space-between;
        }

        .print-btn {
            position: fixed;
            top: 16px;
            right: 16px;
            background: #e5730a;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .print-btn:hover { background: #c86009; }

        @media print {
            .print-btn { display: none; }
            body { padding: 8px; }
            .header-left h1 { font-size: 14px; }
            table { font-size: 9px; }
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>

    <div class="header">
        <div class="header-left">
            <h1>Activity Logs</h1>
            <p>IntelliHatch System — Audit Trail</p>
        </div>
        <div class="header-right">
            <div>Generated: {{ now()->format('d M Y, H:i:s') }}</div>
            <div>Total records: {{ $logs->count() }}</div>
        </div>
    </div>

    @if ($filters['search'] || $filters['dateFrom'] || $filters['dateTo'] || $filters['actionFilter'])
        <div class="filters">
            <strong>Active Filters:</strong>
            @if ($filters['search'])
                <span>Search: "{{ $filters['search'] }}"</span>
            @endif
            @if ($filters['actionFilter'])
                <span>Action: {{ str_replace('_', ' ', ucfirst($filters['actionFilter'])) }}</span>
            @endif
            @if ($filters['dateFrom'] || $filters['dateTo'])
                <span>
                    Date:
                    {{ $filters['dateFrom'] ? \Carbon\Carbon::parse($filters['dateFrom'])->format('d M Y') : 'All' }}
                    —
                    {{ $filters['dateTo'] ? \Carbon\Carbon::parse($filters['dateTo'])->format('d M Y') : 'Now' }}
                </span>
            @endif
        </div>
    @endif

    <div class="summary">Showing {{ $logs->count() }} {{ Str::plural('record', $logs->count()) }}</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Time</th>
                <th>User</th>
                <th>Role</th>
                <th>Action</th>
                <th>Description</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $index => $log)
                @php
                    $isDestructive = str_contains($log->action, 'deleted') || str_contains($log->action, 'disabled') || str_contains($log->action, 'deactivated');
                    $isCreate      = str_contains($log->action, 'created');
                    $isUpdate      = str_contains($log->action, 'updated') || str_contains($log->action, 'changed') || str_contains($log->action, 'reset') || str_contains($log->action, 'enabled') || str_contains($log->action, 'activated');
                    $badgeClass    = match(true) {
                        $log->action === 'login'  => 'badge-green',
                        $log->action === 'logout' => 'badge-gray',
                        $isDestructive            => 'badge-red',
                        $isCreate                 => 'badge-emerald',
                        $isUpdate                 => 'badge-yellow',
                        default                   => 'badge-blue',
                    };
                    $roleMap = [0 => 'Superadmin', 1 => 'Admin', 2 => 'User'];
                    $role    = $log->user ? ($roleMap[(int) $log->user->user_type] ?? '—') : '—';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="white-space:nowrap">{{ $log->created_at->format('d M Y') }}</td>
                    <td style="white-space:nowrap">{{ $log->created_at->format('H:i:s') }}</td>
                    <td>
                        @if ($log->user)
                            <div>{{ $log->user->full_name }}</div>
                            <div style="color:#888;font-size:9px">{{ $log->user->username }}</div>
                        @else
                            <span class="deleted-user">Deleted user</span>
                        @endif
                    </td>
                    <td>{{ $role }}</td>
                    <td>
                        <span class="action-badge {{ $badgeClass }}">
                            {{ str_replace('_', ' ', ucfirst($log->action)) }}
                        </span>
                    </td>
                    <td>{{ $log->description }}</td>
                    <td style="white-space:nowrap">{{ $log->ip_address ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:20px;color:#999">No activity logs found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span>IntelliHatch System — Confidential</span>
        <span>Exported by {{ Auth::user()->full_name }} on {{ now()->format('d M Y, H:i') }}</span>
    </div>

</body>
</html>

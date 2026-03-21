<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ActivityLogExportController extends Controller
{
    public function csv(Request $request): Response|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = $this->buildQuery($request);

        $filename = 'activity-logs-' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // BOM for UTF-8 Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, ['Date', 'Time', 'User', 'Username', 'Role', 'Action', 'Description', 'IP Address']);

            $query->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    $roleMap = [0 => 'Superadmin', 1 => 'Admin', 2 => 'User'];
                    $role    = $log->user ? ($roleMap[(int) $log->user->user_type] ?? 'Unknown') : 'Deleted';

                    fputcsv($handle, [
                        $log->created_at->format('d M Y'),
                        $log->created_at->format('H:i:s'),
                        $log->user ? $log->user->full_name : 'Deleted user',
                        $log->user ? $log->user->username : '—',
                        $role,
                        str_replace('_', ' ', ucfirst($log->action)),
                        $log->description,
                        $log->ip_address ?? '—',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function pdf(Request $request): \Illuminate\View\View
    {
        $logs = $this->buildQuery($request)
            ->orderBy('created_at', 'desc')
            ->get();

        $filters = [
            'search'       => $request->get('search', ''),
            'dateFrom'     => $request->get('dateFrom', ''),
            'dateTo'       => $request->get('dateTo', ''),
            'actionFilter' => $request->get('actionFilter', ''),
        ];

        return view('admin.activity-logs-print', compact('logs', 'filters'));
    }

    protected function buildQuery(Request $request): Builder
    {
        $search       = trim($request->get('search', ''));
        $actionFilter = $request->get('actionFilter', '');
        $userFilter   = $request->get('userFilter', '');
        $dateFrom     = $request->get('dateFrom', '');
        $dateTo       = $request->get('dateTo', '');
        $sortField    = in_array($request->get('sortField', 'created_at'), ['created_at', 'action'])
                            ? $request->get('sortField', 'created_at')
                            : 'created_at';
        $sortDir      = $request->get('sortDirection', 'desc') === 'asc' ? 'asc' : 'desc';

        $query = ActivityLog::query()->with('user');

        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('action', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function (Builder $u) use ($search) {
                      $u->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($actionFilter) {
            $query->where('action', $actionFilter);
        }

        if ($userFilter) {
            $query->where('user_id', $userFilter);
        }

        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        } elseif ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->orderBy($sortField, $sortDir);
    }
}

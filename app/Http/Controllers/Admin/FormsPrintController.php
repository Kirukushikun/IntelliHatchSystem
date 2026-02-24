<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormsPrintController extends Controller
{
    public function blowerAirHatcher(Request $request)
    {
        $formType = FormType::where('form_name', 'Hatcher Blower Air Speed Monitoring')->firstOrFail();

        return $this->printForms(
            $request,
            $formType,
            'hatcher',
            'hatcher-machines',
            'hatcherName',
            false
        );
    }

    public function blowerAirIncubator(Request $request)
    {
        $formType = FormType::where('form_name', 'Incubator Blower Air Speed Monitoring')->firstOrFail();

        return $this->printForms(
            $request,
            $formType,
            'incubator',
            'incubator-machines',
            'incubatorName',
            false
        );
    }

    public function incubatorRoutine(Request $request)
    {
        $formType = FormType::where('form_name', 'Incubator Routine Checklist Per Shift')->firstOrFail();

        return $this->printForms(
            $request,
            $formType,
            'incubator',
            'incubator-machines',
            'incubatorName',
            true
        );
    }

    public function incubatorRoutinePerformance(Request $request)
    {
        $formType = FormType::where('form_name', 'Incubator Routine Checklist Per Shift')->firstOrFail();

        $search = (string) $request->query('search', '');
        $dateFrom = (string) $request->query('dateFrom', '');
        $dateTo = (string) $request->query('dateTo', '');
        $userId = (int) $request->query('user_id', 0);

        if (trim($search) === '') {
            return view('admin.print.incubator-routine-performance', [
                'title' => 'Incubator Routine Performance Report',
                'criteria' => $this->emptyPerformanceCriteria($dateFrom, $dateTo),
                'requiredShifts' => ['1st Shift', '2nd Shift', '3rd Shift'],
                'report' => [],
            ]);
        }

        [$start, $end] = $this->parsePerformanceRange($dateFrom, $dateTo);
        $requiredShifts = ['1st Shift', '2nd Shift', '3rd Shift'];

        $applicableDays = $this->loadApplicableDays((int) $formType->id, $start, $end);
        $users = $this->loadPerformanceUsers($userId, $search);
        $userIds = $users->pluck('id')->map(static fn ($id) => (int) $id)->values()->all();

        $submissions = $this->loadPerformanceSubmissions((int) $formType->id, $start, $end, $userIds, $requiredShifts);
        $report = $this->buildPerformanceReport($users, $applicableDays, $requiredShifts, $submissions);
        $criteria = $this->buildPerformanceCriteria($search, $dateFrom, $dateTo, $start, $end, $userId, $users);

        return view('admin.print.incubator-routine-performance', [
            'title' => 'Incubator Routine Performance Report',
            'criteria' => $criteria,
            'requiredShifts' => $requiredShifts,
            'report' => $report,
        ]);
    }

    private function emptyPerformanceCriteria(string $dateFrom, string $dateTo): array
    {
        return [
            'search' => '—',
            'date_from' => $dateFrom !== '' ? $dateFrom : '—',
            'date_to' => $dateTo !== '' ? $dateTo : '—',
        ];
    }

    private function parsePerformanceRange(string $dateFrom, string $dateTo): array
    {
        if ($dateFrom !== '' && $dateTo !== '') {
            return [Carbon::parse($dateFrom)->startOfDay(), Carbon::parse($dateTo)->endOfDay()];
        }

        if ($dateFrom !== '') {
            return [Carbon::parse($dateFrom)->startOfDay(), Carbon::parse($dateFrom)->endOfDay()];
        }

        if ($dateTo !== '') {
            return [Carbon::parse($dateTo)->startOfDay(), Carbon::parse($dateTo)->endOfDay()];
        }

        $now = now();
        return [$now->copy()->startOfMonth()->startOfDay(), $now->copy()->endOfMonth()->endOfDay()];
    }

    private function loadApplicableDays(int $formTypeId, Carbon $start, Carbon $end): array
    {
        return DB::table('forms')
            ->where('form_type_id', $formTypeId)
            ->whereNotNull('date_submitted')
            ->whereBetween('date_submitted', [$start, $end])
            ->selectRaw('DATE(date_submitted) as day')
            ->distinct()
            ->orderBy('day')
            ->pluck('day')
            ->map(static fn ($d) => (string) $d)
            ->values()
            ->all();
    }

    private function loadPerformanceUsers(int $userId, string $search)
    {
        $usersQuery = User::query()
            ->where('user_type', 1)
            ->where('is_disabled', false);

        if ($userId > 0) {
            $usersQuery->where('id', $userId);
        } elseif ($search !== '') {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%');
            });
        }

        return $usersQuery
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
    }

    private function loadPerformanceSubmissions(int $formTypeId, Carbon $start, Carbon $end, array $userIds, array $requiredShifts): array
    {
        if ($userIds === []) {
            return [];
        }

        $rows = DB::table('forms')
            ->where('form_type_id', $formTypeId)
            ->whereNotNull('date_submitted')
            ->whereBetween('date_submitted', [$start, $end])
            ->whereIn('uploaded_by', $userIds)
            ->selectRaw("uploaded_by as user_id, DATE(date_submitted) as day, JSON_UNQUOTE(JSON_EXTRACT(form_inputs, '$.shift')) as shift")
            ->get();

        $submissions = [];
        foreach ($rows as $row) {
            $userId = (int) ($row->user_id ?? 0);
            $day = (string) ($row->day ?? '');
            $shift = (string) ($row->shift ?? '');
            if ($userId <= 0 || $day === '' || $shift === '') {
                continue;
            }

            if (!in_array($shift, $requiredShifts, true)) {
                continue;
            }

            $submissions[$userId][$day][$shift] = true;
        }

        return $submissions;
    }

    private function buildPerformanceReport($users, array $applicableDays, array $requiredShifts, array $submissions): array
    {
        $report = [];

        foreach ($users as $user) {
            $userId = (int) $user->id;
            $userName = trim(((string) $user->first_name) . ' ' . ((string) $user->last_name));
            if ($userName === '') {
                $userName = 'Unknown';
            }

            $days = [];
            $submittedCount = 0;
            $missingCount = 0;

            foreach ($applicableDays as $day) {
                $has = [];
                foreach ($requiredShifts as $shift) {
                    $has[$shift] = (bool) ($submissions[$userId][$day][$shift] ?? false);
                    if ($has[$shift]) {
                        $submittedCount++;
                    }
                }

                $missing = array_values(array_filter($requiredShifts, static fn ($s) => !($has[$s] ?? false)));
                $missingCount += count($missing);

                $days[] = [
                    'day' => $day,
                    'has' => $has,
                    'missing' => $missing,
                ];
            }

            $report[] = [
                'user_id' => $userId,
                'user_name' => $userName,
                'days' => $days,
                'totals' => [
                    'applicable_days' => count($applicableDays),
                    'required_shifts' => count($applicableDays) * count($requiredShifts),
                    'submitted_shifts' => $submittedCount,
                    'missing_shifts' => $missingCount,
                ],
            ];
        }

        return $report;
    }

    private function buildPerformanceCriteria(string $search, string $dateFrom, string $dateTo, Carbon $start, Carbon $end, int $userId, $users): array
    {
        $resolvedSearchLabel = $search !== '' ? $search : '—';
        if ($userId > 0 && $users->count() === 1) {
            $onlyUser = $users->first();
            $resolvedName = trim(((string) ($onlyUser->first_name ?? '')) . ' ' . ((string) ($onlyUser->last_name ?? '')));
            if ($resolvedName !== '') {
                $resolvedSearchLabel = $resolvedName;
            }
        }

        return [
            'search' => $resolvedSearchLabel,
            'date_from' => $dateFrom !== '' ? $dateFrom : $start->format('Y-m-d'),
            'date_to' => $dateTo !== '' ? $dateTo : $end->format('Y-m-d'),
        ];
    }

    public function hatcherySullair(Request $request)
    {
        $formType = FormType::where('form_name', 'Hatchery Sullair Air Compressor Weekly PMS Checklist')->firstOrFail();

        $search = (string) $request->query('search', '');
        $dateFrom = (string) $request->query('dateFrom', '');
        $dateTo = (string) $request->query('dateTo', '');
        $sullairNumberFilter = (string) $request->query('sullairNumberFilter', '');
        $sortField = (string) $request->query('sortField', 'date_submitted');
        $sortDirection = strtolower((string) $request->query('sortDirection', 'desc'));

        if (!in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        $allowedSortFields = ['date_submitted'];
        if (!in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'date_submitted';
        }

        $query = Form::query()
            ->where('form_type_id', $formType->id)
            ->whereNotNull('date_submitted')
            ->with(['user']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                })
                    ->orWhere(function ($subQ) use ($search) {
                        $subQ->where('form_inputs', 'like', '%"sullair_number"%')
                            ->where('form_inputs', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($dateFrom !== '' && $dateTo !== '') {
            $query->whereBetween('date_submitted', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        } elseif ($dateFrom !== '') {
            $query->whereDate('date_submitted', '>=', $dateFrom);
        } elseif ($dateTo !== '') {
            $query->whereDate('date_submitted', '<=', $dateTo);
        }

        if ($sullairNumberFilter !== '') {
            $query->where('form_inputs', 'like', '%"sullair_number":"' . $sullairNumberFilter . '"%');
        }

        $query->orderBy('date_submitted', $sortDirection);

        $forms = $query->get();

        $rows = $forms->map(function ($form) {
            $inputs = is_array($form->form_inputs) ? $form->form_inputs : (json_decode((string) $form->form_inputs, true) ?: []);

            $hatcheryMan = $form->user ? trim(($form->user->first_name ?? '') . ' ' . ($form->user->last_name ?? '')) : 'Unknown';
            $hatcheryMan = $hatcheryMan !== '' ? $hatcheryMan : 'Unknown';

            $sullairNumber = isset($inputs['sullair_number']) && $inputs['sullair_number'] !== '' ? (string) $inputs['sullair_number'] : 'N/A';

            return [
                'date' => $form->date_submitted ? $form->date_submitted->format('d M, Y g:i A') : 'N/A',
                'hatchery_man' => $hatcheryMan,
                'machine' => $sullairNumber,
            ];
        })->values();

        $sortDirectionLabel = $sortDirection === 'asc' ? 'Ascending' : 'Descending';

        $criteria = [
            'search' => $search !== '' ? $search : '—',
            'date_from' => $dateFrom !== '' ? $dateFrom : '—',
            'date_to' => $dateTo !== '' ? $dateTo : '—',
            'sort' => 'Date Submitted (' . $sortDirectionLabel . ')',
            'sullair_number_filter' => $sullairNumberFilter !== '' ? $sullairNumberFilter : 'all',
        ];

        return view('admin.print.forms', [
            'title' => $formType->form_name,
            'rows' => $rows,
            'includeShift' => false,
            'criteria' => $criteria,
        ]);
    }

    private function printForms(
        Request $request,
        FormType $formType,
        string $machineInputKey,
        string $machineTable,
        string $machineNameColumn,
        bool $includeShift
    ) {
        $search = (string) $request->query('search', '');
        $dateFrom = (string) $request->query('dateFrom', '');
        $dateTo = (string) $request->query('dateTo', '');
        $shiftFilter = (string) $request->query('shiftFilter', 'all');
        $sortField = (string) $request->query('sortField', 'date_submitted');
        $sortDirection = strtolower((string) $request->query('sortDirection', 'desc'));

        if (!in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        $query = Form::query()
            ->where('form_type_id', $formType->id)
            ->whereNotNull('date_submitted')
            ->with(['user']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                })
                    ->orWhere(function ($subQ) use ($search) {
                        $subQ->where('form_inputs', 'like', '%"machine_info":%')
                            ->where('form_inputs', 'like', '%"name":%' . $search . '%');
                    });
            });
        }

        if ($dateFrom !== '' && $dateTo !== '') {
            $query->whereBetween('date_submitted', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        } elseif ($dateFrom !== '') {
            $query->whereDate('date_submitted', '>=', $dateFrom);
        } elseif ($dateTo !== '') {
            $query->whereDate('date_submitted', '<=', $dateTo);
        }

        if ($includeShift && $shiftFilter !== '' && $shiftFilter !== 'all') {
            $query->where(function ($q) use ($shiftFilter) {
                $q->where('form_inputs', 'like', '%"' . $shiftFilter . '"%')
                    ->orWhere('form_inputs', 'like', '%' . $shiftFilter . '%');
            });
        }

        $allowedSortFields = ['date_submitted'];
        if ($includeShift) {
            $allowedSortFields[] = 'shift';
        }

        if (!in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'date_submitted';
        }

        if ($includeShift && $sortField === 'shift') {
            $query->orderByRaw("JSON_EXTRACT(form_inputs, '$.shift') {$sortDirection}");
        } else {
            $query->orderBy('date_submitted', $sortDirection);
        }

        $forms = $query->get();

        $rows = $forms->map(function ($form) use ($includeShift, $machineInputKey, $machineTable, $machineNameColumn) {
            $inputs = is_array($form->form_inputs) ? $form->form_inputs : (json_decode((string) $form->form_inputs, true) ?: []);

            $hatcheryMan = $form->user ? trim(($form->user->first_name ?? '') . ' ' . ($form->user->last_name ?? '')) : 'Unknown';
            $hatcheryMan = $hatcheryMan !== '' ? $hatcheryMan : 'Unknown';

            $machineName = 'N/A';

            if (isset($inputs['machine_info']['name']) && $inputs['machine_info']['name'] !== '') {
                $machineName = (string) $inputs['machine_info']['name'];
            } elseif (isset($inputs[$machineInputKey]) && $inputs[$machineInputKey] !== '') {
                $machineId = $inputs[$machineInputKey];
                $machine = DB::table($machineTable)->where('id', $machineId)->first();
                if ($machine && isset($machine->{$machineNameColumn})) {
                    $machineName = (string) $machine->{$machineNameColumn};
                }
            }

            $row = [
                'date' => $form->date_submitted ? $form->date_submitted->format('d M, Y g:i A') : 'N/A',
                'hatchery_man' => $hatcheryMan,
                'machine' => $machineName,
            ];

            if ($includeShift) {
                $row['shift'] = (string) ($inputs['shift'] ?? 'N/A');
            }

            return $row;
        })->values();

        $sortFieldLabels = [
            'date_submitted' => 'Date Submitted',
            'shift' => 'Shift',
        ];
        $sortDirectionLabel = $sortDirection === 'asc' ? 'Ascending' : 'Descending';
        $sortFieldLabel = $sortFieldLabels[$sortField] ?? 'Date Submitted';

        $criteria = [
            'search' => $search !== '' ? $search : '—',
            'date_from' => $dateFrom !== '' ? $dateFrom : '—',
            'date_to' => $dateTo !== '' ? $dateTo : '—',
            'sort' => $sortFieldLabel . ' (' . $sortDirectionLabel . ')',
        ];

        if ($includeShift) {
            $criteria['shift_filter'] = $shiftFilter !== '' ? $shiftFilter : 'all';
        }

        return view('admin.print.forms', [
            'title' => $formType->form_name,
            'rows' => $rows,
            'includeShift' => $includeShift,
            'criteria' => $criteria,
        ]);
    }
}

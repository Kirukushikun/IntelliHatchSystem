<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormType;
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

    public function hatcherySullair(Request $request)
    {
        $formType = FormType::where('form_name', 'Hatchery Sullair Air Compressor Weekly PMS Checklist')->firstOrFail();

        $search = (string) $request->query('search', '');
        $dateFrom = (string) $request->query('dateFrom', '');
        $dateTo = (string) $request->query('dateTo', '');
        $hatcheryManFilter = (string) $request->query('hatcheryManFilter', '');
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

        if ($hatcheryManFilter !== '') {
            $query->where('uploaded_by', (int) $hatcheryManFilter);
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
                'date' => $form->date_submitted ? $form->date_submitted->format('M d, Y H:i') : 'N/A',
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
            'hatchery_man_filter' => $hatcheryManFilter !== '' ? $hatcheryManFilter : 'all',
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
                'date' => $form->date_submitted ? $form->date_submitted->format('M d, Y H:i') : 'N/A',
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

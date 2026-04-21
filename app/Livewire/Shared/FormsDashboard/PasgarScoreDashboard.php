<?php

namespace App\Livewire\Shared\FormsDashboard;

use App\Models\Form;
use App\Models\FormType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PasgarScoreDashboard extends Component
{
    public int $typeId;
    public string $search = '';
    public string $sortField = 'date_submitted';
    public string $sortDirection = 'desc';
    public int $page = 1;
    public int $perPage = 10;
    public string $dateFrom = '';
    public string $dateTo = '';
    public bool $showFilterDropdown = false;
    public bool $showModal = false;
    public bool $showPhotoModal = false;
    public bool $showDeleteModal = false;
    public ?int $selectedFormId = null;
    public ?int $formToDelete = null;
    public array $formPhotos = [];
    public string $selectedPhotoField = '';
    public array $selectedPhotos = [];
    public int $currentPhotoIndex = 0;

    public ?FormType $formType = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'date_submitted'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        $this->formType = FormType::where('form_name', 'PASGAR Score')->first();

        if (! $this->formType) {
            abort(404, 'PASGAR Score form type not found');
        }

        $this->typeId = $this->formType->id;
        $this->page = (int) request()->query('page', 1);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
        $this->page = 1;
    }

    public function updatingSearch(): void
    {
        $this->page = 1;
    }

    public function updatingDateFrom(): void
    {
        if ($this->dateFrom && $this->dateTo && $this->dateFrom > $this->dateTo) {
            $this->dateTo = '';
        }
        $this->page = 1;
    }

    public function updatingDateTo(): void
    {
        if ($this->dateTo && $this->dateFrom && $this->dateTo < $this->dateFrom) {
            $this->dateTo = '';
        }
        $this->page = 1;
    }

    public function updatingPerPage(): void
    {
        $this->page = 1;
    }

    public function toggleFilterDropdown(): void
    {
        $this->showFilterDropdown = ! $this->showFilterDropdown;
    }

    public function resetFilters(): void
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->page = 1;
        $this->showFilterDropdown = false;
    }

    public function getPaginationData()
    {
        $query = $this->baseQuery();

        $query->orderBy($this->sortField, $this->sortDirection);

        $forms = $query->paginate($this->perPage, ['*'], 'page', $this->page);

        $currentPage = $forms->currentPage();
        $lastPage = $forms->lastPage();

        $this->page = $currentPage;

        if ($lastPage <= 3) {
            $startPage = 1;
            $endPage = $lastPage;
        } elseif ($currentPage == 1) {
            $startPage = 1;
            $endPage = min(3, $lastPage);
        } elseif ($currentPage == $lastPage) {
            $startPage = max(1, $lastPage - 2);
            $endPage = $lastPage;
        } else {
            $startPage = max(1, $currentPage - 1);
            $endPage = min($lastPage, $currentPage + 1);
        }

        $pages = [];
        for ($i = $startPage; $i <= $endPage; $i++) {
            $pages[] = $i;
        }

        return [
            'forms' => $forms,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
        ];
    }

    public function gotoPage($page): void
    {
        $page = (int) $page;

        $totalPages = $this->baseQuery()->paginate($this->perPage)->lastPage();

        if ($page < 1) {
            $page = 1;
        } elseif ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->page = $page;
    }

    protected function baseQuery(): Builder
    {
        $query = Form::with(['user', 'formType'])
            ->where('form_type_id', $this->typeId);

        $search = trim($this->search);
        $terms = $search !== '' ? preg_split('/\s+/', $search) : [];
        $terms = is_array($terms) ? array_values(array_filter($terms, static fn ($t) => is_string($t) && $t !== '')) : [];

        if ($search !== '') {
            $query->where(function ($q) use ($terms, $search) {
                // Search by personnel name in form_inputs
                $q->where('form_inputs', 'like', '%"personnel_name":"' . '%' . $search . '%' . '"%')
                    // Search by PS number via machine_info
                    ->orWhere(function ($subQ) use ($search) {
                        $subQ->where('form_inputs', 'like', '%"machine_info":%')
                            ->where('form_inputs', 'like', '%"name":%' . $search . '%');
                    })
                    // Search by QC personnel
                    ->orWhere('form_inputs', 'like', '%"qc_personnel":"' . '%' . $search . '%' . '"%');
            });
        }

        if ($this->dateFrom || $this->dateTo) {
            if ($this->dateFrom && $this->dateTo) {
                $query->whereBetween('date_submitted', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
            } elseif ($this->dateFrom) {
                $query->whereDate('date_submitted', '>=', $this->dateFrom);
            } elseif ($this->dateTo) {
                $query->whereDate('date_submitted', '<=', $this->dateTo);
            }
        }

        return $query;
    }

    #[Computed]
    public function selectedForm()
    {
        if (! $this->selectedFormId) {
            return null;
        }

        return Form::with(['user', 'formType'])->find($this->selectedFormId);
    }

    #[Computed]
    public function formData()
    {
        if (! $this->selectedFormId) {
            return [];
        }

        $freshForm = DB::table('forms')
            ->where('id', $this->selectedFormId)
            ->first();

        if ($freshForm && $freshForm->form_inputs) {
            return json_decode($freshForm->form_inputs, true) ?? [];
        }

        return [];
    }

    public function viewDetails(int $formId): void
    {
        $this->selectedFormId = $formId;
        $this->formPhotos = $this->getFormPhotos($formId);
        $this->currentPhotoIndex = 0;
        $this->showModal = true;
    }

    public function deleteForm(int $formId): void
    {
        $this->formToDelete = $formId;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        if (! $this->formToDelete) {
            return;
        }

        try {
            $form = Form::find($this->formToDelete);

            if (! $form) {
                $this->dispatch('showToast', message: 'Form not found.', type: 'error');
                return;
            }

            $form->delete();

            $this->showDeleteModal = false;
            $this->formToDelete = null;

            $this->dispatch('showToast', message: 'Form deleted successfully!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Error deleting form. Please try again.', type: 'error');
        }
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->formToDelete = null;
    }

    public function viewPhotos(string $field): void
    {
        $this->selectedPhotoField = $field;
        $this->selectedPhotos = $this->getFormPhotos($this->selectedFormId, $field);
        $this->showPhotoModal = true;
    }

    public function getPhotoCount(string $field): int
    {
        return count($this->getFormPhotos($this->selectedFormId, $field));
    }

    public function closePhotoModal(): void
    {
        $this->showPhotoModal = false;
        $this->selectedPhotoField = '';
        $this->selectedPhotos = [];
    }

    private function getFormPhotos(int $formId, ?string $field = null): array
    {
        $form = Form::find($formId);
        if (! $form) {
            return [];
        }

        $formData = is_array($form->form_inputs) ? $form->form_inputs : [];

        // If no specific field, return form_photo photos
        $photoFieldKey = $field ?: 'form_photo';

        if (isset($formData[$photoFieldKey]) && ! empty($formData[$photoFieldKey])) {
            $photoUrls = is_array($formData[$photoFieldKey]) ? $formData[$photoFieldKey] : [];

            $photos = [];
            foreach ($photoUrls as $url) {
                $photos[] = [
                    'id' => uniqid(),
                    'url' => $url,
                    'caption' => 'Photo',
                ];
            }

            return $photos;
        }

        return [];
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedFormId = null;
        $this->formPhotos = [];
    }

    public function printPasgarList(): void
    {
        $url = URL::temporarySignedRoute(
            'admin.print.forms.pasgar-score',
            now()->addMinutes(10),
            [
                'search' => $this->search,
                'dateFrom' => $this->dateFrom,
                'dateTo' => $this->dateTo,
                'sortField' => $this->sortField,
                'sortDirection' => $this->sortDirection,
            ]
        );

        $this->dispatch('openNewTab', url: $url);
    }

    public function printPasgarDetail(int $formId): void
    {
        $url = URL::temporarySignedRoute(
            'admin.print.forms.pasgar-score-detail',
            now()->addMinutes(10),
            ['form_id' => $formId]
        );

        $this->dispatch('openNewTab', url: $url);
    }

    public function render()
    {
        $paginationData = $this->getPaginationData();

        return view('livewire.shared.forms-dashboard.pasgar-score-dashboard', [
            'forms' => $paginationData['forms'],
            'pages' => $paginationData['pages'],
            'currentPage' => $paginationData['currentPage'],
            'lastPage' => $paginationData['lastPage'],
        ]);
    }
}

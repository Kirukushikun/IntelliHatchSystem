<?php

namespace App\Livewire\Shared\FormsDashboard;

use App\Models\Form;
use App\Models\FormType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class HatcherySullairDashboard extends Component
{
    use WithPagination;

    public string $search = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public bool $showFilterDropdown = false;

    public string $hatcheryManFilter = '';
    public string $sullairNumberFilter = '';

    public ?int $selectedFormId = null;
    public FormType $formType;
    public int $todayFormCount = 0;

    // Modal properties
    public bool $showModal = false;
    public bool $showPhotoModal = false;
    public array $selectedPhotos = [];
    public string $selectedPhotoField = '';
    public array $formPhotos = [];
    public int $currentPhotoIndex = 0;

    // Delete confirmation properties
    public bool $showDeleteModal = false;
    public ?int $formToDelete = null;

    public int $perPage = 10;
    public string $sortField = 'date_submitted';
    public string $sortDirection = 'desc';
    public int $page = 1;

    /** @var array<int,string> */
    public array $hatcheryMen = [];

    /** @var array<int,string> */
    public array $sullairNumbers = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'hatcheryManFilter' => ['except' => ''],
        'sullairNumberFilter' => ['except' => ''],
        'page' => ['except' => 1],
        'sortField' => ['except' => 'date_submitted'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        $this->formType = FormType::where('form_name', 'Hatchery Sullair Air Compressor Weekly PMS Checklist')->firstOrFail();
        $this->calculateTodayFormCount();

        $this->hatcheryMen = User::where('user_type', 1)
            ->where('is_disabled', false)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->id => $user->first_name . ' ' . $user->last_name];
            })
            ->toArray();

        $this->sullairNumbers = [
            'Sullair 1 (Inside Incubation Area)',
            'Sullair 2 (Maintenance Area)',
        ];
    }

    protected function calculateTodayFormCount(): void
    {
        $this->todayFormCount = Form::where('form_type_id', $this->formType->id)
            ->whereDate('date_submitted', now()->format('Y-m-d'))
            ->count();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedHatcheryManFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSullairNumberFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function quickFilterToday(): void
    {
        $today = now()->format('Y-m-d');

        if ($this->dateFrom === $today && $this->dateTo === $today) {
            $this->reset(['dateFrom', 'dateTo']);
        } else {
            $this->dateFrom = $today;
            $this->dateTo = $today;
        }

        $this->resetPage();
    }

    public function toggleFilterDropdown(): void
    {
        $this->showFilterDropdown = !$this->showFilterDropdown;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'dateFrom', 'dateTo', 'hatcheryManFilter', 'sullairNumberFilter']);
        $this->resetPage();
    }

    public function gotoPage($page): void
    {
        $page = (int) $page;

        $query = $this->baseQuery();

        $totalPages = $query->paginate($this->perPage)->lastPage();

        if ($page < 1) {
            $page = 1;
        } elseif ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->page = $page;
    }

    protected function baseQuery()
    {
        $query = Form::where('form_type_id', $this->formType->id)
            ->with(['user']);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->whereHas('user', function ($userQuery) {
                    $userQuery->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                    ->orWhere(function ($subQ) {
                        $subQ->where('form_inputs', 'like', '%"sullair_number"%')
                            ->where('form_inputs', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('date_submitted', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('date_submitted', '<=', $this->dateTo);
        }

        if ($this->hatcheryManFilter !== '') {
            $query->where('uploaded_by', (int) $this->hatcheryManFilter);
        }

        if ($this->sullairNumberFilter !== '') {
            $query->where('form_inputs', 'like', '%"sullair_number":"' . $this->sullairNumberFilter . '"%');
        }

        return $query;
    }

    #[Computed]
    public function selectedForm()
    {
        if (!$this->selectedFormId) {
            return null;
        }

        return Form::with(['user', 'formType'])->find($this->selectedFormId);
    }

    #[Computed]
    public function formData(): array
    {
        if (!$this->selectedFormId) {
            return [];
        }

        $freshForm = DB::table('forms')
            ->where('id', $this->selectedFormId)
            ->first();

        if ($freshForm && $freshForm->form_inputs) {
            return is_array($freshForm->form_inputs) ? $freshForm->form_inputs : (json_decode($freshForm->form_inputs, true) ?: []);
        }

        return [];
    }

    public function viewDetails($formId): void
    {
        $this->selectedFormId = (int) $formId;
        $this->formPhotos = $this->getFormPhotos($this->selectedFormId);
        $this->currentPhotoIndex = 0;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedFormId = null;
        $this->formPhotos = [];
    }

    public function viewPhotos(string $field): void
    {
        $this->selectedPhotoField = $field;
        $this->selectedPhotos = $this->getFormPhotos((int) $this->selectedFormId, $field);
        $this->showPhotoModal = true;
    }

    public function closePhotoModal(): void
    {
        $this->showPhotoModal = false;
        $this->selectedPhotoField = '';
        $this->selectedPhotos = [];
    }

    private function getFormPhotos(int $formId, string $field = null): array
    {
        try {
            $form = Form::findOrFail($formId);
            $formData = is_array($form->form_inputs) ? $form->form_inputs : [];

            $photos = [];

            if ($field !== null) {
                $value = $formData[$field] ?? [];
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    $value = is_array($decoded) ? $decoded : [];
                }

                if (is_array($value)) {
                    foreach ($value as $photo) {
                        if (is_string($photo)) {
                            $photos[] = ['url' => $photo, 'name' => $field];
                        } elseif (is_array($photo) && isset($photo['url'])) {
                            $photos[] = ['url' => $photo['url'], 'name' => $photo['name'] ?? $field];
                        }
                    }
                }

                return $photos;
            }

            foreach ($formData as $key => $value) {
                if (!is_string($key) || !str_ends_with($key, '_photos')) {
                    continue;
                }

                $fieldPhotos = $this->getFormPhotos($formId, $key);
                foreach ($fieldPhotos as $p) {
                    $photos[] = $p;
                }
            }

            return $photos;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getPhotoCount(string $field): int
    {
        if (!$this->selectedFormId) {
            return 0;
        }

        return count($this->getFormPhotos((int) $this->selectedFormId, $field));
    }

    public function deleteForm($formId): void
    {
        $this->formToDelete = (int) $formId;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        if ($this->formToDelete) {
            $form = Form::findOrFail($this->formToDelete);
            $formName = $form->formType->form_name ?? 'Unknown form';
            $form->delete();

            session()->flash('success', "Form '{$formName}' deleted successfully.");

            $this->cancelDelete();
        }
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->formToDelete = null;
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    protected function getForms()
    {
        $query = $this->baseQuery();

        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    protected function getPaginationData(): array
    {
        $forms = $this->getForms();

        $currentPage = $forms->currentPage();
        $lastPage = $forms->lastPage();

        if ($lastPage <= 3) {
            $startPage = 1;
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

    public function render()
    {
        $paginationData = $this->getPaginationData();

        return view('livewire.shared.forms-dashboard.hatchery-sullair-dashboard', [
            'forms' => $paginationData['forms'],
            'pages' => $paginationData['pages'],
            'currentPage' => $paginationData['currentPage'],
            'lastPage' => $paginationData['lastPage'],
            'formType' => $this->formType,
        ]);
    }
}

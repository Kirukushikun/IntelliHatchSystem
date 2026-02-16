<?php

namespace App\Livewire\Shared\FormsDashboard;

use App\Models\Form;
use App\Models\FormType;
use App\Models\Hatcher;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class BlowerAirHatcherDashboard extends Component
{
    use WithPagination;

    public string $search = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public bool $showFilterDropdown = false;
    
    public ?Form $selectedForm = null;
    public FormType $formType;
    public int $todayFormCount = 0;
    
    public int $perPage = 10;
    public string $sortField = 'date_submitted';
    public string $sortDirection = 'desc';
    public int $page = 1;

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'page' => ['except' => 1],
        'sortField' => ['except' => 'date_submitted'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $this->formType = FormType::where('form_name', 'Hatcher Blower Air Speed Monitoring')->firstOrFail();
        $this->calculateTodayFormCount();
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

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function quickFilterToday(): void
    {
        $today = now()->format('Y-m-d');
        
        // If already filtered to today, clear the filter
        if ($this->dateFrom === $today && $this->dateTo === $today) {
            $this->reset(['dateFrom', 'dateTo']);
        } else {
            // Otherwise, filter to today
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
        $this->reset(['search', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function gotoPage($page): void
    {
        $this->page = (int) $page;
    }

    public function viewDetails($formId): void
    {
        $this->selectedForm = Form::with('user')->findOrFail($formId);
    }

    public function closeDetails(): void
    {
        $this->selectedForm = null;
    }

    public function deleteForm($formId): void
    {
        $form = Form::findOrFail($formId);
        $formName = $form->formType->form_name ?? 'Unknown form';
        $form->delete();

        session()->flash('success', "Form '{$formName}' deleted successfully.");
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
        $query = Form::where('form_type_id', $this->formType->id)
            ->with(['user']);

        // Search filter
        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->whereHas('user', function ($userQuery) {
                    $userQuery->where('first_name', 'like', '%' . $this->search . '%')
                           ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('form_inputs->cfm_fan_reading', 'like', '%' . $this->search . '%')
                ->orWhere('form_inputs->cfm_fan_action_taken', 'like', '%' . $this->search . '%');
            });
        }

        // Date range filter
        if ($this->dateFrom) {
            $query->whereDate('date_submitted', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('date_submitted', '<=', $this->dateTo);
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    protected function getPaginationData()
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
        
        return view('livewire.shared.forms-dashboard.blower-air-hatcher-dashboard', [
            'forms' => $paginationData['forms'],
            'pages' => $paginationData['pages'],
            'currentPage' => $paginationData['currentPage'],
            'lastPage' => $paginationData['lastPage'],
            'formType' => $this->formType,
        ]);
    }
}

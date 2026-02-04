<?php

namespace App\Livewire\FormsDashboard;

use App\Models\Form;
use App\Models\FormType;
use App\Models\HatcheryUser;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class IncubatorRoutineDashboard extends Component
{
    use WithPagination;

    public int $typeId;
    public string $search = '';
    public string $sortField = 'date_submitted';
    public string $sortDirection = 'desc';

    public ?FormType $formType = null;
    public $currentPage = 1;
    public $lastPage = 1;
    public $pages = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'date_submitted'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        // Hardcode the incubator routine form type ID (assuming it's ID 1)
        $this->typeId = 1;
        $this->loadFormType();
    }

    protected function loadFormType(): void
    {
        $this->formType = FormType::find($this->typeId);
        
        if (!$this->formType) {
            abort(404, 'Form type not found');
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Form::with(['user', 'formType', 'incubator'])
            ->where('form_type_id', $this->typeId);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->whereHas('user', function ($subQ) {
                    $subQ->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('form_inputs', 'like', '%' . $this->search . '%')
                ->orWhere('date_submitted', 'like', '%' . $this->search . '%');
            });
        }

        $forms = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        // Calculate pagination data
        $this->currentPage = $forms->currentPage();
        $this->lastPage = $forms->lastPage();
        
        // Calculate the range of pages to show (max 3)
        if ($this->lastPage <= 3) {
            $startPage = 1;
            $endPage = $this->lastPage;
        } elseif ($this->currentPage == 1) {
            $startPage = 1;
            $endPage = min(3, $this->lastPage);
        } elseif ($this->currentPage == $this->lastPage) {
            $startPage = max(1, $this->lastPage - 2);
            $endPage = $this->lastPage;
        } else {
            $startPage = max(1, $this->currentPage - 1);
            $endPage = min($this->lastPage, $this->currentPage + 1);
        }
        
        $this->pages = [];
        for ($i = $startPage; $i <= $endPage; $i++) {
            $this->pages[] = $i;
        }

        return view('livewire.forms-dashboard.incubator-routine-dashboard', [
            'forms' => $forms,
        ]);
    }

    public function gotoPage($page): void
    {
        $page = (int) $page;
        
        // Validate page number
        $totalPages = Form::where('form_type_id', $this->typeId)
            ->paginate(15)
            ->lastPage();
        
        if ($page < 1) {
            $page = 1;
        } elseif ($page > $totalPages) {
            $page = $totalPages;
        }
        
        $this->setPage($page);
    }
}

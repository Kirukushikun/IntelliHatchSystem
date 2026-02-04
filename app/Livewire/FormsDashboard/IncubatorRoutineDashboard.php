<?php

namespace App\Livewire\FormsDashboard;

use App\Models\Form;
use App\Models\FormType;
use App\Models\HatcheryUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class IncubatorRoutineDashboard extends Component
{
    public int $typeId;
    public string $search = '';
    public string $sortField = 'date_submitted';
    public string $sortDirection = 'desc';
    public int $page = 1;
    public int $perPage = 15;
    public array $expandedHeaders = [];
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $shiftFilter = 'all';
    public bool $showFilterDropdown = false;

    public ?FormType $formType = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'date_submitted'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'shiftFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        // Hardcode the incubator routine form type ID (assuming it's ID 1)
        $this->typeId = 1;
        $this->loadFormType();
        $this->page = (int) request()->query('page', 1);
        
        // Debug: Log that component is mounting
        Log::info('IncubatorRoutineDashboard component mounted with typeId: ' . $this->typeId);
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

    public function updatingShiftFilter(): void
    {
        $this->page = 1;
    }

    public function toggleFilterDropdown(): void
    {
        $this->showFilterDropdown = !$this->showFilterDropdown;
    }

    public function resetFilters(): void
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->shiftFilter = 'all';
        $this->page = 1;
        $this->showFilterDropdown = false;
    }

    public function getPaginationData()
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

        // Apply date filter
        if ($this->dateFrom || $this->dateTo) {
            if ($this->dateFrom && $this->dateTo) {
                $query->whereBetween('date_submitted', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
            } elseif ($this->dateFrom) {
                $query->whereDate('date_submitted', '>=', $this->dateFrom);
            } elseif ($this->dateTo) {
                $query->whereDate('date_submitted', '<=', $this->dateTo);
            }
        }

        // Apply shift filter
        if ($this->shiftFilter !== 'all') {
            $query->where(function ($q) {
                $q->where('form_inputs', 'like', '%"' . $this->shiftFilter . '"%')
                  ->orWhere('form_inputs', 'like', '%' . $this->shiftFilter . '%');
            });
        }

        $forms = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'page', $this->page);
            
        $currentPage = $forms->currentPage();
        $lastPage = $forms->lastPage();
        
        // Sync the page property with the actual current page
        $this->page = $currentPage;
        
        // Calculate the range of pages to show (max 3)
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
        
        // Validate page number
        $totalPages = Form::where('form_type_id', $this->typeId)
            ->paginate($this->perPage)
            ->lastPage();
        
        if ($page < 1) {
            $page = 1;
        } elseif ($page > $totalPages) {
            $page = $totalPages;
        }
        
        $this->page = $page;
    }

    public function truncateText($text, $maxLength = 10, $headerKey = null)
    {
        // If header is expanded, show full text
        if ($headerKey && isset($this->expandedHeaders[$headerKey]) && $this->expandedHeaders[$headerKey]) {
            return $text;
        }
        
        // Check if text needs truncation
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        
        return substr($text, 0, $maxLength) . '...';
    }

    public function toggleHeader($headerKey): void
    {
        $this->expandedHeaders[$headerKey] = !($this->expandedHeaders[$headerKey] ?? false);
    }

    public function refresh(): void
    {
        // This method is called by wire:poll
        // The component will automatically re-render
    }

    public function render()
    {
        $paginationData = $this->getPaginationData();
        
        return view('livewire.forms-dashboard.incubator-routine-dashboard', [
            'forms' => $paginationData['forms'],
            'pages' => $paginationData['pages'],
            'currentPage' => $paginationData['currentPage'],
            'lastPage' => $paginationData['lastPage'],
        ]);
    }
}
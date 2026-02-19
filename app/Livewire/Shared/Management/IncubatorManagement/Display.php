<?php

namespace App\Livewire\Shared\Management\IncubatorManagement;

use Livewire\Component;
use App\Models\Incubator;

class Display extends Component
{
    public $search = '';
    public $perPage = 10;
    public $sortField = 'incubatorName';
    public $sortDirection = 'asc';
    public $page = 1;
    public $statusFilter = 'all'; // all, active, inactive
    public $dateFrom = ''; // Custom date range from
    public $dateTo = ''; // Custom date range to
    public $showFilterDropdown = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'statusFilter' => ['except' => 'all'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    protected $listeners = ['refreshIncubators' => '$refresh'];

    public function mount()
    {
        $this->search = request()->get('search', '');
        $this->page = request()->get('page', 1);
        $this->statusFilter = request()->get('statusFilter', 'all');
        $this->dateFrom = request()->get('dateFrom', '');
        $this->dateTo = request()->get('dateTo', '');
    }

    public function updatingSearch()
    {
        $this->page = 1;
    }

    public function updatingStatusFilter()
    {
        $this->page = 1;
    }

    public function updatingDateFrom()
    {
        $this->page = 1;
    }

    public function updatingDateTo()
    {
        $this->page = 1;
    }

    public function updatingPerPage(): void
    {
        $this->page = 1;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->page = 1;
    }

    public function toggleFilterDropdown()
    {
        $this->showFilterDropdown = !$this->showFilterDropdown;
    }

    public function closeFilterDropdown()
    {
        $this->showFilterDropdown = false;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter', 'dateFrom', 'dateTo']);
        $this->page = 1;
    }

    public function getPaginationData()
    {
        $incubators = Incubator::query()
            ->where(function($query) {
                $query->where('incubatorName', 'like', '%' . $this->search . '%');
            });

        // Apply status filter
        if ($this->statusFilter === 'active') {
            $incubators->where('isActive', true);
        } elseif ($this->statusFilter === 'inactive') {
            $incubators->where('isActive', false);
        }

        // Apply date range filter
        if ($this->dateFrom) {
            $incubators->whereDate('creationDate', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $incubators->whereDate('creationDate', '<=', $this->dateTo);
        }

        $incubators = $incubators->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'page', $this->page);
            
        $currentPage = $incubators->currentPage(); // Get from paginator
        $lastPage = $incubators->lastPage();
        
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
            'incubators' => $incubators,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
        ];
    }

    public function gotoPage($page)
    {
        $page = (int) $page;
        
        // Validate page number
        $totalPages = Incubator::query()
            ->where(function($query) {
                $query->where('incubatorName', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter === 'active', function($query) {
                $query->where('isActive', true);
            })
            ->when($this->statusFilter === 'inactive', function($query) {
                $query->where('isActive', false);
            })
            ->when($this->dateFrom, function($query) {
                $query->whereDate('creationDate', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function($query) {
                $query->whereDate('creationDate', '<=', $this->dateTo);
            })
            ->paginate($this->perPage)
            ->lastPage();
        
        if ($page < 1) {
            $page = 1;
        } elseif ($page > $totalPages) {
            $page = $totalPages;
        }
        
        $this->page = $page;
    }

    public function render()
    {
        return view('livewire.shared.management.incubator-management.display-incubator-management', $this->getPaginationData());
    }
}

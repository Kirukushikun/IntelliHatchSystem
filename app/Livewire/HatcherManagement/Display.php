<?php

namespace App\Livewire\HatcherManagement;

use Livewire\Component;
use App\Models\Hatcher;

class Display extends Component
{
    public $search = '';
    public $perPage = 10;
    public $sortField = 'hatcherName';
    public $sortDirection = 'asc';
    public $page = 1;
    public $statusFilter = 'all'; // all, enabled, disabled
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

    protected $listeners = ['refreshHatchers' => '$refresh'];

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

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleFilterDropdown()
    {
        $this->showFilterDropdown = !$this->showFilterDropdown;
    }

    public function closeFilterDropdown()
    {
        $this->showFilterDropdown = false;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'dateFrom', 'dateTo']);
        $this->page = 1;
    }

    public function getPaginationData()
    {
        $hatchers = Hatcher::query()
            ->where(function($query) {
                $query->where('hatcherName', 'like', '%' . $this->search . '%');
            });

        // Apply status filter
        if ($this->statusFilter === 'enabled') {
            $hatchers->where('isDisabled', false);
        } elseif ($this->statusFilter === 'disabled') {
            $hatchers->where('isDisabled', true);
        }

        // Apply date range filter
        if ($this->dateFrom) {
            $hatchers->whereDate('creationDate', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $hatchers->whereDate('creationDate', '<=', $this->dateTo);
        }

        $hatchers = $hatchers->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'page', $this->page);
            
        $currentPage = $hatchers->currentPage(); // Get from paginator
        $lastPage = $hatchers->lastPage();
        
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
            'hatchers' => $hatchers,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
        ];
    }

    public function gotoPage($page)
    {
        $page = (int) $page;
        
        // Validate page number
        $totalPages = Hatcher::query()
            ->where(function($query) {
                $query->where('hatcherName', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter === 'enabled', function($query) {
                $query->where('isDisabled', false);
            })
            ->when($this->statusFilter === 'disabled', function($query) {
                $query->where('isDisabled', true);
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
        return view('livewire.hatcher-management.display-hatcher-management', $this->getPaginationData());
    }
}

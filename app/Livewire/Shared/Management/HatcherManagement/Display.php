<?php

namespace App\Livewire\Shared\Management\HatcherManagement;

use Livewire\Component;
use App\Models\Hatcher;
use Illuminate\Database\Eloquent\Builder;

class Display extends Component
{
    public $search = '';
    public $perPage = 10;
    public $sortField = 'hatcherName';
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
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'hatcherName'],
        'sortDirection' => ['except' => 'asc'],
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
        $hatchers = $this->baseQuery()
            ->orderBy($this->sortField, $this->sortDirection)
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
        $hatchers = Hatcher::query()
            ->where(function ($query) {
                $query->where('hatcherName', 'like', '%' . $this->search . '%');
            });

        if ($this->statusFilter === 'active') {
            $hatchers->where('isActive', true);
        } elseif ($this->statusFilter === 'inactive') {
            $hatchers->where('isActive', false);
        }

        if ($this->dateFrom) {
            $hatchers->whereDate('creationDate', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $hatchers->whereDate('creationDate', '<=', $this->dateTo);
        }

        return $hatchers;
    }

    public function render()
    {
        return view('livewire.shared.management.hatcher-management.display-hatcher-management', $this->getPaginationData());
    }
}

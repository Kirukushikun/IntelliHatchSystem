<?php

namespace App\Livewire\Shared\Management\PlenumManagement;

use Livewire\Component;
use App\Models\Plenum;

class Display extends Component
{
    public $search = '';
    public $perPage = 10;
    public $sortField = 'plenumName';
    public $sortDirection = 'asc';
    public $page = 1;
    public $statusFilter = 'all'; // all, active, inactive
    public $dateFrom = ''; // Custom date range from
    public $dateTo = ''; // Custom date range to
    public $showFilterDropdown = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'plenumName'],
        'sortDirection' => ['except' => 'asc'],
        'statusFilter' => ['except' => 'all'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    protected $listeners = ['refreshPlenums' => '$refresh'];

    public function mount()
    {
        $this->search = request()->get('search', '');
        $this->page = request()->get('page', 1);
        $this->statusFilter = request()->get('statusFilter', 'all');
        $this->dateFrom = request()->get('dateFrom', '');
        $this->dateTo = request()->get('dateTo', '');
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

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'dateFrom', 'dateTo']);
        $this->page = 1;
    }

    public function getPaginationData()
    {
        $plenums = Plenum::query()
            ->where(function($query) {
                $query->where('plenumName', 'like', '%' . $this->search . '%');
            });

        // Apply status filter
        if ($this->statusFilter === 'active') {
            $plenums->where('isActive', true);
        } elseif ($this->statusFilter === 'inactive') {
            $plenums->where('isActive', false);
        }

        // Apply date range filter
        if ($this->dateFrom) {
            $plenums->whereDate('creationDate', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $plenums->whereDate('creationDate', '<=', $this->dateTo);
        }

        $plenums = $plenums->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'page', $this->page);
            
        $currentPage = $plenums->currentPage(); // Get from paginator
        $lastPage = $plenums->lastPage();
        
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
            'plenums' => $plenums,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
        ];
    }

    public function gotoPage($page)
    {
        $page = (int) $page;
        
        // Validate page number
        $totalPages = Plenum::query()
            ->where(function($query) {
                $query->where('plenumName', 'like', '%' . $this->search . '%');
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
            ->count();
            
        $totalPages = ceil($totalPages / $this->perPage);
        
        if ($page < 1) {
            $page = 1;
        } elseif ($page > $totalPages) {
            $page = $totalPages;
        }
        
        $this->page = $page;
    }

    public function render()
    {
        return view('livewire.shared.management.plenum-management.display-plenum-management', $this->getPaginationData());
    }
}

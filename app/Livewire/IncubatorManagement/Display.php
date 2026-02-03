<?php

namespace App\Livewire\IncubatorManagement;

use Livewire\Component;
use App\Models\Incubator;

class Display extends Component
{
    public $search = '';
    public $perPage = 10;
    public $sortField = 'incubatorName';
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

    public function getIncubatorsProperty()
    {
        $incubators = Incubator::query()
            ->where(function($query) {
                $query->where('incubatorName', 'like', '%' . $this->search . '%');
            });

        // Apply status filter
        if ($this->statusFilter === 'enabled') {
            $incubators->where('isDisabled', false);
        } elseif ($this->statusFilter === 'disabled') {
            $incubators->where('isDisabled', true);
        }

        // Apply date range filter
        if ($this->dateFrom) {
            $incubators->whereDate('creationDate', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $incubators->whereDate('creationDate', '<=', $this->dateTo);
        }

        // Apply sorting
        $incubators->orderBy($this->sortField, $this->sortDirection);

        return $incubators->paginate($this->perPage);
    }

    public function getTotalPagesProperty()
    {
        // Validate page number
        $totalPages = Incubator::query()
            ->where(function($query) {
                $query->where('incubatorName', 'like', '%' . $this->search . '%');
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

        // Ensure current page is not greater than total pages
        if ($this->page > $totalPages) {
            $this->page = $totalPages > 0 ? $totalPages : 1;
        }

        return $totalPages;
    }

    public function render()
    {
        return view('livewire.incubator-management.display-incubator-management', [
            'incubators' => $this->incubators,
            'totalPages' => $this->totalPages,
        ]);
    }
}

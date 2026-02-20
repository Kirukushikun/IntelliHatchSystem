<?php

namespace App\Livewire\Admin\UserManagement;

use Livewire\Component;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class Display extends Component
{
    public $search = '';
    public $perPage = 10;
    public $sortField = 'first_name';
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
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'first_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $listeners = ['refreshUsers' => '$refresh'];

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

    public function updatingPerPage(): void
    {
        $this->page = 1;
    }

    public function updatedDateFrom()
    {
        // Ensure dateFrom is not after dateTo
        if ($this->dateFrom && $this->dateTo && $this->dateFrom > $this->dateTo) {
            $this->dateTo = ''; // Clear dateTo if it's before the new dateFrom
        }
        $this->page = 1;
    }

    public function updatedDateTo()
    {
        // Ensure dateTo is not before dateFrom
        if ($this->dateTo && $this->dateFrom && $this->dateTo < $this->dateFrom) {
            $this->dateTo = ''; // Clear dateTo if it's before dateFrom
        }
        $this->page = 1;
    }

    public function toggleFilterDropdown()
    {
        $this->showFilterDropdown = !$this->showFilterDropdown;
    }

    public function resetFilters()
    {
        $this->statusFilter = 'all';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->page = 1;
        $this->showFilterDropdown = false;
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

    public function deleteUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->delete();
            session()->flash('message', 'User deleted successfully.');
        }
    }

    public function editUser($userId)
    {
        return redirect()->to('/users/' . $userId . '/edit');
    }

    public function addUser()
    {
        return redirect()->to('/users/create');
    }

    public function getPaginationData()
    {
        $users = $this->baseQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'page', $this->page);
            
        $currentPage = $users->currentPage(); // Get from paginator
        $lastPage = $users->lastPage();
        
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
            'users' => $users,
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
        $users = User::query()
            ->where('user_type', 1)
            ->where(function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%');
            });

        if ($this->statusFilter === 'disabled') {
            $users->where('is_disabled', true);
        } elseif ($this->statusFilter === 'enabled') {
            $users->where('is_disabled', false);
        }

        if ($this->dateFrom || $this->dateTo) {
            if ($this->dateFrom && $this->dateTo) {
                $users->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
            } elseif ($this->dateFrom) {
                $users->whereDate('created_at', '>=', $this->dateFrom);
            } elseif ($this->dateTo) {
                $users->whereDate('created_at', '<=', $this->dateTo);
            }
        }

        return $users;
    }

    public function render()
    {
        return view('livewire.admin.user-management.display-user-management', $this->getPaginationData());
    }
}
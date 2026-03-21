<?php

namespace App\Livewire\Admin\AdminManagement;

use Livewire\Component;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Display extends Component
{
    public $search = '';
    public $perPage = 10;
    public $sortField = 'first_name';
    public $sortDirection = 'asc';
    public $page = 1;
    public $statusFilter = 'all'; // all, enabled, disabled
    public $roleFilter = 'all';   // all, superadmin, admin
    public $dateFrom = '';
    public $dateTo = '';
    public $showFilterDropdown = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'statusFilter' => ['except' => 'all'],
        'roleFilter' => ['except' => 'all'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'first_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $listeners = ['refreshAdmins' => '$refresh'];

    public function mount()
    {
        $this->search = request()->get('search', '');
        $this->page = request()->get('page', 1);
        $this->statusFilter = request()->get('statusFilter', 'all');
        $this->roleFilter = request()->get('roleFilter', 'all');
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

    public function updatingRoleFilter()
    {
        $this->page = 1;
    }

    public function updatingPerPage(): void
    {
        $this->page = 1;
    }

    public function updatedDateFrom()
    {
        if ($this->dateFrom && $this->dateTo && $this->dateFrom > $this->dateTo) {
            $this->dateTo = '';
        }
        $this->page = 1;
    }

    public function updatedDateTo()
    {
        if ($this->dateTo && $this->dateFrom && $this->dateTo < $this->dateFrom) {
            $this->dateTo = '';
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
        $this->roleFilter = 'all';
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

    public function getPaginationData()
    {
        $users = $this->baseQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        $currentPage = $users->currentPage();
        $lastPage = $users->lastPage();

        $this->page = $currentPage;

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
        $query = User::query()
            ->whereIn('user_type', [0, 1])
            ->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%');
            });

        if ($this->roleFilter === 'superadmin') {
            $query->where('user_type', 0);
        } elseif ($this->roleFilter === 'admin') {
            $query->where('user_type', 1);
        }

        if ($this->statusFilter === 'disabled') {
            $query->where('is_disabled', true);
        } elseif ($this->statusFilter === 'enabled') {
            $query->where('is_disabled', false);
        }

        if ($this->dateFrom || $this->dateTo) {
            if ($this->dateFrom && $this->dateTo) {
                $query->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
            } elseif ($this->dateFrom) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            } elseif ($this->dateTo) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            }
        }

        return $query;
    }

    public function render()
    {
        $firstSuperadminId = Cache::remember('admin_management:first_superadmin_id', 3600, function () {
            return User::where('user_type', 0)->orderBy('id')->value('id');
        });

        $data = $this->getPaginationData();
        $data['firstSuperadminId'] = $firstSuperadminId;
        $data['currentUserId'] = Auth::id();

        return view('livewire.admin.admin-management.display', $data);
    }
}

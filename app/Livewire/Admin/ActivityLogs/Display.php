<?php

namespace App\Livewire\Admin\ActivityLogs;

use Livewire\Component;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class Display extends Component
{
    public string $search = '';
    public int $perPage = 15;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $page = 1;
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $actionFilter = '';
    public string $userFilter = '';
    public bool $showFilterDropdown = false;

    protected $queryString = [
        'search'       => ['except' => ''],
        'page'         => ['except' => 1],
        'perPage'      => ['except' => 15],
        'sortField'    => ['except' => 'created_at'],
        'sortDirection'=> ['except' => 'desc'],
        'dateFrom'     => ['except' => ''],
        'dateTo'       => ['except' => ''],
        'actionFilter' => ['except' => ''],
        'userFilter'   => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->search       = request()->get('search', '');
        $this->page         = (int) request()->get('page', 1);
        $this->actionFilter = request()->get('actionFilter', '');
        $this->userFilter   = request()->get('userFilter', '');
        $this->dateFrom     = request()->get('dateFrom', '');
        $this->dateTo       = request()->get('dateTo', '');
    }

    public function updatingSearch(): void  { $this->page = 1; }
    public function updatingActionFilter(): void { $this->page = 1; }
    public function updatingUserFilter(): void   { $this->page = 1; }
    public function updatingPerPage(): void      { $this->page = 1; }

    public function updatedDateFrom(): void
    {
        if ($this->dateFrom && $this->dateTo && $this->dateFrom > $this->dateTo) {
            $this->dateTo = '';
        }
        $this->page = 1;
    }

    public function updatedDateTo(): void
    {
        if ($this->dateTo && $this->dateFrom && $this->dateTo < $this->dateFrom) {
            $this->dateTo = '';
        }
        $this->page = 1;
    }

    public function toggleFilterDropdown(): void
    {
        $this->showFilterDropdown = !$this->showFilterDropdown;
    }

    public function resetFilters(): void
    {
        $this->actionFilter      = '';
        $this->userFilter        = '';
        $this->dateFrom          = '';
        $this->dateTo            = '';
        $this->page              = 1;
        $this->showFilterDropdown = false;
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'desc';
        }
        $this->page = 1;
    }

    protected function baseQuery(): Builder
    {
        $query = ActivityLog::query()->with('user');

        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('action', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function (Builder $u) {
                      $u->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('username', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->actionFilter) {
            $query->where('action', $this->actionFilter);
        }

        if ($this->userFilter) {
            $query->where('user_id', $this->userFilter);
        }

        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
        } elseif ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        } elseif ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query;
    }

    public function getPaginationData(): array
    {
        $logs = $this->baseQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        $currentPage = $logs->currentPage();
        $lastPage    = $logs->lastPage();
        $this->page  = $currentPage;

        if ($lastPage <= 3) {
            $startPage = 1;
            $endPage   = $lastPage;
        } elseif ($currentPage === 1) {
            $startPage = 1;
            $endPage   = min(3, $lastPage);
        } elseif ($currentPage === $lastPage) {
            $startPage = max(1, $lastPage - 2);
            $endPage   = $lastPage;
        } else {
            $startPage = max(1, $currentPage - 1);
            $endPage   = min($lastPage, $currentPage + 1);
        }

        $pages = range($startPage, $endPage);

        return compact('logs', 'pages', 'currentPage', 'lastPage');
    }

    public function gotoPage(int $page): void
    {
        $totalPages = $this->baseQuery()->paginate($this->perPage)->lastPage();
        $this->page = max(1, min($page, $totalPages));
    }

    public function getExportUrlsProperty(): array
    {
        $params = array_filter([
            'search'        => $this->search,
            'actionFilter'  => $this->actionFilter,
            'userFilter'    => $this->userFilter,
            'dateFrom'      => $this->dateFrom,
            'dateTo'        => $this->dateTo,
            'sortField'     => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ]);

        return [
            'csv' => route('admin.activity-logs.export.csv', $params),
            'pdf' => route('admin.activity-logs.export.pdf', $params),
        ];
    }

    public function getDistinctActionsProperty(): array
    {
        return ActivityLog::query()
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->toArray();
    }

    public function getAdminUsersProperty(): \Illuminate\Support\Collection
    {
        return User::query()
            ->whereIn('user_type', [0, 1])
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'username']);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.activity-logs.display', array_merge(
            $this->getPaginationData(),
            [
                'distinctActions' => $this->distinctActions,
                'adminUsers'      => $this->adminUsers,
                'exportUrls'      => $this->exportUrls,
            ]
        ));
    }
}

<?php

namespace App\Livewire\Shared\FormsDashboard;

use App\Models\Form;
use App\Models\FormType;
use App\Models\Hatcher;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class BlowerAirHatcherDashboard extends Component
{
    use WithPagination;

    public string $search = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public bool $showFilterDropdown = false;
    
    public ?int $selectedFormId = null;
    public FormType $formType;
    public int $todayFormCount = 0;
    
    // Modal properties
    public bool $showModal = false;
    public bool $showPhotoModal = false;
    public array $selectedPhotos = [];
    public string $selectedPhotoField = '';
    public array $formPhotos = [];
    public int $currentPhotoIndex = 0;
    
    public int $perPage = 10;
    public string $sortField = 'date_submitted';
    public string $sortDirection = 'desc';
    public int $page = 1;

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'page' => ['except' => 1],
        'sortField' => ['except' => 'date_submitted'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $this->formType = FormType::where('form_name', 'Hatcher Blower Air Speed Monitoring')->firstOrFail();
        $this->calculateTodayFormCount();
    }

    protected function calculateTodayFormCount(): void
    {
        $this->todayFormCount = Form::where('form_type_id', $this->formType->id)
            ->whereDate('date_submitted', now()->format('Y-m-d'))
            ->count();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function quickFilterToday(): void
    {
        $today = now()->format('Y-m-d');
        
        // If already filtered to today, clear the filter
        if ($this->dateFrom === $today && $this->dateTo === $today) {
            $this->reset(['dateFrom', 'dateTo']);
        } else {
            // Otherwise, filter to today
            $this->dateFrom = $today;
            $this->dateTo = $today;
        }
        
        $this->resetPage();
    }

    public function toggleFilterDropdown(): void
    {
        $this->showFilterDropdown = !$this->showFilterDropdown;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function gotoPage($page): void
    {
        $this->page = (int) $page;
    }

    // Computed property to get selectedForm freshly each time
    #[Computed]
    public function selectedForm()
    {
        if (!$this->selectedFormId) {
            return null;
        }
        
        return Form::with(['user', 'formType'])->find($this->selectedFormId);
    }

    // Computed property to get formData freshly each time
    #[Computed]
    public function formData()
    {
        if (!$this->selectedFormId) {
            return [];
        }
        
        // Get fresh data directly from database
        $freshForm = DB::table('forms')
            ->where('id', $this->selectedFormId)
            ->first();
        
        if ($freshForm && $freshForm->form_inputs) {
            return is_array($freshForm->form_inputs) ? $freshForm->form_inputs : (json_decode($freshForm->form_inputs, true) ?: []);
        }
        
        return [];
    }

    // Computed property to get machine_info freshly each time
    #[Computed]
    public function machine_info()
    {
        if (!$this->selectedFormId) {
            return [];
        }
        
        $freshForm = DB::table('forms')
            ->where('id', $this->selectedFormId)
            ->first();
        
        if ($freshForm && $freshForm->form_inputs) {
            $formData = is_array($freshForm->form_inputs) ? $freshForm->form_inputs : (json_decode($freshForm->form_inputs, true) ?: []);
            
            // Check for different machine types in form_inputs
            if (isset($formData['hatcher']) && !empty($formData['hatcher'])) {
                $machineId = $formData['hatcher'];
                $machine = DB::table('hatcher-machines')
                    ->where('id', $machineId)
                    ->first();
                
                if ($machine) {
                    return [
                        'table' => 'hatcher-machines',
                        'id' => $machineId,
                        'name' => $machine->hatcherName
                    ];
                }
            }
        }

        return [
            'table' => null,
            'id' => null,
            'name' => null
        ];
    }

    public function viewDetails($formId): void
    {
        $this->selectedFormId = $formId;
        $this->formPhotos = $this->getFormPhotos($formId);
        $this->currentPhotoIndex = 0;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedFormId = null;
        $this->formPhotos = [];
    }

    public function viewPhotos(string $field): void
    {
        $this->selectedPhotoField = $field;
        $this->selectedPhotos = $this->getFormPhotos($this->selectedFormId, $field);
        $this->showPhotoModal = true;
    }

    public function closePhotoModal(): void
    {
        $this->showPhotoModal = false;
        $this->selectedPhotoField = '';
        $this->selectedPhotos = [];
    }

    private function getFormPhotos(int $formId, string $field = null): array
    {
        try {
            $form = Form::findOrFail($formId);
            $formData = is_array($form->form_inputs) ? $form->form_inputs : [];
            
            $photos = [];
            
            // Handle CFM fan photos
            if (isset($formData['cfm_fan_photos'])) {
                $cfmPhotos = $formData['cfm_fan_photos'];
                
                if (is_string($cfmPhotos)) {
                    // Handle JSON string
                    $decodedPhotos = json_decode($cfmPhotos, true);
                    if (is_array($decodedPhotos)) {
                        foreach ($decodedPhotos as $photo) {
                            if (is_string($photo)) {
                                $photos[] = [
                                    'url' => $photo,
                                    'name' => 'CFM Fan Photo'
                                ];
                            } elseif (is_array($photo) && isset($photo['url'])) {
                                $photos[] = [
                                    'url' => $photo['url'],
                                    'name' => $photo['name'] ?? 'CFM Fan Photo'
                                ];
                            }
                        }
                    }
                } elseif (is_array($cfmPhotos)) {
                    // Handle array format
                    foreach ($cfmPhotos as $photo) {
                        if (is_array($photo) && isset($photo['url'])) {
                            $photos[] = [
                                'url' => $photo['url'],
                                'name' => $photo['name'] ?? 'CFM Fan Photo'
                            ];
                        } elseif (is_string($photo)) {
                            $photos[] = [
                                'url' => $photo,
                                'name' => 'CFM Fan Photo'
                            ];
                        }
                    }
                }
            }
            
            return $photos;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getPhotoCount(string $field): int
    {
        if (!$this->selectedFormId) {
            return 0;
        }
        
        return count($this->getFormPhotos($this->selectedFormId, $field));
    }

    public function deleteForm($formId): void
    {
        $form = Form::findOrFail($formId);
        $formName = $form->formType->form_name ?? 'Unknown form';
        $form->delete();

        session()->flash('success', "Form '{$formName}' deleted successfully.");
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    protected function getForms()
    {
        $query = Form::where('form_type_id', $this->formType->id)
            ->with(['user']);

        // Search filter
        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->whereHas('user', function ($userQuery) {
                    $userQuery->where('first_name', 'like', '%' . $this->search . '%')
                           ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->orWhere(function ($subQ) {
                    $subQ->where('form_inputs', 'like', '%"machine_info":%')
                          ->where('form_inputs', 'like', '%"name":"' . $this->search . '"%');
                });
            });
        }

        // Date range filter
        if ($this->dateFrom) {
            $query->whereDate('date_submitted', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('date_submitted', '<=', $this->dateTo);
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    protected function getPaginationData()
    {
        $forms = $this->getForms();
        
        $currentPage = $forms->currentPage();
        $lastPage = $forms->lastPage();
        
        if ($lastPage <= 3) {
            $startPage = 1;
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

    public function render()
    {
        $paginationData = $this->getPaginationData();
        
        return view('livewire.shared.forms-dashboard.blower-air-hatcher-dashboard', [
            'forms' => $paginationData['forms'],
            'pages' => $paginationData['pages'],
            'currentPage' => $paginationData['currentPage'],
            'lastPage' => $paginationData['lastPage'],
            'formType' => $this->formType,
        ]);
    }
}

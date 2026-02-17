<?php

namespace App\Livewire\Shared\FormsDashboard;

use App\Models\Form;
use App\Models\FormType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Computed;

class IncubatorRoutineDashboard extends Component
{
    public int $typeId;
    public string $search = '';
    public string $sortField = 'date_submitted';
    public string $sortDirection = 'desc';
    public int $page = 1;
    public int $perPage = 10;
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $shiftFilter = 'all';
    public bool $showFilterDropdown = false;
    public bool $showModal = false;
    public bool $showPhotoModal = false;
    public bool $showDeleteModal = false;
    public ?int $selectedFormId = null; // Changed from selectedForm to just ID
    public ?int $formToDelete = null;
    public array $formPhotos = [];
    public string $selectedPhotoField = '';
    public array $selectedPhotos = [];
    public int $currentPhotoIndex = 0;
    public array $todayShiftCounts = [];

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
        $this->loadTodayShiftCounts();
        
        // Debug: Log that component is mounting
        Log::info('IncubatorRoutineDashboard component mounted with typeId: ' . $this->typeId);
    }

    protected function loadTodayShiftCounts(): void
    {
        $today = now()->format('Y-m-d');
        
        $this->todayShiftCounts = [
            '1st Shift' => $this->getTodayShiftCount('1st Shift'),
            '2nd Shift' => $this->getTodayShiftCount('2nd Shift'),
            '3rd Shift' => $this->getTodayShiftCount('3rd Shift'),
        ];
    }

    protected function getTodayShiftCount(string $shift): int
    {
        $today = now()->format('Y-m-d');
        
        return Form::where('form_type_id', $this->typeId)
            ->whereDate('date_submitted', $today)
            ->where(function ($query) use ($shift) {
                $query->where('form_inputs', 'like', '%"' . $shift . '"%')
                      ->orWhere('form_inputs', 'like', '%' . $shift . '%');
            })
            ->count();
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

    public function quickFilterTodayShift(string $shift): void
    {
        // Check if this shift is already active for today
        $isCurrentlyActive = $this->shiftFilter === $shift && 
                           $this->dateFrom === now()->format('Y-m-d') && 
                           $this->dateTo === now()->format('Y-m-d');
        
        if ($isCurrentlyActive) {
            // Toggle off - reset to all shifts
            $this->dateFrom = '';
            $this->dateTo = '';
            $this->shiftFilter = 'all';
        } else {
            // Toggle on - apply today's date and specific shift
            $this->dateFrom = now()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
            $this->shiftFilter = $shift;
        }
        
        $this->page = 1;
        $this->showFilterDropdown = false;
    }

    public function getPaginationData()
    {
        $query = Form::with(['user', 'formType'])
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

        // Apply sorting
        if ($this->sortField === 'shift') {
            $query->orderByRaw("JSON_EXTRACT(form_inputs, '$.shift') {$this->sortDirection}");
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $forms = $query->paginate($this->perPage, ['*'], 'page', $this->page);
        
        // Debug: Log paginated form data for comparison
        $forms->getCollection()->each(function ($form, $index) {
            Log::info('Paginated Form Data', [
                'index' => $index,
                'form_id' => $form->id,
                'form_inputs_type' => gettype($form->form_inputs),
                'form_inputs_is_array' => is_array($form->form_inputs),
                'shift_from_paginated' => $form->form_inputs['shift'] ?? 'NOT_FOUND',
                'raw_form_inputs' => $form->form_inputs,
            ]);
        });
            
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
            return json_decode($freshForm->form_inputs, true) ?? [];
        }
        
        return [];
    }

    public function viewDetails(int $formId): void
    {
        // Simply set the ID - computed properties will handle the rest
        $this->selectedFormId = $formId;
        $this->formPhotos = $this->getFormPhotos($formId);
        $this->currentPhotoIndex = 0;
        $this->showModal = true;
        
        // Debug logging
        Log::info('ViewDetails Called - Simple', [
            'form_id' => $formId,
            'selectedFormId' => $this->selectedFormId,
        ]);
    }

    public function deleteForm(int $formId): void
    {
        $this->formToDelete = $formId;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        if (!$this->formToDelete) {
            return;
        }

        try {
            $form = Form::find($this->formToDelete);
            
            if (!$form) {
                $this->dispatch('showToast', message: 'Form not found.', type: 'error');
                return;
            }

            // Delete the form
            $form->delete();
            
            // Close modal and reset
            $this->showDeleteModal = false;
            $this->formToDelete = null;
            
            // Show success message
            $this->dispatch('showToast', message: 'Form deleted successfully!', type: 'success');
            
        } catch (\Exception $e) {
            Log::error('Error deleting form: ' . $e->getMessage());
            $this->dispatch('showToast', message: 'Error deleting form. Please try again.', type: 'error');
        }
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->formToDelete = null;
    }

    public function viewPhotos(string $field): void
    {
        $this->selectedPhotoField = $field;
        $this->selectedPhotos = $this->getFormPhotos($this->selectedForm->id, $field);
        $this->showPhotoModal = true;
    }

    public function getPhotoCount(string $field): int
    {
        return count($this->getFormPhotos($this->selectedForm->id, $field));
    }

    public function closePhotoModal(): void
    {
        $this->showPhotoModal = false;
        $this->selectedPhotoField = '';
        $this->selectedPhotos = [];
    }

    private function getFormPhotos(int $formId, string $field = null): array
    {
        // Get the form and its photos
        $form = Form::find($formId);
        if (!$form) {
            return [];
        }
        
        // Get photos from the form_inputs JSON data
        $formData = is_array($form->form_inputs) ? $form->form_inputs : [];
        $photoFieldKey = $field . '_photos';
        
        if (isset($formData[$photoFieldKey]) && !empty($formData[$photoFieldKey])) {
            // Return actual photo URLs from the form data
            $photoUrls = is_array($formData[$photoFieldKey]) ? $formData[$photoFieldKey] : [];
            
            $photos = [];
            foreach ($photoUrls as $url) {
                $photos[] = [
                    'id' => uniqid(),
                    'url' => $url,
                    'caption' => 'Photo'
                ];
            }
            return $photos;
        }
        
        return [];
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedFormId = null;
        $this->formPhotos = [];
    }

    public function render()
    {
        // Refresh today's shift counts
        $this->loadTodayShiftCounts();
        
        $paginationData = $this->getPaginationData();
        
        return view('livewire.shared.forms-dashboard.incubator-routine-dashboard', [
            'forms' => $paginationData['forms'],
            'pages' => $paginationData['pages'],
            'currentPage' => $paginationData['currentPage'],
            'lastPage' => $paginationData['lastPage'],
        ]);
    }
}
<!-- Detail Modal -->
<div x-data="{ showModal: @entangle('showModal').live }"
     x-show="showModal"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto bg-black/50 dark:bg-black/80"
     style="display: none;"
     @click.self="showModal = false; $wire.closeModal()">
    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
             class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all w-full max-w-4xl"
             @click.stop>
            <div class="bg-white dark:bg-gray-800 px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">PASGAR SCORE DETAILS</h3>
                    <div class="flex items-center gap-2">
                        @if($this->selectedFormId)
                            <button type="button" wire:click="printPasgarSummary({{ $this->selectedFormId }})" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors cursor-pointer" title="Print Summary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Print Summary
                            </button>
                            <button type="button" wire:click="printPasgarDetail({{ $this->selectedFormId }})" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors cursor-pointer" title="Print Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                                </svg>
                                Print
                            </button>
                        @endif
                        <button type="button" @click="showModal = false; $wire.closeModal()" class="rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none cursor-pointer">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 px-6 py-4 max-h-[70vh] overflow-y-auto">
                @if($this->selectedForm)
                    <!-- Basic Info -->
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Date Submitted:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->selectedForm->date_submitted ? $this->selectedForm->date_submitted->format('d M, Y g:i A') : 'N/A' }}</span>
                        </div>
                        @php
                            $modalPersonnel = $this->formData['personnel_name'] ?? 'N/A';
                            if (is_numeric($modalPersonnel)) {
                                $modalUser = \Illuminate\Support\Facades\DB::table('users')->where('id', $modalPersonnel)->first();
                                $modalPersonnel = $modalUser ? trim($modalUser->first_name . ' ' . $modalUser->last_name) : 'N/A';
                            }
                        @endphp
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Personnel:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-200">{{ $modalPersonnel }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Hatch Date:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['hatch_date'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Time Started:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['time_started'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Time Finished:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['time_finished'] ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Machine / Registry Info -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Registry Info</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">PS Number:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['machine_info']['name'] ?? 'N/A' }}</span>
                            </div>
                            @php
                                $houseId = $this->formData['house_number'] ?? null;
                                $houseName = 'N/A';
                                if ($houseId) {
                                    $house = \Illuminate\Support\Facades\DB::table('house-numbers')->where('id', $houseId)->first();
                                    $houseName = $house->houseNumber ?? 'N/A';
                                }
                                $incubatorId = $this->formData['incubator_number'] ?? null;
                                $incubatorName = 'N/A';
                                if ($incubatorId) {
                                    $inc = \Illuminate\Support\Facades\DB::table('incubator-machines')->where('id', $incubatorId)->first();
                                    $incubatorName = $inc->incubatorName ?? 'N/A';
                                }
                                $hatcherId = $this->formData['hatcher_number'] ?? null;
                                $hatcherName = 'N/A';
                                if ($hatcherId) {
                                    $hatcher = \Illuminate\Support\Facades\DB::table('hatcher-machines')->where('id', $hatcherId)->first();
                                    $hatcherName = $hatcher->hatcherName ?? 'N/A';
                                }
                            @endphp
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">House Number:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $houseName }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Incubator:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $incubatorName }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Hatcher:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $hatcherName }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Scoring Summary -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Scoring Summary</h4>
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-3 text-center">
                                <div class="text-xs font-medium text-blue-600 dark:text-blue-400">PASGAR Average</div>
                                <div class="text-xl font-bold text-blue-800 dark:text-blue-200">{{ $this->formData['pasgar_average_scoring'] ?? 'N/A' }}</div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-3 text-center">
                                <div class="text-xs font-medium text-green-600 dark:text-green-400">Avg Chick Weight</div>
                                <div class="text-xl font-bold text-green-800 dark:text-green-200">{{ $this->formData['average_chick_weight'] ?? 'N/A' }}g</div>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-900/30 rounded-lg p-3 text-center">
                                <div class="text-xs font-medium text-purple-600 dark:text-purple-400">Total Samples</div>
                                <div class="text-xl font-bold text-purple-800 dark:text-purple-200">{{ $this->formData['total_samples'] ?? count($this->formData['samples'] ?? []) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Issue Totals -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Issue Totals</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div class="flex justify-between items-center px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Low Reflex</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-200">{{ $this->formData['low_reflex_alertness_qty'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Navel Issue</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-200">{{ $this->formData['navel_issue_qty'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Leg Issue</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-200">{{ $this->formData['leg_issue_qty'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Beak Issue</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-200">{{ $this->formData['beak_issue_qty'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Belly Bloated</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-200">{{ $this->formData['belly_bloated_qty'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Vaccination</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-200">{{ $this->formData['vaccination_issue_qty'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Samples Table -->
                    @if(!empty($this->formData['samples']))
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">DOP Samples</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-700">
                                            <th class="px-3 py-2 border border-gray-200 dark:border-gray-600 font-semibold text-gray-700 dark:text-gray-300">#</th>
                                            <th class="px-3 py-2 border border-gray-200 dark:border-gray-600 font-semibold text-gray-700 dark:text-gray-300">Weight (g)</th>
                                            <th class="px-3 py-2 border border-gray-200 dark:border-gray-600 font-semibold text-gray-700 dark:text-gray-300 text-center">Reflex</th>
                                            <th class="px-3 py-2 border border-gray-200 dark:border-gray-600 font-semibold text-gray-700 dark:text-gray-300 text-center">Navel</th>
                                            <th class="px-3 py-2 border border-gray-200 dark:border-gray-600 font-semibold text-gray-700 dark:text-gray-300 text-center">Leg</th>
                                            <th class="px-3 py-2 border border-gray-200 dark:border-gray-600 font-semibold text-gray-700 dark:text-gray-300 text-center">Beak</th>
                                            <th class="px-3 py-2 border border-gray-200 dark:border-gray-600 font-semibold text-gray-700 dark:text-gray-300 text-center">Belly</th>
                                            <th class="px-3 py-2 border border-gray-200 dark:border-gray-600 font-semibold text-gray-700 dark:text-gray-300 text-center">Vaccine</th>
                                            <th class="px-3 py-2 border border-gray-200 dark:border-gray-600 font-semibold text-gray-700 dark:text-gray-300 text-center">Photos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($this->formData['samples'] as $index => $sample)
                                            <tr class="even:bg-gray-50 dark:even:bg-gray-700/50">
                                                <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-200">{{ $index + 1 }}</td>
                                                <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-200">{{ $sample['chick_weight'] ?? 'N/A' }}</td>
                                                <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-center">
                                                    @if(!empty($sample['low_reflex_alertness']))
                                                        <span class="text-red-500">&#10005;</span>
                                                    @else
                                                        <span class="text-green-500">&#10003;</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-center">
                                                    @if(!empty($sample['navel_issue']))
                                                        <span class="text-red-500">&#10005;</span>
                                                    @else
                                                        <span class="text-green-500">&#10003;</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-center">
                                                    @if(!empty($sample['leg_issue']))
                                                        <span class="text-red-500">&#10005;</span>
                                                    @else
                                                        <span class="text-green-500">&#10003;</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-center">
                                                    @if(!empty($sample['beak_issue']))
                                                        <span class="text-red-500">&#10005;</span>
                                                    @else
                                                        <span class="text-green-500">&#10003;</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-center">
                                                    @if(!empty($sample['belly_bloated']))
                                                        <span class="text-red-500">&#10005;</span>
                                                    @else
                                                        <span class="text-green-500">&#10003;</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-center">
                                                    @if(!empty($sample['vaccination_issue']))
                                                        <span class="text-red-500">&#10005;</span>
                                                    @else
                                                        <span class="text-green-500">&#10003;</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 border border-gray-200 dark:border-gray-600 text-center">
                                                    <div class="flex flex-col gap-1 items-center">
                                                        @php $weighingCount = $this->getPhotoCount("samples.{$index}.weighing_photos"); @endphp
                                                        @if($weighingCount > 0)
                                                            <button @click="$wire.viewPhotos('samples.{{ $index }}.weighing_photos')" class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold rounded border border-blue-200 dark:border-blue-600 bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 hover:bg-blue-100 transition-all cursor-pointer whitespace-nowrap">
                                                                Weighing ({{ $weighingCount }})
                                                            </button>
                                                        @endif
                                                        @php $issueCount = $this->getPhotoCount("samples.{$index}.issue_photos"); @endphp
                                                        @if($issueCount > 0)
                                                            <button @click="$wire.viewPhotos('samples.{{ $index }}.issue_photos')" class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold rounded border border-amber-200 dark:border-amber-600 bg-amber-50 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 hover:bg-amber-100 transition-all cursor-pointer whitespace-nowrap">
                                                                Issue ({{ $issueCount }})
                                                            </button>
                                                        @endif
                                                        @if($weighingCount === 0 && $issueCount === 0)
                                                            <span class="text-gray-400 text-[10px]">—</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- DOP Info -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">DOP Information</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">DOP Prime Qty:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['dop_prime_qty'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">DOP Prime Box Numbers:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['dop_prime_box_numbers'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">DOP JR Prime Qty:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['dop_jr_prime_qty'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">DOP JR Prime Box Numbers:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['dop_jr_prime_box_numbers'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- QC Personnel -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">QC Personnel:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-200">{{ $this->formData['qc_personnel'] ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Form Photos -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Form Photos:</span>
                            @php $photoCount = $this->getPhotoCount('form_photo'); @endphp
                            @if($photoCount > 0)
                                <button @click="$wire.viewPhotos('form_photo')" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg border border-blue-200 dark:border-blue-600 bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 hover:bg-blue-100 transition-all cursor-pointer">Photos ({{ $photoCount }})</button>
                            @elseif($this->selectedForm && $this->selectedForm->photos_purged_at)
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-600">Photos Expired</span>
                            @else
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">No Photos</span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No form data available</h3>
                    </div>
                @endif
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end">
                <button type="button" @click="showModal = false; $wire.closeModal()" class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none cursor-pointer">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Photo Viewer Modal -->
@if(count($selectedPhotos) > 0)
<div x-data="{ showPhotoModal: @entangle('showPhotoModal'), currentPhotoIndex: 0, selectedPhotos: @js($selectedPhotos) }"
     x-init="currentPhotoIndex = Math.min(currentPhotoIndex, selectedPhotos.length - 1) || 0"
     x-show="showPhotoModal"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto bg-black/50 dark:bg-black/80"
     style="display: none;">
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="showPhotoModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl w-full max-w-sm">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Photos</h3>
                <button type="button" @click="showPhotoModal = false; $wire.closePhotoModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="p-4">
                <div class="relative w-full aspect-square bg-gray-900 rounded-lg overflow-hidden">
                    <template x-if="selectedPhotos[currentPhotoIndex]"><img :src="selectedPhotos[currentPhotoIndex]?.url || ''" class="w-full h-full object-contain"></template>
                    @if(count($selectedPhotos) > 1)
                        <button type="button" @click="currentPhotoIndex = (currentPhotoIndex - 1 + selectedPhotos.length) % selectedPhotos.length" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>
                        <button type="button" @click="currentPhotoIndex = (currentPhotoIndex + 1) % selectedPhotos.length" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
                    @endif
                </div>
                <div class="mt-3 text-center text-sm text-gray-600 dark:text-gray-400"><span x-text="currentPhotoIndex + 1"></span> / <span>{{ count($selectedPhotos) }}</span></div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 flex justify-end">
                <button type="button" @click="showPhotoModal = false; $wire.closePhotoModal()" class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 cursor-pointer">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

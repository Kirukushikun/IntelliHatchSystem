<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Components\FormNavigation;
use App\Livewire\Configs\PasgarScoreConfig;
use App\Livewire\Shared\Forms\Traits\TempPhotoManager;
use App\Models\Hatcher;
use App\Models\HouseNumber;
use App\Models\Incubator;
use App\Models\PsNumber;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

class PasgarScoreForm extends FormNavigation
{
    use WithFileUploads, TempPhotoManager;

    public array $form = [];

    public array $photoUploads = [];

    public bool $formSubmitted = false;

    protected bool $disableShiftLogic = true;

    public array $users = [];

    public array $psNumbers = [];

    public array $houseNumbers = [];

    public array $incubators = [];

    public array $hatchers = [];

    public array $qcPersonnel = [];

    public int $expandedSample = 0;

    public int $nextSampleKey = 2;

    public function mount($formType = 'pasgar_score'): void
    {
        $this->form = PasgarScoreConfig::defaultFormState();
        // Start with one empty sample (with stable key for photo tracking)
        $this->form['samples'] = [array_merge(PasgarScoreConfig::defaultSample(), ['_key' => 1])];

        parent::mount($formType);
        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();

        $this->users = $this->loadPersonnelByTags();
        $this->form['personnel_name'] = $this->initPersonnelField();

        $this->psNumbers = PsNumber::where('isActive', true)
            ->orderBy('psNumber')
            ->get()
            ->mapWithKeys(fn ($ps) => [$ps->id => $ps->psNumber])
            ->toArray();

        $this->houseNumbers = HouseNumber::where('isActive', true)
            ->orderBy('houseNumber')
            ->get()
            ->mapWithKeys(fn ($house) => [$house->id => $house->houseNumber])
            ->toArray();

        $this->incubators = Incubator::where('isActive', true)
            ->orderBy('incubatorName')
            ->get()
            ->mapWithKeys(fn ($inc) => [$inc->id => $inc->incubatorName])
            ->toArray();

        $this->hatchers = Hatcher::where('isActive', true)
            ->orderBy('hatcherName')
            ->get()
            ->mapWithKeys(fn ($h) => [$h->id => $h->hatcherName])
            ->toArray();

        $this->qcPersonnel = $this->loadQcPersonnel();
    }

    protected function loadQcPersonnel(): array
    {
        $qcTagId = Tag::where('name', 'QA/QC')->value('id');

        if (!$qcTagId) {
            return [];
        }

        return User::where('user_type', 2)
            ->where('is_disabled', false)
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $qcTagId))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(fn ($u) => [$u->id => $u->full_name])
            ->toArray();
    }

    public function updated($name, $value): void
    {
        if (!is_string($name) || !str_starts_with($name, 'photoUploads.')) {
            return;
        }

        $photoKey = substr($name, strlen('photoUploads.'));
        $photoKey = explode('.', $photoKey)[0];

        $files = $this->photoUploads[$photoKey] ?? [];
        if (!is_array($files) || empty($files)) {
            return;
        }

        $formType = $this->formTypeKey();
        $this->handleTempPhotoUpload($photoKey, $files, $formType);
    }

    public function addSample(): void
    {
        $lastIndex = count($this->form['samples']) - 1;
        $lastSample = $this->form['samples'][$lastIndex] ?? null;

        if ($lastSample && (!is_numeric($lastSample['chick_weight']) || $lastSample['chick_weight'] <= 0)) {
            $this->dispatch('showToast', message: 'Please enter the Chick Weight for DOP #' . ($lastIndex + 1) . ' before adding a new sample.', type: 'error');
            return;
        }

        $this->form['samples'][] = array_merge(PasgarScoreConfig::defaultSample(), ['_key' => $this->nextSampleKey]);
        $this->nextSampleKey++;
        $this->expandedSample = count($this->form['samples']) - 1;

        $newIndex = $this->expandedSample;
        $this->dispatch('focus-chick-weight', index: $newIndex);
    }

    public function toggleSample(int $index): void
    {
        $this->expandedSample = $this->expandedSample === $index ? -1 : $index;
    }

    public function removeSample(int $index): void
    {
        if (count($this->form['samples']) <= 1) {
            return;
        }

        // Clean up photos for this sample
        $sampleKey = $this->form['samples'][$index]['_key'] ?? null;
        if ($sampleKey) {
            foreach (['weighing_photo_', 'issue_photo_'] as $prefix) {
                $photoKey = $prefix . $sampleKey;
                $photoIds = $this->uploadedPhotoIds[$photoKey] ?? [];
                foreach ($photoIds as $photoId) {
                    $this->deleteUploadedPhoto($photoKey, $photoId);
                }
            }
        }

        unset($this->form['samples'][$index]);
        $this->form['samples'] = array_values($this->form['samples']);

        $lastIndex = count($this->form['samples']) - 1;
        if ($this->expandedSample >= count($this->form['samples'])) {
            $this->expandedSample = $lastIndex;
        }
    }

    public function getSampleScore(int $index): int
    {
        $sample = $this->form['samples'][$index] ?? [];
        $score = 100;

        // Deduct 1 for each issue checked (6 possible issues)
        $issues = collect([
            $sample['low_reflex_alertness'] ?? false,
            $sample['navel_issue'] ?? false,
            $sample['leg_issue'] ?? false,
            $sample['beak_issue'] ?? false,
            $sample['belly_bloated'] ?? false,
            $sample['vaccination_issue'] ?? false,
        ])->filter()->count();
        $score -= $issues;

        // Weight deduction
        $weight = (float) ($sample['chick_weight'] ?? 0);
        if ($weight > 0 && $weight <= 30) {
            $score -= 2;
        } elseif ($weight >= 31 && $weight <= 34) {
            $score -= 1;
        }
        // 35g or above: no deduction

        return $score;
    }

    public function getPasgarAverageProperty(): string
    {
        $samples = $this->form['samples'] ?? [];
        if (empty($samples)) {
            return '0.00';
        }

        $total = 0;
        foreach ($samples as $index => $sample) {
            $total += $this->getSampleScore($index);
        }

        return number_format($total / count($samples), 2);
    }

    public function getAverageChickWeight(): string
    {
        $samples = $this->form['samples'] ?? [];
        $weights = collect($samples)
            ->pluck('chick_weight')
            ->filter(fn ($w) => is_numeric($w) && $w > 0);

        if ($weights->isEmpty()) {
            return '0.00';
        }

        return number_format($weights->avg(), 2);
    }

    public function getIssueTotals(): array
    {
        $samples = $this->form['samples'] ?? [];

        return [
            'low_reflex_alertness' => collect($samples)->where('low_reflex_alertness', true)->count(),
            'navel_issue'          => collect($samples)->where('navel_issue', true)->count(),
            'leg_issue'            => collect($samples)->where('leg_issue', true)->count(),
            'beak_issue'           => collect($samples)->where('beak_issue', true)->count(),
            'belly_bloated'        => collect($samples)->where('belly_bloated', true)->count(),
            'vaccination_issue'    => collect($samples)->where('vaccination_issue', true)->count(),
        ];
    }

    public function getInitialPhotosForKey(string $photoKey): array
    {
        $ids = $this->uploadedPhotoIds[$photoKey] ?? [];
        $urls = $this->uploadedPhotoUrls[$photoKey] ?? [];
        $result = [];

        foreach ($ids as $i => $id) {
            $result[] = ['id' => $id, 'url' => $urls[$i] ?? ''];
        }

        return $result;
    }

    protected function formTypeKey(): string
    {
        return 'pasgar_score';
    }

    protected function scheduleConfig(): array
    {
        return PasgarScoreConfig::schedule();
    }

    protected function stepFieldMap(): array
    {
        return PasgarScoreConfig::stepFieldMap();
    }

    protected function formTypeName(): string
    {
        return PasgarScoreConfig::getFormTypeName();
    }

    protected function messages(): array
    {
        return PasgarScoreConfig::getMessages();
    }

    public function submitForm()
    {
        $this->formSubmitted = true;

        try {
            $this->validate(PasgarScoreConfig::getRules(), $this->messages());

            if (!$this->ensureAllPhotosUploaded()) {
                $this->dispatch('showToast', message: 'Photo uploads are still in progress. Please wait for all photos to finish uploading before submitting the form.', type: 'error');
                return;
            }

            $formId = $this->storeSubmissionAndReturnId($this->formTypeName(), $this->formInputsForStorageWithoutPhotos());
            $this->finalizePhotosForForm($formId);

            $finalInputs = $this->formInputsWithPhotos($this->formInputsForStorageWithoutPhotos());

            // Embed per-sample photos and remove top-level keys
            foreach ($this->form['samples'] as $i => $sample) {
                $key = $sample['_key'] ?? null;
                if ($key && isset($finalInputs['samples'][$i])) {
                    foreach (['weighing_photo_' => 'weighing_photos', 'issue_photo_' => 'issue_photos'] as $prefix => $field) {
                        $photoKey = $prefix . $key;
                        $finalInputs['samples'][$i][$field] = $finalInputs[$photoKey] ?? [];
                        unset($finalInputs[$photoKey]);
                    }
                }
            }

            DB::table('forms')->where('id', $formId)->update([
                'form_inputs' => json_encode($finalInputs),
                'updated_at'  => now(),
            ]);

            $this->sendFormToWebhook($formId);

            session()->flash('success', 'Form submitted successfully!');

            return redirect()->route('forms.pasgar-score');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstKey = array_key_first($e->validator->errors()->messages());
            if ($firstKey) {
                $fieldName = str_replace('form.', '', $firstKey);
                $this->goToStepWithField($fieldName);
            }

            throw $e;
        } catch (\Exception $e) {
            Log::error('PASGAR Score form submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatch('showToast', message: 'Failed to submit form. Please try again.', type: 'error');
        }
    }

    protected function storeSubmissionAndReturnId(string $formTypeName, array $formInputs): int
    {
        DB::beginTransaction();

        try {
            $formTypeId = DB::table('form_types')
                ->where('form_name', $formTypeName)
                ->value('id');

            if (!$formTypeId) {
                throw new \Exception('Form type not found: ' . $formTypeName);
            }

            $formId = (int) DB::table('forms')->insertGetId([
                'form_type_id' => $formTypeId,
                'form_inputs'  => json_encode($formInputs),
                'date_submitted' => now(),
                'uploaded_by'  => \Illuminate\Support\Facades\Auth::id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            DB::commit();

            return $formId;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function formInputsForStorageWithoutPhotos(): array
    {
        $inputs = $this->form;

        // Resolve personnel name IDs to names
        $inputs['personnel_name'] = $this->resolvePersonnelNames($this->form['personnel_name'] ?? [], $this->users);

        // Resolve QC personnel IDs to names
        $inputs['qc_personnel'] = $this->resolvePersonnelNames($this->form['qc_personnel'] ?? [], $this->qcPersonnel);

        // Resolve PS number label
        if (!empty($inputs['ps_number']) && isset($this->psNumbers[$inputs['ps_number']])) {
            $inputs['machine_info'] = [
                'table' => 'ps-numbers',
                'id'    => $inputs['ps_number'],
                'name'  => $this->psNumbers[$inputs['ps_number']],
            ];
        }

        // Compute average chick weight and issue totals from samples
        $inputs['average_chick_weight']     = $this->getAverageChickWeight();
        $totals = $this->getIssueTotals();
        $inputs['low_reflex_alertness_qty'] = $totals['low_reflex_alertness'];
        $inputs['navel_issue_qty']          = $totals['navel_issue'];
        $inputs['leg_issue_qty']            = $totals['leg_issue'];
        $inputs['beak_issue_qty']           = $totals['beak_issue'];
        $inputs['belly_bloated_qty']        = $totals['belly_bloated'];
        $inputs['vaccination_issue_qty']    = $totals['vaccination_issue'];
        $inputs['pasgar_average_scoring']   = $this->getPasgarAverageProperty();
        $inputs['total_samples']            = count($inputs['samples'] ?? []);

        // Strip internal _key from samples
        foreach ($inputs['samples'] as $i => &$sample) {
            unset($sample['_key']);
        }
        unset($sample);

        return $inputs;
    }

    protected function sendFormToWebhook(int $formId): void
    {
        try {
            $webhookUrl = config('services.webhook.url');

            if (!$webhookUrl) {
                return;
            }

            $form = DB::table('forms')
                ->select('forms.*', 'form_types.form_name as form_type_name')
                ->leftJoin('form_types', 'forms.form_type_id', '=', 'form_types.id')
                ->where('forms.id', $formId)
                ->first();

            if (!$form) {
                return;
            }

            $formInputs = is_array($form->form_inputs) ? $form->form_inputs : json_decode($form->form_inputs, true);
            $formInputs = (array) $formInputs;

            $machineInfo = $formInputs['machine_info'] ?? null;

            $payload = [
                'form' => [
                    'form_id'   => $form->id,
                    'form_name' => $form->form_type_name ?: 'Unknown Form Type',
                ],
                'records'        => $formInputs,
                'date_submitted' => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                'uploaded_by'    => null,
                'machine'        => $machineInfo,
                'message'        => [
                    'form_name'    => $form->form_type_name ?: 'Unknown Form Type',
                    'machine_name' => is_array($machineInfo) ? ($machineInfo['name'] ?? null) : null,
                    'submitted_by' => isset($this->form['personnel_name'])
                        ? ($this->users[$this->form['personnel_name']] ?? $this->form['personnel_name'])
                        : null,
                    'date_time'    => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                    'photos'       => [],
                    'shift'        => 'N/A',
                ],
                'timestamp' => now()->toISOString(),
            ];

            Http::post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending PASGAR Score form to webhook', [
                'form_id' => $formId,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.shared.forms.pasgar-score-form');
    }
}

<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Components\FormNavigation;
use App\Livewire\Configs\PasgarScoreConfig;
use App\Livewire\Shared\Forms\Traits\TempPhotoManager;
use App\Models\Hatcher;
use App\Models\HouseNumber;
use App\Models\Incubator;
use App\Models\PsNumber;
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

    public array $psNumbers = [];

    public array $houseNumbers = [];

    public array $incubators = [];

    public array $hatchers = [];

    public function mount($formType = 'pasgar_score'): void
    {
        $this->form = PasgarScoreConfig::defaultFormState();

        parent::mount($formType);
        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();

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

            // Require at least one photo of the form
            $sessionPhotos = session("temp_photos.pasgar_score.form_photo", []);
            if (empty($sessionPhotos)) {
                $this->dispatch('showToast', message: 'Please upload at least one photo of the form with data before submitting.', type: 'error');
                $this->goToStepWithField('form_photo');
                return;
            }

            if (!$this->ensureAllPhotosUploaded()) {
                $this->dispatch('showToast', message: 'Photo uploads are still in progress. Please wait for all photos to finish uploading before submitting the form.', type: 'error');
                return;
            }

            $formId = $this->storeSubmissionAndReturnId($this->formTypeName(), $this->formInputsForStorageWithoutPhotos());
            $this->finalizePhotosForForm($formId);

            DB::table('forms')->where('id', $formId)->update([
                'form_inputs' => json_encode($this->formInputsWithPhotos($this->formInputsForStorageWithoutPhotos())),
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
                'uploaded_by'  => null,
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

        // Resolve PS number label
        if (!empty($inputs['ps_number']) && isset($this->psNumbers[$inputs['ps_number']])) {
            $inputs['machine_info'] = [
                'table' => 'ps-numbers',
                'id'    => $inputs['ps_number'],
                'name'  => $this->psNumbers[$inputs['ps_number']],
            ];
        }

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
                    'submitted_by' => $this->form['personnel_name'] ?? null,
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

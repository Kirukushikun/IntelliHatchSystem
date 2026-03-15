<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Configs\IncubatorTempCalibrationConfig;
use App\Livewire\Components\FormNavigation;
use App\Livewire\Shared\Forms\Traits\TempPhotoManager;
use App\Models\Incubator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

class IncubatorTempCalibrationForm extends FormNavigation
{
    use WithFileUploads, TempPhotoManager;

    public array $form = [];

    public array $photoUploads = [];

    public bool $formSubmitted = false;

    protected bool $disableShiftLogic = true;

    /** @var array */
    public $incubators = [];

    /** @var array */
    public $hatcheryMen = [];

    /** @var array */
    public $completedIncubators = [];

    public function mount($formType = 'incubator_temp_calibration'): void
    {
        $this->form = IncubatorTempCalibrationConfig::defaultFormState();

        parent::mount($formType);
        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();

        $this->incubators = Incubator::where('isActive', true)
            ->orderBy('incubatorName')
            ->get()
            ->mapWithKeys(fn ($m) => [$m->id => $m->incubatorName])
            ->toArray();

        $this->hatcheryMen = User::where('user_type', 1)
            ->where('is_disabled', false)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(fn ($u) => [$u->id => $u->first_name . ' ' . $u->last_name])
            ->toArray();

        $this->updateCompletedIncubators();
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

        $this->handleTempPhotoUpload($photoKey, $files, $this->formTypeKey());
    }

    protected function formTypeKey(): string
    {
        return 'incubator_temp_calibration';
    }

    protected function scheduleConfig(): array
    {
        return IncubatorTempCalibrationConfig::schedule();
    }

    protected function stepFieldMap(): array
    {
        return IncubatorTempCalibrationConfig::stepFieldMap();
    }

    protected function formTypeName(): string
    {
        return IncubatorTempCalibrationConfig::getFormTypeName();
    }

    protected function messages(): array
    {
        return IncubatorTempCalibrationConfig::getMessages();
    }

    public function submitForm()
    {
        $this->formSubmitted = true;

        try {
            $this->validate(IncubatorTempCalibrationConfig::getRules(), $this->messages());

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

            return redirect()->route('forms.incubator-temp-calibration');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstKey = array_key_first($e->validator->errors()->messages());
            if ($firstKey) {
                $fieldName = str_replace('form.', '', $firstKey);
                $this->goToStepWithField($fieldName);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Form submission failed', [
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

            $hatcheryMan = $this->form['hatchery_man'] ?? null;

            $formId = (int) DB::table('forms')->insertGetId([
                'form_type_id'   => $formTypeId,
                'form_inputs'    => json_encode($formInputs),
                'date_submitted' => now(),
                'uploaded_by'    => $hatcheryMan,
                'created_at'     => now(),
                'updated_at'     => now(),
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

        unset($inputs['hatchery_man']);
        unset($inputs['incubator']);

        $inputs['date_submitted'] = now()->format('Y-m-d');

        if (!empty($this->form['incubator'])) {
            $incubator = DB::table('incubator-machines')
                ->where('id', $this->form['incubator'])
                ->first();

            if ($incubator) {
                $inputs['machine_info'] = [
                    'table' => 'incubator-machines',
                    'id'    => $this->form['incubator'],
                    'name'  => $incubator->incubatorName,
                ];
            }
        }

        return $inputs;
    }

    protected function updateCompletedIncubators(): void
    {
        $today = now()->format('Y-m-d');
        $formTypeName = $this->formTypeName();

        $formTypeId = DB::table('form_types')
            ->where('form_name', $formTypeName)
            ->value('id');

        if (!$formTypeId) {
            $this->completedIncubators = [];
            return;
        }

        $completedForms = DB::table('forms')
            ->where('form_type_id', $formTypeId)
            ->whereDate('date_submitted', $today)
            ->whereNotNull('form_inputs')
            ->get();

        $this->completedIncubators = [];

        foreach ($completedForms as $form) {
            $formInputs = is_array($form->form_inputs)
                ? $form->form_inputs
                : json_decode($form->form_inputs, true);

            if (isset($formInputs['machine_info']['id'])) {
                $this->completedIncubators[] = $formInputs['machine_info']['id'];
            }
        }
    }

    protected function sendFormToWebhook(int $formId): void
    {
        try {
            $webhookUrl = config('services.webhook.url');

            if (!$webhookUrl) {
                return;
            }

            $form = DB::table('forms')
                ->select('forms.*', 'form_types.form_name as form_type_name', 'users.first_name', 'users.last_name')
                ->leftJoin('form_types', 'forms.form_type_id', '=', 'form_types.id')
                ->leftJoin('users', 'forms.uploaded_by', '=', 'users.id')
                ->where('forms.id', $formId)
                ->first();

            if (!$form) {
                return;
            }

            $formInputs = is_array($form->form_inputs)
                ? $form->form_inputs
                : json_decode($form->form_inputs, true);
            $formInputs = (array) $formInputs;

            $machineInfo = $formInputs['machine_info'] ?? null;

            $payload = [
                'form' => [
                    'form_id'   => $form->id,
                    'form_name' => $form->form_type_name ?: 'Unknown Form Type',
                ],
                'records'        => $formInputs,
                'date_submitted' => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                'uploaded_by'    => $form->uploaded_by ? [
                    'id'   => $form->uploaded_by,
                    'name' => trim(($form->first_name ?: '') . ' ' . ($form->last_name ?: '')) ?: 'Unknown User',
                ] : null,
                'machine'  => $machineInfo,
                'message'  => [
                    'form_name'    => $form->form_type_name ?: 'Unknown Form Type',
                    'machine_name' => is_array($machineInfo) ? ($machineInfo['name'] ?? null) : null,
                    'submitted_by' => $form->uploaded_by ? trim(($form->first_name ?: '') . ' ' . ($form->last_name ?: '')) : null,
                    'date_time'    => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                    'photos'       => [],
                    'shift'        => $formInputs['shift'] ?? 'N/A',
                ],
                'timestamp' => now()->toISOString(),
            ];

            Http::post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending form to webhook', [
                'form_id' => $formId,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.shared.forms.incubator-temp-calibration-form');
    }
}

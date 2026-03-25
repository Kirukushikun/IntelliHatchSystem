<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Components\FormNavigation;
use App\Livewire\Configs\WeeklyVoltAmpereConfig;
use App\Livewire\Shared\Forms\Traits\TempPhotoManager;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

class WeeklyVoltAmpereForm extends FormNavigation
{
    use WithFileUploads, TempPhotoManager;

    public array $form = [];

    public array $photoUploads = [];

    public bool $formSubmitted = false;

    protected bool $disableShiftLogic = true;

    public array $personnelList = [];

    public function mount($formType = 'weekly_volt_ampere'): void
    {
        $this->form = WeeklyVoltAmpereConfig::defaultFormState();

        parent::mount($formType);
        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();

        $this->personnelList = User::where('is_disabled', false)
            ->whereIn('user_type', [1, 2])
            ->orderBy('first_name')
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

    protected function formTypeKey(): string
    {
        return 'weekly_volt_ampere';
    }

    protected function scheduleConfig(): array
    {
        return WeeklyVoltAmpereConfig::schedule();
    }

    protected function stepFieldMap(): array
    {
        return WeeklyVoltAmpereConfig::stepFieldMap();
    }

    protected function formTypeName(): string
    {
        return WeeklyVoltAmpereConfig::getFormTypeName();
    }

    protected function messages(): array
    {
        return WeeklyVoltAmpereConfig::getMessages();
    }

    public function submitForm()
    {
        $this->formSubmitted = true;

        try {
            $this->validate(WeeklyVoltAmpereConfig::getRules(), $this->messages());

            $formTypeKey = $this->formTypeKey();

            // voltage_ampere_photos is required
            $voltageAmperePhotos = session("temp_photos.{$formTypeKey}.voltage_ampere_photos", []);
            if (empty($voltageAmperePhotos)) {
                $this->dispatch('showToast', message: 'Please upload at least one photo of the voltage and ampere readings before submitting.', type: 'error');
                $this->goToStepWithField('voltage_ampere_photos');
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

            return redirect()->route('forms.weekly-volt-ampere');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstKey = array_key_first($e->validator->errors()->messages());
            if ($firstKey) {
                $fieldName = str_replace('form.', '', $firstKey);
                $this->goToStepWithField($fieldName);
            }

            throw $e;
        } catch (\Exception $e) {
            Log::error('Weekly Voltage and Ampere Monitoring form submission failed', [
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
                'form_type_id'   => $formTypeId,
                'form_inputs'    => json_encode($formInputs),
                'date_submitted' => now(),
                'uploaded_by'    => null,
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

        if (!empty($inputs['maintenance_personnel']) && isset($this->personnelList[$inputs['maintenance_personnel']])) {
            $inputs['machine_info'] = [
                'table' => 'users',
                'id'    => $inputs['maintenance_personnel'],
                'name'  => $this->personnelList[$inputs['maintenance_personnel']],
            ];
            $inputs['maintenance_personnel_name'] = $this->personnelList[$inputs['maintenance_personnel']];
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
                    'submitted_by' => $formInputs['maintenance_personnel_name'] ?? null,
                    'date_time'    => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                    'photos'       => [],
                    'shift'        => 'N/A',
                ],
                'timestamp' => now()->toISOString(),
            ];

            Http::post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending Weekly Voltage and Ampere Monitoring form to webhook', [
                'form_id' => $formId,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.shared.forms.weekly-volt-ampere-form');
    }
}

<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Configs\PlenumTempHumidityConfig;
use App\Livewire\Components\FormNavigation;
use App\Livewire\Shared\Forms\Traits\TempPhotoManager;
use App\Models\Hatcher;
use App\Models\Incubator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

class PlenumTempHumidityForm extends FormNavigation
{
    use WithFileUploads, TempPhotoManager;

    public array $form = [];

    public array $photoUploads = [];

    public bool $formSubmitted = false;

    protected bool $disableShiftLogic = true;

    public ?int $uploadedBy = null;

    /** @var array */
    public $incubators = [];

    /** @var array */
    public $hatchers = [];

    /** @var array */
    public $hatcheryMen = [];

    public function mount($formType = 'plenum_temp_humidity'): void
    {
        $this->form = PlenumTempHumidityConfig::defaultFormState();
        $this->form['date'] = now()->format('Y-m-d');

        parent::mount($formType);
        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();

        $this->incubators = Incubator::where('isActive', true)
            ->orderBy('incubatorName')
            ->get()
            ->mapWithKeys(fn ($m) => [$m->id => $m->incubatorName])
            ->toArray();

        $this->hatchers = Hatcher::where('isActive', true)
            ->orderBy('hatcherName')
            ->get()
            ->mapWithKeys(fn ($m) => [$m->id => $m->hatcherName])
            ->toArray();

        $this->hatcheryMen = User::where('user_type', 2)
            ->where('is_disabled', false)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(fn ($u) => [$u->id => $u->first_name . ' ' . $u->last_name])
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

        $this->handleTempPhotoUpload($photoKey, $files, $this->formTypeKey());
    }

    public function addIncubatorReading(): void
    {
        if (count($this->form['incubator_readings']) >= count($this->incubators)) {
            return;
        }

        $this->form['incubator_readings'][] = ['incubator_id' => '', 'temperature' => '', 'humidity' => ''];
    }

    public function removeIncubatorReading(int $index): void
    {
        if (count($this->form['incubator_readings']) <= 1) {
            return;
        }

        array_splice($this->form['incubator_readings'], $index, 1);
        $this->form['incubator_readings'] = array_values($this->form['incubator_readings']);
    }

    public function addHatcherReading(): void
    {
        if (count($this->form['hatcher_readings']) >= count($this->hatchers)) {
            return;
        }

        $this->form['hatcher_readings'][] = ['hatcher_id' => '', 'temperature' => '', 'humidity' => ''];
    }

    public function removeHatcherReading(int $index): void
    {
        if (count($this->form['hatcher_readings']) <= 1) {
            return;
        }

        array_splice($this->form['hatcher_readings'], $index, 1);
        $this->form['hatcher_readings'] = array_values($this->form['hatcher_readings']);
    }

    protected function formTypeKey(): string
    {
        return 'plenum_temp_humidity';
    }

    protected function scheduleConfig(): array
    {
        return PlenumTempHumidityConfig::schedule();
    }

    protected function stepFieldMap(): array
    {
        return PlenumTempHumidityConfig::stepFieldMap();
    }

    protected function formTypeName(): string
    {
        return PlenumTempHumidityConfig::getFormTypeName();
    }

    protected function messages(): array
    {
        return PlenumTempHumidityConfig::getMessages();
    }

    public function submitForm()
    {
        $this->formSubmitted = true;

        try {
            $this->validate(PlenumTempHumidityConfig::getRules(), $this->messages());

            if (!$this->ensureAllPhotosUploaded()) {
                $this->dispatch('showToast', message: 'Photo uploads are still in progress. Please wait for all photos to finish uploading before submitting the form.', type: 'error');
                return;
            }

            $formId = $this->storeSubmissionAndReturnId($this->formTypeName(), $this->formInputsForStorageWithoutPhotos());
            $this->finalizePhotosForForm($formId);

            DB::table('forms')->where('id', $formId)->update([
                'form_inputs' => json_encode($this->formInputsWithPhotos($this->formInputsForStorageWithoutPhotos())),
                'updated_at' => now(),
            ]);

            $this->sendFormToWebhook($formId);

            session()->flash('success', 'Form submitted successfully!');

            return redirect()->route('forms.plenum-temp-humidity');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstKey = array_key_first($e->validator->errors()->messages());
            if ($firstKey) {
                $fieldName = str_replace('form.', '', $firstKey);
                // For array fields like "incubator_readings.0.temperature", extract the base key
                $fieldName = explode('.', $fieldName)[0];
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

            $hatcheryman = $this->form['hatcheryman'] ?? null;

            $formId = (int) DB::table('forms')->insertGetId([
                'form_type_id'   => $formTypeId,
                'form_inputs'    => json_encode($formInputs),
                'date_submitted' => now(),
                'uploaded_by'    => $this->uploadedBy ?: $hatcheryman,
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
        unset($inputs['hatcheryman']);

        $incubatorReadings = [];
        foreach ($this->form['incubator_readings'] as $reading) {
            if (empty($reading['incubator_id'])) {
                continue;
            }
            $machine = DB::table('incubator-machines')->where('id', $reading['incubator_id'])->first();
            $incubatorReadings[] = [
                'incubator_id'   => (int) $reading['incubator_id'],
                'incubator_name' => $machine ? $machine->incubatorName : null,
                'temperature'    => $reading['temperature'],
                'humidity'       => $reading['humidity'],
            ];
        }

        $hatcherReadings = [];
        foreach ($this->form['hatcher_readings'] as $reading) {
            if (empty($reading['hatcher_id'])) {
                continue;
            }
            $machine = DB::table('hatcher-machines')->where('id', $reading['hatcher_id'])->first();
            $hatcherReadings[] = [
                'hatcher_id'   => (int) $reading['hatcher_id'],
                'hatcher_name' => $machine ? $machine->hatcherName : null,
                'temperature'  => $reading['temperature'],
                'humidity'     => $reading['humidity'],
            ];
        }

        $inputs['incubator_readings'] = $incubatorReadings;
        $inputs['hatcher_readings']   = $hatcherReadings;

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

            $incubatorReadings = $formInputs['incubator_readings'] ?? [];
            $hatcherReadings   = $formInputs['hatcher_readings'] ?? [];

            $machineNames = array_merge(
                array_column((array) $incubatorReadings, 'incubator_name'),
                array_column((array) $hatcherReadings, 'hatcher_name'),
            );
            $machineNamesStr = implode(', ', array_filter($machineNames));

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
                'message' => [
                    'form_name'    => $form->form_type_name ?: 'Unknown Form Type',
                    'machine_name' => $machineNamesStr ?: null,
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
        return view('livewire.shared.forms.plenum-temp-humidity-form');
    }
}

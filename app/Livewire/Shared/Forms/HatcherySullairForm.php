<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Components\FormNavigation;
use App\Livewire\Configs\HatcherySullairConfig;
use App\Livewire\Shared\Forms\Traits\TempPhotoManager;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

class HatcherySullairForm extends FormNavigation
{
    use WithFileUploads, TempPhotoManager;

    public array $form = [];

    public array $photoUploads = [];

    public bool $formSubmitted = false;

    protected bool $disableShiftLogic = true;

    /** @var array */
    public $hatcheryMen = [];

    /** @var int|null Store uploaded_by user ID from name field */
    public ?int $uploadedBy = null;

    public function mount($formType = 'hatchery_sullair'): void
    {
        $this->form = HatcherySullairConfig::defaultFormState();

        parent::mount($formType);
        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();

        $this->hatcheryMen = User::where('user_type', 1)
            ->where('is_disabled', false)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->id => $user->first_name . ' ' . $user->last_name];
            })
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
        return 'hatchery_sullair';
    }

    protected function scheduleConfig(): array
    {
        return HatcherySullairConfig::schedule();
    }

    protected function stepFieldMap(): array
    {
        return HatcherySullairConfig::stepFieldMap();
    }

    protected function formTypeName(): string
    {
        return HatcherySullairConfig::getFormTypeName();
    }

    protected function messages(): array
    {
        return HatcherySullairConfig::getMessages();
    }

    public function submitForm()
    {
        $this->formSubmitted = true;

        $this->validate(HatcherySullairConfig::getRules(), $this->messages());

        try {
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

            return redirect()->route('forms.hatchery-sullair');
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
                'form_type_id' => $formTypeId,
                'form_inputs' => json_encode($formInputs),
                'date_submitted' => now(),
                'uploaded_by' => $this->uploadedBy ?: $hatcheryMan,
                'created_at' => now(),
                'updated_at' => now(),
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

        $sullairNumber = (string) ($this->form['sullair_number'] ?? '');
        if ($sullairNumber !== '') {
            $inputs['machine_info'] = [
                'table' => null,
                'id' => null,
                'name' => $sullairNumber,
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
                ->select('forms.*', 'form_types.form_name as form_type_name', 'users.first_name', 'users.last_name')
                ->leftJoin('form_types', 'forms.form_type_id', '=', 'form_types.id')
                ->leftJoin('users', 'forms.uploaded_by', '=', 'users.id')
                ->where('forms.id', $formId)
                ->first();

            if (!$form) {
                return;
            }

            $formInputs = is_array($form->form_inputs) ? $form->form_inputs : json_decode($form->form_inputs, true);
            $formInputs = (array) $formInputs;

            $machineInfo = isset($formInputs['machine_info']) ? $formInputs['machine_info'] : null;

            $payload = [
                'form' => [
                    'form_id' => $form->id,
                    'form_name' => $form->form_type_name ?: 'Unknown Form Type',
                ],
                'records' => $formInputs,
                'date_submitted' => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                'uploaded_by' => $form->uploaded_by ? [
                    'id' => $form->uploaded_by,
                    'name' => trim(($form->first_name ?: '') . ' ' . ($form->last_name ?: '')) ?: 'Unknown User',
                ] : null,
                'machine' => $machineInfo,
                'message' => [
                    'form_name' => $form->form_type_name ?: 'Unknown Form Type',
                    'machine_name' => is_array($machineInfo) ? ($machineInfo['name'] ?? null) : null,
                    'submitted_by' => $form->uploaded_by ? trim(($form->first_name ?: '') . ' ' . ($form->last_name ?: '')) : null,
                    'date_time' => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                    'photos' => [],
                    'shift' => 'N/A',
                ],
                'timestamp' => now()->toISOString(),
            ];

            Http::post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending form to webhook', [
                'form_id' => $formId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.shared.forms.hatchery-sullair-form');
    }
}

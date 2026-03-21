<?php

namespace App\Livewire\Admin\AiChat;

use App\Jobs\ProcessAiChatRequest;
use App\Models\AiChat;
use App\Models\FormType;
use App\Models\SystemPrompt;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;

    #[Validate('required|string|min:10|max:2000')]
    public string $prompt = '';

    #[Validate(['selectedFormTypeIds' => 'array', 'selectedFormTypeIds.*' => 'integer|exists:form_types,id'])]
    public array $selectedFormTypeIds = [];

    #[Validate('required|in:week,month,all,custom')]
    public string $contextPeriod = 'week';

    #[Validate('nullable|date|required_if:contextPeriod,custom')]
    public string $dateFrom = '';

    #[Validate('nullable|date|required_if:contextPeriod,custom|after_or_equal:dateFrom')]
    public string $dateTo = '';

    public bool $hasPending = false;

    public array $formTypes = [];

    public function mount(): void
    {
        $this->formTypes = FormType::orderBy('form_name')
            ->get(['id', 'form_name'])
            ->toArray();
    }

    public function submit(): void
    {
        $this->validate();

        // Rate limit: max 10 requests per hour per user
        $recentCount = AiChat::where('user_id', auth()->id())
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentCount >= 10) {
            $this->addError('prompt', 'Rate limit reached: max 10 requests per hour.');

            return;
        }

        $systemPrompt = SystemPrompt::active()->first()?->prompt;
        $formTypeIds = array_values(array_map('intval', $this->selectedFormTypeIds));

        $chat = AiChat::create([
            'user_id' => auth()->id(),
            'prompt' => $this->prompt,
            'system_prompt_snapshot' => $systemPrompt,
            'form_type_ids' => empty($formTypeIds) ? null : $formTypeIds,
            'context_period' => $this->contextPeriod,
            'context_date_from' => $this->contextPeriod === 'custom' ? $this->dateFrom : null,
            'context_date_to' => $this->contextPeriod === 'custom' ? $this->dateTo : null,
            'status' => 'pending',
        ]);

        ProcessAiChatRequest::dispatch($chat->id);

        $this->reset(['prompt', 'selectedFormTypeIds', 'showForm', 'dateFrom', 'dateTo']);
        $this->contextPeriod = 'week';
        $this->resetPage();
    }

    public function retry(int $id): void
    {
        $chat = AiChat::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'failed')
            ->first();

        if (! $chat) {
            return;
        }

        $chat->update([
            'status' => 'pending',
            'response' => null,
            'error_message' => null,
        ]);

        ProcessAiChatRequest::dispatch($chat->id);
    }

    public function delete(int $id): void
    {
        AiChat::where('id', $id)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['done', 'failed'])
            ->delete();
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;

        if (! $this->showForm) {
            $this->reset(['prompt', 'selectedFormTypeIds', 'dateFrom', 'dateTo']);
            $this->resetErrorBag();
            $this->contextPeriod = 'week';
        }
    }

    public function render()
    {
        $chats = AiChat::where('user_id', auth()->id())
            ->with('formType')
            ->orderByDesc('created_at')
            ->paginate(10);

        $this->hasPending = AiChat::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'analyzing'])
            ->exists();

        return view('livewire.admin.ai-chat.index', compact('chats'));
    }
}

<?php

namespace App\Jobs;

use App\Models\AiChat;
use App\Models\FormType;
use App\Services\OpenRouterClient;
use Carbon\Carbon;
use Carbon\Constants\UnitValue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAiChatRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 1;

    public function __construct(public readonly int $aiChatId) {}

    public function handle(): void
    {
        $chat = AiChat::find($this->aiChatId);

        if (! $chat || $chat->status === 'failed') {
            return;
        }

        $chat->update(['status' => 'analyzing']);

        try {
            $contextData = $this->buildContextData($chat);

            $chat->update(['context_data' => $contextData]);

            $systemPrompt = $chat->system_prompt_snapshot ?: $this->defaultSystemPrompt();

            $userMessage = "User Question:\n{$chat->prompt}\n\n--- Hatchery Data Context ---\n{$contextData}";

            $client = new OpenRouterClient;
            $response = $client->ask($userMessage, $systemPrompt);

            $chat->update([
                'status' => 'done',
                'response' => $response,
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessAiChatRequest failed', [
                'aiChatId' => $this->aiChatId,
                'error' => $e->getMessage(),
            ]);

            $chat->update([
                'status' => 'failed',
                'error_message' => 'Failed to generate a response. Please try again.',
            ]);
        }
    }

    private function buildContextData(AiChat $chat): string
    {
        [$start, $end, $label] = $this->dateRange($chat);

        // Use multi-select form_type_ids if present; fall back to legacy single form_type_id
        $selectedIds = $chat->form_type_ids ?? [];

        if (empty($selectedIds) && $chat->form_type_id) {
            $selectedIds = [$chat->form_type_id];
        }

        $formTypeIds = empty($selectedIds)
            ? DB::table('form_types')->pluck('id')->toArray()
            : $selectedIds;

        $sections = [];

        foreach ($formTypeIds as $typeId) {
            $formType = FormType::find($typeId);
            if (! $formType) {
                continue;
            }

            $forms = DB::table('forms')
                ->where('form_type_id', $typeId)
                ->whereBetween('date_submitted', [$start, $end])
                ->orderBy('date_submitted', 'asc')
                ->get(['id', 'form_inputs', 'date_submitted']);

            $total = $forms->count();

            if ($total === 0) {
                $sections[] = "## {$formType->form_name}\nPeriod: {$label}\nSubmissions: 0 — No data for this period.";

                continue;
            }

            // Daily breakdown
            $byDay = [];
            foreach ($forms as $form) {
                $day = Carbon::parse($form->date_submitted)->format('Y-m-d');
                $byDay[$day] = ($byDay[$day] ?? 0) + 1;
            }

            // Field value aggregation (sample up to 50)
            $sample = $forms->take(50);
            $fieldAggregates = [];

            foreach ($sample as $form) {
                $inputs = is_string($form->form_inputs)
                    ? json_decode($form->form_inputs, true)
                    : (array) $form->form_inputs;

                if (! is_array($inputs)) {
                    continue;
                }

                foreach ($inputs as $key => $value) {
                    if (is_array($value) || is_null($value) || (string) $value === '') {
                        continue;
                    }
                    $fieldAggregates[$key][] = (string) $value;
                }
            }

            $fieldLines = [];
            foreach ($fieldAggregates as $field => $values) {
                $counts = array_count_values($values);
                arsort($counts);
                $top = array_slice($counts, 0, 5, true);
                $parts = [];
                foreach ($top as $val => $cnt) {
                    $parts[] = "{$val} ({$cnt}x)";
                }
                $fieldLines[] = "  {$field}: ".implode(', ', $parts);
            }

            $byDayText = '';
            foreach ($byDay as $day => $cnt) {
                $byDayText .= "  {$day}: {$cnt} submission(s)\n";
            }

            $sections[] = implode("\n", array_filter([
                "## {$formType->form_name}",
                "Period: {$label}",
                "Total Submissions: {$total}",
                'Daily Breakdown:',
                rtrim($byDayText),
                "Field Value Summary (top values from {$sample->count()} sampled records):",
                implode("\n", $fieldLines),
            ]));
        }

        if (empty($sections)) {
            return 'No hatchery data found for the selected scope and period.';
        }

        return implode("\n\n---\n\n", $sections);
    }

    private function dateRange(AiChat $chat): array
    {
        $period = $chat->context_period;
        $now = now();

        if ($period === 'custom') {
            $from = Carbon::parse($chat->context_date_from)->startOfDay();
            $to = Carbon::parse($chat->context_date_to)->endOfDay();
            $label = 'Custom Range ('.$from->format('M d, Y').' – '.$to->format('M d, Y').')';

            return [$from, $to, $label];
        }

        return match ($period) {
            'week' => [
                $now->copy()->startOfWeek(UnitValue::SUNDAY)->startOfDay(),
                $now->copy()->endOfWeek(UnitValue::SATURDAY)->endOfDay(),
                'Current Week ('.$now->copy()->startOfWeek(UnitValue::SUNDAY)->format('M d')
                    .' – '.$now->copy()->endOfWeek(UnitValue::SATURDAY)->format('M d, Y').')',
            ],
            'month' => [
                $now->copy()->startOfMonth()->startOfDay(),
                $now->copy()->endOfMonth()->endOfDay(),
                'Current Month ('.$now->format('F Y').')',
            ],
            default => [
                $now->copy()->subDays(90)->startOfDay(),
                $now->copy()->endOfDay(),
                'Last 90 Days',
            ],
        };
    }

    private function defaultSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert Poultry Hatchery Data Analyst and AI assistant for IntelliHatchSystem.
Your role is to analyze hatchery data and answer questions from hatchery staff and administrators.

When given a user question and data context, analyze the data thoroughly and provide a clear, actionable response.
Be professional and concise. Focus on practical insights that help improve hatchery operations.
Format your response with clear headings and bullet points where appropriate.
If the data context is limited or empty, say so and provide general guidance based on your expertise.
PROMPT;
    }
}

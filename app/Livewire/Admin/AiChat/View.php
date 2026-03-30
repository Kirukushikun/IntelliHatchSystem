<?php

namespace App\Livewire\Admin\AiChat;

use App\Models\AiChat;
use App\Services\OpenRouterClient;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class View extends Component
{
    public int $chatId;
    public ?AiChat $chat = null;
    public bool $isPending = false;

    public string $translatedResponse = '';
    public bool $showTranslation = false;
    public bool $isTranslating = false;

    public function mount(int $chatId): void
    {
        $this->chatId = $chatId;
        $this->loadChat();
    }

    public function refresh(): void
    {
        $this->loadChat();
    }

    protected function loadChat(): void
    {
        $chat = AiChat::with('formType')->find($this->chatId);

        abort_if(! $chat, 404);
        abort_if($chat->user_id !== auth()->id(), 403);

        $this->chat      = $chat;
        $this->isPending = $chat->isPending();

        $tlKey = $this->translationCacheKey();
        if (Cache::has($tlKey)) {
            $this->translatedResponse = Cache::get($tlKey);
        }
    }

    public function translate(): void
    {
        if (! $this->chat?->response) {
            return;
        }

        $tlKey = $this->translationCacheKey();

        if (Cache::has($tlKey)) {
            $this->translatedResponse = Cache::get($tlKey);
            $this->showTranslation    = true;

            return;
        }

        $this->isTranslating = true;

        try {
            $client = new OpenRouterClient();

            $translated = $client->ask(
                userMessage:  $this->chat->response,
                systemPrompt: 'Isinalin mo ang teksto sa Filipino (Tagalog). Gamitin ang natural na Taglish (halo ng Tagalog at Ingles) tulad ng karaniwang ginagamit sa Pilipinas. Panatilihin ang lahat ng numero, sukat, pangalan ng makina, teknikal na abbreviation, at unit ng pagsukat sa Ingles. Huwag magdagdag ng sariling komento — isinalin lamang ang ibinigay na teksto.',
            );

            Cache::put($tlKey, $translated, now()->addHours(6));

            $this->translatedResponse = $translated;
            $this->showTranslation    = true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AiChat translate() failed', [
                'chatId' => $this->chatId,
                'error'  => $e->getMessage(),
            ]);
        } finally {
            $this->isTranslating = false;
        }
    }

    public function toggleLanguage(): void
    {
        $this->showTranslation = ! $this->showTranslation;
    }

    public function openPrint(): void
    {
        if ($this->chat?->status !== 'done') {
            return;
        }

        $url = \Illuminate\Support\Facades\URL::signedRoute('admin.print.ai-chat', [
            'id' => $this->chatId,
        ]);

        $this->dispatch('open-print-window', url: $url);
    }

    protected function translationCacheKey(): string
    {
        return "ai-chat:{$this->chatId}:tl";
    }

    public function renderedResponse(): string
    {
        if (! $this->chat?->response) {
            return '';
        }

        $source = ($this->showTranslation && $this->translatedResponse !== '')
            ? $this->translatedResponse
            : $this->chat->response;

        return $this->renderMarkdown($source);
    }

    private function renderMarkdown(string $markdown): string
    {
        $lines       = explode("\n", str_replace("\r\n", "\n", $markdown));
        $html        = '';
        $inList      = false;
        $listTag     = '';
        $inCode      = false;
        $codeBuffer  = '';
        $inTable     = false;
        $tableBuffer = [];

        $closeList  = function () use (&$html, &$inList, &$listTag) {
            if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
        };
        $closeTable = function () use (&$html, &$inTable, &$tableBuffer) {
            if ($inTable) { $html .= $this->renderTable($tableBuffer); $inTable = false; $tableBuffer = []; }
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Fenced code block toggle
            if (preg_match('/^```/', $trimmed)) {
                if (! $inCode) {
                    $closeList();
                    $closeTable();
                    $inCode     = true;
                    $codeBuffer = '';
                } else {
                    $escaped = htmlspecialchars($codeBuffer);
                    $html   .= "<pre class=\"bg-gray-100 dark:bg-gray-900 rounded-lg p-4 overflow-x-auto my-3 text-xs font-mono text-gray-800 dark:text-gray-200\">{$escaped}</pre>";
                    $inCode     = false;
                    $codeBuffer = '';
                }
                continue;
            }

            if ($inCode) {
                $codeBuffer .= $line . "\n";
                continue;
            }

            // Table row (starts and ends with |)
            if (preg_match('/^\|.*\|$/', $trimmed)) {
                // Separator row — skip but don't end the table
                if (preg_match('/^[\|\-\:\s]+$/', $trimmed)) {
                    continue;
                }
                if (! $inTable) {
                    $closeList();
                    $inTable     = true;
                    $tableBuffer = [];
                }
                $tableBuffer[] = $trimmed;
                continue;
            } elseif ($inTable) {
                $closeTable();
            }

            if ($trimmed === '') {
                $closeList();
                continue;
            }

            // h1 (single #)
            if (preg_match('/^#(?!#)\s+(.+)$/', $trimmed, $m)) {
                $closeList();
                $out   = $this->inline($m[1]);
                $html .= "<h2 class=\"text-lg font-bold text-gray-900 dark:text-white mt-6 mb-2\">{$out}</h2>";
                continue;
            }

            // h2 (##)
            if (preg_match('/^##(?!#)\s+(.+)$/', $trimmed, $m)) {
                $closeList();
                $out   = $this->inline($m[1]);
                $html .= "<h3 class=\"text-base font-semibold text-gray-900 dark:text-white mt-5 mb-1.5\">{$out}</h3>";
                continue;
            }

            // h3 (###)
            if (preg_match('/^###\s+(.+)$/', $trimmed, $m)) {
                $closeList();
                $out   = $this->inline($m[1]);
                $html .= "<h4 class=\"text-sm font-semibold text-gray-800 dark:text-gray-200 mt-4 mb-1\">{$out}</h4>";
                continue;
            }

            // Bold-only line → sub-heading
            if (preg_match('/^\*\*(.+?)\*\*:?\s*$/', $trimmed, $m)) {
                $closeList();
                $sub   = htmlspecialchars($m[1]);
                $html .= "<p class=\"text-sm font-semibold text-gray-900 dark:text-white mt-3 mb-0.5\">{$sub}</p>";
                continue;
            }

            // Bullet list item
            if (preg_match('/^[-*•]\s+(.+)$/', $trimmed, $m)) {
                if (! $inList || $listTag !== 'ul') {
                    if ($inList) { $html .= "</{$listTag}>"; }
                    $html   .= '<ul class="space-y-1.5 mt-1.5 mb-2">';
                    $inList  = true;
                    $listTag = 'ul';
                }
                $content = $this->inline($m[1]);
                $html   .= "<li class=\"flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300\">"
                    . "<span class=\"w-1.5 h-1.5 rounded-full bg-orange-400 mt-1.5 shrink-0\"></span>"
                    . "<span>{$content}</span></li>";
                continue;
            }

            // Numbered list item
            if (preg_match('/^(\d+)\.\s+(.+)$/', $trimmed, $m)) {
                if (! $inList || $listTag !== 'ol') {
                    if ($inList) { $html .= "</{$listTag}>"; }
                    $html   .= '<ol class="space-y-1.5 mt-1.5 mb-2 list-none">';
                    $inList  = true;
                    $listTag = 'ol';
                }
                $num     = $m[1];
                $content = $this->inline($m[2]);
                $html   .= "<li class=\"flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300\">"
                    . "<span class=\"shrink-0 w-5 h-5 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-xs font-bold flex items-center justify-center mt-0.5\">{$num}</span>"
                    . "<span>{$content}</span></li>";
                continue;
            }

            // Horizontal rule
            if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $trimmed)) {
                $closeList();
                $html .= '<hr class="my-4 border-gray-200 dark:border-gray-700">';
                continue;
            }

            // Regular paragraph
            $closeList();
            $para  = $this->inline($trimmed);
            $html .= "<p class=\"text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-2\">{$para}</p>";
        }

        // Close any open blocks
        if ($inCode) {
            $escaped = htmlspecialchars($codeBuffer);
            $html   .= "<pre class=\"bg-gray-100 dark:bg-gray-900 rounded-lg p-4 overflow-x-auto my-3 text-xs font-mono text-gray-800 dark:text-gray-200\">{$escaped}</pre>";
        }
        if ($inTable) {
            $html .= $this->renderTable($tableBuffer);
        }
        if ($inList) {
            $html .= "</{$listTag}>";
        }

        return $html;
    }

    private function renderTable(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $html = '<div class="overflow-x-auto my-3"><table class="w-full text-sm border-collapse">';

        foreach ($rows as $i => $row) {
            $cells = array_map('trim', explode('|', trim($row, '|')));

            if ($i === 0) {
                $html .= '<thead><tr>';
                foreach ($cells as $cell) {
                    $html .= '<th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-700">'
                        . $this->inline($cell) . '</th>';
                }
                $html .= '</tr></thead><tbody>';
            } else {
                $html .= '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">';
                foreach ($cells as $cell) {
                    $html .= '<td class="px-3 py-2 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">'
                        . $this->inline($cell) . '</td>';
                }
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table></div>';

        return $html;
    }

    private function inline(string $text): string
    {
        $text = htmlspecialchars($text);
        // Bold
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong class="font-semibold text-gray-900 dark:text-white">$1</strong>', $text);
        // Strip leftover unmatched **
        $text = str_replace('**', '', $text);
        // Italic
        $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/', '<em class="italic">$1</em>', $text);
        // Inline code
        $text = preg_replace('/`(.+?)`/', '<code class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-1 py-0.5 rounded font-mono">$1</code>', $text);

        return $text;
    }

    public function render()
    {
        return view('livewire.admin.ai-chat.view');
    }
}

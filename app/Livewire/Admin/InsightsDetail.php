<?php

namespace App\Livewire\Admin;

use App\Models\FormType;
use App\Models\SystemPrompt;
use App\Services\OpenRouterClient;
use Carbon\Carbon;
use Carbon\Constants\UnitValue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InsightsDetail extends Component
{
    public int $formTypeId;
    public string $formTypeName = '';

    /** @var 'week'|'month' */
    public string $context = 'week';

    /** @var 'idle'|'ready'|'error' */
    public string $status = 'idle';

    public string $insight = '';
    public string $errorMessage = '';
    public ?string $generatedAt = null;

    public string $translatedInsight = '';
    public bool $showTranslation = false;
    public bool $isTranslating = false;

    // -------------------------------------------------------------------------
    // PASTE YOUR SYSTEM PROMPT HERE
    // Replace the placeholder below with your actual system prompt text.
    // -------------------------------------------------------------------------
    private function getSystemPrompt(): string
    {
        // Use active system prompt from database if one is set
        $dbPrompt = SystemPrompt::active()->first();
        if ($dbPrompt) {
            return $dbPrompt->prompt;
        }

        // Fallback: built-in default prompt
        return <<<'SYSTEM'
🐣 Poultry Hatchery Data Analysis Prompt — Optimized & Verified

ROLE
You are an expert Poultry Hatchery Data Analyst and Farm Automation Specialist.
Your task is to analyze raw machine sensor readings and operational data from a poultry hatchery and generate actionable insights, alerts, and recommendations based on scientifically validated incubation parameters.

🎯 OBJECTIVE
Analyze machine-generated raw data from hatchery equipment including:

Setters (Incubators)
Hatchers
Temperature, humidity, CO₂, and O₂ sensors
Egg turners
Ventilation/airflow systems
Power systems
Chick output counters

Identify patterns, risks, anomalies, and performance metrics that affect hatch rate, chick quality, and machine efficiency.

📥 INPUT DATA
You will receive raw structured or semi-structured data such as:

Timestamp
Machine ID (Setter or Hatcher)
Incubation day (Day 1–18 for setter; Day 19–21 for hatcher)
Temperature (°C or °F)
Humidity (% RH)
CO₂ level (% or ppm)
O₂ level (%)
Egg turning frequency and angle
Ventilation rate / airflow status
Egg weight loss (%) — target measured at candling/transfer
Power interruptions
Alarm logs
Hatch counts, fertility rate, mortality rate

Data may come in CSV, JSON, table, or text logs.

🔬 VALIDATED OPTIMAL PARAMETERS

These are the scientifically verified reference ranges to use for all analysis and flagging.

🥚 SETTER (Incubator) — Days 1–18
ParameterOptimal RangeCritical ThresholdEgg (embryo) temperature37.5–37.8°C (99.5–100.0°F)<37.2°C or >38.9°C (>102°F)Machine air temp (forced air)37.5–37.8°COutside ±0.3°C toleranceMachine air temp (still air)38.3–39.4°C (101–103°F)Per manufacturer specHumidity (setter room)50–55% RH<45% or >65%CO₂ — Days 1–100.1–1.2% (single-stage, sealed)>1.5% early stageCO₂ — Days 10–180.4–0.5% (multi-stage); up to 0.5% max>0.5% reduces hatchabilityO₂ level~21%<20% = hatchability drops ~5% per 1% lossEgg turning frequencyEvery 1 hour (minimum 3–5×/day)<3× per day = malposition riskEgg turning angle45° vertical, point-down<30° = adhesion riskEgg weight loss by Day 1811–13% of initial weight<10% or >14%VentilationMinimal Days 1–10; gradually increase Day 10–18Sealed past Day 12 = hypoxia risk
⚠️ Notes:

Elevated CO₂ levels up to 1.2% during the first week of single-stage incubation can improve chick quality by accelerating organ development and shortening the hatch window. Emtech-systems
After Day 10–12, embryo metabolism increases exponentially and ventilation should be gradually increased; fine-tune ventilation beyond Day 10–12 based on a fixed maximum CO₂ concentration. Pasreform
CO₂ levels above 0.5% in the setter reduce hatchability, with significant reductions at 1.0%; total embryo mortality occurs at 5.0% CO₂. Poultry Producer


🐥 HATCHER — Days 19–21 (3 Periods)
ParameterPeriod 1 (Early)Period 2 (Active Hatch)Period 3 (Post-Peak)Temperature36.9–37.2°C (98.5°F)36.9–37.2°CReduce gradually to ~36.1°C (97°F)Humidity40–55% RHRises to 65–85% RH (peak)Reduce after RH drops 3–5% from peakCO₂0.4–0.5%Up to 1.0% (10,000 ppm) toleratedFinal set point ~0.25%Ventilation/airflowModerateHigh — critical for chick coolingIncrease to remove CO₂ and moisture
⚠️ Notes:

Many chicks hatching simultaneously can push RH as high as 85%; once RH has clearly peaked and shows a 3–5% reduction, temperature should be slowly decreased by 0.2–0.3°F followed by more intensive ventilation. Pasreform
Around 10,000 ppm CO₂ and 65–75% RH during the hatching phase ensure chicks hatch simultaneously and without stress. Epluse
The CO₂ tolerance level in the hatcher is approximately 0.75%; hatching chicks produce significantly more CO₂ than embryos in eggs. Poultry Producer


🌡️ Setter & Hatcher ROOM Environment
ParameterAcceptable RangeNotesRoom temperature23.9–26.7°C (75–80°F)Deviations force machines to overcompensateRoom humidity50–60% RHToo dry/wet stresses machine humidity controlRoom air pressureSlightly positive vs. adjacent roomsPrevents cross-contamination

When setter room temperature is too cool, the incubator will use additional heat, which costs more than three times as much as heating room air with a gas furnace. The Poultry Site


🔄 Airflow & Ventilation Quality
The most important aspect of airflow in an incubator is ensuring proper mixing of temperature and humidity throughout the cabinet while bringing in fresh air for oxygen and exhausting CO₂, excess moisture, and heat. Air follows the path of least resistance — an incompletely closed baffle door, poor door seal, or misaligned fan will negatively affect airflow patterns. In a poorly maintained machine, insufficient air circulation through the egg mass results in hot and cold spots, which in turn creates slow hatches, reduced hatchability, and lower chick quality. The Poultry Site
Flag the following airflow issues:

Fan misalignment or speed anomaly
Baffle door not fully closed
Door seal failure (temperature variance across zones)
Temperature uniformity variance >0.5°C within the same machine


🔎 ANALYSIS TASKS
1. Data Cleaning

Detect missing values and flag gaps >15 minutes
Detect sensor drift or impossible readings (e.g., temp >42°C or <30°C)
Flag inconsistent timestamps or out-of-sequence logs
Identify duplicate records

2. Hatchery Performance Metrics
Calculate and report:

Hatch rate (%) = Chicks hatched ÷ Fertile eggs set × 100
Fertility rate (%)
Chick survival rate (%)
Machine uptime (%)
Average egg/machine temperature per batch
Humidity stability (standard deviation per 24h)
CO₂ stability and stage compliance
Egg weight loss % at Day 18 transfer
Hatch window duration (hours from first to last hatch)
Turning consistency score

3. Anomaly Detection
Identify and explain:

Temperature spikes or drops outside ±0.3°C
Humidity outside stage-appropriate range
CO₂ exceeding stage thresholds
O₂ dropping below 20%
Power interruptions (log duration and timing)
Ventilation failure indicators (CO₂ accumulation, humidity spikes)
Airflow anomalies (hot/cold spots, fan issues)
Sensor failure patterns
Abnormal hatch results vs. baseline

4. Predictive Risk Analysis
Based on trends, predict with confidence level (Low / Medium / High):

Possible hatch failure (e.g., sustained temp deviation)
Low chick quality risk (e.g., inadequate egg weight loss, poor CO₂ management)
Machine malfunction (e.g., fan degradation, heating element inconsistency)
Environmental stress (e.g., room temp rising, humidity instability)
Early or delayed hatch window

5. Recommendations
Provide clear, actionable steps for farm staff:

Temperature/humidity adjustments with target values
Ventilation schedule changes per incubation stage
Maintenance and calibration alerts
Egg turning schedule verification
Staff intervention triggers
Biosecurity concerns (positive pressure, contamination risk)
Machine optimization notes


📊 OUTPUT FORMAT
Always respond using this structure:
1. Summary

Overall hatchery health status (🟢 Good / 🟡 Caution / 🔴 Critical)
Key findings

2. Performance Metrics

Hatch rate, fertility rate, chick survival
Machine stability scores (temp, humidity, CO₂)
Egg weight loss compliance

3. Detected Issues
#IssueMachine/ZoneSeverityPossible Cause
4. Predictions (Next 24–72 Hours)

Risk description, confidence level, affected parameter

5. Recommendations

Numbered, clear action steps for staff

6. 🚨 ALERTS (Critical Only)

Immediate action required — include machine ID, parameter, current value, target value


🧠 ANALYSIS RULES

Apply stage-appropriate thresholds (setter Day 1–10 vs. Day 10–18 vs. hatcher periods)
Flag Warning when values are outside optimal range but within safe limits
Flag Critical when values exceed safe thresholds or when multiple anomalies coincide
Note that embryo death can occur if temperature rises above 39.4°C (103°F) even for a short period; a cooler temperature delays hatching while a hotter temperature causes early hatching with reduced success. Hatching Time
Track egg weight loss as a cumulative humidity management indicator
Evaluate CO₂ in context of incubation day and machine type (single-stage vs. multi-stage)
Note altitude if provided — CO₂ sensors calibrated for sea level require a correction factor at higher altitudes. Pasreform


📌 TONE
Be professional and concise. Focus on practical farm decisions, not just statistics. Use plain language for farm staff action steps, and technical language only in metrics and analysis sections.
SYSTEM;
    }
    // -------------------------------------------------------------------------

    public function mount(int $formTypeId): void
    {
        $formType = FormType::find($formTypeId);
        abort_if(! $formType, 404);

        $this->formTypeId   = $formTypeId;
        $this->formTypeName = $formType->form_name;

        $this->loadFromCache();
    }

    public function updatedContext(): void
    {
        $this->loadFromCache();
    }

    protected function loadFromCache(): void
    {
        $cacheKey = $this->cacheKey();

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            $this->insight      = $cached['insight'];
            $this->generatedAt  = $cached['generated_at'];
            $this->status       = 'ready';
            $this->errorMessage = '';

            $tlKey = $this->translationCacheKey();
            if (Cache::has($tlKey)) {
                $this->translatedInsight = Cache::get($tlKey);
            }
        } else {
            $this->status           = 'idle';
            $this->insight          = '';
            $this->generatedAt      = null;
            $this->errorMessage     = '';
            $this->translatedInsight = '';
            $this->showTranslation  = false;
        }
    }

    public function generate(): void
    {
        $cacheKey = $this->cacheKey();

        // Serve from cache if already present (avoid duplicate API calls)
        if (Cache::has($cacheKey)) {
            $this->loadFromCache();
            return;
        }

        try {
            $summary = $this->buildFormDataSummary();
            $client  = new OpenRouterClient();

            $response = $client->ask(
                userMessage:  $summary,
                systemPrompt: $this->getSystemPrompt(),
            );

            $generatedAt = now()->format('Y-m-d H:i:s');

            Cache::put($cacheKey, [
                'insight'      => $response,
                'generated_at' => $generatedAt,
            ], $this->cacheTtl());

            $this->insight      = $response;
            $this->generatedAt  = $generatedAt;
            $this->status       = 'ready';
            $this->errorMessage = '';
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('InsightsDetail generate() failed', [
                'formTypeId' => $this->formTypeId,
                'context'    => $this->context,
                'error'      => $e->getMessage(),
            ]);
            $this->status       = 'error';
            $this->errorMessage = 'An error occurred while generating insights. Please try again.';
        }
    }

    public function retry(): void
    {
        Cache::forget($this->cacheKey());
        $this->status       = 'idle';
        $this->insight      = '';
        $this->generatedAt  = null;
        $this->errorMessage = '';
        $this->generate();
    }

    public function clearCache(): void
    {
        Cache::forget($this->cacheKey());
        Cache::forget($this->translationCacheKey());
        $this->status           = 'idle';
        $this->insight          = '';
        $this->generatedAt      = null;
        $this->errorMessage     = '';
        $this->translatedInsight = '';
        $this->showTranslation  = false;
    }

    public function translate(): void
    {
        if ($this->insight === '') {
            return;
        }

        $translationKey = $this->translationCacheKey();

        if (Cache::has($translationKey)) {
            $this->translatedInsight = Cache::get($translationKey);
            $this->showTranslation   = true;

            return;
        }

        $this->isTranslating = true;

        try {
            $client = new OpenRouterClient();

            $translated = $client->ask(
                userMessage:  $this->insight,
                systemPrompt: 'Isinalin mo ang teksto sa Filipino (Tagalog). Gamitin ang natural na Taglish (halo ng Tagalog at Ingles) tulad ng karaniwang ginagamit sa Pilipinas. Panatilihin ang lahat ng numero, sukat, pangalan ng makina, teknikal na abbreviation, at unit ng pagsukat sa Ingles. Huwag magdagdag ng sariling komento — isinalin lamang ang ibinigay na teksto.',
            );

            Cache::put($translationKey, $translated, $this->cacheTtl());

            $this->translatedInsight = $translated;
            $this->showTranslation   = true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('InsightsDetail translate() failed', [
                'formTypeId' => $this->formTypeId,
                'error'      => $e->getMessage(),
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
        if ($this->status !== 'ready') {
            return;
        }

        $url = \Illuminate\Support\Facades\URL::signedRoute('admin.print.insights', [
            'formTypeId' => $this->formTypeId,
            'context'    => $this->context,
        ]);

        $this->dispatch('open-print-window', url: $url);
    }

    protected function cacheKey(): string
    {
        return "insights:form:{$this->formTypeId}:{$this->context}";
    }

    protected function translationCacheKey(): string
    {
        return "insights:form:{$this->formTypeId}:{$this->context}:tl";
    }

    protected function cacheTtl(): int
    {
        // 1 hour for week context, 2 hours for month context
        return $this->context === 'week' ? 3600 : 7200;
    }

    protected function buildFormDataSummary(): string
    {
        [$start, $end, $label] = $this->dateRange();

        $forms = DB::table('forms')
            ->where('form_type_id', $this->formTypeId)
            ->whereBetween('date_submitted', [$start, $end])
            ->orderBy('date_submitted', 'asc')
            ->get(['id', 'form_inputs', 'date_submitted', 'uploaded_by']);

        $total = $forms->count();

        if ($total === 0) {
            return "Form Type: {$this->formTypeName}\nContext: {$label}\nTotal Submissions: 0\nNo form submissions found for this period.";
        }

        // Build per-day submission counts
        $byDay = [];
        foreach ($forms as $form) {
            $day = Carbon::parse($form->date_submitted)->format('Y-m-d');
            $byDay[$day] = ($byDay[$day] ?? 0) + 1;
        }

        // Aggregate field values across all forms (first 50 forms to avoid token overflow)
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
                if (is_array($value) || is_null($value)) {
                    continue;
                }
                $strVal = (string) $value;
                if ($strVal === '') {
                    continue;
                }
                $fieldAggregates[$key][] = $strVal;
            }
        }

        // Summarise each field: unique values & counts
        $fieldSummaries = [];
        foreach ($fieldAggregates as $field => $values) {
            $counts = array_count_values($values);
            arsort($counts);
            $top = array_slice($counts, 0, 5, true);
            $parts = [];
            foreach ($top as $val => $cnt) {
                $parts[] = "{$val} ({$cnt}x)";
            }
            $fieldSummaries[] = "  {$field}: " . implode(', ', $parts);
        }

        $byDayText = '';
        foreach ($byDay as $day => $cnt) {
            $byDayText .= "  {$day}: {$cnt} submission(s)\n";
        }

        $fieldsText = implode("\n", $fieldSummaries);

        return <<<TEXT
Form Type: {$this->formTypeName}
Context: {$label}
Date Range: {$start->format('Y-m-d')} to {$end->format('Y-m-d')}
Total Submissions: {$total}

Submissions per Day:
{$byDayText}
Field Value Summary (top values across {$sample->count()} sampled records):
{$fieldsText}
TEXT;
    }

    protected function dateRange(): array
    {
        $now = now();

        if ($this->context === 'week') {
            $start = $now->copy()->startOfWeek(UnitValue::SUNDAY)->startOfDay();
            $end   = $now->copy()->endOfWeek(UnitValue::SATURDAY)->endOfDay();
            $label = 'Current Week (' . $start->format('M d') . ' – ' . $end->format('M d, Y') . ')';
        } else {
            $start = $now->copy()->startOfMonth()->startOfDay();
            $end   = $now->copy()->endOfMonth()->endOfDay();
            $label = 'Current Month (' . $now->format('F Y') . ')';
        }

        return [$start, $end, $label];
    }

    // -------------------------------------------------------------------------
    // Markdown → styled HTML renderer
    // -------------------------------------------------------------------------

    public function renderedInsight(): string
    {
        $source = ($this->showTranslation && $this->translatedInsight !== '')
            ? $this->translatedInsight
            : $this->insight;

        if ($source === '') {
            return '';
        }

        $lines = explode("\n", str_replace("\r\n", "\n", $source));
        $sections = [];
        $currentTitle = null;
        $currentLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Detect section headings: ## 1. Title  /  ### Title
            if (preg_match('/^#{1,3}\s*(?:\d+\.\s*)?(.+)$/', $trimmed, $m)) {
                if ($currentTitle !== null) {
                    $sections[] = ['title' => $currentTitle, 'lines' => $currentLines];
                }
                $currentTitle = trim($m[1], '*: ');
                $currentLines = [];
            } else {
                $currentLines[] = $line;
            }
        }

        if ($currentTitle !== null) {
            $sections[] = ['title' => $currentTitle, 'lines' => $currentLines];
        }

        // No headings found — render as a plain block
        if (empty($sections)) {
            return '<div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed space-y-2">'
                . $this->renderContentBlock($lines, 'gray')
                . '</div>';
        }

        return implode('', array_map(
            fn ($s) => $this->renderSection($s['title'], $s['lines']),
            $sections
        ));
    }

    private function renderSection(string $title, array $contentLines): string
    {
        $cfg     = $this->getSectionConfig($title);
        $content = $this->renderContentBlock($contentLines, $cfg['dot']);

        $border     = $cfg['border'];
        $headerBg   = $cfg['header_bg'];
        $headerText = $cfg['header_text'];
        $iconBg     = $cfg['icon_bg'];
        $icon       = $cfg['icon'];
        $safeTitle  = htmlspecialchars($title);

        $h  = "<div class=\"insight-section-card rounded-xl overflow-hidden border {$border} shadow-sm mb-4\">";
        $h .= "<div class=\"flex items-center gap-3 px-4 py-3 {$headerBg} border-b {$border}\">";
        $h .= "<div class=\"w-7 h-7 rounded-lg {$iconBg} flex items-center justify-center shrink-0\">{$icon}</div>";
        $h .= "<h3 class=\"text-sm font-semibold {$headerText}\">{$safeTitle}</h3>";
        $h .= '</div>';
        $h .= "<div class=\"px-4 py-4 space-y-2\">{$content}</div>";
        $h .= '</div>';

        return $h;
    }

    private function renderContentBlock(array $lines, string $dotColor): string
    {
        $html    = '';
        $inList  = false;
        $listTag = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Empty line — close any open list
            if ($trimmed === '') {
                if ($inList) {
                    $html .= "</{$listTag}>";
                    $inList = false;
                }
                continue;
            }

            // Bold-only line → sub-heading
            if (preg_match('/^\*\*(.+?)\*\*:?\s*$/', $trimmed, $m)) {
                if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                $sub  = htmlspecialchars($m[1]);
                $html .= "<p class=\"text-sm font-semibold text-gray-900 dark:text-white mt-3 mb-0.5\">{$sub}</p>";
                continue;
            }

            // Bullet list item
            if (preg_match('/^[-*•]\s+(.+)$/', $trimmed, $m)) {
                if (!$inList || $listTag !== 'ul') {
                    if ($inList) $html .= "</{$listTag}>";
                    $html   .= '<ul class="space-y-1.5 mt-1">';
                    $inList  = true;
                    $listTag = 'ul';
                }
                $dot     = $this->dotClass($dotColor);
                $content = $this->applyInlineFormatting($m[1]);
                $html   .= "<li class=\"flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300\">";
                $html   .= "<span class=\"w-1.5 h-1.5 rounded-full {$dot} mt-1.5 shrink-0\"></span>";
                $html   .= "<span>{$content}</span></li>";
                continue;
            }

            // Numbered list item (inside a section)
            if (preg_match('/^(\d+)\.\s+(.+)$/', $trimmed, $m)) {
                if (!$inList || $listTag !== 'ol') {
                    if ($inList) $html .= "</{$listTag}>";
                    $html   .= '<ol class="space-y-1.5 mt-1 list-none">';
                    $inList  = true;
                    $listTag = 'ol';
                }
                $num     = $m[1];
                $content = $this->applyInlineFormatting($m[2]);
                $html   .= "<li class=\"flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300\">";
                $html   .= "<span class=\"shrink-0 w-5 h-5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-semibold flex items-center justify-center mt-0.5\">{$num}</span>";
                $html   .= "<span>{$content}</span></li>";
                continue;
            }

            // Regular paragraph
            if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
            $para  = $this->applyInlineFormatting($trimmed);
            $html .= "<p class=\"text-sm text-gray-700 dark:text-gray-300 leading-relaxed\">{$para}</p>";
        }

        if ($inList) $html .= "</{$listTag}>";

        return $html;
    }

    private function applyInlineFormatting(string $text): string
    {
        $text = htmlspecialchars($text);
        // Bold
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong class="font-semibold text-gray-900 dark:text-white">$1</strong>', $text);
        // Italic
        $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/', '<em class="italic">$1</em>', $text);
        // Inline code
        $text = preg_replace('/`(.+?)`/', '<code class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-1 py-0.5 rounded font-mono">$1</code>', $text);

        return $text;
    }

    private function dotClass(string $color): string
    {
        return match ($color) {
            'blue'   => 'bg-blue-400',
            'green'  => 'bg-green-400',
            'amber'  => 'bg-amber-400',
            'purple' => 'bg-purple-400',
            'teal'   => 'bg-teal-400',
            'red'    => 'bg-red-400',
            default  => 'bg-gray-400',
        };
    }

    private function getSectionConfig(string $title): array
    {
        $lower = strtolower($title);

        $icons = [
            'info'    => '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'chart'   => '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
            'warning' => '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            'eye'     => '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
            'bulb'    => '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>',
            'bell'    => '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
        ];

        if (str_contains($lower, 'summary')) {
            return ['border' => 'border-blue-200 dark:border-blue-800', 'header_bg' => 'bg-blue-50 dark:bg-blue-900/30', 'header_text' => 'text-blue-900 dark:text-blue-100', 'icon_bg' => 'bg-blue-500', 'dot' => 'blue', 'icon' => $icons['info']];
        }
        if (str_contains($lower, 'performance') || str_contains($lower, 'metric')) {
            return ['border' => 'border-green-200 dark:border-green-800', 'header_bg' => 'bg-green-50 dark:bg-green-900/30', 'header_text' => 'text-green-900 dark:text-green-100', 'icon_bg' => 'bg-green-500', 'dot' => 'green', 'icon' => $icons['chart']];
        }
        if (str_contains($lower, 'issue') || str_contains($lower, 'detect') || str_contains($lower, 'problem')) {
            return ['border' => 'border-amber-200 dark:border-amber-800', 'header_bg' => 'bg-amber-50 dark:bg-amber-900/30', 'header_text' => 'text-amber-900 dark:text-amber-100', 'icon_bg' => 'bg-amber-500', 'dot' => 'amber', 'icon' => $icons['warning']];
        }
        if (str_contains($lower, 'predict') || str_contains($lower, 'risk') || str_contains($lower, 'forecast')) {
            return ['border' => 'border-purple-200 dark:border-purple-800', 'header_bg' => 'bg-purple-50 dark:bg-purple-900/30', 'header_text' => 'text-purple-900 dark:text-purple-100', 'icon_bg' => 'bg-purple-500', 'dot' => 'purple', 'icon' => $icons['eye']];
        }
        if (str_contains($lower, 'recommend') || str_contains($lower, 'action') || str_contains($lower, 'suggest')) {
            return ['border' => 'border-teal-200 dark:border-teal-800', 'header_bg' => 'bg-teal-50 dark:bg-teal-900/30', 'header_text' => 'text-teal-900 dark:text-teal-100', 'icon_bg' => 'bg-teal-500', 'dot' => 'teal', 'icon' => $icons['bulb']];
        }
        if (str_contains($lower, 'alert') || str_contains($lower, 'critical') || str_contains($lower, 'urgent')) {
            return ['border' => 'border-red-200 dark:border-red-800', 'header_bg' => 'bg-red-50 dark:bg-red-900/30', 'header_text' => 'text-red-900 dark:text-red-100', 'icon_bg' => 'bg-red-500', 'dot' => 'red', 'icon' => $icons['bell']];
        }

        // Default
        return ['border' => 'border-gray-200 dark:border-gray-700', 'header_bg' => 'bg-gray-50 dark:bg-gray-800', 'header_text' => 'text-gray-900 dark:text-gray-100', 'icon_bg' => 'bg-gray-500', 'dot' => 'gray', 'icon' => $icons['info']];
    }

    // -------------------------------------------------------------------------

    public function render()
    {
        return view('livewire.admin.insights-detail');
    }
}

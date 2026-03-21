<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Insights — {{ $formTypeName }} — IntelliHatchSystem</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111;
            background: #fff;
            padding: 24px;
            max-width: 960px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 3px solid #e5730a;
        }

        .header-left h1 {
            font-size: 20px;
            font-weight: 700;
            color: #e5730a;
            letter-spacing: -0.3px;
        }

        .header-left .subtitle {
            font-size: 13px;
            font-weight: 600;
            color: #222;
            margin-top: 3px;
        }

        .header-left .tagline {
            font-size: 10px;
            color: #888;
            margin-top: 2px;
        }

        .header-right {
            text-align: right;
            font-size: 10px;
            color: #555;
            line-height: 1.6;
        }

        /* ── Meta bar ── */
        .meta-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 10px;
            color: #555;
        }

        .meta-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 99px;
            font-weight: 600;
            background: #fff3e0;
            color: #b45309;
            border: 1px solid #fcd34d;
        }

        /* ── Section cards ── */
        .section-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #f5f5f5;
            border-bottom: 1px solid #e0e0e0;
        }

        .section-header h3 {
            font-size: 12px;
            font-weight: 700;
            color: #111;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .section-body {
            padding: 10px 14px;
        }

        /* Section colour accents */
        .section-summary   .section-header { background: #eff6ff; border-bottom-color: #bfdbfe; }
        .section-summary   .section-header h3 { color: #1e40af; }
        .section-summary   { border-color: #bfdbfe; }

        .section-metrics   .section-header { background: #f0fdf4; border-bottom-color: #bbf7d0; }
        .section-metrics   .section-header h3 { color: #166534; }
        .section-metrics   { border-color: #bbf7d0; }

        .section-issues    .section-header { background: #fffbeb; border-bottom-color: #fde68a; }
        .section-issues    .section-header h3 { color: #92400e; }
        .section-issues    { border-color: #fde68a; }

        .section-predict   .section-header { background: #faf5ff; border-bottom-color: #e9d5ff; }
        .section-predict   .section-header h3 { color: #6b21a8; }
        .section-predict   { border-color: #e9d5ff; }

        .section-recommend .section-header { background: #f0fdfa; border-bottom-color: #99f6e4; }
        .section-recommend .section-header h3 { color: #115e59; }
        .section-recommend { border-color: #99f6e4; }

        .section-alert     .section-header { background: #fff1f2; border-bottom-color: #fecdd3; }
        .section-alert     .section-header h3 { color: #9f1239; }
        .section-alert     { border-color: #fecdd3; }

        /* ── Content elements ── */
        .section-body p {
            font-size: 11px;
            color: #222;
            line-height: 1.6;
            margin-bottom: 4px;
        }

        .section-body ul {
            padding-left: 14px;
            margin: 4px 0;
        }

        .section-body li {
            font-size: 11px;
            color: #222;
            line-height: 1.6;
            margin-bottom: 2px;
        }

        .section-body .subh {
            font-size: 11px;
            font-weight: 700;
            color: #111;
            margin: 6px 0 2px;
        }

        .section-body .numbered {
            display: flex;
            gap: 6px;
            font-size: 11px;
            color: #222;
            margin-bottom: 3px;
        }

        .section-body .numbered .num {
            font-weight: 700;
            min-width: 18px;
        }

        /* Fallback plain content block */
        .plain-content p {
            font-size: 11px;
            color: #222;
            line-height: 1.6;
            margin-bottom: 4px;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 10px;
            color: #888;
        }

        .footer .ai-notice {
            font-style: italic;
            font-weight: 600;
            color: #e5730a;
            font-size: 10px;
        }

        /* ── Print button (hidden when printing) ── */
        .print-btn {
            position: fixed;
            top: 16px;
            right: 16px;
            background: #e5730a;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .print-btn:hover { background: #c86009; }

        @media print {
            .print-btn { display: none; }
            body { padding: 8px; max-width: none; }
            .section-card { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <h1>IntelliHatchSystem</h1>
            <div class="subtitle">AI-Generated Insights — {{ $formTypeName }}</div>
            <div class="tagline">Intelligence and insights from hatchery inputs with AI assistance</div>
        </div>
        <div class="header-right">
            <div><strong>Context:</strong> {{ $context === 'week' ? 'Current Week' : 'Current Month' }}</div>
            <div><strong>Generated:</strong> {{ $generatedAt }}</div>
            <div><strong>Printed:</strong> {{ now()->format('d M Y, H:i') }}</div>
            <div><strong>By:</strong> {{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}</div>
        </div>
    </div>

    {{-- Meta --}}
    <div class="meta-bar">
        <span class="meta-badge">{{ $context === 'week' ? 'Weekly Insights' : 'Monthly Insights' }}</span>
        <span>Form Type: <strong>{{ $formTypeName }}</strong></span>
    </div>

    {{-- Content --}}
    @php
        function ihs_section_class(string $title): string {
            $lower = strtolower($title);
            if (str_contains($lower, 'summary')) return 'section-summary';
            if (str_contains($lower, 'performance') || str_contains($lower, 'metric')) return 'section-metrics';
            if (str_contains($lower, 'issue') || str_contains($lower, 'detect') || str_contains($lower, 'problem')) return 'section-issues';
            if (str_contains($lower, 'predict') || str_contains($lower, 'risk') || str_contains($lower, 'forecast')) return 'section-predict';
            if (str_contains($lower, 'recommend') || str_contains($lower, 'action') || str_contains($lower, 'suggest')) return 'section-recommend';
            if (str_contains($lower, 'alert') || str_contains($lower, 'critical') || str_contains($lower, 'urgent')) return 'section-alert';
            return '';
        }

        function ihs_inline_md(string $text): string {
            $text = htmlspecialchars($text);
            $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
            $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/', '<em>$1</em>', $text);
            return $text;
        }

        function ihs_render_section_body(array $lines): string {
            $html    = '';
            $inList  = false;
            $listTag = '';

            foreach ($lines as $line) {
                $t = trim($line);

                if ($t === '') {
                    if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                    continue;
                }

                // Bold-only line → sub-heading
                if (preg_match('/^\*\*(.+?)\*\*:?\s*$/', $t, $m)) {
                    if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                    $html .= '<p class="subh">' . htmlspecialchars($m[1]) . '</p>';
                    continue;
                }

                // Bullet
                if (preg_match('/^[-*•]\s+(.+)$/', $t, $m)) {
                    if (!$inList || $listTag !== 'ul') {
                        if ($inList) $html .= "</{$listTag}>";
                        $html .= '<ul>'; $inList = true; $listTag = 'ul';
                    }
                    $html .= '<li>' . ihs_inline_md($m[1]) . '</li>';
                    continue;
                }

                // Numbered
                if (preg_match('/^(\d+)\.\s+(.+)$/', $t, $m)) {
                    if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                    $html .= '<div class="numbered"><span class="num">' . $m[1] . '.</span><span>' . ihs_inline_md($m[2]) . '</span></div>';
                    continue;
                }

                if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                $html .= '<p>' . ihs_inline_md($t) . '</p>';
            }

            if ($inList) $html .= "</{$listTag}>";
            return $html;
        }

        // Parse into sections
        $lines    = explode("\n", str_replace("\r\n", "\n", $insight));
        $sections = [];
        $currentTitle = null;
        $currentLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
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
    @endphp

    @if(empty($sections))
        <div class="plain-content">
            @foreach($lines as $line)
                @if(trim($line) !== '')
                    <p>{!! ihs_inline_md(trim($line)) !!}</p>
                @endif
            @endforeach
        </div>
    @else
        @foreach($sections as $section)
            @php $cls = ihs_section_class($section['title']); @endphp
            <div class="section-card {{ $cls }}">
                <div class="section-header">
                    <h3>{{ $section['title'] }}</h3>
                </div>
                <div class="section-body">
                    {!! ihs_render_section_body($section['lines']) !!}
                </div>
            </div>
        @endforeach
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div>
            <div class="ai-notice">⚠ This document is AI/System Generated</div>
            <div style="margin-top:2px">Content is produced by an AI model and should be reviewed by qualified hatchery personnel before acting on recommendations.</div>
        </div>
        <div style="text-align:right">
            <div>IntelliHatchSystem — Confidential</div>
            <div>{{ now()->format('d M Y') }}</div>
        </div>
    </div>

</body>
</html>

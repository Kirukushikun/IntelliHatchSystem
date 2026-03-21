<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Response — IntelliHatchSystem</title>
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

        /* ── Question block ── */
        .question-block {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .block-header {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: #f5f5f5;
            border-bottom: 1px solid #e0e0e0;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #555;
        }

        .block-body {
            padding: 10px 14px;
        }

        .block-body p {
            font-size: 12px;
            color: #111;
            line-height: 1.6;
        }

        .meta-row {
            padding: 6px 12px;
            background: #fafafa;
            border-top: 1px solid #eee;
            font-size: 10px;
            color: #666;
            display: flex;
            flex-wrap: wrap;
            gap: 0 16px;
        }

        /* ── Response block ── */
        .response-block {
            border: 1px solid #d1fae5;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 14px;
        }

        .response-block .block-header {
            background: #f0fdf4;
            border-bottom-color: #bbf7d0;
            color: #166534;
        }

        /* ── Markdown content ── */
        .md-content h2 {
            font-size: 14px;
            font-weight: 700;
            color: #111;
            margin: 12px 0 4px;
        }

        .md-content h3 {
            font-size: 12px;
            font-weight: 700;
            color: #222;
            margin: 10px 0 3px;
        }

        .md-content h4 {
            font-size: 11px;
            font-weight: 700;
            color: #333;
            margin: 8px 0 2px;
        }

        .md-content p {
            font-size: 11px;
            color: #222;
            line-height: 1.6;
            margin-bottom: 4px;
        }

        .md-content ul {
            padding-left: 16px;
            margin: 4px 0;
        }

        .md-content li {
            font-size: 11px;
            color: #222;
            line-height: 1.6;
            margin-bottom: 2px;
        }

        .md-content .subh {
            font-size: 11px;
            font-weight: 700;
            color: #111;
            margin: 6px 0 2px;
        }

        .md-content .numbered {
            display: flex;
            gap: 6px;
            font-size: 11px;
            color: #222;
            margin-bottom: 3px;
        }

        .md-content .numbered .num {
            font-weight: 700;
            min-width: 18px;
        }

        hr.md-hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 8px 0;
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

        /* ── Print button ── */
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
            .question-block, .response-block { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <h1>IntelliHatchSystem</h1>
            <div class="subtitle">AI Chat Response</div>
            <div class="tagline">Intelligence and insights from hatchery inputs with AI assistance</div>
        </div>
        <div class="header-right">
            <div><strong>Submitted:</strong> {{ $chat->created_at->format('d M Y, g:i A') }}</div>
            <div><strong>Printed:</strong> {{ now()->format('d M Y, H:i') }}</div>
            <div><strong>By:</strong> {{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}</div>
        </div>
    </div>

    {{-- Question --}}
    <div class="question-block">
        <div class="block-header">Your Question</div>
        <div class="block-body">
            <p>{{ $chat->prompt }}</p>
        </div>
        <div class="meta-row">
            <span><strong>Scope:</strong> {{ $chat->formType ? $chat->formType->form_name : 'All Form Types' }}</span>
            <span><strong>Period:</strong> {{ $chat->contextPeriodLabel() }}</span>
            <span><strong>Status:</strong> Done</span>
        </div>
    </div>

    {{-- Response --}}
    <div class="response-block">
        <div class="block-header">AI Response</div>
        <div class="block-body">
            @php
                function aichat_inline(string $text): string {
                    $text = htmlspecialchars($text);
                    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
                    $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/', '<em>$1</em>', $text);
                    return $text;
                }

                function aichat_render(string $markdown): string {
                    $lines   = explode("\n", str_replace("\r\n", "\n", $markdown));
                    $html    = '';
                    $inList  = false;
                    $listTag = '';

                    foreach ($lines as $line) {
                        $t = trim($line);

                        if ($t === '') {
                            if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                            continue;
                        }

                        if (preg_match('/^#(?!#)\s+(.+)$/', $t, $m)) {
                            if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                            $html .= '<h2>' . aichat_inline($m[1]) . '</h2>';
                            continue;
                        }
                        if (preg_match('/^##(?!#)\s+(.+)$/', $t, $m)) {
                            if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                            $html .= '<h3>' . aichat_inline($m[1]) . '</h3>';
                            continue;
                        }
                        if (preg_match('/^###\s+(.+)$/', $t, $m)) {
                            if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                            $html .= '<h4>' . aichat_inline($m[1]) . '</h4>';
                            continue;
                        }

                        if (preg_match('/^\*\*(.+?)\*\*:?\s*$/', $t, $m)) {
                            if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                            $html .= '<p class="subh">' . htmlspecialchars($m[1]) . '</p>';
                            continue;
                        }

                        if (preg_match('/^[-*•]\s+(.+)$/', $t, $m)) {
                            if (!$inList || $listTag !== 'ul') {
                                if ($inList) $html .= "</{$listTag}>";
                                $html .= '<ul>'; $inList = true; $listTag = 'ul';
                            }
                            $html .= '<li>' . aichat_inline($m[1]) . '</li>';
                            continue;
                        }

                        if (preg_match('/^(\d+)\.\s+(.+)$/', $t, $m)) {
                            if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                            $html .= '<div class="numbered"><span class="num">' . $m[1] . '.</span><span>' . aichat_inline($m[2]) . '</span></div>';
                            continue;
                        }

                        if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $t)) {
                            if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                            $html .= '<hr class="md-hr">';
                            continue;
                        }

                        if ($inList) { $html .= "</{$listTag}>"; $inList = false; }
                        $html .= '<p>' . aichat_inline($t) . '</p>';
                    }

                    if ($inList) $html .= "</{$listTag}>";
                    return $html;
                }
            @endphp

            <div class="md-content">
                {!! aichat_render($chat->response) !!}
            </div>
        </div>
    </div>

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

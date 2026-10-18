@php
    use App\Services\SystemHealth;

    // Status is never colour alone: every state carries a glyph and a word.
    $badge = fn (?string $status) => match ($status) {
        SystemHealth::OK   => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300', '✓', 'OK'],
        SystemHealth::WARN => ['bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300', '▲', 'Warning'],
        SystemHealth::FAIL => ['bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300', '✕', 'Failed'],
        SystemHealth::NA   => ['bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', '–', 'N/A'],
        default            => [null, null, null],
    };
    [$overallClass, $overallGlyph, $overallLabel] = $badge($report['status']);
@endphp

<x-layout>
    <x-navbar title="System Health" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4 space-y-6">

            {{-- Overall verdict --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-base font-semibold {{ $overallClass }}">
                        <span aria-hidden="true">{{ $overallGlyph }}</span> {{ $overallLabel }}
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Overall: the worst section decides</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Generated {{ \Illuminate\Support\Carbon::parse($report['generated_at'])->toDateTimeString() }}</p>
                    </div>
                </div>
                <div class="flex gap-2 text-sm">
                    <a href="{{ route('admin.system-health') }}" class="px-3 py-2 rounded-lg bg-orange-500 text-white hover:bg-orange-600">Refresh</a>
                    <a href="{{ route('admin.system-health.json') }}" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">JSON</a>
                </div>
            </div>

            {{-- Sections --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                @foreach($report['sections'] as $section)
                    @php [$cls, $glyph, $label] = $badge($section['status']); @endphp
                    <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden" id="section-{{ $section['key'] }}">
                        <div class="px-5 py-3.5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $section['title'] }}</h3>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $cls }}">
                                <span aria-hidden="true">{{ $glyph }}</span> {{ $label }}
                            </span>
                        </div>

                        @if($section['error'])
                            <div class="px-5 py-3 bg-red-50 dark:bg-red-950/30 text-sm text-red-700 dark:text-red-300 break-words">
                                ✕ Section failed: {{ $section['error'] }}
                            </div>
                        @endif

                        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($section['rows'] as $row)
                                @php [$rcls, $rglyph, $rlabel] = $badge($row['status']); @endphp
                                <li class="px-5 py-2.5 text-sm">
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-1 sm:gap-4">
                                        <span class="text-gray-500 dark:text-gray-400 shrink-0">{{ $row['label'] }}</span>
                                        <span class="flex items-start gap-2 sm:justify-end text-gray-900 dark:text-gray-100 sm:text-right break-words min-w-0">
                                            <span class="break-words min-w-0">{{ $row['value'] }}</span>
                                            @if($rlabel)
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium shrink-0 {{ $rcls }}">
                                                    <span aria-hidden="true">{{ $rglyph }}</span> {{ $rlabel }}
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                    @if($row['detail'])
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 break-words">{{ $row['detail'] }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        @if(!empty($section['notes']))
                            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 space-y-1">
                                @foreach($section['notes'] as $note)
                                    <p class="text-xs text-gray-600 dark:text-gray-400">ⓘ {{ $note }}</p>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>

            {{-- Thresholds: the same constant the code bands on --}}
            <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Thresholds</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Read from <code>SystemHealth::THRESHOLDS</code> — the same values the checks above are judged on.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-5 py-2 font-medium">Check</th>
                                <th class="px-3 py-2 font-medium whitespace-nowrap">▲ Warning at</th>
                                <th class="px-3 py-2 font-medium whitespace-nowrap">✕ Failed at</th>
                                <th class="px-5 py-2 font-medium">Why</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-800 dark:text-gray-200">
                            @foreach($report['thresholds'] as $t)
                                <tr>
                                    <td class="px-5 py-2">{{ $t['label'] }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">≥ {{ $t['warn'] }}{{ $t['unit'] }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $t['fail'] === null ? 'never' : '≥ '.$t['fail'].$t['unit'] }}</td>
                                    <td class="px-5 py-2 text-xs text-gray-500 dark:text-gray-400">{{ $t['why'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </x-navbar>
</x-layout>

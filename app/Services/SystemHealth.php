<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FileAttributes;

/**
 * System Health — the single source behind both /admin/system-health (page)
 * and /admin/system-health.json (monitor). Every check degrades to a status
 * plus an error string; nothing in here is allowed to throw out of report().
 */
class SystemHealth
{
    public const OK = 'ok';

    public const WARN = 'warn';

    public const FAIL = 'fail';

    /** Not available on this platform / not applicable to this configuration. Never affects the verdict. */
    public const NA = 'na';

    public const SECTIONS = [
        'server' => 'Server',
        'cpu' => 'CPU',
        'memory' => 'Memory',
        'disks' => 'Local disks',
        'dependencies' => 'Dependencies',
        'queues' => 'Queues',
        'scheduler' => 'Scheduler',
        'backups' => 'Backups',
        'uploads' => 'Uploads storage',
        'php' => 'PHP runtime',
    ];

    /**
     * Every band the code judges on. The page renders this same array, so a
     * warning can be argued with rather than merely believed.
     */
    public const THRESHOLDS = [
        'disk_used_percent' => ['label' => 'Disk used', 'warn' => 80, 'fail' => 92, 'unit' => '%',
            'why' => 'Of the space the web user can write to. 80% is a dashboard line; 92% leaves a few days of logs and uploads.'],
        'memory_used_percent' => ['label' => 'Memory used (total − available)', 'warn' => 85, 'fail' => 95, 'unit' => '%',
            'why' => 'Page cache is not counted as used — Linux hands it back on demand.'],
        'swap_used_percent' => ['label' => 'Swap used', 'warn' => 50, 'fail' => 90, 'unit' => '%',
            'why' => 'Sustained swapping means RAM is genuinely short.'],
        'load_per_core' => ['label' => '5-minute load average per core', 'warn' => 1.0, 'fail' => 2.0, 'unit' => '',
            'why' => 'Load average, not a percentage. 1.00 per core = every core busy; 2.00 = as much work again waiting.'],
        'dependency_latency_ms' => ['label' => 'Dependency round trip (database, cache, Redis)', 'warn' => 200, 'fail' => 1000, 'unit' => 'ms',
            'why' => 'Applied only to the quick round trips. A database taking a second to answer "select 1" is broken.'],
        'queue_wait_minutes' => ['label' => 'Oldest job waiting', 'warn' => 5, 'fail' => 30, 'unit' => 'min',
            'why' => 'Jobs here (AI chat, purges) normally start within seconds. A wait that only grows means no worker.'],
        'failed_jobs_recent' => ['label' => 'Failed jobs in the last '.self::FAILED_JOBS_WINDOW_HOURS.'h', 'warn' => 1, 'fail' => null, 'unit' => '',
            'why' => 'Warning only, never fatal. Older rows are history, reported beside it.'],
        'scheduler_heartbeat_minutes' => ['label' => 'Scheduler heartbeat age', 'warn' => 5, 'fail' => 30, 'unit' => 'min',
            'why' => 'The scheduler touches a file every minute; cron runs schedule:run every minute.'],
        'backup_age_hours' => ['label' => 'Newest backup age', 'warn' => 26, 'fail' => 72, 'unit' => 'h',
            'why' => 'Backups are scheduled daily at 02:00. 26h allows a slow run; 72h is three missed runs.'],
    ];

    /** One window for "has the queue been failing". Rows older than this are history. */
    public const FAILED_JOBS_WINDOW_HOURS = 24;

    /** Cap on the uploads walk, so an unexpectedly large tree degrades to "truncated" instead of a timeout. */
    public const UPLOAD_SCAN_MAX_FILES = 20000;

    public const UPLOAD_SCAN_CACHE_SECONDS = 900;

    public const BACKUP_LISTING_CACHE_SECONDS = 600;

    /** Disk + directory the photo uploads live in (TempPhotoManager writes to public:forms/). */
    public const UPLOADS_DISK = 'public';

    public const UPLOADS_DIRECTORY = 'forms';

    public const CACHE_PREFIX = 'system-health:';

    /**
     * The narrowest existing gate: superadmin (user_type 0). Roles here are
     * additive — AdminMiddleware admits superadmins too — so "admin" would let
     * every ordinary administrator in. Used by the controller and the sidebar.
     */
    public static function canView(?User $user): bool
    {
        return $user !== null && (int) $user->user_type === 0 && ! $user->is_disabled;
    }

    public static function heartbeatPath(): string
    {
        return storage_path('framework/schedule-heartbeat');
    }

    /** Called by the scheduler every minute (routes/console.php). */
    public static function recordSchedulerHeartbeat(): void
    {
        @touch(static::heartbeatPath());
    }

    // ─── Report ──────────────────────────────────────────────────────────────

    public function report(): array
    {
        $sections = [];
        foreach (self::SECTIONS as $key => $title) {
            $sections[$key] = $this->section($key, $title);
        }

        $report = [
            'status' => self::worst(array_column($sections, 'status')),
            'generated_at' => now()->toIso8601String(),
            'app' => config('app.name'),
            'sections' => $sections,
            'thresholds' => self::THRESHOLDS,
        ];

        return $this->redact($report);
    }

    /** Build one section; a section that throws becomes a failed section, never a 500. */
    protected function section(string $key, string $title): array
    {
        try {
            $section = $this->{$key}();
        } catch (\Throwable $e) {
            $section = ['rows' => [], 'error' => $this->describe($e)];
        }

        $section += ['rows' => [], 'notes' => [], 'error' => null];
        $statuses = array_column($section['rows'], 'status');
        if ($section['error'] !== null) {
            $statuses[] = self::FAIL;
        }

        $real = array_filter($statuses, fn ($s) => in_array($s, [self::OK, self::WARN, self::FAIL], true));

        return [
            'key' => $key,
            'title' => $title,
            'status' => $real ? self::worst($real) : self::NA,
        ] + $section;
    }

    /** The verdict is the worst section, never an average. NA never counts. */
    public static function worst(array $statuses): string
    {
        if (in_array(self::FAIL, $statuses, true)) {
            return self::FAIL;
        }
        if (in_array(self::WARN, $statuses, true)) {
            return self::WARN;
        }

        return self::OK;
    }

    public static function band(?float $value, string $threshold): string
    {
        if ($value === null) {
            return self::NA;
        }
        $t = self::THRESHOLDS[$threshold];
        if ($t['fail'] !== null && $value >= $t['fail']) {
            return self::FAIL;
        }
        if ($t['warn'] !== null && $value >= $t['warn']) {
            return self::WARN;
        }

        return self::OK;
    }

    /**
     * Run a probe and time it. Catches Throwable, not Exception: a driver
     * handed the wrong type throws a TypeError, which is exactly the shape
     * worth showing. Success is OK — latency is judged by the caller, and only
     * for the quick dependency round trips.
     *
     * @return array{ok: bool, value: mixed, error: ?string, ms: float}
     */
    public function attempt(callable $probe): array
    {
        $start = hrtime(true);
        try {
            $value = $probe();
            $error = null;
        } catch (\Throwable $e) {
            $value = null;
            $error = $this->describe($e);
        }

        return ['ok' => $error === null, 'value' => $value, 'error' => $error, 'ms' => round((hrtime(true) - $start) / 1e6, 1)];
    }

    protected function row(string $label, ?string $value, ?string $status = null, ?string $detail = null): array
    {
        return compact('label', 'value', 'status', 'detail');
    }

    protected function describe(\Throwable $e): string
    {
        return class_basename($e).': '.Str::limit(trim(preg_replace('/\s+/', ' ', $e->getMessage())), 300);
    }

    /**
     * Cache data, never a verdict. The collector returns bytes and counts;
     * every judgement is recomputed on read. A collector that throws caches
     * nothing, so a recovery shows up on the very next load.
     */
    protected function cachedData(string $key, int $ttlSeconds, callable $collect): array
    {
        $cacheKey = self::CACHE_PREFIX.$key;

        try {
            $hit = Cache::get($cacheKey);
        } catch (\Throwable) {
            $hit = null; // the cache may be the thing that's broken
        }
        if (is_array($hit) && isset($hit['collected_at'])) {
            return $hit + ['from_cache' => true];
        }

        $data = $collect();
        $data['collected_at'] = now()->getTimestamp();

        try {
            Cache::put($cacheKey, $data, $ttlSeconds);
        } catch (\Throwable) {
            //
        }

        return $data + ['from_cache' => false];
    }

    // ─── 1. Server ───────────────────────────────────────────────────────────

    protected function server(): array
    {
        $uptime = $this->readProc('/proc/uptime');
        $uptimeSeconds = $uptime !== null ? (float) strtok($uptime, ' ') : null;

        $rows = [
            $this->row('Hostname', gethostname() ?: null),
            $this->row('Operating system', php_uname('s').' '.php_uname('r')),
            $this->row('Architecture', php_uname('m')),
            $this->row('Uptime', $uptimeSeconds !== null ? self::duration($uptimeSeconds) : 'not available', $uptimeSeconds === null ? self::NA : null),
            $this->row('PHP', PHP_VERSION.' ('.PHP_SAPI.')'),
            $this->row('Laravel', app()->version()),
            $this->row('Environment', app()->environment()),
            $this->row('Timezone', config('app.timezone')),
            $this->row('Server time', now()->toDateTimeString()),
            $this->row('Deployed revision', $this->gitRevision() ?? 'not available'),
        ];

        $debug = (bool) config('app.debug');
        if ($debug && app()->environment('production')) {
            $rows[] = $this->row('Debug mode (APP_DEBUG)', 'ON in production', self::FAIL,
                'Every unhandled exception becomes a stack trace — bound query values included — for whoever triggered it. Set APP_DEBUG=false in .env and run php artisan config:cache.');
        } else {
            $rows[] = $this->row('Debug mode (APP_DEBUG)', $debug ? 'on (not production)' : 'off', self::OK);
        }

        return ['rows' => $rows];
    }

    protected function gitRevision(): ?string
    {
        $git = base_path('.git');
        $head = @is_readable($git.'/HEAD') ? trim((string) @file_get_contents($git.'/HEAD')) : '';
        if ($head === '') {
            return null;
        }
        if (! str_starts_with($head, 'ref: ')) {
            return substr($head, 0, 7).' (detached)';
        }

        $ref = substr($head, 5);
        $branch = Str::after($ref, 'refs/heads/');
        $sha = @is_readable($git.'/'.$ref) ? trim((string) @file_get_contents($git.'/'.$ref)) : null;

        if (! $sha && @is_readable($git.'/packed-refs')) {
            foreach (file($git.'/packed-refs', FILE_IGNORE_NEW_LINES) ?: [] as $line) {
                if (str_ends_with($line, ' '.$ref)) {
                    $sha = strtok($line, ' ');
                    break;
                }
            }
        }

        return $branch.($sha ? ' @ '.substr($sha, 0, 7) : '');
    }

    // ─── 2. CPU ──────────────────────────────────────────────────────────────

    protected function cpu(): array
    {
        $cpuinfo = $this->readProc('/proc/cpuinfo');
        $cores = $cpuinfo !== null ? preg_match_all('/^processor\s*:/m', $cpuinfo) : 0;

        $load = function_exists('sys_getloadavg') ? @sys_getloadavg() : false;
        if (! is_array($load) && ($raw = $this->readProc('/proc/loadavg')) !== null) {
            $load = array_map('floatval', array_slice(explode(' ', $raw), 0, 3));
        }

        $notes = ['These are load averages (tasks running or waiting, averaged over 1/5/15 minutes), not a CPU percentage. Per core, 1.00 means every core is busy.'];

        if (! is_array($load)) {
            return ['rows' => [$this->row('Load average', 'not available on this platform', self::NA)], 'notes' => $notes];
        }

        $perCore = $cores > 0 ? round($load[1] / $cores, 2) : null;

        return ['rows' => [
            $this->row('Cores', $cores > 0 ? (string) $cores : 'not available', $cores > 0 ? null : self::NA),
            $this->row('Load average (1 / 5 / 15 min)', sprintf('%.2f / %.2f / %.2f', ...$load)),
            $this->row('5-min load per core', $perCore !== null ? number_format($perCore, 2) : 'not available', self::band($perCore, 'load_per_core')),
        ], 'notes' => $notes, 'data' => ['cores' => $cores, 'load' => $load, 'load_per_core' => $perCore]];
    }

    // ─── 3. Memory ───────────────────────────────────────────────────────────

    /** Parse /proc/meminfo into bytes. "Used" is total − available, not total − free. */
    public static function memoryFromMeminfo(string $meminfo): ?array
    {
        preg_match_all('/^(\w+(?:\(\w+\))?):\s+(\d+)\s*kB/m', $meminfo, $m);
        $kb = array_combine($m[1], array_map('intval', $m[2]));

        if (empty($kb['MemTotal'])) {
            return null;
        }

        $total = $kb['MemTotal'] * 1024;
        $available = ($kb['MemAvailable'] ?? ($kb['MemFree'] + ($kb['Cached'] ?? 0))) * 1024;
        $cache = (($kb['Buffers'] ?? 0) + ($kb['Cached'] ?? 0) + ($kb['SReclaimable'] ?? 0)) * 1024;
        $swapTotal = ($kb['SwapTotal'] ?? 0) * 1024;
        $swapFree = ($kb['SwapFree'] ?? 0) * 1024;

        return [
            'total' => $total,
            'available' => $available,
            'used' => $total - $available,
            'free' => ($kb['MemFree'] ?? 0) * 1024,
            'cache' => $cache,
            'used_percent' => round(($total - $available) / $total * 100, 1),
            'swap_total' => $swapTotal,
            'swap_used' => $swapTotal - $swapFree,
            'swap_free' => $swapFree,
            'swap_used_percent' => $swapTotal > 0 ? round(($swapTotal - $swapFree) / $swapTotal * 100, 1) : null,
        ];
    }

    protected function memory(): array
    {
        $raw = $this->readProc('/proc/meminfo');
        $mem = $raw !== null ? self::memoryFromMeminfo($raw) : null;

        if ($mem === null) {
            return ['rows' => [$this->row('Memory', 'not available on this platform (/proc/meminfo unreadable)', self::NA)]];
        }

        $rows = [
            $this->row('RAM used', self::bytes($mem['used']).' of '.self::bytes($mem['total']).' ('.$mem['used_percent'].'%)', self::band($mem['used_percent'], 'memory_used_percent'),
                'Total − available. Buffers/cache are shown separately, not counted as used.'),
            $this->row('RAM available', self::bytes($mem['available'])),
            $this->row('Buffers / cache', self::bytes($mem['cache']), null, 'Reclaimable on demand.'),
        ];

        if ($mem['swap_total'] === 0) {
            $rows[] = $this->row('Swap', 'not configured', null,
                'No swap at all: under memory pressure the kernel\'s only option is to kill a process (OOM killer).');
        } else {
            $rows[] = $this->row('Swap used', self::bytes($mem['swap_used']).' of '.self::bytes($mem['swap_total']).' ('.$mem['swap_used_percent'].'%) — free '.self::bytes($mem['swap_free']),
                self::band($mem['swap_used_percent'], 'swap_used_percent'));
        }

        return ['rows' => $rows, 'data' => $mem];
    }

    // ─── 4. Local disks ──────────────────────────────────────────────────────

    protected function diskPaths(): array
    {
        return array_filter([
            'App checkout' => base_path(),
            'Storage' => storage_path(),
            'Logs' => storage_path('logs'),
            'Uploads (public disk)' => config('filesystems.disks.public.root'),
            'Backups (local disk)' => config('filesystems.disks.local.root'),
            'Temp' => sys_get_temp_dir(),
        ]);
    }

    protected function disks(): array
    {
        $filesystems = [];
        $rows = [];

        foreach ($this->diskPaths() as $label => $path) {
            $stat = @stat($path);
            if ($stat === false) {
                $rows[] = $this->row($label, 'not available ('.$path.' is missing or unreadable)', self::NA);

                continue;
            }

            // One filesystem, one row: dedupe on the device id, not on the numbers matching.
            $dev = (string) $stat['dev'];
            if (isset($filesystems[$dev])) {
                $filesystems[$dev]['labels'][] = $label;

                continue;
            }

            $total = @disk_total_space($path);
            $free = @disk_free_space($path); // what an unprivileged user can use (excludes root's reserve)
            $filesystems[$dev] = ['labels' => [$label], 'path' => $path, 'total' => $total ?: null, 'free' => $free === false ? null : $free];
        }

        foreach ($filesystems as $dev => $fs) {
            $name = implode(', ', $fs['labels']);
            if (! $fs['total'] || $fs['free'] === null) {
                $rows[] = $this->row($name, 'not available', self::NA);

                continue;
            }
            $used = $fs['total'] - $fs['free'];
            $pct = round($used / $fs['total'] * 100, 1);
            $filesystems[$dev]['used_percent'] = $pct;
            $rows[] = $this->row($name, self::bytes($used).' of '.self::bytes($fs['total']).' ('.$pct.'%) — free '.self::bytes($fs['free']),
                self::band($pct, 'disk_used_percent'), $fs['path']);
        }

        return ['rows' => $rows, 'data' => ['filesystems' => array_values($filesystems)]];
    }

    // ─── 5. Dependencies ─────────────────────────────────────────────────────

    protected function dependencies(): array
    {
        $rows = [];

        // Database
        $connection = config('database.default');
        $cfg = config("database.connections.$connection", []);
        $driver = $cfg['driver'] ?? '?';
        $target = $driver === 'sqlite'
            ? basename((string) ($cfg['database'] ?? ''))
            : ($cfg['database'] ?? '?').' on '.($cfg['host'] ?? '?').':'.($cfg['port'] ?? '?');

        $db = $this->attempt(fn () => DB::connection()->select('select 1 as one'));
        $rows[] = $this->dependencyRow("Database ($driver)", $target, $db);

        if ($db['ok'] && in_array($driver, ['mysql', 'mariadb'], true)) {
            $size = $this->attempt(fn () => DB::connection()->selectOne(
                'select sum(data_length + index_length) as bytes from information_schema.tables where table_schema = database()'
            )?->bytes);
            $rows[] = $size['ok']
                ? $this->row('Database size', self::bytes((int) $size['value']))
                : $this->row('Database size', 'unavailable', self::NA, $size['error']);
        }

        // Cache — a real put/get/forget round trip on the configured store
        $store = config('cache.default');
        $cache = $this->attempt(function () {
            $key = self::CACHE_PREFIX.'probe:'.Str::random(8);
            Cache::put($key, 'ok', 30);
            $value = Cache::get($key);
            Cache::forget($key);
            if ($value !== 'ok') {
                throw new \RuntimeException('cache returned '.var_export($value, true).' for a value just written');
            }
        });
        $rows[] = $this->dependencyRow('Cache', $store.' ('.config("cache.stores.$store.driver").')', $cache);

        // Redis — only when something is configured to use it
        $redisConnections = $this->redisConnectionsInUse();
        if ($redisConnections === []) {
            $rows[] = $this->row('Redis', 'not used by this configuration', null,
                'cache='.config('cache.default').', queue='.config('queue.default').', session='.config('session.driver').' — nothing to probe.');
        }
        foreach ($redisConnections as $name => $usedBy) {
            $rcfg = config("database.redis.$name", []);
            $probe = $this->attempt(fn () => Redis::connection($name)->ping());
            $rows[] = $this->dependencyRow("Redis ($name)", ($rcfg['host'] ?? '?').':'.($rcfg['port'] ?? '?').' — used by '.implode(', ', $usedBy), $probe);
        }

        return ['rows' => $rows];
    }

    /** The quick round trips are the only probes banded on latency. */
    protected function dependencyRow(string $label, string $target, array $probe): array
    {
        if (! $probe['ok']) {
            return $this->row($label, $target, self::FAIL, $probe['error']);
        }

        return $this->row($label, $target.' — '.$probe['ms'].' ms', self::band($probe['ms'], 'dependency_latency_ms'));
    }

    /** @return array<string, string[]> redis connection name => what uses it */
    protected function redisConnectionsInUse(): array
    {
        $used = [];

        $store = config('cache.default');
        if (config("cache.stores.$store.driver") === 'redis') {
            $used[config("cache.stores.$store.connection", 'default')][] = 'cache';
        }

        $queue = config('queue.default');
        if (config("queue.connections.$queue.driver") === 'redis') {
            $used[config("queue.connections.$queue.connection", 'default')][] = 'queue';
        }

        if (config('session.driver') === 'redis') {
            $used[config('session.connection') ?: 'default'][] = 'session';
        }

        $broadcast = config('broadcasting.default');
        if (config("broadcasting.connections.$broadcast.driver") === 'redis') {
            $used[config("broadcasting.connections.$broadcast.connection", 'default')][] = 'broadcast';
        }

        return $used;
    }

    // ─── 6. Queues ───────────────────────────────────────────────────────────

    protected function queues(): array
    {
        $connection = config('queue.default');
        $cfg = config("queue.connections.$connection", []);
        $driver = $cfg['driver'] ?? '?';
        $rows = [];

        if ($driver === 'sync') {
            $rows[] = $this->row("Connection: $connection (sync)", 'jobs run inline in the request — there is no queue to back up', self::NA);
        } elseif ($driver === 'database') {
            $table = $cfg['table'] ?? 'jobs';
            $probe = $this->attempt(fn () => DB::connection($cfg['connection'] ?? null)->table($table)
                ->selectRaw('queue, count(*) as depth, min(case when reserved_at is null then available_at end) as oldest_waiting')
                ->groupBy('queue')->get());

            if (! $probe['ok']) {
                $rows[] = $this->row("Connection: $connection (database)", 'unreadable', self::FAIL, $probe['error']);
            } elseif ($probe['value']->isEmpty()) {
                $rows[] = $this->row("Queue: {$cfg['queue']} ($connection)", 'empty', self::OK);
            } else {
                foreach ($probe['value'] as $q) {
                    $waitMin = $q->oldest_waiting ? max(0, (now()->getTimestamp() - (int) $q->oldest_waiting) / 60) : null;
                    $rows[] = $this->row("Queue: {$q->queue} ($connection)",
                        $q->depth.' job(s)'.($waitMin !== null ? ', oldest waiting '.self::duration($waitMin * 60) : ', all reserved by a worker'),
                        $waitMin !== null ? self::band($waitMin, 'queue_wait_minutes') : self::OK);
                }
            }
        } else {
            $queue = $cfg['queue'] ?? 'default';
            $probe = $this->attempt(fn () => Queue::connection($connection)->size($queue));
            $rows[] = $probe['ok']
                ? $this->row("Queue: $queue ($connection, $driver)", $probe['value'].' job(s)', self::OK, 'Depth only; this driver does not expose job age here.')
                : $this->row("Queue: $queue ($connection, $driver)", 'unreadable', self::FAIL, $probe['error']);
        }

        $rows = array_merge($rows, $this->failedJobRows());

        return ['rows' => $rows, 'notes' => [
            'Worker liveness cannot be proved from inside PHP — there is no visibility into supervisor/systemd. A dead worker shows up only as a waiting time that climbs and never falls.',
        ]];
    }

    protected function failedJobRows(): array
    {
        $cfg = config('queue.failed', []);
        if (! in_array($cfg['driver'] ?? null, ['database', 'database-uuids'], true)) {
            return [$this->row('Failed jobs', 'driver '.($cfg['driver'] ?? 'none').' — not inspected', self::NA)];
        }

        $since = now()->subHours(self::FAILED_JOBS_WINDOW_HOURS);
        $probe = $this->attempt(function () use ($cfg, $since) {
            $q = fn () => DB::connection($cfg['database'] ?? null)->table($cfg['table'] ?? 'failed_jobs');

            return [
                'total' => $q()->count(),
                'recent' => $q()->where('failed_at', '>=', $since)->count(),
                'first' => $q()->min('failed_at'),
                'last' => $q()->max('failed_at'),
            ];
        });

        if (! $probe['ok']) {
            return [$this->row('Failed jobs', 'unreadable', self::WARN, $probe['error'])];
        }

        $f = $probe['value'];

        return [
            // Judged on the recent window only, and never worse than a warning.
            $this->row('Failed jobs, last '.self::FAILED_JOBS_WINDOW_HOURS.'h', (string) $f['recent'], self::band($f['recent'], 'failed_jobs_recent'),
                $f['recent'] > 0 ? 'Inspect with php artisan queue:failed; retry with queue:retry.' : null),
            $this->row('Failed jobs, all time (history)', $f['total'] > 0 ? $f['total'].' between '.$f['first'].' and '.$f['last'] : '0'),
        ];
    }

    // ─── 7. Scheduler ────────────────────────────────────────────────────────

    protected function scheduler(): array
    {
        $path = static::heartbeatPath();
        $mtime = @filemtime($path);
        $notes = ['Evidence is a file the scheduler touches every minute (routes/console.php). Cron output itself goes to /dev/null per deployment/PRODUCTION.md.'];

        if ($mtime === false) {
            return ['rows' => [$this->row('Heartbeat', 'none recorded', self::WARN,
                'Either cron is not running schedule:run, or this deploy predates the heartbeat. Check: crontab -u www-data -l')], 'notes' => $notes];
        }

        $ageMin = max(0, (time() - $mtime) / 60);

        return ['rows' => [
            $this->row('Last heartbeat', self::duration($ageMin * 60).' ago ('.date('Y-m-d H:i:s', $mtime).')', self::band($ageMin, 'scheduler_heartbeat_minutes')),
        ], 'notes' => $notes];
    }

    // ─── 8. Backups ──────────────────────────────────────────────────────────

    protected function backups(): array
    {
        $name = (string) config('backup.backup.name');
        $disks = (array) config('backup.backup.destination.disks', []);
        $rows = [];

        if ($disks === []) {
            return ['rows' => [$this->row('Destinations', 'none configured', self::WARN)]];
        }

        foreach ($disks as $disk) {
            $probe = $this->attempt(fn () => $this->cachedData("backups:$disk", self::BACKUP_LISTING_CACHE_SECONDS, fn () => $this->listBackups($disk, $name)));

            if (! $probe['ok']) {
                $rows[] = $this->row("Backups on '$disk'", 'unreachable', self::FAIL, $probe['error']);

                continue;
            }

            $data = $probe['value'];
            if ($data['count'] === 0) {
                $rows[] = $this->row("Backups on '$disk'", 'no archives found in '.$name.'/', self::WARN);

                continue;
            }

            $ageHours = max(0, (time() - $data['newest_at']) / 3600);
            $rows[] = $this->row("Backups on '$disk'",
                'newest '.self::duration($ageHours * 3600).' old ('.date('Y-m-d H:i', $data['newest_at']).', '.self::bytes($data['newest_bytes']).') — '.$data['count'].' archive(s)',
                self::band($ageHours, 'backup_age_hours'),
                $data['from_cache'] ? 'Listing cached; refreshed every '.(self::BACKUP_LISTING_CACHE_SECONDS / 60).' min.' : null);
        }

        return ['rows' => $rows, 'notes' => [
            'The scheduled backup runs "backup:run --only-db": these archives hold the DATABASE ONLY. Uploaded photos are not included.',
        ]];
    }

    /** Data only — no status, no error. */
    protected function listBackups(string $disk, string $name): array
    {
        $count = 0;
        $newestAt = null;
        $newestBytes = 0;

        foreach (Storage::disk($disk)->files($name) as $file) {
            if (! str_ends_with($file, '.zip')) {
                continue;
            }
            $count++;
            $modified = Storage::disk($disk)->lastModified($file);
            if ($newestAt === null || $modified > $newestAt) {
                $newestAt = $modified;
                $newestBytes = Storage::disk($disk)->size($file);
            }
        }

        return ['count' => $count, 'newest_at' => $newestAt, 'newest_bytes' => $newestBytes];
    }

    // ─── 9. Uploads storage ──────────────────────────────────────────────────

    protected function uploads(): array
    {
        $rows = [];

        // Asked twice: what the database filed (live) …
        $db = $this->attempt(fn () => DB::table('photos')->where('disk', self::UPLOADS_DISK)->count());
        $rows[] = $db['ok']
            ? $this->row('Photo rows in database', number_format($db['value']).' (live)', self::OK, 'The photos table has no size column, so this side is a count, not bytes.')
            : $this->row('Photo rows in database', 'unreadable', self::FAIL, $db['error']);

        // … and what is actually on the disk (walked through Flysystem, capped, cached).
        $scan = $this->attempt(fn () => $this->cachedData('uploads-scan', self::UPLOAD_SCAN_CACHE_SECONDS, fn () => $this->scanUploadFiles()));

        if (! $scan['ok']) {
            $rows[] = $this->row('Files on disk', 'scan failed', self::FAIL, $scan['error']);
        } else {
            $s = $scan['value'];
            // A successful scan is OK however long it took — walking many files is just what that costs.
            $rows[] = $this->row('Files on disk', number_format($s['files']).' files, '.self::bytes($s['bytes']).($s['truncated'] ? ' (truncated at '.number_format(self::UPLOAD_SCAN_MAX_FILES).' files)' : ''),
                $s['truncated'] ? self::WARN : self::OK,
                'Scanned '.self::duration(max(0, time() - $s['collected_at'])).' ago in '.$s['scan_ms'].' ms; refreshed every '.(self::UPLOAD_SCAN_CACHE_SECONDS / 60).' min.');

            if ($db['ok'] && ! $s['truncated']) {
                $gap = $s['files'] - $db['value'];
                $rows[] = $this->row('Gap (disk − database)', ($gap > 0 ? '+' : '').number_format($gap).' files', null,
                    $gap > 0 ? 'Files with no photo row — orphans, or temp uploads not yet cleaned by photos:clean.' : ($gap < 0 ? 'Rows whose file is missing.' : null));
            }
        }

        return ['rows' => $rows, 'notes' => [
            'Uploads are on the local "'.self::UPLOADS_DISK.'" disk ('.self::UPLOADS_DIRECTORY.'/); its free space is under Local disks. Cloudflare R2 holds backups only, not uploads.',
            'The database figure is live; the disk scan may be minutes old, so a small gap either way is drift rather than a finding.',
        ]];
    }

    /** Data only — no status, no error. */
    protected function scanUploadFiles(): array
    {
        $start = hrtime(true);
        $files = 0;
        $bytes = 0;
        $truncated = false;

        $listing = Storage::disk(self::UPLOADS_DISK)->getDriver()->listContents(self::UPLOADS_DIRECTORY, true);
        foreach ($listing as $item) {
            if (! $item instanceof FileAttributes) {
                continue;
            }
            if ($files >= self::UPLOAD_SCAN_MAX_FILES) {
                $truncated = true;
                break;
            }
            $files++;
            $bytes += (int) $item->fileSize();
        }

        return ['files' => $files, 'bytes' => $bytes, 'truncated' => $truncated, 'scan_ms' => round((hrtime(true) - $start) / 1e6, 1)];
    }

    // ─── 10. PHP runtime ─────────────────────────────────────────────────────

    protected function php(): array
    {
        $upload = self::iniBytes(ini_get('upload_max_filesize'));
        $post = self::iniBytes(ini_get('post_max_size'));
        $photoLimit = 10 * 1024 * 1024; // TempPhotoManager accepts photos up to 10MB

        $rows = [
            $this->row('memory_limit', ini_get('memory_limit') ?: '?'),
            $this->row('max_execution_time', ini_get('max_execution_time').'s'),
            $this->row('upload_max_filesize', ini_get('upload_max_filesize') ?: '?', $upload !== null && $upload > 0 && $upload < $photoLimit ? self::WARN : self::OK,
                $upload !== null && $upload > 0 && $upload < $photoLimit ? 'Below the 10MB the photo uploader accepts.' : null),
            $this->row('post_max_size', ini_get('post_max_size') ?: '?', $post !== null && $upload !== null && $post > 0 && $post < $upload ? self::WARN : self::OK,
                $post !== null && $upload !== null && $post > 0 && $post < $upload ? 'Smaller than upload_max_filesize, so large uploads are silently dropped.' : null),
            $this->row('max_file_uploads', (string) ini_get('max_file_uploads')),
        ];

        $status = function_exists('opcache_get_status') ? @opcache_get_status(false) : false;
        if (is_array($status) && ! empty($status['opcache_enabled'])) {
            $mem = $status['memory_usage'] ?? [];
            $stats = $status['opcache_statistics'] ?? [];
            $rows[] = $this->row('OPcache', 'enabled — '.self::bytes((int) ($mem['used_memory'] ?? 0)).' used, '.self::bytes((int) ($mem['free_memory'] ?? 0)).' free, hit rate '.round((float) ($stats['opcache_hit_rate'] ?? 0), 1).'%', self::OK);
        } elseif (PHP_SAPI === 'cli') {
            $rows[] = $this->row('OPcache', 'not active in this CLI process', self::NA);
        } else {
            $rows[] = $this->row('OPcache', 'disabled', app()->environment('production') ? self::WARN : self::OK,
                'Every request recompiles every PHP file.');
        }

        return ['rows' => $rows];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    protected function readProc(string $path): ?string
    {
        try {
            if (! @is_readable($path)) {
                return null;
            }
            $raw = @file_get_contents($path);

            return $raw === false || $raw === '' ? null : $raw;
        } catch (\Throwable) {
            return null; // open_basedir and friends
        }
    }

    public static function bytes(int|float|null $bytes): string
    {
        if ($bytes === null) {
            return '?';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while (abs($bytes) >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $i === 0 ? 0 : 1).' '.$units[$i];
    }

    public static function duration(float $seconds): string
    {
        $seconds = (int) round($seconds);
        if ($seconds < 60) {
            return $seconds.'s';
        }
        if ($seconds < 3600) {
            return intdiv($seconds, 60).'m';
        }
        if ($seconds < 86400) {
            return intdiv($seconds, 3600).'h '.intdiv($seconds % 3600, 60).'m';
        }

        return intdiv($seconds, 86400).'d '.intdiv($seconds % 86400, 3600).'h';
    }

    protected static function iniBytes(string|false $value): ?int
    {
        if ($value === false || $value === '') {
            return null;
        }
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $n = (int) $value;

        return match ($unit) {
            'g' => $n * 1024 ** 3,
            'm' => $n * 1024 ** 2,
            'k' => $n * 1024,
            default => $n,
        };
    }

    /**
     * Defence in depth: no section reads a credential, but error strings come
     * from drivers. Any configured secret that turns up is masked.
     */
    protected function redact(array $report): array
    {
        $secrets = [];
        $walk = function ($value, $key = '') use (&$walk, &$secrets) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $walk($v, (string) $k);
                }
            } elseif (is_string($value) && strlen($value) >= 6 && preg_match('/key|secret|password|token/i', $key)) {
                $secrets[] = $value;
                if (str_starts_with($value, 'base64:')) {
                    $secrets[] = substr($value, 7);
                }
            }
        };
        $walk(config()->all());
        $secrets = array_unique($secrets);

        if ($secrets === []) {
            return $report;
        }

        array_walk_recursive($report, function (&$v) use ($secrets) {
            if (is_string($v)) {
                $v = str_replace($secrets, '[redacted]', $v);
            }
        });

        return $report;
    }
}

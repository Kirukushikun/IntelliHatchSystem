<?php

namespace Tests\Feature;

use App\Http\Middleware\SuperadminMiddleware;
use App\Models\User;
use App\Services\SystemHealth;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The project's migrations use MySQL-only statements, so these tests build
 * just the tables the health checks read, on the suite's in-memory SQLite.
 */
class SystemHealthTest extends TestCase
{
    private string $backupRoot;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('first_name');
            $t->string('last_name');
            $t->integer('user_type')->default(2);
            $t->boolean('is_disabled')->default(false);
            $t->string('username')->unique();
            $t->string('password');
            $t->rememberToken();
            $t->timestamps();
        });
        Schema::create('jobs', function (Blueprint $t) {
            $t->id();
            $t->string('queue')->index();
            $t->longText('payload');
            $t->unsignedTinyInteger('attempts');
            $t->unsignedInteger('reserved_at')->nullable();
            $t->unsignedInteger('available_at');
            $t->unsignedInteger('created_at');
        });
        Schema::create('failed_jobs', function (Blueprint $t) {
            $t->id();
            $t->string('uuid')->unique();
            $t->text('connection');
            $t->text('queue');
            $t->longText('payload');
            $t->longText('exception');
            $t->timestamp('failed_at')->useCurrent();
        });
        Schema::create('photos', function (Blueprint $t) {
            $t->id();
            $t->string('public_path');
            $t->string('disk')->default('public');
            $t->unsignedBigInteger('uploaded_by')->nullable();
            $t->timestamps();
        });

        Storage::fake('public');
        $this->backupRoot = sys_get_temp_dir().'/ihs-health-backups-'.uniqid();
        @mkdir($this->backupRoot.'/IHS', 0777, true);
        config([
            'backup.backup.name' => 'IHS',
            'backup.backup.destination.disks' => ['local'],
            'filesystems.disks.local.root' => $this->backupRoot,
        ]);
        Storage::forgetDisk('local');

        touch(SystemHealth::heartbeatPath());
    }

    protected function tearDown(): void
    {
        @unlink(SystemHealth::heartbeatPath());
        foreach (glob($this->backupRoot.'/IHS/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->backupRoot.'/IHS');
        @rmdir($this->backupRoot);

        parent::tearDown();
    }

    private function user(int $type): User
    {
        return User::factory()->create(['user_type' => $type]);
    }

    private function section(array $report, string $key): array
    {
        return $report['sections'][$key];
    }

    private function rowsLike(array $section, string $needle): array
    {
        return array_values(array_filter($section['rows'], fn ($r) => str_contains($r['label'], $needle)));
    }

    private function writeBackup(int $ageSeconds): void
    {
        $file = $this->backupRoot.'/IHS/'.date('Y-m-d-H-i-s', time() - $ageSeconds).'.zip';
        file_put_contents($file, 'zip');
        touch($file, time() - $ageSeconds);
    }

    // ─── Access ──────────────────────────────────────────────────────────────

    public function test_guest_is_sent_to_login_on_both_doors(): void
    {
        $this->get('/admin/system-health')->assertRedirect('/login');
        $this->get('/admin/system-health.json')->assertRedirect('/login');
    }

    public function test_hatchery_user_is_refused(): void
    {
        $user = $this->user(2);
        $this->actingAs($user)->get('/admin/system-health')->assertRedirect();
        $this->actingAs($user)->get('/admin/system-health.json')->assertRedirect();
    }

    public function test_ordinary_admin_is_refused_on_both_doors(): void
    {
        // The near miss: roles are additive (AdminMiddleware admits superadmins), so
        // an "admin" gate would let every administrator in.
        $admin = $this->user(1);
        $this->actingAs($admin)->get('/admin/system-health')->assertRedirect('/admin/dashboard');
        $this->actingAs($admin)->get('/admin/system-health.json')->assertRedirect('/admin/dashboard');
    }

    public function test_controller_refuses_admin_even_without_route_middleware(): void
    {
        $admin = $this->user(1);
        $this->withoutMiddleware(SuperadminMiddleware::class);

        $this->actingAs($admin)->get('/admin/system-health')->assertForbidden();
        $this->actingAs($admin)->get('/admin/system-health.json')->assertForbidden();
    }

    public function test_disabled_superadmin_is_refused_by_the_gate(): void
    {
        $this->assertFalse(SystemHealth::canView(User::factory()->make(['user_type' => 0, 'is_disabled' => true])));
        $this->assertFalse(SystemHealth::canView(User::factory()->make(['user_type' => 1])));
        $this->assertFalse(SystemHealth::canView(null));
        $this->assertTrue(SystemHealth::canView(User::factory()->make(['user_type' => 0])));
    }

    public function test_superadmin_sees_page_and_json(): void
    {
        $super = $this->user(0);
        $this->actingAs($super)->get('/admin/system-health')->assertOk()->assertSee('System Health')->assertSee('Thresholds');
        $this->actingAs($super)->getJson('/admin/system-health.json')->assertJsonStructure(['status', 'generated_at', 'sections', 'thresholds']);
    }

    public function test_nav_item_follows_the_same_gate(): void
    {
        $this->actingAs($this->user(0))->get('/admin/system-health')->assertSee('href="/admin/system-health"', false);

        $this->withoutMiddleware(SuperadminMiddleware::class);
        $html = view('components.sidebar', ['user' => $this->user(1)])->render();
        $this->assertStringNotContainsString('/admin/system-health', $html);
    }

    // ─── Shape ───────────────────────────────────────────────────────────────

    public function test_every_section_is_present_and_overall_status_is_valid(): void
    {
        $report = app(SystemHealth::class)->report();

        $this->assertSame(array_keys(SystemHealth::SECTIONS), array_keys($report['sections']));
        $this->assertContains($report['status'], [SystemHealth::OK, SystemHealth::WARN, SystemHealth::FAIL]);
        foreach ($report['sections'] as $section) {
            $this->assertContains($section['status'], [SystemHealth::OK, SystemHealth::WARN, SystemHealth::FAIL, SystemHealth::NA]);
            $this->assertArrayHasKey('rows', $section);
        }
    }

    public function test_page_renders_thresholds_from_the_constant(): void
    {
        $response = $this->actingAs($this->user(0))->get('/admin/system-health');
        foreach (SystemHealth::THRESHOLDS as $t) {
            $response->assertSee($t['label']);
            $response->assertSee('≥ '.$t['warn'].$t['unit']);
        }
    }

    // ─── Degradation ─────────────────────────────────────────────────────────

    private function breakRedis(): void
    {
        $broken = ['host' => 'redis.nonexistent.invalid', 'port' => 6379, 'timeout' => 1, 'max_retries' => 0, 'database' => 0];
        config([
            'database.redis.default' => $broken,
            'database.redis.cache' => $broken,
            'cache.default' => 'redis',
        ]);
    }

    public function test_unresolvable_dependency_degrades_instead_of_throwing(): void
    {
        $this->breakRedis();

        $response = $this->actingAs($this->user(0))->get('/admin/system-health');
        $response->assertOk();
        $response->assertSee('Redis (cache)');
        $response->assertSee('redis.nonexistent.invalid');

        $report = app(SystemHealth::class)->report();
        $deps = $this->section($report, 'dependencies');
        $this->assertSame(SystemHealth::FAIL, $deps['status']);
        $redis = $this->rowsLike($deps, 'Redis (cache)')[0];
        $this->assertSame(SystemHealth::FAIL, $redis['status']);
        $this->assertNotEmpty($redis['detail']);
        $this->assertSame(SystemHealth::FAIL, $report['status']);
    }

    public function test_a_section_that_throws_a_type_error_becomes_a_failed_section(): void
    {
        $this->app->instance(SystemHealth::class, new class extends SystemHealth
        {
            protected function cpu(): array
            {
                return array_sum('not an array'); // TypeError, not an Exception
            }
        });

        $response = $this->actingAs($this->user(0))->get('/admin/system-health');
        $response->assertOk()->assertSee('TypeError');

        $report = app(SystemHealth::class)->report();
        $this->assertSame(SystemHealth::FAIL, $report['sections']['cpu']['status']);
        $this->assertSame(SystemHealth::FAIL, $report['status']);
    }

    public function test_json_answers_503_on_failure(): void
    {
        $this->breakRedis();
        $this->actingAs($this->user(0))->getJson('/admin/system-health.json')
            ->assertStatus(503)->assertJson(['status' => 'fail']);
    }

    public function test_json_answers_200_on_warning(): void
    {
        $this->app->instance(SystemHealth::class, new class extends SystemHealth
        {
            public function report(): array
            {
                return ['status' => self::WARN, 'sections' => []];
            }
        });
        $this->actingAs($this->user(0))->getJson('/admin/system-health.json')->assertOk()->assertJson(['status' => 'warn']);
    }

    public function test_redis_is_not_probed_when_nothing_uses_it(): void
    {
        // The suite runs on an array cache and sync queue; an unconditional probe
        // would report every test run and every dev machine as broken.
        config(['database.redis.default.host' => 'redis.nonexistent.invalid', 'database.redis.cache.host' => 'redis.nonexistent.invalid']);

        $deps = $this->section(app(SystemHealth::class)->report(), 'dependencies');
        $redis = $this->rowsLike($deps, 'Redis');
        $this->assertCount(1, $redis);
        $this->assertSame('not used by this configuration', $redis[0]['value']);
        $this->assertNotSame(SystemHealth::FAIL, $deps['status']);
    }

    // ─── Secrets ─────────────────────────────────────────────────────────────

    public function test_no_secrets_in_the_payload(): void
    {
        config([
            'database.connections.mysql.password' => 'db-password-sentinel-91f3',
            'filesystems.disks.r2.secret' => 'r2-secret-sentinel-77ab',
            'filesystems.disks.r2.key' => 'r2-key-sentinel-0c1d',
            'database.redis.default.password' => 'redis-password-sentinel-5e2a',
        ]);
        $secrets = array_filter([
            config('app.key'), // the real APP_KEY
            env('DB_PASSWORD') ?: null, // the real database password, when set
            'db-password-sentinel-91f3', 'r2-secret-sentinel-77ab', 'r2-key-sentinel-0c1d', 'redis-password-sentinel-5e2a',
        ]);
        $this->assertNotEmpty(config('app.key'));

        $super = $this->user(0);
        $json = $this->actingAs($super)->get('/admin/system-health.json')->getContent();
        $html = $this->actingAs($super)->get('/admin/system-health')->getContent();

        foreach ($secrets as $secret) {
            $this->assertStringNotContainsString($secret, $json);
            $this->assertStringNotContainsString($secret, $html);
            $this->assertStringNotContainsString(str_replace('base64:', '', $secret), $json);
        }
    }

    public function test_a_secret_in_a_driver_error_is_redacted(): void
    {
        config(['database.connections.broken' => [
            'driver' => 'sqlite',
            'database' => '/nonexistent/s3cret-sentinel-path.sqlite',
            'password' => 's3cret-sentinel',
        ], 'database.default' => 'broken']);

        $json = json_encode(app(SystemHealth::class)->report());
        $this->assertStringNotContainsString('s3cret-sentinel', $json);
        $this->assertStringContainsString('[redacted]', $json);
    }

    // ─── Latency ─────────────────────────────────────────────────────────────

    public function test_a_slow_but_successful_scan_is_ok(): void
    {
        $this->app->instance(SystemHealth::class, new class extends SystemHealth
        {
            protected function scanUploadFiles(): array
            {
                usleep(1_100_000); // slower than the dependency fail band

                return parent::scanUploadFiles();
            }
        });

        $uploads = $this->section(app(SystemHealth::class)->report(), 'uploads');
        $this->assertSame(SystemHealth::OK, $this->rowsLike($uploads, 'Files on disk')[0]['status']);
        $this->assertSame(SystemHealth::OK, $uploads['status']);
    }

    public function test_a_fast_failure_is_still_a_failure(): void
    {
        $this->app->instance(SystemHealth::class, new class extends SystemHealth
        {
            protected function scanUploadFiles(): array
            {
                throw new \RuntimeException('disk gone');
            }
        });

        $uploads = $this->section(app(SystemHealth::class)->report(), 'uploads');
        $this->assertSame(SystemHealth::FAIL, $uploads['status']);
        $this->assertStringContainsString('disk gone', $this->rowsLike($uploads, 'Files on disk')[0]['detail']);
    }

    public function test_latency_band_applies_to_quick_round_trips(): void
    {
        $this->assertSame(SystemHealth::OK, SystemHealth::band(5, 'dependency_latency_ms'));
        $this->assertSame(SystemHealth::WARN, SystemHealth::band(SystemHealth::THRESHOLDS['dependency_latency_ms']['warn'], 'dependency_latency_ms'));
        $this->assertSame(SystemHealth::FAIL, SystemHealth::band(SystemHealth::THRESHOLDS['dependency_latency_ms']['fail'], 'dependency_latency_ms'));
    }

    // ─── Caching ─────────────────────────────────────────────────────────────

    public function test_cached_values_carry_no_verdict(): void
    {
        Storage::disk('public')->put('forms/a.jpg', 'abc');
        $this->writeBackup(3600);

        app(SystemHealth::class)->report();

        foreach (['uploads-scan', 'backups:local'] as $key) {
            $cached = Cache::get(SystemHealth::CACHE_PREFIX.$key);
            $this->assertIsArray($cached, "$key was not cached");
            foreach (['status', 'ok', 'error'] as $verdictKey) {
                $this->assertArrayNotHasKey($verdictKey, $cached, "$key cached a verdict ($verdictKey)");
            }
        }
    }

    public function test_verdict_is_recomputed_from_cached_data_on_read(): void
    {
        // A cached listing saying the newest backup is 100h old must read as failed today,
        // whatever the code that cached it thought.
        Cache::put(SystemHealth::CACHE_PREFIX.'backups:local', [
            'count' => 1, 'newest_at' => time() - 100 * 3600, 'newest_bytes' => 10, 'collected_at' => time(),
        ], 600);

        $backups = $this->section(app(SystemHealth::class)->report(), 'backups');
        $this->assertSame(SystemHealth::FAIL, $backups['status']);
    }

    public function test_a_failure_is_not_cached_so_recovery_is_immediate(): void
    {
        $health = new class extends SystemHealth
        {
            public bool $broken = true;

            protected function scanUploadFiles(): array
            {
                if ($this->broken) {
                    throw new \RuntimeException('transient blip');
                }

                return parent::scanUploadFiles();
            }
        };

        $this->assertSame(SystemHealth::FAIL, $health->report()['sections']['uploads']['status']);
        $this->assertNull(Cache::get(SystemHealth::CACHE_PREFIX.'uploads-scan'));

        $health->broken = false;
        $this->assertSame(SystemHealth::OK, $health->report()['sections']['uploads']['status']);
    }

    // ─── Queues ──────────────────────────────────────────────────────────────

    private function failJob(string $failedAt): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(), 'connection' => 'database', 'queue' => 'default',
            'payload' => '{}', 'exception' => 'boom', 'failed_at' => $failedAt,
        ]);
    }

    private function queueStatus(): array
    {
        config(['queue.default' => 'database']);

        return $this->section(app(SystemHealth::class)->report(), 'queues');
    }

    public function test_an_old_failed_job_is_history_not_a_warning(): void
    {
        $this->failJob(now()->subDays(40)->toDateTimeString());

        $queues = $this->queueStatus();
        $this->assertSame(SystemHealth::OK, $queues['status']);
        $this->assertStringContainsString('1 between', $this->rowsLike($queues, 'all time')[0]['value']);
    }

    public function test_a_recent_failed_job_warns_but_is_never_fatal(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $this->failJob(now()->subMinutes(10)->toDateTimeString());
        }

        $queues = $this->queueStatus();
        $this->assertSame(SystemHealth::WARN, $queues['status']);
        $this->assertSame(SystemHealth::WARN, $this->rowsLike($queues, 'last '.SystemHealth::FAILED_JOBS_WINDOW_HOURS.'h')[0]['status']);
    }

    public function test_a_job_waiting_too_long_fails_the_queue(): void
    {
        DB::table('jobs')->insert(['queue' => 'default', 'payload' => '{}', 'attempts' => 0, 'reserved_at' => null,
            'available_at' => time() - 3600, 'created_at' => time() - 3600]);

        $this->assertSame(SystemHealth::FAIL, $this->queueStatus()['status']);
    }

    // ─── Scheduler & backups ─────────────────────────────────────────────────

    public function test_scheduler_heartbeat_age_is_judged(): void
    {
        $scheduler = fn () => $this->section(app(SystemHealth::class)->report(), 'scheduler')['status'];

        $this->assertSame(SystemHealth::OK, $scheduler());

        touch(SystemHealth::heartbeatPath(), time() - 3600);
        clearstatcache();
        $this->assertSame(SystemHealth::FAIL, $scheduler());

        @unlink(SystemHealth::heartbeatPath());
        clearstatcache();
        $this->assertSame(SystemHealth::WARN, $scheduler());
    }

    public function test_the_heartbeat_is_scheduled(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('system-health:heartbeat')->assertSuccessful();
    }

    public function test_backups_are_judged_by_age_not_existence(): void
    {
        $backups = fn () => $this->section(app(SystemHealth::class)->report(), 'backups')['status'];

        $this->assertSame(SystemHealth::WARN, $backups(), 'no archives at all should warn');

        $this->writeBackup(10 * 86400);
        Cache::flush();
        $this->assertSame(SystemHealth::FAIL, $backups(), 'only a 10-day-old archive should fail');

        $this->writeBackup(3600);
        Cache::flush();
        $this->assertSame(SystemHealth::OK, $backups());
    }

    public function test_database_only_note_matches_the_schedule(): void
    {
        // The page tells people backups exclude uploads; fail if the schedule stops saying so.
        $this->assertStringContainsString('backup:run --only-db', file_get_contents(base_path('routes/console.php')));
        $notes = implode(' ', $this->section(app(SystemHealth::class)->report(), 'backups')['notes']);
        $this->assertStringContainsString('DATABASE ONLY', $notes);
    }

    // ─── Uploads ─────────────────────────────────────────────────────────────

    public function test_upload_storage_is_asked_twice_and_reports_the_gap(): void
    {
        Storage::disk('public')->put('forms/a.jpg', str_repeat('x', 100));
        Storage::disk('public')->put('forms/b.jpg', str_repeat('x', 50));
        Storage::disk('public')->put('forms/orphan.jpg', 'x');
        DB::table('photos')->insert([
            ['public_path' => '/storage/forms/a.jpg', 'disk' => 'public'],
            ['public_path' => '/storage/forms/b.jpg', 'disk' => 'public'],
        ]);

        $uploads = $this->section(app(SystemHealth::class)->report(), 'uploads');
        $this->assertStringContainsString('3 files, 151 B', $this->rowsLike($uploads, 'Files on disk')[0]['value']);
        $this->assertStringStartsWith('2', $this->rowsLike($uploads, 'Photo rows')[0]['value']);
        $this->assertSame('+1 files', $this->rowsLike($uploads, 'Gap')[0]['value']);
    }

    // ─── Platform figures ────────────────────────────────────────────────────

    public function test_memory_used_is_total_minus_available_not_free(): void
    {
        $mem = SystemHealth::memoryFromMeminfo(<<<'TXT'
            MemTotal:        8000000 kB
            MemFree:          200000 kB
            MemAvailable:    6000000 kB
            Buffers:          100000 kB
            Cached:          5000000 kB
            SReclaimable:     300000 kB
            SwapTotal:             0 kB
            SwapFree:              0 kB
            TXT);

        $this->assertSame(2000000 * 1024, $mem['used']);
        $this->assertSame(25.0, $mem['used_percent']);
        $this->assertSame(5400000 * 1024, $mem['cache']);
        $this->assertSame(0, $mem['swap_total']);
        $this->assertNull($mem['swap_used_percent']);
    }

    public function test_real_proc_and_disk_figures_are_sane(): void
    {
        $report = app(SystemHealth::class)->report();

        if (! is_readable('/proc/meminfo')) {
            $this->markTestSkipped('/proc is not available on this platform.');
        }

        $mem = $report['sections']['memory']['data'];
        $this->assertGreaterThan(0, $mem['total']);
        $this->assertLessThanOrEqual($mem['total'], $mem['available']);
        $this->assertGreaterThanOrEqual(0, $mem['used']);

        $cpu = $report['sections']['cpu']['data'];
        $this->assertGreaterThan(0, $cpu['cores']);
        $this->assertGreaterThanOrEqual(0, $cpu['load_per_core']);

        $filesystems = $report['sections']['disks']['data']['filesystems'];
        $this->assertNotEmpty($filesystems);
        $devices = [];
        foreach ($filesystems as $fs) {
            $this->assertGreaterThanOrEqual(0, $fs['used_percent']);
            $this->assertLessThanOrEqual(100, $fs['used_percent']);
            $devices[] = stat($fs['path'])['dev'];
        }
        $this->assertSame(count($devices), count(array_unique($devices)), 'one row per filesystem');

        // Storage and Logs live on one filesystem here; they must share a row.
        if (stat(storage_path())['dev'] === stat(storage_path('logs'))['dev']) {
            $row = array_values(array_filter($filesystems, fn ($fs) => in_array('Storage', $fs['labels'], true)))[0];
            $this->assertContains('Logs', $row['labels']);
        }
    }
}

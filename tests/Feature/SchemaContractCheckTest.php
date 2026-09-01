<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\IntegrityReport;
use App\Services\Integrity\SchemaContractProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class SchemaContractCheckTest extends TestCase
{
    use RefreshDatabase;

    private string $fixtureRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureRoot = storage_path('framework/testing/schema-contract-'.uniqid());
        File::ensureDirectoryExists($this->fixtureRoot.'/migrations');
        File::ensureDirectoryExists($this->fixtureRoot.'/app');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->fixtureRoot);

        parent::tearDown();
    }

    public function test_it_only_reports_provable_overflows_for_the_resolved_table(): void
    {
        $this->migration(<<<'PHP'
<?php
Schema::create('zero_result_searches', function (Blueprint $table): void {
    $table->string('suggested_action', 40);
});
Schema::create('content_factory_batches', function (Blueprint $table): void {
    $table->string('status', 40);
    $table->string('suggested_action', 255);
});
PHP);
        File::put($this->fixtureRoot.'/app/Writer.php', <<<'PHP'
<?php
DB::table('zero_result_searches')->update(['suggested_action' => 'https://housechefcyprus.com/sure/20-dakika']);
DB::table('content_factory_batches')->insert(['status' => 'ready_for_human_review']);
DB::table('zero_result_searches')->update(['suggested_action' => $dynamicValue]);
PHP);

        $report = app(SchemaContractProbe::class)->inspect(
            [$this->fixtureRoot.'/app'],
            [$this->fixtureRoot.'/migrations'],
        );

        $this->assertCount(3, $report['writers']);
        $this->assertSame(1, $report['finding_count']);
        $this->assertSame('zero_result_searches', $report['findings'][0]['table']);
        $this->assertSame('suggested_action', $report['findings'][0]['column']);
        $this->assertSame('literal_overflow', $report['findings'][0]['reason']);
        $this->assertNotContains('unbounded_dynamic_write', array_column($report['findings'], 'reason'));
        $this->assertFalse(collect($report['findings'])->contains(
            fn (array $finding): bool => $finding['table'] === 'content_factory_batches',
        ));
        $this->assertSame([40, 255], $report['column_length_variants'][0]['lengths']);
    }

    public function test_it_resolves_model_query_and_typed_model_instance_writes(): void
    {
        $this->migration(<<<'PHP'
<?php
Schema::create('zero_result_searches', function (Blueprint $table): void {
    $table->string('suggested_action', 40);
});
Schema::create('content_factory_batches', function (Blueprint $table): void {
    $table->string('status', 40);
});
PHP);
        File::put($this->fixtureRoot.'/app/Writer.php', <<<'PHP'
<?php
\App\Models\ZeroResultSearch::query()->update(['suggested_action' => 'https://housechefcyprus.com/sure/20-dakika']);
$batch = \App\Models\ContentFactoryBatch::query()->firstOrCreate([]);
$batch->update(['status' => 'ready_for_human_review']);
function resolveIt(\App\Models\ZeroResultSearch $search): void {
    $search->update(['suggested_action' => '/sure/20-dakika']);
}
PHP);

        $report = app(SchemaContractProbe::class)->inspect(
            [$this->fixtureRoot.'/app'],
            [$this->fixtureRoot.'/migrations'],
        );

        $this->assertCount(3, $report['writers']);
        $this->assertSame(1, $report['finding_count']);
        $this->assertSame('zero_result_searches', $report['findings'][0]['table']);
        $this->assertTrue(collect($report['writers'])->contains(
            fn (array $writer): bool => $writer['table'] === 'content_factory_batches'
                && $writer['column'] === 'status'
                && $writer['observed_length'] === 22
                && $writer['reason'] === null,
        ));
    }

    public function test_it_reports_unknown_receivers_and_resolves_typed_model_properties(): void
    {
        $this->migration(<<<'PHP'
<?php
Schema::create('zero_result_searches', function (Blueprint $table): void {
    $table->string('suggested_action', 40);
});
PHP);
        File::put($this->fixtureRoot.'/app/Writer.php', <<<'PHP'
<?php
final class Writer {
    public function __construct(private \App\Models\ZeroResultSearch $searches) {}

    public function write($repository): void {
        $this->searches->update(['suggested_action' => '/sure/20-dakika']);
        $this->searches->newQuery()->update(['suggested_action' => '/sure/30-dakika']);
        $repository->update(['suggested_action' => '/zorluk/kolay']);
    }
}
PHP);

        $report = app(SchemaContractProbe::class)->inspect(
            [$this->fixtureRoot.'/app'],
            [$this->fixtureRoot.'/migrations'],
        );

        $this->assertCount(2, $report['writers']);
        $this->assertSame('zero_result_searches', $report['writers'][0]['table']);
        $this->assertSame(1, $report['skipped_writer_count']);
        $this->assertSame('variable_receiver', $report['skipped_writers'][0]['pattern']);
        $this->assertSame(['suggested_action'], $report['skipped_writers'][0]['columns']);
    }

    public function test_it_reports_production_schema_length_missing_and_extra_column_drift(): void
    {
        $this->migration(<<<'PHP'
<?php
Schema::create('things', function (Blueprint $table): void {
    $table->string('state', 8);
    $table->string('code', 4);
});
PHP);

        $report = app(SchemaContractProbe::class)->inspect(
            [],
            [$this->fixtureRoot.'/migrations'],
            [
                ['table' => 'things', 'column' => 'state', 'type' => 'text', 'max_length' => 12],
                ['table' => 'things', 'column' => 'manual_column', 'type' => 'varchar', 'max_length' => 20],
            ],
        );

        $reasons = array_column($report['schema_drift'], 'reason');

        $this->assertContains('length_mismatch', $reasons);
        $this->assertContains('type_mismatch', $reasons);
        $this->assertContains('missing_in_database', $reasons);
        $this->assertContains('unexpected_in_database', $reasons);
    }

    public function test_migration_down_method_does_not_erase_the_up_contract(): void
    {
        $this->migration(<<<'PHP'
<?php
return new class {
    public function up(): void
    {
        Schema::create('things', function (Blueprint $table): void {
            $table->string('state', 8);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('things');
    }
};
PHP);

        $report = app(SchemaContractProbe::class)->inspect([], [$this->fixtureRoot.'/migrations']);

        $this->assertSame('things', $report['columns'][0]['table']);
        $this->assertSame('state', $report['columns'][0]['column']);
    }

    public function test_schema_parser_understands_constants_loops_and_blueprint_column_macros(): void
    {
        $this->migration(<<<'PHP'
<?php
return new class {
    private const TABLE = 'things';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->id();
            $table->morphs('subject');
            $table->softDeletes();
            $table->rememberToken();
        });

        foreach (['things', 'others'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->ulid('public_id');
            });
        }
    }
};
PHP);

        $report = app(SchemaContractProbe::class)->inspect([], [$this->fixtureRoot.'/migrations'], [
            ['table' => 'things', 'column' => 'id', 'type' => 'bigint', 'max_length' => null],
            ['table' => 'things', 'column' => 'subject_type', 'type' => 'varchar', 'max_length' => 255],
            ['table' => 'things', 'column' => 'subject_id', 'type' => 'bigint', 'max_length' => null],
            ['table' => 'things', 'column' => 'deleted_at', 'type' => 'timestamp', 'max_length' => null],
            ['table' => 'things', 'column' => 'remember_token', 'type' => 'varchar', 'max_length' => 100],
            ['table' => 'things', 'column' => 'public_id', 'type' => 'char', 'max_length' => 26],
            ['table' => 'others', 'column' => 'public_id', 'type' => 'char', 'max_length' => 26],
        ]);

        $this->assertCount(2, $report['columns']);
        $this->assertSame([], $report['schema_drift']);
    }

    public function test_the_command_is_observational_records_history_and_returns_success(): void
    {
        $this->migration(<<<'PHP'
<?php
Schema::create('things', function (Blueprint $table): void {
    $table->string('state', 8);
});
PHP);
        File::put($this->fixtureRoot.'/app/Writer.php', "<?php\nDB::table('things')->insert(['state' => 'too-long-state']);\n");

        $this->artisan('db:schema-contract', [
            '--source' => [$this->fixtureRoot.'/app'],
            '--migrations' => [$this->fixtureRoot.'/migrations'],
            '--json' => true,
        ])->assertSuccessful();

        $saved = IntegrityReport::query()->where('guard', 'db:schema-contract')->firstOrFail();

        $this->assertSame('issues_found', $saved->status);
        $this->assertSame(1, $saved->headline);
    }

    private function migration(string $contents): void
    {
        File::put($this->fixtureRoot.'/migrations/2026_01_01_000000_contract.php', $contents);
    }
}

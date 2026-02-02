<?php

namespace Tests\Feature\Api;

use App\Models\Form;
use App\Models\FormType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormStatsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        /** @var \App\Models\User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);
    }

    public function test_can_get_form_stats_with_current_month_filter()
    {
        // Create test data
        $formType = FormType::factory()->create(['name' => 'Test Form']);
        Form::factory()->create([
            'form_type_id' => $formType->id,
            'date_submitted' => Carbon::now(),
        ]);

        $response = $this->getJson('/api/v1/form-stats?date_filter=current_month');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'date_filter',
                'date_range',
                'form_types',
                'stats',
                'summary',
            ]);
    }

    public function test_can_get_form_stats_with_custom_date_range()
    {
        $formType = FormType::factory()->create();
        Form::factory()->create([
            'form_type_id' => $formType->id,
            'date_submitted' => Carbon::parse('2024-01-15'),
        ]);

        $response = $this->getJson('/api/v1/form-stats', [
            'date_filter' => 'custom',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        $response->assertStatus(200);
    }

    public function test_can_get_form_types()
    {
        FormType::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/form-types');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'form_types');
    }

    public function test_can_get_quick_stats()
    {
        $formType = FormType::factory()->create();
        Form::factory()->count(5)->create([
            'form_type_id' => $formType->id,
            'date_submitted' => Carbon::now(),
        ]);

        $response = $this->getJson('/api/v1/form-stats/quick');

        $response->assertStatus(200)
            ->assertJsonStructure(['week', 'month', 'year']);
    }

    public function test_validates_date_filter_parameter()
    {
        $response = $this->getJson('/api/v1/form-stats?date_filter=invalid');

        $response->assertStatus(422);
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CourseApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_index_returns_empty_array_when_no_courses_exist(): void
    {
        $response = $this->getJson('/api/v1/courses');

        $response
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_index_returns_all_courses(): void
    {
        $firstCourse = Course::factory()->create();
        $secondCourse = Course::factory()->create();

        $response = $this->getJson('/api/v1/courses');

        $response
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', $firstCourse->id)
            ->assertJsonPath('1.id', $secondCourse->id)
            ->assertJsonPath('0.price_cents', $firstCourse->price_cents);
    }

    public function test_index_can_filter_courses_by_search_query_using_name_and_description(): void
    {
        $nameMatch = Course::factory()->create([
            'name' => 'Laravel API Design',
            'description' => 'Advanced resource controllers',
        ]);

        $descriptionMatch = Course::factory()->create([
            'name' => 'Docker Fundamentals',
            'description' => 'Deploy your Laravel app safely',
        ]);

        Course::factory()->create([
            'name' => 'Node.js Basics',
            'description' => 'Intro to JavaScript backends',
        ]);

        $response = $this->getJson('/api/v1/courses?search=laravel');

        $response
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', $nameMatch->id)
            ->assertJsonPath('1.id', $descriptionMatch->id);
    }

    public function test_index_search_results_are_cached_between_requests(): void
    {
        $course = Course::factory()->create([
            'name' => 'Caching in Laravel',
            'description' => 'Performance essentials',
        ]);

        $this->getJson('/api/v1/courses?search=caching')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $course->id);

        DB::table('courses')->where('id', $course->id)->update([
            'name' => 'Renamed Course',
        ]);

        $this->getJson('/api/v1/courses?search=caching')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $course->id);
    }

    public function test_index_cache_is_invalidated_when_course_is_updated(): void
    {
        $course = Course::factory()->create([
            'name' => 'Cached Term',
            'description' => 'Initial description',
            'price_cents' => 12000,
        ]);

        $this->getJson('/api/v1/courses?search=cached')
            ->assertOk()
            ->assertJsonCount(1);

        $this->putJson("/api/v1/courses/{$course->id}", [
            'name' => 'Updated Course Name',
            'description' => 'Initial description',
            'price_cents' => 12000,
        ])->assertOk();

        $this->getJson('/api/v1/courses?search=cached')
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_index_cache_is_invalidated_when_course_is_created(): void
    {
        Course::factory()->create([
            'name' => 'Distributed Systems',
            'description' => 'Consensus and replication',
        ]);

        $this->getJson('/api/v1/courses?search=distributed')
            ->assertOk()
            ->assertJsonCount(1);

        $this->postJson('/api/v1/courses', [
            'name' => 'Distributed Tracing',
            'description' => 'Observability with traces',
            'price_cents' => 9000,
        ])->assertCreated();

        $this->getJson('/api/v1/courses?search=distributed')
            ->assertOk()
            ->assertJsonCount(2);
    }

    public function test_show_returns_single_course(): void
    {
        $course = Course::factory()->create();

        $response = $this->getJson("/api/v1/courses/{$course->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $course->id)
            ->assertJsonPath('name', $course->name)
            ->assertJsonPath('description', $course->description)
            ->assertJsonPath('price_cents', $course->price_cents);
    }

    public function test_show_returns_404_when_course_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/courses/999999');

        $response->assertNotFound();
    }

    public function test_show_results_are_cached_between_requests(): void
    {
        $course = Course::factory()->create([
            'name' => 'Original Name',
        ]);

        $this->getJson("/api/v1/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('name', 'Original Name');

        DB::table('courses')->where('id', $course->id)->update([
            'name' => 'Updated Name',
        ]);

        $this->getJson("/api/v1/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('name', 'Original Name');
    }

    public function test_show_cache_is_invalidated_when_course_is_updated(): void
    {
        $course = Course::factory()->create([
            'name' => 'Old Name',
            'description' => 'Old Description',
            'price_cents' => 2500,
        ]);

        $this->getJson("/api/v1/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('name', 'Old Name');

        $this->putJson("/api/v1/courses/{$course->id}", [
            'name' => 'New Name',
            'description' => 'Old Description',
            'price_cents' => 2500,
        ])->assertOk();

        $this->getJson("/api/v1/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('name', 'New Name');
    }

    public function test_store_creates_course_and_returns_201(): void
    {
        $payload = [
            'name' => 'Laravel 12 APIs',
            'description' => 'Build robust versioned APIs.',
            'price_cents' => 9900,
        ];

        $response = $this->postJson('/api/v1/courses', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('name', $payload['name'])
            ->assertJsonPath('description', $payload['description'])
            ->assertJsonPath('price_cents', $payload['price_cents']);

        $this->assertDatabaseHas('courses', $payload);
    }

    public function test_store_returns_422_for_invalid_payload(): void
    {
        $response = $this->postJson('/api/v1/courses', [
            'name' => '',
            'description' => ['not', 'a', 'string'],
            'price_cents' => -1,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'description', 'price_cents']);
    }

    public function test_update_modifies_course_and_returns_200(): void
    {
        $course = Course::factory()->create([
            'name' => 'Old Name',
            'description' => 'Old Description',
            'price_cents' => 10000,
        ]);

        $payload = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'price_cents' => 15000,
        ];

        $response = $this->putJson("/api/v1/courses/{$course->id}", $payload);

        $response
            ->assertOk()
            ->assertJsonPath('id', $course->id)
            ->assertJsonPath('name', $payload['name'])
            ->assertJsonPath('description', $payload['description'])
            ->assertJsonPath('price_cents', $payload['price_cents']);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => $payload['name'],
            'description' => $payload['description'],
            'price_cents' => $payload['price_cents'],
        ]);
    }

    public function test_update_returns_404_when_course_does_not_exist(): void
    {
        $response = $this->putJson('/api/v1/courses/999999', [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'price_cents' => 15000,
        ]);

        $response->assertNotFound();
    }

    public function test_update_returns_422_for_invalid_payload(): void
    {
        $course = Course::factory()->create();

        $response = $this->putJson("/api/v1/courses/{$course->id}", [
            'name' => null,
            'description' => null,
            'price_cents' => 'not-an-integer',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'description', 'price_cents']);
    }

    public function test_destroy_soft_deletes_course_and_returns_204(): void
    {
        $course = Course::factory()->create();

        $response = $this->deleteJson("/api/v1/courses/{$course->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    }

    public function test_destroy_returns_404_when_course_does_not_exist(): void
    {
        $response = $this->deleteJson('/api/v1/courses/999999');

        $response->assertNotFound();
    }

    public function test_soft_deleted_courses_are_not_returned_in_index(): void
    {
        $course = Course::factory()->create([
            'name' => 'To Be Deleted',
        ]);

        $this->deleteJson("/api/v1/courses/{$course->id}")
            ->assertNoContent();

        $this->getJson('/api/v1/courses')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_cors_allows_localhost_8080_origin_for_api_requests(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:8080',
        ])->getJson('/api/v1/courses');

        $response
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:8080');
    }

    public function test_preflight_response_sends_access_control_max_age_for_two_hours(): void
    {
        $response = $this->call(
            'OPTIONS',
            '/api/v1/courses',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN' => 'http://localhost:8080',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'PUT',
                'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type',
            ]
        );

        $response
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:8080')
            ->assertHeader('Access-Control-Max-Age', '7200');
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CourseBulkOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_bulk_delete_returns_204_when_all_ids_are_found(): void
    {
        $firstCourse = Course::factory()->create();
        $secondCourse = Course::factory()->create();

        $response = $this->deleteJson("/api/v1/courses/bulk-delete?ids={$firstCourse->id},{$secondCourse->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('courses', ['id' => $firstCourse->id]);
        $this->assertSoftDeleted('courses', ['id' => $secondCourse->id]);
    }

    public function test_bulk_delete_returns_200_with_not_found_ids_when_partially_successful(): void
    {
        $course = Course::factory()->create();

        $response = $this->deleteJson("/api/v1/courses/bulk-delete?ids={$course->id},999999");

        $response
            ->assertOk()
            ->assertJsonPath('deleted_ids.0', $course->id)
            ->assertJsonPath('not_found_ids.0', 999999);

        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    }

    public function test_bulk_delete_returns_404_when_no_ids_are_found(): void
    {
        $response = $this->deleteJson('/api/v1/courses/bulk-delete?ids=100001,100002');

        $response
            ->assertNotFound()
            ->assertJsonPath('not_found_ids.0', 100001)
            ->assertJsonPath('not_found_ids.1', 100002);
    }

    public function test_bulk_delete_accepts_array_query_format(): void
    {
        $firstCourse = Course::factory()->create();
        $secondCourse = Course::factory()->create();

        $response = $this->deleteJson(
            "/api/v1/courses/bulk-delete?ids[]={$firstCourse->id}&ids[]={$secondCourse->id}"
        );

        $response->assertNoContent();
    }

    public function test_bulk_delete_returns_422_when_ids_are_missing(): void
    {
        $response = $this->deleteJson('/api/v1/courses/bulk-delete');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids']);
    }

    public function test_bulk_delete_returns_422_when_ids_are_invalid(): void
    {
        $response = $this->deleteJson('/api/v1/courses/bulk-delete?ids=1,abc,3');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids.1']);
    }

    public function test_bulk_delete_treats_soft_deleted_records_as_not_found(): void
    {
        $course = Course::factory()->create();
        $course->delete();

        $response = $this->deleteJson("/api/v1/courses/bulk-delete?ids={$course->id}");

        $response
            ->assertNotFound()
            ->assertJsonPath('not_found_ids.0', $course->id);
    }

    public function test_bulk_update_returns_200_when_all_ids_are_found(): void
    {
        $firstCourse = Course::factory()->create();
        $secondCourse = Course::factory()->create();

        $response = $this->putJson(
            "/api/v1/courses/bulk-update?ids={$firstCourse->id},{$secondCourse->id}",
            ['price_cents' => 7777]
        );

        $response
            ->assertOk()
            ->assertJsonPath('updated_ids.0', $firstCourse->id)
            ->assertJsonPath('updated_ids.1', $secondCourse->id);

        $this->assertDatabaseHas('courses', ['id' => $firstCourse->id, 'price_cents' => 7777]);
        $this->assertDatabaseHas('courses', ['id' => $secondCourse->id, 'price_cents' => 7777]);
    }

    public function test_bulk_update_returns_200_with_not_found_ids_when_partially_successful(): void
    {
        $course = Course::factory()->create([
            'name' => 'Before Update',
        ]);

        $response = $this->putJson(
            "/api/v1/courses/bulk-update?ids={$course->id},999999",
            ['name' => 'After Update']
        );

        $response
            ->assertOk()
            ->assertJsonPath('updated_ids.0', $course->id)
            ->assertJsonPath('not_found_ids.0', 999999);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'After Update',
        ]);
    }

    public function test_bulk_update_returns_404_when_no_ids_are_found(): void
    {
        $response = $this->putJson('/api/v1/courses/bulk-update?ids=100001,100002', [
            'name' => 'Will Not Update',
        ]);

        $response
            ->assertNotFound()
            ->assertJsonPath('not_found_ids.0', 100001)
            ->assertJsonPath('not_found_ids.1', 100002);
    }

    public function test_bulk_update_returns_422_when_ids_are_missing(): void
    {
        $response = $this->putJson('/api/v1/courses/bulk-update', [
            'name' => 'Missing IDs',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids']);
    }

    public function test_bulk_update_returns_422_when_no_attributes_are_provided(): void
    {
        $course = Course::factory()->create();

        $response = $this->putJson("/api/v1/courses/bulk-update?ids={$course->id}", []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['attributes']);
    }

    public function test_bulk_update_returns_422_for_invalid_attribute_values(): void
    {
        $course = Course::factory()->create();

        $response = $this->putJson("/api/v1/courses/bulk-update?ids={$course->id}", [
            'price_cents' => -100,
            'name' => '',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price_cents', 'name']);
    }

    public function test_bulk_update_treats_soft_deleted_records_as_not_found(): void
    {
        $course = Course::factory()->create();
        $course->delete();

        $response = $this->putJson("/api/v1/courses/bulk-update?ids={$course->id}", [
            'name' => 'Renamed',
        ]);

        $response
            ->assertNotFound()
            ->assertJsonPath('not_found_ids.0', $course->id);
    }
}

<?php

namespace Tests\Unit\Services;

use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CourseServiceTest extends TestCase
{
    use RefreshDatabase;

    private CourseService $courseService;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->courseService = app(CourseService::class);
    }

    public function test_list_courses_returns_all_records_in_id_order(): void
    {
        $firstCourse = Course::factory()->create();
        $secondCourse = Course::factory()->create();

        $courses = $this->courseService->listCourses();

        $this->assertCount(2, $courses);
        $this->assertSame($firstCourse->id, $courses[0]->id);
        $this->assertSame($secondCourse->id, $courses[1]->id);
    }

    public function test_list_courses_filters_by_name_and_description(): void
    {
        $nameMatch = Course::factory()->create([
            'name' => 'Unit Testing in PHP',
            'description' => 'Testing service classes',
        ]);

        $descriptionMatch = Course::factory()->create([
            'name' => 'Docker Intro',
            'description' => 'Run phpunit test suites reliably',
        ]);

        Course::factory()->create([
            'name' => 'React Basics',
            'description' => 'Frontend fundamentals',
        ]);

        $courses = $this->courseService->listCourses('test');

        $this->assertCount(2, $courses);
        $this->assertSame([$nameMatch->id, $descriptionMatch->id], $courses->pluck('id')->all());
    }

    public function test_list_courses_uses_cache_for_the_same_search_term(): void
    {
        $course = Course::factory()->create([
            'name' => 'Caching Strategy',
        ]);

        $initial = $this->courseService->listCourses('caching');
        $this->assertCount(1, $initial);

        DB::table('courses')->where('id', $course->id)->update([
            'name' => 'Renamed',
        ]);

        $cached = $this->courseService->listCourses('caching');
        $this->assertCount(1, $cached);
        $this->assertSame('Caching Strategy', $cached->first()->name);
    }

    public function test_invalidate_cache_refreshes_cached_index_results(): void
    {
        $course = Course::factory()->create([
            'name' => 'Cache Invalidation',
        ]);

        $this->assertCount(1, $this->courseService->listCourses('cache'));

        DB::table('courses')->where('id', $course->id)->update([
            'name' => 'Renamed',
        ]);

        $this->courseService->invalidateCache();

        $this->assertCount(0, $this->courseService->listCourses('cache'));
    }

    public function test_get_course_by_id_returns_existing_course(): void
    {
        $course = Course::factory()->create();

        $resolved = $this->courseService->getCourseById($course->id);

        $this->assertNotNull($resolved);
        $this->assertSame($course->id, $resolved->id);
    }

    public function test_get_course_by_id_returns_null_for_missing_course(): void
    {
        $resolved = $this->courseService->getCourseById(999999);

        $this->assertNull($resolved);
    }

    public function test_get_course_by_id_uses_cache_for_the_same_course_id(): void
    {
        $course = Course::factory()->create([
            'name' => 'Original Title',
        ]);

        $this->assertSame('Original Title', $this->courseService->getCourseById($course->id)?->name);

        DB::table('courses')->where('id', $course->id)->update([
            'name' => 'New Title',
        ]);

        $this->assertSame('Original Title', $this->courseService->getCourseById($course->id)?->name);
    }

    public function test_invalidate_cache_refreshes_cached_show_results(): void
    {
        $course = Course::factory()->create([
            'name' => 'Before',
        ]);

        $this->assertSame('Before', $this->courseService->getCourseById($course->id)?->name);

        DB::table('courses')->where('id', $course->id)->update([
            'name' => 'After',
        ]);

        $this->courseService->invalidateCache();

        $this->assertSame('After', $this->courseService->getCourseById($course->id)?->name);
    }

    public function test_create_course_persists_record(): void
    {
        $course = $this->courseService->createCourse([
            'name' => 'Created By Service',
            'description' => 'A service level create test',
            'price_cents' => 12345,
        ]);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Created By Service',
            'description' => 'A service level create test',
            'price_cents' => 12345,
        ]);
    }

    public function test_update_course_updates_persisted_values(): void
    {
        $course = Course::factory()->create([
            'name' => 'Before Update',
            'description' => 'Before description',
            'price_cents' => 5000,
        ]);

        $updated = $this->courseService->updateCourse($course, [
            'name' => 'After Update',
            'description' => 'After description',
            'price_cents' => 7500,
        ]);

        $this->assertSame('After Update', $updated->name);
        $this->assertSame('After description', $updated->description);
        $this->assertSame(7500, $updated->price_cents);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'After Update',
            'description' => 'After description',
            'price_cents' => 7500,
        ]);
    }

    public function test_delete_course_soft_deletes_record(): void
    {
        $course = Course::factory()->create();

        $this->courseService->deleteCourse($course);

        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    }

    public function test_bulk_delete_by_ids_soft_deletes_found_records_and_returns_missing_ids(): void
    {
        $firstCourse = Course::factory()->create();
        $secondCourse = Course::factory()->create();

        $result = $this->courseService->bulkDeleteByIds([
            $firstCourse->id,
            999999,
            $secondCourse->id,
        ]);

        $this->assertSame(2, $result['deleted_count']);
        $this->assertSame([$firstCourse->id, $secondCourse->id], $result['deleted_ids']);
        $this->assertSame([999999], $result['not_found_ids']);

        $this->assertSoftDeleted('courses', ['id' => $firstCourse->id]);
        $this->assertSoftDeleted('courses', ['id' => $secondCourse->id]);
    }

    public function test_bulk_delete_by_ids_returns_zero_when_no_ids_are_found(): void
    {
        $result = $this->courseService->bulkDeleteByIds([101010, 202020]);

        $this->assertSame(0, $result['deleted_count']);
        $this->assertSame([], $result['deleted_ids']);
        $this->assertSame([101010, 202020], $result['not_found_ids']);
    }

    public function test_bulk_update_by_ids_updates_found_records_and_returns_missing_ids(): void
    {
        $firstCourse = Course::factory()->create([
            'price_cents' => 1000,
        ]);
        $secondCourse = Course::factory()->create([
            'price_cents' => 2000,
        ]);

        $result = $this->courseService->bulkUpdateByIds(
            [$firstCourse->id, 303030, $secondCourse->id],
            ['price_cents' => 9000]
        );

        $this->assertSame(2, $result['updated_count']);
        $this->assertSame([$firstCourse->id, $secondCourse->id], $result['updated_ids']);
        $this->assertSame([303030], $result['not_found_ids']);

        $this->assertDatabaseHas('courses', ['id' => $firstCourse->id, 'price_cents' => 9000]);
        $this->assertDatabaseHas('courses', ['id' => $secondCourse->id, 'price_cents' => 9000]);
    }

    public function test_bulk_update_by_ids_returns_zero_when_no_ids_are_found(): void
    {
        $result = $this->courseService->bulkUpdateByIds([101010, 202020], ['price_cents' => 9999]);

        $this->assertSame(0, $result['updated_count']);
        $this->assertSame([], $result['updated_ids']);
        $this->assertSame([101010, 202020], $result['not_found_ids']);
    }
}

<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CourseService
{
    private const CACHE_TTL_SECONDS = 7200;

    private const CACHE_VERSION_KEY = 'courses:cache:version';

    public function listCourses(?string $search = null): Collection
    {
        $searchTerm = trim((string) $search);
        $cacheKey = $this->indexCacheKey($searchTerm);

        /** @var Collection<int, Course> $courses */
        $courses = Cache::remember(
            $cacheKey,
            now()->addSeconds(self::CACHE_TTL_SECONDS),
            fn () => $this->baseQuery($searchTerm)->get()
        );

        return $courses;
    }

    public function getCourseById(int $courseId): ?Course
    {
        $cacheKey = $this->showCacheKey($courseId);

        /** @var Course|null $course */
        $course = Cache::remember(
            $cacheKey,
            now()->addSeconds(self::CACHE_TTL_SECONDS),
            fn () => Course::query()->find($courseId)
        );

        return $course;
    }

    /**
     * @param  array{name: string, description: string, price_cents: int}  $payload
     */
    public function createCourse(array $payload): Course
    {
        return Course::query()->create($payload);
    }

    /**
     * @param  array{name: string, description: string, price_cents: int}  $payload
     */
    public function updateCourse(Course $course, array $payload): Course
    {
        $course->update($payload);

        return $course->refresh();
    }

    public function deleteCourse(Course $course): void
    {
        $course->delete();
    }

    /**
     * @param  array<int, int>  $ids
     * @return array{
     *     deleted_count: int,
     *     deleted_ids: array<int, int>,
     *     not_found_ids: array<int, int>
     * }
     */
    public function bulkDeleteByIds(array $ids): array
    {
        $normalizedIds = $this->normalizeIds($ids);
        $coursesById = Course::query()
            ->whereIn('id', $normalizedIds)
            ->get()
            ->keyBy('id');

        $deletedIds = [];
        $notFoundIds = [];

        foreach ($normalizedIds as $id) {
            /** @var Course|null $course */
            $course = $coursesById->get($id);

            if ($course === null) {
                $notFoundIds[] = $id;

                continue;
            }

            $course->delete();
            $deletedIds[] = $id;
        }

        return [
            'deleted_count' => count($deletedIds),
            'deleted_ids' => $deletedIds,
            'not_found_ids' => $notFoundIds,
        ];
    }

    /**
     * @param  array<int, int>  $ids
     * @param  array<string, mixed>  $attributes
     * @return array{
     *     updated_count: int,
     *     updated_ids: array<int, int>,
     *     not_found_ids: array<int, int>
     * }
     */
    public function bulkUpdateByIds(array $ids, array $attributes): array
    {
        $normalizedIds = $this->normalizeIds($ids);
        $coursesById = Course::query()
            ->whereIn('id', $normalizedIds)
            ->get()
            ->keyBy('id');

        $updatedIds = [];
        $notFoundIds = [];

        foreach ($normalizedIds as $id) {
            /** @var Course|null $course */
            $course = $coursesById->get($id);

            if ($course === null) {
                $notFoundIds[] = $id;

                continue;
            }

            $course->update($attributes);
            $updatedIds[] = $id;
        }

        return [
            'updated_count' => count($updatedIds),
            'updated_ids' => $updatedIds,
            'not_found_ids' => $notFoundIds,
        ];
    }

    public function invalidateCache(): void
    {
        $nextVersion = $this->cacheVersion() + 1;
        Cache::forever(self::CACHE_VERSION_KEY, $nextVersion);
    }

    private function cacheVersion(): int
    {
        /** @var int $version */
        $version = Cache::rememberForever(self::CACHE_VERSION_KEY, fn (): int => 1);

        return $version;
    }

    private function indexCacheKey(string $search): string
    {
        return sprintf(
            'courses:index:v%d:%s',
            $this->cacheVersion(),
            sha1(Str::lower($search))
        );
    }

    private function showCacheKey(int $courseId): string
    {
        return sprintf(
            'courses:show:v%d:%d',
            $this->cacheVersion(),
            $courseId
        );
    }

    private function baseQuery(string $search): Builder
    {
        return Course::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('id');
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    private function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_map('intval', $ids)));
    }
}

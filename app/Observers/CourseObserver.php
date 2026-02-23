<?php

namespace App\Observers;

use App\Models\Course;
use App\Services\CourseService;

class CourseObserver
{
    public function created(Course $course): void
    {
        $this->invalidateCourseCache();
    }

    public function updated(Course $course): void
    {
        $this->invalidateCourseCache();
    }

    public function deleted(Course $course): void
    {
        $this->invalidateCourseCache();
    }

    public function restored(Course $course): void
    {
        $this->invalidateCourseCache();
    }

    public function forceDeleted(Course $course): void
    {
        $this->invalidateCourseCache();
    }

    private function invalidateCourseCache(): void
    {
        app(CourseService::class)->invalidateCache();
    }
}

<?php

use App\Http\Controllers\Api\V1\CourseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::delete('courses/bulk-delete', [CourseController::class, 'bulkDelete'])
        ->name('courses.bulk-delete');

    Route::put('courses/bulk-update', [CourseController::class, 'bulkUpdate'])
        ->name('courses.bulk-update');

    Route::apiResource('courses', CourseController::class)
        ->where(['course' => '[0-9]+']);
});

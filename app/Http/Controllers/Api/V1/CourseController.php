<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BulkDeleteCoursesRequest;
use App\Http\Requests\Api\V1\BulkUpdateCoursesRequest;
use App\Http\Requests\Api\V1\StoreCourseRequest;
use App\Http\Requests\Api\V1\UpdateCourseRequest;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class CourseController extends Controller
{
    public function __construct(private readonly CourseService $courseService) {}

    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $courses = $this->courseService->listCourses(is_string($search) ? $search : null);

        return response()->json($courses);
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->createCourse($request->validated());

        return response()->json($course, 201);
    }

    public function show(Course $course): JsonResponse
    {
        $course = $this->courseService->getCourseById((int) $course->id);

        abort_if($course === null, 404);

        return response()->json($course);
    }

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        return response()->json(
            $this->courseService->updateCourse($course, $request->validated())
        );
    }

    public function destroy(Course $course): Response
    {
        $this->courseService->deleteCourse($course);

        return response()->noContent();
    }

    public function bulkDelete(BulkDeleteCoursesRequest $request): JsonResponse|Response
    {
        /** @var array<int, int> $ids */
        $ids = $request->validated('ids');
        $result = $this->courseService->bulkDeleteByIds($ids);

        if ($result['deleted_count'] === 0) {
            return response()->json([
                'message' => 'No courses found for the provided IDs.',
                'not_found_ids' => $result['not_found_ids'],
            ], 404);
        }

        if ($result['not_found_ids'] === []) {
            return response()->noContent();
        }

        return response()->json([
            'deleted_ids' => $result['deleted_ids'],
            'not_found_ids' => $result['not_found_ids'],
        ]);
    }

    public function bulkUpdate(BulkUpdateCoursesRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var array<int, int> $ids */
        $ids = $validated['ids'];
        $attributes = Arr::only($validated, ['name', 'description', 'price_cents']);
        $result = $this->courseService->bulkUpdateByIds($ids, $attributes);

        if ($result['updated_count'] === 0) {
            return response()->json([
                'message' => 'No courses found for the provided IDs.',
                'not_found_ids' => $result['not_found_ids'],
            ], 404);
        }

        $responsePayload = [
            'updated_ids' => $result['updated_ids'],
        ];

        if ($result['not_found_ids'] !== []) {
            $responsePayload['not_found_ids'] = $result['not_found_ids'];
        }

        return response()->json($responsePayload);
    }
}

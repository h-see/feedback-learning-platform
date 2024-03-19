<?php

namespace App\Course\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Course\Models\Course;
use App\Course\Models\CourseMember;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Date;

class CoursesController extends Controller
{
    /** Get all courses created by the currently authenticated user
     *
     * @return \Illuminate\Http\Response
     */
    public function getCreatedCourses() {
        $user = auth()->user();
        $courses = Course::where('creator_id', $user->id)
            ->orderByDesc('start_date')
            ->get()
            ->map(function ($course) {
                $course->end_date = DateHelper::formatDate($course->end_date);
                $course->start_date = DateHelper::formatDate($course->start_date);
                return $course;
            });
        return response()->json([ 'courses' => $courses]);
    }

    /** Get all courses where the currently authenticated user is a member
     *
     * @return \Illuminate\Http\Response
     */
    public function getCourses() {
        $user = auth()->user();

        $courses = CourseMember::where('user_id', $user->id)
            ->with('course', 'course.counsellingSetups')
            ->get()
            ->each(function ($courseMember) {
                $courseMember->course->userIsTrainer = $courseMember->role_id === 3;
                $courseMember->course->userIsEnabled = $courseMember->enabled !== 0;
            })
            ->pluck('course');
        return response()->json($courses);
    }

    /** Returns the view for course enrollment
     */
    public function enrollmentView() {
        $today = Date::now();
        $courses = Course::whereDate('end_date', '>=', $today)
                         ->get(); // Retrieve courses within the date range

        return view('student.enrollment', compact('courses'));
    }

    public function trainerEnrollmentView()
    {
        $this->authorize('isTeacher');
        $today = Date::now();
        $courses = Course::whereDate('end_date', '>=', $today)
            ->get(); // Retrieve courses within the date range

        return view('teacher.trainer-enrollment', compact('courses'));
    }
}

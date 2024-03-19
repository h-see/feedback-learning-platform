<?php

namespace App\Policies;

use Carbon\Carbon;
use App\Models\User;
use App\Course\Models\Course;

class CoursePolicy
{
    /** checks if current user is member in the given course
     *
     * @param  \App\Models\User  $user
     * @param  \App\Course\Models\Course  $course
     * @return bool
     */
    public function userIsCourseMember(User $user, Course $course) {
        return $user->courseMembers->contains('course_id', $course->id) && $user->courseMembers->contains('enabled', 1);
    }

    /** checks if current user is creator of the given course
     *
     * @param  \App\Models\User  $user
     * @param  \App\Course\Models\Course  $course
     * @return bool
     */
    public function userIsCourseCreator(User $user, Course $course) {
        return $user->id === $course->creator_id;
    }

    /** checks if current user is editingteacher in the given course
     *
     * @param  \App\Models\User  $user
     * @param  \App\Course\Models\Course  $course
     * @return bool
     */
    public function userIsEditingTeacher(User $user, Course $course) {
        if (!$this->userIsCourseMember($user, $course)) {
            return false;
        }

        $courseMember = $user->courseMembers->where('course_id', $course->id)->first();
        return $courseMember && $courseMember->role->title === 'editingteacher';
    }

    /** checks if course is editable
     *
     * @param  \App\Models\User  $user
     * @param  \App\Course\Models\Course  $course
     * @return bool
     */
    public function courseIsEditable(User $user, Course $course) {
        $end_date = Carbon::createFromFormat('Y-m-d H:i:s', $course->end_date);
        return now()->startOfDay()->lt($end_date->startOfDay());
    }
}

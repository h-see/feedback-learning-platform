<?php

namespace App\Course\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Course\Models\Course;
use App\Course\Models\CourseMember;
use App\Models\User;

use App\Helpers\RoleHelper;


class CourseMemberController extends Controller
{
    /** Retrieves all course members for given course
     *
     * @param  \App\Course\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function index(Course $course) {
        $this->authorize('userIsEditingTeacher', $course);

        $course_id = $course->id;
        $course = Course::find($course_id);
        if (!$course) {
            return response()->json(['message' => 'Kurs nicht gefunden'], 404);
        }

        $courseMembers = CourseMember::join('users', 'course_members.user_id', '=', 'users.id')
            ->where('course_members.course_id', $course_id)
            ->select('course_members.pseudo_first_name', 'course_members.pseudo_last_name', 'course_members.role_id', 'users.name', 'users.email', 'course_members.id', 'enabled')
            ->orderBy('name', 'asc')
            ->get();
        return response()->json($courseMembers);
    }


    /** Edit course member: update CourseMember Role
     *
     * @param  \App\Course\Models\CourseMember  $courseMember
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CourseMember $courseMember) {
        $course = Course::find($courseMember->course_id);
        $this->authorize('userIsEditingTeacher', $course);
        $this->authorize('courseIsEditable', $course);

        if($request->type === 'changeRole'){
            $this->validate($request, [
                'role_id'     => 'required|exists:roles,id',
            ]);

            $new_role_id = $request->input('role_id');

            //ensures that there's always at least one editingteacher
            if ($courseMember->role->title === 'editingteacher' && RoleHelper::getRoleById($new_role_id) !== 'editingteacher' && $this->onlyOneEditingTeacher($courseMember->course_id)) {
                return response()->json(['message' => 'Mindestens ein*e Kurstrainer*in mit Bearbeitungsrecht notwendig.'], 400);
            }

            // ensures that the user does not have a role in this course higher than his main role
            if ($new_role_id < User::find($courseMember->user_id)->main_role_id) {
                return response()->json(['message' => 'User hat nicht die entsprechenden Rechte für diese Rolle. An Administrator wenden.', 'current_role' => $courseMember->role_id], 400);
            }

            // update the courseMember's role
            $updateResult = $courseMember->update(['role_id' => $new_role_id]);
        }

        else if($request->type === 'acceptUser'){
            $updateResult = $courseMember->update(['enabled' => 1]);
        }

        if ($updateResult) {
            return response()->json(['message' => 'Kursteilnehmer*in hinzugefügt', 'courseMember' => $courseMember], 200);
        } else {
            return response()->json(['message' => 'Fehler beim Aktualisieren'], 500);
        }



    }

    /** Check if there is only one editingteacher in the given course
     *
     * @return bool
     */
    private function onlyOneEditingTeacher($courseId) {
        $teacherCount = CourseMember::where('course_id', $courseId)
            ->whereHas('role', function ($query) {
                $query->where('title', 'editingteacher');
            })->count();

        return $teacherCount == 1;
    }

    /** Deletes given user from course
     *
     * @param  \App\Course\Models\CourseMember  $courseMember
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */

    public function delete(CourseMember $courseMember) {
        $this->authorize('userIsEditingTeacher', Course::find($courseMember->course_id));

        $result = $this->deleteCourseMember($courseMember->course_id, $courseMember->user_id);
        if ($result === true) {
            return response()->json(['message' => 'Kursmitglied gelöscht'], 200);
        } else {
            return $result;
        }
    }


    /** Deletes current user from course
     *
     * @param  \App\Course\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function leave(Course $course) {
        $result = $this->deleteCourseMember($course->id, auth()->user()->id);
        if ($result === true) {
            return response()->json(['message' => 'Kurs verlassen'], 200);
        } else {
            return $result;
        }
    }

    /** Deletes course member by userId
     *
     * @param  int  $courseId
     * @param  int  $userId
     */
    private function deleteCourseMember($courseId, $userId) {
        $courseMember = CourseMember::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        //ensure that there's always at least one editingteacher
        if ($courseMember->role->title === 'editingteacher' && $this->onlyOneEditingTeacher($courseId)) {
            return response()->json(['message' => 'Mindestens ein*e Kurstrainer*in mit Bearbeitungsrecht notwendig.'], 400);
        }

        if (!$courseMember) {
            return response()->json(['message' => 'Kursmitglied nicht gefunden'], 404);
        }
        $courseMember->delete();
        return true;
    }

    /** Adds current user as a course member
     */
    public function store(Request $request) {
        $customValidationRules = [
            'coursename' => [
                'required',
                'string'
            ],
            'key' => [
                'required',
                'string'
            ],
            'pseudoFirstName' => [
                'required',
                'string'
            ],
            'pseudoLastName' => [
                'required',
                'string'
            ],
        ];
        $validator = Validator::make($request->all(), $customValidationRules);
        if ($validator->fails()) {
            return redirect()->route('courses.enrollment')
                ->withErrors($validator)
                ->withInput();
        }

        $coursename = $request->coursename;
        $course = Course::where('name', $coursename)->first();

        // check if enrollment key is correct
        if (!$course || $course->enrollment_key !== $request->key) {
            $validator->getMessageBag()->add('coursename', 'Kursname oder Einschreibeschlüssel falsch.');
            $validator->getMessageBag()->add('key', 'Kursname oder Einschreibeschlüssel falsch.');
            return redirect()->route('courses.enrollment')
                ->withErrors($validator)
                ->withInput();
        }
        // check if course is still open
        $end_date = Carbon::createFromFormat('Y-m-d H:i:s', $course->end_date);
        if ($end_date->startOfDay()->lt(now()->startOfDay())) {
            $validator->getMessageBag()->add('coursename', 'Kurs ist bereits zu Ende.');
            return redirect()->route('courses.enrollment')
                ->withErrors($validator)
                ->withInput();
        }

        $userId = auth()->user()->id;

        $courseSettings = json_decode($course->settings);

        $courseMember = CourseMember::firstOrCreate([
            'user_id' => $userId,
            'course_id' => $course->id,
        ]);

        if ($courseMember->wasRecentlyCreated) { // new courseMember created
            $courseMember->pseudo_first_name = $request->pseudoFirstName;
            $courseMember->pseudo_last_name = $request->pseudoLastName;
            $courseMember->properties = '{"feedback_requests_made": "0", "peer_review_token": "' . $courseSettings->peer_review_start_token . '"}';

            $courseMember->save();
            $start_date = Carbon::createFromFormat('Y-m-d H:i:s', $course->start_date);
            if($start_date->startOfDay()->gt(now()->startOfDay())){
                return redirect()->route('home')->with('success', 'Du wurdest erfolgreich für den Kurs eingeschrieben, aber der Kurs hat noch nicht begonnen.');
            }
            else{
                return redirect()->route('course.index', $course->id)->with('success', 'Du wurdest erfolgreich für den Kurs eingeschrieben.');
            }
        }
        else if(!$courseMember->wasRecentlyCreated && $courseMember->enabled === 0){
            return redirect()->route('home')->with('error', 'Einschreibung als Student*in nicht möglich. Du hast bereits eine bestehende ungenehmigte Anfrage als Trainer*in in diesem Kurs.');
        }
        else { // user is already courseMember
            return redirect()->route('course.index', $course->id)->with('error', 'Du bist bereits Mitglied in diesem Kurs.');
        }
    }

    public function storeTrainerRequest(Request $request){
        $customValidationRules = [
            'coursename' => [
                'required',
                'string'
            ],
            'key' => [
                'required',
                'string'
            ],
        ];

        $validator = Validator::make($request->all(), $customValidationRules);
        if ($validator->fails()) {
            return redirect()->route('courses.trainerEnrollment')
                ->withErrors($validator)
                ->withInput();
        }

        $userId = auth()->user()->id;
        $coursename = $request->coursename;
        $course = Course::where('name', $coursename)->first();

        // check if enrollment key is correct
        if (!$course || $course->enrollment_key !== $request->key) {
            $validator->getMessageBag()->add('coursename', 'Kursname oder Einschreibeschlüssel falsch.');
            $validator->getMessageBag()->add('key', 'Kursname oder Einschreibeschlüssel falsch.');
            return redirect()->route('courses.trainerEnrollment')
                ->withErrors($validator)
                ->withInput();
        }

        $courseMember = CourseMember::firstOrCreate([
            'user_id' => $userId,
            'course_id' => $course->id,
            'enabled' => 0,
            'role_id' => 3
        ]);

        if ($courseMember->wasRecentlyCreated) { // new courseMember created
            $courseMember->save();

            return redirect()->route('home')->with('success', 'Deine Anfrage wurde erfolgreich abgeschickt. Sobald diese durch den*die Kursleiter*in akzeptiert wurde, kannst du ihn öffnen.');
        }

        else if(!$courseMember->wasRecentlyCreated && $courseMember->enabled === 0){
            return redirect()->route('home')->with('error', 'Der*Die Kursleiter*in hat deine Anfrage noch nicht genehmigt.');
        }

        else{
            return redirect()->route('course.index', $course->id)->with('error', 'Du bist bereits Mitglied in diesem Kurs.');
        }

    }

    public function addTeacherFromCourseSettings(Course $course, Request $request){

        $user = User::where('email', '=', $request->email)->first();

        if(!$user){
            return response()->json(['message' => 'Kein User mit dieser E-Mail Adresse.'], 404);
        }

        else if($user && $user->main_role_id === 4){
            return response()->json(['message' => 'User hat nicht die entsprechenden Rechte für diese Rolle. An Administrator wenden.'], 400);
        }

        else{
            $courseMember = CourseMember::firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enabled' => 1,
                'role_id' => 3
            ]);

            if ($courseMember->wasRecentlyCreated) {
                $courseMember->save();
                return response()->json(['message' => 'User erfolgreich hinzugefügt', 'courseMember' => $courseMember], 200);
            }

            else{
                return response()->json(['message' => 'User ist bereits Mitglied in diesem Kurs'], 400);
            }
        }
    }
}

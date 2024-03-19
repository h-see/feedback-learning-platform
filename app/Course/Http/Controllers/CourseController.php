<?php

namespace App\Course\Http\Controllers;

use App\Counselling\Models\CounsellingFeedback;
use App\Models\User;
use App\Persona\Models\CounsellingField;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Course\Models\Course;
use App\Course\Models\CourseMember;
use App\Counselling\Models\CounsellingSetup;
use App\Counselling\Models\Counselling;
use App\Models\Role;
use App\Persona\Models\Persona;
use App\Helpers\RoleHelper;
use App\Helpers\DateHelper;
use function Laravel\Prompts\error;

class CourseController extends Controller
{
    /** Displays the course overview for students, displays course settings for editingteacher
     * Depending on role of course-member
     *
     * @param  \App\Course\Models\Course  $course
     */
    public function index(Course $course)
    {
        $this->authorize('userIsCourseMember', $course);
        $this->storeCourseInSession($course->id, $course->name);

        $user = auth()->user();
        $courseMember = $user->courseMembers()->where('course_id', $course->id)->first();
        $data = $course->load('counsellingSetups');
        $data->end_date = DateHelper::formatDate($course->end_date);
        $data->start_date = DateHelper::formatDate($course->start_date);
        $data->counsellingSetups = $this->setTaskPersonaForFE($data->counsellingSetups);
        $data->pseudonym = $this->getPseudonymforCurrentUser($course->id);
        $data->requests_and_tokens = $this->getPeerReviewTokenForCurrentUser($course->id, $course);
        if ($courseMember->role->title === 'student') {
            return view('student.course',  ['course' => $data]);
        } else if ($courseMember->role->title === 'editingteacher' || $courseMember->role->title === 'teacher') {

            $courseMembers = CourseMember::where([['course_id', $course->id], ['role_id', 4]])->get();
            $course->student_count = $courseMembers->count();
            if ($courseMembers->isNotEmpty()) {
                $counsellings = Counselling::whereIn('course_member_id', function ($query) use ($course) {
                    $query->select('id')
                        ->from('course_members')
                        ->where([['course_id', $course->id], ['role_id', 4]]);
                })->get();
            }

            $openFeedbackRequests = CounsellingFeedback::whereHas('counselling', function ($query) use ($courseMembers) {
                $query->whereIn('course_member_id', $courseMembers->pluck('id'));
            })->where('feedback_source_id', 1)
                ->where(function ($query) {
                    $query->where('status_feedback_id', 1)
                        ->orWhere('status_feedback_id', 2);
                })
                ->count();

            $course->open_feedback_requests = $openFeedbackRequests;


            return view('teacher.course', ['course' => $data, 'user_id' => $user->id, 'course_members' => $courseMembers]);
        }
    }

    /** Returns information for given course
     *
     * @param  \App\Course\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function indexData(Course $course) {
        $this->authorize('userIsCourseMember', $course);

        $data = $course->load('counsellingSetups');
        $data->end_date = DateHelper::formatDate($course->end_date);
        $data->start_date = DateHelper::formatDate($course->start_date);

        return response()->json($data);
    }

    /** Displays the course creation view for teachers.
     */
    public function create() {
        return view('teacher.course-settings', ['course' => null]);
    }

    /** Handles the creation or editing of a course, including related counselling_setups
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        $this->validate($request, [
            'name'              => 'required',
            'enrollment_key'    => 'required',
            'start_date'        => 'required',
            'end_date'          => 'required',
            'trainer_feedback_contingent' => 'required|integer|min:1',
            'peer_review_start_token' => 'required|integer|min:1',
            'counselling_setups'=> 'required|array|min:1', // ensure that at least one setup exists
            'counselling_setups.*.settings.personae' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    // Check if there's at least one persona ID
                    if (empty($value)) {
                        $fail("Mindestens eine Persona für Übungen und jede Aufgabe notwendig.");
                    } else {
                        // Check if all persona IDs exist in the database and are enabled
                        $personaIds = collect($value)->unique();
                        $existingPersonas = Persona::whereIn('id', $personaIds)->where('enabled', true)->count();
                        if ($personaIds->count() !== $existingPersonas) {
                            $fail("Ausgewählte Persona nicht in der Datenbank oder deaktiviert.");
                        }
                    }
                },
            ],
        ]);

        if (!$request->input('counselling_setups') || !collect($request->input('counselling_setups'))->contains('mandatory', false)) {
            return response()->json(['message' => 'Beratungs-Setting für Übungen fehlt'], 400);
        }

        $data = $request->only(
            'name',
            'enrollment_key',
            'start_date',
            'end_date',
            'counselling_setups'
        );

        // settings field has default values, at the moment only 2 are relevant and editable
        $settingsArray =[
            'trainer_feedback_contingent' => strval($request->input('trainer_feedback_contingent')),
            'peer_review_start_token' => strval($request->input('peer_review_start_token')),
            'peer_review_available' => '1',
            'ai_feedback_available' => '1',
            'display_peer_review' => '0',
            'display_notes' => '0',
            'display_counsellings' => '0',
        ];

        $data['settings'] = json_encode($settingsArray);

        $course_id = $request->only('id');

        // Create new course
        if ($course_id == []) {
            $existingCourse = Course::where('name', $data['name'])->first();
            if ($existingCourse) {
                return response()->json(['message' => 'Ein Kurs mit diesem Namen existiert bereits.'], 400);
            }

            $data['creator_id'] = auth()->user()->id;

            $course = Course::create($data);

            $course_member = CourseMember::create([
                'course_id' => $course->id,
                'user_id' => auth()->user()->id,
                'role_id' => RoleHelper::getIdFromTitle('editingteacher'),
            ]);

            foreach ($data['counselling_setups'] as $setup_data) {
                $mandatory = $setup_data['mandatory'] ?? false;
                $dueDate = $setup_data['due_date'] ?? null;
                $settings = $setup_data['settings'];

                $counsellingSetup = CounsellingSetup::create([
                    'mandatory' => $mandatory,
                    'due_date' => $dueDate,
                    'course_id' => $course->id,
                    'settings' => $settings
                ]);
            }

            return response()->json(['message' => 'Kurs erfolgreich erstellt.', 'id' => $course->id], 200);
        }
        // edit existing course
        else {
            $course = Course::find($course_id)->first();
            $this->authorize('userIsEditingTeacher', $course);
            $this->authorize('courseIsEditable', $course);

            // edit base data
            $course->name = $data['name'];
            $course->start_date = $data['start_date'];
            $course->end_date = $data['end_date'];
            $course->enrollment_key = $data['enrollment_key'];
            $course->settings = $data['settings'];
            $course->save();

            // edit counselling setups: overwrite existing one or create one
            foreach ($data['counselling_setups'] as $setup_data) {
                $mandatory = $setup_data['mandatory'] ?? false;
                $dueDate = $setup_data['due_date'] ?? null;
                $settings = $setup_data['settings'];

                $existingSetup = CounsellingSetup::find($setup_data['id']);

                if ($existingSetup) {
                    // update data
                    $existingSetup->update([
                        'mandatory' => $mandatory,
                        'due_date' => $dueDate,
                        'settings' => $settings,
                    ]);
                } else {
                    // create new setup
                    $newSetup = CounsellingSetup::create([
                        'mandatory' => $mandatory,
                        'due_date' => $dueDate,
                        'settings' => $settings,
                        'course_id' => $course->id,
                    ]);
                }
            }
            // delete counselling setups from the course which are not in request-data
            $ids = collect($data['counselling_setups'])->pluck('id')->all();
            DB::table('counselling_setups')
                ->where('course_id', $course->id)
                ->whereNotIn('id', $ids)
                ->delete();

            $course->load('counsellingSetups');
            return response()->json(['message' => 'Kurs aktualisiert.', 'course' => $course], 200);
        }

    }

    /**
     * Show the form for editing the specified resource.
     * @param \App\Course\Models\Course $course
     */


    public function edit(Course $course)
    {
        return view('teacher.course-settings', ['course_id' => $course->id]);
    }

    /** Deletes given course
     *
     * @param  \App\Course\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function delete(Course $course) {
        $this->authorize('userIsEditingTeacher', $course);

        if (!$course) {
            return response()->json(['message' => 'Kurs nicht gefunden'], 404);
        }
        $course->delete();
        return response()->json(['message' => 'Kurs gelöscht'], 200);
    }


    /** Retrieves a list of available roles from database
     *
     * @return \Illuminate\Http\Response
     */
    public function getRoles() {
        $roles = Role::where('title', '!=', 'admin')
            ->select('id', 'title')
            ->orderBy('id', 'asc')
            ->get();
        $roles->each(function ($role) {
            $role->text = RoleHelper::translateRole($role->id);
        });
        return response()->json($roles);
    }

    /** Displays the exercises view for students
     *
     * @param  \App\Course\Models\Course  $course
     */
    public function getExercisesView(Course $course) {
        $user = User::find(Auth::id());
        $userId = $user->id;
        $isTeacher = $user->main_role_id == 2 || $user->main_role_id == 3;

        if(!$isTeacher){
            $this->authorize('userIsCourseMember', $course);
            $exerciseSetup = $course->exerciseSetup->first();
        }
        $this->storeCourseInSession($course->id, $course->name);
        if(!$isTeacher){
            return view('student.exercises', ['id' => $exerciseSetup->id, 'end_date' => DateHelper::formatDate($course->end_date)]);
        }
        else{
            $data = $course->load('counsellingSetups');
            $courseMembers = CourseMember::where([['course_id', $course->id], ['role_id', 4]])->get();

            return view('teacher.exercises', ['course' => $data, 'user_id' => $userId, 'end_date' => DateHelper::formatDate($course->end_date), 'course_members' => $courseMembers]);
        }


    }

    /** Displays the tasks view for students
     *
     * @param  \App\Course\Models\Course  $course
     */
    public function getTasksView(Course $course) {
        $user = User::find(Auth::id());
        $userId = $user->id;
        $isTeacher = $user->main_role_id == 2 || $user->main_role_id == 3;
        if(!$isTeacher){
            $this->authorize('userIsCourseMember', $course);
            $tasksSetups = $course->tasksSetups;
            $tasksSetups = $this->setTaskPersonaForFE($tasksSetups);
        }

        $this->storeCourseInSession($course->id, $course->name);
        if(!$isTeacher){
            return view('student.tasks', ['setups' => $tasksSetups, 'end_date' => DateHelper::formatDate($course->end_date)]);
        }
        else{
            $data = $course->load('counsellingSetups');
            $courseMembers = CourseMember::where([['course_id', $course->id], ['role_id', 4]])->get();

            return view('teacher.tasks', ['course' => $data,'user_id' => $userId, 'end_date' => DateHelper::formatDate($course->end_date), 'course_members' => $courseMembers]);
        }
    }

    public function getTasksViewFeedbackOnly(Course $course){
        $user = User::find(Auth::id());
        $userId = $user->id;
        $data = $course->load('counsellingSetups');
        $courseMembers = CourseMember::where([['course_id', $course->id], ['role_id', 4]])->get();

        return view('teacher.tasks', ['course' => $data, 'user_id' => $userId, 'feedbacksOnly' => "true", 'end_date' => DateHelper::formatDate($course->end_date), 'course_members' => $courseMembers]);
    }

    public function getPeerReviewRequestsView(Course $course)
    {
        return view('student.peerReviews', ['course' => $course, 'end_date' => DateHelper::formatDate($course->end_date)]);
    }

    public function getPeerReviewRequests(Course $course)
    {
        $this->authorize('userIsCourseMember', $course);
        $userId = Auth::id();
        $courseId = $course->id;

        $peerReviewRequests = CounsellingFeedback::with([
                'counselling.courseMember' => function ($query) {
                    $query->select('id', 'pseudo_first_name', 'pseudo_last_name');
                },
                'counselling.persona' => function ($query) {
                    $query->select('id', 'name', 'counselling_field_id');
                },
                'counselling.persona.counsellingField' => function ($query) {
                    $query->select('id', 'name');
                },
            ])
            // get only reviewRequests for current course
            ->whereHas('counselling.counsellingSetup.course', function ($query) use ($courseId) {
                $query->where('id', $courseId);
            })
            ->where('feedback_source_id', 2) // peer
            // get only requests from other course members
            ->whereHas('counselling.courseMember.user', function ($query) use ($userId) {
                $query->where('id', '!=', $userId);
            })
            ->where(function ($query) use ($userId) {
                // get when in progress AND user_id == current user
                $query->where(function ($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId)->where('status_feedback_id',  2);
                })
                    // or when requested
                    ->orWhere(function ($innerSubQuery) {
                        $innerSubQuery->where('status_feedback_id', 1);
                    });
            })
            ->get();

        // cleanup possible duplicates
        if($peerReviewRequests){
            $filteredRequests = collect([]);
            foreach ($peerReviewRequests as $request) {
                $existingRequest = $filteredRequests
                    ->where('counselling_id', $request->counselling_id)
                    ->first();
                // add if there is no feedback_request for this counselling_id
                if (!$existingRequest) {
                    // check if user has completed already request for this counselling
                    $userHasAlreadyPeerReview = CounsellingFeedback::where('counselling_id', $request->counselling_id)
                        ->where('user_id', $userId)
                        ->where('status_feedback_id','!=', 2)->first();

                    if(!$userHasAlreadyPeerReview){
                        $filteredRequests->push($request);
                    }

                } else {
                    // if there is already a feedback_request for this counselling_id, check if there is one in progress
                    if ($request->status_feedback_id == 2) {
                        // replace existing with one in progress
                        $filteredRequests->where('counselling_id', $request->counselling_id)
                            ->where('status_feedback_id', '!=', 2)
                            ->first()
                            ->replace($request);
                    }
                }
            }
            $peerReviewRequests = $filteredRequests;
        }

        // flatten nested structure for easier usage in vue
        $flattenedPeerReviewRequests = $peerReviewRequests->map(function ($request) {
            return [
                'id' => $request->id,
                'created_at' => $request->created_at,
                'updated_at' => $request->updated_at,
                'feedback_text' => $request->feedback_text,
                'ai_feedback_properties' => $request->ai_feedback_properties,
                'counselling_id' => $request->counselling_id,
                'user_id' => $request->user_id,
                'status_feedback_id' => $request->status_feedback_id,
                'feedback_source_id' => $request->feedback_source_id,
                'pseudo_first_name' => $request->counselling->courseMember->pseudo_first_name,
                'pseudo_last_name' => $request->counselling->courseMember->pseudo_last_name,
                'persona_name' => $request->counselling->persona->name,
                'counselling_field' => $request->counselling->persona->counsellingField->name,
            ];
        });


        $this->storeCourseInSession($course->id, $course->name);
        return response()->json(['peerReviewRequests' => $flattenedPeerReviewRequests, 'end_date' => DateHelper::formatDate($course->end_date)], 200);
    }

    /** Retrieves the pseudonym for the current user in the course
     *
     * @param  \App\Course\Models\Course  $course
     * @return \Illuminate\Http\Response
     */

    public function getPseudonym(Course $course) {
        $this->authorize('userIsCourseMember', $course);

        $responseData = $this->getPseudonymforCurrentUser($course->id);
        if ($responseData) {
            return response()->json($responseData);
        } else {
            return response()->json(['message' => 'Kursmitglied nicht gefunden'], 404);
        }
    }

    /** Retrieves the statistics for the current user in the course
     *
     * @param  \App\Course\Models\Course  $course
     * @return \Illuminate\Http\Response
     */

     public function getStatistics(Course $course) {
        $this->authorize('userIsCourseMember', $course);

        $courseMember = CourseMember::where('course_id', $course->id)->where('user_id', auth()->user()->id)->first();
        if ($courseMember) {
            // load all data
            $personae = Persona::where('enabled', true)->get();

            $countAllData = Counselling::whereHas('counsellingSetup', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->select('persona_id', \DB::raw('COUNT(*) as countAll'))
                ->groupBy('persona_id')
                ->get();

            $countUserData = Counselling::where('course_member_id', $courseMember->id)
                ->whereHas('counsellingSetup', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->select('persona_id', \DB::raw('COUNT(*) as countUser'))
                ->groupBy('persona_id')
                ->get();

            $countCourseMembers = CourseMember::where('course_id', $course->id)
                ->where('role_id', RoleHelper::getIdFromTitle('student'))
                ->count();
            $statistics = [];

            // calculate statistic for each persona
            foreach ($personae as $persona) {
                $countAll = $countAllData->where('persona_id', $persona->id)->first()->countAll ?? 0;
                $countUser = $countUserData->where('persona_id', $persona->id)->first()->countUser ?? 0;

                $averageUsers = $countAll > 0 ? round($countAll / $countCourseMembers, 0) : 0;

                array_push($statistics, [
                    'id' => $persona->id,
                    'name' => $persona->name,
                    'counsellingField' => $persona->counsellingField->name,
                    'countAll' => $countAll,
                    'countUser' => $countUser,
                    'averageUser' => $averageUsers,
                ]);
            }

            return response()->json(['statistics' => $statistics, 'countCourseMembers' => $countCourseMembers], 200);
        } else {
            return response()->json(['message' => 'Kursmitglied nicht gefunden'], 404);
        }
    }

    public function getTeacherStatistics(Course $course) {
        $this->authorize('userIsCourseMember', $course);

        $courseMembers = CourseMember::where('course_id', $course->id)->get();
        if ($courseMembers) {
            // load all data
            $personae = Persona::where('enabled', true)->get();
            $countCourseMembers = CourseMember::where('course_id', $course->id)
                ->where('role_id', RoleHelper::getIdFromTitle('student'))
                ->count();

            $allTasks = $course->tasksSetups();
            $countAllTasksData = $allTasks
                ->select("id")
                ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(counselling_setups.settings, "$.personae[0]")) as persona_id')
                ->selectRaw(DB::raw($countCourseMembers . ' as countAll'))
                ->groupBy('persona_id', 'id')
                ->get();

            $countDoneTasksData = Counselling::whereHas('counsellingSetup', function ($query) use ($course) {
                    $query->where([['course_id', $course->id],['mandatory', 1]]);
                })
                ->select('persona_id', \DB::raw('COUNT(*) as countDone'))
                ->groupBy('persona_id')
                ->get();

            $countExercises = Counselling::whereHas('counsellingSetup', function ($query) use ($course) {
                $query->where([['course_id', $course->id],['mandatory', 0]]);
            })
                ->select('persona_id', \DB::raw('COUNT(*) as countDone'))
                ->groupBy('persona_id')
                ->get();

            $statistics = [];

            // calculate statistic for each persona
            foreach ($personae as $persona) {
                $countAllTasks = $countAllTasksData->where('persona_id', $persona->id)->first()->countAll ?? 0;
                $countDoneTasks = $countDoneTasksData->where('persona_id', $persona->id)->first()->countDone ?? 0;
                $countExcercises = $countExercises->where('persona_id', $persona->id)->first()->countDone ?? 0;

                $statistics[] = [
                    'id' => $persona->id,
                    'name' => $persona->name,
                    'counsellingField' => $persona->counsellingField->name,
                    'countAllTasks' => $countAllTasks,
                    'countDoneTasks' => $countDoneTasks,
                    'countExercises' => $countExcercises,
                ];
            }

            return response()->json(['statistics' => $statistics, 'countCourseMembers' => $countCourseMembers], 200);
        } else {
            return response()->json(['message' => 'Kursmitglied nicht gefunden'], 404);
        }
    }



    /** Formats task data for the front end
     */
    private function setTaskPersonaForFE($setups) {
        $setups->where('mandatory', 'true')->each(function ($task) {
            $task->due_date = DateHelper::formatDate($task->due_date);

            // set persona and counselling field for mandatory = true
            $personaIds = $task->settings['personae'];

            $personaeData = Persona::with('counsellingField:id,name')
                ->select('id', 'name', 'counselling_field_id')
                ->whereIn('id', $personaIds)
                ->get();

            $task->personae = $personaeData->map(function ($persona) {
                return [
                    'id' => $persona->id,
                    'name' => $persona->name,
                    'counselling_field' => $persona->counsellingField->name,
                ];
            });

            unset($task->settings);
        });
        return $setups;
    }

    /** Stores course information in the session
     */
    private function storeCourseInSession($courseId, $courseName) {
        session(['current_course' => [
            'id' => $courseId,
            'name' => $courseName,
        ]]);
    }

    /** Retrieves the pseudonym for the current user in the course if available.
     */
    private function getPseudonymforCurrentUser($courseId) {
        $courseMember = CourseMember::where('course_id', $courseId)
            ->where('user_id', Auth::id())
            ->first();

        if ($courseMember) {
            $pseudoFirstName = $courseMember->pseudo_first_name ?? 'Unbekannt';
            $pseudoLastName = $courseMember->pseudo_last_name ?? 'Unbekannt';

            return [
                'pseudo_first_name' => $pseudoFirstName,
                'pseudo_last_name' => $pseudoLastName,
            ];
        } else {
            return false;
        }
    }

    private function getPeerReviewTokenForCurrentUser($courseId, $course){
        $courseMember = CourseMember::where('course_id', $courseId)
            ->where('user_id', Auth::id())
            ->first();

        $courseSettings = json_decode($course->settings);

        if ($courseMember) {
            $properties = json_decode($courseMember->properties);
            $feedbackRequestsMade= $properties->feedback_requests_made ?? '';
            $feedbackRequestsAvailable = $feedbackRequestsMade != '' ? $courseSettings->trainer_feedback_contingent - $feedbackRequestsMade : '';
            $peerReviewToken = $properties->peer_review_token ?? '';

            return [
                'feedback_requests_made' => $feedbackRequestsMade,
                'feedback_requests_available' => $feedbackRequestsAvailable,
                'peer_review_token' => $peerReviewToken,
            ];
        } else {
            return false;
        }
    }

}

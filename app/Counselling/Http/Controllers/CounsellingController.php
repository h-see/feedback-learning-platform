<?php

namespace App\Counselling\Http\Controllers;

use App\Counselling\Models\CounsellingFeedback;
use App\Helpers\GenerateFeedbackHelper;
use App\Models\FeedbackSource;
use App\Models\FeedbackType;
use App\Models\Status;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Helpers\StatusHelper;
use App\Helpers\DateHelper;
use App\Helpers\GenerateMessageHelper;
use App\Counselling\Models\CounsellingSetup;
use App\Counselling\Models\Counselling;
use App\Counselling\Models\CounsellingMessage;
use App\Persona\Models\CounsellingField;
use App\Persona\Models\Persona;
use App\Course\Models\CourseMember;
use App\Course\Models\Course;

class CounsellingController extends Controller
{
    /** Show the counselling view based on the chat's status
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     */
    public function index(Counselling $counselling)
    {
        $user = User::find(Auth::id());
        $userId = $user->id;
        $isTeacher = $user->main_role_id == 2 || $user->main_role_id == 3;

        if(!$isTeacher){
          $this->authorize('userIsCounsellingCreator', $counselling);
        }

        $this->storeCourseInSession($counselling->counsellingSetup->course->id);
        $course = Course::find($counselling->counsellingSetup->course->id);
        $trainerFeedbackContingent = json_decode($course->settings,true)['trainer_feedback_contingent'];

        if(!$isTeacher){
            if (StatusHelper::getStatusById($counselling->status_chat_id) === 'in progress') {
                return view('student.chat', ['counsellingId' => $counselling->id, 'end_date' => DateHelper::formatDate($course->end_date)]);
            } else {
                return view('student.postprocessing', ['counsellingId' => $counselling->id, 'end_date' => DateHelper::formatDate($course->end_date), 'userIsCounsellingCreator' => true, 'trainerFeedbackContingent' => $trainerFeedbackContingent]);
            }
        }
        else{
            $counsellingRequest = $counselling->counsellingFeedbacks->where('feedback_source_id', 1)->first();
            $counsellingRequest ? $feedback_request_id = $counsellingRequest->id : $feedback_request_id = null;
            if ($counsellingRequest && $counsellingRequest->status_feedback_id == 1) {
                $counsellingRequest->update(['status_feedback_id' => 2, 'user_id' => $userId, 'accepted_at' => now()]);
            }

            return view('teacher.postprocessing', ['counsellingId' => $counselling->id, 'user_id' => $userId, 'counsellingFeedback' => $counsellingRequest, 'feedback_request_id' => $feedback_request_id, 'end_date' => DateHelper::formatDate($course->end_date), 'source' => 'teacher']);
        }
    }

    /** Get data for given counselling
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function indexData(Counselling $counselling) {
        $user = User::find(Auth::id());
        $userId = $user->id;
        $isTeacher = $user->main_role_id == 2 || $user->main_role_id == 3;

        if(!$isTeacher){
            $this->authorize('userIsCounsellingCreator', $counselling);
        }

        $counselling->load([
            'counsellingMessages',
            'counsellingFeedbacks' => function ($query) {
                $query->orderBy('updated_at', 'asc');
            }
        ]);

        if($counselling->counsellingFeedbacks){
            foreach($counselling->counsellingFeedbacks as $feedback){
                if($feedback->feedback_source_id == 1 && $feedback->user_id !== null){
                    $trainer = CourseMember::where('user_id', $feedback->user_id)->first();
                    $feedback->name_trainer = $trainer->user->name;
                }

                if($feedback->feedback_source_id == 2 && $feedback->user_id !== null){
                    $peerReviewer = CourseMember::where('user_id', $feedback->user_id)->first();
                    $feedback->peer_reviewer_pseudonym = $peerReviewer->pseudo_first_name . ' ' . $peerReviewer->pseudo_last_name;
                }

            }
        }

        return response()->json(['counselling' => $this->adaptCounsellingProperties($counselling)], 200);
    }


    /** Show the counselling view for peer reviewers
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @param \App\Counselling\Models\CounsellingFeedback $counsellingFeedback
     */

    public function indexPeer(Counselling $counselling, CounsellingFeedback $counsellingFeedback){

        $this->authorize('userIsLockedPeerReviewer', $counsellingFeedback);
        $course = Course::find($counselling->counsellingSetup->course->id);

        return view('student.peerReview', ['counsellingId' => $counselling->id, 'counsellingFeedback' => $counsellingFeedback, 'feedbackRequestId' => $counsellingFeedback->id, 'end_date' => DateHelper::formatDate($course->end_date)]);
    }

    public function indexDataPeer(Counselling $counselling, CounsellingFeedback $counsellingFeedback){
        $this->authorize('userIsLockedPeerReviewer', $counsellingFeedback);

        $counselling->load([
            'counsellingMessages',
            'counsellingFeedbacks' => function ($query) use($counsellingFeedback) {
                $query->where('id', $counsellingFeedback->id)->first();
            }
        ]);

        return response()->json(['counselling' => $this->adaptCounsellingProperties($counselling)], 200);
    }



    /** Get data for given counselling
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @param \App\Counselling\Models\CounsellingFeedback $counsellingFeedback
     */
    public function lockPeer(Counselling $counselling, CounsellingFeedback $counsellingFeedback){
        $this->authorize('userAlreadySubmittedFeedback', $counselling);
        $currentLoggedInUser = User::find(\Illuminate\Support\Facades\Auth::id());

        if($counsellingFeedback->status_feedback_id === 1){
            $updateSuccess = CounsellingFeedback::where('id', $counsellingFeedback->id)->update(['user_id' => $currentLoggedInUser->id, 'accepted_at' => now(), 'status_feedback_id' => 2]);
        }
        else{
            $updateSuccess = true;
        }

        if($updateSuccess){
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['message' => 'Anfrage konnte nicht angenommen werden.'], 500);
    }

    /** Get all counsellings for a user for given setup-id
     *
     * @param  \App\Counselling\Models\CounsellingSetup  $counsellingSetup
     * @return \Illuminate\Http\Response
     */
    public function getCounsellingBySetupId(CounsellingSetup $counsellingSetup)
    {
        $userId = Auth::id();

        $counsellings = Counselling::where('counselling_setup_id', $counsellingSetup->id)
            ->whereHas('courseMember', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->load('counsellingFeedbacks')
            ->map(function ($counselling) {
                $counselling = $this->adaptCounsellingProperties($counselling);
                $counselling->feedbackCounts = $this->countCounsellingFeedbacks($counselling->counsellingFeedbacks);
                return $counselling;
            });

        return response()->json($counsellings);
    }

    /** Get all counsellings for a user for given setup-id
     *
     * @param  \App\Course\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function getCounsellingByCourseId($mandatory, Course $course){

        $courseMembers = CourseMember::where([['course_id', $course->id], ['role_id', 4]])->get();

        if($mandatory === "mandatory"){
            $counsellingSetups = $course->counsellingSetups->where('mandatory', true);
            $setups = [];

            foreach($courseMembers as $courseMember){

                $userId = $courseMember->user_id;

                foreach($counsellingSetups as $setup){

                    $settings = $setup->settings;
                    $counsellingField = CounsellingField::whereIn('id', $settings['counselling_fields'])->select('id', 'name')->first();
                    $persona = Persona::whereIn('id', $settings['personae'])->select('id', 'name', 'counselling_field_id')->first();

                    $personaeData =  [
                        'id' => $setup->id,
                        'name' => $persona->name,
                        'counselling_field' => $counsellingField->name,
                    ];

                    $counsellings = Counselling::where('counselling_setup_id', $setup->id)
                        ->whereHas('courseMember', function ($query) use ($userId) {
                            $query->where('user_id', $userId);
                        })
                        ->orderBy('created_at', 'desc')
                        ->whereIn('counselling_setup_id', $counsellingSetups->pluck('id'))
                        ->get()
                        ->load(['counsellingFeedbacks' => function ($query) {
                            $query->where(['feedback_source_id' => 1]);}])
                        ->map(function ($counselling) {
                            $counselling = $this->adaptCounsellingProperties($counselling);
                            $counselling->feedbackCounts = $this->countCounsellingFeedbacks($counselling->counsellingFeedbacks);
                            return $counselling;
                        });

                    $setups[] = array_merge($setup->toArray(),
                        [
                            'course_member_name' => $courseMember->user->name,
                            'counselling' => $counsellings->toArray(),
                            'personae' => $personaeData
                        ]);
                }
            }

            return response()->json($setups);
        }

        else{
            $counsellingSetups = $course->counsellingSetups->where('mandatory', false);

            $counsellings = Counselling::whereIn('course_member_id', function ($query) use ($course) {
                $query->select('id')
                    ->from('course_members')
                    ->where([['course_id', $course->id], ['role_id', 4]]);
            })
                ->whereHas('counsellingFeedbacks', function ($query) {
                    $query->where('feedback_source_id', 1);
                })
                ->whereIn('counselling_setup_id', $counsellingSetups->pluck('id'))
                ->get()
                ->map(function ($counselling) {
                    $counselling = $this->adaptCounsellingProperties($counselling);
                    $counselling->feedbackCounts = $this->countCounsellingFeedbacks($counselling->counsellingFeedbacks);
                    return $counselling;
                })
                ->load(['counsellingFeedbacks' => function ($query) {
                    $query->where(['feedback_source_id' => 1]);}]);

            return response()->json($counsellings);

        }
    }

    /** Get data for given counselling
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     */
    public function getPseudonymForCounselling(Counselling $counselling) {

        $courseMember = $counselling->courseMember;

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


    /** Show wizard for exercise creation
     *
     * @param  \App\Counselling\Models\CounsellingSetup  $counsellingSetup
     */
    public function showWizard(CounsellingSetup $counsellingSetup) {
        $this->checkEditRights($counsellingSetup->course_id);

        $settings = $counsellingSetup->settings;
        $counsellingFields = CounsellingField::whereIn('id', $settings['counselling_fields'])->select('id', 'name')->get();
        $personae = Persona::whereIn('id', $settings['personae'])->select('id', 'name', 'counselling_field_id')->get();
        $data =  [
            'counselling_fields' => $counsellingFields,
            'personae' => $personae,
            'setup_id' => $counsellingSetup->id
        ];
        $this->storeCourseInSession($counsellingSetup->course->id);
        return view('student.wizard', ['settings' => $data]);
    }

    /** Create new counselling
     *
     * @param  \App\Counselling\Models\CounsellingSetup  $counsellingSetup
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, CounsellingSetup $counsellingSetup) {
        $this->checkEditRights($counsellingSetup->course_id);

        if ($counsellingSetup->mandatory || !$request->filled('persona')) { // load possible personae from database, if it's a task or no personae are given
            $personae = Persona::whereIn('id', $counsellingSetup->settings['personae'])->pluck('id')->toArray();
        } else {
            $personae = $request->input('persona');
        }

        if (count($personae) > 1) { // select random persona_id, if more than one is possible
            $index = array_rand($personae);
            $personaId = $personae[$index];
        } else {
            $personaId = reset($personae);
        }

        $data =  [
            'persona_id' => $personaId,
            'counselling_setup_id' => $counsellingSetup->id,
            'course_member_id' => CourseMember::where('user_id', auth()->user()->id)->where('course_id', $counsellingSetup->course->id)->first()->id,
            'title' => Persona::find($personaId)->name . ' - ' . Carbon::now()->format('d.m.Y'),
            'status_chat_id' => StatusHelper::getIdFromTitle('in progress')
        ];

        $counselling = Counselling::create($data);
        return response()->json($counselling);
    }

    /** Delete the given counselling
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function delete(Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);

        Counselling::destroy($counselling->id);
        return response()->json(['message' => 'Beratung gelöscht'], 200);
    }

    /** Change the chat status to 'done'
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function finishChat(Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);

        $courseMember = CourseMember::find($counselling->course_member_id);
        $this->checkEditRights($courseMember->course_id);
        $counselling->status_chat_id = StatusHelper::getIdFromTitle('done');
        $counselling->save();
        return response()->json($counselling);
    }

    /** Generate AI Feedback after finish Chat
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function generateAIFeedback(Counselling $counselling, Request $request)
    {
        if(!isset($request->feedbackTypeId)){
            $feedbackTypes = FeedbackType::where('id', 1)->orWhere('id', 5)->get();
        }

        else{
            $feedbackTypes = FeedbackType::where('id', $request->feedbackTypeId)->get();
        }
        GenerateFeedbackHelper::generateFeedbackWithOpenAi($counselling->persona_id, $counselling->id, $feedbackTypes);
        $counsellingFeedbacks = CounsellingFeedback::where('feedback_source_id', 3)->get();

        return response()->json(['counsellingFeedbacks' => $counsellingFeedbacks], 200);
    }

    /** Edit given counselling
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);
        $courseMember = CourseMember::find($counselling->course_member_id);
        $this->checkEditRights($courseMember->course_id);
        $data = $request->only(
            'title',
            'note',
            'data',
        );
        $currentPostProcessingProcess = $counselling->postprocessing_process;
        $type = $request->type;

        if($type === 'note'){
            $currentPostProcessingProcess[$type] = $data['note'];
        }
        else if($type === 'title'){
            $counselling->title = $data['data'];
        }
        else{
            $currentPostProcessingProcess[$type] = $data['data'];
        }

        $counselling->postprocessing_process = $currentPostProcessingProcess;

        $counselling->save();
        $counselling->load('counsellingMessages', 'counsellingFeedbacks');


        $counselling->load([
            'counsellingMessages',
            'counsellingFeedbacks' => function ($query) {
                $query->orderBy('updated_at', 'asc');
            }
        ]);

        if($counselling->counsellingFeedbacks){
            foreach($counselling->counsellingFeedbacks as $feedback){
                if($feedback->feedback_source_id == 2 && $feedback->user_id !== null){
                    $peerReviewer = CourseMember::where('user_id', $feedback->user_id)->first();
                    $feedback->peer_reviewer_pseudonym = $peerReviewer->pseudo_first_name . ' ' . $peerReviewer->pseudo_last_name;
                }

                if($feedback->feedback_source_id == 1 && $feedback->user_id !== null){
                    $trainer = CourseMember::where('user_id', $feedback->user_id)->first();

                    $feedback->name_trainer = $trainer->user->name;
                }
            }
        }
        return response()->json(['message' => 'Erfolgreich gespeichert', 'counselling' => $this->adaptCounsellingProperties($counselling)], 200);
    }

    /** Stores new message from user
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
    */
    public function storeMessage(Request $request, Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);
        $courseMember = CourseMember::find($counselling->course_member_id);
        $this->checkEditRights($courseMember->course_id);

        $textUser = '';
        if ($request->has('message')) {
            $textUser = $request->input('message');
            $messageUser = new CounsellingMessage();
            $messageUser->counselling_id = $counselling->id;
            $messageUser->content = $textUser;
            $messageUser->author = 'user';
            $messageUser->save();
            $response[] = $messageUser;
            return response()->json($response);
        }
        return response()->json(['message' => 'Nachricht konnte nicht gesendet werden.'], 500);
    }

    /** Generates new Message
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function generate(Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);
        $courseMember = CourseMember::find($counselling->course_member_id);
        $this->checkEditRights($courseMember->course_id);

        $response = [];
        try {
            $messageViKl = GenerateMessageHelper::generateWithOpenAi($counselling->persona_id, $counselling->id);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Fehler beim Generieren einer neuen Nachricht.'], 500);
        }
        $message = new CounsellingMessage();
        $message->counselling_id = $counselling->id;
        $message->content = $messageViKl;
        // $message->content = 'Hallo';
        $message->author = 'vikl';
        $message->save();
        $response[] = $message;

        sleep(3);

        return response()->json($response);
    }

    /** Handles edit request for counsellingMessage note
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function editMessageAddition(Request $request, $counsellingId, $messageNumber) {
        $counsellingMessage = CounsellingMessage::where(['counselling_id' => $counsellingId, 'message_number' => $messageNumber])->first();
        $type = $request->input('type');

        if ($counsellingMessage) {
            $text = $request->input('text');
            $additions = $counsellingMessage->additions;

            switch ($type) {
                case 'note':
                    $additions['note'] = $text;
                    break;
                case 'explanation':
                    $additions['rating']['explanation'] = $text;
                    break;
                case 'ratingValue':
                    $additions['rating']['value'] = $text;
                    break;
                default:
                    break;
            }

            $counsellingMessage->additions = $additions;
            $counsellingMessage->save();

            $successMessage = ucfirst($type) . ' erfolgreich gespeichert.';
            return response()->json(['message' => $successMessage, 'counsellingMessage' => $counsellingMessage], 200);
        }

        return response()->json(['message' => 'Nachricht nicht gefunden'], 500);
    }


    private function adaptCounsellingProperties($counselling) {
        // TODO_FB: feedback or peer-review exists -> translate status_id
        $persona = Persona::find($counselling->persona_id);
        $counselling->persona = $persona->name;
        $counselling->counselling_field = CounsellingField::find($persona->counselling_field_id)->name;
        $counselling->start_date = DateHelper::formatDate($counselling->created_at);
        $counselling->status_chat = StatusHelper::getStatusById($counselling->status_chat_id);
        $counselling->course = $counselling->counsellingSetup->course->id;

        return $counselling;
    }

    private function countCounsellingFeedbacks($counsellingFeedbacks) {
        // Retrieve all feedback sources and statuses with their titles indexed by their ids.
        $feedbackSources = FeedbackSource::pluck('title', 'id');
        $statuses = Status::pluck('title', 'id');

        // Group counselling feedbacks by feedback source id.
        return $counsellingFeedbacks
            ->groupBy('feedback_source_id')
            ->mapWithKeys(function ($feedbacksBySource, $sourceId) use ($feedbackSources, $statuses) {
                $sourceTitle = $feedbackSources[$sourceId];

                // Check if the source is 'teacher' and handle it differently since it can only have one feedback.
                if ($sourceTitle === 'teacher' && $feedbacksBySource->count() === 1) {
                    // Get the single feedback object.
                    $singleFeedback = $feedbacksBySource->first();
                    // Get the title of the current status.
                    $currentStatusTitle = $statuses[$singleFeedback->status_feedback_id];
                    // Return the source with the current status title and a count of 1.
                    return [$sourceTitle => [
                        'total' => 1,
                        'status' => [$currentStatusTitle => 1]
                    ]];
                }

                // For other sources, group the feedbacks by status id and count them.
                $statusCounts = $feedbacksBySource
                    ->groupBy('status_feedback_id')
                    ->mapWithKeys(function ($feedbacksByStatus, $statusId) use ($statuses) {
                        $statusTitle = $statuses[$statusId];
                        return [$statusTitle => $feedbacksByStatus->count()];
                    });

                return [$sourceTitle => [
                    'total' => $feedbacksBySource->count(),
                    'status' => $statusCounts
                ]];
            });
    }




    private function storeCourseInSession($courseId) {
        $course = Course::find($courseId);
        session(['current_course' => [
            'id' => $courseId,
            'name' => $course->name,
        ]]);
    }

    private function checkEditRights($course_id) {
        $course = Course::find($course_id);
        $this->authorize('userIsCourseMember', $course);
        $this->authorize('courseIsEditable', $course);
    }
}

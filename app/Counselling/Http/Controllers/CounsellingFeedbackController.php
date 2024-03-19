<?php

namespace App\Counselling\Http\Controllers;

use App\Counselling\Models\Counselling;
use App\Counselling\Models\CounsellingFeedback;
use App\Course\Models\Course;
use App\Course\Models\CourseMember;
use App\Helpers\DateHelper;
use App\Http\Controllers\Controller;
use App\Models\FeedbackSource;
use App\Models\User;
use App\Persona\Models\CounsellingField;
use App\Persona\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class CounsellingFeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     */
    public function storeRequest(Request $request)
    {
        $counsellingId = $request->input('counselling_id');
        $counselling = Counselling::find($counsellingId);
        $source = $request->input('source');
        $feedbackSource = FeedbackSource::where('title', $source)->first();
        $courseMember = CourseMember::find($counselling->course_member_id);
        $currentCourseMemberProperties = json_decode($courseMember->properties, true);

        if($source === 'peer'){
            $coins = $request->input('coins');

            // Check if $coins is a number >=1
            if (!is_numeric($coins) || $coins < 1) {
                return response()->json(['message' => 'Die Anzahl der Peer Reviews muss eine Zahl größer oder gleich 1 sein.'], 400);
            }

            // create as much Feedback Requests as tokens spent
            for ($i = 0; $i < $coins; $i++) {
                CounsellingFeedback::create([
                    'counselling_id' => $counsellingId,
                    'status_feedback_id' => 1,
                    'feedback_source_id' => $feedbackSource->id,
                ]);
            }
            $currentCourseMemberTokens = $currentCourseMemberProperties['peer_review_token'];
            $newCourseMemberTokens = $currentCourseMemberTokens - $coins;
            $currentCourseMemberProperties['peer_review_token'] = $newCourseMemberTokens;
            $courseMember->properties = json_encode($currentCourseMemberProperties);
            $courseMember->save();

            $totalPeerReviews = CounsellingFeedback::where('counselling_id', $counsellingId)->where('feedback_source_id', $feedbackSource->id)->count();

            $pendingPeerReviews = CounsellingFeedback::where('counselling_id', $counsellingId)
                ->where('feedback_source_id', $feedbackSource->id)
                ->whereIn('status_feedback_id', [1, 2])
                ->count();

            $feedbackRequestText = $coins === 1 ? 'Feedbackanfrage' : 'Feedbackanfragen';

            return response()->json([
                'message' => $feedbackRequestText .' erfolgreich verschickt',
                'availableTokens' => $newCourseMemberTokens,
                'totalPeerReviews' => $totalPeerReviews,
                'pendingPeerReviews' => $pendingPeerReviews,
            ], 200);
        }

        if($source === 'teacher'){

            CounsellingFeedback::create([
                'counselling_id' => $counsellingId,
                'status_feedback_id' => 1,
                'feedback_source_id' => $feedbackSource->id,
            ]);

            $currentCourseMemberFeedbackRequestsMade = $currentCourseMemberProperties['feedback_requests_made'];
            $newCourseMemberFeedbackRequestsMade = $currentCourseMemberFeedbackRequestsMade + 1;
            $currentCourseMemberProperties['feedback_requests_made'] = $newCourseMemberFeedbackRequestsMade;
            $courseMember->properties = json_encode($currentCourseMemberProperties);
            $courseMember->save();

            $today = Date::now();
            return response()->json([
                'message' => 'Feedbackanfrage erfolgreich verschickt',
                'requestDate' => $today
            ], 200);
        }
    }

    public function storeFeedback(Request $request){

        $feedbackRequest = CounsellingFeedback::find($request->feedback_request_id);
        if ($feedbackRequest) {
            $updateSuccess = $feedbackRequest->update(['feedback_text' => $request->feedback_text, 'received_at' => now(), 'status_feedback_id' => 3]);

            if ($updateSuccess) {
                if($request->source === 'teacher'){
                    $feedbackRequest->update(['user_id' => auth()->user()->id]);
                    return response()->json(['message' => 'Feedback erfolgreich gespeichert.', 'feedback_text' => $request->feedback_text], 200);
                }
                else{
                    $user = User::find(Auth::id());
                    $courseMember = CourseMember::where([['user_id', $user->id], ['course_id', $request->course_id]])->first();

                    if ($courseMember) {
                        $courseMemberProperties = json_decode($courseMember->properties, true);

                        $currentCourseMemberTokens = $courseMemberProperties['peer_review_token'];
                        $courseMemberProperties['peer_review_token'] = $currentCourseMemberTokens + 1;
                        $courseMember->properties = json_encode($courseMemberProperties);

                        $saveSuccess = $courseMember->save();

                        if ($saveSuccess) {
                            return response()->json(['message' => 'Feedback erfolgreich gespeichert.'], 200);
                        }
                    }
                }

            }
        }
        return response()->json(['status' => 'error'], 500);
    }

    public function autoSave(Request $request){
        $feedbackRequest = CounsellingFeedback::find($request->feedback_request_id);
        if ($feedbackRequest) {
            $feedbackRequest->update(['feedback_text' => $request->feedback_text]);
            return response()->json(['status' => 'success'], 200);
        }
    }

    public function cancelStoreFeedback(Request $request){
        $feedbackRequest = CounsellingFeedback::find($request->feedback_request_id);
        if ($feedbackRequest) {
            $updateSuccess = $feedbackRequest->update(['user_id' => null, 'feedback_text' => null, 'accepted_at' => null, 'status_feedback_id' => 1]);
        }

        if ($updateSuccess) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }


    public  function updateNewAvailable(Request $request){
        $feedbackRequest = CounsellingFeedback::find($request->feedback_request_id);
        if ($feedbackRequest) {
            $updateSuccess = $feedbackRequest->update(['status_feedback_id' => 4]);
        }

        if ($updateSuccess) {
            return response()->json(['success' => true, 'data' => $feedbackRequest]);
        }

        return response()->json(['success' => false]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

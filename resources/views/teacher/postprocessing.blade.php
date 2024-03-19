@extends('layouts.app')

@section('content')
    <div class="container">
        <postprocessing :id="{{ $counsellingId }}" :user-id="{{$user_id}}" @if($counsellingFeedback !== null):counselling-feedback="{{$counsellingFeedback}}" @endif @if($feedback_request_id !== null) :feedback-request-id="{{ $feedback_request_id }}" @endif course-end-date="{{ $end_date }}" :is-teacher="true" :user-is-counselling-creator="false"></postprocessing>
    </div>
@endsection

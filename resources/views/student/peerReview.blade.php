@extends('layouts.app')

@section('content')
    <div class="container">
        <feedback-postprocessing :id="{{ $counsellingId }}" :counselling-feedback="{{$counsellingFeedback}}" :feedback-request-id="{{$feedbackRequestId}}" course-end-date="{{ $end_date }}" :is-teacher="false" :is-peer="true"></feedback-postprocessing>
    </div>
@endsection

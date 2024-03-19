@extends('layouts.app')

@section('content')
<div class="container">
    <postprocessing :id="{{ $counsellingId }}" course-end-date="{{ $end_date }}" :trainer-feedback-contingent="{{$trainerFeedbackContingent}}" :is-teacher="false" :user-is-counselling-creator="true"></postprocessing>
</div>
@endsection

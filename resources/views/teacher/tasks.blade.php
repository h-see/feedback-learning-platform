@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <fa-icon :icon="['fas', 'list-check']"></fa-icon>
                        @if(isset($feedbacksOnly) &&$feedbacksOnly)
                            Feedbackanfragen Pflichtaufgaben
                        @else
                            Übersicht Pflichtaufgaben
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <task-table :small-view="false" :user-id="{{$user_id}}" :is-teacher="true" @if(isset($feedbacksOnly)) :feedbacks-only="{{$feedbacksOnly}}" @endif :course="{{$course}}" :course-members="{{$course_members}}" course-end-date="{{ $end_date }}"></task-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

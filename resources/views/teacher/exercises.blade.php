@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <fa-icon :icon="['fas', 'list-check']"></fa-icon>
                        Feedbackanfragen Übungen
                    </div>
                    <div class="card-body p-0">
                        <exercise-table :small-view="false" :user-id="{{$user_id}}" :is-teacher="true" :course="{{$course}}" :course-members="{{$course_members}}" course-end-date="{{ $end_date }}"></exercise-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

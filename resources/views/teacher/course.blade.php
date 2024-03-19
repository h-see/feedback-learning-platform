@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <fa-icon :icon="['fas', 'comment']"></fa-icon>
                        Feedbackanfragen Pflichtaufgaben
                    </div>
                    <div class="card-body p-0">
                        <task-table :small-view="true" :user-id="{{$user_id}}" :is-teacher="true" :feedbacks-only = "true" :course="{{$course}}" :course-members="{{$course_members}}"  course-end-date="{{ $course->end_date }}"></task-table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <fa-icon :icon="['fas', 'comment']"></fa-icon>
                        Feedbackanfragen Übungen
                    </div>
                    <div class="card-body p-0">
                        <exercise-table :small-view="true" :user-id="{{$user_id}}" :is-teacher="true" :course="{{$course}}" :course-members="{{$course_members}}"  course-end-date="{{ $course->end_date }}"></exercise-table>
                    </div>
                </div>
                @if(count($course->counsellingSetups) > 1)
                    <div class="card">
                        <div class="card-header">
                            <fa-icon :icon="['fas', 'tasks']"></fa-icon>
                            Übersicht Pflichtaufgaben
                        </div>
                        <div class="card-body p-0">
                            <task-table :small-view="true" :is-teacher="true" :course="{{$course}}" :course-members="{{$course_members}}" course-end-date="{{ $course->end_date }}" ></task-table>
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <fa-icon :icon="['fas', 'info-circle']"></fa-icon>
                        Kursinformationen
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <fa-icon :icon="['fas', 'calendar']" class="text-grey-dark h2 mb-0"></fa-icon>
                            </div>
                            <div class="col">{{ \Carbon\Carbon::parse($course->start_date)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($course->end_date)->format('d.m.Y') }}</div>
                        </div>
                        <div class="row align-items-center mt-2">
                            <div class="col-auto pe-0" title="Anzahl eingeschriebener Studierender">
                                <fa-icon :icon="['fas', 'user-group']" class="text-grey-dark h2 mb-0"></fa-icon>
                            </div>
                            <div class="col">{{$course->student_count}} Kursmitglieder</div>
                        </div>
                        <div class="row align-items-center mt-2">
                            <div class="col-auto pe-1">
                                <fa-icon :icon="['fas', 'comment']" class="text-grey-dark h2 mb-0"></fa-icon>
                            </div>
                            <div class="col" title="Feedbackanfragen die noch nicht abgegeben sind (angefragt oder in Bearbeitung)">{{$course->open_feedback_requests}} offene {{$course->open_feedback_requests === 1 ? "Feedbackanfrage" : "Feedbackanfragen" }}</div>
                        </div>

                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <fa-icon :icon="['fas', 'chart-pie']"></fa-icon>
                        Statistik
                    </div>
                    <div class="card-body p-0">
                        <teacher-statistics :course-id="{{ $course->id }}"></teacher-statistics>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <fa-icon :icon="['fas', 'question']"></fa-icon>
                        Hilfecenter
                    </div>
                    <div class="card-body text-center">
                        <p>Unterstützung notwendig oder technische Probleme?</p>
                        <div class="fw-bold text-grey-dark">Zum Hilfecenter</div>
                        {{-- <a class="link" href="{{ route('helpdesk.index') }}">Zum Hilfecenter</a> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

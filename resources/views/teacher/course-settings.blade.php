@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">{{ isset($course_id) ? 'Kurs bearbeiten' : 'Neuer Kurs' }}</div>
                <div class="card-body">
                    <course-settings :course-id="{{ $course_id ?? 'null' }}"></course-settings>
                </div>
            </div>
        </div>
</div>
@endsection

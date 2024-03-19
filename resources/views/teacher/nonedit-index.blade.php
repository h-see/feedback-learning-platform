@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-12 col-md-9">
                <div class="card">
                    <div class="card-header">Kurse</div>
                    <div class="card-body p-0">
                        <student-courses></student-courses>
                        <div class="row text-center mt-5 mb-3">
                            <div class="col text-end">
                                <a href="{{ route('courses.enrollment') }}" class="btn btn-primary">Kurseinschreibung als Student*in</a>
                            </div>
                            <div class="col text-start">
                                <a href="{{ route('courses.trainerEnrollment') }}" class="btn btn-secondary">Kurseinschreibung als Trainer*in</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

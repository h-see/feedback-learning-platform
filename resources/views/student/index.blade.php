@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Kurse</div>
                <div class="card-body px-0">
                    <student-courses></student-courses>
                    <div class="row text-center mt-5">
                        <div class="col">
                            <a href="{{ route('courses.enrollment') }}" class="btn btn-primary">Kurseinschreibung</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

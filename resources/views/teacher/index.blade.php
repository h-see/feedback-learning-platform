@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-md-9">
            <div class="card">
                <div class="card-header">Erstellte Kurse</div>
                <div class="card-body p-0">
                    <course-list></course-list>
                    <div class="row text-center mt-5">
                        <div class="col">
                            <a href="{{ route('course.create') }}"
                                class="btn btn-primary mb-3">
                                Neuen Kurs anlegen
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

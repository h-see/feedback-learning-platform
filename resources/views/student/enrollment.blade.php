@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row text-center mb-3">
        <div class="col h5">
            Kurseinschreibung
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="container-panel container-content">
                <form method="POST" action="{{ route('course.members.store') }}">
                    @csrf
                    <div class="row mb-3 mt-3">
                        <div class="col-sm-12">
                            <select id="coursename" class="form-select form-control @error('coursename') is-invalid @enderror" name="coursename" required autofocus>
                                <option value="">Wähle einen Kurs aus</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->name }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <input id="key" class="form-control @error('key') is-invalid @enderror" name="key" required placeholder="Einschreibeschlüssel" autocomplete="off">

                            @error('key')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="text-center mt-5 mb-3">Um geschützt und anonym üben zu können, muss ein Pseudonym vergeben werden:</div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <input id="pseudoFirstName" type="text" class="form-control @error('pseudoFirstName') is-invalid @enderror" name="pseudoFirstName" value="{{ old('pseudoFirstName') }}" required placeholder="Vorname" autocomplete="off">

                            @error('pseudoFirstName')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <input id="pseudoLastName" type="text" class="form-control @error('pseudoLastName') is-invalid @enderror" name="pseudoLastName" value="{{ old('pseudoLastName') }}" required placeholder="Nachname" autocomplete="off">

                            @error('pseudoLastName')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary btn--full-width">
                                {{ __('Einschreiben') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

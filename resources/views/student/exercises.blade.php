@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'cog']"></fa-icon>
                    Übungen
                </div>
                <div class="card-body p-0">
                    <exercise-table :small-view="false" :is-teacher="false" :setup-id="{{$id}}" course-end-date="{{ $end_date }}"></exercise-table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'list-check']"></fa-icon>
                    Aufgaben
                </div>
                <div class="card-body p-0">
                    <task-table :small-view="false" :is-teacher="false" :setups="{{ $setups }}"  course-end-date="{{ $end_date }}"></task-table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

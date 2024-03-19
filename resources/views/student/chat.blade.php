@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'comment']"></fa-icon>
                    Chat
                </div>
                <div class="card-body">
                    <chat :id="{{ $counsellingId }}" course-end-date="{{ $end_date }}"></chat>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
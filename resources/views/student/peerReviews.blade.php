@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <fa-icon :icon="['fas', 'user-friends']"></fa-icon>
                        Peer-Review Anfragen
                    </div>
                    <div class="card-body p-0">
                        <peer-review-request-table :course-id="{{$course->id}}" course-end-date="{{ $end_date }}" :small-view="false"></peer-review-request-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

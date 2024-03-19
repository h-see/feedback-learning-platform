@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Beratungsfelder und Personae</div>
                <div class="card-body">
                    <personae-setting></personae-setting>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Nutzerverwaltung</div>
                <div class="card-body text-center">
                    <a href="{{ route('admin.usermanagement') }}"
                        class="btn btn-link">
                        Zu den Nutzern
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

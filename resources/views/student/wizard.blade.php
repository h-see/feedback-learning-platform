@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'cog']"></fa-icon>
                    Neue Übung
                </div>
                <div class="card-body">
                    <wizard-component :settings="{{ json_encode($settings) }}"></wizard-component>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

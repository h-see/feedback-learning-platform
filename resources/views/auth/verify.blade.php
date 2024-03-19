@extends('layouts.app')

@section('content')
<div class="container auth-form mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="container-panel container-content">
                @if (session('resent'))
                    <div class="alert alert-success" role="alert">
                        {{ __('Neuer Link wurde verschickt.') }}
                    </div>
                @endif

                {{ __('Vor dem Fortfahren bitte die E-Mails auf einen Bestätigungslink überprüfen.') }}
                {{ __('Falls keine E-Mail gesendet wurde') }},
                <form class="d-inline" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('hier klicken, um eine weitere anzufordern.') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
